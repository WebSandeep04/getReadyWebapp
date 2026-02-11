<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        $stats = $this->getStats();
        
        // Initial load can pass empty orders or pre-filled. 
        // Let's pass empty and let AJAX load it, OR pass initial like Orders page.
        // The Orders page passes initial $orders. Let's do that for consistency, 
        // but since we are moving to AJAX, we can reuse the logic.
        
        // For blade rendering, we just need the structure.
        return view('admin.screens.security', compact('stats'));
    }

    public function fetchData(Request $request)
    {
        $query = Order::with('buyer', 'payments')
            ->where('has_rental_items', true)
            ->whereNotNull('security_amount')
            ->where('security_amount', '>', 0);
        
        // Filter by return status
        if ($request->has('status') && $request->status) {
            $status = $request->status;
            if ($status === 'returned') {
                 // Pending Return
                 $query->where('status', 'Returned')->where('is_security_returned', false);
            } elseif ($status === 'held') {
                 // Held
                 $query->where('status', '!=', 'Returned')->where('is_security_returned', false);
            } elseif ($status === 'completed') {
                 // Security Returned
                 $query->where('is_security_returned', true);
            }
        }
        
        $orders = $query->latest()->paginate(20)->appends($request->query());

        return response()->json([
            'table_html' => view('admin.components.security-rows', compact('orders'))->render(),
            'pagination_html' => view('admin.components.orders-pagination', compact('orders'))->render(), // Reusing orders pagination
            'stats' => $this->getStats(),
        ]);
    }

    protected function getStats()
    {
        return [
            'total_held' => Order::where('has_rental_items', true)
                            ->where('status', '!=', 'Returned')
                            ->where('is_security_returned', false)
                            ->sum('security_amount'),
            'need_to_return' => Order::where('has_rental_items', true)
                                ->where('status', 'Returned')
                                ->where('is_security_returned', false)
                                ->sum('security_amount'),
            'returned' => Order::where('has_rental_items', true)
                                ->where('is_security_returned', true)
                                ->sum('security_amount'),
        ];
    }

    public function markAsReturned(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->is_security_returned) {
             return response()->json(['success' => false, 'message' => 'Security already returned.'], 400);
        }

        $order->security_returned_at = now();
        $order->is_security_returned = true;
        $order->save();
        
        return response()->json(['success' => true, 'message' => 'Security marked as returned.']);
    }
}
