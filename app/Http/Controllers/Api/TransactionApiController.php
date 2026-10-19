<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionApiController extends Controller
{
    /**
     * Get list of all transactions (debits, payouts, security refunds) for authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $request->user() ? $request->user()->id : Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // 1. Payments made by the user (as Buyer) - Debits
        $debits = Payment::whereHas('order', function($q) use ($userId) {
            $q->where('buyer_id', $userId);
        })
        ->with('order')
        ->get()
        ->map(function($payment) {
            $rawDate = $payment->paid_at ?? $payment->created_at;
            $carbonDate = $rawDate ? Carbon::parse($rawDate) : null;
            
            return [
                'id' => 'pmt-' . $payment->id,
                'date' => $carbonDate ? $carbonDate->toISOString() : null,
                'formatted_date' => $carbonDate ? $carbonDate->format('d M Y, h:i A') : 'N/A',
                'order_id' => $payment->order_id,
                'order_number' => 'GR-' . str_pad($payment->order_id, 5, '0', STR_PAD_LEFT),
                'transaction_id' => $payment->transaction_id ?? 'N/A',
                'method' => $payment->payment_method ?? 'Razorpay',
                'status' => $payment->payment_status ?? 'Paid',
                'amount' => (float) $payment->amount,
                'type' => 'debit',
                'description' => 'Payment for Order'
            ];
        });

        // 2. Payouts received by the user (as Seller) - Credits
        $payouts = Order::whereHas('items.cloth', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('is_seller_paid', true)
        ->with(['items.cloth', 'extensions' => function($q) {
            $q->where('status', 'paid');
        }])
        ->get()
        ->map(function($order) use ($userId) {
            $netAmount = 0;
            foreach ($order->items as $item) {
                if ($item->cloth && $item->cloth->user_id == $userId) {
                    $sCommGst = ($item->seller_commission ?? 0) * 0.18;
                    $gstShare = ($item->rent_gst ?? 0);
                    if ($item->purchase_type === 'buy' && !$item->is_seller_gst) {
                        $gstShare = 0;
                    }
                    $netAmount += ($item->base_rent ?? 0) + $gstShare - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
                }
            }
            // Add extensions share
            $netAmount += (float) $order->extensions->sum('seller_net_amount');

            $rawDate = $order->seller_paid_at ?? $order->updated_at;
            $carbonDate = $rawDate ? Carbon::parse($rawDate) : null;

            return [
                'id' => 'payout-' . $order->id,
                'date' => $carbonDate ? $carbonDate->toISOString() : null,
                'formatted_date' => $carbonDate ? $carbonDate->format('d M Y, h:i A') : 'N/A',
                'order_id' => $order->id,
                'order_number' => 'GR-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'transaction_id' => 'PAYOUT-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'method' => 'Platform Payout',
                'status' => 'Paid',
                'amount' => (float) $netAmount,
                'type' => 'credit',
                'description' => 'Seller Earnings'
            ];
        });

        // 3. Security returns received by the user (as Buyer) - Credits
        $securityReturns = Order::where('buyer_id', $userId)
            ->where('is_security_returned', true)
            ->get()
            ->map(function($order) {
                $rawDate = $order->security_returned_at ?? $order->updated_at;
                $carbonDate = $rawDate ? Carbon::parse($rawDate) : null;

                return [
                    'id' => 'sec-' . $order->id,
                    'date' => $carbonDate ? $carbonDate->toISOString() : null,
                    'formatted_date' => $carbonDate ? $carbonDate->format('d M Y, h:i A') : 'N/A',
                    'order_id' => $order->id,
                    'order_number' => 'GR-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'transaction_id' => 'SEC-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'method' => 'Security Refund',
                    'status' => 'Paid',
                    'amount' => (float) $order->security_amount,
                    'type' => 'credit',
                    'description' => 'Security Deposit Return'
                ];
            });

        $allTransactions = $debits->concat($payouts)->concat($securityReturns)
            ->sortByDesc(function($item) {
                return $item['date'] ?? '';
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $allTransactions
        ]);
    }
}
