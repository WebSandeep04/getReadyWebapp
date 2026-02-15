<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cloth;
use App\Models\User;
use App\Models\Notification;
use App\Models\FrontendSetting;
use App\Models\Order;
use App\Models\Category;
use App\Models\Brand;
use App\Models\FabricType;
use App\Models\Color;
use App\Models\Size;
use App\Models\BottomType;
use App\Models\BodyTypeFit;
use App\Models\GarmentCondition;
use Illuminate\Support\Carbon;
use App\Models\Shipment;
use App\Models\Payment;
use App\Services\XpressbeesService;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.dashboard');
    }

    public function clothApproval()
    {
        $showFilters = false;
        return view('admin.screens.cloth_approval', compact('showFilters'));
    }

    public function orders(Request $request)
    {
        $orders = $this->buildOrdersQuery($request)->paginate(20)->appends($request->query());
        $stats = $this->getOrderStats();
        $filters = $request->all();
        $statuses = ['Pending', 'Confirmed', 'Delivered', 'Returned', 'Cancelled'];
        $paymentStatuses = ['Paid', 'Pending', 'Failed', 'Refunded', 'Partially Refunded', 'unpaid'];

        return view('admin.screens.orders', compact('orders', 'stats', 'filters', 'statuses', 'paymentStatuses'));
    }

    public function ordersData(Request $request)
    {
        $orders = $this->buildOrdersQuery($request)->paginate(20)->appends($request->query());

        return response()->json([
            'table_html' => view('admin.components.orders-rows', compact('orders'))->render(),
            'pagination_html' => view('admin.components.orders-pagination', compact('orders'))->render(),
            'stats' => $this->getOrderStats(),
        ]);
    }

    protected function buildOrdersQuery(Request $request)
    {
        $today = Carbon::today();

        $query = Order::with([
            'buyer',
            'payments' => function ($paymentQuery) {
                $paymentQuery->latest();
            },
            'shipments'
        ]);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            if ($type === 'rental') {
                $query->where('has_rental_items', true)->where('has_purchase_items', false);
            } elseif ($type === 'purchase') {
                $query->where('has_purchase_items', true)->where('has_rental_items', false);
            } elseif ($type === 'mixed') {
                $query->where('has_rental_items', true)->where('has_purchase_items', true);
            }
        }

        if ($returnState = $request->get('return_state')) {
            if ($returnState === 'overdue') {
                $query->where('has_rental_items', true)
                    ->whereNotNull('rental_to')
                    ->whereDate('rental_to', '<', $today)
                    ->whereNotIn('status', ['Returned', 'Cancelled']);
            } elseif ($returnState === 'due_soon') {
                $query->where('has_rental_items', true)
                    ->whereNotNull('rental_to')
                    ->whereBetween('rental_to', [$today, $today->copy()->addDays(7)]);
            } elseif ($returnState === 'completed') {
                $query->where('status', 'Returned');
            }
        }

        if ($paymentStatus = $request->get('payment_status')) {
            if ($paymentStatus === 'unpaid') {
                $query->whereDoesntHave('payments');
            } else {
                $query->whereHas('payments', function ($q) use ($paymentStatus) {
                    $q->where('payment_status', $paymentStatus);
                });
            }
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('total_amount', 'like', '%' . $search . '%')
                    ->orWhereHas('buyer', function ($buyerQuery) use ($search) {
                        $buyerQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($from = $request->get('placed_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('placed_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $query->orderByRaw("
            CASE
                WHEN has_rental_items = 1 AND rental_to IS NOT NULL AND rental_to < ? THEN 0
                WHEN has_rental_items = 1 THEN 1
                ELSE 2
            END
        ", [$today])
        ->orderByRaw('CASE WHEN rental_to IS NULL THEN 1 ELSE 0 END')
        ->orderBy('rental_to', 'asc')
        ->orderBy('created_at', 'desc');

        return $query;
    }

    protected function getOrderStats(): array
    {
        $today = Carbon::today();

        return [
            'total' => Order::count(),
            'overdue' => Order::where('has_rental_items', true)
                ->whereNotNull('rental_to')
                ->whereDate('rental_to', '<', $today)
                ->whereNotIn('status', ['Returned', 'Cancelled'])
                ->count(),
            'due_today' => Order::where('has_rental_items', true)
                ->whereNotNull('rental_to')
                ->whereDate('rental_to', '=', $today)
                ->count(),
            'purchase' => Order::where('has_purchase_items', true)->count(),
        ];
    }

    // Frontend Management
    public function frontend()
    {
        $sections = [
            'general' => 'General Settings',
            'logo' => 'Logo Settings',
            'hero' => 'Hero Section',
            'about' => 'About Section',
            'footer' => 'Footer Section',
            'social' => 'Social Media'
        ];
        
        $settings = FrontendSetting::orderBy('section')->orderBy('label')->get();
        return view('admin.screens.frontend', compact('settings', 'sections'));
    }

    // Update frontend setting (AJAX)
    public function updateFrontendSetting(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable|string',
            'type' => 'required|string'
        ]);

        $setting = FrontendSetting::where('key', $request->key)->first();
        
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Setting not found']);
        }

        // Handle file upload for image type
        if ($request->type === 'image' && $request->hasFile('value')) {
            $file = $request->file('value');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $setting->value = 'images/' . $filename;
        } else {
            $setting->value = $request->value;
        }

        $setting->save();

        return response()->json([
            'success' => true, 
            'message' => 'Setting updated successfully',
            'value' => $setting->value
        ]);
    }

    // Get frontend settings by section (AJAX)
    public function getFrontendSettings($section)
    {
        $settings = FrontendSetting::where('section', $section)->get();
        return response()->json($settings);
    }

    // Fetch all clothes (AJAX)
    public function fetchClothes(Request $request)
    {
        $query = Cloth::with([
            'images', 
            'user', 
            'category', 
            'brand',
            'fabric', 
            'color', 
            'size', 
            'bottomType', 
            'fitType',
            'condition'
        ]);
        
        // Apply status filter if provided
        if ($request->has('status')) {
            $status = $request->get('status');
            switch ($status) {
                case 'pending':
                    $query->where('is_approved', null);
                    break;
                case 'approved':
                    $query->where('is_approved', 1);
                    break;
                case 'rejected':
                    $query->where('is_approved', -1);
                    break;
                case 're-approval':
                    $query->where('is_approved', null)
                          ->where('resubmission_count', '>', 0); // Items that have been resubmitted
                    break;
            }
        }
        
        // Limit for dashboard table
        $clothesLimited = $query->latest()->limit(5)->get();
        
        // Convert objects to display format efficiently
        $formattedClothes = $clothesLimited->map(function ($cloth) {
            // Map relationships to flat names
            $cloth->category_name = $cloth->category ? $cloth->category->name : 'Unknown';
            $cloth->brand_name = $cloth->brand ? $cloth->brand->name : 'Unknown';
            $cloth->fabric_name = $cloth->fabric ? $cloth->fabric->name : 'Unknown';
            $cloth->color_name = $cloth->color ? $cloth->color->name : 'Unknown';
            $cloth->size_name = $cloth->size ? $cloth->size->name : 'Unknown';
            $cloth->bottom_type_name = $cloth->bottomType ? $cloth->bottomType->name : 'Unknown';
            $cloth->fit_type_name = $cloth->fitType ? $cloth->fitType->name : 'Unknown';
            $cloth->condition_name = $cloth->condition ? $cloth->condition->name : 'Unknown';
            
            // Format timestamps
            $cloth->created_at_formatted = $cloth->created_at ? $cloth->created_at->toISOString() : null;
            $cloth->updated_at_formatted = $cloth->updated_at ? $cloth->updated_at->toISOString() : null;
            
            // Convert to array and remove relationship objects to avoid [object Object] in JS
            $data = $cloth->toArray();
            
            // Explicitly unset relationships to ensure flattened string versions are used
            $relationsToUnset = [
                'category', 'brand', 'fabric', 'color', 'size', 
                'bottomType', 'bottom_type', 'fitType', 'fit_type', 'condition'
            ];
            
            foreach ($relationsToUnset as $rel) {
                if (isset($data[$rel]) && (is_array($data[$rel]) || is_object($data[$rel]))) {
                    unset($data[$rel]);
                }
            }

            // Re-assign flattened names
            $data['category'] = $cloth->category_name;
            $data['brand'] = $cloth->brand_name;
            $data['fabric'] = $cloth->fabric_name;
            $data['color'] = $cloth->color_name;
            $data['size'] = $cloth->size_name;
            $data['bottom_type'] = $cloth->bottom_type_name;
            $data['fit_type'] = $cloth->fit_type_name;
            $data['condition'] = $cloth->condition_name;
            $data['user_name'] = $cloth->user ? $cloth->user->name : 'Unknown';
            
            // Full Pricing Breakdown for Admin (using 4 days as standard)
            $pricing = (new \App\Services\PriceCalculatorService())->calculate($cloth, 4);
            $data['display_rent_price'] = $pricing['total_buyer_pay'];
            $data['seller_rent'] = $pricing['net_seller_payout'];
            $data['base_rent'] = $pricing['base_rent'];
            
            // Intermediate prices for transparency
            $data['buyer_see_rent'] = $cloth->display_rent_price;
            $data['seller_see_rent'] = $cloth->seller_rent;
            
            return $data;
        });

        // Global Stats
        $stats = [
            'total' => Cloth::count(),
            'approved' => Cloth::where('is_approved', 1)->count(),
            'pending' => Cloth::where('is_approved', null)->where('resubmission_count', 0)->count(),
            'reapproval' => Cloth::where('is_approved', null)->where('resubmission_count', '>', 0)->count(),
            'rejected' => Cloth::where('is_approved', -1)->count(),
            'total_rent' => Cloth::sum('rent_price'),
            'total_security' => Cloth::sum('security_deposit'),
        ];
        
        return response()->json([
            'clothes' => $formattedClothes,
            'stats' => $stats
        ]);
    }

    public function fetchOrders(Request $request)
    {
        $query = Order::with(['buyer', 'items', 'shipments']);

        if ($request->has('status') && $request->status) {
            $status = $request->status;
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Limit for dashboard table
        $orders = $query->latest()->limit(5)->get();

        $formattedOrders = $orders->map(function ($order) {
            $createdAt = $order->created_at;
            if (!($createdAt instanceof \Carbon\Carbon)) {
                $createdAt = \Carbon\Carbon::parse($createdAt);
            }
            
            $data = $order->toArray();
            $data['user_name'] = $order->buyer ? $order->buyer->name : 'Guest';
            $data['items_count'] = $order->items->count();
            $data['created_at_formatted'] = $createdAt ? $createdAt->format('d M Y, h:i A') : '-';
            
            // Flags for UI buttons
            $data['shipment_missing'] = !$order->shipments->where('type', 'forward')->first() && $order->status === 'Confirmed';
            $data['is_rental'] = (bool) $order->has_rental_items;
            
            return $data;
        });

        // Global Stats
        $stats = [
            'processing' => Order::where('status', 'Processing')->count(),
            'shipped' => Order::where('status', 'Shipped')->count(),
            'delivered' => Order::where('status', 'Delivered')->count(),
            'returned' => Order::where('status', 'Returned')->count(),
        ];

        return response()->json([
            'orders' => $formattedOrders,
            'stats' => $stats
        ]);
    }

    public function fetchPayments(Request $request)
    {
        $query = Payment::with(['order.buyer', 'order.items']);

        if ($request->has('status') && $request->status) {
            $status = $request->status;
            if ($status !== 'all') {
                $query->where('payment_status', $status);
            }
        }

        // Limit for dashboard table
        $payments = $query->latest()->limit(5)->get();

        $formattedPayments = $payments->map(function ($payment) {
            $data = $payment->toArray();
            $data['payer_name'] = $payment->order && $payment->order->buyer ? $payment->order->buyer->name : 'Unknown';
            $data['order_id'] = $payment->order_id;
            
            // Financial Breakdown for the Order
            $baseRent = 0;
            $buyerComm = 0;
            $sellerComm = 0;
            $rentGst = 0;
            $buyerCommGst = 0;
            $sellerCommGst = 0;
            $sellerNet = 0;
            
            if ($payment->order && $payment->order->items) {
                foreach ($payment->order->items as $item) {
                    $baseRent += (float) ($item->base_rent ?? 0);
                    $buyerComm += (float) ($item->buyer_commission ?? 0);
                    $sellerComm += (float) ($item->seller_commission ?? 0);
                    $rentGst += (float) ($item->rent_gst ?? 0);
                    
                    // Specific GST parts from stored columns or fallback
                    $bCommGst = (float) ($item->buyer_commission_gst ?? (($item->buyer_commission ?? 0) * 0.18));
                    $sCommGst = (float) ($item->seller_commission_gst ?? (($item->seller_commission ?? 0) * 0.18));
                    
                    $buyerCommGst += $bCommGst;
                    $sellerCommGst += $sCommGst;
                    
                    // Net for Seller calculation
                    $sellerNet += ($item->base_rent ?? 0) + ($item->rent_gst ?? 0) - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
                }
            }
            
            $data['base_rent_total'] = round($baseRent, 2);
            $data['buyer_comm_total'] = round($buyerComm, 2);
            $data['seller_comm_total'] = round($sellerComm, 2);
            $data['rent_gst_total'] = round($rentGst, 2);
            $data['buyer_comm_gst_total'] = round($buyerCommGst, 2);
            $data['seller_comm_gst_total'] = round($sellerCommGst, 2);
            $data['gst_total'] = round($rentGst + $buyerCommGst + $sellerCommGst, 2);
            $data['seller_net_payout'] = round($sellerNet, 2);
            
            $data['security_amount'] = $payment->order ? ($payment->order->security_amount ?? 0) : 0;
            $data['order_status'] = $payment->order ? $payment->order->status : 'Draft';
            
            $data['paid_at_formatted'] = $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : 
                                         ($payment->created_at ? $payment->created_at->format('d M Y, h:i A') : '-');
            return $data;
        });

        // Global Stats updated with pricing logic
        $paidStatus = ['Paid', 'Success', 'paid', 'success'];
        $failedStatus = ['Failed', 'Cancelled', 'failed', 'cancelled'];
        $refundStatus = ['Refunded', 'Partially Refunded', 'refunded'];

        $paidOrderIds = Payment::whereIn('payment_status', $paidStatus)->pluck('order_id');
        
        $orderItems = \App\Models\OrderItem::whereIn('order_id', $paidOrderIds)->get();
        
        // Commissions as base amounts
        $buyerCommTotal = $orderItems->sum('buyer_commission');
        $sellerCommTotal = $orderItems->sum('seller_commission');
        $totalCommission = $buyerCommTotal + $sellerCommTotal;
        
        // GST Breakup using new columns
        $rentGstTotal = $orderItems->sum('rent_gst');
        $buyerCommGstTotal = $orderItems->sum('buyer_commission_gst');
        $sellerCommGstTotal = $orderItems->sum('seller_commission_gst');
        $totalGst = $rentGstTotal + $buyerCommGstTotal + $sellerCommGstTotal;
        
        $sellerPayouts = $orderItems->sum(function($item) {
            $sCommGst = (float)($item->seller_commission_gst ?? (($item->seller_commission ?? 0) * 0.18));
            return ($item->base_rent ?? 0) + ($item->rent_gst ?? 0) - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
        });

        $stats = [
            'paid_count' => Payment::whereIn('payment_status', $paidStatus)->count(),
            'paid_amount' => Payment::whereIn('payment_status', $paidStatus)->sum('amount'),
            
            'pending_count' => Payment::where('payment_status', 'Pending')->count(),
            'pending_amount' => Payment::where('payment_status', 'Pending')->sum('amount'),
            
            'failed_count' => Payment::whereIn('payment_status', $failedStatus)->count(),
            'failed_amount' => Payment::whereIn('payment_status', $failedStatus)->sum('amount'),
            
            'refund_count' => Payment::whereIn('payment_status', $refundStatus)->count(),
            'refund_amount' => Payment::whereIn('payment_status', $refundStatus)->sum('amount'),

            // New detailed stats
            'total_commission' => round($totalCommission, 2),
            'total_gst' => round($totalGst, 2),
            'total_seller_payouts' => round($sellerPayouts, 2),
            'total_rent_gst' => round($rentGstTotal, 2),
            'total_buyer_comm_gst' => round($buyerCommGstTotal, 2),
            'total_seller_comm_gst' => round($sellerCommGstTotal, 2),
        ];

        return response()->json([
            'payments' => $formattedPayments,
            'stats' => $stats
        ]);
    }

    // Approve a cloth (AJAX)
    public function approveCloth($id)
    {
        $cloth = Cloth::with('user')->findOrFail($id);
        
        // Prevent approving rejected items
        if ($cloth->is_approved === -1) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot approve a rejected item. User must resubmit it first.'
            ], 400);
        }

        $cloth->is_approved = 1; // Use integer 1 instead of true
        $cloth->save();

        // Send notification to the user
        if ($cloth->user) {
            Notification::create([
                'user_id' => $cloth->user->id,
                'title' => 'Item Approved',
                'message' => "Your item '{$cloth->title}' has been approved and is now live on our platform!",
                'type' => 'success',
                'icon' => 'bi-check-circle',
                'data' => [
                    'cloth_id' => $cloth->id,
                    'cloth_title' => $cloth->title
                ]
            ]);
        }

        return response()->json(['success' => true]);
    }

    // Reject a cloth (AJAX)
    public function rejectCloth(Request $request, $id)
    {
        $request->validate([
            'reject_reason' => 'required|string|max:500'
        ]);

        $cloth = Cloth::with('user')->findOrFail($id);
        
        // Prevent rejecting approved items
        if ($cloth->is_approved === 1) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot reject an approved item. Please approve it first.'
            ], 400);
        }
        
        // Allow rejecting pending, rejected, and re-approval items
        $cloth->is_approved = -1; // Use integer -1 for rejected
        $cloth->save();

        // Send notification to the user with rejection reason
        if ($cloth->user) {
            Notification::create([
                'user_id' => $cloth->user->id,
                'title' => 'Item Rejected',
                'message' => "Your item '{$cloth->title}' has been rejected. Reason: {$request->reject_reason}. Please review and resubmit.",
                'type' => 'warning',
                'icon' => 'bi-exclamation-triangle',
                'data' => [
                    'cloth_id' => $cloth->id,
                    'cloth_title' => $cloth->title,
                    'reject_reason' => $request->reject_reason
                ]
            ]);
        }

        return response()->json(['success' => true]);
    }

    // Get all rejection reasons for a cloth (for Admin view)
    public function getRejectionReason($id)
    {
        $notifications = Notification::where('type', 'warning')
            ->whereRaw("JSON_EXTRACT(data, '$.cloth_id') = ?", [$id])
            ->whereRaw("JSON_EXTRACT(data, '$.reject_reason') IS NOT NULL")
            ->orderByDesc('created_at')
            ->get();

        if ($notifications->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No rejection reasons found for this item.'
            ], 404);
        }

        $reasons = $notifications->map(function ($n) {
            return [
                'reason' => $n->data['reject_reason'] ?? null,
                'rejected_at' => $n->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'reasons' => $reasons,
        ]);
    }

    // Dashboard stats for AJAX
    public function dashboardStats()
    {
        $total = Cloth::count();
        $approved = Cloth::where('is_approved', 1)->count(); // Use integer 1
        $pending = Cloth::where('is_approved', null)->count(); // Use null for pending
        $rejected = Cloth::where('is_approved', -1)->count(); // Use integer -1 for rejected

        return response()->json([
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected
        ]);
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
            if (!$user) { // Should not happen for valid orders but safety check
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

    public function refundOrderPayment($id)
    {
        $order = Order::with('payments')->findOrFail($id);

        if ($order->has_rental_items) {
            return response()->json(['success' => false, 'message' => 'Rental and Mixed orders must be managed through the Security Dashboard to handle security deposits correctly.'], 400);
        }
        
        $paidPayments = $order->payments()->whereIn('payment_status', ['Paid', 'Success', 'paid', 'success'])->get();
        
        if ($paidPayments->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No paid payments found for this order.'], 400);
        }

        foreach ($paidPayments as $payment) {
            $payment->payment_status = 'Refunded';
            $payment->save();
        }

        return response()->json(['success' => true, 'message' => 'Payments marked as refunded successfully.']);
    }

    /**
     * Handle full refund (Rent + Security) for an order that was returned due to an issue.
     */
    public function processIssueRefund($id)
    {
        $order = Order::with('payments')->findOrFail($id);

        if ($order->status !== 'Returned') {
            return response()->json(['success' => false, 'message' => 'Order must be in Returned status to process the final refund.'], 400);
        }

        if (!$order->return_reason) {
            return response()->json(['success' => false, 'message' => 'This order was not returned via a reported issue. Please use the standard security dashboard.'], 400);
        }

        // 1. Mark all payments as Refunded
        $payments = Payment::where('order_id', $order->id)
            ->whereIn('payment_status', ['Paid', 'Success', 'paid', 'success'])
            ->get();
            
        if ($payments->isEmpty()) {
             return response()->json(['success' => false, 'message' => 'No eligible payments found to refund.'], 400);
        }

        foreach ($payments as $payment) {
            $payment->update(['payment_status' => 'Refunded']);
        }

        // 2. Mark security as returned
        $order->update([
            'is_security_returned' => true,
            'security_returned_at' => now(),
        ]);

        // 3. Notify Buyer
        Notification::create([
            'user_id' => $order->buyer_id,
            'title' => 'Full Refund Processed',
            'message' => "Your full refund for Order #{$order->id} (including security deposit) has been processed successfully.",
            'type' => 'success',
            'icon' => 'bi-cash-stack'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Full refund processed and security marked as returned.'
        ]);
    }
}
