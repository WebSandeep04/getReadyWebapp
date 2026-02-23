<?php

namespace App\Services;

use App\Models\Cloth;
use App\Models\AvailabilityBlock;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Block dates for a new rental order.
     */
    public function blockRentalDates(Cloth $cloth, $start, $end, int $orderId)
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        $fullBlockStart = $startDate->copy()->subDay();
        $fullBlockEnd = $endDate->copy()->addDay();

        // 1. Block Rental
        AvailabilityBlock::create([
            'cloth_id' => $cloth->id,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'type' => 'blocked',
            'reason' => 'Rented (Order #' . $orderId . ')'
        ]);

        // 2. Block Delivery Buffer
        AvailabilityBlock::create([
            'cloth_id' => $cloth->id,
            'start_date' => $fullBlockStart->format('Y-m-d'),
            'end_date' => $fullBlockStart->format('Y-m-d'),
            'type' => 'blocked',
            'reason' => 'Delivery buffer (Order #' . $orderId . ')'
        ]);

        // 3. Block Pickup Buffer
        AvailabilityBlock::create([
            'cloth_id' => $cloth->id,
            'start_date' => $fullBlockEnd->format('Y-m-d'),
            'end_date' => $fullBlockEnd->format('Y-m-d'),
            'type' => 'blocked',
            'reason' => 'Pickup buffer (Order #' . $orderId . ')'
        ]);

        $this->updateAvailableBlocks($cloth->id, $fullBlockStart, $fullBlockEnd);
    }

    /**
     * Extend blocked dates for an existing order.
     */
    public function extendBlockedDates(Cloth $cloth, $oldEnd, $newEnd, int $orderId)
    {
        $oldEndDate = Carbon::parse($oldEnd);
        $newEndDate = Carbon::parse($newEnd);
        
        // 1. Update/Remove old Pickup Buffer
        $oldPickupBufferDate = $oldEndDate->copy()->addDay()->format('Y-m-d');
        AvailabilityBlock::where('cloth_id', $cloth->id)
            ->where('start_date', $oldPickupBufferDate)
            ->where(function($q) use ($orderId) {
                $q->where('reason', 'like', '%Order #' . $orderId . '%')
                  ->orWhere('reason', 'Pickup buffer')
                  ->orWhere('reason', 'Pickup Buffer');
            })
            ->delete();

        // 2. Update the "Rented" block to include the new days
        $rentalBlock = AvailabilityBlock::where('cloth_id', $cloth->id)
            ->where('end_date', $oldEndDate->format('Y-m-d'))
            ->where('type', 'blocked')
            ->where('reason', 'like', 'Rented (Order #' . $orderId . ')%')
            ->first();

        if ($rentalBlock) {
            $rentalBlock->update([
                'end_date' => $newEndDate->format('Y-m-d')
            ]);
        } else {
            AvailabilityBlock::create([
                'cloth_id' => $cloth->id,
                'start_date' => $oldEndDate->copy()->addDay()->format('Y-m-d'),
                'end_date' => $newEndDate->format('Y-m-d'),
                'type' => 'blocked',
                'reason' => 'Rented Extension (Order #' . $orderId . ')'
            ]);
        }

        // 3. Create new Pickup Buffer at newEnd + 1
        $newPickupBufferDate = $newEndDate->copy()->addDay();
        AvailabilityBlock::create([
            'cloth_id' => $cloth->id,
            'start_date' => $newPickupBufferDate->format('Y-m-d'),
            'end_date' => $newPickupBufferDate->format('Y-m-d'),
            'type' => 'blocked',
            'reason' => 'Pickup buffer (Order #' . $orderId . ')'
        ]);

        // 4. Update available blocks for the newly blocked period
        $this->updateAvailableBlocks($cloth->id, $oldEndDate->copy()->addDay(), $newPickupBufferDate);
    }

    /**
     * Check if a specific period is available for a cloth.
     */
    public function isAvailable(Cloth $cloth, $start, $end, $excludeOrderId = null): bool
    {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->startOfDay();

        // 1. Prepare Order context if excluding
        $order = null;
        $orderBufferReasons = ['Pickup buffer', 'Delivery buffer'];
        $orderDates = [];
        
        if ($excludeOrderId) {
            $order = \App\Models\Order::find($excludeOrderId);
            if ($order) {
                $orderDates = [
                    Carbon::parse($order->rental_from)->subDay()->format('Y-m-d'),
                    Carbon::parse($order->rental_from)->format('Y-m-d'),
                    Carbon::parse($order->rental_to)->format('Y-m-d'),
                    Carbon::parse($order->rental_to)->addDay()->format('Y-m-d')
                ];
            }
        }

        // 2. Conflict Query (Blocked blocks)
        $query = AvailabilityBlock::where('cloth_id', $cloth->id)
            ->where('type', 'blocked');

        if ($excludeOrderId) {
            $query->where(function ($q) use ($excludeOrderId, $orderBufferReasons, $orderDates) {
                // Ignore blocks belonging to this order
                $q->where(function($sub) use ($excludeOrderId) {
                    $sub->where('reason', 'not like', '%Order #' . $excludeOrderId . '%')
                        ->where('reason', 'not like', '%Order#' . $excludeOrderId . '%')
                        ->where('reason', 'not like', '%(#' . $excludeOrderId . ')%');
                });
                
                // Broadly ignore buffers on order boundary dates
                if (!empty($orderDates)) {
                    $q->where(function ($sub) use ($orderDates) {
                        $sub->where(function($ss) {
                                $ss->where('reason', 'not like', '%buffer%')
                                   ->where('reason', 'not like', '%Buffer%');
                            })
                            ->orWhereNotIn('start_date', $orderDates);
                    });
                }
            });
        }

        $conflicts = $query->where(function ($q) use ($startDate, $endDate) {
                $s = $startDate->format('Y-m-d');
                $e = $endDate->format('Y-m-d');
                $q->whereBetween('start_date', [$s, $e])
                  ->orWhereBetween('end_date', [$s, $e])
                  ->orWhere(function ($sub) use ($s, $e) {
                      $sub->where('start_date', '<=', $s)
                          ->where('end_date', '>=', $e);
                  });
            })
            ->exists();

        if ($conflicts) return false;

        // 3. Coverage Verification
        $hasAnyAvailable = AvailabilityBlock::where('cloth_id', $cloth->id)->where('type', 'available')->exists();
        if (!$hasAnyAvailable) return true;

        $availableBlocks = AvailabilityBlock::where('cloth_id', $cloth->id)
            ->where('type', 'available')
            ->where(function ($q) use ($startDate, $endDate) {
                $s = $startDate->format('Y-m-d');
                $e = $endDate->format('Y-m-d');
                $q->whereBetween('start_date', [$s, $e])
                  ->orWhereBetween('end_date', [$s, $e])
                  ->orWhere(function ($sub) use ($s, $e) {
                      $sub->where('start_date', '<=', $s)
                          ->where('end_date', '>=', $e);
                  });
            })
            ->get();

        $ignoredBlocks = collect([]);
        if ($excludeOrderId) {
            $ignoredBlocks = AvailabilityBlock::where('cloth_id', $cloth->id)
                ->where('type', 'blocked')
                ->where(function ($q) use ($excludeOrderId, $orderDates) {
                    $q->where(function($sub) use ($excludeOrderId) {
                        $sub->where('reason', 'like', '%Order #' . $excludeOrderId . '%')
                            ->orWhere('reason', 'like', '%Order#' . $excludeOrderId . '%')
                            ->orWhere('reason', 'like', '%(#' . $excludeOrderId . ')%');
                    });
                    
                    if (!empty($orderDates)) {
                        $q->orWhere(function ($sub) use ($orderDates) {
                            $sub->where(function($ss) {
                                    $ss->where('reason', 'like', '%buffer%')
                                       ->orWhere('reason', 'like', '%Buffer%');
                                })
                                ->whereIn('start_date', $orderDates);
                        });
                    }
                })
                ->get();
        }

        $allCovering = $availableBlocks->concat($ignoredBlocks);
        return $this->checkCoverage($startDate, $endDate, $allCovering);
    }

    private function checkCoverage(Carbon $start, Carbon $end, $blocks): bool
    {
        if ($blocks->isEmpty()) return false;
        
        $merged = [];
        $sorted = $blocks->sortBy('start_date');

        $currentStart = Carbon::parse($sorted->first()->start_date);
        $currentEnd = Carbon::parse($sorted->first()->end_date);

        foreach ($sorted as $block) {
            $blockStart = Carbon::parse($block->start_date);
            $blockEnd = Carbon::parse($block->end_date);

            if ($blockStart->lte($currentEnd->copy()->addDay())) {
                $currentEnd = $currentEnd->max($blockEnd);
            } else {
                if ($currentStart->lte($start) && $currentEnd->gte($end)) return true;
                $currentStart = $blockStart;
                $currentEnd = $blockEnd;
            }
        }

        return $currentStart->lte($start) && $currentEnd->gte($end);
    }

    /**
     * Internal helper to update available blocks after blocking a period.
     */
    private function updateAvailableBlocks($clothId, Carbon $fullBlockStart, Carbon $fullBlockEnd)
    {
        $availableBlocks = AvailabilityBlock::where('cloth_id', $clothId)
            ->where('type', 'available')
            ->get();

        foreach ($availableBlocks as $available) {
            $availStart = Carbon::parse($available->start_date);
            $availEnd = Carbon::parse($available->end_date);

            if ($availStart->lte($fullBlockEnd) && $availEnd->gte($fullBlockStart)) {
                if ($fullBlockStart->lte($availStart) && $fullBlockEnd->gte($availEnd)) {
                    $available->delete();
                } elseif ($fullBlockStart->gt($availStart) && $fullBlockEnd->lt($availEnd)) {
                    AvailabilityBlock::create([
                        'cloth_id' => $clothId, 
                        'start_date' => $availStart->format('Y-m-d'), 
                        'end_date' => $fullBlockStart->copy()->subDay()->format('Y-m-d'), 
                        'type' => 'available', 
                        'reason' => $available->reason
                    ]);
                    $available->update(['start_date' => $fullBlockEnd->copy()->addDay()->format('Y-m-d')]);
                } elseif ($fullBlockEnd->gte($availStart) && $fullBlockEnd->lt($availEnd)) {
                    $available->update(['start_date' => $fullBlockEnd->copy()->addDay()->format('Y-m-d')]);
                } elseif ($fullBlockStart->gt($availStart) && $fullBlockStart->lte($availEnd)) {
                    $available->update(['end_date' => $fullBlockStart->copy()->subDay()->format('Y-m-d')]);
                }
            }
        }
    }
}
