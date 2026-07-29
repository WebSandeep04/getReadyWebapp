# Invoice Generation Logic Analysis

Based on a thorough analysis of the codebase (specifically `InvoiceService.php`, `CheckoutController.php`, and `OrderExtensionController.php`), here is the complete breakdown of how, when, and for whom invoices are generated.

## 1. Trigger Scenarios

Invoices are generated in two distinct scenarios within the application:

1. **Initial Order Checkout:** 
   - **Trigger:** When a buyer successfully completes the checkout process and the payment is verified (`CheckoutController::verifyPayment`).
   - **Method:** `InvoiceService::generateOrderInvoices(Order $order)`
2. **Rental Extension:** 
   - **Trigger:** When a buyer extends an active rental period and successfully pays the additional extension fees (`OrderExtensionController::verifyExtensionPayment`).
   - **Method:** `InvoiceService::generateExtensionInvoices(OrderExtension $extension)`

---

## 2. Types of Invoices Generated

Because this is a multi-vendor/C2C platform, a single transaction involves three parties (Buyer, Seller, and Platform). To maintain strict tax compliance and transparent accounting, **three distinct types of invoices** are generated for every transaction (both initial orders and extensions).

### A. Rent / Sale Invoice (Seller to Buyer)
This is the primary bill of sale or rental agreement between the two users.
- **Type:** `rent_sale`
- **Issued By:** The Seller (Cloth Owner)
- **Issued To:** The Buyer
- **Invoice Prefix:** 
  - `INV-` (for initial orders)
  - `EXT-` (for extensions)
- **Logic & Contents:** 
  - Contains the Base Rent or Base Sale Price of the item.
  - Contains the GST on the rent/sale, **BUT only if the seller is GST registered** (`is_seller_gst == true`). 
  - If the seller is not GST registered, the tax portion on this specific invoice is ₹0.

### B. Platform Fee Invoice (Platform to Seller)
This is the platform's bill to the seller for providing the marketplace service.
- **Type:** `platform_fee_seller`
- **Issued By:** The Platform (GetReady)
- **Issued To:** The Seller
- **Invoice Prefix:**
  - `GR-S-` (for initial orders)
  - `GR-EXT-S-` (for extensions)
- **Logic & Contents:**
  - Contains the **Seller Commission** charged by the platform for facilitating the transaction.
  - Contains the 18% GST levied specifically on that seller commission (`seller_commission_gst`).
  - Contains any TCS (Tax Collected at Source) amount applicable to the seller.

### C. Platform Fee Invoice (Platform to Buyer)
This is the platform's bill to the buyer for using the service, as well as a mechanism for handling unregistered seller taxes.
- **Type:** `platform_fee_buyer`
- **Issued By:** The Platform (GetReady)
- **Issued To:** The Buyer
- **Invoice Prefix:**
  - `GR-B-` (for initial orders)
  - `GR-EXT-B-` (for extensions)
- **Logic & Contents:**
  - Contains the **Buyer Commission** (Convenience fee) charged by the platform.
  - Contains the 18% GST levied specifically on that buyer commission (`buyer_commission_gst`).
  - **Crucial Logic (Reverse Charge mechanism):** If the seller is *not* GST registered (`!is_seller_gst`), the platform assumes the responsibility of collecting the GST on the rental amount. Therefore, the `rent_gst` is added to *this* invoice as an additional fee collected by the platform from the buyer.

---

## 3. Data Structure & Storage
- **Database:** All invoices are recorded in the `invoices` database table, linked to the `order_id` (and `order_extension_id` if applicable).
- **PDF Generation:** The system uses `Barryvdh\DomPDF` to generate physical PDF files for each of these three invoices.
- **Storage:** The PDFs are saved in the `storage/app/public/invoices/{order_id}/` directory and can be downloaded by the respective parties from their transaction dashboards.
