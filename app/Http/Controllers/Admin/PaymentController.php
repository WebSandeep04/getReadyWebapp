<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $stats = $this->getStats();
        return view('admin.screens.payments', compact('stats'));
    }

    public function fetchData(Request $request)
    {
        $query = Payment::with('order.buyer');
        
        // Status Filter
        if ($request->has('status') && $request->status) {
            $status = $request->status;
            if ($status !== 'all') {
                $query->where('payment_status', $status);
            }
        }

        // Search (Transaction ID or Order ID)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('order_id', $search);
        }
        
        $payments = $query->latest()->paginate(20)->appends($request->query());

        return response()->json([
            'table_html' => view('admin.components.payment-rows', compact('payments'))->render(),
            'pagination_html' => view('admin.components.orders-pagination', ['orders' => $payments])->render(), // Reusing generic pagination
            'stats' => $this->getStats(),
        ]);
    }

    protected function getStats()
    {
        return [
            'total_revenue' => Payment::where('payment_status', 'Paid')->sum('amount'), // Assuming 'Paid' is the status
            'pending_count' => Payment::where('payment_status', 'Pending')->count(),
            'failed_count' => Payment::whereIn('payment_status', ['Failed', 'Cancelled'])->count(),
        ];
    }
}
