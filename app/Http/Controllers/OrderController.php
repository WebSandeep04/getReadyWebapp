<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $showFilters = false;

        $orders = Order::with(['payments', 'shipments', 'invoices'])
            ->where('buyer_id', Auth::id())
            ->where('status', '!=', 'Pending')
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
            ->where('status', '!=', 'Pending')
            ->with(['items' => function ($query) use ($userId) {
                // Filter items to only show those belonging to the current user (in case of mixed carts)
                $query->whereHas('cloth', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->with(['cloth' => function($q) {
                    $q->with(['category', 'brand', 'size', 'color', 'fabric', 'condition', 'fitType', 'bottomType']);
                }]);
            }, 'buyer', 'payments', 'invoices'])
            ->latest()
            ->paginate(10);

        return view('orders.sales', compact('orders', 'showFilters'));
    }

    public function transactions()
    {
        $userId = Auth::id();
        
        // 1. Payments made by the user (as Buyer) - Debits
        $debits = \App\Models\Payment::whereHas('order', function($q) use ($userId) {
            $q->where('buyer_id', $userId);
        })
        ->with('order')
        ->get()
        ->map(function($payment) {
            return (object) [
                'date' => $payment->paid_at ?? $payment->created_at,
                'order_id' => $payment->order_id,
                'transaction_id' => $payment->transaction_id,
                'method' => $payment->payment_method,
                'status' => $payment->payment_status,
                'amount' => $payment->amount,
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

            return (object) [
                'date' => $order->seller_paid_at ?? $order->updated_at,
                'order_id' => $order->id,
                'transaction_id' => 'PAYOUT-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'method' => 'Platform Payout',
                'status' => 'Paid',
                'amount' => $netAmount,
                'type' => 'credit',
                'description' => 'Seller Earnings'
            ];
        });

        // 3. Security returns received by the user (as Buyer) - Credits
        $securityReturns = Order::where('buyer_id', $userId)
            ->where('is_security_returned', true)
            ->get()
            ->map(function($order) {
                return (object) [
                    'date' => $order->security_returned_at ?? $order->updated_at,
                    'order_id' => $order->id,
                    'transaction_id' => 'SEC-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'method' => 'Security Refund',
                    'status' => 'Paid',
                    'amount' => $order->security_amount,
                    'type' => 'credit',
                    'description' => 'Security Deposit Return'
                ];
            });

        $allTransactions = $debits->concat($payouts)->concat($securityReturns)->sortByDesc('date');

        // Manual Pagination
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $currentItems = $allTransactions->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $payments = new \Illuminate\Pagination\LengthAwarePaginator($currentItems, $allTransactions->count(), $perPage);
        $payments->setPath(request()->url());

        return view('orders.transactions', compact('payments'));
    }

    public function cancel($id)
    {
        $order = Order::with(['items.cloth', 'payments', 'shipments'])->where('buyer_id', Auth::id())->findOrFail($id);

        if (!in_array($order->status, ['Pending', 'Confirmed', 'Order Confirmed & Shipment Created'])) {
            return back()->with('error', 'Only Pending, Confirmed or recently shipped orders can be cancelled.');
        }

        // Cancel shipment if any
        if ($order->status === 'Order Confirmed & Shipment Created') {
            $forwardShipment = $order->shipments()->where('type', 'forward')->where('status', '!=', 'Cancelled')->first();
            if ($forwardShipment && $forwardShipment->waybill_number) {
                $xpressbees = app(\App\Services\XpressbeesService::class);
                $xpressbees->cancelShipment($forwardShipment->waybill_number);
                $forwardShipment->status = 'Cancelled';
                $forwardShipment->save();
            }
        }

        // Cancel order
        $order->status = 'Cancelled';
        $order->save();

        // Restore stock and availability
        $availabilityService = app(\App\Services\AvailabilityService::class);
        foreach ($order->items as $item) {
            $cloth = $item->cloth;
            if ($cloth) {
                $cloth->sku = $cloth->sku + 1;
                $cloth->is_available = true;
                $cloth->save();

                if ($item->purchase_type !== 'buy') {
                    $availabilityService->restoreAvailabilityForOrder($cloth->id, $order->id);
                }
            }
        }

        // Refund payments via Razorpay API
        $paidPayments = $order->payments()->whereIn('payment_status', ['Paid', 'Success', 'paid', 'success'])->get();
        foreach ($paidPayments as $payment) {
            // Check if it's a Razorpay payment and we have a transaction ID
            if (str_contains(strtolower($payment->payment_method), 'razorpay') && $payment->transaction_id) {
                try {
                    $keyId = config('services.razorpay.key_id');
                    $keySecret = config('services.razorpay.key_secret');
                    
                    \Illuminate\Support\Facades\Http::withBasicAuth($keyId, $keySecret)
                        ->post("https://api.razorpay.com/v1/payments/{$payment->transaction_id}/refund", [
                            'amount' => (int) round($payment->amount * 100)
                        ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Razorpay Refund Failed for Order ID ' . $order->id . ': ' . $e->getMessage());
                }
            }

            $payment->payment_status = 'Refunded';
            $payment->save();
        }

        return back()->with('success', 'Order cancelled successfully and full refund initiated.');
    }
}

