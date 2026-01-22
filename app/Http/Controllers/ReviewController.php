<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use App\Models\Cloth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Store a new review for a product.
     */
    public function store(Request $request, $clothId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to post a review.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cloth = Cloth::findOrFail($clothId);

        // Check if user already reviewed this product
        $existingReview = ProductReview::where('cloth_id', $clothId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            // Update existing review
            $existingReview->update([
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            $review = $existingReview;
            $message = 'Review updated successfully!';
        } else {
            // Create new review
            $review = ProductReview::create([
                'cloth_id' => $clothId,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            $message = 'Review posted successfully!';

            // Notify Cloth Owner
            if ($cloth->user_id && $cloth->user_id !== Auth::id()) {
                \App\Models\Notification::create([
                    'user_id' => $cloth->user_id,
                    'title' => 'New Review Received',
                    'message' => Auth::user()->name . " rated your item '{$cloth->title}' {$request->rating} stars.",
                    'type' => 'info',
                    'icon' => 'bi-star-fill',
                    'data' => ['cloth_id' => $cloth->id, 'review_id' => $review->id],
                    'read' => false
                ]);
            }
        }

        // Load relationships for response
        $review->load('user');

        return response()->json([
            'success' => true,
            'message' => $message,
            'review' => $review
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to delete a review.'
            ], 401);
        }

        $review = ProductReview::findOrFail($id);

        // Check if user owns this review
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own reviews.'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully!'
        ]);
    }
}
