<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class OrderReturnController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'return_reason' => 'required|string',
            'return_details' => 'required|string',
            'return_images.*' => 'nullable|image|max:2048',
        ]);

        $order = Order::where('buyer_id', Auth::id())->findOrFail($id);

        if ($order->status !== 'Delivered') {
            return back()->with('error', 'Only delivered orders can be reported for issues.');
        }

        if (!$order->delivered_at || $order->delivered_at->addHours(4)->isPast()) {
            return back()->with('error', 'The reporting period for this order has expired (limit: 4 hours after delivery).');
        }

        $imagePaths = [];
        if ($request->hasFile('return_images')) {
            foreach ($request->file('return_images') as $image) {
                $imagePaths[] = $image->store('return_requests', 'public');
            }
        }

        $order->update([
            'status' => 'Return Requested',
            'return_reason' => $request->return_reason,
            'return_details' => $request->return_details,
            'return_images' => $imagePaths,
        ]);

        return back()->with('success', 'Return request submitted successfully. Admin will review your request.');
    }

    public function earlyReturn(Request $request, $id)
    {
        $request->validate([
            'new_return_date' => 'required|date|after_or_equal:today'
        ]);

        $order = Order::where('buyer_id', Auth::id())->findOrFail($id);

        if ($order->status !== 'Delivered') {
            return back()->with('error', 'Only delivered orders can be returned early.');
        }

        if (!$order->has_rental_items) {
            return back()->with('error', 'Early return is only available for rental items.');
        }

        $newReturnDate = $request->new_return_date;
        $originalRentalTo = $order->rental_to;

        if (Carbon::parse($newReturnDate)->gt(Carbon::parse($originalRentalTo))) {
            return back()->with('error', 'New return date cannot be later than the original rental end date.');
        }

        // Update order dates only. Status remains unchanged as it is handled by return service.
        $order->update([
            'rental_to' => $newReturnDate,
            'return_date' => $newReturnDate,
            'return_reason' => 'Early Return',
            'return_details' => 'User scheduled an early return.'
        ]);

        // Update availability blocks immediately
        $availabilityService = app(\App\Services\AvailabilityService::class);
        foreach ($order->items as $item) {
            if ($item->cloth && $item->purchase_type !== 'buy') {
                $availabilityService->updateAvailabilityForEarlyReturn($item->cloth->id, $order->id, $newReturnDate);
            }
        }

        // Trigger return shipment creation automatically (optional - requires careful refactoring)
        // For now, the Admin will see it in 'Return In Progress' and can manage courier if needed.

        return back()->with('success', 'Early return confirmed. Your return date has been updated to ' . Carbon::parse($newReturnDate)->format('d M Y') . ' and the calendar has been updated.');
    }
}
