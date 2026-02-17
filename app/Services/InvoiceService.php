<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceService
{
    public function generateOrderInvoices(Order $order)
    {
        // Ensure relationships are loaded
        $order->load(['items.cloth.user', 'buyer', 'items.cloth']);

        // Group items by Seller
        $itemsBySeller = $order->items->groupBy(function ($item) {
            return $item->cloth->user_id;
        });

        foreach ($itemsBySeller as $sellerId => $items) {
            $seller = $items->first()->cloth->user;
            
            // 1. Invoice A: Rent/Sale (Seller -> Buyer)
            $this->generateSellerToBuyerInvoice($order, $seller, $items);

            // 2. Invoice B: Platform Fee (Platform -> Seller)
            $this->generatePlatformToSellerInvoice($order, $seller, $items);
        }

        // 3. Invoice C: Platform Fee (Platform -> Buyer) - Consolidated for all items
        $this->generatePlatformToBuyerInvoice($order, $order->items);
    }

    protected function generateSellerToBuyerInvoice(Order $order, $seller, $items)
    {
        // Calculate totals
        $totalBase = 0;
        $totalTax = 0;
        $totalAmount = 0;

        foreach ($items as $item) {
            $base = (float) $item->base_rent; // or base_price
            $tax = (float) $item->rent_gst;
            
            // Logic: If seller is NOT GST registered, they cannot charge GST.
            // The 'rent_gst' column contains the amount buyer paid as "Tax/Fee".
            // If seller is unregistered, this amount is collected by Platform (Invoice C).
            // So for Invoice A, we only include Tax if seller is GST registered.
            
            $itemTax = $item->is_seller_gst ? $tax : 0;
            
            $totalBase += $base;
            $totalTax += $itemTax;
            $totalAmount += ($base + $itemTax);
        }

        $invoiceNumber = 'INV-' . strtoupper(Str::random(8)); // In real app, use sequence
        $pdf = Pdf::loadView('invoices.seller_to_buyer', compact('order', 'seller', 'items', 'totalBase', 'totalTax', 'totalAmount', 'invoiceNumber'));
        
        $path = 'invoices/' . $order->id . '/' . $invoiceNumber . '_seller_buyer.pdf';
        Storage::put('public/' . $path, $pdf->output());

        Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'type' => 'rent_sale',
            'amount' => $totalAmount,
            'tax_amount' => $totalTax,
            'pdf_path' => $path,
            'issued_by_id' => $seller->id,
            'issued_to_id' => $order->buyer_id,
        ]);
    }

    protected function generatePlatformToSellerInvoice(Order $order, $seller, $items)
    {
        // Calculate totals (Commission from Seller)
        $totalComm = 0;
        $totalCommGst = 0;
        $totalTcs = 0;

        foreach ($items as $item) {
            $comm = (float) $item->seller_commission;
            $gst = (float) $item->seller_commission_gst;
            $tcs = (float) $item->tcs_amount;

            $totalComm += $comm;
            $totalCommGst += $gst;
            $totalTcs += $tcs;
        }

        $totalAmount = $totalComm + $totalCommGst; // Invoice amount to be deducted

        $invoiceNumber = 'GR-S-' . strtoupper(Str::random(8));
        $pdf = Pdf::loadView('invoices.platform_to_seller', compact('order', 'seller', 'items', 'totalComm', 'totalCommGst', 'totalTcs', 'totalAmount', 'invoiceNumber'));

        $path = 'invoices/' . $order->id . '/' . $invoiceNumber . '_platform_seller.pdf';
        Storage::put('public/' . $path, $pdf->output());

        Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'type' => 'platform_fee_seller',
            'amount' => $totalAmount,
            'tax_amount' => $totalCommGst,
            'pdf_path' => $path,
            'issued_by_id' => null, // Platform
            'issued_to_id' => $seller->id,
        ]);
    }

    protected function generatePlatformToBuyerInvoice(Order $order, $items)
    {
        // Calculate totals (Commission from Buyer + Unregistered Seller Fee)
        $totalComm = 0;
        $totalCommGst = 0;
        $totalOtherFees = 0; // Fee from unregistered seller items

        foreach ($items as $item) {
            $comm = (float) $item->buyer_commission;
            $gst = (float) $item->buyer_commission_gst;
            
            // If seller is UNREGISTERED, the 'rent_gst' (18% of base) is retained by platform as a fee.
            // This needs to be invoiced to Buyer by Platform.
            $otherFee = 0;
            if (!$item->is_seller_gst) {
                $otherFee = (float) $item->rent_gst;
            }

            $totalComm += $comm;
            $totalCommGst += $gst;
            $totalOtherFees += $otherFee;
        }

        // Note: The 'other fee' (rent_gst equivalent) is technically "Service Fee".
        // Does it attract its own GST? In the pricing logic, it was 18% of base.
        // So 18% of Base is the fee. It likely matches the tax rate.
        // We will list it as "Additional Service Fee".

        $totalAmount = $totalComm + $totalCommGst + $totalOtherFees;

        $invoiceNumber = 'GR-B-' . strtoupper(Str::random(8));
        $buyer = $order->buyer;
        
        $pdf = Pdf::loadView('invoices.platform_to_buyer', compact('order', 'buyer', 'items', 'totalComm', 'totalCommGst', 'totalOtherFees', 'totalAmount', 'invoiceNumber'));

        $path = 'invoices/' . $order->id . '/' . $invoiceNumber . '_platform_buyer.pdf';
        Storage::put('public/' . $path, $pdf->output());

        Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'type' => 'platform_fee_buyer',
            'amount' => $totalAmount,
            'tax_amount' => $totalCommGst, // + part of other fee if applicable, but keeping simple
            'pdf_path' => $path,
            'issued_by_id' => null, // Platform
            'issued_to_id' => $order->buyer_id,
        ]);
    }
}
