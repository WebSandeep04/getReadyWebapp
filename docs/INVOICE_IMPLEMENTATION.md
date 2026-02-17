# Technical Implementation Plan: Invoice System

This document outlines the technical steps required to implement the 3-invoice system within the GetReady Laravel application.

---

## 1. Database Schema Design

We need a dedicated `invoices` table to store the generated documents and their metadata.

### New Table: `invoices`
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key |
| `order_id` | BigInt | Foreign Key to `orders` |
| `order_item_id` | BigInt | Nullable (If invoices are per-item, otherwise per-order) |
| `invoice_number` | String | Unique Identifier (e.g., `INV-2024-001`) |
| `type` | Enum | `rent_sale` (Seller->Buyer), `platform_fee_seller` (GR->Seller), `platform_fee_buyer` (GR->Buyer) |
| `amount` | Decimal | Total invoice amount |
| `tax_amount` | Decimal | Total GST component |
| `pdf_path` | String | Path to stored PDF file in `storage/` |
| `issued_by_id` | BigInt | User ID (Seller) or `NULL` (Platform) |
| `issued_to_id` | BigInt | User ID (Buyer/Seller) |
| `created_at` | Timestamp | Date of issuance |

---

## 2. PDF Generation Logic (Service Layer)

We will create a service class `App\Services\InvoiceService` to handle the generation.

**Library**: Use `barryvdh/laravel-dompdf` (Standard Laravel PDF wrapper) or `spatie/laravel-browsershot` for high-quality rendering.

### Method: `generateOrderInvoices(Order $order)`
This method triggers when an order becomes `Confirmed`.

1.  **Generate Invoice A (Rent/Sale)**:
    *   **Data**: Seller details, Buyer details, Item breakdown (Base Price + GST).
    *   **Template**: `resources/views/invoices/seller_to_buyer.blade.php`
    *   **Storage**: `invoices/{order_id}/A_seller_invoice.pdf`

2.  **Generate Invoice B (Platform Fee - Seller)**:
    *   **Data**: Platform details (GetReady), Seller details, Commission + GST + TCS.
    *   **Template**: `resources/views/invoices/platform_to_seller.blade.php`
    *   **Storage**: `invoices/{order_id}/B_commission_invoice.pdf`

3.  **Generate Invoice C (Platform Fee - Buyer)**:
    *   **Data**: Platform details, Buyer details, Buyer Commission + GST.
    *   **Template**: `resources/views/invoices/platform_to_buyer.blade.php`
    *   **Storage**: `invoices/{order_id}/C_convenience_fee.pdf`

---

## 3. Integration Points

### A. Trigger Event
In `CheckoutController@verifyPayment` (or the webhook handler):
```php
if ($payment->status === 'captured') {
    // ... existing logic ...
    
    // NEW: Generate Invoices
    $invoiceService = new InvoiceService();
    $invoiceService->generateOrderInvoices($order);
}
```

### B. User Interface
1.  **Buyer Dashboard (`/my-orders`)**:
    *   Add "Download Invoice" dropdown.
    *   Options: "Order Invoice" (Merges A + C) or individual downloads.
2.  **Seller Dashboard (`/my-sales`)**:
    *   Add "Tax Invoice" button (Downloads Invoice A).
    *   Add "Commission Invoice" button (Downloads Invoice B).
3.  **Admin Panel**:
    *   View all linked invoices in the Order Detail view.

---

## 4. Numbering System (Compliance)

*   **Seller Invoices**: Technically, these should follow the *Seller's* own accounting sequence. However, typically marketplaces generate a sequence like `GR/{SELLER_ID}/{YYYY}/001` to maintain uniqueness while acting as the issuer.
*   **Platform Invoices**: Strict sequential numbering `GR/INV/{YYYY}/0001` for GST filing.

---

## 5. Development Checklist
- [ ] Create migration for `invoices` table.
- [ ] Install PDF library (`composer require barryvdh/laravel-dompdf`).
- [ ] Create Blade templates for 3 invoice types.
- [ ] Implement `InvoiceService` with generation logic.
- [ ] Hook service into `CheckoutController`.
- [ ] Add download routes and UI buttons.
