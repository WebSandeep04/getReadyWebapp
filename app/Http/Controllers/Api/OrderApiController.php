<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Razorpay\Api\Api; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Order; 
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\CartItem; 
use App\Models\Notification;
use App\Services\AvailabilityService;
use App\Services\PriceCalculatorService;
use App\Services\InvoiceService;
use App\Services\OrderShipmentService;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderApiController extends Controller
{
    public function index()
    {
        $orders = Order::with(['payments', 'shipments', 'invoices', 'items.cloth.images'])
            ->where('buyer_id', Auth::id())
            ->where('status', '!=', 'Pending')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    public function store(Request $request)
    {
        Log::info('--- NEW ORDER CHECKOUT STARTED (API) ---');
        Log::info('Request Data: ', $request->all());

        // 1. Validate the incoming request from the mobile app
        $request->validate([
            'payment_method' => 'required|in:cod,online',
            'delivery_address' => 'required|string',
            'delivery_city' => 'required|string',
            'delivery_state' => 'required|string',
            'delivery_pincode' => 'required|string',
            'security_amount' => 'required|numeric',
            'rental_from' => 'nullable|date',
            'rental_to' => 'nullable|date',
        ]);

        $user = Auth::user();
        Log::info('User authenticated: ID ' . $user->id);

        // Update User Profile with new delivery address if needed
        if ($request->has('delivery_address')) {
            $user->address = $request->input('delivery_address');
            $user->city = $request->input('delivery_city');
            $user->state = $request->input('delivery_state');
            $user->pincode = $request->input('delivery_pincode');
            $user->save();
        }

        // 2. Calculate your total amount securely
        $cartItems = CartItem::where('user_id', $user->id)->get();
        Log::info('Found ' . $cartItems->count() . ' items in cart.');

        if ($cartItems->isEmpty()) {
            Log::warning('Checkout failed: Cart is empty.');
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.'
            ], 400);
        }

        $priceService = new PriceCalculatorService();
        $availabilityService = new AvailabilityService();
        
        $rentalSubtotal = 0;
        $buySubtotal = 0;
        $securityDeposit = 0;
        $rentalEndDates = [];
        $detailedItems = [];

        foreach ($cartItems as $item) {
            $seller = $item->cloth->user ?? null;
            if (!$seller || empty($seller->address) || empty($seller->city) || empty($seller->state) || empty($seller->pincode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, the seller of "' . $item->cloth->title . '" has not provided a complete pickup address.'
                ], 422);
            }

            if ($item->purchase_type === 'buy') {
                if ($item->cloth->sku <= 0) {
                     return response()->json([
                        'success' => false, 
                        'message' => 'Sorry, the item "' . $item->cloth->title . '" has been sold out.'
                    ], 422);
                }
                $pricing = $priceService->calculatePurchase($item->cloth);
                $buySubtotal += $pricing['total_buyer_pay'] * $item->quantity;
                $detailedItems[] = [
                    'item' => $item,
                    'is_buy' => true,
                    'pricing' => $pricing,
                    'price' => $pricing['total_buyer_pay']
                ];
            } else {
                if ($item->rental_start_date && $item->rental_end_date) {
                    $isAvailable = $availabilityService->isAvailable($item->cloth, $item->rental_start_date, $item->rental_end_date);
                    if (!$isAvailable) {
                        return response()->json([
                            'success' => false, 
                            'message' => 'Sorry, the item "' . $item->cloth->title . '" is no longer available for the selected dates.'
                        ], 422);
                    }
                }

                $days = $item->rental_days ?? 4;
                $pricing = $priceService->calculate($item->cloth, $days);
                
                $rentalSubtotal += $pricing['total_buyer_pay'] * $item->quantity;
                $securityDeposit += (float) ($item->cloth->security_deposit ?? 0) * $item->quantity;

                if ($item->rental_end_date) $rentalEndDates[] = $item->rental_end_date;
                
                $detailedItems[] = [
                    'item' => $item,
                    'is_buy' => false,
                    'pricing' => $pricing
                ];
            }
        }

        $grandTotal = round($rentalSubtotal + $buySubtotal + $securityDeposit);
        Log::info('Final Total Pay: ' . $grandTotal);

        if ($grandTotal <= 0) {
            return response()->json(['success' => false, 'message' => 'Unable to calculate order total'], 422);
        }

        $rentalTo = !empty($rentalEndDates) ? max($rentalEndDates) : now()->addDays(3);

        // 3. Create the Order in your database
        $order = Order::create([
            'buyer_id' => $user->id,
            'buyer_name' => $user->name,
            'buyer_phone' => $user->phone,
            'status' => 'Pending',
            'payment_method' => $request->payment_method,
            'delivery_address' => $request->delivery_address,
            'delivery_city' => $request->delivery_city,
            'delivery_state' => $request->delivery_state,
            'delivery_pincode' => $request->delivery_pincode,
            'total_amount' => $grandTotal,
            'has_rental_items' => $rentalSubtotal > 0,
            'has_purchase_items' => $buySubtotal > 0,
            'security_amount' => $securityDeposit,
            'rental_from' => $request->rental_from, 
            'rental_to' => $rentalTo,
            'return_date' => Carbon::parse($rentalTo)->addDay(),
        ]);
        Log::info('Order successfully created in DB: Order ID ' . $order->id);

        foreach ($detailedItems as $dItem) {
            $item = $dItem['item'];
            $pricing = $dItem['pricing'];
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
                'rent_gst' => $pricing['item_tax_fee'] ?? $pricing['rent_gst'],
                'buyer_commission_gst' => $pricing['buyer_comm_gst'],
                'seller_commission_gst' => $pricing['seller_comm_gst'],
                'tcs_amount' => $pricing['tcs'],
                'is_seller_gst' => $pricing['is_seller_gst'],
            ]);
        }
        Log::info('OrderItems saved successfully.');

        // 4. Handle based on Payment Method
        if ($request->payment_method === 'cod') {
            Log::info('Processing COD payment...');
            
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'cod',
                'payment_status' => 'Pending',
                'amount' => $grandTotal,
                'transaction_id' => 'COD-' . Str::upper(Str::random(8)),
            ]);

            $order->update(['status' => 'Confirmed']);
            Log::info('Order status updated to Confirmed for COD.');

            $this->processPostOrderTasks($order, $user, $cartItems, 'COD');

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order' => $order
            ]);
        }

        // 5. Handle Razorpay (ONLINE)
        if ($request->payment_method === 'online') {
            Log::info('Processing ONLINE payment via Razorpay...');
            try {
                $apiKey = config('services.razorpay.key_id');
                $apiSecret = config('services.razorpay.key_secret');

                $api = new Api($apiKey, $apiSecret);

                $razorpayOrder = $api->order->create([
                    'receipt' => (string) $order->id,
                    'amount' => (int) (round($grandTotal) * 100),
                    'currency' => 'INR',
                ]);

                $order->update(['razorpay_order_id' => $razorpayOrder['id']]);

                return response()->json([
                    'success' => true,
                    'order' => [
                        'id' => $razorpayOrder['id'],
                        'amount_paise' => (int) (round($grandTotal) * 100),
                        'currency' => 'INR'
                    ],
                    'razorpay' => [
                        'key' => $apiKey 
                    ],
                    'customer' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'contact' => $user->phone ?? '' 
                    ]
                ]);

            } catch (\Exception $e) {
                Log::error('Razorpay Error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to initialize payment gateway.'
                ], 500);
            }
        }
    }


    public function verifyPayment(Request $request)
    {
        Log::info('--- VERIFY PAYMENT CALLED (API) ---');

        $request->validate([
            'order_id' => 'required|string', 
            'razorpay_payment_id' => 'required|string',
        ]);

        try {
            $apiKey = config('services.razorpay.key_id');
            $apiSecret = config('services.razorpay.key_secret');
            $api = new Api($apiKey, $apiSecret);
            
            $razorpayOrder = $api->order->fetch($request->order_id);
            $dbOrderId = $razorpayOrder['receipt'];

            $order = Order::where('id', $dbOrderId)->firstOrFail();
            
            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $request->razorpay_payment_id,
                'amount' => $order->total_amount,
                'payment_method' => 'Razorpay',
                'payment_status' => 'Paid',
                'paid_at' => now(),
            ]);

            $order->update(['status' => 'Confirmed']);
            
            $user = Auth::user();
            $cartItems = CartItem::where('user_id', $user->id)->get();
            $this->processPostOrderTasks($order, $user, $cartItems, 'Prepaid');

            return response()->json([
                'success' => true,
                'message' => 'Payment successful!'
            ]);

        } catch (\Exception $e) {
            Log::error('Verify Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 400);
        }
    }

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

                if ($item->purchase_type === 'buy' && $cloth->sku > 0) {
                    $newSku = max(0, $cloth->sku - $item->quantity);
                    $cloth->sku = $newSku;
                    if ($newSku == 0) $cloth->is_available = false;
                    $cloth->save();
                }
            }
        }

        // 5. Clear Cart
        CartItem::where('user_id', $user->id)->delete();

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
        $shipmentService = new OrderShipmentService();
        $shipmentService->createShipments($order, $user, $paymentType);
    }

    private function blockDates($cloth, $start, $end, $orderId)
    {
        $availabilityService = new AvailabilityService();
        $availabilityService->blockRentalDates($cloth, $start, $end, $orderId);
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::with(['items.cloth', 'payments', 'shipments'])->where('buyer_id', Auth::id())->findOrFail($id);

        if (!in_array($order->status, ['Pending', 'Confirmed', 'Order Confirmed & Shipment Created'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only Pending, Confirmed or recently shipped orders can be cancelled.'
            ], 400);
        }

        // Cancel shipment if any
        if ($order->status === 'Order Confirmed & Shipment Created') {
            $forwardShipment = $order->shipments()->where('type', 'forward')->where('status', '!=', 'Cancelled')->first();
            if ($forwardShipment && $forwardShipment->waybill_number) {
                $xpressbees = app(\App\Services\XpressbeesService::class);
                $xpressbees->cancelShipment($forwardShipment->waybill_number);
                $forwardShipment->status = 'Cancelled';
                $forwardShipment->save();
            }
        }

        // Cancel order
        $order->status = 'Cancelled';
        $order->save();

        // Restore stock and availability
        $availabilityService = app(AvailabilityService::class);
        foreach ($order->items as $item) {
            $cloth = $item->cloth;
            if ($cloth) {
                $cloth->sku = $cloth->sku + 1;
                $cloth->is_available = true;
                $cloth->save();

                if ($item->purchase_type !== 'buy') {
                    $availabilityService->restoreAvailabilityForOrder($cloth->id, $order->id);
                }
            }
        }

        // Refund payments via Razorpay API
        $paidPayments = $order->payments()->whereIn('payment_status', ['Paid', 'Success', 'paid', 'success'])->get();
        foreach ($paidPayments as $payment) {
            // Check if it's a Razorpay payment and we have a transaction ID
            if (str_contains(strtolower($payment->payment_method), 'razorpay') && $payment->transaction_id) {
                try {
                    $keyId = config('services.razorpay.key_id');
                    $keySecret = config('services.razorpay.key_secret');
                    
                    \Illuminate\Support\Facades\Http::withBasicAuth($keyId, $keySecret)
                        ->post("https://api.razorpay.com/v1/payments/{$payment->transaction_id}/refund", [
                            'amount' => (int) round($payment->amount * 100)
                        ]);
                } catch (\Exception $e) {
                    Log::error('Razorpay Refund Failed for Order ID ' . $order->id . ': ' . $e->getMessage());
                }
            }

            $payment->payment_status = 'Refunded';
            $payment->save();
        }

        return response()->json([
            'success' => true, 
            'message' => 'Order cancelled successfully and full refund initiated.'
        ]);
    }

    public function initiateReturn(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:early,normal',
            'return_date' => 'required|date'
        ]);
        
        // TODO: Implement return logic
        return response()->json(['success' => true, 'message' => 'Return initiated']);
    }

    public function rate(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string'
        ]);
        
        $order = \App\Models\Order::with('items')->findOrFail($id);
        
        if ($order->buyer_id !== \Illuminate\Support\Facades\Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $sellerId = $order->items->first()->seller_id ?? null;

        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller not found for this order'], 400);
        }

        \App\Models\Rating::updateOrCreate(
            [
                'order_id' => $order->id,
                'rater_id' => \Illuminate\Support\Facades\Auth::id(),
            ],
            [
                'rated_user_id' => $sellerId,
                'rating' => $request->rating,
                'review' => $request->review,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Review saved successfully']);
    }

    public function purchaseEligibility(Request $request, $id)
    {
        $request->validate([
            'cloth_id' => 'required|integer'
        ]);

        // TODO: Implement calculation logic
        return response()->json([
            'success' => true,
            'is_eligible' => true,
            'conversion_quote' => [
                'base_price' => 0,
                'commission' => 0,
                'gst' => 0,
                'minus_rent_paid_security' => 0,
                'total' => 0
            ]
        ]);
    }

    public function convertToPurchase(Request $request, $id)
    {
        // TODO: Implement Razorpay order creation for buyout
        return response()->json([
            'success' => true,
            'order_id' => 'razorpay_order_id_here',
            'keys' => [
                'key_id' => config('services.razorpay.key_id')
            ]
        ]);
    }

    public function extensionQuote(Request $request, $id)
    {
        $request->validate([
            'new_date' => 'required|date'
        ]);

        // TODO: Implement extension price calculation
        return response()->json([
            'success' => true,
            'quote' => 0
        ]);
    }

    public function extend(Request $request, $id)
    {
        // TODO: Implement Razorpay payment initiation for extension
        return response()->json([
            'success' => true,
            'order_id' => 'razorpay_order_id_here',
            'keys' => [
                'key_id' => config('services.razorpay.key_id')
            ]
        ]);
    }
    public function sales()
    {
        $userId = Auth::id();

        $orders = Order::whereHas('items.cloth', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('status', '!=', 'Pending')
            ->with(['items' => function ($query) use ($userId) {
                $query->whereHas('cloth', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->with(['cloth' => function($q) {
                    $q->with(['category', 'brand', 'size', 'color', 'fabric', 'condition', 'fitType', 'bottomType', 'images']);
                }]);
            }, 'buyer', 'payments', 'invoices'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
}

