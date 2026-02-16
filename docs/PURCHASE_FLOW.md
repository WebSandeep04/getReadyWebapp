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
*   **Note**: The automated `orders:process-returns` command specifically skips purchase items, as no reverse pickup is needed.

---

## 6. Financial Settlement (20/20 Model)

Purchases now follow the same financial model as rentals to ensure platform consistency.

*   **Commission Structure**: 20% from Seller + 20% from Buyer.
*   **GST Handling**: If the seller is GST registered, the buyer pays the 18% GST on the item price, which is passed to the seller for compliance.
*   **Formula (Non-GST Seller)**:
    *   `Buyer Pays = Item Price + 20% Buyer Comm`
    *   `Seller Receives = Item Price - 20% Seller Comm`
*   **Formula (GST Registered Seller)**:
    *   `Buyer Pays = Item Price + 18% GST + 20% Buyer Comm + 18% GST on Comm`

### 💰 Calculation Example (Selling Price: ₹1,000)

| Component | Logic | Value |
| :--- | :--- | :--- |
| **Input Price** | Seller entered price | **₹1,000** |
| **Seller Payout** | ₹1,000 - ₹200 (20%) | **₹800** |
| **Buyer Payment** | ₹1,000 + ₹200 (20%) | **₹1,200** |
| **Platform Revenue** | ₹200 (Seller) + ₹200 (Buyer) | **₹400** |
| **PG Expense** | Fixed Placeholder | **₹30** |
| **Delivery Cost** | Fixed Placeholder | **₹80** |
| **Net Profit** | ₹400 - (₹30 + ₹80) | **₹290** |

---

## 7. Reporting Highlights
*   **Financial Report**: Purchase items are flagged with `is_purchase = true`.
*   **Alert Calendar**: Purchase items appear with a green **Sale [S]** indicator on the day of order creation.
*   **Security Dashboard**: These items never appear in the security dashboard as no funds are held.

---
👉 **[Back to System Guide](../SYSTEM_GUIDE.md)**
