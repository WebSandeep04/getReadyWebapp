<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cloth;
use App\Models\User;
use App\Models\Notification;
use App\Models\FrontendSetting;
use App\Models\Order;
use Illuminate\Support\Carbon;

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
            }
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
        $query = Cloth::with('images', 'user');
        
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
                    $query->where('is_approved', 0);
                    break;
                case 're-approval':
                    $query->where('is_approved', null)
                          ->where('resubmission_count', '>', 0); // Items that have been resubmitted
                    break;
            }
        }
        
        $clothes = $query->get();
        
        // Convert IDs to names for display
        foreach ($clothes as $cloth) {
            if ($cloth->category) {
                $category = \App\Models\Category::find($cloth->category);
                $cloth->category = $category ? $category->name : 'Unknown';
            }
            
            if ($cloth->fabric) {
                $fabric = \App\Models\FabricType::find($cloth->fabric);
                $cloth->fabric = $fabric ? $fabric->name : 'Unknown';
            }
            
            if ($cloth->color) {
                $color = \App\Models\Color::find($cloth->color);
                $cloth->color = $color ? $color->name : 'Unknown';
            }
            
            if ($cloth->size) {
                $size = \App\Models\Size::find($cloth->size);
                $cloth->size = $size ? $size->name : 'Unknown';
            }
            
            if ($cloth->bottom_type) {
                $bottomType = \App\Models\BottomType::find($cloth->bottom_type);
                $cloth->bottom_type = $bottomType ? $bottomType->name : 'Unknown';
            }
            
            if ($cloth->fit_type) {
                $bodyTypeFit = \App\Models\BodyTypeFit::find($cloth->fit_type);
                $cloth->fit_type = $bodyTypeFit ? $bodyTypeFit->name : 'Unknown';
            }
            
            // Add timestamps for status detection
            $cloth->created_at = $cloth->created_at->toISOString();
            $cloth->updated_at = $cloth->updated_at->toISOString();
            
            // Add resubmission count for frontend detection
            $cloth->resubmission_count = $cloth->resubmission_count ?? 0;
        }
        
        return response()->json($clothes);
    }

    // Approve a cloth (AJAX)
    public function approveCloth($id)
    {
        $cloth = Cloth::with('user')->findOrFail($id);
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
        $cloth->is_approved = 0; // Use integer 0 instead of false
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
        $rejected = Cloth::where('is_approved', 0)->count(); // Use integer 0

        return response()->json([
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected
        ]);
    }
}
