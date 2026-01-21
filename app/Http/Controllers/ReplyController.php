<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reply;
use App\Models\ProductQuestion;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReplyController extends Controller
{

    /**
     * Store a reply to a product question.
     */
    public function storeQuestionReply(Request $request, $id)
    {
        $question = ProductQuestion::findOrFail($id);
        return $this->store($request, $question);
    }

    /**
     * Store a reply to a product review.
     */
    public function storeReviewReply(Request $request, $id)
    {
        $review = ProductReview::findOrFail($id);
        return $this->store($request, $review);
    }

    /**
     * Common logic to store a reply.
     */
    private function store(Request $request, $model)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to reply.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reply = $model->replies()->create([
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        // Load user relationship for response
        $reply->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Reply posted successfully!',
            'reply' => $reply
        ]);
    }

    /**
     * Delete a reply.
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to delete a reply.'
            ], 401);
        }

        $reply = Reply::findOrFail($id);

        // Check if user owns this reply
        if ($reply->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own replies.'
            ], 403);
        }

        $reply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reply deleted successfully!'
        ]);
    }
}
