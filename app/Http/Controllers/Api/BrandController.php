<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand; // Ensure this matches your Brand model path
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of all brands.
     */
    public function index()
    {
        // Fetch all active brands, ordered by name. 
        // You can adjust the query based on your database schema (e.g., adding pagination).
        $brands = Brand::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Brands fetched successfully',
            'data' => $brands
        ], 200);
    }
}
