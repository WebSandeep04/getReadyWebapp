<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
class ColorController extends Controller
{
    public function index()
    {
        // Fetch all colors from the database
        $colors = Color::all();
        return response()->json([
            'success' => true,
            'data' => $colors
        ], 200);
    }
}
