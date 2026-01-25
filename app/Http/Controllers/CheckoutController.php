<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function createOrder(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $cartItems = $user->cartItems()->with(['cloth.images', 'cloth.size', 'cloth.condition'])->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty'], 422);
        }

        $rentalSubtotal = 0;
        $buySubtotal = 0;
        $securityDeposit = 0;

        $rentalStartDates = [];
        $rentalEndDates = [];

        foreach ($cartItems as $item) {
            if ($item->purchase_type === 'buy') {
                $buySubtotal += (float) ($item->total_purchase_cost ?? 0);
            } else {
                $daily = (float) ($item->cloth->rent_price ?? 0);
                $rentalSubtotal += (float) ($item->total_rental_cost ?? ($daily * $item->quantity));
                $securityDeposit += (float) ($item->cloth->security_deposit ?? 0) * $item->quantity;

                if ($item->rental_start_date) {
                    $rentalStartDates[] = $item->rental_start_date;
                }
                if ($item->rental_end_date) {
                    $rentalEndDates[] = $item->rental_end_date;
                }
            }
        }

        $grandTotal = $rentalSubtotal + $buySubtotal + $securityDeposit;

        if ($grandTotal <= 0) {
            return response()->json(['success' => false, 'message' => 'Unable to calculate order total'], 422);
        }

        $order = Order::create([
            'buyer_id' => $user->id,
            'total_amount' => $grandTotal,
            'security_amount' => $securityDeposit,
            'has_rental_items' => $rentalSubtotal > 0,
            'has_purchase_items' => $buySubtotal > 0,
            'status' => 'Pending',
            'delivery_address' => $user->address ?? 'Not provided',
            'rental_from' => !empty($rentalStartDates) ? min($rentalStartDates) : now(),
            'rental_to' => !empty($rentalEndDates) ? max($rentalEndDates) : now()->addDays(3),
        ]);

        // Create Order Items
        foreach ($cartItems as $item) {
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'cloth_id' => $item->cloth_id,
                'price' => $item->purchase_type === 'buy' 
                    ? ($item->total_purchase_cost ?? $item->cloth->purchase_value ?? 0)
                    : ($item->total_rental_cost ?? ($item->cloth->rent_price * $item->quantity)),
            ]);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'amount' => round($grandTotal, 2),
                'amount_paise' => (int) round($grandTotal * 100),
                'currency' => 'INR',
                'receipt' => 'GR-' . Str::upper(Str::random(6)),
            ],
            'customer' => [
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->phone ?? '',
            ],
            'razorpay' => [
                'key' => config('services.razorpay.key_id', 'rzp_test_dummy'),
            ],
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'razorpay_payment_id' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $order = Order::where('id', $request->order_id)
            ->where('buyer_id', $user->id)
            ->firstOrFail();

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'payment_status' => 'Paid',
            'amount' => $order->total_amount,
            'transaction_id' => $request->razorpay_payment_id,
            'paid_at' => now(),
        ]);

        $order->update(['status' => 'Confirmed']);

        // Update availability for rented items
        $cartItems = $user->cartItems;
        foreach ($cartItems as $item) {
            if ($item->purchase_type !== 'buy' && $item->rental_start_date && $item->rental_end_date) {
                $cloth = $item->cloth;
                if ($cloth) {
                    $startDate = \Carbon\Carbon::parse($item->rental_start_date);
                    $endDate = \Carbon\Carbon::parse($item->rental_end_date);

                    // Calculate full blocked period (Delivery + Rental + Pickup)
                    $fullBlockStart = $startDate->copy()->subDay();
                    $fullBlockEnd = $endDate->copy()->addDay();

                    // 1. Block Rental Period
                    \App\Models\AvailabilityBlock::create([
                        'cloth_id' => $cloth->id,
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'type' => 'blocked',
                        'reason' => 'Rented (Order #' . $order->id . ')'
                    ]);

                    // 2. Block Delivery (1 day before)
                    \App\Models\AvailabilityBlock::create([
                        'cloth_id' => $cloth->id,
                        'start_date' => $fullBlockStart->format('Y-m-d'),
                        'end_date' => $fullBlockStart->format('Y-m-d'),
                        'type' => 'blocked',
                        'reason' => 'Delivery buffer'
                    ]);

                    // 3. Block Pickup (1 day after)
                    \App\Models\AvailabilityBlock::create([
                        'cloth_id' => $cloth->id,
                        'start_date' => $fullBlockEnd->format('Y-m-d'),
                        'end_date' => $fullBlockEnd->format('Y-m-d'),
                        'type' => 'blocked',
                        'reason' => 'Pickup buffer'
                    ]);

                    // 4. Update existing 'available' blocks to reflect the new blocked period
                    $availableBlocks = \App\Models\AvailabilityBlock::where('cloth_id', $cloth->id)
                        ->where('type', 'available')
                        ->get();

                    foreach ($availableBlocks as $available) {
                        $availStart = \Carbon\Carbon::parse($available->start_date);
                        $availEnd = \Carbon\Carbon::parse($available->end_date);

                        // Check for overlap
                        if ($availStart->lte($fullBlockEnd) && $availEnd->gte($fullBlockStart)) {
                            
                            // Case 1: Blocked period covers the entire available block -> Delete
                            if ($fullBlockStart->lte($availStart) && $fullBlockEnd->gte($availEnd)) {
                                $available->delete();
                            }
                            // Case 2: Blocked period is inside the available block -> Split
                            elseif ($fullBlockStart->gt($availStart) && $fullBlockEnd->lt($availEnd)) {
                                // Create new block for the first part
                                \App\Models\AvailabilityBlock::create([
                                    'cloth_id' => $cloth->id,
                                    'start_date' => $availStart->format('Y-m-d'),
                                    'end_date' => $fullBlockStart->copy()->subDay()->format('Y-m-d'),
                                    'type' => 'available',
                                    'reason' => $available->reason
                                ]);
                                
                                // Update existing block for the second part
                                $available->update([
                                    'start_date' => $fullBlockEnd->copy()->addDay()->format('Y-m-d')
                                ]);
                            }
                            // Case 3: Overlap at the start of available block -> Shorten from start
                            elseif ($fullBlockEnd->gte($availStart) && $fullBlockEnd->lt($availEnd)) {
                                $available->update([
                                    'start_date' => $fullBlockEnd->copy()->addDay()->format('Y-m-d')
                                ]);
                            }
                            // Case 4: Overlap at the end of available block -> Shorten from end
                            elseif ($fullBlockStart->gt($availStart) && $fullBlockStart->lte($availEnd)) {
                                $available->update([
                                    'end_date' => $fullBlockStart->copy()->subDay()->format('Y-m-d')
                                ]);
                            }
                        }
                    }

                    // 5. Cleanup: Remove available blocks shorter than 4 days (Minimum Rental Period)
                    $remainingAvailable = \App\Models\AvailabilityBlock::where('cloth_id', $cloth->id)
                        ->where('type', 'available')
                        ->get();

                    foreach ($remainingAvailable as $block) {
                        $s = \Carbon\Carbon::parse($block->start_date);
                        $e = \Carbon\Carbon::parse($block->end_date);
                        $duration = $s->diffInDays($e) + 1;
                        
                        if ($duration < 4) {
                            $block->delete();
                        }
                    }
                }
            }
        }

        // 6. Send Notification to Buyer
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'title' => 'Order Placed Successfully',
            'message' => "Your order #{$order->id} has been confirmed. Thank you for shopping with us!",
            'type' => 'success',
            'icon' => 'bi-bag-check',
            'data' => ['order_id' => $order->id],
            'read' => false
        ]);

        // 7. Send New Sale Notification to Sellers
        foreach ($cartItems as $item) {
            $cloth = $item->cloth;
            if ($cloth && $cloth->user_id) {
                $transactionType = $item->purchase_type === 'buy' ? 'sold' : 'rented';
                $messageType = $item->purchase_type === 'buy' ? 'Sale' : 'Rental';
                
                \App\Models\Notification::create([
                    'user_id' => $cloth->user_id,
                    'title' => "New {$messageType}!",
                    'message' => "Good news! Your item '{$cloth->title}' has been {$transactionType}.",
                    'type' => 'success',
                    'icon' => 'bi-cash-coin',
                    'data' => [
                        'cloth_id' => $cloth->id,
                        'order_id' => $order->id
                    ],
                    'read' => false
                ]);

                // Update SKU logic
                if ($cloth->sku > 0) {
                    $newSku = $cloth->sku - $item->quantity;
                    $cloth->sku = max(0, $newSku); // Ensure doesn't go below 0
                    
                    if ($cloth->sku == 0) {
                        $cloth->is_available = false; // Disable if Sold Out
                    }
                    $cloth->save();
                }
            }
        }

        // clear cart
        $user->cartItems()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully.',
            'redirect' => route('orders.index', ['payment' => 'success']),
        ]);
    }
}

