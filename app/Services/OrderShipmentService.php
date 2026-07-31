<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OrderShipmentService
{
    /**
     * Attempt to create forward shipments for an order.
     * Retries only for sellers that don't already have a forward shipment.
     *
     * @param Order $order
     * @param User $user (Buyer)
     * @param string $paymentType (e.g., 'cod', 'prepaid')
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function createShipments(Order $order, User $user, $paymentType)
    {
        try {
            Log::info("OrderShipmentService: Creating {$paymentType} shipments for Order #{$order->id}");
            
            $courier = new XpressbeesService();
            
            // Consignee details (Buyer)
            $buyerAddress = $user->address;
            $buyerCity = $user->city;
            $buyerState = $user->state;
            $buyerPincode = $user->pincode;

            // Group items by seller
            $itemsBySeller = [];
            foreach ($order->items as $item) {
                $sellerId = $item->cloth->user_id ?? 0;
                $itemsBySeller[$sellerId][] = $item;
            }

            $hasFailures = false;
            $errorMessages = [];

            // Existing shipments mapping by seller to prevent duplicates
            $existingShipments = Shipment::where('order_id', $order->id)->where('type', 'forward')->pluck('seller_id')->toArray();

            foreach ($itemsBySeller as $sellerId => $sellerItems) {
                // Skip if this seller already has a successful shipment
                if (in_array($sellerId, $existingShipments)) {
                    continue;
                }

                $seller = User::find($sellerId);
                
                $sellerAddress = $seller->address;
                $sellerCity = $seller->city;
                $sellerState = $seller->state;
                $sellerPincode = $seller->pincode;
                $sellerPhone = str_pad(preg_replace('/[^0-9]/', '', $seller->phone), 10, '0', STR_PAD_LEFT);
                $sellerName = $seller->name;
                
                // Calculate collectable amount for THIS specific shipment (only if COD)
                $shipmentAmount = 0;
                $shipmentCollectable = 0;
                $orderItemsArray = [];
                
                foreach ($sellerItems as $sItem) {
                    $itemTotal = $sItem->price;
                    if ($sItem->purchase_type !== 'buy') {
                        $itemTotal += (float) ($sItem->cloth->security_deposit ?? 0);
                    }
                    
                    $shipmentAmount += $itemTotal;
                    
                    $orderItemsArray[] = [
                        'name' => $sItem->cloth->title ?? 'Item',
                        'qty' => 1,
                        'price' => $sItem->price
                    ];
                }
                
                if (strtolower($paymentType) === 'cod') {
                    $shipmentCollectable = $shipmentAmount;
                }

                $buyerPhoneStr = substr(str_pad(preg_replace('/[^0-9]/', '', $user->phone), 10, '0', STR_PAD_LEFT), -10);
                
                $orderLoad = [
                    'order_number' => $order->id . '-' . $sellerId . '-' . time(), // Unique order number per seller & retry attempt
                    'payment_type' => strtolower($paymentType) === 'cod' ? 'cod' : 'prepaid',
                    'order_amount' => $shipmentAmount,
                    'collectable_amount' => $shipmentCollectable,
                    'package_weight' => 500 * count($sellerItems),
                    'package_length' => 10,
                    'package_breadth' => 10,
                    'package_height' => 10,
                    'request_auto_pickup' => 'yes',
                    'shipping_charges' => 0,
                    'discount' => 0,
                    'cod_charges' => 0,
                    'consignee' => [
                        'name' => substr(trim($user->name), 0, 100),
                        'address' => substr(trim($buyerAddress), 0, 200),
                        'address_2' => '',
                        'city' => substr(trim($buyerCity), 0, 40),
                        'state' => substr(trim($buyerState), 0, 40),
                        'pincode' => substr(trim($buyerPincode), 0, 6),
                        'phone' => $buyerPhoneStr
                    ],
                    'pickup' => [
                        'warehouse_name' => substr(trim($sellerName), 0, 20),
                        'name' => substr(trim($sellerName), 0, 100),
                        'address' => substr(trim($sellerAddress), 0, 200),
                        'address_2' => '',
                        'city' => substr(trim($sellerCity), 0, 40),
                        'state' => substr(trim($sellerState), 0, 40),
                        'pincode' => substr(trim($sellerPincode), 0, 6),
                        'phone' => substr(str_pad(preg_replace('/[^0-9]/', '', $sellerPhone), 10, '0', STR_PAD_LEFT), -10)
                    ],
                    'order_items' => $orderItemsArray
                ];

                $response = $courier->createOrder($orderLoad);

                if ($response && isset($response['awb_number'])) {
                    Shipment::create([
                        'order_id' => $order->id,
                        'seller_id' => $sellerId,
                        'type' => 'forward',
                        'courier_name' => 'Xpressbees',
                        'waybill_number' => $response['awb_number'],
                        'reference_id' => $response['order_id'] ?? null,
                        'tracking_url' => $response['label_url'] ?? null,
                        'label_url' => $response['label_url'] ?? null,
                        'status' => 'Booked',
                    ]);
                    
                    Log::info("OrderShipmentService: Shipment created for Seller {$sellerId}. AWB: {$response['awb_number']}");
                } else {
                    $hasFailures = true;
                    $errorMsg = isset($response['message']) ? $response['message'] : json_encode($response);
                    $errorMessages[] = "Seller $sellerId: $errorMsg";
                    Log::error("OrderShipmentService: Failed to create shipment for Seller {$sellerId}. Response: " . json_encode($response));
                }
            }
            
            if ($hasFailures) {
                $errorString = implode(" | ", $errorMessages);
                $order->update([
                    'status' => 'Order Confirmed & Shipment Failed',
                    'shipment_error' => $errorString
                ]);
                return ['success' => false, 'error' => $errorString];
            } else {
                $order->update([
                    'status' => 'Order Confirmed & Shipment Created',
                    'shipment_error' => null
                ]);
                return ['success' => true, 'error' => null];
            }

        } catch (\Exception $e) {
            $order->update([
                'status' => 'Order Confirmed & Shipment Failed',
                'shipment_error' => $e->getMessage()
            ]);
            Log::error("OrderShipmentService Exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
