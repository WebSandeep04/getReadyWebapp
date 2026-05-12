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
        $query = Order::with(['buyer', 'items.cloth.user', 'extensions'])
            // ->where('has_rental_items', true) // Removed to include Purchase orders
            ->whereNull('return_reason') // Exclude issue-based returns
            ->whereNotIn('status', ['Cancelled', 'Return Requested', 'Return In Progress']);

        if ($request->has('status') && $request->status) {
            $status = $request->status;
            if ($status === 'pending') {
                // Eligible for Payout: Rental Returned OR Purchase Delivered
                $query->where('is_seller_paid', false)
                      ->where(function($q) {
                          $q->where(function($sq) {
                              $sq->where('has_rental_items', true)->where('status', 'Returned');
                          })->orWhere(function($sq) {
                              $sq->where('has_purchase_items', true)->where('status', 'Delivered');
                          });
                      });
            } elseif ($status === 'processing') {
                // In Progress: Not yet eligible
                $query->where('is_seller_paid', false)
                      ->where(function($q) {
                          $q->where(function($sq) {
                              $sq->where('has_rental_items', true)->where('status', '!=', 'Returned');
                          })->orWhere(function($sq) {
                              $sq->where('has_purchase_items', true)->where('status', '!=', 'Delivered');
                          });
                      });
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
            
            // For Buy items, rent_gst is Buyer's Tax (18%). Seller gets it only if GST registered.
            // For Rent items, rent_gst is already Seller's Share.
            $gstShare = ($item->rent_gst ?? 0);
            if ($item->purchase_type === 'buy' && !$item->is_seller_gst) {
                $gstShare = 0;
            }
            
            $net = ($item->base_rent ?? 0) + $gstShare - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
            $totalSellerNet += $net;
            
            if ($item->cloth && $item->cloth->user) {
                $sellerNames[] = $item->cloth->user->name;
            }
        }
        
        // Add extension payouts
        $totalSellerNet += (float) $order->extensions->where('status', 'paid')->sum('seller_net_amount');
        
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
        $data['is_purchase'] = $order->has_purchase_items;
        
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
        $validOrderIds = Order::whereNull('return_reason')
            ->whereNotIn('status', ['Cancelled', 'Return Requested', 'Return In Progress'])
            ->pluck('id');

        // 2. Total Seller Amount Held (Earnings from valid orders that haven't been paid yet)
        $totalHeld = OrderItem::whereIn('order_id', $validOrderIds)
            ->whereHas('order', function($q) {
                $q->where('is_seller_paid', false);
            })->get()->sum(function($item) {
                $sCommGst = (float)($item->seller_commission_gst ?? (($item->seller_commission ?? 0) * 0.18));
                
                $gstShare = ($item->rent_gst ?? 0);
                if ($item->purchase_type === 'buy' && !$item->is_seller_gst) {
                    $gstShare = 0;
                }
                
                return ($item->base_rent ?? 0) + $gstShare - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
            });
            
        // 2b. Add extensions to totalHeld
        $extensionHeld = \App\Models\OrderExtension::whereIn('order_id', $validOrderIds)
            ->where('status', 'paid')
            ->whereHas('order', function($q) {
                $q->where('is_seller_paid', false);
            })->sum('seller_net_amount');
        
        $totalHeld += $extensionHeld;

        // 3. Need to Pay (Returned items, not yet paid)
        $needToPay = Order::with(['items', 'extensions'])
            ->where('is_seller_paid', false)
            ->whereNull('return_reason')
            ->where(function($q) {
                // Rental Returned OR Purchase Delivered
                $q->where(function($sq) {
                    $sq->where('status', 'Returned')->where('has_rental_items', true);
                })->orWhere(function($sq) {
                    $sq->where('status', 'Delivered')->where('has_purchase_items', true);
                });
            })
            ->get()->sum(function($order) {
                $itemSum = $order->items->sum(function($item) {
                    $sCommGst = (float)($item->seller_commission_gst ?? (($item->seller_commission ?? 0) * 0.18));
                    $gstShare = ($item->rent_gst ?? 0);
                    if ($item->purchase_type === 'buy' && !$item->is_seller_gst) {
                        $gstShare = 0;
                    }
                    return ($item->base_rent ?? 0) + $gstShare - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
                });
                
                $extensionSum = (float) $order->extensions->where('status', 'paid')->sum('seller_net_amount');
                
                return $itemSum + $extensionSum;
            });

        // 4. Paid to Sellers (Lifetime paid)
        $paidToSellers = Order::with(['items', 'extensions'])
            ->where('is_seller_paid', true)
            ->get()->sum(function($order) {
                $itemSum = $order->items->sum(function($item) {
                    $sCommGst = (float)($item->seller_commission_gst ?? (($item->seller_commission ?? 0) * 0.18));
                    $gstShare = ($item->rent_gst ?? 0);
                    if ($item->purchase_type === 'buy' && !$item->is_seller_gst) {
                        $gstShare = 0;
                    }
                    return ($item->base_rent ?? 0) + $gstShare - (($item->seller_commission ?? 0) + $sCommGst + ($item->tcs_amount ?? 0));
                });
                
                $extensionSum = (float) $order->extensions->where('status', 'paid')->sum('seller_net_amount');
                
                return $itemSum + $extensionSum;
            });

        return [
            'total_held' => round($totalHeld, 2),
            'need_to_pay' => round($needToPay, 2),
            'paid_to_sellers' => round($paidToSellers, 2),
        ];
    }
}
