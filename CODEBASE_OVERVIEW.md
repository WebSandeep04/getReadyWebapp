# GetReady - Codebase Overview & Developer Documentation

## 1. Project Overview

**GetReady** is a fashion marketplace platform built with **Laravel 12** (PHP) and **TailwindCSS**. It facilitates buying, selling, and renting pre-owned clothing. The platform features robust user roles (Buyers, Sellers, Admins), an extensive product taxonomy (Sizes, Brands, Colors), and integrated workflows for item approval, shipping (Xpressbees), and AI-generated descriptions (Google Gemini).

### Key Technologies
*   **Backend:** Laravel 12.x (PHP 8.2+)
*   **Frontend:** Blade Templates (Server-Side Rendering) + TailwindCSS
*   **Database:** MySQL (Relational Schema)
*   **Assets:** Vite
*   **Authentication:** Laravel Sanctum (API), Session Auth (Web)
*   **Integrations:**
    *   **Google Gemini:** AI-powered product description generation.
    *   **Xpressbees:** Logistics and shipping integration.

---

## 2. Directory Structure

The project follows the standard Laravel directory structure with key application logic located in:

*   **`app/Http/Controllers/`**: Contains all request handling logic.
    *   **Admin/**: Administrative functions (User management, specific master data CRUDs).
    *   **Api/**: API endpoints (e.g., `XpressbeesWebhookController`).
    *   Specific feature controllers: `ClothController`, `OrderController`, `CartController`, `RejectionController`.
*   **`app/Models/`**: Eloquent models representing database tables (`Cloth`, `User`, `Order`, `Role`, `Permission`, `Rejection`).
*   **`database/migrations/`**: Database schema definitions.
*   **`resources/views/`**: Blade templates for the frontend UI.
*   **`routes/`**: Route definitions (`web.php` for browser, `api.php` for external services).

---

## 3. Core Modules & Features

### 3.1 User Roles & Authentication
The application distinguishes between **End Users** (Buyers/Sellers) and **Admins**.
*   **Users:** Register/Login via `LoginController`, `RegisterController`.
*   **Admins:** Separate login via `AdminAuthController`. Protected by `admin.auth` middleware.
*   **RBAC (Role-Based Access Control):** A new Role/Permission system is implemented (`RoleMasterController`, `roles`, `permissions` tables) to granulate access within the admin panel.

### 3.2 Product Management (Clothes)
This is the central entity of the marketplace.
*   **Listing:** Users list items via `ClothController@create` (`/sell`).
    *   AI Description: Users can generate descriptions using Gemini AI (`GeminiController`).
*   **Approval Workflow:**
    *   New listings are marked as `is_approved = null` (pending).
    *   Admins review listings via `AdminController@clothApproval`.
    *   Admins can **Approve** (`is_approved = 1`) or **Reject** (`is_approved = 0`) items.
    *   Rejections are managed by `RejectionController` and recorded in the `rejections` table.
*   **Attributes:** Clothes have extensive metadata managed via Admin CRUDs:
    *   `Category`, `FabricType`, `Color`, `Brand`, `Size`, `BottomType`, `BodyTypeFit`, `GarmentCondition`.

### 3.3 Shopping & Checkout
*   **Cart:** Managed by `CartController`. Supports adding items and rental dates (`cart_items` table includes `rental_start_date`, `rental_end_date`).
*   **Checkout:** `CheckoutController` handles order creation.
*   **Orders:** Stored in `orders` and `order_items`.
    *   Status workflow: Pending -> Processing -> Shipped -> Delivered -> Returned.
    *   Admins manage orders via `AdminController@orders`.

### 3.4 Shipping & Logistics
*   Integration with **Xpressbees** for shipment tracking and updates.
*   `Shipments` table stores tracking info.
*   Webhooks handled by `Api/XpressbeesWebhookController`.

### 3.5 Interactions
*   **Reviews:** Users can review products (`ReviewController`).
*   **Q&A:** Users can ask questions about products (`QuestionController`).
*   **Notifications:** System notifications for users (`NotificationController`).

---

## 4. Key Data Flows

### Purchase Flow
1.  **User** browses `ProductController@index`.
2.  Adds item to **Cart** (`CartController@addToCart`).
3.  Proceeds to **Checkout** (`CheckoutController@createOrder`).
4.  **Order** is created in pending state.
5.  **Payment** is verified (`CheckoutController@verifyPayment`).
6.  **Admin** marks order for shipping/fulfillment.

### Selling Flow
1.  **User** visits `/sell` (`ClothController@create`).
2.  Fills details (images, brand, size, etc.).
3.  Hooks into `GeminiController` for description (optional).
4.  Submits Listing (`ClothController@store`).
5.  Item enters **Pending Approval** state.
6.  **Admin** reviews in Dashboard.
    *   **If Approved:** Item becomes visible in marketplace.
    *   **If Rejected:** User is notified (via `RejectionController` logic).

---

## 5. Database Schema Highlights

*   **`users`**: Standard user table extended with `gstin`, `city`, `age`.
*   **`clothes`**: Stores product info. Key columns: `is_approved` (status), `selling_price`, `mrp`, `brand_id`, `category_id`.
*   **`cart_items`**: Links `user` and `cloth`. Includes `rental_start/end_date`.
*   **`orders`**: Master order record.
*   **`order_items`**: Line items for orders.
*   **`rejections`**: Stores reason/details for rejected cloth listings.
*   **`admin_panel_users`, `roles`, `permissions`**: Internal Admin RBAC.

---

## 6. Setup & Installation

1.  **Clone Repository:**
    ```bash
    git clone <repository-url>
    cd getReady
    ```

2.  **Install Dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Environment Setup:**
    ```bash
    cp .env.example .env
    # Update DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env
    php artisan key:generate
    ```

4.  **Database Migration & Seeding:**
    ```bash
    php artisan migrate
    php artisan db:seed
    ```
    *Note: Ensure you run the `RoleMaster` seeder if available to set up initial permissions.*

5.  **Build Assets:**
    ```bash
    npm run dev  # for development
    npm run build # for production
    ```

6.  **Run Server:**
    ```bash
    php artisan serve
    ```

---

## 7. Developer Notes

*   **Routes:** `routes/web.php` is the primary entry point. It contains both User and Admin routes. Admin routes are grouped under `middleware(['admin.auth'])`.
*   **Validation:** Most validation logic resides directly in Controllers (e.g., `ClothController::store`).
*   **API:** The project is primarily server-side rendered (Blade). API routes are minimal.
*   **Linting:** `php artisan pint` (if installed) or standard PSR-12 coding standards.
