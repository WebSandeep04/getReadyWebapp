# Architectural Plan: Rental Period Extension Handling

## Introduction
This document outlines the architectural design and implementation strategy for the Rental Period Extension feature in the GetReady platform. The goal is to allow customers to extend their rental period before the original end date, ensuring data consistency and availability integrity.

## Core Objectives
1.  **Transparency**: Maintain a clear history of original bookings vs. extensions.
2.  **Accuracy**: Ensure pricing and taxes are calculated exactly as per the 4-day base rent model.
3.  **Integrity**: Prevent double-bookings by strictly validating against the current `AvailabilityBlock` logic with **Order-Aware Context**.
4.  **Flexibility**: Support admin overrides and multiple extensions.

---

## 1. Database Schema Implementation

### Model: `OrderExtension`
Tracks extensions separately while updating the parent order for current state.

| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Unique identifier. |
| `order_id` | Foreign Key | Links to the `orders` table. |
| `old_rental_to` | Date | Return date before this extension. |
| `new_rental_to` | Date | Updated return date. |
| `extra_days` | Integer | Additional days requested. |
| `additional_amount`| Decimal | Total extra cost (Rent + Comm + Taxes). |
| `payment_id` | Foreign Key | Link to the `payments` record. |
| `status` | Enum | `pending`, `paid`, `cancelled`, `expired`. |
| `is_admin_override`| Boolean | For manual/special extensions. |

---

## 2. Business Logic Implementation

### A. Extension Request (Validation)
1.  **UI Interface**: Users select a new return date using a **Flatpickr Calendar** in the "My Orders" modal.
2.  **Availability Check (Order-Aware)**: 
    - The system checks `isAvailable()` for the period `[current_rental_to + 1, new_rental_to + 1]`.
    - **Self-Conflict Resolution**: To allow extension, the system ignores blocks/buffers belonging to the **same order** (`excludeOrderId`).
    - **Buffer Knowledge**: Blocks now store the Order ID in the reason field (e.g., `Pickup buffer (Order #123)`) to prevent self-blocking.
3.  **Pricing Calculation**:
    - `Additional Base Rent = extra_days * (base_rent / 4)`.
    - Pro-rated calculations for Commissions and GST are applied.

### B. Confirmation & Processing
1.  Create `OrderExtension` in `pending`.
2.  Process payment via **Razorpay**.
3.  On success (`verifyPayment`):
    - **Update Order**: Sets `order.rental_to` to the new date.
    - **Atomic Availability Update**:
        - Locates and **removes** the old pickup buffer (generic or order-specific).
        - **Extends** the original "Rented" block for that order.
        - Creates a **new Pickup Buffer** at `new_rental_to + 1`.
        - Calls `updateAvailableBlocks` to consume newly blocked inventory.

---

## 3. Component Design

### Availability Service (`App\Services\AvailabilityService`)
-   `blockRentalDates(Cloth $cloth, $start, $end, int $orderId)`
-   `extendBlockedDates(Cloth $cloth, $oldEnd, $newEnd, int $orderId)`
-   `isAvailable(Cloth $cloth, $start, $end, $excludeOrderId = null): bool`
    - High-integrity check that handles both **Conflicts** (overlapping blocks) and **Coverage** (ensuring the item is listed as available).

### Extension Service (`App\Services\ExtensionService`)
-   `validateAvailability(Order $order, int $extraDays): bool`
-   `calculateExtensionCost(Order $order, int $extraDays): array`
-   `processExtension(Order $order, OrderExtension $extension): void`

---

## 4. Edge Cases & Handling

| Case | Handling Strategy |
| :--- | :--- |
| **Self-Blocking** | The `excludeOrderId` parameter in the availability check ensures users aren't blocked by their own current booking's return buffer. |
| **Legacy Buffers** | The system includes "Flexible Buffer Detection" to identify and clean up old blocks lacking explicit Order IDs. |
| **Atomic Updates** | Availability shifts happen within the `processExtension` flow to ensure the calendar is always in sync with payments. |
| **Multiple Extensions**| Each extension adds to the `extra_days` and creates a unique historical record in `order_extensions`. |

---

## 5. UI/UX Workflow
1.  Click **"Extend Rental"** on any active order.
2.  Pick a date on the calendar (restricted to dates after the current return).
3.  View real-time price breakdown and availability status.
4.  Pay via Razorpay.
5.  Order details and reverse logistics schedule update automatically.

---

## 6. Technical Buffers Clarification
- **12-17 Window**: For a 13-16 booking:
    - 12 is **Delivery Buffer**.
    - 17 is **Pickup Buffer**.
- **Extension to 18**:
    - 17 (old buffer) is removed.
    - 17-18 becomes **Rented**.
    - 19 becomes the **new Pickup Buffer**.

---
*Last Updated: 2026-02-23*
