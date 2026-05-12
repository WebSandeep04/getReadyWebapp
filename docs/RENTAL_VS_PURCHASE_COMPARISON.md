# Rental vs. Purchase Flow Comparison

This document provides a comparative analysis of the **Rental** and **Purchase** workflows within the GetReady platform. While both share the same core infrastructure for orders, payments, and shipments, they diverge significantly in post-delivery logic, financial handling, and inventory management.

---

## 🚀 High-Level Summary

| Feature | **Rental Flow** | **Purchase Flow** |
| :--- | :--- | :--- |
| **Primary Goal** | Temporary usage for a fixed period. | Permanent ownership transfer. |
| **User Input** | Requires **Start Date** & **End Date**. | Simple "Buy Now" (No dates). |
| **Inventory** | Item remains available for other dates. | inventory (SKU) is **decremented**. |
| **Security Deposit** | **Charged** (Held until return). | **₹0** (Not applicable). |
| **Logistics** | **Two-way**: Forward (Delivery) & Reverse (Return). | **One-way**: Forward (Delivery) only. |
| **Post-Delivery** | Automated return scheduling & QC. | Transaction complete (unless disputed). |

---

## 🔍 Detailed Comparison

### 1. Product Selection & Cart

*   **Rental**:
    *   **Logic**: The user *must* select a rental duration.
    *   **Validation**: Checks if dates fall within explicitly defined "Available Blocks" and do not overlap with bookings.
    *   **Cart Data**: Stores `rental_start_date`, `rental_end_date`, and `purchase_type='rent'`.
*   **Purchase**:
    *   **Logic**: The user purchases the item outright.
    *   **Validation**: Checks if `check_sku > 0` and `is_purchased=true`.
    *   **Cart Data**: Stores `purchase_type='buy'`. No dates required.

### 2. Financial Model (20/20 Rule)

Both flows utilize the **20/20 Commission Model** (20% from Seller + 20% from Buyer), but the components differ.

| Component | Rental Calculation | Purchase Calculation |
| :--- | :--- | :--- |
| **Base Price** | Set by Seller (per 4 days). | Set by Seller (Selling Price). |
| **Buyer Pays** | Base + GST (18%) + Comm (20%) + **Security Deposit**. | Base + GST (18%) + Comm (20%). |
| **Seller Earns** | Base + GST Credit (if Reg) - Comm (20%) - TCS. | Base + GST Credit (if Reg) - Comm (20%) - TCS. |
| **Taxation** | 18% "Item Tax" (Service Fee if non-GST). | 18% "Item Tax" (Service Fee if non-GST). |
| **Security** | **Yes** (Refundable). | **No**. |

### 3. Fulfillment & Logistics

*   **Shared**: Both trigger an immediate **Forward Shipment** creation via Xpressbees API upon order confirmation.
*   **Rental Divergence**:
    *   Items are tracked via **Order ID** + **Date Range**.
    *   The system *does not* decrease global SKU count, but blocks the specific dates.
*   **Purchase Divergence**:
    *   **Inventory**: The system **permanently decrements** the `sku` count.
    *   If `sku` hits 0, the item is marked `is_available = false` and removed from listing.

### 4. Post-Delivery & Returns

This is the most significant difference between the two flows.

#### Rental (Loop)
1.  **Trigger**: `orders:process-returns` cron job runs daily.
2.  **Action**: Automatically schedules a **Reverse Pickup** on the `rental_end_date`.
3.  **Tracking**: Creates a sibling `shipment` record with `type='reverse'`.
4.  **Completion**: Order marked `Returned` upon reverse delivery -> Security Deposit refunded.

#### Purchase (Linear)
1.  **Trigger**: Courier webhook sends `Delivered` status.
2.  **Action**: Order marked `Delivered`.
3.  **End State**: The workflow **ends here**. No reverse pickup is scheduled.
    *   *Exception*: If the buyer manually raises a "Dispute" (e.g., damaged item), a manual return flow is triggered similar to rentals.

### 5. Admin & Reporting

*   **Financial Report**:
    *   **Rental**: Shows revenue recognized over the rental period. Security deposit is **liability** (not revenue).
    *   **Purchase**: Recognizes full revenue immediately. No liability column.
*   **Alert Calendar**:
    *   **Rental**: Shows **[P] Pickup** (Start Date) and **[R] Return** (End Date) events.
    *   **Purchase**: Shows **[S] Sale** event on the dispatch date.
*   **Payouts**:
    *   **Rental**: **Immediately Eligible** after item is **Returned**.
    *   **Purchase**: **Immediately Eligible** after item is **Delivered**.

### 6. Rental to Purchase Conversion (Mid-stream)
Buyers actively renting an item (status `Delivered`) can choose to convert the order into a purchase.
*   **Flow Shift**: Converts the order item from the **Rental (Loop)** to the **Purchase (Linear)** flow.
*   **Logistics Check**: The system disables the `process-returns` cron job's reverse pickup action for this specific item because the buyer is keeping it.
*   **Financial Handling**: Changes the `purchase_type` to `buy`. The system typically consumes the held Security Deposit to offset the purchase price so no refund takes place.
*   **Payout Shift**: The seller becomes eligible for a purchase payout instead of a rental one.

---

## 🛠 Database Impact

| Table | Rental Specifics | Purchase Specifics |
| :--- | :--- | :--- |
| `cart_items` | `rental_start_date`, `rental_end_date` populated. | Dates are `NULL`. |
| `order_items` | `base_rent` populated. `security_deposit` > 0. | `selling_price` populated. `security_deposit` = 0. |
| `orders` | `has_rental_items = true`. | `has_purchase_items = true`. |
| `shipments` | Can have `type='forward'` AND `type='reverse'`. | Only `type='forward'` (usually). |
