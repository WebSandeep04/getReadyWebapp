<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Cloth;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FailedShipmentOrderSeeder extends Seeder
{
    public function run()
    {
        // 1. Get User
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create(); // Try factory if available, or manual
            if (!$user) {
                 $user = User::create([
                    'name' => 'Tester', 'email' => 'test@test.com', 'password' => bcrypt('123'), 'phone' => '1111111111'
                 ]);
            }
        }

        // 2. Get Cloth (Optional, just use ID 1 or create dummy if possible without many constraints)
        // We will try to find one.
        $cloth = Cloth::first();
        $clothId = $cloth ? $cloth->id : null;
        
        // If no cloth, we can't easily create one due to many FKs (Brand, Category...). 
        // We will try to insert a raw record if needed, but safer to assume environment has data.
        if (!$clothId) {
             $this->command->warn("No clothes found in DB. Creating order with NULL cloth_id (might fail if constrained).");
        }

        // 3. Create "Failed Shipment" Order
        $order = Order::create([
            'buyer_id' => $user->id,
            'total_amount' => 1500,
            'security_amount' => 1000,
            'has_rental_items' => true,
            'has_purchase_items' => false,
            'status' => 'Confirmed', // Stuck state
            'delivery_address' => 'Flat 101, Test Tower, Bandra West, Mumbai, Maharashtra, 400050',
            'rental_from' => now(),
            'rental_to' => now()->addDays(3),
        ]);

        if ($clothId) {
            OrderItem::create([
                'order_id' => $order->id,
                'cloth_id' => $clothId,
                'price' => 500
            ]);
        }

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'payment_status' => 'Paid',
            'amount' => 1500,
            'transaction_id' => 'pay_test_' . Str::random(10),
            'paid_at' => now(),
        ]);

        $this->command->info("\nSuccess! Created Order #{$order->id}");
        $this->command->info("Status: Confirmed");
        $this->command->info("Shipment: Missing (Simulated Failure)");
        $this->command->info("Go to Admin Panel -> Orders to test the 'Retry Shipment' button.\n");
    }
}
