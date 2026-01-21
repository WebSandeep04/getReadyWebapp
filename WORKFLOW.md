# Developer Workflow & System Overview

## 1. Project Overview
**GetReady** is a web-based platform for **selling, renting, and swapping clothes**. It features a user-facing marketplace and a comprehensive admin backend for managing inventory, orders, and configuration.

**Tech Stack:**
- **Framework:** Laravel 11.x
- **Frontend:** Blade Templates + Vanilla CSS/JS (React/Vue components not observed in primary flows)
- **Database:** MySQL

---

## 2. Core Modules & Database Schema

### A. User Management
*   **Table:** `users`
*   **Key Fields:** `name`, `email`, `phone`, `address`, `city`, `gstin`, `gender`.
*   **Roles:** No explicit `role_id` column found. Admin features are currently separated by routes, likely requiring future middleware hardening.

### B. Product (Clothes) Management
The core entity is the "Cloth" item being listed.
*   **Table:** `clothes`
*   **Key Fields:**
    *   `user_id`: The seller/owner.
    *   `title`, `description`
    *   `rent_price`, `security_deposit`, `purchase_price` (implied by recent migrations).
    *   `is_approved`: `1` (Approved), `0` (Rejected), `NULL` (Pending).
    *   `is_available`: Availability status.
*   **Attributes (Normalized Tables):**
    *   `categories`, `brands`, `fabric_types`, `colors`, `sizes`, `bottom_types`, `body_type_fits`, `garment_conditions`.
    *   *Note:* The `clothes` table references these via IDs (e.g., `category`, `brand` columns).

### C. Commerce (Orders & Cart)
A dual-mode commerce system supporting both **Rentals** and **Purchases**.
*   **Tables:**
    *   `cart_items`: Temporary storage for user selections.
        *   Columns: `purchase_type` ('rent'/'buy'), `rental_start_date`, `rental_end_date`.
    *   `orders`: Finalized transactions.
        *   Columns: `status` ('Pending', 'Confirmed', 'delivered', 'Returned'), `total_amount`, `security_amount`.
        *   Flags: `has_rental_items`, `has_purchase_items`.
    *   `order_items`: (Presumed) Line items for orders.
    *   `payments`: Payment records linked to orders.

### D. Social & Feedback
*   **Tables:**
    *   `product_reviews`, `product_questions`: User engagement on product pages.
    *   `replies`: Nested responses to reviews/questions.
    *   `ratings`: User-to-User or User-to-Product ratings.

### E. System Configuration (Admin)
*   **Table:** `frontend_settings`
    *   Stores dynamic configuration for the frontend (Logo, Hero Section, etc.).
    *   Managed via `/admin/frontend`.

---

## 3. Key Workflows

### 3.1. Selling Item Flow (Listing)
1.  **User Action:** Authenticated user navigates to `/sell`.
2.  **Submission:** Fills out the form. Data is stored in `clothes` table with `is_approved = NULL`.
3.  **Admin Review:**
    *   Admin visits `/admin/cloth-approval`.
    *   Filters by 'Pending'.
    *   **Action:** Approve -> `is_approved = 1`. Notification sent to user.
    *   **Action:** Reject -> `is_approved = 0`. Reason stored. Notification sent to user.

### 3.2. Purchase/Rental Flow
1.  **Browsing:** User views products at `/clothes` or `/product`.
2.  **Add to Cart:**
    *   User selects **Rent** (with dates) or **Buy**.
    *   POST request to `/cart/add`.
    *   Data saved to `cart_items` with `purchase_type`.
3.  **Checkout:**
    *   User proceeds to `/checkout`.
    *   System calculates totals (Rent + Security Deposit vs Purchase Price).
4.  **Order Creation:**
    *   `CheckoutController@createOrder` creates an entry in `orders`.
    *   Items move from `cart_items` to order lines.
    *   Payment is processed (verified via `CheckoutController@verifyPayment`).

### 3.3. Admin & Order Fulfillment
1.  **Order Management:**
    *   Admin views `/admin/orders`.
    *   Filters by Status, Payment Status, or Type (Rent/Buy).
2.  **Rental Tracking:**
    *   System flags 'Overdue' rentals based on `rental_to` date vs Current Date.
    *   Admin manages returns and deposit refunds.

---

## 4. Developer Guide

### Directory Structure Highlights
*   `app/Http/Controllers/AdminController.php`: Handles most admin logic (Dashboard, Orders, Approvals).
*   `app/Models/Cloth.php`: Central model for products.
*   `routes/web.php`: Contains all application routes. **Note:** Admin routes are grouped but currently lack a dedicated Admin Middleware group.

### Ongoing Development Notes
*   **Security:** Admin routes need `middleware` protection (currently relying on obscurity or mixed `auth`).
*   **API:** `routes/api.php` exists but primary logic seems to be in `web.php`.
*   **Frontend:** Uses Blade templates stored in `resources/views`. Admin views are in `resources/views/admin`.

