# Seeders Documentation

This document lists all the database seeders used in the application and provides details about the data they populate.

## List of Seeders

1.  [DatabaseSeeder](#databaseseeder)
2.  [FrontendSettingsSeeder](#frontendsettingsseeder)

---

## DatabaseSeeder

**File:** `database/seeders/DatabaseSeeder.php`

The `DatabaseSeeder` is the main seeder class. It populates the database with essential initial data required for the application to function correctly, such as categories, sizes, colors, and a test user.

### Data Seeded:

*   **Test User**:
    *   Name: `Test User`
    *   Email: `test@example.com`
    *   Phone: `1234567890`
    *   Gender: `Male`

*   **Categories**:
    *   Wedding Wear
    *   Festive Wear
    *   Formal Wear
    *   Ethnic Wear
    *   Traditional Wear
    *   Pre-Wedding Shoot Outfits
    *   Indo-Western
    *   Western Wear
    *   Premium Wear

*   **Fabric Types**:
    *   Silk
    *   Cotton
    *   Polyester
    *   Linen

*   **Colors**:
    *   Red
    *   Blue
    *   Green
    *   Black
    *   White

*   **Sizes**:
    *   XS
    *   S
    *   M
    *   L
    *   XL
    *   XXL

*   **Bottom Types**:
    *   Straight
    *   Skinny
    *   Wide Leg
    *   Palazzo

*   **Body Type Fits**:
    *   Regular
    *   Slim
    *   Loose
    *   Oversized

*   **Garment Conditions**:
    *   Brand New
    *   Like New
    *   Excellent
    *   Good
    *   Fair

---

## FrontendSettingsSeeder

**File:** `database/seeders/FrontendSettingsSeeder.php`

The `FrontendSettingsSeeder` populates the `frontend_settings` table with configuration values for the dynamic frontend content. This allows administrators to manage website content like hero banners, about text, and footer details from the admin panel.

### Data Seeded (Grouped by Section):

#### Logo Section
*   `site_logo`: Path to site logo (`images/logo.png`)
*   `site_logo_alt`: Alt text for logo

#### Hero Section
*   `hero_title`: Main homepage title ("Welcome to GetReady")
*   `hero_subtitle`: Homepage subtitle
*   `hero_description`: Detailed hero description
*   `hero_image`: Path to hero image
*   `hero_button_text`: Call to action button text
*   `hero_button_url`: Button redirection URL

#### About Section
*   `about_title`: Title for the About section
*   `about_content`: Main text content for the About section
*   `about_image`: Image for the About section

#### Footer Section
*   `footer_title`: Brand title in footer
*   `footer_description`: Short description in footer
*   `footer_address`: Physical address
*   `footer_phone`: Contact phone number
*   `footer_email`: Contact email
*   `footer_copyright`: Copyright text

#### Social Media
*   `social_facebook`: Facebook URL
*   `social_instagram`: Instagram URL
*   `social_twitter`: Twitter URL

#### General Settings
*   `site_title`: Meta title for SEO
*   `site_description`: Meta description for SEO
*   `site_keywords`: Meta keywords for SEO
*   `contact_email`: General contact email
*   `contact_phone`: General contact phone
