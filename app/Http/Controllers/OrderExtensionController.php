<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderExtension;
use App\Models\Payment;
use App\Services\ExtensionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderExtensionController extends Controller
{
    protected $extensionService;

    public function __construct(ExtensionService $extensionService)
    {
        $this->extensionService = $extensionService;
    }

    /**
     * GET /api/orders/{id}/extension-quote?days=X
     */
    public function quote(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|integer|min:1'
        ]);

        $order = Order::with('items.cloth')->findOrFail($id);
        
        // Ensure user owns the order
        if ($order->buyer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $extraDays = (int) $request->days;
        
        $isAvailable = $this->extensionService->validateAvailability($order, $extraDays);
        $costBreakdown = $this->extensionService->calculateExtensionCost($order, $extraDays);

        return response()->json([
            'success' => true,
            'is_available' => $isAvailable,
            'quote' => $costBreakdown,
            'new_rental_to' => \Carbon\Carbon::parse($order->rental_to)->addDays($extraDays)->format('Y-m-d')
        ]);
    }

    /**
     * POST /api/orders/{id}/extend
     */
    public function extend(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|integer|min:1'
        ]);

        $order = Order::with('items.cloth')->findOrFail($id);
        
        if ($order->buyer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $extraDays = (int) $request->days;

        if (!$this->extensionService->validateAvailability($order, $extraDays)) {
            return response()->json(['success' => false, 'message' => 'Selected dates are no longer available.'], 422);
        }

        $costBreakdown = $this->extensionService->calculateExtensionCost($order, $extraDays);
        $totalAmount = $costBreakdown['total_additional_amount'];

        $newRentalTo = \Carbon\Carbon::parse($order->rental_to)->addDays($extraDays);

        // Create the extension record
        $extension = OrderExtension::create([
            'order_id' => $order->id,
            'old_rental_to' => $order->rental_to,
            'new_rental_to' => $newRentalTo,
            'extra_days' => $extraDays,
            'additional_amount' => $totalAmount,
            'status' => 'pending'
        ]);

        // Return Razorpay payload
        return response()->json([
            'success' => true,
            'extension_id' => $extension->id,
            'razorpay_order' => [
                'amount' => (int) round($totalAmount * 100),
                'currency' => 'INR',
                'receipt' => 'EXT-' . $extension->id . '-' . Str::random(4),
            ],
            'key' => config('services.razorpay.key_id')
        ]);
    }

    /**
     * POST /api/orders/extension/verify-payment
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'extension_id' => 'required|exists:order_extensions,id',
            'razorpay_payment_id' => 'required|string',
        ]);

        $extension = OrderExtension::with('order.items.cloth')->findOrFail($request->extension_id);
        
        if ($extension->order->buyer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // 1. Create Payment record
        $payment = Payment::create([
            'order_id' => $extension->order_id,
            'payment_method' => 'razorpay_extension',
            'payment_status' => 'Paid',
            'amount' => $extension->additional_amount,
            'transaction_id' => $request->razorpay_payment_id,
            'paid_at' => now(),
        ]);

        $extension->update(['payment_id' => $payment->id]);

        // 2. Process Extension (Update dates and availability blocks)
        $this->extensionService->processExtension($extension->order, $extension);

        return response()->json([
            'success' => true,
            'message' => 'Rental extended successfully!',
            'new_date' => $extension->new_rental_to->format('d M Y')
        ]);
    }
}
