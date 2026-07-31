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

    public function showDetails($id)
    {
        $order = Order::with(['buyer', 'items.cloth.images', 'items.cloth.user', 'shipments', 'payments', 'invoices'])->findOrFail($id);

        $data = [
            'id' => $order->id,
            'status' => $order->status,
            'total_amount' => $order->total_amount,
            'security_amount' => $order->security_amount ?? 0,
            'delivery_address' => $order->delivery_address,
            'delivery_city' => $order->delivery_city,
            'delivery_state' => $order->delivery_state,
            'delivery_pincode' => $order->delivery_pincode,
            'formatted_date' => $order->created_at->setTimezone('Asia/Kolkata')->format('d M Y, h:i A'),
            'buyer_name' => $order->buyer ? $order->buyer->name : 'Unknown',
            'buyer_email' => $order->buyer ? $order->buyer->email : 'N/A',
            'buyer_phone' => $order->buyer ? $order->buyer->phone : 'N/A',
            'shipment_error' => $order->shipment_error,
        ];
        
        if ($order->has_rental_items && $order->rental_from && $order->rental_to) {
            $data['rental_period'] = \Carbon\Carbon::parse($order->rental_from)->format('d/m/Y') . ' – ' . \Carbon\Carbon::parse($order->rental_to)->format('d/m/Y');
            $data['return_date_formatted'] = ($order->return_date ? \Carbon\Carbon::parse($order->return_date) : \Carbon\Carbon::parse($order->rental_to)->addDay())->format('d/m/Y');
        } else {
            $data['rental_period'] = 'N/A';
            $data['return_date_formatted'] = 'N/A';
        }

        $orderType = $order->has_rental_items && $order->has_purchase_items ? 'Mixed' : ($order->has_rental_items ? 'Rental' : 'Purchase');
        $data['order_type'] = $orderType;

        $itemsList = [];
        foreach ($order->items as $item) {
            $imagePath = $item->cloth && $item->cloth->images->isNotEmpty() ? asset('storage/' . $item->cloth->images->first()->image_path) : asset('images/placeholder.jpg');
            
            $sellerEmail = $item->cloth && $item->cloth->user ? $item->cloth->user->email : 'N/A';

            $itemsList[] = [
                'id' => $item->id,
                'title' => $item->cloth ? $item->cloth->title : 'Item',
                'purchase_type' => $item->purchase_type,
                'price' => $item->price,
                'security_deposit' => $item->cloth->security_deposit ?? 0,
                'image_url' => $imagePath,
                'seller_name' => $item->seller_name ?? 'Unknown',
                'seller_email' => $sellerEmail,
                'seller_phone' => $item->seller_phone ?? 'N/A',
                'seller_address' => $item->seller_address ?? 'N/A',
                'seller_city' => $item->seller_city ?? '',
                'seller_state' => $item->seller_state ?? '',
                'seller_pincode' => $item->seller_pincode ?? '',
            ];
        }
        $data['items_list'] = $itemsList;

        $data['shipments_list'] = $order->shipments->map(function($shipment) {
            return [
                'type' => ucfirst($shipment->type),
                'courier_name' => $shipment->courier_name,
                'waybill_number' => $shipment->waybill_number,
                'status' => $shipment->status,
                'tracking_url' => $shipment->tracking_url,
                'date' => $shipment->created_at->setTimezone('Asia/Kolkata')->format('d/m/Y h:i A')
            ];
        });

        $data['payments_list'] = $order->payments->map(function($payment) {
            return [
                'id' => $payment->id,
                'method' => strtoupper($payment->payment_method),
                'amount' => $payment->amount,
                'status' => $payment->payment_status,
                'date' => $payment->created_at->setTimezone('Asia/Kolkata')->format('d/m/Y h:i A')
            ];
        });

        return response()->json(['success' => true, 'order' => $data]);
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
                    ->where(function($q) use ($today) {
                        $q->whereNotNull('return_date')
                          ->where('return_date', '<', $today)
                          ->orWhere(function($sq) use ($today) {
                              $sq->whereNull('return_date')
                                 ->where('rental_to', '<', $today);
                          });
                    })
                    ->whereNotIn('status', ['Returned', 'Cancelled']);
            } elseif ($request->return_state === 'due_soon') {
                $threeDaysLater = now()->addDays(3)->toDateString();
                $query->where('has_rental_items', true)
                    ->where(function($q) use ($today, $threeDaysLater) {
                        $q->whereNotNull('return_date')
                          ->whereBetween('return_date', [$today, $threeDaysLater])
                          ->orWhere(function($sq) use ($today, $threeDaysLater) {
                              $sq->whereNull('return_date')
                                 ->whereBetween('rental_to', [$today, $threeDaysLater]);
                          });
                    })
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
                ->where(function($q) use ($today) {
                    $q->whereNotNull('return_date')
                      ->where('return_date', '<', $today)
                      ->orWhere(function($sq) use ($today) {
                          $sq->whereNull('return_date')
                             ->where('rental_to', '<', $today);
                      });
                })
                ->whereNotIn('status', ['Returned', 'Cancelled'])
                ->count(),
            'due_today' => Order::where(function($q) use ($today) {
                    $q->where('return_date', $today)
                      ->orWhere(function($sq) use ($today) {
                          $sq->whereNull('return_date')
                             ->where('rental_to', $today);
                      });
                })
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

        // If moving to Cancelled, we must also restore stock and availability
        if ($newStatus === 'Cancelled' && $oldStatus !== 'Cancelled') {
            return $this->cancelOrder($id);
        }

        $order->status = $newStatus;
        if ($newStatus === 'Delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
        }
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

        $availabilityService = app(\App\Services\AvailabilityService::class);

        // Increment SKU for all returned items (Rental or Purchase)
        foreach ($order->items as $item) {
            $cloth = $item->cloth;
            if ($cloth) { 
                $cloth->sku = $cloth->sku + 1;
                $cloth->is_available = true; // Make available again
                $cloth->save();

                // If it was a rental item and has availability blocks, restore them
                if ($item->purchase_type !== 'buy') {
                    $availabilityService->restoreAvailabilityForOrder($cloth->id, $order->id);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Order marked as returned.']);
    }

    public function cancelOrder($id)
    {
        $order = Order::with('items.cloth')->findOrFail($id);

        if ($order->status === 'Cancelled') {
            return response()->json(['success' => false, 'message' => 'Order is already cancelled.'], 400);
        }

        $order->status = 'Cancelled';
        $order->save();

        $availabilityService = app(\App\Services\AvailabilityService::class);

        // Increment SKU for all cancelled items and restore blocks
        foreach ($order->items as $item) {
            $cloth = $item->cloth;
            if ($cloth) { 
                $cloth->sku = $cloth->sku + 1;
                $cloth->is_available = true; // Make available again
                $cloth->save();

                if ($item->purchase_type !== 'buy') {
                    $availabilityService->restoreAvailabilityForOrder($cloth->id, $order->id);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Order marked as cancelled and stock restored.']);
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
            $updateData = ['status' => 'Return In Progress'];
            
            // Check if it's an Early Return
            if ($order->return_reason === 'Early Return') {
                $todayString = now()->toDateString();
                $updateData['rental_to'] = $todayString;
                $updateData['return_date'] = $todayString;
                
                // Update availability blocks immediately
                $availabilityService = app(\App\Services\AvailabilityService::class);
                foreach ($order->items as $item) {
                     if ($item->cloth && $item->purchase_type !== 'buy') {
                        $availabilityService->updateAvailabilityForEarlyReturn($item->cloth->id, $order->id, $todayString);
                     }
                }
            }

            $order->update($updateData);
            
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

    public function retryShipment($id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->status !== 'Order Confirmed & Shipment Failed' && $order->status !== 'Order Confirmed & Shipment Created') {
            return response()->json(['success' => false, 'message' => 'Shipments can only be retried for confirmed orders.'], 400);
        }

        $paymentType = 'prepaid'; 
        if ($order->payments()->where('payment_method', 'cod')->exists()) {
            $paymentType = 'cod';
        }

        $shipmentService = new \App\Services\OrderShipmentService();
        $result = $shipmentService->createShipments($order, $order->buyer, $paymentType);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Shipments successfully created for all sellers.', 'status' => $order->fresh()->status]);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to create shipments: ' . $result['error'], 'status' => $order->fresh()->status], 500);
        }
    }
}
