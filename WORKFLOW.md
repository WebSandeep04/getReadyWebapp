# System Workflow Guide

This document provides a brief overview of how the **GetReady** platform operates, detailing the life cycle of products, orders, and administrative oversight.

---

## 1. Product Life Cycle (Selling & Lending)

The platform supports both direct sales and rentals. Users can monetize their wardrobe through a structured flow:

1.  **Submission**: A user navigates to the "Sell" section and provides product details (images, category, brand, sizing, and specific attributes).
2.  **AI Assistance**: The system integrates with **Google Gemini API** to optionally generate high-quality product descriptions based on user-provided titles and attributes.
3.  **Availability Management**: For rental items, users can define "Availability Blocks" to prevent bookings during specific periods.
4.  **Admin Review**: Every submission enters a "Pending" state and is not visible to other users until an administrator reviews and approves it.
5.  **Rejection/Correction**: If an item is rejected, the user receives a specific reason and can resubmit the item with corrections.

---

## 2. Discovery & Interaction

Once an item is live, the marketplace facilitates discovery:

*   **Smart Search**: Users can filter the catalog by multiple facets (Color, Size, Fabric, Condition, etc.).
*   **Community Trust**: 
    *   **Q&A**: Potential buyers/renters can ask questions directly on the product page.
    *   **Reviews**: Users can rate and review items post-transaction.
    *   **Replies**: Sellers and Admins can engage with users through threaded replies.

---

## 3. Transaction Workflow

### Purchase Flow
1.  **Cart**: Users add items (Sale or Rent) to a unified cart.
2.  **Checkout**: The system calculates total amounts, including rental durations and security deposits.
3.  **Payment**: Integrated payment gateway (Razorpay style flow) handles the transaction.
4.  **Order Processing**: Upon payment, the order status moves to "Processing," alerting the Admin.

### Rental & Security Flow
*   **Security Deposit**: Rental items require a security amount which is held by the platform.
*   **Tracking**: Managed via the Admin Dashboard.
*   **Return Process**: Once the renter returns the item, the Admin verifies the condition.
*   **Security Refund**: After verification, the Admin marks the security as "Returned," triggering the refund tracking process.

---

## 4. Administrative Command Center

The Admin Panel serves as the brain of the platform:

*   **Real-time Dashboard**: AJAX-powered metrics for platform health (Revenue, Pending Listings, Order Volume).
*   **Security & Payment Modules**: Dedicated tracking for every rupee moving through the system, ensuring security deposits are managed accurately.
*   **Software Setup Mode**: A specialized interface for managing master data (Categories, Brands, Attributes) and user roles without cluttering the day-to-day operational dashboard.
*   **User & Role Management**: Granular control over platform access and permissions.

---

## 5. Technology Integration

*   **Laravel 12**: Core framework for robust backend logic and routing.
*   **Tailwind CSS 4.0**: Modern, responsive frontend styling.
*   **Xpressbees**: Integration for shipping logistics and automated tracking updates.
*   **AJAX Architecture**: Used extensively in the Admin Dashboard to provide a seamless, SPA-like experience for data updates.
