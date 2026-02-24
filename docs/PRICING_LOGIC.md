# Product Pricing & Revenue Logic Documentation

This document outlines the complete logic for product pricing, rental calculations, and revenue distribution.

## 1. Internal Constants (20/20 Revenue Model)
- **Seller Commission Rate**: 20% of the Base Price (Deducted from payout).
- **Buyer Commission Rate**: 20% of the Base Price (Added to the total).
- **Item Tax / Fee**: **18% of the Base Price** (Charged to Buyer).
    - If Seller is **GST Registered**: This is "IGST/CGST" credited to the Seller.
    - If Seller is **Unregistered**: This is a "Service Fee" retained by the Platform.
- **Commission GST**: 18% (Applicable to all commission fees).
- **TCS**: 1% (Tax Collected at Source, deducted from GST registered sellers).

---

## 2. Universal Pricing Structure
The platform ensures a consistent price for the buyer regardless of the seller's tax status.

### A. For Buyers
The price breakdown is always:
1.  **Base Price** (Set by Seller)
2.  **(+) Buyer Commission** (20% of Base)
3.  **(+) Item Tax / Fee** (18% of Base)
4.  **(+) GST on Buyer Comm** (18% of Comm)
5.  **(=) Total Payable**

### B. For Sellers
The payout depends on registration status:
1.  **Base Price**
2.  **(+) Item Tax Credit** (18% of Base) *[Only if GST Registered]*
3.  **(-) Seller Commission** (20% of Base)
4.  **(-) GST on Seller Comm** (18% of Comm)
5.  **(-) TCS** (1% of Base) *[Only if GST Registered]*
6.  **(=) Net Payout**

---

## 3. Calculation Examples (Base Price: ₹1,000)

### Case 1: GST Registered Seller (`is_gst = 1`)
*   **Buyer Pays**: ₹1,000 + ₹200 (Comm) + ₹180 (Item Tax) + ₹36 (Comm GST) = **₹1,416**
*   **Seller Earns**: (₹1,000 + ₹180) - (₹200 + ₹36 + ₹10) = **₹934**
*   **Platform Net**: ₹400 (Gross Comm) - Expenses.

### Case 2: Non-GST Registered Seller (`is_gst = 0`)
*   **Buyer Pays**: ₹1,000 + ₹200 (Comm) + ₹180 (Service Fee) + ₹36 (Comm GST) = **₹1,416**
*   **Seller Earns**: ₹1,000 - (₹200 + ₹36) = **₹764**
*   **Platform Net**: ₹400 (Gross Comm) + ₹180 (Service Fee) - Expenses.

---

## 4. Rental vs Purchase Context
The logic above applies to both:
*   **Rent**: Base Price = Rent for 4 Days.
*   **Purchase**: Base Price = Selling Price.

---

## 5. Rental Extensions
Rental extensions are calculated using the "4-day base rent model" pro-rata.

1.  **Extension Base Rent**: `(Original Base Rent / 4) * Extra Days`.
2.  **Logic**: All commissions (Buyer/Seller) and taxes (GST) are recalculated based on this new Extension Base Rent, following the exact same rules defined in Section 2.
3.  **Storage**: These details are stored in the `order_extensions` table and aggregated for Payouts and Payments.

---
---

## 6. Returns, Refunds & Disputed Orders

The system distinguishes between a successful rental completion and a disputed order (Issue Reported).

### A. Standard Rental Return (Success)
*   **Rent & Fees**: Platform keeps commission; Seller receives payout.
*   **Security Deposit**: Refunded 100% to Buyer (via Security Dashboard).
*   **Payout Status**: Becomes "Eligible for Payout" once status is `Returned`.

### B. Dispute / Issue Reported (Fault)
If a buyer reports an issue (Wrong Item/Damaged) and the Admin approves:
*   **Full Refund**: The buyer receives a 100% refund of **Rent + GST + Security**.
*   **Seller Payout**: Automatically set to **₹0**. The order is excluded from all payout reports.
*   **Workflow**: 
    1. Admin approves return -> Reverse AWB generated.
    2. Item reaches Seller -> Status becomes `Returned`.
    3. Admin clicks **"Refund All"** -> Logic reverts completely (Stock restored, Buyer refunded).

### C. Financial Summary (Post-Refund)
| Component | Standard Return | Approved Issue Return |
| :--- | :--- | :--- |
| **Buyer Status** | Security Back | **Full Payment Back** |
| **Seller Status** | Rent Received | **No Payment (fault)** |
| **Platform Status** | Commission Kept | **Fee Reversed** |
| **Stock Status** | Available (+1) | Available (+1) |

---

## 7. Transaction Reporting (Credits & Debits)
To provide a secure and transparent financial history, the platform logs every inward and outward movement for both Buyers and Sellers.

### A. For Buyers (Debits & Security Credits)
- **Debit**: When an order is placed or extended (Razorpay payment).
- **Credit**: When a security deposit is returned by the Admin (marked as `is_security_returned = true`).

### B. For Sellers (Credit Earnings)
- **Credit**: When an order payout is processed (marked as `is_seller_paid = true`).
- **Calculation**: The amount shown is the **Net Payout** (Base Price - Fees) as defined in Section 2B. This includes earnings from both original rentals and extensions.

👉 **Viewable at**: `/transactions` (Personal Dashboard)
