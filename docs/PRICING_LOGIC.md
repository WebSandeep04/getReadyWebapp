# Product Pricing & Revenue Logic Documentation

This document outlines the complete logic for product pricing, rental calculations, and revenue distribution.

## 1. Internal Constants (20/20 Revenue Model)
- **Seller Commission Rate**: 20% of the rent entered by the seller (Deducted from payout).
- **Buyer Commission Rate**: 20% of the rent entered by the seller (Added to the display price).
- **Total Platform Revenue**: **40% of the Base Rent** (e.g., ₹200 + ₹200 = ₹400 on a ₹1,000 rent).
- **GST Rate (Rent)**: 18% (Applicable only for GST Registered Sellers).
- **GST Rate (Commission)**: 18% (Applicable to all commission fees).
- **Minimum Rental Period**: 4 Days.

---

## 2. Case 1: GST Registered Seller (`is_gst = 1`)
When a seller is registered under GST, taxes are applied to both the rent and the platform fees.

### A. Prices & Display
- **Seller Input (Rent)**: ₹1,000
- **Marketplace Display (Buyer)**: **₹1,200** (Rent ₹1,000 + Buyer Commission ₹200)
- **Seller Dashboard View**: **₹800** (Rent ₹1,000 - Seller Commission ₹200)

### B. Buyer Checkout Breakdown
| Component | Amount | Logic |
| :--- | :--- | :--- |
| **Base Rent** | ₹1,000 | Seller Entered Price |
| **Buyer Commission** | ₹200 | 20% of Base Rent |
| **GST on Rent (18%)** | ₹180 | Collected for Seller |
| **GST on Buyer Comm (18%)** | ₹36 | 18% of ₹200 |
| **Total (Excl. Security)** | **₹1,416** | 1000 + 200 + 180 + 36 |
| **Security Deposit** | ₹1,000 | Refundable |

---

## 3. Case 2: Non-GST Registered Seller (`is_gst = 0`)
When a seller is NOT registered, no GST is charged on the rent portion.

### A. Prices & Display
- **Seller Input (Rent)**: ₹1,000
- **Marketplace Display (Buyer)**: **₹1,200** (Rent ₹1,000 + Buyer Commission ₹200)
- **Seller Dashboard View**: **₹800** (Rent ₹1,000 - Seller Commission ₹200)

### B. Buyer Checkout Breakdown
| Component | Amount | Logic |
| :--- | :--- | :--- |
| **Base Rent** | ₹1,000 | Seller Entered Price |
| **Buyer Commission** | ₹200 | 20% of Base Rent |
| **GST on Rent** | ₹0 | Not Applicable |
| **GST on Buyer Comm (18%)** | ₹36 | 18% of ₹200 |
| **Total (Excl. Security)** | **₹1,236** | 1000 + 200 + 36 |
| **Security Deposit** | ₹1,000 | Refundable |

---

## 4. Rental Duration & Extensions
The logic above applies to the **Base Period (4 Days)**. For longer durations:
- **Rent Extension**: `Additional Days * (Rent / 4)`
- All commissions and GST calculations apply to the **Total Rental Cost** (Base + Extension).

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
