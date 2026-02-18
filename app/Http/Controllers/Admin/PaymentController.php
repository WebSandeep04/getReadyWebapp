<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Notification;
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
            return $this->attachPaymentDetails($payment);
        });

        return response()->json([
            'table_html' => view('admin.components.payment-rows', compact('payments'))->render(),
            'pagination_html' => view('admin.components.orders-pagination', ['orders' => $payments])->render(),
            'stats' => $this->getStats(),
        ]);
    }

    // New method for Dashboard widget (Limit 5)
    public function fetchDashboardPayments(Request $request)
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
            $data = $this->attachPaymentDetails($payment)->toArray();
            $data['payer_name'] = $payment->order && $payment->order->buyer ? $payment->order->buyer->name : 'Unknown';
            $data['order_id'] = $payment->order_id;
            $data['paid_at_formatted'] = $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : 
                                         ($payment->created_at ? $payment->created_at->format('d M Y, h:i A') : '-');
            return $data;
        });

        return response()->json([
            'payments' => $formattedPayments,
            'stats' => $this->getStats()
        ]);
    }

    private function attachPaymentDetails($payment)
    {
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
                
                // For Buy items, rent_gst is Buyer's Tax (18%). Seller gets it only if GST registered.
                $gstShare = ($item->rent_gst ?? 0);
                if ($item->purchase_type === 'buy' && !$item->is_seller_gst) {
                    $gstShare = 0;
                }
                
                // Net for Seller calculation
                $sellerNet += ($item->base_rent ?? 0) + $gstShare - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
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
        $payment->order_status = $payment->order ? $payment->order->status : 'Draft';
        
        return $payment;
    }

    protected function getStats()
    {
        $paidStatus = ['Paid', 'Success', 'paid', 'success'];
        $failedStatus = ['Failed', 'Cancelled', 'failed', 'cancelled'];
        $refundStatus = ['Refunded', 'Partially Refunded', 'refunded'];

        $paidOrderIds = Payment::whereIn('payment_status', $paidStatus)->pluck('order_id');
        $orderItems = OrderItem::whereIn('order_id', $paidOrderIds)->get();

        $rentGstTotal = $orderItems->sum('rent_gst');
        $buyerCommGstTotal = $orderItems->sum('buyer_commission_gst');
        $sellerCommGstTotal = $orderItems->sum('seller_commission_gst');
        $buyerCommTotal = $orderItems->sum('buyer_commission');
        $sellerCommTotal = $orderItems->sum('seller_commission');
        $totalCommission = $buyerCommTotal + $sellerCommTotal;
        $totalGst = $rentGstTotal + $buyerCommGstTotal + $sellerCommGstTotal;

        $sellerPayouts = $orderItems->sum(function($item) {
            $sCommGst = (float)($item->seller_commission_gst ?? (($item->seller_commission ?? 0) * 0.18));
            $gstShare = ($item->rent_gst ?? 0);
            if ($item->purchase_type === 'buy' && !$item->is_seller_gst) {
                $gstShare = 0;
            }
            return ($item->base_rent ?? 0) + $gstShare - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
        });

        return [
            'paid_count' => Payment::whereIn('payment_status', $paidStatus)->count(),
            'paid_amount' => Payment::whereIn('payment_status', $paidStatus)->sum('amount'),
            
            'pending_count' => Payment::where('payment_status', 'Pending')->count(),
            'pending_amount' => Payment::where('payment_status', 'Pending')->sum('amount'),
            
            'failed_count' => Payment::whereIn('payment_status', $failedStatus)->count(),
            'failed_amount' => Payment::whereIn('payment_status', $failedStatus)->sum('amount'),
            
            'refund_count' => Payment::whereIn('payment_status', $refundStatus)->count(),
            'refund_amount' => Payment::whereIn('payment_status', $refundStatus)->sum('amount'),

            // Breakdown (from AdminController stats structure)
            'total_commission' => round($totalCommission, 2),
            'total_gst' => round($totalGst, 2),
            'total_seller_payouts' => round($sellerPayouts, 2),
            'total_rent_gst' => round($rentGstTotal, 2),
            'total_buyer_comm_gst' => round($buyerCommGstTotal, 2),
            'total_seller_comm_gst' => round($sellerCommGstTotal, 2),
             // Keeping original keys for backward compatibility if needed by listing page (though listing page seemed to use getStats directly)
             'rent_gst' => $rentGstTotal,
             'buyer_gst' => $buyerCommGstTotal,
             'seller_gst' => $sellerCommGstTotal,
             'total_comm' => $totalCommission,
             'platform_earning' => $totalCommission + $totalGst
        ];
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
