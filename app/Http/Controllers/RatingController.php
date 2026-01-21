<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            return back()->with('error', $validator->errors()->first());
        }

        $order = Order::with(['buyer', 'items.cloth.user'])->findOrFail($request->order_id);
        $user = Auth::user();

        // Determine who is being rated based on who is logged in
        $ratedUserId = null;

        // If logged in user is the Buyer, they are rating the Seller(s)
        // Note: An order might have items from multiple sellers. 
        // For simplicity in this first iteration, we might assume single seller per order or restricted logic.
        // However, standard marketplaces split orders or allow rating items.
        // The `ratings` table links to `order_id`. If an order has mixed sellers, this simple logic fails.
        // Let's check `order_items` -> `cloth` -> `user_id` to see the sellers.
        
        // Let's assume for now 1 Rating per Order for simplicity, OR we explicitly require a 'target_user_id' if we want to support multi-seller orders.
        // But the Schema has `rated_user_id`.
        
        // Let's refine the logic: User passes `rated_user_id` implicitly or explicitly.
        // Option 1: Implicit. 
        // If I am Buyer ($order->buyer_id == $user->id), I rate the Seller.
        // IF there is only 1 seller in the order.
        
        $sellerIds = $order->items->pluck('cloth.user_id')->unique();
        
        \Illuminate\Support\Facades\Log::info('Debug Rating', [
            'order_id' => $order->id,
            'items_count' => $order->items->count(),
            'item_ids' => $order->items->pluck('id'),
            'seller_ids' => $sellerIds,
            'first_item' => $order->items->first(),
            'first_item_cloth' => $order->items->first() ? $order->items->first()->cloth : null,
        ]);

        if ($user->id == $order->buyer_id) {
            // User is Buyer.
            if ($sellerIds->count() > 1) {
                // Complex scenario. For now, let's just grab the first seller or handle via UI passing ID.
                // Let's see if the UI sends it.
                if ($request->has('rated_user_id')) {
                    $ratedUserId = $request->rated_user_id;
                } else {
                     $ratedUserId = $sellerIds->first();
                }
            } else {
                $ratedUserId = $sellerIds->first();
            }
        } elseif ($sellerIds->contains($user->id)) {
            // User is Seller. They are rating the Buyer.
            $ratedUserId = $order->buyer_id;
        } else {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You are not authorized to rate this order.'], 403);
            }
            return back()->with('error', 'You are not authorized to rate this order.');
        }

        if (!$ratedUserId) {
             if ($request->wantsJson()) {
                 return response()->json(['success' => false, 'message' => 'Could not determine user to rate.'], 422);
             }
             return back()->with('error', 'Could not determine user to rate.');
        }
        
        // Prevent duplicate rating
        $exists = Rating::where('order_id', $order->id)
            ->where('rater_id', $user->id)
            ->where('rated_user_id', $ratedUserId)
            ->exists();

        if ($exists) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You have already submitted a rating for this user on this order.'], 422);
            }
            return back()->with('error', 'You have already submitted a rating for this user on this order.');
        }

        try {
            \Illuminate\Support\Facades\Log::info('Attempting to create rating', [
                'rater_id' => $user->id,
                'rated_user_id' => $ratedUserId,
                'order_id' => $order->id,
                'rating' => $request->rating,
            ]);

            Rating::create([
                'rater_id' => $user->id,
                'rated_user_id' => $ratedUserId,
                'order_id' => $order->id,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);



            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Rating submitted successfully!',
                    'order_id' => $order->id
                ]);
            }

            return back()->with('success', 'Rating submitted successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create rating: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to save rating. Please try again.'], 500);
            }
            
            return back()->with('error', 'Failed to save rating. Please try again.');
        }
    }
}
