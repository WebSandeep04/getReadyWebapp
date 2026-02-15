# Product Pricing & Revenue Logic Documentation

This document outlines the complete logic for product pricing, rental calculations, and revenue distribution.

## 1. Internal Constants (20/20 Revenue Model)
- **Seller Commission Rate**: 20% of the rent entered by the seller (Deducted from payout).
- **Buyer Commission Rate**: 20% of the rent entered by the seller (Added to the display price).
- **Total Platform Revenue**: **40% of the Base Rent** (e.g., ₹200 + ₹200 = ₹400 on a ₹1,000 rent).
- **GST Rate (Rent)**: 18% (Applicable only for GST Registered Sellers).
- **GST Rate (Commission)**: 18% (Applicable to all commission fees).
- **Minimum Rental Period**: 4 Days.

---

## 2. Case 1: GST Registered Seller (`is_gst = 1`)
When a seller is registered under GST, taxes are applied to both the rent and the platform fees.

### A. Prices & Display
- **Seller Input (Rent)**: ₹1,000
- **Marketplace Display (Buyer)**: **₹1,200** (Rent ₹1,000 + Buyer Commission ₹200)
- **Seller Dashboard View**: **₹800** (Rent ₹1,000 - Seller Commission ₹200)

### B. Buyer Checkout Breakdown
| Component | Amount | Logic |
| :--- | :--- | :--- |
| **Base Rent** | ₹1,000 | Seller Entered Price |
| **Buyer Commission** | ₹200 | 20% of Base Rent |
| **GST on Rent (18%)** | ₹180 | Collected for Seller |
| **GST on Commission** | ₹36 | 18% of ₹200 |
| **Total (Excl. Security)** | **₹1,416** | 1000 + 200 + 180 + 36 |
| **Security Deposit** | ₹1,000 | Refundable |

---

## 3. Case 2: Non-GST Registered Seller (`is_gst = 0`)
When a seller is NOT registered, no GST is charged on the rent portion.

### A. Prices & Display
- **Seller Input (Rent)**: ₹1,000
- **Marketplace Display (Buyer)**: **₹1,200** (Rent ₹1,000 + Buyer Commission ₹200)
- **Seller Dashboard View**: **₹800** (Rent ₹1,000 - Seller Commission ₹200)

### B. Buyer Checkout Breakdown
| Component | Amount | Logic |
| :--- | :--- | :--- |
| **Base Rent** | ₹1,000 | Seller Entered Price |
| **Buyer Commission** | ₹200 | 20% of Base Rent |
| **GST on Rent** | ₹0 | Not Applicable |
| **GST on Commission** | ₹36 | 18% of ₹200 |
| **Total (Excl. Security)** | **₹1,236** | 1000 + 200 + 36 |
| **Security Deposit** | ₹1,000 | Refundable |

---

## 4. Rental Duration & Extensions
The logic above applies to the **Base Period (4 Days)**. For longer durations:
- **Rent Extension**: `Additional Days * (Rent / 4)`
- All commissions and GST calculations apply to the **Total Rental Cost** (Base + Extension).

---

## 5. Implementation Summary
| Perspective | GST Registered | Non-GST Registered |
| :--- | :--- | :--- |
| **Seller Enters** | ₹1,000 | ₹1,000 |
| **Buyer Sees** | ₹1,200 | ₹1,200 |
| **Buyer Pays (Tax Incl)** | ₹1,416 | ₹1,236 |
| **Seller Receives Net** | ₹934 (after 20% comm + GST on comm + 1% TCS) | ₹800 (TBC based on commission only) |
| **Platform Revenue** | **₹400** (+ GST Collection ₹72) | **₹400** (+ GST Collection ₹36) |
