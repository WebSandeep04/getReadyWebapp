<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'buyer_id' => \App\Models\User::factory(),
            'total_amount' => fake()->randomFloat(2, 50, 500),
            'security_amount' => fake()->randomFloat(2, 20, 100),
            'status' => 'Pending',
            'delivery_address' => fake()->address(),
            'rental_from' => now(),
            'rental_to' => now()->addDays(7),
            'has_purchase_items' => true,
            'has_rental_items' => false,
        ];
    }
}
