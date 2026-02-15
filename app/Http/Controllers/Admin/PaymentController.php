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
                    
                    $bCommGst = ($item->buyer_commission ?? 0) * 0.18;
                    $sCommGst = ($item->seller_commission ?? 0) * 0.18;
                    
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
        $paidPayments = Payment::with('order.items')
            ->whereIn('payment_status', ['Paid', 'Success', 'paid', 'success'])
            ->get();

        $transactionVolume = $paidPayments->sum('amount');
        
        $totalBuyerComm = 0;
        $totalSellerComm = 0;
        $totalRentGst = 0;
        $totalBuyerCommGst = 0;
        $totalSellerCommGst = 0;
        $totalSellerPayout = 0;

        foreach ($paidPayments as $payment) {
            if ($payment->order && $payment->order->items) {
                foreach ($payment->order->items as $item) {
                    $totalBuyerComm += (float) $item->buyer_commission;
                    $totalSellerComm += (float) $item->seller_commission;
                    $totalRentGst += (float) $item->rent_gst;
                    
                    $bCommGst = ($item->buyer_commission ?? 0) * 0.18;
                    $sCommGst = ($item->seller_commission ?? 0) * 0.18;
                    
                    $totalBuyerCommGst += $bCommGst;
                    $totalSellerCommGst += $sCommGst;
                    
                    $totalSellerPayout += ($item->base_rent ?? 0) + ($item->rent_gst ?? 0) - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
                }
            }
        }

        return [
            'total_volume' => $transactionVolume,
            'buyer_commission_total' => $totalBuyerComm,
            'seller_commission_total' => $totalSellerComm,
            'total_commission' => $totalBuyerComm + $totalSellerComm,
            'rent_gst_total' => $totalRentGst,
            'buyer_comm_gst_total' => $totalBuyerCommGst,
            'seller_comm_gst_total' => $totalSellerCommGst,
            'total_gst' => $totalRentGst + $totalBuyerCommGst + $totalSellerCommGst,
            'seller_payouts' => $totalSellerPayout,
            'total_platform_earning' => $totalBuyerComm + $totalSellerComm + $totalBuyerCommGst + $totalSellerCommGst,
            'pending_count' => Payment::where('payment_status', 'Pending')->count(),
            'failed_count' => Payment::whereIn('payment_status', ['Failed', 'Cancelled', 'failed', 'cancelled'])->count(),
        ];
    }
}
