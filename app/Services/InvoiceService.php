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

    /**
     * Generate invoices for a rental extension.
     */
    public function generateExtensionInvoices(\App\Models\OrderExtension $extension)
    {
        $order = $extension->order->load(['buyer', 'items.cloth.user']);
        
        // We need the item-by-item breakdown to generate accurate seller invoices
        $extensionService = app(\App\Services\ExtensionService::class);
        $costData = $extensionService->calculateExtensionCost($order, $extension->extra_days);
        
        // Group by seller
        $itemsBySeller = collect($costData['items'])->groupBy(function($item) {
             $orderItem = \App\Models\OrderItem::find($item['item_id']);
             return $orderItem->cloth->user_id;
        });

        foreach ($itemsBySeller as $sellerId => $extendedItems) {
            $seller = \App\Models\User::find($sellerId);
            
            // Map the extendedItems to look like generic item objects for the views
            $mappedItems = $extendedItems->map(function($extItem) {
                $orderItem = \App\Models\OrderItem::find($extItem['item_id']);
                return (object)[
                    'cloth' => $orderItem->cloth,
                    'base_rent' => $extItem['pricing']['base_rent'],
                    'rent_gst' => $extItem['pricing']['rent_gst'],
                    'is_seller_gst' => $orderItem->is_seller_gst,
                    'buyer_commission' => $extItem['pricing']['buyer_comm'],
                    'buyer_commission_gst' => $extItem['pricing']['buyer_comm_gst'],
                    'seller_commission' => $extItem['pricing']['seller_comm'],
                    'seller_commission_gst' => $extItem['pricing']['seller_comm_gst'],
                    'tcs_amount' => $extItem['pricing']['tcs_amount'] ?? 0,
                    'is_extension' => true // Tooltip/label hint
                ];
            });

            // generate for this seller
            $this->generateSellerToBuyerInvoice($order, $seller, $mappedItems, $extension);
            $this->generatePlatformToSellerInvoice($order, $seller, $mappedItems, $extension);
        }

        // generate platform to buyer
        $allMappedItems = collect($costData['items'])->map(function($extItem) {
             $orderItem = \App\Models\OrderItem::find($extItem['item_id']);
             return (object)[
                    'cloth' => $orderItem->cloth,
                    'base_rent' => $extItem['pricing']['base_rent'],
                    'rent_gst' => $extItem['pricing']['rent_gst'],
                    'is_seller_gst' => $orderItem->is_seller_gst,
                    'buyer_commission' => $extItem['pricing']['buyer_comm'],
                    'buyer_commission_gst' => $extItem['pricing']['buyer_comm_gst'],
                    'is_extension' => true
                ];
        });
        $this->generatePlatformToBuyerInvoice($order, $allMappedItems, $extension);
    }

    protected function generateSellerToBuyerInvoice(Order $order, $seller, $items, $extension = null)
    {
        // Calculate totals
        $totalBase = 0;
        $totalTax = 0;
        $totalAmount = 0;

        foreach ($items as $item) {
            $base = (float) $item->base_rent;
            $tax = (float) $item->rent_gst;
            $itemTax = $item->is_seller_gst ? $tax : 0;
            
            $totalBase += $base;
            $totalTax += $itemTax;
            $totalAmount += ($base + $itemTax);
        }

        $isExt = !is_null($extension);
        $prefix = $isExt ? 'EXT-' : 'INV-';
        $invoiceNumber = $prefix . strtoupper(Str::random(8)); 
        
        $pdf = Pdf::loadView('invoices.seller_to_buyer', compact('order', 'seller', 'items', 'totalBase', 'totalTax', 'totalAmount', 'invoiceNumber', 'isExt', 'extension'));
        
        $dir = 'invoices/' . $order->id;
        $path = $dir . '/' . $invoiceNumber . '_seller_buyer.pdf';
        Storage::put('public/' . $path, $pdf->output());

        Invoice::create([
            'order_id' => $order->id,
            'order_extension_id' => $extension?->id,
            'invoice_number' => $invoiceNumber,
            'type' => 'rent_sale',
            'amount' => $totalAmount,
            'tax_amount' => $totalTax,
            'pdf_path' => $path,
            'issued_by_id' => $seller->id,
            'issued_to_id' => $order->buyer_id,
        ]);
    }

    protected function generatePlatformToSellerInvoice(Order $order, $seller, $items, $extension = null)
    {
        // Calculate totals (Commission from Seller)
        $totalComm = 0;
        $totalCommGst = 0;
        $totalTcs = 0;

        foreach ($items as $item) {
            $comm = (float) $item->seller_commission;
            $gst = (float) $item->seller_commission_gst;
            $tcs = (float) ($item->tcs_amount ?? 0);

            $totalComm += $comm;
            $totalCommGst += $gst;
            $totalTcs += $tcs;
        }

        $totalAmount = $totalComm + $totalCommGst;

        $isExt = !is_null($extension);
        $prefix = $isExt ? 'GR-EXT-S-' : 'GR-S-';
        $invoiceNumber = $prefix . strtoupper(Str::random(8));
        
        $pdf = Pdf::loadView('invoices.platform_to_seller', compact('order', 'seller', 'items', 'totalComm', 'totalCommGst', 'totalTcs', 'totalAmount', 'invoiceNumber', 'isExt', 'extension'));

        $path = 'invoices/' . $order->id . '/' . $invoiceNumber . '_platform_seller.pdf';
        Storage::put('public/' . $path, $pdf->output());

        Invoice::create([
            'order_id' => $order->id,
            'order_extension_id' => $extension?->id,
            'invoice_number' => $invoiceNumber,
            'type' => 'platform_fee_seller',
            'amount' => $totalAmount,
            'tax_amount' => $totalCommGst,
            'pdf_path' => $path,
            'issued_by_id' => null, // Platform
            'issued_to_id' => $seller->id,
        ]);
    }

    protected function generatePlatformToBuyerInvoice(Order $order, $items, $extension = null)
    {
        // Calculate totals (Commission from Buyer + Unregistered Seller Fee)
        $totalComm = 0;
        $totalCommGst = 0;
        $totalOtherFees = 0; 

        foreach ($items as $item) {
            $comm = (float) $item->buyer_commission;
            $gst = (float) $item->buyer_commission_gst;
            $otherFee = (!$item->is_seller_gst) ? (float) $item->rent_gst : 0;

            $totalComm += $comm;
            $totalCommGst += $gst;
            $totalOtherFees += $otherFee;
        }

        $totalAmount = $totalComm + $totalCommGst + $totalOtherFees;

        $isExt = !is_null($extension);
        $prefix = $isExt ? 'GR-EXT-B-' : 'GR-B-';
        $invoiceNumber = $prefix . strtoupper(Str::random(8));
        $buyer = $order->buyer;
        
        $pdf = Pdf::loadView('invoices.platform_to_buyer', compact('order', 'buyer', 'items', 'totalComm', 'totalCommGst', 'totalOtherFees', 'totalAmount', 'invoiceNumber', 'isExt', 'extension'));

        $path = 'invoices/' . $order->id . '/' . $invoiceNumber . '_platform_buyer.pdf';
        Storage::put('public/' . $path, $pdf->output());

        Invoice::create([
            'order_id' => $order->id,
            'order_extension_id' => $extension?->id,
            'invoice_number' => $invoiceNumber,
            'type' => 'platform_fee_buyer',
            'amount' => $totalAmount,
            'tax_amount' => $totalCommGst,
            'pdf_path' => $path,
            'issued_by_id' => null, // Platform
            'issued_to_id' => $order->buyer_id,
        ]);
    }
}
