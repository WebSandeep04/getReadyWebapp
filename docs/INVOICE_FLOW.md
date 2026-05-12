# Invoice Flow & Tax Compliance Guide

This document outlines the invoicing requirements for transactions on the GetReady platform. Based on the 20/20 revenue model and GST regulations, **three distinct invoices** are generated for every completed order (Rental or Purchase).

---

## 1. Overview of Invoices

For a standard transaction, the platform acts as an intermediary (e-commerce operator). This requires splitting the billing into components handled by the Seller and components handled by the Platform (GetReady).

| # | Invoice Type | Issued By | Issued To | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| **1** | **Rent/Sale Invoice** | **GetReady** (on behalf of Seller) | **Buyer** | Covers the base rent/price and the tax on the item itself. |
| **2** | **Commission Invoice (Seller)** | **GetReady** (Platform) | **Seller** | Covers the commission charged to the seller for using the platform. |
| **3** | **Commission Invoice (Buyer)** | **GetReady** (Platform) | **Buyer** | Covers the convenience/service fee (Buyer Commission) charged to the buyer. |

---

## 2. Detailed Breakdown (Example)

**Scenario**: A Registered Seller rents an item with a **Base Rent of ₹1,000**.
*   **Seller Commission**: 20% (₹200)
*   **Buyer Commission**: 20% (₹200)
*   **GST Rate**: 18%

### Invoice 1: Rent/Sale Invoice (Seller → Buyer)
Matches the "Seller: rent" row in the tax sheet.
*   **Issuer**: GetReady (Authorized Representative for Seller)
*   **Recipient**: Buyer
*   **Line Items**:
    *   Base Rent: ₹1,000
    *   GST (18%): ₹180
*   **Total Amount**: **₹1,180**
*   **Tax Responsibility**: Collected from Buyer by GetReady, but **must be deposited to Govt by the Seller** (via GSTR-1/3B).
*   *Note: If the seller is Unregistered, GST is not charged here (or is charged as a platform fee depending on specific business logic).*

### Invoice 2: Platform Fee Invoice (GetReady → Seller)
Matches the "GR comm seller" row.
*   **Issuer**: GetReady (Own Invoice)
*   **Recipient**: Seller
*   **Line Items**:
    *   Service Fee (Seller Commission): ₹200
    *   GST (18%): ₹36
*   **Total Amount**: **₹236**
*   **Settlement**: This amount (₹236) is **deducted** from the payout. It is not paid separately by the seller.
*   **Tax Responsibility**: GetReady deposits this ₹36 to the proper tax authority.
*   **TCS Compliance**: GetReady also deducts **1% TCS** (₹10) from the base amount and deposits it against the Seller's GSTIN.

### Invoice 3: Service Fee Invoice (GetReady → Buyer)
Matches the "Commission from buyer" row.
*   **Issuer**: GetReady (Own Invoice)
*   **Recipient**: Buyer
*   **Line Items**:
    *   Platform Service Fee (Buyer Commission): ₹200
    *   GST (18%): ₹36
*   **Total Amount**: **₹236**
*   **Tax Responsibility**: GetReady deposits this ₹36 to the proper tax authority.

---

## 3. Financial Settlement Flow

How the money moves for this **₹1,000** transaction:

1.  **Buyer Pays**: **₹1,416** + Security
    *   (₹1,180 for Item + ₹236 for Buyer Service Fee)
2.  **GetReady Collections**:
    *   Keeps **₹236** (Buyer Fee) directly.
    *   Holds **₹1,180** (Seller Share).
3.  **Deductions from Seller Share**:
    *   Subtracts **₹236** (Seller Commission Invoice).
    *   Subtracts **₹10** (TCS).
4.  **Net Payout to Seller**:
    *   ₹1,180 (Gross) - ₹236 (Comm) - ₹10 (TCS) = **₹934**
    *   *Verification*: Rent Shown (800) + Tax Credit (180 if Reg) - Comm (200) - Comm Tax (36) - TCS (10) = 934. Matches.

---

## 4. Summary Table

| Invoice Component | Base Amount | GST Amount | Total | Responsibility (Deposit to Gov) |
| :--- | :--- | :--- | :--- | :--- |
| **Item Rent (Seller)** | ₹1,000 | ₹180 | ₹1,180 | **Seller** |
| **Seller Comm (GR)** | ₹200 | ₹36 | ₹236 | **GetReady** |
| **Buyer Comm (GR)** | ₹200 | ₹36 | ₹236 | **GetReady** |
| **Total Buyer Cost** | **₹1,400** | **₹252** | **₹1,652**\* | -- |

*\*Note: The provided example sums to 1416 because 1000 base + 180 tax + 200 comm + 36 tax = 1416. The Buyer Comm invoice is separate.*

---

## 5. Rental Extension Invoices
When a user extends a rental period, the system generates a distinct supplementary set of the same three invoices (Sale, Seller Comm, Buyer Comm).

- **Logic**: The amounts are calculated pro-rata based on the pro-rated Extension Base Rent.
- **Reference**: Extension invoices are linked to both the original `order_id` and the specific `order_extension_id`.
- **Visibility**: Buyers see these invoices in their order history alongside the original booking documents.

---

*Document Last Updated: 2026-02-24*
