<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Color;

class ColorController extends Controller
{
    public function index()
    {
        return view('admin.screens.color', [
            'total' => Color::count(),
        ]);
    }

    public function json()
    {
        return response()->json(Color::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:colors,name',
        ]);
        $color = Color::create(['name' => $request->name]);
        return response()->json(['success' => true, 'color' => $color]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:colors,name,' . $id,
        ]);
        $color = Color::findOrFail($id);
        $color->update(['name' => $request->name]);
        return response()->json(['success' => true, 'color' => $color]);
    }

    public function destroy($id)
    {
        $color = Color::findOrFail($id);
        $color->delete();
        return response()->json(['success' => true]);
    }
} 