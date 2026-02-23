# GetReady - Master System Guide

Welcome to the **GetReady** developer documentation. This document serves as the central hub for understanding the platform's architecture, business logic, and operational flows.

---

## 🚀 Platform Overview
**GetReady** is a high-performance fashion marketplace built with **Laravel 12**. It supports a dual-inventory model where users can both **buy** and **rent** clothing. The system features deep integrations with **Google Gemini AI** for content and **Xpressbees** for logistics.

---

## 🗺️ Documentation Directory

### 1. Core Architecture & Tech Stack
Detailed overview of the project structure, roles, and database schema.
👉 **[Codebase Overview](SYSTEM_GUIDE.md)** (Note: I will rename this to avoid confusion with the master guide, actually I'll make this SYSTEM_GUIDE.md and rename the moved one to CODEBASE_DETAILS.md)

*Revision: I'll name the master guide `SUMMARY.md` or keep it as `SYSTEM_GUIDE.md` and rename the other one.*

Let's stick to this hierarchy:
- `docs/SYSTEM_GUIDE.md` (This file - Master Hub)
- `docs/CODEBASE_DETAILS.md` (Technical deep dive)
- `docs/RENTAL_FLOW.md` (Rental & Shipping lifecycle)
- `docs/PURCHASE_FLOW.md` (Direct Buy lifecycle)
- `docs/USER_WORKFLOW.md` (User & Admin operational flows)
- `docs/DESIGN_SYSTEM.md` (Zero-Radius Design Tokens)
- `docs/reports/FINANCIAL_REPORT.md` (Financial logic & formulas)

---

## 🔄 Core System Flows

### A. The Rental Cycle
The most complex part of the platform, involving automated reverse logistics.
👉 **[Read Full Rental Logic](RENTAL_FLOW.md)**

### B. The Purchase Cycle (Direct Buy)
Simplified direct-sale model with single-leg shipping and stock management.
👉 **[Read Full Purchase Logic](PURCHASE_FLOW.md)**

### C. Product Lifecycle (Sell/Lend)
How items move from user closets to the live marketplace.
1. **Submission**: AI-assisted descriptions via Gemini.
2. **Admin Approval**: Mandatory review before listing goes live.
3. **Rejection Flow**: Guided corrections for sellers.
👉 **[Read User & Admin Workflows](USER_WORKFLOW.md)**

### C. Financial Reporting & Payouts
Precise tracking of platform revenue and seller settlements.
- **Revenue**: 20% Seller Comm + 20% Buyer Comm (40% Total).
- **Expenses**: Fixed placeholders for PG (₹30) and Delivery (₹80).
- **Payouts**: Net profit tracking after accounting for operational costs.
👉 **[Read Financial Formulas](reports/FINANCIAL_REPORT.md)**

---

## 🎨 Design Philosophy: "Zero Radius"
The admin panel uses a specialized "System UI" design system:
- **Strict Square Corners**: 0px border-radius globally.
- **High Contrast**: Bold typography and monochrome palettes.
- **Data Density**: Optimized for professional monitoring.
👉 **[Read Design System Guide](DESIGN_SYSTEM.md)**

---

## 🛠️ Management & Setup
- **Software Setup Mode**: Toggleable admin sidebar for master data.
- **Logistics**: Xpressbees integration status.
- **AI**: Gemini API configuration.
👉 **[Read Technical Setup](CODEBASE_DETAILS.md)**

---
*Last Updated: 2026-02-23*
