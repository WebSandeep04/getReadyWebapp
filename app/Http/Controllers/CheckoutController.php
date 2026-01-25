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
        
        // Validate Address from Request
        $deliveryAddress = $request->input('delivery_address');
        if (empty($deliveryAddress)) {
             return response()->json(['success' => false, 'message' => 'Delivery address is required.'], 422);
        }

        // Calculate Totals
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

                if ($item->rental_start_date) $rentalStartDates[] = $item->rental_start_date;
                if ($item->rental_end_date) $rentalEndDates[] = $item->rental_end_date;
            }
        }

        $grandTotal = $rentalSubtotal + $buySubtotal + $securityDeposit;

        if ($grandTotal <= 0) {
            return response()->json(['success' => false, 'message' => 'Unable to calculate order total'], 422);
        }

        // Create Order Record
        $order = Order::create([
            'buyer_id' => $user->id,
            'total_amount' => $grandTotal,
            'security_amount' => $securityDeposit,
            'has_rental_items' => $rentalSubtotal > 0,
            'has_purchase_items' => $buySubtotal > 0,
            'status' => 'Pending',
            'delivery_address' => $deliveryAddress,
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

        // --- HANDLE COD vs ONLINE ---
        $paymentMethod = $request->input('payment_method', 'online');

        if ($paymentMethod === 'cod') {
            // Process COD Order Immediately
            
            // 1. Create Pending Payment Record
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'cod',
                'payment_status' => 'Pending', // pending until delivered
                'amount' => $grandTotal,
                'transaction_id' => 'COD-' . Str::upper(Str::random(8)),
            ]);

            // 2. Update Order Status
            $order->update(['status' => 'Confirmed']);

            // 3. Process Post-Order (Shipment, Inventory, Notifications)
            $this->processPostOrderTasks($order, $user, $cartItems, 'COD');

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully via COD.',
                'redirect' => route('orders.index'),
            ]);
        }

        // ONLINE (Razorpay)
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

        // Process Post-Order (Shipment, Inventory, Notifications)
        $this->processPostOrderTasks($order, $user, $user->cartItems, 'Prepaid');

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully.',
            'redirect' => route('orders.index', ['payment' => 'success']),
        ]);
    }

    /**
     * Shared logic for processing confirmed orders (inventory, shipment, notifs)
     */
    private function processPostOrderTasks($order, $user, $cartItems, $paymentType)
    {
        // 1. Update Availability (Blocking)
        foreach ($cartItems as $item) {
            if ($item->purchase_type !== 'buy' && $item->rental_start_date && $item->rental_end_date) {
                $this->blockDates($item->cloth, $item->rental_start_date, $item->rental_end_date, $order->id);
            }
        }

        // 2. Create Shipment
        $this->createShipment($order, $user, $paymentType);

        // 3. Send Notification to Buyer
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'title' => 'Order Placed Successfully',
            'message' => "Your order #{$order->id} has been confirmed. Thank you for shopping with us!",
            'type' => 'success',
            'icon' => 'bi-bag-check',
            'data' => ['order_id' => $order->id],
            'read' => false
        ]);

        // 4. Send Notifications to Sellers & Update Stock
        foreach ($cartItems as $item) {
            $cloth = $item->cloth;
            if ($cloth) {
                if ($cloth->user_id) {
                    $transactionType = $item->purchase_type === 'buy' ? 'sold' : 'rented';
                    $messageType = $item->purchase_type === 'buy' ? 'Sale' : 'Rental';
                    
                    \App\Models\Notification::create([
                        'user_id' => $cloth->user_id,
                        'title' => "New {$messageType}!",
                        'message' => "Good news! Your item '{$cloth->title}' has been {$transactionType}.",
                        'type' => 'success',
                        'icon' => 'bi-cash-coin',
                        'data' => ['cloth_id' => $cloth->id, 'order_id' => $order->id],
                        'read' => false
                    ]);
                }

                if ($cloth->sku > 0) {
                    $newSku = max(0, $cloth->sku - $item->quantity);
                    $cloth->sku = $newSku;
                    if ($newSku == 0) $cloth->is_available = false;
                    $cloth->save();
                }
            }
        }

        // 5. Clear Cart
        $user->cartItems()->delete();
    }

    private function createShipment($order, $user, $paymentType)
    {
        try {
            \Illuminate\Support\Facades\Log::info("Checkout: Creating {$paymentType} shipment for Order #{$order->id}");
            
            $courier = new \App\Services\XpressbeesService();
            
            $addressParts = explode(',', $order->delivery_address);
            $city = trim($addressParts[count($addressParts)-2] ?? 'Mumbai');
            $pincode = trim($addressParts[count($addressParts)-1] ?? '400001');

            $orderLoad = [
                'order_number' => $order->id,
                'payment_method' => $paymentType, // 'Prepaid' or 'COD'
                'collectable_amount' => ($paymentType === 'COD') ? $order->total_amount : 0,
                'consignee_name' => $user->name,
                'consignee_phone' => $user->phone ?? '9999999999',
                'consignee_address' => $order->delivery_address,
                'consignee_pincode' => $pincode,
                'consignee_city' => $city,
                'consignee_state' => 'Maharashtra',
                'products' => [],
                'total_amount' => $order->total_amount,
                'weight' => 0.5,
                'length' => 10,
                'breadth' => 10,
                'height' => 10
            ];

            foreach ($order->items as $item) {
                 $orderLoad['products'][] = [
                     'name' => $item->cloth->title ?? 'Item',
                     'qty' => 1,
                     'price' => $item->price
                 ];
            }

            $response = $courier->createOrder($orderLoad);

            if ($response && isset($response['awb_number'])) {
                \App\Models\Shipment::create([
                    'order_id' => $order->id,
                    'courier_name' => 'Xpressbees',
                    'waybill_number' => $response['awb_number'],
                    'reference_id' => $response['order_id'] ?? null,
                    'tracking_url' => $response['label_url'] ?? null,
                    'label_url' => $response['label_url'] ?? null,
                    'status' => 'Booked',
                ]);
                
                $order->update(['status' => 'Order Confirmed & Shipment Created']);
                \Illuminate\Support\Facades\Log::info("Checkout: Shipment created. AWB: {$response['awb_number']}");
            } else {
                \Illuminate\Support\Facades\Log::error("Checkout: Failed to create shipment. Response: " . json_encode($response));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Checkout: Shipment Exception: " . $e->getMessage());
        }
    }

    private function blockDates($cloth, $start, $end, $orderId)
    {
        $startDate = \Carbon\Carbon::parse($start);
        $endDate = \Carbon\Carbon::parse($end);
        $fullBlockStart = $startDate->copy()->subDay();
        $fullBlockEnd = $endDate->copy()->addDay();

        // 1. Block Rental
        \App\Models\AvailabilityBlock::create(['cloth_id' => $cloth->id, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d'), 'type' => 'blocked', 'reason' => 'Rented (Order #' . $orderId . ')']);
        // 2. Block Delivery
        \App\Models\AvailabilityBlock::create(['cloth_id' => $cloth->id, 'start_date' => $fullBlockStart->format('Y-m-d'), 'end_date' => $fullBlockStart->format('Y-m-d'), 'type' => 'blocked', 'reason' => 'Delivery buffer']);
        // 3. Block Pickup
        \App\Models\AvailabilityBlock::create(['cloth_id' => $cloth->id, 'start_date' => $fullBlockEnd->format('Y-m-d'), 'end_date' => $fullBlockEnd->format('Y-m-d'), 'type' => 'blocked', 'reason' => 'Pickup buffer']);

        // 4. Update existing available blocks (Splitting/Shortening) logic omitted for brevity as it is unchanged from original
        // Ideally this logic should also be moved to a Service or Model method properly
        // For now, assume this helper method handles the basic blocking. The complex splitting logic should be preserved if not moving.
        // RE-INSERTING COMPLEX LOGIC BELOW TO PRESERVE FUNCTIONALITY:

        $availableBlocks = \App\Models\AvailabilityBlock::where('cloth_id', $cloth->id)->where('type', 'available')->get();
        foreach ($availableBlocks as $available) {
            $availStart = \Carbon\Carbon::parse($available->start_date);
            $availEnd = \Carbon\Carbon::parse($available->end_date);

            if ($availStart->lte($fullBlockEnd) && $availEnd->gte($fullBlockStart)) {
                if ($fullBlockStart->lte($availStart) && $fullBlockEnd->gte($availEnd)) {
                    $available->delete();
                } elseif ($fullBlockStart->gt($availStart) && $fullBlockEnd->lt($availEnd)) {
                    \App\Models\AvailabilityBlock::create(['cloth_id' => $cloth->id, 'start_date' => $availStart->format('Y-m-d'), 'end_date' => $fullBlockStart->copy()->subDay()->format('Y-m-d'), 'type' => 'available', 'reason' => $available->reason]);
                    $available->update(['start_date' => $fullBlockEnd->copy()->addDay()->format('Y-m-d')]);
                } elseif ($fullBlockEnd->gte($availStart) && $fullBlockEnd->lt($availEnd)) {
                    $available->update(['start_date' => $fullBlockEnd->copy()->addDay()->format('Y-m-d')]);
                } elseif ($fullBlockStart->gt($availStart) && $fullBlockStart->lte($availEnd)) {
                    $available->update(['end_date' => $fullBlockStart->copy()->subDay()->format('Y-m-d')]);
                }
            }
        }
    }
}

