<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Cloth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\PriceCalculatorService;

class CartApiController extends Controller
{
    /**
     * Get all items in the user's cart
     */
    public function index()
    {
        $cartItems = Auth::user()->cartItems()
            ->with(['cloth.images', 'cloth.size', 'cloth.condition'])
            ->get();

        $formattedItems = $cartItems->map(function ($item) {
            $cloth = $item->cloth;
            $price = ($item->purchase_type === 'buy') ? $item->total_selling_price : $item->total_rental_cost;
            
            return [
                'cart_item_id' => $item->id,
                'cloth_id' => $item->cloth_id,
                'title' => $cloth->title ?? '',
                'image' => $cloth->images->count() ? asset('storage/' . $cloth->images->first()->image_path) : null,
                'size' => $cloth->size->name ?? $cloth->size,
                'quantity' => $item->quantity,
                'purchase_type' => $item->purchase_type ?? 'rent',
                'price' => $price,
                'rental_start_date' => $item->rental_start_date,
                'rental_end_date' => $item->rental_end_date,
                'rental_days' => $item->rental_days,
                'security_deposit' => $cloth->security_deposit ?? 0,
            ];
        });

        $subtotal = $formattedItems->sum('price');

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $formattedItems,
                'subtotal' => $subtotal,
                'cart_count' => $cartItems->count()
            ]
        ], 200);
    }

    /**
     * Add item to cart
     */
    public function addToCart(Request $request)
    {
        Log::info('API Cart add request:', $request->all());

        $cloth = Cloth::findOrFail($request->cloth_id);

        if ($cloth->sku <= 0) {
            return response()->json([
                'success' => false, 
                'message' => 'This item is currently sold out.'
            ], 400);
        }
        
        $priceService = new PriceCalculatorService();

        // -------------------------
        // HANDLE BUY REQUEST
        // -------------------------
        if ($request->has('purchase_type') && $request->purchase_type === 'buy') {
            $request->validate([
                'cloth_id' => 'required|exists:clothes,id',
                'purchase_type' => 'required|in:buy',
            ]);
            
            if (!$cloth->is_purchased) {
                return response()->json(['success' => false, 'message' => 'This item is not available for purchase.'], 400);
            }
            
            $existingItem = Auth::user()->cartItems()->where('cloth_id', $request->cloth_id)->first();
            if ($existingItem) {
                return response()->json(['success' => false, 'message' => 'Item is already in your bag.'], 400);
            }
            
            // Calculate Purchase Price securely
            $purchasePricing = $priceService->calculatePurchase($cloth);
            $totalSellingPrice = $purchasePricing['total_buyer_pay'];

            Auth::user()->cartItems()->create([
                'cloth_id' => $request->cloth_id,
                'quantity' => 1,
                'purchase_type' => 'buy',
                'total_selling_price' => $totalSellingPrice,
            ]);
            
            $message = 'Item added to bag for purchase!';
        } 
        // -------------------------
        // HANDLE RENT REQUEST
        // -------------------------
        else {
            $request->validate([
                'cloth_id' => 'required|exists:clothes,id',
                'rental_start_date' => 'required|date|after_or_equal:today',
                'rental_end_date' => 'required|date|after:rental_start_date',
                'rental_days' => 'required|integer|min:4',
            ]);

            // Backend Availability Check
            $isAvailable = $this->checkAvailability($cloth, $request->rental_start_date, $request->rental_end_date);
            if (!$isAvailable['success']) {
                return response()->json(['success' => false, 'message' => $isAvailable['message']], 422);
            }

            // Recalculate cost securely on backend
            $pricing = $priceService->calculate($cloth, $request->rental_days);
            $totalRentalCost = $pricing['total_buyer_pay'];
            
            $existingItem = Auth::user()->cartItems()->where('cloth_id', $request->cloth_id)->first();
            
            if ($existingItem) {
                $existingItem->update([
                    'rental_start_date' => $request->rental_start_date,
                    'rental_end_date' => $request->rental_end_date,
                    'total_rental_cost' => $totalRentalCost,
                    'rental_days' => $request->rental_days,
                    'purchase_type' => 'rent'
                ]);
                $message = 'Rental dates and price updated in your bag.';
            } else {
                Auth::user()->cartItems()->create([
                    'cloth_id' => $request->cloth_id,
                    'quantity' => 1,
                    'rental_start_date' => $request->rental_start_date,
                    'rental_end_date' => $request->rental_end_date,
                    'total_rental_cost' => $totalRentalCost,
                    'rental_days' => $request->rental_days,
                    'purchase_type' => 'rent'
                ]);
                $message = 'Item added to bag for renting!';
            }
        }

        $cartCount = Auth::user()->cartItems()->count();

        Log::info('API Cart item added successfully', [
            'cloth_id' => $request->cloth_id,
            'cart_count' => $cartCount
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'cartCount' => $cartCount
        ], 200);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);

        $cartItem = Auth::user()->cartItems()->find($request->cart_item_id);
        
        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Item not found in bag.'], 404);
        }

        $cartItem->delete();
        $cartCount = Auth::user()->cartItems()->count();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from bag.',
            'cartCount' => $cartCount
        ], 200);
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Auth::user()->cartItems()->with('cloth')->find($request->cart_item_id);

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Item not found in bag.'], 404);
        }

        if ($request->quantity > $cartItem->cloth->sku) {
             return response()->json([
                 'success' => false, 
                 'message' => 'Requested quantity exceeds available stock.'
             ], 400);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated.',
            'cartCount' => Auth::user()->cartItems()->count()
        ], 200);
    }

    /**
     * Check if dates are valid based on Availability Blocks
     */
    private function checkAvailability(Cloth $cloth, $start, $end)
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        // 1. Check Whitelist (Available Blocks)
        $availableBlocks = $cloth->availabilityBlocks()->where('type', 'available')->get();
        
        if ($availableBlocks->count() > 0) {
            $isCovered = false;
            foreach ($availableBlocks as $block) {
                $blockStart = Carbon::parse($block->start_date);
                $blockEnd = Carbon::parse($block->end_date);

                if ($startDate->gte($blockStart) && $endDate->lte($blockEnd)) {
                    $isCovered = true;
                    break;
                }
            }
            if (!$isCovered) {
                return ['success' => false, 'message' => 'Selected dates are not within available rental periods.'];
            }
        }

        // 2. Check Blacklist (Blocked Blocks)
        $blockedBlocks = $cloth->availabilityBlocks()->where('type', 'blocked')->get();
        
        foreach ($blockedBlocks as $block) {
            $blockStart = Carbon::parse($block->start_date);
            $blockEnd = Carbon::parse($block->end_date);

            if ($startDate->lte($blockEnd) && $endDate->gte($blockStart)) {
                return ['success' => false, 'message' => 'Selected dates overlap with a blocked period.'];
            }
        }

        return ['success' => true];
    }
}
 