<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cloth>
 */
class ClothFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'category' => '1', 
            'gender' => fake()->randomElement(['Male', 'Female', 'Unisex']),
            'size' => 'M', // Assuming enum or simplified for test
            'user_id' => \App\Models\User::factory(),
            'rent_price' => fake()->randomFloat(2, 50, 500),
            'security_deposit' => fake()->randomFloat(2, 100, 1000),
            'condition' => 'Good Condition',
            'is_available' => true,
        ];
    }
}
