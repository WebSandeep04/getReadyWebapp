<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\FabricType;
use App\Models\Color;
use App\Models\Size;
use App\Models\BottomType;
use App\Models\BodyTypeFit;
use App\Models\GarmentCondition;
use App\Models\Brand;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user if not exists
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '1234567890',
                'gender' => 'Male',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        // Create categories
        $categories = [
            'Wedding Wear', 'Festive Wear', 'Formal Wear', 'Ethnic Wear', 
            'Traditional Wear', 'Pre-Wedding Shoot Outfits', 'Indo-Western', 
            'Western Wear', 'Premium Wear'
        ];
        foreach ($categories as $name) {
            Category::updateOrCreate(['name' => $name]);
        }

        // Create sample fabric types
        $fabrics = ['Silk', 'Cotton', 'Polyester', 'Linen'];
        foreach ($fabrics as $name) {
            FabricType::updateOrCreate(['name' => $name]);
        }

        // Create sample colors
        $colors = ['Red', 'Blue', 'Green', 'Black', 'White'];
        foreach ($colors as $name) {
            Color::updateOrCreate(['name' => $name]);
        }

        // Create sample sizes
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        foreach ($sizes as $name) {
            Size::updateOrCreate(['name' => $name]);
        }

        // Create sample bottom types
        $bottoms = ['Straight', 'Skinny', 'Wide Leg', 'Palazzo'];
        foreach ($bottoms as $name) {
            BottomType::updateOrCreate(['name' => $name]);
        }

        // Create sample body type fits
        $fits = ['Regular', 'Slim', 'Loose', 'Oversized'];
        foreach ($fits as $name) {
            BodyTypeFit::updateOrCreate(['name' => $name]);
        }

        // Create outfit conditions
        $conditions = ['Brand New', 'Like New', 'Excellent', 'Good', 'Fair'];
        foreach ($conditions as $name) {
            GarmentCondition::updateOrCreate(['name' => $name]);
        }

        // Create sample brands
        $brands = ['Zara', 'H&M', 'Manyavar', 'Sabyasachi', 'FabIndia', 'Biba'];
        foreach ($brands as $name) {
            Brand::updateOrCreate(['name' => $name]);
        }

        // Admin System Seeders
        $this->call([
            FrontendSettingsSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            AdminPermissionSeeder::class,
        ]);
    }
}
