<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function financial(Request $request)
    {
        $query = Order::with(['buyer', 'items.cloth', 'payments', 'shipments']);

        // Date Filtering
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->get();

        $reportData = $orders->map(function ($order) {
            $itemsData = $order->items->map(function ($item) use ($order) {
                $cloth = $item->cloth;
                $pricePaid = $item->price;
                
                // Heuristic to detect Purchase vs Rental
                // 1. If order only has purchase items, it's a purchase.
                // 2. If item price is significantly higher than rent price, it's likely a purchase.
                $isPurchase = $order->has_purchase_items && (!$order->has_rental_items || $pricePaid > ($cloth->rent_price * 2));
                
                if ($isPurchase) {
                    $basePrice = $cloth->selling_price > 0 ? $cloth->selling_price : $pricePaid;
                    $mrp = $cloth->mrp ?? $basePrice;
                    $security = 0;
                    
                    // For purchases, we assume commission is only from seller (standard fee)
                    // unless software logic dictates otherwise. 
                    $sellerCommRate = 0.20; 
                    $buyerCommRate = 0; 
                } else {
                    $basePrice = $cloth->rent_price > 0 ? $cloth->rent_price : $pricePaid;
                    $mrp = $cloth->mrp ?? 0;
                    $security = $cloth->security_deposit ?? 0;
                    
                    // For rentals, use the 20/20 logic
                    $sellerCommRate = 0.20;
                    $buyerCommRate = 0.20;
                }
                
                $sellerComm = $basePrice * $sellerCommRate;
                
                // Actual Buyer Commission: What was paid above the base price
                $buyerComm = max(0, $pricePaid - $basePrice);
                
                // Discount: Only if they paid LESS than the base price
                $discount = max(0, $basePrice - $pricePaid);
                
                $payableToSeller = $basePrice - $sellerComm;
                $receivableFromBuyer = $pricePaid;
                
                // Expenses
                $pgFee = $pricePaid * 0.02; // 2% 
                $deliveryCost = 100; // Placeholder
                
                return [
                    'item_id' => $item->id,
                    'title' => $cloth->title ?? 'Deleted Item',
                    'is_purchase' => $isPurchase,
                    'mrp' => $mrp,
                    'base_price' => $basePrice,
                    'discount' => $discount,
                    'security' => $security,
                    'payable_to_seller' => $payableToSeller,
                    'receivable_from_buyer' => $receivableFromBuyer,
                    'revenue_seller_comm' => $sellerComm,
                    'revenue_buyer_comm' => $buyerComm,
                    'return_handling' => 0,
                    'total_revenue' => $sellerComm + $buyerComm,
                    'exp_pg' => $pgFee,
                    'exp_delivery' => $deliveryCost,
                    'total_exp' => $pgFee + $deliveryCost,
                    'net_profit' => ($sellerComm + $buyerComm) - ($pgFee + $deliveryCost)
                ];
            });

            return [
                'order_id' => $order->id,
                'created_at' => $order->created_at->format('d/m/Y'),
                'items' => $itemsData,
                'total_security' => $order->security_amount,
                'status' => $order->status
            ];
        });

        // Summary Stats
        $stats = [
            'total_revenue' => $reportData->sum(fn($o) => collect($o['items'])->sum('total_revenue')),
            'total_security' => $reportData->sum('total_security'),
            'total_payouts' => $reportData->sum(fn($o) => collect($o['items'])->sum('payable_to_seller')) + Payment::where('payment_status', 'Refunded')->sum('amount'),
            'net_profit' => $reportData->sum(fn($o) => collect($o['items'])->sum('net_profit')),
        ];

        return view('admin.screens.reports.financial', compact('reportData', 'stats'));
    }

    public function calendar(Request $request)
    {
        // Fetch all relevant orders for the calendar
        $orders = Order::with(['buyer', 'items.cloth'])
            ->where(function($query) {
                $query->where('has_rental_items', true)
                      ->whereNotNull('rental_from');
            })
            ->orWhere('has_purchase_items', true)
            ->get();

        return view('admin.screens.reports.calendar', compact('orders'));
    }
}
