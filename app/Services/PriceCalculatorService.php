<?php

namespace App\Services;

use App\Models\Cloth;

class PriceCalculatorService
{
    /**
     * Calculate all price components for a rental.
     * 
     * @param Cloth $cloth
     * @param int $days
     * @return array
     */
    public function calculate(Cloth $cloth, int $days)
    {
        $seller = $cloth->user;
        
        // 1. Base Rent Calculation (4-day rule)
        // Rent price is for 4 days.
        $baseRentFor4Days = (float) $cloth->rent_price;
        $dailyRate = $baseRentFor4Days / 4;
        
        $baseRent = $baseRentFor4Days;
        if ($days > 4) {
            $extraDays = $days - 4;
            $baseRent += $extraDays * $dailyRate;
        }
        
        // 2. Platform Fees (20/20 model)
        $buyerComm = $baseRent * 0.20;
        $sellerComm = $baseRent * 0.20;
        
        // 3. Tax Logic
        $isSellerGst = $seller && $seller->is_gst;
        
        $rentGst = $isSellerGst ? ($baseRent * 0.18) : 0;
        
        // GST on commissions is always 18%
        $buyerCommGst = $buyerComm * 0.18;
        $sellerCommGst = $sellerComm * 0.18;
        
        $totalCommGst = $buyerCommGst + $sellerCommGst;
        
        // 4. TCS (1% of base rent for GST registered sellers)
        $tcs = $isSellerGst ? ($baseRent * 0.01) : 0;
        
        // 5. Final Aggregates
        // Total for Buyer: Base Rent + Buyer Comm + GST on Rent (if applicable) + GST on Buyer Comm
        $totalBuyerPay = $baseRent + $buyerComm + $rentGst + $buyerCommGst;
        
        // Net for Seller: (Base Rent + GST on Rent) - (Seller Comm + GST on Seller Comm + TCS)
        $netSellerPayout = ($baseRent + $rentGst) - ($sellerComm + $sellerCommGst + $tcs);
        
        return [
            'base_rent' => round($baseRent, 2),
            'buyer_comm' => round($buyerComm, 2),
            'seller_comm' => round($sellerComm, 2),
            'rent_gst' => round($rentGst, 2),
            'commission_gst' => round($totalCommGst, 2),
            'buyer_comm_gst' => round($buyerCommGst, 2),
            'seller_comm_gst' => round($sellerCommGst, 2),
            'tcs' => round($tcs, 2),
            'is_seller_gst' => (bool)$isSellerGst,
            'total_buyer_pay' => round($totalBuyerPay, 2),
            'net_seller_payout' => round($netSellerPayout, 2)
        ];
    }
}
