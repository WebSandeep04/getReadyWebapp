<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderExtension;
use App\Models\OrderItem;
use Carbon\Carbon;

class ExtensionService
{
    protected $availabilityService;
    protected $priceService;

    public function __construct(AvailabilityService $availabilityService, PriceCalculatorService $priceService)
    {
        $this->availabilityService = $availabilityService;
        $this->priceService = $priceService;
    }

    /**
     * Validate if an extension is possible.
     */
    public function validateAvailability(Order $order, int $extraDays): bool
    {
        $newRentalTo = Carbon::parse($order->rental_to)->addDays($extraDays);
        
        // The extension period is [current_rental_to + 1, new_rental_to + 1]
        // The +1 at the end is for the new pickup buffer.
        $checkStart = Carbon::parse($order->rental_to)->addDay();
        $checkEnd = $newRentalTo->copy()->addDay();

        foreach ($order->items as $item) {
            if ($item->purchase_type === 'rent') {
                if (!$this->availabilityService->isAvailable($item->cloth, $checkStart, $checkEnd, $order->id)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Calculate cost for the extension.
     */
    public function calculateExtensionCost(Order $order, int $extraDays): array
    {
        $aggregates = [
            'total_additional_amount' => 0,
            'base_rent_amount' => 0,
            'buyer_commission' => 0,
            'seller_commission' => 0,
            'rent_gst' => 0,
            'buyer_commission_gst' => 0,
            'seller_commission_gst' => 0,
            'seller_net_amount' => 0,
        ];
        $breakdown = [];

        foreach ($order->items as $item) {
            if ($item->purchase_type === 'rent') {
                $pricing = $this->priceService->calculateExtension($item->cloth, $extraDays);
                
                $aggregates['total_additional_amount'] += $pricing['total_buyer_pay'];
                $aggregates['base_rent_amount'] += $pricing['base_rent'];
                $aggregates['buyer_commission'] += $pricing['buyer_comm'];
                $aggregates['seller_commission'] += $pricing['seller_comm'];
                $aggregates['rent_gst'] += $pricing['rent_gst'];
                $aggregates['buyer_commission_gst'] += $pricing['buyer_comm_gst'];
                $aggregates['seller_commission_gst'] += $pricing['seller_comm_gst'];
                $aggregates['seller_net_amount'] += $pricing['net_seller_payout'];

                $breakdown[] = [
                    'item_id' => $item->id,
                    'cloth_title' => $item->cloth->title,
                    'pricing' => $pricing
                ];
            }
        }

        foreach ($aggregates as $key => $val) {
            $aggregates[$key] = round($val, 2);
        }

        return array_merge($aggregates, ['items' => $breakdown]);
    }

    /**
     * Process a paid extension.
     */
    public function processExtension(Order $order, OrderExtension $extension): void
    {
        // 1. Update Order's rental_to
        $oldRentalTo = $order->rental_to;
        $order->update([
            'rental_to' => $extension->new_rental_to
        ]);

        // 2. Update Availability Blocks for each item
        foreach ($order->items as $item) {
            if ($item->purchase_type === 'rent') {
                $this->availabilityService->extendBlockedDates(
                    $item->cloth, 
                    $oldRentalTo, 
                    $extension->new_rental_to, 
                    $order->id
                );
            }
        }

        // 3. Mark extension as paid
        $extension->update([
            'status' => 'paid'
        ]);

        // 4. Update Order Item financial details (Optional, but good for reporting)
        // Note: The architectural plan doesn't explicitly say to update OrderItem amounts,
        // but it says "Maintain a clear history of original bookings vs. extensions."
        // We have OrderExtension for that. 
    }
}
