# Admin Financial Report Documentation

This document explains the logic, calculations, and interface of the **Financial Report** located at `/admin/reports/financial`. The report provides a granular view of every transaction, detailing revenue, payouts, expenses, and net profitability.

---

## 1. Overview
The Financial Report is designed for administrators to track the flow of money for every order. It distinguishes between **Rental** and **Purchase** transactions and calculates the platform's net profit after accounting for seller payouts, buyer commissions, and operational expenses.

---

## 2. Interface Components

### A. Summary Statistics (Top Cards)
The top of the page displays four key performance indicators:
- **Total Platform Revenue**: Sum of all gross commissions (Seller + Buyer) collected.
- **Total Security Held**: Total refundable security deposits currently in the system for the filtered range.
- **Total Payouts**: Combined amount of "Rent payable to seller" across all items, plus any manual refunds processed.
- **Net Platform Profit**: Total Revenue minus all tracked expenses (PG fees, Delivery, etc.).

### B. Date Filter
Allows administrators to filter report data based on the **Order Creation Date**.

---

## 3. Data Logic & Calculations

The report processes data at the **Order Item** level, using the stored financial snapshots in `order_items` (e.g., `base_rent`, `buyer_commission`, `rent_gst`).

### A. Pricing Components (20/20 Model)
| Field | Formula / Logic |
| :--- | :--- |
| **Base Price** | Seller entered price (Rent or Selling Price). |
| **Item Tax / Fee** | 18% of Base Price (Always charged to Buyer). |
| **Buyer Commission** | 20% of Base Price. |
| **Seller Commission** | 20% of Base Price. |
| **Comm GST** | 18% on both commissions. |
| **TCS** | 1% of Base (Deducted if Seller GST Registered). |

### B. Revenue & Payout Formulas
| Metric | Formula |
| :--- | :--- |
| **Total Inflow (Buyer Pays)** | `Base + Item Tax + Buyer Comm + Comm GST` |
| **Net Payout (Seller Earns)** | `Base + Item Tax (if Reg) - Seller Comm - Comm GST - TCS` |
| **Platform Revenue** | `Buyer Comm + Seller Comm + Item Tax (if Unreg)` |
| **Net Profit** | `Platform Revenue - Expenses (PG + Delivery)` |

---

## 4. Calculation Example (Base Price: ₹1,000)

The following table explains the derivation for a standard **₹1,000** transaction (Rent or Buy).

| Component | Seller: **Unregistered** | Seller: **Registered** |
| :--- | :--- | :--- |
| **BUYER PAYS** | **₹1,416** | **₹1,416** |
| (Base + Tax + Comm + GST) | (1000 + 180 + 200 + 36) | (1000 + 180 + 200 + 36) |
| | | |
| **SELLER EARNS** | **₹764** | **₹934** |
| (Base + TaxCredit - Deductions) | (1000 + 0 - 236) | (1000 + 180 - 246) |
| | | |
| **PLATFORM REVENUE** | **₹580** | **₹400** |
| (Gross Comm + Retained Fee) | (400 + 180) | (400 + 0) |
| | | |
| **EXPENSES** | **₹110** | **₹110** |
| (PG ₹30 + Delivery ₹80) | | |
| | | |
| **NET PROFIT** | **₹470** | **₹290** |

---

## 5. Technical Implementation

### File Locations
- **Controller**: `app/Http/Controllers/Admin/ReportController.php` (Method: `financial`)
- **View**: `resources/views/admin/screens/reports/financial.blade.php`
- **Route**: `Route::get('/admin/reports/financial', [ReportController::class, 'financial'])`

### Data Fetching
The report uses Eloquent eager loading to minimize database queries:
```php
Order::with(['buyer', 'items.cloth', 'payments', 'shipments']);
```

### Calculation Pivot
The system checks if the `OrderItem` has the `base_rent` column filled. If not (for older orders), it falls back to a legacy calculation based on current product prices.

---

## 5. Column Reference Table

| Column Name | Description |
| :--- | :--- |
| **ORDER ID / Date** | System Order ID (GR-XXXXX) and the date it was placed. |
| **Type** | "Rental" or "Purchase" badge. |
| **MRP** | Manufacturer's Suggested Retail Price of the item. |
| **Base rent** | The base price (Rent or Selling Price) set by the seller. |
| **Rent GST** | The Item Tax / Fee (18% of Base). |
| **Security** | The refundable security deposit (0 for Purchases). |
| **Seller See** | `Base Price - Seller Comm` (Simplified Gross View). |
| **Buyer See** | `Base Price + Buyer Comm` (Simplified Gross View). |
| **Commission from Seller** | The total cut taken from the seller's side. |
| **Commission from Buyer** | The markup paid by the buyer above the base rent. |
| **Net Profit** | The final profit for the platform after all payouts and expenses. |

---
*Created on: 2026-02-16*
