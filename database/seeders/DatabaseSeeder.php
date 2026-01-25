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
        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'gender' => 'Male',
        ]);

        // Create categories
        Category::create(['name' => 'Wedding Wear']);
        Category::create(['name' => 'Festive Wear']);
        Category::create(['name' => 'Formal Wear']);
        Category::create(['name' => 'Ethnic Wear']);
        Category::create(['name' => 'Traditional Wear']);
        Category::create(['name' => 'Pre-Wedding Shoot Outfits']);
        Category::create(['name' => 'Indo-Western']);
        Category::create(['name' => 'Western Wear']);
        Category::create(['name' => 'Premium Wear']);

        // Create sample fabric types
        FabricType::create(['name' => 'Silk']);
        FabricType::create(['name' => 'Cotton']);
        FabricType::create(['name' => 'Polyester']);
        FabricType::create(['name' => 'Linen']);

        // Create sample colors
        Color::create(['name' => 'Red']);
        Color::create(['name' => 'Blue']);
        Color::create(['name' => 'Green']);
        Color::create(['name' => 'Black']);
        Color::create(['name' => 'White']);

        // Create sample sizes
        Size::create(['name' => 'XS']);
        Size::create(['name' => 'S']);
        Size::create(['name' => 'M']);
        Size::create(['name' => 'L']);
        Size::create(['name' => 'XL']);
        Size::create(['name' => 'XXL']);

        // Create sample bottom types
        BottomType::create(['name' => 'Straight']);
        BottomType::create(['name' => 'Skinny']);
        BottomType::create(['name' => 'Wide Leg']);
        BottomType::create(['name' => 'Palazzo']);

        // Create sample body type fits
        BodyTypeFit::create(['name' => 'Regular']);
        BodyTypeFit::create(['name' => 'Slim']);
        BodyTypeFit::create(['name' => 'Loose']);
        BodyTypeFit::create(['name' => 'Oversized']);

        // Create outfit conditions
        GarmentCondition::create(['name' => 'Brand New']);
        GarmentCondition::create(['name' => 'Like New']);
        GarmentCondition::create(['name' => 'Excellent']);
        GarmentCondition::create(['name' => 'Good']);
        GarmentCondition::create(['name' => 'Fair']);

        // Create sample brands
        Brand::create(['name' => 'Zara']);
        Brand::create(['name' => 'H&M']);
        Brand::create(['name' => 'Manyavar']);
        Brand::create(['name' => 'Sabyasachi']);
        Brand::create(['name' => 'FabIndia']);
        Brand::create(['name' => 'Biba']);
    }
}
