<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\FabricType; // Make sure you have a FabricType model!

class FabricTypeController extends Controller
{
    public function index()
    {
        try {
            // Fetch all fabric types from the database
            $fabricTypes = FabricType::all();

            return response()->json([
                'success' => true,
                'data' => $fabricTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch fabric types.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
