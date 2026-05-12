<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
            'chest_bust' => 'nullable|string|max:255',
            'waist' => 'nullable|string|max:255',
            'length' => 'nullable|string|max:255',
            'shoulder' => 'nullable|string|max:255',
            'sleeve_length' => 'nullable|string|max:255',
        ]);
        $size = Size::create($request->all());
        return response()->json(['success' => true, 'size' => $size]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sizes,name,' . $id,
            'chest_bust' => 'nullable|string|max:255',
            'waist' => 'nullable|string|max:255',
            'length' => 'nullable|string|max:255',
            'shoulder' => 'nullable|string|max:255',
            'sleeve_length' => 'nullable|string|max:255',
        ]);
        $size = Size::findOrFail($id);
        $size->update($request->all());
        return response()->json(['success' => true, 'size' => $size]);
    }

    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();
        return response()->json(['success' => true]);
    }
} 