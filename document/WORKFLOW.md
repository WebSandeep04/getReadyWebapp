# Developer Workflow & System Overview

## 1. Project Overview
**GetReady** is a comprehensive web-based platform for **selling, renting, and swapping clothes**. It features a dynamic user-facing marketplace and a robust admin backend for managing inventory, orders, configuration, and user engagement.

**Tech Stack:**
- **Framework:** Laravel 11.x
- **Frontend:** Blade Templates + Vanilla CSS/JS
- **Interactive Elements:** jQuery / AJAX for admin operations and dynamic forms.
- **Database:** MySQL
- **AI Integration:** Google Gemini 2.0 Flash (via API) for content generation.
- **Notifications:** Custom database-driven notification system + Msg91 (OTP).

---

## 2. Core Modules & Database Schema

### A. User Management
*   **Table:** `users`
*   **Key Fields:** `name`, `email`, `phone`, `address`, `city`, `gstin`, `gender`, `last_login_at`.
*   **Authentication:**
    *   Standard Login/Register logic.
    *   **Mobile/OTP Signup:** Implemented via `RegisterController` using `Msg91Service`.
    *   **Profile:** Users can update profile details via `/profile/update`.

### B. Product (Clothes) Management
The core entity is the "Cloth" item.
*   **Table:** `clothes`
*   **Key Fields:**
    *   `user_id`: Owner/Seller.
    *   `title`, `description` (AI-assisted).
    *   `rent_price`, `security_deposit`, `purchase_price`.
    *   `sku`: Stock keeping unit (managed during Rent/Return cycles).
    *   `is_approved`: `1` (Approved), `-1` (Rejected), `NULL` (Pending).
    *   `resubmission_count`: Tracks edits after rejection.
*   **Attributes (Normalized):**
    *   Managed via Admin: `categories`, `brands`, `fabric_types`, `colors`, `sizes`, `bottom_types`, `body_type_fits`, `garment_conditions`.

### C. Commerce (Orders & Cart)
Dual-mode commerce system: **Rent** vs **Buy**.
*   **Tables:**
    *   `cart_items`: User selections (`purchase_type`, `rental_start_date`, `rental_end_date`).
    *   `orders`: Final Transactions.
        *   Columns: `status`, `payment_status`, `total_amount`, `security_amount`, `has_rental_items`, `rental_to`.
    *   `payments`: Tracks payment status linked to orders.
    *   `shipments`: Logistics tracking.
        *   Columns: `order_id`, `courier_name` (Xpressbees), `waybill_number`, `status`, `courier_response` (JSON).

### D. User Engagement & Notifications
*   **Table:** `notifications`
    *   Stores system alerts (Welcome, Approval, Rejection).
    *   Columns: `user_id`, `title`, `message`, `type` (success/warning/info), `icon`, `data` (JSON), `read`, `read_at`.
*   **Features:**
    *   **Welcome Notification:** Triggered on first login/registration.
    *   **Status Updates:** Users are notified when items are Approved or Rejected.
    *   **AI Descriptions:** `GeminiController` generates descriptions via `/generate-description`.

### E. System Configuration (Admin)
*   **Table:** `frontend_settings`
    *   Stores dynamic UI config (Logo, Hero Section, Social Links).
    *   Managed via `/admin/frontend` using AJAX.

---

## 3. Key Workflows

### 3.1. Selling Item Flow (Listing)
1.  **Creation:** User visits `/sell`.
    *   **AI Assistance:** User types usage details, clicks "Generate" -> Gemini API returns professional description.
2.  **Submission:** Item saved with `is_approved = NULL`.
3.  **Admin Review (`/admin/cloth-approval`):**
    *   **Approve:** Sets `is_approved = 1`. Mentions "Item Approved" in `Notification`.
    *   **Reject:** Admin provides reason. Sets `is_approved = -1`.
        *   `Notification` created with `reject_reason` in `data`.
        *   User can view reason, edit, and resubmit (incrementing `resubmission_count`).

### 3.2. Delivery & Fulfillment (Xpressbees Integration)
1.  **Shipment Creation:**
    *   **Trigger:** Automatically fired inside `CheckoutController@processPostOrderTasks` after successful Payment or COD confirmation.
    *   **Action:** Calls `XpressbeesService` to book the shipment.
    *   **Record:** Creates `Shipment` entry with `waybill_number` (AWB).
    *   **Status Update:** Order status -> 'Order Confirmed & Shipment Created'.
2.  **Availability Blocking:**
    *   System automatically blocks dates for the rental period **plus 1 buffer day** before (delivery) and after (pickup).
    *   Stored in `availability_blocks` table.
3.  **Real-time Tracking (Webhook):**
    *   **Route:** `/api/xpressbees/webhook`.
    *   **Controller:** `Api\XpressbeesWebhookController`.
    *   **Logic:** Updates `Shipment` status (e.g., 'In Transit', 'Delivered').
        *   If status is 'Delivered', automatically updates `Order` status to 'Delivered' and logs `delivered_at`.

4.  **Failure Handling (Edge Case):**
    *   If the Xpressbees API call fails during Checkout, the system **logs the error** but does not block the order.
    *   **Result:** Payment is successful, Order is `'Confirmed'`, but **no Shipment exists**.
    *   **Action Required:** Admin must manually address orders stuck in `'Confirmed'` state.

### 3.3. Order Management (Admin)
1.  **Overview (`/admin/orders`):**
    *   **Filters:** Status, Payment (Valid/Invalid), Type (Rent/Buy), Return State (Overdue/Due Soon).
2.  **Return Process:**
    *   Admin marks order as `Returned`.
    *   System automatically **increments SKU** and resets `is_available` for rented items.

### 3.3. Admin Configuration
*   **Product Attributes:** Admin can fully CRUD Categories, Brands, Colors, etc. via modal-driven AJAX interfaces (no page reloads).
*   **Frontend Customization:** Admin updates Logo, Hero images, and Footer text directly from the dashboard.

---

## 4. Developer Guide

### Directory Structure Highlights
*   **Controllers:**
    *   `AdminController.php`: Heavy lifting for Admin Dashboard, Orders (`ordersData`), and Approvals (`approveCloth`, `rejectCloth`).
    *   `GeminiController.php`: Handles Google Gemini API requests.
    *   `NotificationController.php`: User notification fetching and read-marking.
    *   `RegisterController.php`: OTP and Registration logic.
*   **Routes:**
    *   `web.php`: Primary route file.
    *   Review `routes/web.php` for `ajax` endpoints (e.g., `/admin/frontend/update`, `/admin/clothes/approve/{id}`).
*   **Views:**
    *   `resources/views/admin/screens`: Main admin pages.
    *   `resources/views/admin/components`: Partial views for AJAX responses.

### Ongoing Development Notes
*   **Security:** Ensure `GEMINI_API_KEY` is set in `.env`.
*   **Middlewares:** Admin routes are guarded by `auth` but rely on role checks within controllers/views or implicit separation.
*   **Performance:** AJAX is heavily used for Admin tables (Orders, Cloth Approval) to improve responsiveness.
