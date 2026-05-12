<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cloth;
use App\Models\Order;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.screens.dashboard');
    }

    // Dashboard stats for AJAX
    public function dashboardStats()
    {
        $total = Cloth::count();
        $approved = Cloth::where('is_approved', 1)->count();
        $pending = Cloth::where('is_approved', null)->count();
        $rejected = Cloth::where('is_approved', -1)->count();

        return response()->json([
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected
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
            $data['is_purchase'] = (bool) $order->has_purchase_items;
            
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
}
