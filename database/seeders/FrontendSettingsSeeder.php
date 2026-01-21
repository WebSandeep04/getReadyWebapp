<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FrontendSetting;

class FrontendSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Logo Section
            [
                'key' => 'site_logo',
                'value' => 'images/logo.png',
                'type' => 'image',
                'section' => 'logo',
                'label' => 'Site Logo',
                'description' => 'Main site logo (recommended size: 200x60px)'
            ],
            [
                'key' => 'site_logo_alt',
                'value' => 'GetReady Logo',
                'type' => 'text',
                'section' => 'logo',
                'label' => 'Logo Alt Text',
                'description' => 'Alternative text for the logo'
            ],

            // Hero Section
            [
                'key' => 'hero_title',
                'value' => 'Welcome to GetReady',
                'type' => 'text',
                'section' => 'hero',
                'label' => 'Hero Title',
                'description' => 'Main heading on the homepage'
            ],
            [
                'key' => 'hero_subtitle',
                'value' => 'Your premier destination for fashion rental',
                'type' => 'text',
                'section' => 'hero',
                'label' => 'Hero Subtitle',
                'description' => 'Subtitle text below the main title'
            ],
            [
                'key' => 'hero_description',
                'value' => 'Discover amazing fashion pieces for your special occasions. Rent, wear, and return with ease.',
                'type' => 'textarea',
                'section' => 'hero',
                'label' => 'Hero Description',
                'description' => 'Detailed description for the hero section'
            ],
            [
                'key' => 'hero_image',
                'value' => 'images/main.png',
                'type' => 'image',
                'section' => 'hero',
                'label' => 'Hero Image',
                'description' => 'Main hero image (recommended size: 1200x600px)'
            ],
            [
                'key' => 'hero_button_text',
                'value' => 'Start Shopping',
                'type' => 'text',
                'section' => 'hero',
                'label' => 'Hero Button Text',
                'description' => 'Text for the main call-to-action button'
            ],
            [
                'key' => 'hero_button_url',
                'value' => '/clothes',
                'type' => 'text',
                'section' => 'hero',
                'label' => 'Hero Button URL',
                'description' => 'URL for the hero button'
            ],

            // About Section
            [
                'key' => 'about_title',
                'value' => 'About GetReady',
                'type' => 'text',
                'section' => 'about',
                'label' => 'About Title',
                'description' => 'Title for the about section'
            ],
            [
                'key' => 'about_content',
                'value' => 'GetReady is your premier fashion rental platform, offering a curated collection of designer pieces for every occasion. We believe that everyone deserves to look and feel amazing without the commitment of ownership.',
                'type' => 'textarea',
                'section' => 'about',
                'label' => 'About Content',
                'description' => 'Main content for the about section'
            ],
            [
                'key' => 'about_image',
                'value' => 'images/about.jpg',
                'type' => 'image',
                'section' => 'about',
                'label' => 'About Image',
                'description' => 'Image for the about section (recommended size: 600x400px)'
            ],

            // Footer Section
            [
                'key' => 'footer_title',
                'value' => 'GetReady',
                'type' => 'text',
                'section' => 'footer',
                'label' => 'Footer Title',
                'description' => 'Title displayed in the footer'
            ],
            [
                'key' => 'footer_description',
                'value' => 'Your trusted partner in fashion rental. Quality, style, and convenience all in one place.',
                'type' => 'textarea',
                'section' => 'footer',
                'label' => 'Footer Description',
                'description' => 'Description text in the footer'
            ],
            [
                'key' => 'footer_address',
                'value' => '123 Fashion Street, Style City, SC 12345',
                'type' => 'text',
                'section' => 'footer',
                'label' => 'Footer Address',
                'description' => 'Company address for the footer'
            ],
            [
                'key' => 'footer_phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'text',
                'section' => 'footer',
                'label' => 'Footer Phone',
                'description' => 'Contact phone number'
            ],
            [
                'key' => 'footer_email',
                'value' => 'info@getready.com',
                'type' => 'text',
                'section' => 'footer',
                'label' => 'Footer Email',
                'description' => 'Contact email address'
            ],
            [
                'key' => 'footer_copyright',
                'value' => '© 2024 GetReady. All rights reserved.',
                'type' => 'text',
                'section' => 'footer',
                'label' => 'Footer Copyright',
                'description' => 'Copyright text for the footer'
            ],

            // Social Media
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/getready',
                'type' => 'text',
                'section' => 'social',
                'label' => 'Facebook URL',
                'description' => 'Facebook page URL'
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/getready',
                'type' => 'text',
                'section' => 'social',
                'label' => 'Instagram URL',
                'description' => 'Instagram profile URL'
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/getready',
                'type' => 'text',
                'section' => 'social',
                'label' => 'Twitter URL',
                'description' => 'Twitter profile URL'
            ],

            // General Settings
            [
                'key' => 'site_title',
                'value' => 'GetReady - Fashion Rental Platform',
                'type' => 'text',
                'section' => 'general',
                'label' => 'Site Title',
                'description' => 'Main site title for SEO'
            ],
            [
                'key' => 'site_description',
                'value' => 'Your premier destination for fashion rental. Rent designer pieces for special occasions.',
                'type' => 'textarea',
                'section' => 'general',
                'label' => 'Site Description',
                'description' => 'Site meta description for SEO'
            ],
            [
                'key' => 'site_keywords',
                'value' => 'fashion rental, designer clothes, dress rental, formal wear',
                'type' => 'text',
                'section' => 'general',
                'label' => 'Site Keywords',
                'description' => 'SEO keywords for the site'
            ],
            [
                'key' => 'contact_email',
                'value' => 'support@getready.com',
                'type' => 'text',
                'section' => 'general',
                'label' => 'Contact Email',
                'description' => 'Main contact email address'
            ],
            [
                'key' => 'contact_phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'text',
                'section' => 'general',
                'label' => 'Contact Phone',
                'description' => 'Main contact phone number'
            ]
        ];

        foreach ($settings as $setting) {
            FrontendSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
