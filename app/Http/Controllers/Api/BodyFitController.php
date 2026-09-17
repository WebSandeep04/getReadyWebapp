<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BodyTypeFit;
use Illuminate\Http\Request;
class BodyFitController extends Controller
{
    public function index()
    {
        // Use the exact Model name here too
        $bodyFits = BodyTypeFit::all(); 
        return response()->json([
            'success' => true,
            'data' => $bodyFits
        ], 200);
    }
}
