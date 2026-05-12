<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_belongs_to_buyer()
    {
        $buyer = User::factory()->create();
        $order = Order::factory()->create(['buyer_id' => $buyer->id]);

        $this->assertInstanceOf(User::class, $order->buyer);
        $this->assertEquals($buyer->id, $order->buyer->id);
    }

    public function test_order_status_default_is_pending()
    {
        $order = Order::factory()->create();
        $this->assertEquals('Pending', $order->status);
    }
}
