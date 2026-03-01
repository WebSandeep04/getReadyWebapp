# Purchase Lifecycle Workflow

This document details the end-to-end flow for direct sales (Purchases) on the GetReady platform. Unlike the rental model, the purchase flow is a traditional e-commerce transaction focusing on permanent ownership transfer.

---

## 1. Item Listing & Eligibility
Sellers decide which items are available for direct sale during the listing process.

*   **Controller**: `ClothController@store` / `ClothController@update`
*   **Database Column**: `is_purchased` (Boolean)
*   **Logic**:
    *   If `is_purchased` is true, the seller must provide a `selling_price`.
    *   **Enforcement**: `rent_price` **cannot exceed 20%** of the `selling_price`.
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
        3.  **Payout Eligibility**: The order is **immediately** flagged as "Eligible for Payout".
*   **Note**: The automated `orders:process-returns` command specifically skips purchase items, as no reverse pickup is needed.

### 💰 Payout Timing Difference
*   **Rentals**: Seller is paid ONLY after the item is **Returned** and verified.
*   **Purchases**: Seller is paid after **Delivery** (No waiting for return).

---

## 6. Financial Settlement (20/20 Model)

Purchases follow the **Universal 20/20 Financial Structure**:

*   **Seller Input**: The seller enters a **Base Selling Price** (e.g., ₹1,000).
*   **Display Price**: The buyer sees **₹1,200** (Base + 20% Comm) on the product page.
*   **Final Price**: The buyer pays **₹1,416** (Display Price + 18% Tax on Base + 18% Tax on Comm) at checkout.

### 💰 Calculation Example (Base Selling Price: ₹1,000)

| Component | Logic | Value |
| :--- | :--- | :--- |
| **BUYER PAYS** | | |
| **(+) Display Price** | Base (1000) + Buyer Comm (200) | ₹1,200 |
| **(+) Item Tax / Fee** | 18% of Base (Always Charged) | ₹180 |
| **(+) Comm GST** | 18% of Buyer Comm (200) | ₹36 |
| **(=) Final Pay** | Total charged at checkout | **₹1,416** |
| | | |
| **SELLER EARNS** | | |
| **(+) Base Price** | Selling Price | ₹1,000 |
| **(+) Tax Credit** | 18% of Base (Only if GST Reg) | ₹180 / ₹0 |
| **(-) Seller Comm** | 20% of Base | ₹200 |
| **(-) Comm GST** | 18% of Seller Comm | ₹36 |
| **(-) TCS** | 1% of Base (Only if GST Reg) | ₹10 / ₹0 |
| **(=) Net Payout** | | **₹934** (Reg) / **₹764** (Unreg) |
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

## 8. Converting a Rental to a Purchase
If a user is currently renting an item marked as `is_purchased=true`, they can convert that rental into a permanent purchase from their active order view.
*   **Controller**: `OrderConversionController@convertToPurchase`
*   **Pricing**: The system calculates the remaining amount due by adjusting the rental amount and using the **Security Deposit** already held for the item. The user pays only the difference.
*   **Order Update**: The item's `purchase_type` changes instantly from `rent` to `buy`. The order is flagged `security_absorbed_into_purchase = true` so the deposit is intentionally not returned.
*   **Fulfillment Impact**: No new shipment is created because the buyer already has the physical item. The automated return scan ignores this converted item. The physical SKU is decremented from the catalog.

---
👉 **[Back to System Guide](../SYSTEM_GUIDE.md)**
