<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'cloth_id' => \App\Models\Cloth::factory(),
            'quantity' => 1,
            'purchase_type' => 'buy',
            'rental_start_date' => null,
            'rental_end_date' => null,
            'total_rental_cost' => null,
            'rental_days' => null,
            'total_purchase_cost' => fake()->randomFloat(2, 50, 500),
        ];
    }
}
