<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Category; // Ensure you have a Category model

class CategoryController extends Controller
{
    public function index()
    {
        try {
            // Fetch all categories (you can add a select() to get specific columns)
            $categories = Category::all();

            return response()->json([
                'success' => true,
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
