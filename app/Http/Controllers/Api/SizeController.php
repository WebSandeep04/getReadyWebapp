<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
class SizeController extends Controller
{
    public function index()
    {
        // Fetch all sizes from the database
        $sizes = Size::all();
        return response()->json([
            'success' => true,
            'data' => $sizes
        ], 200);
    }
}
