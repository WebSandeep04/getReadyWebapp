<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shipment;
use Illuminate\Support\Facades\Log;

class XpressbeesWebhookController extends Controller
{
    /**
     * Handle incoming webhook updates from Xpressbees.
     */
    public function handleWebhook(Request $request)
    {
        // 1. Log the incoming payload
        Log::info('Xpressbees Webhook Received', $request->all());

        // 2. Validate Key Fields
        // Structure depends on Xpressbees docs. Assuming: { "awb": "...", "status": "...", "current_status": "..." }
        $awb = $request->input('awb_number') ?? $request->input('awb');
        $status = $request->input('current_status') ?? $request->input('status');

        if (!$awb || !$status) {
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 400);
        }

        // 3. Find Shipment
        $shipment = Shipment::where('waybill_number', $awb)->first();

        if (!$shipment) {
            Log::warning("Webhook: Shipment not found for AWB: {$awb}");
            return response()->json(['success' => false, 'message' => 'Shipment not found'], 404);
        }

        // 4. Update Shipment Status
        $shipment->status = $status;
        $shipment->courier_response = array_merge($shipment->courier_response ?? [], ['webhook' => $request->all()]);
        
        if (strtolower($status) === 'delivered') {
            $shipment->delivered_at = now();
        }
        
        $shipment->save();

        // 5. Update Order Status
        $order = $shipment->order;
        if ($order) {
            // Map Courier "Delivered" to System "Delivered" or "Returned"
            if (stripos($status, 'Delivered') !== false) {
                $newStatus = ($shipment->type === 'reverse') ? 'Returned' : 'Delivered';
                
                if ($order->status !== $newStatus) {
                    if ($newStatus === 'Returned') {
                        // Increment SKU ONLY for returned purchase items (as rentals don't decrement SKU)
                        foreach ($order->items as $item) {
                            if ($item->purchase_type === 'buy') {
                                $cloth = $item->cloth;
                                if ($cloth) { 
                                    $cloth->sku = $cloth->sku + 1;
                                    $cloth->is_available = true; // Make available again
                                    $cloth->save();
                                }
                            }
                        }
                    }
                    
                    $order->status = $newStatus;
                    if ($newStatus === 'Delivered') {
                        $order->delivered_at = now();
                    }
                    $order->save();
                    Log::info("Webhook: Order #{$order->id} marked as {$newStatus} via {$shipment->type} Webhook and stock updated if needed.");
                }
            }
            // You can map other statuses here (e.g., RTO -> Returned)
        }

        return response()->json(['success' => true]);
    }
}
