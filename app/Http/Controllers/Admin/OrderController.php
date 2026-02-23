<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Notification;
use App\Services\XpressbeesService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $statuses = ['Pending', 'Confirmed', 'Shipped', 'Delivered', 'Returned', 'Cancelled', 'Order Confirmed & Shipment Created', 'Return Requested', 'Return In Progress'];
        $paymentStatuses = ['Paid', 'Pending', 'Failed', 'Refunded'];
        $filters = $request->all();

        $query = $this->buildOrdersQuery($request);
        $orders = $query->paginate(10)->appends($request->query());

        $stats = $this->getStats();

        return view('admin.screens.orders', compact('orders', 'stats', 'statuses', 'paymentStatuses', 'filters'));
    }

    public function ordersData(Request $request)
    {
        $query = $this->buildOrdersQuery($request);
        $orders = $query->paginate(10)->appends($request->query());

        return response()->json([
            'table_html' => view('admin.components.orders-rows', compact('orders'))->render(),
            'pagination_html' => view('admin.components.orders-pagination', compact('orders'))->render(),
            'stats' => $this->getStats()
        ]);
    }

    private function transformOrder($order)
    {
        $data = $order->toArray();
        $data['user_name'] = $order->buyer ? $order->buyer->name : 'Guest';
        $data['items_count'] = $order->items->count();
        $data['formatted_date'] = $order->created_at->format('d M Y, h:i A');
        
        $data['shipment_details'] = $order->shipments->map(function($shipment) {
            return [
                 'type' => $shipment->type,
                 'awb' => $shipment->waybill_number,
                 'status' => $shipment->status,
                 'tracking_url' => $shipment->tracking_url
            ];
        });

        $data['payment_status'] = $order->payments->where('payment_status', 'Paid')->count() > 0 ? 'Paid' : 'Pending';
        
        return $data;
    }

    private function buildOrdersQuery(Request $request)
    {
        $query = Order::with(['buyer', 'items.cloth', 'shipments', 'payments', 'invoices']);

        // Search by Order ID or User Name/Email or Amount
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('id', $search)->orWhere('total_amount', 'like', "%{$search}%");
                }
                $q->orWhereHas('buyer', function ($bq) use ($search) {
                    $bq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Type
        if ($request->filled('type')) {
            if ($request->type === 'rental') {
                $query->where('has_rental_items', true)->where('has_purchase_items', false);
            } elseif ($request->type === 'purchase') {
                $query->where('has_purchase_items', true)->where('has_rental_items', false);
            } elseif ($request->type === 'mixed') {
                $query->where('has_rental_items', true)->where('has_purchase_items', true);
            }
        }

        // Filter by Return State (Overdue, Due Soon, etc.)
        if ($request->filled('return_state')) {
            $today = now()->toDateString();
            if ($request->return_state === 'overdue') {
                $query->where('has_rental_items', true)
                    ->where('rental_to', '<', $today)
                    ->whereNotIn('status', ['Returned', 'Cancelled']);
            } elseif ($request->return_state === 'due_soon') {
                $threeDaysLater = now()->addDays(3)->toDateString();
                $query->where('has_rental_items', true)
                    ->whereBetween('rental_to', [$today, $threeDaysLater])
                    ->whereNotIn('status', ['Returned', 'Cancelled']);
            } elseif ($request->return_state === 'completed') {
                $query->where('status', 'Returned');
            }
        }

        // Filter by Payment Status
        if ($request->filled('payment_status')) {
            $ps = $request->payment_status;
            $query->whereHas('payments', function ($pq) use ($ps) {
                $pq->where('payment_status', $ps);
            });
        }

        // Filter by Placement Date
        if ($request->filled('placed_from')) {
            $query->whereDate('created_at', '>=', $request->placed_from);
        }
        if ($request->filled('placed_to')) {
            $query->whereDate('created_at', '<=', $request->placed_to);
        }

        // Sort by Date
        $query->orderBy('created_at', 'desc');

        return $query;
    }

    protected function getStats()
    {
        $today = now()->toDateString();

        return [
            'total' => Order::count(),
            'overdue' => Order::where('has_rental_items', true)
                ->where('rental_to', '<', $today)
                ->whereNotIn('status', ['Returned', 'Cancelled'])
                ->count(),
            'due_today' => Order::where('rental_to', $today)
                ->whereNotIn('status', ['Returned', 'Cancelled'])
                ->count(),
            'purchase' => Order::where('has_purchase_items', true)->count(),
        ];
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Confirmed,Shipped,Delivered,Returned,Cancelled,Order Confirmed & Shipment Created,Return Requested,Return In Progress'
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        // If moving to Returned, use the dedicated logic to handle stock
        if ($newStatus === 'Returned' && $oldStatus !== 'Returned') {
            return $this->markAsReturned($id);
        }

        $order->status = $newStatus;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Order status updated to ' . $newStatus]);
    }

    public function markAsReturned($id)
    {
        $order = Order::with('items.cloth')->findOrFail($id);

        if ($order->status === 'Returned') {
            return response()->json(['success' => false, 'message' => 'Order is already returned.'], 400);
        }

        // Only allow marking as returned if it's already delivered or in return process
        if (!in_array($order->status, ['Delivered', 'Return Requested', 'Return In Progress', 'Order Confirmed & Shipment Created', 'Confirmed'])) {
             return response()->json(['success' => false, 'message' => 'Only orders that have been shipped or delivered can be marked as returned.'], 400);
        }

        $order->status = 'Returned';
        $order->save();

        // Increment SKU for all returned items (Rental or Purchase)
        foreach ($order->items as $item) {
            $cloth = $item->cloth;
            if ($cloth) { 
                $cloth->sku = $cloth->sku + 1;
                $cloth->is_available = true; // Make available again
                $cloth->save();
            }
        }

        return response()->json(['success' => true, 'message' => 'Order marked as returned.']);
    }

    public function retryShipment($id)
    {
        $order = Order::with(['items.cloth', 'buyer', 'payments', 'shipments'])->findOrFail($id);

        if (!in_array($order->status, ['Confirmed', 'Order Confirmed & Shipment Created'])) {
            return response()->json(['success' => false, 'message' => 'Only Confirmed or Shipment Created orders can have shipments retried.'], 400);
        }

        if ($order->shipments->where('type', 'forward')->isNotEmpty()) {
            return response()->json(['success' => false, 'message' => 'Forward shipment already exists for this order.'], 400);
        }

        // Determine Payment Type
        $latestPayment = $order->payments->sortByDesc('created_at')->first();
        $paymentType = 'Prepaid';
        if ($latestPayment && $latestPayment->payment_method === 'cod') {
            $paymentType = 'COD';
        }

        try {
            $courier = new XpressbeesService();
            
            $user = $order->buyer;
            if (!$user) { 
                 return response()->json(['success' => false, 'message' => 'Buyer information missing.'], 400);
            }

            $addressParts = explode(',', $order->delivery_address);
            $city = trim($addressParts[count($addressParts)-2] ?? 'Mumbai');
            $pincode = trim($addressParts[count($addressParts)-1] ?? '400001');

            $orderLoad = [
                'order_number' => $order->id,
                'payment_method' => $paymentType,
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
                Shipment::create([
                    'order_id' => $order->id,
                    'type' => 'forward',
                    'courier_name' => 'Xpressbees',
                    'waybill_number' => $response['awb_number'],
                    'reference_id' => $response['order_id'] ?? null,
                    'tracking_url' => $response['label_url'] ?? null,
                    'label_url' => $response['label_url'] ?? null,
                    'status' => 'Booked',
                ]);
                
                $order->update(['status' => 'Order Confirmed & Shipment Created']);
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Shipment created successfully. AWB: ' . $response['awb_number']
                ]);
            } else {
                Log::error("Admin Retry Shipment Failed: " . json_encode($response));
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to create shipment with courier. Check logs.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error("Admin Retry Shipment Exception: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approveOrderReturn($id)
    {
        $order = Order::with(['buyer', 'items.cloth.user', 'shipments'])->findOrFail($id);

        if ($order->status !== 'Return Requested') {
            return response()->json(['success' => false, 'message' => 'Order is not in return requested state.'], 400);
        }

        $itemsBySeller = $order->items->groupBy(function ($item) {
            return $item->cloth->user_id;
        });

        $errors = [];
        $shipmentsCreated = 0;

        foreach ($itemsBySeller as $sellerId => $items) {
            $seller = $items->first()->cloth->user;
            
            if (!$seller) {
                $errors[] = "Seller #{$sellerId} not found.";
                continue;
            }

            try {
                $courier = new XpressbeesService();
                
                // Addresses for early return
                $buyer = $order->buyer;
                $deliveryAddress = $order->delivery_address ?? '';
                $buyerAddressParts = explode(',', $deliveryAddress);
                $buyerCity = trim($buyerAddressParts[count($buyerAddressParts)-2] ?? 'Mumbai');
                $buyerPincode = trim($buyerAddressParts[count($buyerAddressParts)-1] ?? '400001');

                $sellerAddress = $seller->address ?? '';
                $sellerAddressParts = explode(',', $sellerAddress);
                $sellerCity = trim($sellerAddressParts[count($sellerAddressParts)-2] ?? 'Mumbai');
                $sellerPincode = trim($sellerAddressParts[count($sellerAddressParts)-1] ?? '400001');

                Log::info("Attempting return shipment for Order #{$id}, Seller #{$sellerId}.");

                $orderLoad = [
                    'order_number' => $order->id . '-R' . $sellerId,
                    'payment_method' => 'Prepaid', 
                    'consignee_name' => $seller->name,
                    'consignee_phone' => $seller->phone ?? '9999999999',
                    'consignee_address' => $sellerAddress,
                    'consignee_pincode' => $sellerPincode,
                    'consignee_city' => $sellerCity,
                    'consignee_state' => 'Maharashtra',
                    'pickup_name' => $buyer->name,
                    'pickup_phone' => $buyer->phone ?? '9999999999',
                    'pickup_address' => $order->delivery_address,
                    'pickup_pincode' => $buyerPincode,
                    'pickup_city' => $buyerCity,
                    'products' => [],
                    'total_amount' => 0, 
                    'weight' => 0.5,
                ];

                foreach ($items as $item) {
                    $orderLoad['products'][] = [
                        'name' => $item->cloth->title,
                        'qty' => 1,
                        'price' => 0,
                    ];
                }

                $response = $courier->createReturnOrder($orderLoad);
                Log::info("Courier response for Order #{$id}, Seller #{$sellerId}: " . json_encode($response));

                if ($response && isset($response['awb_number'])) {
                    Shipment::create([
                        'order_id' => $order->id,
                        'type' => 'reverse',
                        'courier_name' => 'Xpressbees',
                        'waybill_number' => $response['awb_number'],
                        'reference_id' => $response['order_id'] ?? null,
                        'tracking_url' => $response['label_url'] ?? null,
                        'label_url' => $response['label_url'] ?? null,
                        'status' => 'Booked',
                    ]);
                    $shipmentsCreated++;
                } else {
                    $errors[] = "Courier API failed for seller #{$sellerId}";
                    Log::error("Courier API failed for Order #{$id}, Seller #{$sellerId}");
                }

            } catch (\Exception $e) {
                $errors[] = "Exception for seller #{$sellerId}: " . $e->getMessage();
            }
        }

        if ($shipmentsCreated > 0) {
            $order->update(['status' => 'Return In Progress']);
            
            // Send Notification to Buyer
            Notification::create([
                'user_id' => $order->buyer_id,
                'title' => 'Return Request Approved',
                'message' => "Your return request for Order #{$order->id} has been approved. A reverse pickup has been scheduled.",
                'type' => 'success',
                'icon' => 'bi-truck'
            ]);

            // Send Notification to Seller(s)
            $uniqueSellers = $order->items->map(fn($item) => $item->cloth ? $item->cloth->user_id : null)->unique()->filter();
            foreach ($uniqueSellers as $sId) {
                Notification::create([
                    'user_id' => $sId,
                    'title' => 'Return Scheduled',
                    'message' => "Order #{$order->id} is being returned. A reverse shipment has been scheduled.",
                    'type' => 'info',
                    'icon' => 'bi-arrow-left-right'
                ]);
            }

            return response()->json([
                'success' => true, 
                'message' => "Return approved. {$shipmentsCreated} reverse shipments created.",
                'errors' => $errors
            ]);
        }

        return response()->json([
            'success' => false, 
            'message' => 'Failed to create any return shipments.',
            'errors' => $errors
        ], 500);
    }

    public function rejectOrderReturn(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        
        $order = Order::findOrFail($id);
        
        if ($order->status !== 'Return Requested') {
            return response()->json(['success' => false, 'message' => 'Order is not in return requested state.'], 400);
        }

        $order->update([
            'status' => 'Delivered', // Revert back to delivered
            'admin_rejection_reason' => $request->reason,
            'return_reason' => null // Clear reason so it shows in standard dashboards again
        ]);

        // Notify Buyer
        Notification::create([
            'user_id' => $order->buyer_id,
            'title' => 'Return Request Rejected',
            'message' => "Your return request for Order #{$order->id} was rejected. Reason: {$request->reason}",
            'type' => 'danger',
            'icon' => 'bi-x-circle'
        ]);

        return response()->json(['success' => true, 'message' => 'Return request rejected.']);
    }
}
