<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\XpressbeesService;
use Illuminate\Support\Facades\Log;

class ProcessRentalReturns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:process-returns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically create return shipments for rental orders reaching their return date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();
        
        $this->info("Processing rental returns for date: {$today}");

        // Find orders that are:
        // 1. Rental orders
        // 2. Scheduled to be returned today OR earlier (catch up)
        // 3. Status is 'Delivered' (not yet 'Returned' or 'Processing Return')
        $orders = Order::where('has_rental_items', true)
            ->where('rental_to', '<=', $today)
            ->where('status', 'Delivered')
            ->with(['buyer', 'items.cloth.user'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info("No orders found for return processing.");
            return;
        }

        $courier = new XpressbeesService();

        foreach ($orders as $order) {
            $this->info("Processing Order #{$order->id} (Buyer: {$order->buyer->name})");

            // Group items by Seller (Exclude Purchases)
            $itemsBySeller = [];
            foreach ($order->items as $item) {
                if ($item->purchase_type === 'buy') {
                    continue; // Skip purchase items
                }

                if ($item->cloth && $item->cloth->user) {
                    $itemsBySeller[$item->cloth->user_id][] = $item;
                }
            }

            foreach ($itemsBySeller as $sellerId => $items) {
                $seller = $items[0]->cloth->user;
                
                // Check if reverse shipment already exists for this order/seller
                $existing = Shipment::where('order_id', $order->id)
                    ->where('type', 'reverse')
                    ->where('courier_response', 'LIKE', '%"seller_id":' . $sellerId . '%')
                    ->first();

                if ($existing) {
                    $this->warn("Reverse shipment for Order #{$order->id} to Seller #{$sellerId} already exists. Skipping.");
                    continue;
                }

                $this->createReturnShipment($order, $seller, $items, $courier);
            }

            // Update order status
            $order->update(['status' => 'Return In Progress']);
            $this->info("Order #{$order->id} status updated to 'Return In Progress'");
        }

        $this->info("Finished processing returns.");
    }

    private function createReturnShipment($order, $seller, $items, $courier)
    {
        try {
            $buyer = $order->buyer;

            // In a return shipment:
            // Consignee (Receiver) = Seller
            // Pickup Address = Buyer's Address (from Order)

            $orderLoad = [
                'order_number' => $order->id . '-RET-' . $seller->id,
                'payment_method' => 'Prepaid', // Return shipments are always prepaid by the platform
                'collectable_amount' => 0,
                'consignee_name' => $seller->name,
                'consignee_phone' => $seller->phone ?? '9999999999',
                'consignee_address' => $seller->address ?? 'Seller Address Not Found',
                'consignee_pincode' => '400001', // Should ideally come from seller model
                'consignee_city' => $seller->city ?? 'Mumbai',
                'consignee_state' => 'Maharashtra',
                
                // Pickup Info (The Buyer)
                'pickup_name' => $buyer->name,
                'pickup_phone' => $buyer->phone ?? '9999999999',
                'pickup_address' => $order->delivery_address,
                
                'products' => [],
                'total_amount' => 0,
                'weight' => 0.5,
                'length' => 10,
                'breadth' => 10,
                'height' => 10,
                'is_reverse' => true // Custom flag for API if needed
            ];

            foreach ($items as $item) {
                $orderLoad['products'][] = [
                    'name' => 'RETURN: ' . ($item->cloth->title ?? 'Item'),
                    'qty' => 1,
                    'price' => 0
                ];
            }

            $response = $courier->createReturnOrder($orderLoad);

            if ($response && isset($response['awb_number'])) {
                Shipment::create([
                    'order_id' => $order->id,
                    'type' => 'reverse',
                    'courier_name' => 'Xpressbees',
                    'waybill_number' => $response['awb_number'],
                    'reference_id' => $response['order_id'] ?? null,
                    'tracking_url' => $response['label_url'] ?? null,
                    'status' => 'Booked',
                    'courier_response' => [
                        'api_response' => $response,
                        'seller_id' => $seller->id,
                        'batch' => 'automatic_return'
                    ],
                ]);
                
                $this->info("Successfully created return shipment for Order #{$order->id}. AWB: {$response['awb_number']}");
                
                // Notify Buyer
                \App\Models\Notification::create([
                    'user_id' => $buyer->id,
                    'title' => 'Return Shipment Scheduled',
                    'message' => "The rental period for Order #{$order->id} has ended. A return pickup has been scheduled. AWB: {$response['awb_number']}",
                    'type' => 'info',
                    'icon' => 'bi-truck-flatbed',
                    'data' => ['order_id' => $order->id, 'awb' => $response['awb_number']],
                ]);

                // Notify Seller
                \App\Models\Notification::create([
                    'user_id' => $seller->id,
                    'title' => 'Item Returning',
                    'message' => "An item from Order #{$order->id} is being returned to you. Track: {$response['awb_number']}",
                    'type' => 'info',
                    'icon' => 'bi-arrow-return-left',
                    'data' => ['order_id' => $order->id, 'awb' => $response['awb_number']],
                ]);

            } else {
                Log::error("Return Shipment Failed for Order #{$order->id} to Seller #{$sellerId}");
            }
        } catch (\Exception $e) {
            Log::error("Exception in createReturnShipment: " . $e->getMessage());
        }
    }
}
