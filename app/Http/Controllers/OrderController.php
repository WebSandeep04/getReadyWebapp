<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $showFilters = false;

        $orders = Order::with(['payments', 'shipment'])
            ->where('buyer_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders', 'showFilters'));
    }

    public function sales()
    {
        $showFilters = false;
        $userId = Auth::id();

        // Get orders that contain items belonging to the current user
        $orders = Order::whereHas('items.cloth', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['items' => function ($query) use ($userId) {
                // Filter items to only show those belonging to the current user (in case of mixed carts)
                $query->whereHas('cloth', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->with(['cloth' => function($q) {
                    $q->with(['category', 'brand', 'size', 'color', 'fabric', 'condition', 'fitType', 'bottomType']);
                }]);
            }, 'buyer', 'payments'])
            ->latest()
            ->paginate(10);

        return view('orders.sales', compact('orders', 'showFilters'));
    }
}

