# GetReady Platform - Notifications Analysis

This document outlines all system-generated notifications across the GetReady platform, bifurcated by user role (Seller and Buyer). It includes the notification title, the underlying action/event that triggers it, and the timing of the notification.

---

## 1. General Notifications (Applicable to Both Roles)

| Notification Title | Trigger Action / Event | When it Hits |
| :--- | :--- | :--- |
| **Welcome to GetReady!** | User Registration / First Login | Hits immediately after a new user registers or completes their first successful login. |
| **New Reply** | Thread Reply | Hits immediately when another user (or admin) replies to a review or question authored by the user. |

---

## 2. Notifications for Sellers (Item Owners)

These notifications alert the seller about the status of their listings and activities performed by buyers on their items.

| Notification Title | Trigger Action / Event | When it Hits |
| :--- | :--- | :--- |
| **Item Listed Successfully Pending Approval** | New Item Submission | Hits immediately after the seller successfully submits a new clothing item for listing. |
| **Item Approved** | Admin Approval | Hits when the platform Admin reviews and approves the seller's submitted item, making it live. |
| **Item Rejected** | Admin Rejection | Hits when the Admin rejects a submitted item (includes the rejection reason for the seller to fix). |
| **New rental/purchase!** | Order Checkout | Hits immediately when a buyer successfully places an order (checkout) for the seller's item. |
| **New Question Asked** | Buyer Inquiry | Hits when a buyer submits a new question on the seller's product page. |
| **New Review Received** | Buyer Review | Hits when a buyer submits a star rating and review for the seller's item after an order. |
| **Rented Item Purchased!** | Rental Conversion | Hits when a renter decides to buy the item they are currently renting, paying the buyout price. |
| **Item Returning** | Auto-Return Scheduled | Hits automatically via system cron job when a rental period ends and the reverse logistics (return pickup) is scheduled. |
| **Return Scheduled** | Manual Return Approved | Hits when the Admin approves a buyer's manual return request, indicating the item will be shipped back. |

---

## 3. Notifications for Buyers (Renters / Purchasers)

These notifications keep the buyer informed about their orders, returns, refunds, and interactions.

| Notification Title | Trigger Action / Event | When it Hits |
| :--- | :--- | :--- |
| **Order Placed Successfully** | Order Checkout | Hits immediately after the buyer completes the payment and checkout process. |
| **Item Approved** | Admin Approval | Hits when the platform Admin reviews and approves the buyer's submitted item (acting as a seller), making it live. |
| **Item Rejected** | Admin Rejection | Hits when the Admin rejects a buyer's submitted item (includes the rejection reason for the buyer to fix). |
| **Question Answered** | Seller Reply | Hits when the seller responds to a question asked by the buyer on a product page. |
| **Return Reminders** | Approaching Rental End | Hits automatically via system cron job (usually 1-2 days before) reminding the buyer that their rental period is ending soon. |
| **Return Shipment Scheduled** | Rental Period Ended | Hits automatically via system cron job when the rental period expires and the AWB/pickup is successfully scheduled for the return. |
| **Return Request Approved** | Admin Return Approval | Hits when the Admin approves the buyer's manual request to return an item, initiating the reverse pickup. |
| **Return Request Rejected** | Admin Return Rejection | Hits when the Admin rejects the buyer's manual return request (includes the reason for rejection). |
| **Full Refund Processed** | Refund & Security Deposit | Hits when the Admin or system successfully processes the refund and/or returns the security deposit to the buyer's account. |

---

## 4. Notifications for Admin

| Notification Title | Trigger Action / Event | When it Hits |
| :--- | :--- | :--- |
| **Item Resubmitted for Approval** | Seller Resubmission | Hits when a seller updates and resubmits an item that was previously rejected by the Admin. |

