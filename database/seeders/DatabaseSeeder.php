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
        $fabrics = [
            'Fleece', 'Polyester', 'Cotton', 'Knit', 'Denim', 'Cotton Blend', 'Net', 'Chiffon',
            'Silk', 'Acrylic', 'Spandex / Lycra / Elastane', 'Silk Blend', 'Wool', 'Satin',
            'Poly Cotton', 'Organza', 'Georgette', 'Banarasi Silk', 'Viscose', 'Nylon', 
            'Crepe', 'Aeropostale', 'Linen', 'Velvet', 'Mesh', 'Rayon', 'Cashmere',
            'Synthetic Georgette', 'Leather', 'Fur', 'Quilted'
        ];
        foreach ($fabrics as $name) {
            FabricType::updateOrCreate(['name' => $name]);
        }

        // Create sample colors
        $colors = [
            'Maroon', 'Brown', 'Olive', 'Nude', 'Navy Blue', 'Blue', 'Pink', 'Purple',
            'White', 'Green', 'Off White', 'Gold', 'Yellow', 'Black', 'Coral', 'Tan',
            'Multi-color', 'Grey', 'Rose', 'Mustard', 'Red', 'Mauve', 'Beige', 
            'Sea Green', 'Khaki', 'Magenta', 'Burgundy', 'Charcoal', 'Cyan', 'Lavender',
            'Rust', 'Orange', 'Peach', 'Wine', 'Denim Blue', 'Violet', 'Baby Pink', 'Crème'
        ];
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
        $brands = [
            'H&M', 'Zara', 'savana', 'shein', 'New me', 'Calvin klein', 'Forever 21', 'Mango',
            'adidas', 'Nike', 'Puma', 'plum', 'Dot & Key', 'Dior', 'MAC', 'Swiss Beauty',
            'Forever Fashion', 'Biba', 'Zudio', 'Little Box', 'Pantaloons', 'Westside', 'Taavi',
            'Glitchez', 'Terractive', 'NUON', 'Primark Cares', 'DORI', 'TERRANOVA', 'Vero Moda',
            'land\'s end', 'Lifestyle', 'Raymond', 'LYRA', 'DJ & C', 'Sqew', 'Levi\'s', 
            'Jenniffer', 'AKS', 'wardrobe', 'plusS', 'Asybuy', 'Max', '4WRD', 'miss twenty',
            'RIO', 'trigya', 'opaque.clip', 'URBANIC', 'UK 7', 'sharman', 'Chanderi', 
            'Reegan', 'Mengghong ling', 'Roadster', 'love 4 label', 'berabond', 'Tokyo Talkies', 
            'indigo spao', 'WISHFUL BY W', 'Bitterlime', 'Amayra', 'Marks & Spencer', 'ELISIA', 
            'TALLY WEiJL', 'bape', 'Lakers', 'FILA', 'BLACKBERRYS', 'graf', 'Analogue'
        ];
        foreach ($brands as $name) {
            Brand::updateOrCreate(['name' => $name]);
        }

        // Admin System Seeders
        $this->call([
            StateSeeder::class,
            CitySeeder::class,
            TaxSeeder::class,
            FrontendSettingsSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            AdminPermissionSeeder::class,
        ]);
    }
}
