<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\User;
use App\Models\Shipment;
use App\Services\PriceCalculatorService;
use App\Services\AvailabilityService;
use App\Services\InvoiceService;
use App\Services\XpressbeesService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        // Update User Profile with new delivery address if provided
        if ($request->has('delivery_address')) {
            $user->address = $request->input('delivery_address');
            if ($request->has('delivery_city')) $user->city = $request->input('delivery_city');
            if ($request->has('delivery_state')) $user->state = $request->input('delivery_state');
            if ($request->has('delivery_pincode')) $user->pincode = $request->input('delivery_pincode');
            $user->save();
        }

        // Calculate Totals using PriceCalculatorService
        $priceService = new PriceCalculatorService();
        
        $rentalSubtotal = 0;
        $buySubtotal = 0;
        $securityDeposit = 0;
        $rentalStartDates = [];
        $rentalEndDates = [];
        
        $detailedItems = [];

        foreach ($cartItems as $item) {
            if ($item->purchase_type === 'buy') {
                $pricing = $priceService->calculatePurchase($item->cloth);
                $buySubtotal += $pricing['total_buyer_pay'] * $item->quantity;
                $detailedItems[] = [
                    'item' => $item,
                    'is_buy' => true,
                    'pricing' => $pricing,
                    'price' => $pricing['total_buyer_pay']
                ];
            } else {
                $days = $item->rental_days ?? 4;
                $pricing = $priceService->calculate($item->cloth, $days);
                
                $rentalSubtotal += $pricing['total_buyer_pay'] * $item->quantity;
                $securityDeposit += (float) ($item->cloth->security_deposit ?? 0) * $item->quantity;

                if ($item->rental_start_date) $rentalStartDates[] = $item->rental_start_date;
                if ($item->rental_end_date) $rentalEndDates[] = $item->rental_end_date;
                
                $detailedItems[] = [
                    'item' => $item,
                    'is_buy' => false,
                    'pricing' => $pricing
                ];
            }
        }

        $grandTotal = $rentalSubtotal + $buySubtotal + $securityDeposit;

        if ($grandTotal <= 0) {
            return response()->json(['success' => false, 'message' => 'Unable to calculate order total'], 422);
        }

        // --- FINAL AVAILABILITY & SELLER CHECK BEFORE CHECKOUT ---
        $availabilityService = new AvailabilityService();
        foreach ($cartItems as $item) {
            // Check Seller Address
            $seller = $item->cloth->user ?? null;
            if (!$seller || empty($seller->address) || empty($seller->city) || empty($seller->state) || empty($seller->pincode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, the seller of "' . $item->cloth->title . '" has not provided a complete pickup address. Please remove it from your cart to proceed.'
                ], 422);
            }

            if ($item->purchase_type !== 'buy' && $item->rental_start_date && $item->rental_end_date) {
                // If it's a rental, check if the dates are still available
                $isAvailable = $availabilityService->isAvailable($item->cloth, $item->rental_start_date, $item->rental_end_date);
                if (!$isAvailable) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Sorry, the item "' . $item->cloth->title . '" is no longer available for the selected dates. Please remove it from your cart or change the dates.'
                    ], 422);
                }
            } else if ($item->purchase_type === 'buy') {
                // If it's a purchase, check if it's still available for purchase
                if ($item->cloth->sku <= 0) {
                     return response()->json([
                        'success' => false, 
                        'message' => 'Sorry, the item "' . $item->cloth->title . '" has been sold out.'
                    ], 422);
                }
            }
        }

        $rentalTo = !empty($rentalEndDates) ? max($rentalEndDates) : now()->addDays(3);

        // Create Order Record
        $order = Order::create([
            'buyer_id' => $user->id,
            'buyer_name' => $user->name,
            'buyer_phone' => $user->phone,
            'total_amount' => $grandTotal,
            'security_amount' => $securityDeposit,
            'has_rental_items' => $rentalSubtotal > 0,
            'has_purchase_items' => $buySubtotal > 0,
            'status' => 'Pending',
            'delivery_address' => $deliveryAddress,
            'delivery_city' => $request->input('delivery_city', $user->city),
            'delivery_state' => $request->input('delivery_state', $user->state),
            'delivery_pincode' => $request->input('delivery_pincode', $user->pincode),
            'rental_from' => !empty($rentalStartDates) ? min($rentalStartDates) : now(),
            'rental_to' => $rentalTo,
            'return_date' => Carbon::parse($rentalTo)->addDay(),
        ]);

        // Create Order Items
        foreach ($detailedItems as $dItem) {
            $item = $dItem['item'];
            $pricing = $dItem['pricing'];
            
            // For both Rent and Buy, we now populate the detailed breakdown
            // Note: For Buy, base_rent maps to base_price (selling price)
            $seller = $item->cloth->user ?? null;
            OrderItem::create([
                'order_id' => $order->id,
                'cloth_id' => $item->cloth_id,
                'quantity' => $item->quantity,
                'seller_id' => $seller ? $seller->id : null,
                'seller_name' => $seller ? $seller->name : null,
                'seller_phone' => $seller ? $seller->phone : null,
                'seller_address' => $seller ? $seller->address : null,
                'seller_city' => $seller ? $seller->city : null,
                'seller_state' => $seller ? $seller->state : null,
                'seller_pincode' => $seller ? $seller->pincode : null,
                'purchase_type' => $dItem['is_buy'] ? 'buy' : 'rent',
                'price' => $pricing['total_buyer_pay'],
                'base_rent' => $pricing['base_price'] ?? $pricing['base_rent'],
                'buyer_commission' => $pricing['buyer_comm'],
                'seller_commission' => $pricing['seller_comm'],
                'rent_gst' => $pricing['item_tax_fee'] ?? $pricing['rent_gst'], // 'rent_gst' column stores Item Tax
                'buyer_commission_gst' => $pricing['buyer_comm_gst'],
                'seller_commission_gst' => $pricing['seller_comm_gst'],
                'tcs_amount' => $pricing['tcs'],
                'is_seller_gst' => $pricing['is_seller_gst'],
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
        Notification::create([
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
                    
                    Notification::create([
                        'user_id' => $cloth->user_id,
                        'title' => "New {$messageType}!",
                        'message' => "Good news! Your item '{$cloth->title}' has been {$transactionType}.",
                        'type' => 'success',
                        'icon' => 'bi-cash-coin',
                        'data' => ['cloth_id' => $cloth->id, 'order_id' => $order->id],
                        'read' => false
                    ]);
                }

                // Decrement SKU ONLY if it is a purchase
                if ($item->purchase_type === 'buy' && $cloth->sku > 0) {
                    $newSku = max(0, $cloth->sku - $item->quantity);
                    $cloth->sku = $newSku;
                    if ($newSku == 0) $cloth->is_available = false;
                    $cloth->save();
                }
            }
        }

        // 5. Clear Cart
        $user->cartItems()->delete();

        // 6. Generate Invoices
        try {
            $invoiceService = new InvoiceService();
            $invoiceService->generateOrderInvoices($order);
        } catch (\Exception $e) {
            Log::error("Invoice Generation Failed for Order #{$order->id}: " . $e->getMessage());
        }
    }

    private function createShipment($order, $user, $paymentType)
    {
        try {
            Log::info("Checkout: Creating {$paymentType} shipments for Order #{$order->id}");
            
            $courier = new XpressbeesService();
            
            // Consignee details (Buyer)
            $buyerAddress = $user->address;
            $buyerCity = $user->city;
            $buyerState = $user->state;
            $buyerPincode = $user->pincode;

            // Group items by seller
            $itemsBySeller = [];
            foreach ($order->items as $item) {
                $sellerId = $item->cloth->user_id ?? 0;
                $itemsBySeller[$sellerId][] = $item;
            }

            foreach ($itemsBySeller as $sellerId => $sellerItems) {
                $seller = User::find($sellerId);
                
                $sellerAddress = $seller->address;
                $sellerCity = $seller->city;
                $sellerState = $seller->state;
                $sellerPincode = $seller->pincode;
                $sellerPhone = str_pad(preg_replace('/[^0-9]/', '', $seller->phone), 10, '0', STR_PAD_LEFT);
                $sellerName = $seller->name;
                
                // Calculate collectable amount for THIS specific shipment (only if COD)
                $shipmentAmount = 0;
                $shipmentCollectable = 0;
                $orderItemsArray = [];
                
                foreach ($sellerItems as $sItem) {
                    // Include security deposit if applicable
                    $itemTotal = $sItem->price;
                    if ($sItem->purchase_type !== 'buy') {
                        $itemTotal += (float) ($sItem->cloth->security_deposit ?? 0);
                    }
                    
                    $shipmentAmount += $itemTotal;
                    
                    $orderItemsArray[] = [
                        'name' => $sItem->cloth->title ?? 'Item',
                        'qty' => 1,
                        'price' => $sItem->price
                    ];
                }
                
                if (strtolower($paymentType) === 'cod') {
                    $shipmentCollectable = $shipmentAmount;
                }

                $orderLoad = [
                    'order_number' => $order->id . '-' . $sellerId, // Unique order number per seller
                    'payment_type' => strtolower($paymentType) === 'cod' ? 'cod' : 'prepaid',
                    'order_amount' => $shipmentAmount,
                    'collectable_amount' => $shipmentCollectable,
                    'package_weight' => 500 * count($sellerItems),
                    'package_length' => 10,
                    'package_breadth' => 10,
                    'package_height' => 10,
                    'request_auto_pickup' => 'yes',
                    'shipping_charges' => 0,
                    'discount' => 0,
                    'cod_charges' => 0,
                    'consignee' => [
                        'name' => $user->name,
                        'address' => $buyerAddress,
                        'address_2' => '',
                        'city' => $buyerCity,
                        'state' => $buyerState,
                        'pincode' => $buyerPincode,
                        'phone' => str_pad(preg_replace('/[^0-9]/', '', $user->phone), 10, '9', STR_PAD_LEFT)
                    ],
                    'pickup' => [
                        'warehouse_name' => substr($sellerName, 0, 30),
                        'name' => substr($sellerName, 0, 30),
                        'address' => substr($sellerAddress, 0, 100),
                        'address_2' => '',
                        'city' => substr($sellerCity, 0, 40),
                        'state' => $sellerState,
                        'pincode' => $sellerPincode,
                        'phone' => $sellerPhone
                    ],
                    'order_items' => $orderItemsArray
                ];

                $response = $courier->createOrder($orderLoad);

                if ($response && isset($response['awb_number'])) {
                    Shipment::create([
                        'order_id' => $order->id,
                        'seller_id' => $sellerId,
                        'type' => 'forward',
                        'courier_name' => 'Xpressbees',
                        'waybill_number' => $response['awb_number'],
                        'reference_id' => $response['order_id'] ?? null,
                        'tracking_url' => $response['label_url'] ?? null,
                        'label_url' => $response['label_url'] ?? null,
                        'status' => 'Booked',
                    ]);
                    
                    Log::info("Checkout: Shipment created for Seller {$sellerId}. AWB: {$response['awb_number']}");
                } else {
                    Log::error("Checkout: Failed to create shipment for Seller {$sellerId}. Response: " . json_encode($response));
                }
            }
            
            $order->update(['status' => 'Order Confirmed & Shipment Created']);

        } catch (\Exception $e) {
            Log::error("Checkout: Shipment Exception: " . $e->getMessage());
        }
    }

    private function blockDates($cloth, $start, $end, $orderId)
    {
        $availabilityService = new AvailabilityService();
        $availabilityService->blockRentalDates($cloth, $start, $end, $orderId);
    }
}

