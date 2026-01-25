<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\XpressbeesService;

class RetryPendingShipments extends Command
{
    protected $signature = 'shipments:retry-pending';
    protected $description = 'Retry shipment creation for confirmed orders that failed initial API call';

    public function handle(XpressbeesService $courier)
    {
        // Find orders that are 'Confirmed' (Paid) but have NO Shipment record
        $pendingOrders = Order::where('status', 'Confirmed')
            ->whereDoesntHave('shipment') // Check relationship
            ->get();

        $this->info("Found {$pendingOrders->count()} pending orders requiring shipment generation.");

        foreach ($pendingOrders as $order) {
            $this->info("Processing Order #{$order->id}...");

            try {
                $user = $order->buyer;
                if (!$user) {
                    $this->error(" -> Skipper: No buyer found for order #{$order->id}");
                    continue;
                }

                 // Prepare Order Data (Same logic as CheckoutController)
                 $addressParts = explode(',', $order->delivery_address);
                 $city = trim($addressParts[count($addressParts)-2] ?? 'Mumbai');
                 $pincode = trim($addressParts[count($addressParts)-1] ?? '400001');
 
                 $orderLoad = [
                     'order_number' => $order->id,
                     'payment_method' => 'Prepaid',
                     'consignee_name' => $user->name,
                     'consignee_phone' => $user->phone ?? '9999999999',
                     'consignee_address' => $order->delivery_address,
                     'consignee_pincode' => $pincode,
                     'consignee_city' => $city,
                     'consignee_state' => 'Maharashtra',
                     'products' => [],
                     'total_amount' => $order->total_amount,
                     'weight' => 0.5,
                     'length' => 10,
                     'breadth' => 10,
                     'height' => 10
                 ];
 
                 foreach($order->items as $item) {
                     $orderLoad['products'][] = [
                         'name' => $item->cloth->title,
                         'qty' => 1,
                         'price' => $item->price
                     ];
                 }

                // Call API
                $response = $courier->createOrder($orderLoad);

                if ($response && isset($response['awb_number'])) {
                    // Success: Create Shipment Record
                    \App\Models\Shipment::create([
                        'order_id' => $order->id,
                        'courier_name' => 'Xpressbees',
                        'waybill_number' => $response['awb_number'],
                        'reference_id' => $response['order_id'] ?? null,
                        'tracking_url' => $response['label_url'] ?? null,
                        'label_url' => $response['label_url'] ?? null,
                        'status' => 'Booked',
                    ]);
                    
                    $order->update(['status' => 'Order Confirmed & Shipment Created']);
                    $this->info(" -> Success! AWB: {$response['awb_number']}");
                } else {
                    $this->error(" -> Failed: API Error");
                }

            } catch (\Exception $e) {
                $this->error(" -> Exception: " . $e->getMessage());
            }
        }
    }
}
