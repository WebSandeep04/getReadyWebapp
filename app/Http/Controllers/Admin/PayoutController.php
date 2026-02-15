<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Carbon;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $stats = $this->getStats();
        return view('admin.screens.payouts', compact('stats'));
    }

    public function fetchData(Request $request)
    {
        $query = Order::with(['buyer', 'items.cloth.user'])
            ->where('has_rental_items', true)
            ->whereNull('return_reason') // Exclude issue-based returns (seller shouldn't be paid)
            ->whereNotIn('status', ['Cancelled', 'Return Requested', 'Return In Progress']);

        if ($request->has('status') && $request->status) {
            $status = $request->status;
            if ($status === 'pending') {
                $query->where('status', 'Returned')->where('is_seller_paid', false);
            } elseif ($status === 'processing') {
                $query->where('status', '!=', 'Returned')->where('is_seller_paid', false);
            } elseif ($status === 'completed') {
                $query->where('is_seller_paid', true);
            }
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('id', $search);
                }
                $q->orWhereHas('buyer', function($bq) use ($search) {
                    $bq->where('name', 'like', "%{$search}%");
                })->orWhereHas('items.cloth.user', function($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Check if we are fetching for dashboard (limit 5)
        if ($request->has('limit')) {
            $orders = $query->latest()->limit($request->limit)->get();
            
            $formatted = $orders->map(function ($order) {
                return $this->formatOrderForJson($order);
            });

            return response()->json([
                'orders' => $formatted,
                'stats' => $this->getStats(),
            ]);
        }

        $orders = $query->latest()->paginate(20)->appends($request->query());

        $orders->getCollection()->transform(function ($order) {
            return $this->prepareOrderForDisplay($order);
        });

        return response()->json([
            'table_html' => view('admin.components.payout-rows', ['orders' => $orders])->render(),
            'pagination_html' => view('admin.components.orders-pagination', ['orders' => $orders])->render(),
            'stats' => $this->getStats(),
        ]);
    }

    protected function prepareOrderForDisplay($order)
    {
        $totalSellerNet = 0;
        $sellerNames = [];
        foreach ($order->items as $item) {
            $sCommGst = ($item->seller_commission ?? 0) * 0.18;
            $net = ($item->base_rent ?? 0) + ($item->rent_gst ?? 0) - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
            $totalSellerNet += $net;
            
            if ($item->cloth && $item->cloth->user) {
                $sellerNames[] = $item->cloth->user->name;
            }
        }
        
        $order->seller_display_name = count(array_unique($sellerNames)) > 1 ? 'Multiple Sellers' : (count($sellerNames) > 0 ? $sellerNames[0] : 'Unknown');
        $order->total_seller_net = round($totalSellerNet, 2);
        
        return $order;
    }

    protected function formatOrderForJson($order)
    {
        $order = $this->prepareOrderForDisplay($order);
        
        $data = $order->toArray();
        $data['seller_name'] = $order->seller_display_name;
        $data['amount'] = $order->total_seller_net;
        $data['buyer_name'] = $order->buyer ? $order->buyer->name : 'Unknown';
        $data['created_at_formatted'] = $order->created_at->format('d M Y');
        $data['seller_paid_at_formatted'] = $order->seller_paid_at ? $order->seller_paid_at->format('d M Y') : null;
        
        return $data;
    }

    public function markPaid($id)
    {
        $order = Order::findOrFail($id);
        $order->is_seller_paid = true;
        $order->seller_paid_at = now();
        $order->save();

        return response()->json(['success' => true]);
    }

    protected function getStats()
    {
        // 1. Get IDs of orders that are actually paid by buyer AND not disputed/cancelled
        $validOrderQuery = Order::whereNull('return_reason')
            ->whereNotIn('status', ['Cancelled', 'Return Requested', 'Return In Progress']);
            
        $validOrderIds = $validOrderQuery->pluck('id');
        
        $totalNet = OrderItem::whereIn('order_id', $validOrderIds)->get()->sum(function($item) {
            $sCommGst = ($item->seller_commission ?? 0) * 0.18;
            return ($item->base_rent ?? 0) + ($item->rent_gst ?? 0) - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
        });

        // 2. Orders that are Returned (Stock back) and ready for Seller Payout
        $needToPay = Order::with('items')
            ->where('status', 'Returned')
            ->where('is_seller_paid', false)
            ->whereNull('return_reason') // Critical: Only standard returns, not issues
            ->get()->sum(function($order) {
                return $order->items->sum(function($item) {
                    $sCommGst = ($item->seller_commission ?? 0) * 0.18;
                    return ($item->base_rent ?? 0) + ($item->rent_gst ?? 0) - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
                });
            });

        // 3. Orders already paid out to sellers
        $paidToSellers = Order::with('items')
            ->where('is_seller_paid', true)
            ->get()->sum(function($order) {
                return $order->items->sum(function($item) {
                    $sCommGst = ($item->seller_commission ?? 0) * 0.18;
                    return ($item->base_rent ?? 0) + ($item->rent_gst ?? 0) - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
                });
            });

        return [
            'total_net' => round($totalNet, 2),
            'need_to_pay' => round($needToPay, 2),
            'paid_to_sellers' => round($paidToSellers, 2),
        ];
    }
}
