<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BottomType;

class BottomTypeController extends Controller
{
    public function index()
    {
        return view('admin.screens.bottom_type', [
            'total' => BottomType::count(),
        ]);
    }

    public function json()
    {
        return response()->json(BottomType::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:bottom_types,name',
        ]);
        $bottomType = BottomType::create(['name' => $request->name]);
        return response()->json(['success' => true, 'bottom_type' => $bottomType]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:bottom_types,name,' . $id,
        ]);
        $bottomType = BottomType::findOrFail($id);
        $bottomType->update(['name' => $request->name]);
        return response()->json(['success' => true, 'bottom_type' => $bottomType]);
    }

    public function destroy($id)
    {
        $bottomType = BottomType::findOrFail($id);
        $bottomType->delete();
        return response()->json(['success' => true]);
    }
} 