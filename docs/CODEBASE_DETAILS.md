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
    *   **Razorpay:** Secure payment processing for orders and extensions.

---

## 2. Directory Structure

The project follows the standard Laravel directory structure with key application logic located in:

*   **`app/Http/Controllers/`**: Contains all request handling logic.
    *   **Admin/**: Administrative functions (User management, specific master data CRUDs).
    *       *   `PaymentController`: Handles payment listings, filtering (Paid, Pending, Failed), and statistics.
    *       *   `SecurityController`: Manages security deposit tracking, returns, and dashboard stats.
    *       *   `ReportController`: Handles tactical operations monitoring (Calendar) and financial performance analysis.
    *   **Api/**: API endpoints (e.g., `XpressbeesWebhookController`).
    *   Specific feature controllers: `ClothController`, `OrderController`, `CartController`, `RejectionController`, `OrderExtensionController`.
*   **`app/Models/`**: Eloquent models representing database tables (`Cloth`, `User`, `Order`, `OrderExtension`, `Payment`, `Role`, `Permission`).
*   **`database/migrations/`**: Database schema definitions.
*   **`resources/views/`**: Blade templates for the frontend UI.
    *   **admin/screens/reports/**: Contains Operation Alert Calendar and Financial Reporting screens.
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
    *   Status workflow: Pending -> Delivered -> Return Requested (Disputed) -> Return In Progress -> Returned.
    *   **Management**: `OrderController` (Buyer dashboard), `OrderReturnController` (Buyer dispute), `AdminController` (Admin processing).

### 3.4 Shipping & Logistics
*   Integration with **Xpressbees** for shipment tracking and updates.
*   **Shipments Type**: Supports `forward` (Seller -> Buyer) and `reverse` (Buyer -> Seller) shipments.
*   **Automated Returns**: An Artisan command `orders:process-returns` automatically generates reverse shipments when a rental period ends.
*   **Relationship**: `Order` has a `hasMany` relationship with `Shipments` to track both legs of the journey.
*   **Webhooks**: Multi-type status mapping handled by `Api/XpressbeesWebhookController`.

### 3.6 Security & Payments
*   **Payment Management:** Dedicated module (`PaymentController`) to track all transaction statuses.
    *   Filters: Paid, Pending, Failed, Cancelled, Refunded.
    *   Stats: Real-time revenue, pending payments, and failed transaction counts.
*   **Security Deposits:** Comprehensive tracking of security amounts held for rental items.
    *   Status workflow: Held -> Pending Return -> Returned (Refunded).
    *   Dashboard integration with AJAX-loaded statistics for "Total Held", "Returned", and "Need to Return".
    *   Admins can mark security as returned directly from the dashboard with confirmation modals.

### 3.7 Interactions
*   **Reviews:** Users can review products (`ReviewController`).
*   **Q&A:** Users can ask questions about products (`QuestionController`).
### 3.8 Reporting & Operations
*   **Alert Calendar**: A tactical dashboard for daily operations.
    *   Generates dual alerts for every order: **Pickup [P]** (Start Date) and **Return [R]** (End Date).
    *   Includes **Sale [S]** alerts for direct purchases.
    *   Features a detailed table-based modal tracking Order IDs, Security Deposits, and Seller Payouts (Rent/Sale).
*   **Financial Reporting**: A deep-dive analytical screen for profitability.
    *   Tracks: Total Revenue, Security Held, Payouts, and Net Profit.
    *   Logic: Automatically handles platform commission on base prices and adjusts for PG expenses (2%) and delivery costs.
*   **UI Standard**: Both modules follow a premium, high-contrast design system with sharp square components (zero border-radius).
### 3.9 Rental Extensions
Users can increase their rental duration for active orders.
*   **Logic**: Pro-rated charging based on original base rent.
*   **Availability**: Real-time checking (including pickup buffers) to prevent double bookings.
*   **Invoicing**: Automates the generation of supplementary invoices for the extension cost.
*   **History**: Tracks all changes in `order_extensions` with links to Razorpay transaction IDs.

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

### Security Return Flow
1.  **Order** with rental items completes (Item returned by user).
2.  **Admin** reviews the item condition.
3.  **Admin** navigates to Dashboard -> Security Deposits.
4.  Filters by "Returned" status to see pending refunds.
5.  Clicks "Mark Returned" to confirm the security deposit refund.
6.  System updates `is_security_returned` flag and timestamps the action.
7.  Dashboard stats update immediately via AJAX.

---

## 5. Database Schema Highlights

*   **`users`**: Standard user table extended with `gstin`, `city`, `age`.
*   **`clothes`**: Stores product info. Key columns: `is_approved` (status), `selling_price`, `mrp`, `brand_id`, `category_id`.
*   **`cart_items`**: Links `user` and `cloth`. Includes `rental_start/end_date`.
*   **`orders`**: Master order record.
    *   **Status Management**: Tracks lifecycle from `Pending` → `Delivered` → `Return Requested` (Buyer Disputed) → `Return In Progress` → `Returned`.
    *   **Dispute Metadata**: Stores `return_reason`, `return_details`, `return_images` (array), and `admin_rejection_reason`.
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

6.  **Schedule & Background Jobs:**
    *   The platform handles automated returns via a scheduled task.
    *   Ensure your server's cron runs the Laravel scheduler:
    ```bash
    * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
    ```
    *   **Manual Trigger**: You can manually process pending returns using:
    ```bash
    php artisan orders:process-returns
    ```

7.  **Run Server:**
    ```bash
    php artisan serve
    ```

---

## 7. Developer Notes

*   **Routes:** `routes/web.php` is the primary entry point. It contains both User and Admin routes. Admin routes are grouped under `middleware(['admin.auth'])`.
*   **Validation:** Most validation logic resides directly in Controllers (e.g., `ClothController::store`).
*   **API:** The project is primarily server-side rendered (Blade). API routes are minimal.
*   **Linting:** `php artisan pint` (if installed) or standard PSR-12 coding standards.
*   **Admin Sidebar:** The sidebar features a "Software Setup" mode. Clicking the button at the bottom toggles the menu to show only setup-related links (Users, Categories, Attributes), keeping the main interface clean for daily operations.
