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
        $query = Payment::with(['order.buyer', 'order.items']);
        
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

        // Add pricing breakdown to each payment object for the view
        $payments->getCollection()->transform(function ($payment) {
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
                    
                    // Use stored columns or fallback to manual calculation
                    $bCommGst = (float) ($item->buyer_commission_gst ?? ($item->buyer_commission * 0.18 ?? 0));
                    $sCommGst = (float) ($item->seller_commission_gst ?? ($item->seller_commission * 0.18 ?? 0));
                    
                    $buyerCommGst += $bCommGst;
                    $sellerCommGst += $sCommGst;
                    
                    // Net for Seller calculation
                    $sellerNet += ($item->base_rent ?? 0) + ($item->rent_gst ?? 0) - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
                }
            }
            
            $payment->base_rent_total = round($baseRent, 2);
            $payment->buyer_comm_total = round($buyerComm, 2);
            $payment->seller_comm_total = round($sellerComm, 2);
            $payment->rent_gst_total = round($rentGst, 2);
            $payment->buyer_comm_gst_total = round($buyerCommGst, 2);
            $payment->seller_comm_gst_total = round($sellerCommGst, 2);
            $payment->gst_total = round($rentGst + $buyerCommGst + $sellerCommGst, 2);
            $payment->seller_net_payout = round($sellerNet, 2);
            $payment->security_amount = $payment->order ? ($payment->order->security_amount ?? 0) : 0;
            
            return $payment;
        });

        return response()->json([
            'table_html' => view('admin.components.payment-rows', compact('payments'))->render(),
            'pagination_html' => view('admin.components.orders-pagination', ['orders' => $payments])->render(),
            'stats' => $this->getStats(),
        ]);
    }

    protected function getStats()
    {
        $paidStatus = ['Paid', 'Success', 'paid', 'success'];
        $failedStatus = ['Failed', 'Cancelled', 'failed', 'cancelled'];
        $refundStatus = ['Refunded', 'Partially Refunded', 'refunded'];

        $paidPayments = Payment::whereIn('payment_status', $paidStatus)->pluck('order_id');
        $orderItems = \App\Models\OrderItem::whereIn('order_id', $paidPayments)->get();

        $rentGst = $orderItems->sum('rent_gst');
        $buyerGst = $orderItems->sum('buyer_commission_gst');
        $sellerGst = $orderItems->sum('seller_commission_gst');
        $buyerComm = $orderItems->sum('buyer_commission');
        $sellerComm = $orderItems->sum('seller_commission');

        return [
            'confirmed_count' => Payment::whereIn('payment_status', $paidStatus)->count(),
            'confirmed_amount' => Payment::whereIn('payment_status', $paidStatus)->sum('amount'),
            
            'pending_count' => Payment::where('payment_status', 'Pending')->count(),
            'pending_amount' => Payment::where('payment_status', 'Pending')->sum('amount'),
            
            'failed_count' => Payment::whereIn('payment_status', $failedStatus)->count(),
            'failed_amount' => Payment::whereIn('payment_status', $failedStatus)->sum('amount'),
            
            'refund_count' => Payment::whereIn('payment_status', $refundStatus)->count(),
            'refund_amount' => Payment::whereIn('payment_status', $refundStatus)->sum('amount'),

            // Breakdown
            'rent_gst' => $rentGst,
            'buyer_gst' => $buyerGst,
            'seller_gst' => $sellerGst,
            'total_gst' => $rentGst + $buyerGst + $sellerGst,
            'total_comm' => $buyerComm + $sellerComm,
            'platform_earning' => ($buyerComm + $sellerComm) + ($buyerGst + $sellerGst)
        ];
    }
}
