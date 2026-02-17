# End-to-End Rental Workflow Guide

This document describes the complete technical flow of a rental transaction in the **GetReady** platform, from product selection to the return phase.

---

## 1. Product Selection & Cart
When a user decides to rent an item, the process begins in the frontend and persists in the database.

*   **Technical Component**: `CartController`, `CartItem` Model.
*   **Database**: `cart_items` table.
*   **Workflow**:
    1.  User selects "Rent" and chooses a **Date Range** (must be within explicitly provided "Available Blocks").
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

*   **Technical Component**: `CheckoutController@createShipment`, `XpressbeesService`, `Shipment` Model.
*   **Database**: `shipments` table.
*   **Workflow**:
    1.  The system calls the **Xpressbees API** with customer delivery details.
    2.  **Shipment Record**: Upon a successful API response, a record is created in the `shipments` table with `type = 'forward'`.
    3.  **Tracking**: Includes the **AWB (Waybill Number)** and **Tracking URL**.
    4.  **Order Update**: Order status is updated to `"Order Confirmed & Shipment Created"`.
*   **Migration**: `2026_01_25_154747_create_shipments_table.php`, `2026_02_11_101605_add_type_to_shipments_table.php`.

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
    1.  **Scan**: The system scans the `orders` table for records where `rental_to` matches today's date (or earlier) and the status is `Delivered`.
    2.  **Reverse Logistics**: For each eligible order, the system groups items by Seller and calls `XpressbeesService@createReturnOrder`.
    3.  **Addresses**:
        *   **Pickup**: Buyer's delivery address (where the item currently is).
        *   **Consignee**: Seller's registered address (where the item must go back).
    4.  **Tracking**: A new record is created in the `shipments` table with `type = 'reverse'`.
    5.  **Notifications**: Both Buyer and Seller receive automated notifications via the `notifications` table with the new Return AWB number.
    6.  **Order Update**: The order status transitions to **'Return In Progress'**.

---

## 6. Verification & Security Refund
The final stage remains an administrative checkpoint to ensure item quality.

*   **Technical Component**: `Api/XpressbeesWebhookController`, `SecurityController`, `AdminController`.
*   **Workflow**:
    1.  **Return Arrival**: When the logistics partner delivers the return package, the webhook (`handleWebhook`) detects the `reverse` shipment type and automatically updates the order status to **"Returned"**.
    2.  **Inspection**: Admin/Seller verifies the item condition.
    3.  **Repayment (Refund Logic)**:
        *   **Standard Return**: Only the **Security Deposit** is marked for refund. The `payment_status` remains `Paid`.
        *   **Dispute Return**: The system flags the order in the Security Dashboard. The Admin is prompted to refund both the **Rental Fee** and the **Security Deposit** (Total Amount). Marking it as returned will automatically update the `payment_status` to **'Refunded'** to ensure it is excluded from revenue stats.
    4.  **Admin Action**: Admin manually triggers the security refund in the dashboard, updating `is_security_returned = true`.
    4.  **Inventory Recovery**: The system automatically increments the `sku` and sets `is_available = true` for the returned items.

---

## 7. Dispute & Early Return Workflow
This flow handles scenarios where a buyer finds an item unsatisfactory (e.g., damaged or wrong item) and wants to return it before the rental period ends.

*   **Trigger**: Buyer clicks **"Report Issue"** in their order history.
*   **Status Transition**: `Delivered` → `Return Requested`.
*   **Technical Component**: `OrderReturnController@store`.
*   **Workflow**:
    1.  **Buyer Submission**: Buyer selects a reason (e.g., Damaged Item), provides a detailed description, and uploads up to 3 evidence images.
    2.  **Admin Review**: Admin reviews the dispute via the Orders dashboard.
        *   **Approve**: Admin clicks "Approve & Generate AWB". This immediately calls `XpressbeesService@createReturnOrder` to book a reverse pickup. Status → `Return In Progress`.
        *   **Reject**: Admin providing a rejection reason. The order status reverts to `Delivered`, and the buyer is notified.
    3.  **Completion**: Once the reverse pickup is approved, it follows the standard webhook status tracking path until it is delivered back to the seller and marked as `Returned`.
*   **Database**: `return_reason`, `return_details`, `return_images` (JSON), `admin_rejection_reason`.
*   **Safety**: The automated `orders:process-returns` command skips any orders already in `Return Requested` or `Return In Progress` status.

---

## 8. Operations Monitoring
To maintain the efficiency of the rental cycle, a dedicated monitoring tool is provided for admins.

*   **Technical Component**: `ReportController@calendar`, `calendar.blade.php`.
*   **Workflow**:
    1.  **Daily Plan**: Admins check the **Alert Calendar** to see all scheduled **Pickups [P]** and **Returns [R]**.
    2.  **Daily Alerts**: The calendar automatically calculates the "After 8:00 PM" pickup and "After 2:00 PM" return windows.
    3.  **Financial Check**: Each alert identifies the **Security Deposit** status and **Seller Payout (Rent/Sale)** to ensure financial accuracy before shipping or marking as returned.
*   **Aesthetic**: Follows the "Zero Radius" monochrome design system for a focused, professional workspace.

---

## Migration & Logic Summary
*   **Shipment Type**: Added `type` column (`forward`/`reverse`) to the `shipments` table.
*   **Order Dispute**: Added `return_reason`, `return_details`, `return_images`, and `admin_rejection_reason` to the `orders` table.
*   **Artisan Command**: `orders:process-returns` handles the heavy lifting of reverse logistics.
*   **Relationship**: `Order` now has a `hasMany` relationship with `Shipment` to store both forward and return tracking data.
