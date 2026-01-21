<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GarmentCondition;

class GarmentConditionController extends Controller
{
    public function index()
    {
        return view('admin.screens.garment_condition', [
            'total' => GarmentCondition::count(),
        ]);
    }

    public function json()
    {
        return response()->json(GarmentCondition::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:garment_conditions,name',
        ]);
        $garmentCondition = GarmentCondition::create(['name' => $request->name]);
        return response()->json(['success' => true, 'garment_condition' => $garmentCondition]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:garment_conditions,name,' . $id,
        ]);
        $garmentCondition = GarmentCondition::findOrFail($id);
        $garmentCondition->update(['name' => $request->name]);
        return response()->json(['success' => true, 'garment_condition' => $garmentCondition]);
    }

    public function destroy($id)
    {
        $garmentCondition = GarmentCondition::findOrFail($id);
        $garmentCondition->delete();
        return response()->json(['success' => true]);
    }
} 