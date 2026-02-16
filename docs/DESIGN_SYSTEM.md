# Admin Design System - "Zero Radius"

This document defines the high-contrast, professional design tokens used across the GetReady Admin Panel, specifically in the Reporting and Operations modules.

## 1. Core Philosophy
The design follows a "System UI" approach: high density, high contrast, and zero soft edges. It is designed to look like a premium enterprise dashboard.

## 2. Color Palette
| Token | Value | Usage |
| :--- | :--- | :--- |
| **Primary Header** | `rgb(33 37 41)` | Main navigation bars, modal headers. |
| **Accent Indigo** | `#4f46e5` | Primary actions, **Pickup [P]** indicators. |
| **Accent Rose** | `#e11d48` | Warning states, **Return [R]** indicators. |
| **Accent Emerald** | `#059669` | Success states, **Sale [S]** indicators. |
| **Border Dark** | `#000000` | High-contrast component borders. |
| **Border Soft** | `#e5e7eb` | Subtle grid lines (FullCalendar). |

## 3. Typography & Sizing
*   **Font Weight**: Bold (700) or Extra Bold (800) for all headers and labels.
*   **Casing**: `text-transform: uppercase` for labels and stat titles.
*   **Data Density**:
    *   Table font size: `0.7rem`.
    *   Stat box labels: `0.65rem`.
    *   Stat box values: `1.25rem` to `1.5rem`.

## 4. Component Rules
*   **Border Radius**: **STRICT ZERO**.
    *   CSS Rule: `*, ::before, ::after { border-radius: 0 !important; }`
*   **Borders**: Solid 1px or 2px black.
*   **Shadows**: "Hard" shadows with 0 blur.
    *   `box-shadow: 10px 10px 0px rgba(0,0,0,0.05);`

## 5. Implementation Status
*   [x] **Alert Calendar**: Fully migrated to Zero Radius.
*   [x] **Financial Reports**: Fully migrated to Zero Radius.
*   [ ] **Orders Dashboard**: Partially migrated.
*   [ ] **User Management**: Pending migration.
