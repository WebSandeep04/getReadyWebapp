<?php

namespace App\Http\Controllers;

use App\Models\ProductQuestion;
use App\Models\Cloth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Store a new question for a product.
     */
    public function store(Request $request, $clothId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to ask a question.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cloth = Cloth::findOrFail($clothId);

        $question = ProductQuestion::create([
            'cloth_id' => $clothId,
            'user_id' => Auth::id(),
            'question' => $request->question,
        ]);

        // Load relationships for response
        $question->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Question posted successfully!',
            'question' => $question
        ]);
    }

    /**
     * Answer a question (for product owner or admin).
     */
    public function answer(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to answer a question.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'answer' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $question = ProductQuestion::with('cloth')->findOrFail($id);

        // Check if user is the product owner or admin
        $isOwner = $question->cloth->user_id === Auth::id();
        // You can add admin check here if needed: || Auth::user()->is_admin

        if (!$isOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Only the product owner can answer questions.'
            ], 403);
        }

        $question->update([
            'answer' => $request->answer,
            'answered_by' => Auth::id(),
            'answered_at' => now(),
        ]);

        $question->load(['user', 'answerer']);

        return response()->json([
            'success' => true,
            'message' => 'Answer posted successfully!',
            'question' => $question
        ]);
    }

    /**
     * Delete a question.
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to delete a question.'
            ], 401);
        }

        $question = ProductQuestion::findOrFail($id);

        // Check if user owns this question or is the product owner
        $isOwner = $question->user_id === Auth::id();
        $isProductOwner = $question->cloth->user_id === Auth::id();

        if (!$isOwner && !$isProductOwner) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own questions.'
            ], 403);
        }

        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully!'
        ]);
    }
}
