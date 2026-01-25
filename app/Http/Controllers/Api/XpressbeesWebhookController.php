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
            // Map Courier "Delivered" to System "Delivered"
            if (stripos($status, 'Delivered') !== false) {
                if ($order->status !== 'Delivered') {
                    $order->status = 'Delivered';
                    $order->save();
                    Log::info("Webhook: Order #{$order->id} marked as Delivered via Webhook.");
                }
            }
            // You can map other statuses here (e.g., RTO -> Returned)
        }

        return response()->json(['success' => true]);
    }
}
