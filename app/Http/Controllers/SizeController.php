<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Size;

class SizeController extends Controller
{
    public function index()
    {
        return view('admin.screens.size', [
            'total' => Size::count(),
        ]);
    }

    public function json()
    {
        return response()->json(Size::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sizes,name',
        ]);
        $size = Size::create(['name' => $request->name]);
        return response()->json(['success' => true, 'size' => $size]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sizes,name,' . $id,
        ]);
        $size = Size::findOrFail($id);
        $size->update(['name' => $request->name]);
        return response()->json(['success' => true, 'size' => $size]);
    }

    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();
        return response()->json(['success' => true]);
    }
} 