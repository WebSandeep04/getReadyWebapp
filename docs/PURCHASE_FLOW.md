# Purchase Lifecycle Workflow

This document details the end-to-end flow for direct sales (Purchases) on the GetReady platform. Unlike the rental model, the purchase flow is a traditional e-commerce transaction focusing on permanent ownership transfer.

---

## 1. Item Listing & Eligibility
Sellers decide which items are available for direct sale during the listing process.

*   **Controller**: `ClothController@store` / `ClothController@update`
*   **Database Column**: `is_purchased` (Boolean)
*   **Logic**:
    *   If `is_purchased` is true, the seller must provide a `selling_price`.
    *   The system suggests `rent_price` be ~20% of `selling_price` for consistency.
    *   **SKU**: Direct sales are limited by the physical stock provided by the seller.

---

## 2. Selection & Cart
When a buyer chooses to purchase an item instead of renting it.

*   **Workflow**:
    1.  User clicks **"Buy Now"** on the product page.
    2.  AJAX request hits `CartController@addToCart` with `purchase_type = 'buy'`.
    3.  **Validation**: Ensures `SKU > 0` and the item is actually marked for purchase.
    4.  **Cart Entry**: A `cart_items` record is created. Unlike rentals, no `rental_start_date` or `rental_end_date` is required.

---

## 3. Order & Payment
The checkout process bundles items and handles payment types.

*   **Controller**: `CheckoutController@createOrder`
*   **Totals Calculation**:
    *   **Selling Price**: Added to subtotal.
    *   **Security Deposit**: **₹0** (No security is charged for purchases).
*   **Payment Methods**:
    *   **Online**: Payment status marked as **Paid** immediately after Razorpay success.
    *   **COD**: Payment status marked as **Pending**; order moves to **Confirmed** immediately.

---

## 4. Fulfillment (Forward Shipment)
Since there is no return leg, the shipping process involves only the delivery to the buyer.

*   **Process**: Triggered by `CheckoutController@processPostOrderTasks`.
*   **Logistics**: A **Forward Shipment** is created via the **Xpressbees API**.
*   **Inventory Adjustment**: The system decrements the `SKU` count for the item.
    *   If `SKU` becomes **0**, the item is marked `is_available = false` and hidden from the marketplace.
*   **Notification**: Both buyer (Order Confirmed) and seller (New Sale) are notified.

---

## 5. Webhook Tracking & Delivery
Real-time status updates from the courier.

*   **Controller**: `Api/XpressbeesWebhookController@handleWebhook`
*   **Logic**:
    *   When the courier status changes to **"Delivered"**:
        1.  Shipment `delivered_at` timestamp is set.
        2.  Order status transitions to **'Delivered'**.
        3.  **Payout Eligibility**: The order is automatically flagged as "Eligible for Payout" (subject to a 3-day cooling period for disputes).
*   **Note**: The automated `orders:process-returns` command specifically skips purchase items, as no reverse pickup is needed.

### 💰 Payout Timing Difference
*   **Rentals**: Seller is paid ONLY after the item is **Returned** and verified.
*   **Purchases**: Seller is paid after **Delivery** (No waiting for return).

---

## 6. Financial Settlement (20/20 Model)

Purchases now follow the exact same financial structure as rentals to ensure platform consistency and transparency.

*   **Seller Input**: The seller enters a **Base Selling Price** (e.g., ₹1,000).
*   **Seller View**: The seller sees **Base Price - 20% Commission**.
*   **Buyer View**: The buyer sees **Base Price + 20% Commission** (Marketplace Price).
*   **Taxes**:
    *   **Item Tax (18%)**: Always charged to the buyer on the Base Price.
        *   If Seller is **GST Registered**: Credited to Seller.
        *   If Seller is **Unregistered**: Retained by Platform as service fee.
    *   **Commission GST (18%)**: Charged on both Buyer and Seller commissions.
    *   **TCS (1%)**: Tax Collected at Source, deducted from GST registered sellers.

### 💰 Calculation Example (Base Selling Price: ₹1,000)

| Component | Calculation Logic | Value |
| :--- | :--- | :--- |
| **Seller Input** | Base Selling Price | **₹1,000** |
| | | |
| **SELLER SIDE** | | |
| **(-) Seller Comm** | 20% of ₹1,000 | ₹200 |
| **(-) Comm GST** | 18% of ₹200 | ₹36 |
| **(-) TCS** | 1% of ₹1,000 (Only if GST Registered) | ₹10 / ₹0 |
| **(+) Item Tax Credit**| 18% of ₹1,000 (Only if GST Registered) | ₹180 / ₹0 |
| **(=) Net Payout** | ₹1,000 - ₹200 - ₹36 - ₹10 (+ ₹180 if Reg) | **₹764** (Unreg) / **₹934** (Reg) |
| | | |
| **BUYER SIDE** | | |
| **(+) Buyer Comm** | 20% of ₹1,000 | ₹200 |
| **(+) Comm GST** | 18% of ₹200 | ₹36 |
| **(+) Item Tax** | 18% of ₹1,000 (Always Charged) | ₹180 |
| **(=) Final Pay** | ₹1,000 + ₹200 + ₹36 + ₹180 | **₹1,416** |
| | | |
| **PLATFORM REVENUE** | | |
| **(+) Gross Comm** | ₹200 (Seller) + ₹200 (Buyer) | ₹400 |
| **(+) Tax Margin** | Item Tax (If Seller Unregistered) | ₹180 / ₹0 |
| **(-) Expenses** | PG Fee (₹30) + Delivery (₹80) | ₹110 |
| **(=) Net Profit** | Revenue - Expenses | **₹470** (Unreg) / **₹290** (Reg) |

---

## 7. Reporting Highlights
*   **Financial Report**: Purchase items are flagged with `is_purchase = true`.
*   **Alert Calendar**: Purchase items appear with a green **Sale [S]** indicator on the day of order creation.
*   **Security Dashboard**: These items never appear in the security dashboard as no funds are held.

---
👉 **[Back to System Guide](../SYSTEM_GUIDE.md)**
