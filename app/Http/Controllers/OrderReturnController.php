<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        if (!$order->delivered_at || $order->delivered_at->addMinutes(2)->isPast()) {
            return back()->with('error', 'The reporting period for this order has expired (limit: 2 minutes after delivery).');
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
}
