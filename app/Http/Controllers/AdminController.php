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
        $orders = $this->buildOrdersQuery($request)->paginate(15)->appends($request->query());
        $stats = $this->getOrderStats();
        $filters = $request->all();
        $statuses = ['Pending', 'Confirmed', 'Delivered', 'Returned', 'Cancelled'];
        $paymentStatuses = ['Paid', 'Pending', 'Failed', 'unpaid'];

        return view('admin.screens.orders', compact('orders', 'stats', 'filters', 'statuses', 'paymentStatuses'));
    }

    public function ordersData(Request $request)
    {
        $orders = $this->buildOrdersQuery($request)->paginate(15)->appends($request->query());

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
            'shipment'
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
        
        $clothes = $query->latest()->get();
        
        // Convert objects to display format efficiently
        $formattedClothes = $clothes->map(function ($cloth) {
            // Clone only needed properties or rely on serialization, 
            // but we need to overwrite the IDs with Names as per current frontend expectation
            
            // Map relationships to flat names
            $cloth->category_name = $cloth->category ? $cloth->category->name : 'Unknown';
            $cloth->brand_name = $cloth->brand ? $cloth->brand->name : 'Unknown';
            $cloth->fabric_name = $cloth->fabric ? $cloth->fabric->name : 'Unknown';
            $cloth->color_name = $cloth->color ? $cloth->color->name : 'Unknown';
            $cloth->size_name = $cloth->size ? $cloth->size->name : 'Unknown';
            $cloth->bottom_type_name = $cloth->bottomType ? $cloth->bottomType->name : 'Unknown';
            $cloth->fit_type_name = $cloth->fitType ? $cloth->fitType->name : 'Unknown';
            $cloth->condition_name = $cloth->condition ? $cloth->condition->name : 'Unknown';
            
            // For frontend compatibility with existing keys if needed (but we really should change them)
            $cloth->category = $cloth->category_name;
            $cloth->brand = $cloth->brand_name;
            $cloth->fabric = $cloth->fabric_name;
            $cloth->color = $cloth->color_name;
            $cloth->size = $cloth->size_name;
            $cloth->bottom_type = $cloth->bottom_type_name;
            $cloth->fit_type = $cloth->fit_type_name;
            $cloth->condition = $cloth->condition_name;
            
            // Format timestamps
            $cloth->created_at_formatted = $cloth->created_at ? $cloth->created_at->toISOString() : null;
            $cloth->updated_at_formatted = $cloth->updated_at ? $cloth->updated_at->toISOString() : null;
            
            // Ensure numerics
            $cloth->resubmission_count = $cloth->resubmission_count ?? 0;

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

            // Re-assign flattened names to original keys for JS compatibility
            $data['category'] = $cloth->category_name;
            $data['brand'] = $cloth->brand_name;
            $data['fabric'] = $cloth->fabric_name;
            $data['color'] = $cloth->color_name;
            $data['size'] = $cloth->size_name;
            $data['bottom_type'] = $cloth->bottom_type_name;
            $data['fit_type'] = $cloth->fit_type_name;
            $data['condition'] = $cloth->condition_name;
            
            return $data;
        });
        
        return response()->json($formattedClothes);
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

        $order->status = 'Returned';
        $order->save();

        // Increment SKU for rented items
        foreach ($order->items as $item) {
            $cloth = $item->cloth;
            if ($cloth && $item->price != $cloth->purchase_value) { 
                $cloth->sku = $cloth->sku + 1;
                $cloth->is_available = true; // Make available again
                $cloth->save();
            }
        }

        return response()->json(['success' => true, 'message' => 'Order marked as returned.']);
    }

    public function retryShipment($id)
    {
        $order = Order::with(['items.cloth', 'buyer', 'payments', 'shipment'])->findOrFail($id);

        if ($order->shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment already exists for this order.'], 400);
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
}
