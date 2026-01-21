# GetReady - Clothing Rental & Resale Platform

GetReady is a modern, comprehensive web application designed for the circular fashion economy. It empowers users to **buy**, **sell**, and **rent** clothing items through a seamless, robust platform. Built with the latest **Laravel 12** and **Tailwind CSS 4.0**, it offers a premium user experience for customers, sellers, and administrators.

## 🚀 Key Features

### 🛍️ For Shoppers
*   **Unified Marketplace**: Browse a vast catalog of unique clothing items available for purchase or rent.
*   **Smart Filtering**: Filter by category, brand, color, size, fabric, and more to find the perfect fit.
*   **Interactive Community**:
    *   **Q&A**: Ask questions directly on product pages.
    *   **Reviews**: Read and write reviews to share experiences.
    *   **Replies**: Engage in threaded conversations on reviews and questions.
*   **Secure Checkout**: Streamlined cart and checkout process with order verification.
*   **User Dashboard**: Track orders, view history, and manage profile settings.

### 💼 For Sellers & Lenders
*   **Easy Listing**: Inuitive "Sell" flow to list items with detailed attributes and images.
*   **Rental Management**: Manage item availability (Availability Blocks) to effortless handle rental schedules.
*   **Sales Dashboard**: Dedicated "My Sales" view to track earnings and transaction history.
*   **Listing Management**: Edit, delete, and manage active listings.
*   **Rejection Handling**: View detailed reasons for rejected listings and resubmit with corrections.
*   **Reputation System**: Earn ratings from buyers to build trust.

### 🛡️ For Administrators
*   **Command Center**: A powerful admin dashboard with real-time statistics and metrics.
*   **Approval Workflow**: Rigorous "Cloth Approval" workspace to review, approve, or reject user-submitted listings.
*   **Order Supervision**: comprehensive oversight of all platform orders and transactions.
*   **Dynamic Catalog Management**: Complete control over all product attributes:
    *   Categories & Brands
    *   Colors & Sizes
    *   Fabric Types & Bottom Types
    *   Body Type Fits & Garment Conditions
*   **Frontend Management**: Configure site-wide settings and visuals directly from the panel.
*   **User Management**: Monitor user activity and manage accounts.

## 🛠️ Technology Stack

*   **Backend**: [Laravel 12.x](https://laravel.com)
*   **Language**: PHP 8.2+
*   **Frontend**: [Blade Templates](https://laravel.com/docs/blade)
*   **Styling**: [Tailwind CSS v4.0](https://tailwindcss.com) & Vite
*   **Database**: MySQL / SQLite
*   **Scripting**: JavaScript (ES6+)

## 📦 Key Data Models

*   **Core**: `User`, `Cloth`, `Order`, `CartItem`
*   **Catalog**: `Category`, `Brand`, `Color`, `Size`, `FabricType`, `BottomType`
*   **Social**: `ProductReview`, `ProductQuestion`, `Reply`, `Rating`
*   **System**: `Notification`, `FrontendSetting`, `AvailabilityBlock`, `Payment`

## ⚙️ Installation & Setup

Follow these steps to get the project running locally:

### 1. Prerequisites
Ensure you have the following installed:
*   PHP >= 8.2
*   Composer
*   Node.js & npm

### 2. Clone the Repository
```bash
git clone https://github.com/yourusername/getready.git
cd getready
```

### 3. Install Backend Dependencies
```bash
composer install
```

### 4. Install Frontend Dependencies
```bash
npm install
```

### 5. Environment Configuration
Copy the example environment file and configure your database contents:
```bash
cp .env.example .env
```
Open `.env` and set your database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

### 6. Application Key & Database
Generate the app key and run migrations:
```bash
php artisan key:generate
php artisan migrate
```
*(Optional) Seed the database with initial data:*
```bash
php artisan db:seed
```

### 7. Start Development Servers
You need to run both the Laravel server and the Vite development server.

**Terminal 1 (Backend):**
```bash
php artisan serve
```

**Terminal 2 (Frontend):**
```bash
npm run dev
```

### 8. Access the App
Open your browser and navigate to: `http://localhost:8000`

## 🤝 Contributing

Contributions are welcome! If you find a bug or have a feature request, please open an issue or submit a pull request.

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
