<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GarmentCondition;
use Illuminate\Http\Request;

class GarmentConditionController extends Controller
{
    public function index()
    {
        $conditions = GarmentCondition::all();

        return response()->json([
            'success' => true,
            'data' => $conditions
        ]);
    }
}
 
