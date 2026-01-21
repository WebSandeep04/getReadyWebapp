<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FabricType;

class FabricTypeController extends Controller
{
    public function index()
    {
        return view('admin.screens.fabric_type', [
            'total' => FabricType::count(),
        ]);
    }

    public function json()
    {
        return response()->json(FabricType::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:fabric_types,name',
        ]);
        $fabricType = FabricType::create(['name' => $request->name]);
        return response()->json(['success' => true, 'fabric_type' => $fabricType]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:fabric_types,name,' . $id,
        ]);
        $fabricType = FabricType::findOrFail($id);
        $fabricType->update(['name' => $request->name]);
        return response()->json(['success' => true, 'fabric_type' => $fabricType]);
    }

    public function destroy($id)
    {
        $fabricType = FabricType::findOrFail($id);
        $fabricType->delete();
        return response()->json(['success' => true]);
    }
} 