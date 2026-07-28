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
            ->where(function($q) use ($today) {
                $q->whereNotNull('return_date')
                  ->where('return_date', '<=', $today)
                  ->orWhere(function($sq) use ($today) {
                      $sq->whereNull('return_date')
                         ->where('rental_to', '<=', $today);
                  });
            })
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

            $allSuccess = true;
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

                $success = $this->createReturnShipment($order, $seller, $items, $courier);
                if (!$success) {
                    $allSuccess = false;
                }
            }

            // Update order status only if shipments were successful
            if ($allSuccess) {
                $order->update(['status' => 'Return In Progress']);
                $this->info("Order #{$order->id} status updated to 'Return In Progress'");
            } else {
                $this->error("Failed to create return shipment for Order #{$order->id}. Status not updated.");
            }
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
                'order_number' => (string)$order->id . '-RET-' . $seller->id,
                'payment_type' => 'reverse',
                'order_amount' => 10,
                'collectable_amount' => 0,
                'package_weight' => 500, // default 500g
                'package_length' => 10,
                'package_breadth' => 10,
                'package_height' => 10,
                'request_auto_pickup' => 'yes',
                'shipping_charges' => 0,
                'discount' => 0,
                'cod_charges' => 0,
                'consignee' => [
                    'name' => $seller->name,
                    'address' => $seller->address,
                    'address_2' => '',
                    'city' => $seller->city,
                    'state' => $seller->state,
                    'pincode' => $seller->pincode,
                    'phone' => str_pad(preg_replace('/[^0-9]/', '', $seller->phone), 10, '9', STR_PAD_LEFT)
                ],
                'pickup' => [
                    'warehouse_name' => $buyer->name,
                    'name' => $buyer->name,
                    'address' => $buyer->address,
                    'address_2' => '',
                    'city' => $buyer->city,
                    'state' => $buyer->state,
                    'pincode' => $buyer->pincode,
                    'phone' => str_pad(preg_replace('/[^0-9]/', '', $buyer->phone), 10, '9', STR_PAD_LEFT)
                ],
                'order_items' => []
            ];

            foreach ($items as $item) {
                $orderLoad['order_items'][] = [
                    'name' => 'RETURN: ' . ($item->cloth->title ?? 'Item'),
                    'qty' => 1,
                    'price' => 10
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

                return true;
            } else {
                Log::error("Return Shipment Failed for Order #{$order->id} to Seller #{$seller->id}. Response: " . json_encode($response));
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception in createReturnShipment: " . $e->getMessage());
            return false;
        }
    }
}
