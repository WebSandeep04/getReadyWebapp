# End-to-End Rental Workflow Guide

This document describes the complete technical flow of a rental transaction in the **GetReady** platform, from product selection to the return phase.

---

## 1. Product Selection & Cart
When a user decides to rent an item, the process begins in the frontend and persists in the database.

*   **Technical Component**: `CartController`, `CartItem` Model.
*   **Database**: `cart_items` table.
*   **Workflow**:
    1.  User selects "Rent" and chooses a **Date Range** (Start Date to End Date).
    2.  An AJAX request is sent to `cart.add`.
    3.  `cart_items` record is created/updated with `rental_start_date`, `rental_end_date`, and `purchase_type = 'rent'`.
*   **Migration**: `2025_08_01_120809_add_rental_dates_to_cart_items.php`.

---

## 2. Order Creation & Payment
During checkout, the system aggregates the rental costs and security deposits.

*   **Technical Component**: `CheckoutController@createOrder`, `CheckoutController@verifyPayment`.
*   **Database**: `orders`, `order_items`, `payments` tables.
*   **Workflow**:
    1.  **Validation**: The system calculates the `rental_subtotal` and `security_deposit` summed from all items.
    2.  **Order Record**: A record is created in the `orders` table with:
        *   `status = 'Pending'`
        *   `has_rental_items = true`
        *   `rental_from`: Earliest start date among items.
        *   `rental_to`: Latest end date among items.
    3.  **Payment Processing**:
        *   **Online**: Integrated with Razorpay. Upon success, `verifyPayment` updates order status to `Confirmed`.
        *   **COD**: Payment status is set to `Pending`, and order status is set to `Confirmed` immediately.
*   **Migration**: `2025_07_14_112324_create_orders_table.php`, `2025_07_14_112325_create_payments_table.php`.

---

## 3. Shipment Creation
Once an order is confirmed, the system immediately communicates with the logistics partner.

*   **Technical Component**: `CheckoutController@createShipment`, `XpressbeesService`.
*   **Database**: `shipments` table.
*   **Workflow**:
    1.  The system calls the **Xpressbees API** with customer delivery details.
    2.  **Shipment Record**: Upon a successful API response, a record is created in the `shipments` table containing the **AWB (Waybill Number)** and **Tracking URL**.
    3.  **Order Update**: Order status is updated to `"Order Confirmed & Shipment Created"`.
*   **Migration**: `2026_01_25_154747_create_shipments_table.php`.

---

## 4. Delivery & Webhook Updates
As the physical package moves, the logistics partner sends real-time updates.

*   **Technical Component**: `Api/XpressbeesWebhookController@handleWebhook`.
*   **Workflow**:
    1.  **Webhook Trigger**: Xpressbees sends a POST request to the system's webhook URL whenever the shipment status changes (e.g., In Transit, Out for Delivery).
    2.  **Status Sync**: The system finds the shipment by AWB and updates its `status`.
    3.  **Completion**: If the webhook status is **"Delivered"**:
        *   `shipment->delivered_at` is timestamped.
        *   `order->status` is updated to **'Delivered'**.
*   **Notification**: The system sends a success notification to the buyer.

---

## 5. Return Date & Automated Reverse Shipment
This phase is now fully automated to ensure timely returns without manual intervention.

*   **Technical Component**: `ProcessRentalReturns` Artisan Command.
*   **Trigger**: Scheduled to run daily at midnight via `routes/console.php`.
*   **Workflow**:
    1.  **Scan**: The system scans the `orders` table for records where `rental_to` matches today's date and the status is `Delivered`.
    2.  **Reverse Logistics**: For each eligible order, the system               group items by Seller and calls `XpressbeesService@createReturnOrder`.
    3.  **Addresses**:
        *   **Pickup**: Buyer's delivery address (where the item currently is).
        *   **Consignee**: Seller's registered address (where the item must go back).
    4.  **Tracking**: A new record is created in the `shipments` table with `type = 'reverse'`.
    5.  **Notifications**: Both Buyer and Seller receive automated notifications with the new Return AWB number.
    6.  **Order Update**: The order status transitions to **'Return In Progress'**.

---

## 6. Verification & Security Refund
The final stage remains an administrative checkpoint to ensure item quality.

*   **Technical Component**: `SecurityController`, `AdminController`.
*   **Workflow**:
    1.  **Return Arrival**: Once the reverse shipment is marked as "Delivered" (back to seller), the platform status moves to **"Returned"**.
    2.  **Inspection**: Admin/Seller verifies the item condition.
    3.  **Security Refund**: Admin manually triggers the security refund in the dashboard, updating `is_security_returned = true`.

---

## Migration & Logic Summary
*   **Shipment Type**: Added `type` column (`forward`/`reverse`) to the `shipments` table.
*   **Artisan Command**: `orders:process-returns` handles the heavy lifting of reverse logistics.
*   **Relationship**: `Order` now has a `hasMany` relationship with `Shipment` to store both forward and return tracking data.
