<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CartItem;
use App\Models\User;
use App\Models\Cloth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_item_relationships()
    {
        $user = User::factory()->create();
        $cloth = Cloth::factory()->create();
        $cartItem = CartItem::factory()->create([
            'user_id' => $user->id,
            'cloth_id' => $cloth->id
        ]);

        $this->assertInstanceOf(User::class, $cartItem->user);
        $this->assertInstanceOf(Cloth::class, $cartItem->cloth);
    }

    public function test_cart_item_purchase_type_logic()
    {
        $cartItem = CartItem::factory()->create(['purchase_type' => 'buy']);
        $this->assertEquals('buy', $cartItem->purchase_type);
        $this->assertNull($cartItem->rental_start_date);

        $cartItemRental = CartItem::factory()->create([
            'purchase_type' => 'rent',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(2)
        ]);
        $this->assertEquals('rent', $cartItemRental->purchase_type);
        $this->assertNotNull($cartItemRental->rental_start_date);
    }
}
