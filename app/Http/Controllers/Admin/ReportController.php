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
                
                // --- PRICING DATA SOURCE ---
                if ($item->base_rent !== null) {
                    $isPurchase = $item->purchase_type === 'buy';
                    $basePrice = (float)$item->base_rent;
                    $mrp = $cloth->mrp ?? 0;
                    $security = $cloth->security_deposit ?? 0;
                    
                    $sellerComm = (float)$item->seller_commission;
                    $buyerComm = (float)$item->buyer_commission;
                    $sellerCommGst = (float)$item->seller_commission_gst;
                    $buyerCommGst = (float)$item->buyer_commission_gst;
                    $rentGst = (float)$item->rent_gst;
                    $tcs = (float)$item->tcs_amount;

                    // Platform Revenue (Net Commissions + Retained Fee for Unreg)
                    $retainedTax = !$item->is_seller_gst ? $rentGst : 0;
                    $platformRevenue = $sellerComm + $buyerComm + $retainedTax;
                    
                    // Simplified View for Report as requested
                    $payableToSeller = $basePrice - $sellerComm;
                    $receivableFromBuyer = $basePrice + $buyerComm;
                    
                    if ($isPurchase) {
                        $security = 0;
                    }
                } else {
                    $isPurchase = $order->has_purchase_items && (!$order->has_rental_items || $pricePaid > ($cloth->rent_price * 2));
                    
                    if ($isPurchase) {
                        $basePrice = $cloth->selling_price > 0 ? $cloth->selling_price : $pricePaid;
                        $mrp = $cloth->mrp ?? $basePrice;
                        $security = 0;
                        $sellerCommRate = 0.20; 
                        $buyerCommRate = 0; 
                    } else {
                        $basePrice = $cloth->rent_price > 0 ? $cloth->rent_price : $pricePaid;
                        $mrp = $cloth->mrp ?? 0;
                        $security = $cloth->security_deposit ?? 0;
                        $sellerCommRate = 0.20;
                        $buyerCommRate = 0.20;
                    }
                    
                    $rentGst = 0;
                    $grossSellerComm = $basePrice * $sellerCommRate;
                    $grossBuyerComm = max(0, $pricePaid - $basePrice - $security);
                    
                    $payableToSeller = $basePrice - $grossSellerComm;
                    $receivableFromBuyer = $basePrice + $grossBuyerComm;
                    $platformRevenue = $grossSellerComm + $grossBuyerComm;

                    $sellerComm = $grossSellerComm;
                    $buyerComm = $grossBuyerComm;
                }
                
                // Expenses (Based on reference spreadsheet)
                $pgFee = 30; // Standard PG Fee placeholder
                $deliveryCost = 80; // Standard Delivery placeholder
                $fraudCost = 0; // Placeholder
                
                // Dates for report
                $deliveredAt = $order->status === 'Delivered' || $order->status === 'Returned' ? $order->updated_at : null;
                $returnedAt = $order->status === 'Returned' ? $order->updated_at : null;

                $payableToSellerDate = $deliveredAt ? $deliveredAt->addDays(7)->format('d/m/Y') : '-';
                $securityPayableDate = $returnedAt ? $returnedAt->addDays(3)->format('d/m/Y') : '-';

                return [
                    'item_id' => $item->id,
                    'title' => $cloth->title ?? 'Deleted Item',
                    'is_purchase' => $isPurchase,
                    'mrp' => $mrp,
                    'base_price' => $basePrice,
                    'rent_gst' => $rentGst,
                    'security' => $security,
                    'payable_to_seller' => $payableToSeller,
                    'receivable_from_buyer' => $receivableFromBuyer,
                    'payable_to_seller_date' => $payableToSellerDate,
                    'security_payable_date' => $securityPayableDate,
                    'revenue_seller_comm' => $sellerComm,
                    'revenue_buyer_comm' => $buyerComm,
                    'return_handling' => 0,
                    'total_revenue' => $platformRevenue,
                    'exp_pg' => $pgFee,
                    'exp_delivery' => $deliveryCost,
                    'exp_fraud' => $fraudCost,
                    'total_exp' => $pgFee + $deliveryCost + $fraudCost,
                    'net_profit' => ($platformRevenue) - ($pgFee + $deliveryCost + $fraudCost)
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
            ->get()
            ->map(function ($order) {
                // Calculate Payouts
                $rentPayableToSeller = 0;
                $sellingPayableToSeller = 0;

                foreach ($order->items as $item) {
                    // Use new logic: Base Rent - Seller Commission
                    $payout = ($item->base_rent ?? 0) - ($item->seller_commission ?? 0);
                    
                    if ($item->purchase_type === 'buy') {
                        $sellingPayableToSeller += $payout;
                    } else {
                        $rentPayableToSeller += $payout;
                    }
                }
                
                $order->rent_payable_to_seller = $rentPayableToSeller;
                $order->selling_price_payable_to_seller = $sellingPayableToSeller;
                
                return $order;
            });

        return view('admin.screens.reports.calendar', compact('orders'));
    }
}
