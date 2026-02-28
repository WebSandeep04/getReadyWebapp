<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\PriceCalculatorService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderConversionController extends Controller
{
    protected $priceService;
    protected $invoiceService;

    public function __construct(PriceCalculatorService $priceService, InvoiceService $invoiceService)
    {
        $this->priceService = $priceService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * GET /orders/{id}/purchase-eligibility
     */
    public function eligibility($id, Request $request)
    {
        // Require cloth_id to find the specific item
        $request->validate([
            'cloth_id' => 'required|exists:clothes,id'
        ]);

        $order = Order::with('items.cloth')->findOrFail($id);

        if ($order->buyer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($order->status == 'Returned' || $order->status == 'Cancelled') {
            return response()->json(['success' => false, 'message' => 'Order is no longer active.'], 422);
        }

        $orderItem = collect($order->items)->firstWhere('cloth_id', $request->cloth_id);

        if (!$orderItem || $orderItem->purchase_type === 'buy') {
            return response()->json(['success' => false, 'message' => 'This item cannot be converted to purchase.'], 422);
        }

        if ($orderItem->cloth->selling_price <= 0) {
            return response()->json(['success' => false, 'message' => 'This cloth is not available for purchase.'], 422);
        }

        // Calculate Cost Breakdown
        $conversionData = $this->priceService->calculateRentalConversion($orderItem);

        return response()->json([
            'success' => true,
            'is_eligible' => true,
            'conversion_quote' => $conversionData
        ]);
    }

    /**
     * POST /orders/{id}/convert-to-purchase
     * We don't save an intermediate record like extensions; we just return Razorpay if payment > 0.
     */
    public function convertToPurchase(Request $request, $id)
    {
        $request->validate([
            'cloth_id' => 'required|exists:clothes,id'
        ]);

        $order = Order::with('items.cloth')->findOrFail($id);

        if ($order->buyer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $orderItem = collect($order->items)->firstWhere('cloth_id', $request->cloth_id);

        if (!$orderItem || $orderItem->purchase_type === 'buy' || $orderItem->cloth->selling_price <= 0) {
             return response()->json(['success' => false, 'message' => 'Invalid conversion request.'], 422);
        }

        $conversionData = $this->priceService->calculateRentalConversion($orderItem);
        $amountDue = $conversionData['amount_due'];

        // If the item amount due is 0 (or somehow covered), verify immediately without gateway.
        if ($amountDue <= 0) {
            return response()->json([
                'success' => true,
                'requires_payment' => false,
                'message' => 'Conversion successful without additional payment needed.',
                'order_item_id' => $orderItem->id
            ]);
        }

        // Return Razorpay data
        return response()->json([
            'success' => true,
            'requires_payment' => true,
            'order_item_id' => $orderItem->id,
            'conversion_data' => $conversionData,
            'razorpay_order' => [
                'amount' => (int) round($amountDue * 100),
                'currency' => 'INR',
                'receipt' => 'CONV-' . $orderItem->id . '-' . Str::random(4),
            ],
            'key' => config('services.razorpay.key_id')
        ]);
    }

    /**
     * POST /orders/conversion/verify
     */
    public function verifyConversion(Request $request)
    {
        $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'razorpay_payment_id' => 'nullable|string', // nullable if amount due was 0
        ]);

        $orderItem = OrderItem::with(['order', 'cloth'])->findOrFail($request->order_item_id);
        $order = $orderItem->order;

        if ($order->buyer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $conversionData = $this->priceService->calculateRentalConversion($orderItem);
        $amountDue = $conversionData['amount_due'];

        // If amount was > 0, check Razorpay ID
        if ($amountDue > 0 && empty($request->razorpay_payment_id)) {
             return response()->json(['success' => false, 'message' => 'Payment validation failed.'], 422);
        }

        // 1. Create Payment Record
        if ($amountDue > 0) {
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'razorpay_conversion',
                'payment_status' => 'Paid',
                'amount' => $amountDue,
                'transaction_id' => $request->razorpay_payment_id,
                'paid_at' => now(),
            ]);
        }

        // 2. Mark Item as Purchased natively
        $pricing = $conversionData['pricing_breakdown'];
        
        $orderItem->update([
            'converted_to_purchase_at' => now(),
            'conversion_amount' => $amountDue,
            'purchase_type' => 'buy',
            'price' => $pricing['total_buyer_pay'],
            'base_rent' => $pricing['base_price'],
            'buyer_commission' => $pricing['buyer_comm'],
            'seller_commission' => $pricing['seller_comm'],
            'rent_gst' => $pricing['item_tax_fee'],
            'buyer_commission_gst' => $pricing['buyer_comm_gst'],
            'seller_commission_gst' => $pricing['seller_comm_gst'],
            'tcs_amount' => $pricing['tcs'],
        ]);

        // 3. Mark the Order's Security Deposit as absorbed so it's not refunded to the buyer.
        // Also update has_purchase_items so payouts trigger correctly.
        $hasRentalItemsRemaining = $order->items()->where(function($q) use ($orderItem) {
            $q->where('id', '!=', $orderItem->id)
              ->where('purchase_type', 'rent');
        })->exists();

        $order->update([
            'security_absorbed_into_purchase' => true,
            'has_purchase_items' => true,
            'has_rental_items' => $hasRentalItemsRemaining
        ]);

        // 4. Update the Cloth Inventory - decremented if sku > 0
        if ($orderItem->cloth) {
            $cloth = $orderItem->cloth;
            if ($cloth->sku > 0) {
                 $cloth->sku = max(0, $cloth->sku - 1); // since it was 1 item rented.
                 if ($cloth->sku == 0) $cloth->is_available = false;
                 $cloth->save();
            }

            // Notification
            if ($cloth->user_id) {
                \App\Models\Notification::create([
                    'user_id' => $cloth->user_id,
                    'title' => 'Rented Item Purchased!',
                    'message' => "The active rental for '{$cloth->title}' has been successfully bought out by the renter!",
                    'type' => 'success',
                    'icon' => 'bi-cash-coin',
                    'data' => ['cloth_id' => $cloth->id, 'order_id' => $order->id],
                    'read' => false
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully converted to full purchase!'
        ]);
    }
}
