<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_correct_fillable_attributes()
    {
        $user = new User();
        $expectedFillables = [
            'name', 'email', 'phone', 'address', 'city', 'age', 
            'gstin', 'is_gst', 'gst_number', 'gender', 'password', 'profile_image'
        ];

        $this->assertEquals($expectedFillables, $user->getFillable());
    }

    public function test_user_casts_attributes_correctly()
    {
        $user = new User();
        $this->assertEquals('datetime', $user->getCasts()['email_verified_at']);
        $this->assertEquals('hashed', $user->getCasts()['password']);
    }

    public function test_user_has_many_cart_items()
    {
        $user = User::factory()->create();
        $cartItem = \App\Models\CartItem::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->cartItems->contains($cartItem));
    }

    public function test_user_has_many_clothes()
    {
        $user = User::factory()->create();
        $cloth = \App\Models\Cloth::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->clothes->contains($cloth));
    }

    public function test_user_can_be_a_business_user_with_gst()
    {
        $user = User::factory()->create([
            'is_gst' => true,
            'gst_number' => '27AAAAA0000A1Z5'
        ]);

        $this->assertTrue($user->is_gst);
        $this->assertEquals('27AAAAA0000A1Z5', $user->gst_number);
    }
}
