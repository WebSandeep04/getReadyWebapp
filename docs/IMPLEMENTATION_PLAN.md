# Technical Implementation Plan: Pricing & Tax Engine

This document outlines the step-by-step technical implementation of the new pricing/commission structure (20/20 model with GST/TCS) into the Laravel project.

## 1. Database Layer Enhancements
To ensure financial auditing can be performed retroactively (even if commission rates change in the future), we must store a "snapshot" of the pricing at the time of the order.

### Migration: Updating `order_items`
We will add the following columns to the `order_items` table:
- **`base_rent`**: The original rent price entered by the seller (for the duration).
- **`buyer_commission`**: Fee paid by the buyer (20% of base).
- **`seller_commission`**: Fee paid by the seller (20% of base).
- **`rent_gst`**: 18% GST on `base_rent` (if seller is GST registered).
- **`commission_gst`**: Total 18% GST on (buyer_comm + seller_comm).
- **`tcs_amount`**: 1% TCS on `base_rent`.
- **`is_seller_gst`**: Boolean flag at time of order.
- **`total_price`**: Final amount to be paid by the buyer for this item (excluding security).

---

## 2. Centralized Pricing Service
To make the system scalable and prevent "copy-paste" logic between the Frontend (JS) and Backend (PHP), we will implement a `PriceCalculatorService`.

### Logic Blueprint (`app/Services/PriceCalculatorService.php`)
```php
public function calculate(Cloth $cloth, int $days) {
    $seller = $cloth->user;
    
    // 1. Base Rent Calculation (4-day rule)
    $baseRent = $this->calculateBaseRent($cloth->rent_price, $days);
    
    // 2. Platform Fees
    $buyerComm = $baseRent * 0.20;
    $sellerComm = $baseRent * 0.20;
    
    // 3. Tax Logic
    $rentGst = $seller->is_gst ? ($baseRent * 0.18) : 0;
    $commGst = ($buyerComm + sellerComm) * 0.18;
    $tcs = $seller->is_gst ? ($baseRent * 0.01) : 0;
    
    // 4. Final Aggregates
    return [
        'base_rent' => $baseRent,
        'buyer_comm' => $buyerComm,
        'seller_comm' => $sellerComm,
        'rent_gst' => $rentGst,
        'comm_gst' => $commGst,
        'tcs' => $tcs,
        'total_buyer_pay' => $baseRent + $buyerComm + $rentGst + ($buyerComm * 0.18),
        'net_seller_payout' => ($baseRent + $rentGst) - ($sellerComm + ($sellerComm * 0.18) + $tcs)
    ];
}
```

---

## 3. Order Placement Flow
### Phase 1: Checkout Logic
In `CheckoutController.php`, the `createOrder` method will be updated:
1. Fetch items from the cart.
2. For each item, call the `PriceCalculatorService`.
3. Create the `OrderItem` records with the full tax breakdown saved in the new columns.

### Phase 2: Invoicing
Since GR acts as a marketplace:
- Generate **Invoice A** (Platform Fee): GR to Buyer.
- Generate **Invoice B** (Rent): Seller to Buyer (facilitated by GR).
- Generate **Invoice C** (Platform Fee): GR to Seller.

---

## 4. Admin Reporting Refactoring
The current `ReportController.php` uses "heuristics" (guesses) to calculate profit. This will be replaced with direct database queries:

**Current Problem:**
```php
// Heuristic to detect Purchase vs Rental
$isPurchase = $order->has_purchase_items && ...;
```

**Scalable Solution:**
```php
$stats = OrderItem::whereBetween('created_at', [$from, $to])
    ->selectRaw("
        SUM(buyer_commission) + SUM(seller_commission) as gross_revenue,
        SUM(commission_gst) as total_tax_liability,
        SUM(tcs_amount) as total_tcs_collected
    ")->first();
```

---

## 5. Security Deposit Handling
The `security_deposit` will remain a separate, non-taxable entity stored in the `orders` table. It will not be part of the "revenue" (commissionable) amount.

---

## 6. Implementation Checklist
1. [ ] Create Migration to add columns to `order_items`.
2. [ ] Develop `PriceCalculatorService`.
3. [ ] Integrate Service into `CartController` (for "Marketplace Display").
4. [ ] Integrate Service into `CheckoutController` (for "Final Payment").
5. [ ] Update `resources/views/clothes/show.blade.php` JS to match the Service logic exactly.
6. [ ] Update `Admin/ReportController` to utilize the new database columns.
