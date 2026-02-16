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

The report processes data at the **Order Item** level. Each row in the table represents an individual item within an order.

### A. Pricing Data Source
The system uses the `OrderItem` financial snapshots (created during checkout) to ensure report accuracy even if product prices change later.

#### Formulas for Rental Items:
| Field | Formula / Logic |
| :--- | :--- |
| **Base Rent** | The core price of the rental. |
| **Rent Payable to Seller** | `Base Rent - Seller Commission (20%)` |
| **Rent Receivable from Buyer** | `Base Rent + Buyer Commission (20%)` |
| **Security** | Refundable deposit amount. |

#### Formulas for Platform Revenue:
| Component | Logic |
| :--- | :--- |
| **Seller Commission** | 20% of Base Rent. |
| **Buyer Commission** | 20% of Base Rent. |
| **Total Revenue** | `Seller Commission + Buyer Commission` (Total 40% of Base Rent) |

---

### B. Expense Tracking
To calculate true net profit, the system tracks operational costs (placeholders based on system averages):
- **Payment Gateway (PG) Expense**: Set to **₹30** per transaction.
- **Delivery Cost**: Set to **₹80** per transaction.
- **Total Expense**: `₹30 (PG) + ₹80 (Delivery) = ₹110`.

---

### C. Payout & Refund Dates
The report predicts when money should move:
- **Date Payable to Seller**: 7 days after the order status becomes `Delivered`.
- **Date Security Payable to Buyer**: 3 days after the order status becomes `Returned`.

---

## 4. Calculation Example (Base Rent: ₹1,000)

The following table explains how every column in the report is derived using a standard **₹1,000** rental example.

| Component | Column Name | Formula / Logic | Example (₹1,000) |
| :--- | :--- | :--- | :--- |
| **Input** | Base Rent | Seller entered price | **₹1,000.00** |
| **Payout** | Rent payable to seller | `Base Rent - Seller Comm` | **₹800.00** |
| **Inflow** | Rent receivable from buyer | `Base Rent + Buyer Comm` | **₹1,200.00** |
| **Revenue** | Commission from seller | `Base Rent * 20%` | **₹200.00** |
| **Revenue** | Commission from buyer | `Base Rent * 20%` | **₹200.00** |
| **Revenue** | **Total Revenue** | `Seller Comm + Buyer Comm` | **₹400.00** |
| **Expense** | PG Expense | System Placeholder | **₹30.00** |
| **Expense** | Delivery Cost | System Placeholder | **₹80.00** |
| **Profit** | **Net Profit** | `Total Revenue - Expenses` | **₹290.00** |

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
| **Base Rent** | The rental price of the item. |
| **Rent GST** | The GST component of the rental. |
| **Security** | The refundable security deposit. |
| **Commission from Seller** | The total cut taken from the seller's side (including tax/TCS). |
| **Commission from Buyer** | The markup paid by the buyer above the base rent. |
| **Net Profit** | The final profit for the platform after all payouts and expenses. |

---
*Created on: 2026-02-16*
