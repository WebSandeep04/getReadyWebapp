# Rental Flow Analysis: Plan Your Rental

This document explains the technical flow of the "Plan your rental" feature on the product detail page (`/clothes/{id}`).

## 1. User Interface (Frontend)
The rental interface is located in `resources/views/clothes/show.blade.php`.

### UI Components
- **Start Date Input** (`#start_date`): A readonly input field using Flatpickr.
- **End Date Input** (`#start_date`): A readonly input field using Flatpickr.
- **Rental Summary** (`#rental-summary`): A hidden div that reveals the cost breakdown once dates are selected.
- **Rent Button** (`#productRentBtn`): The main call-to-action button, initially disabled.

### JavaScript Logic (`resources/views/clothes/show.blade.php`)
The page initializes `clothData` object containing:
- `rentPrice`: Base rental price (for the minimum 4-day period).
- `securityDeposit`: The refundable deposit amount.
- `availableBlocks`: Whitelist of available dates.
- `blockedBlocks`: Blacklist of unavailable dates (rented or owner-blocked).
- `isAlwaysAvailable`: Boolean flag.

#### Date Selection (Flatpickr)
1.  **Initialization**:
    -   Attributes `minDate: "today"` and `disable` array are used to restrict selection to valid future dates.
    -   `getDisabledDatesArray()` function calculates valid dates based on `availableBlocks` vs `blockedBlocks`.
2.  **Start Date Selection**:
    -   When a user picks a start date, the `onChange` handler triggers.
    -   It automatically sets the **End Date** to 3 days after the start date (enforcing the 4-day minimum).
    -   It verifies if this auto-selected End Date is available.
3.  **Validation**:
    -   `validateAndCalculateRental()` is called on every date change.
    -   **Availability Check**: Iterates through `availableBlocks` to ensure the *entire* selected range falls within a valid block.
    -   **Blocking Check**: Iterates through `blockedBlocks` to ensure *no overlap* with blocked periods.

#### Cost Calculation
The system enforces a **Minimum Rental Period of 4 Days**.
-   **Base Cost**: The `rent_price` stored in the database is treated as the cost for the base 4-day period.
-   **Per Day Rate**: Calculated as `Base Price / 4`.
-   **Total Rental Cost**:
    -   If Duration <= 4 days: `Base Price`
    -   If Duration > 4 days: `Base Price + ((Duration - 4) * Per Day Rate)`

## 2. Data Submission (AJAX)
When the user clicks the "RENT NOW" button:
1.  **Frontend Validation**: Re-checks dates and duration > 4 days.
2.  **AJAX Request**: Sends a POST request to `/cart/add`.

## 3. Backend Processing (`CartController@addToCart`)
The request is handled by `App\Http\Controllers\CartController`.

1.  **Authentication & Basic Validation**: Ensures user login, valid IDs, and `rental_days >= 4`.
2.  **Availability Validation**:
    -   **Mirroring Frontend Logic**: The controller calls `checkAvailability()` to strictly verify that the requested dates are valid.
    -   **Whitelist**: If specific available dates are set, the request must fall *entirely* within them.
    -   **Blacklist**: The request must *not overlap* with any blocked dates.
3.  **Cart Management**: Updates existing item or creates a new one.
4.  **Response**: Returns success JSON.

---
**Status**: The rental flow is fully synchronized. Both Frontend (JS) and Backend (PHP) enforce the exact same strict availability and blocking rules to ensure no invalid bookings can be made.
