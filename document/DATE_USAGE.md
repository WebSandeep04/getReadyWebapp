# Date Usage in Codebase

This document provides a comprehensive analysis of date references and handling across the `getReady` codebase.

## 1. Database and Migrations

Code dealing with dates often interacts with database columns like `created_at`, `updated_at`, `start_date`, `end_date`, `rental_start_date`, etc.

*   `created_at` and `updated_at`: Standard Laravel timestamps present in almost all tables (users, clothes, cart_items, reviews, questions, etc.).
*   `clothes` table (implicit via availability blocks):
    *   Dates are managed in the related `availability_blocks` table.
*   `availability_blocks` table:
    *   `start_date`
    *   `end_date`
*   `cart_items` table:
    *   `rental_start_date`
    *   `rental_end_date`

## 2. Views (Blade Files)

### `resources\views\clothes\show.blade.php` (Product Details Page)

This file contains the most complex date logic in the frontend, primarily handling rental date selection and availability display.

*   **Logic**:
    *   **Availability Timeline**: Loops through `$cloth->availabilityBlocks` (specifically 'available' type) to show date ranges.
        *   Uses `\Carbon\Carbon::parse($block->start_date)->format('d M')` for formatted display.
    *   **Rental Form**: Contains inputs for `start_date` and `end_date`.
    *   **JavaScript (Flatpickr Integration)**:
        *   Calculates `disabledDatesArray` based on availability/blocked blocks.
        *   Initializes `flatpickr` for `#start_date` and `#end_date`.
        *   **Start Date Logic**:
            *   Sets `minDate: "today"`.
            *   On change, sets `#end_date` minimum to start date + 4 days (minimum rental period).
            *   Auto-selects an end date if possible.
        *   **End Date Logic**:
            *   Validates rental duration.
    *   **Cart Addition**: Sends `rental_start_date` and `rental_end_date` via AJAX to `/cart/add`.

### `resources\views\clothes\edit.blade.php` (Edit Cloth Page)

*   **Logic**:
    *   Availability management section allows users to add/edit date ranges.
    *   `start_date` and `end_date` inputs for availability blocks.
    *   Likely uses `Carbon` to format existing dates for input values (e.g., `$block->start_date->format('Y-m-d')`).

### `resources\views\sell.blade.php` (Sell Page)

*   **Logic**:
    *   Similar to the edit page, contains logic for setting initial availability dates.
    *   Inputs for `availability_blocks[...][start_date]` and `[end_date]`.

## 3. Controllers and Logic

### `App\Http\Controllers\CartController.php`

*   `addToCart` method (inferred):
    *   Receives `rental_start_date` and `rental_end_date`.
    *   Likely validates these dates (e.g., valid date format, start < end).
    *   Stores them in `cart_items` table.

### `App\Http\Controllers\ClothController.php`

*   `store` / `update`:
    *   Validates `availability_blocks` dates:
        *   `required|date`
        *   `after_or_equal:start_date`
    *   Parses dates using Carbon for storage.

### `tests\Unit\CartItemTest.php`

*   Tests verify that `rental_start_date` and `rental_end_date` are correctly set and retrieved.
*   Uses `now()` and `addDays()` for generating test data.

## 4. Frontend Libraries

*   **Flatpickr**: The primary library used for the date picker UI in the product detail page (`show.blade.php`).
*   **Moment.js / Native Date**: Native JavaScript `Date` objects are used extensively in `product.js` (embedded in `show.blade.php`) for date arithmetic (calculating duration, checking overlaps).

## 5. Routes

Several API endpoints and routes implicitly handle dates via `created_at`/`updated_at` or specific date fields in request payloads:

*   `/cart/add` (POST): Accepts rental dates.
*   `/listed-clothes` (POST/PUT): Accepts availability dates.

## Summary of Date Formats

*   **Database**: `Y-m-d` (e.g., 2023-10-27) or `Y-m-d H:i:s` (timestamps).
*   **Frontend Display**:
    *   `d M` (e.g., 27 Oct) - Availability timeline.
    *   `d M, Y` (e.g., 27 Oct, 2023) - Long format.
*   **Input Values**: `Y-m-d` (HTML5 date inputs and Flatpickr default).
