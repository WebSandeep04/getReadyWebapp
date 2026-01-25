# Status & Lifecycle Documentation

This document provides a detailed overview of the status fields, their transitions, and the business logic associated with the lifecycle of various entities in the GetReady platform.

---

## 1. Order Status (`orders` table)

The `status` field in the `orders` table tracks the progress of a customer's purchase or rental transaction.

| Status | Code Location | Description |
|:--- |:--- |:--- |
| **`Pending`** | `CheckoutController@createOrder` | The initial state when a user creates an order but hasn't completed the payment yet. |
| **`Confirmed`** | `CheckoutController@verifyPayment` | Set automatically after successful payment verification via the Razorpay gateway. |
| **`Delivered`** | Admin Tracking (Manual) | Updated by the admin when the item has been successfully delivered/picked up by the customer. |
| **`Returned`** | `AdminController@markAsReturned` | Manual action by admin for rental items. Triggers the return of stock (SKU +1). |
| **`Cancelled`** | Admin/User Action | Used when an order is voided or cancelled before completion. |

**Key Transition:**
- `Pending` → `Confirmed`: Triggered by successful payment.
- `Confirmed` → `Returned`: Triggered by Admin action (specific to rentals).

---

## 2. Cloth Approval Status (`is_approved` in `clothes` table)

The `is_approved` field controls the visibility of seller listings on the public marketplace.

| Value | Label | Trigger Event |
|:--- |:--- |:--- |
| **`NULL`** | **Pending** | Default state when an item is **Created**, **Edited**, or **Resubmitted**. |
| **`1`** | **Approved** | Admin clicks "Approve" in the Approval Queue. The item goes live. |
| **`-1`** | **Rejected** | Admin clicks "Reject" (must provide a reason). The item is hidden from the public. |

**Important Note:**
- Any update to a cloth's details (`ClothController@update`) OR a resubmission via the rejection portal (`RejectionController@update`) will reset the status to `NULL` (Pending) and increment the `resubmission_count`.

---

## 3. Stock & Availability Management

The `sku` and `is_available` fields in the `clothes` table are dynamically updated based on orders.

### A. Purchase/Rental (Stock Out)
- **When:** Payment is verified (`CheckoutController@verifyPayment`).
- **Logic:** 
  - `sku` is decremented by the quantity ordered.
  - If `sku` reaches `0`, `is_available` is set to `false`.
- **Location:** `CheckoutController.php` (Lines 264-272).

### B. Rental Return (Stock In)
- **When:** Admin marks an order as "Returned" (`AdminController@markAsReturned`).
- **Logic:** 
  - `sku` is incremented by `1`.
  - `is_available` is set to `true`.
- **Location:** `AdminController.php` (Lines 448-450).

---

## 4. Payment Status (`payment_status` in `payments` table)

Payment status is tracked separately from the Order status to ensure financial auditing.

| Status | Defined In | Context |
|:--- |:--- |:--- |
| **`Paid`** | `CheckoutController@verifyPayment` | Payment successfully captured and verified. |
| **`Pending`** | Admin View Logic | Payment record exists but confirmation is awaited. |
| **`Failed`** | Gateway Response | Payment was attempted but failed at the gateway level. |
| **`unpaid`** | Admin Filter Logic | No payment record associated with the order yet. |

---

## 5. Development Guidelines
- Always use the `Order::update(['status' => '...'])` pattern to ensure model events (if any) are triggered.
- When rejecting a cloth, ensure a notification is created via `Notification::create()` with `type => 'warning'` to inform the seller.
- Stock logic is currently simplistic (assumes 1-to-1 return). Future updates should account for multi-quantity returns if applicable.
