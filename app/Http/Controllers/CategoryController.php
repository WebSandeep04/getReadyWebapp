<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $showFilters = false;
        return view('admin.screens.category', [
            'showFilters' => $showFilters,
            'total' => Category::count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:category,name',
        ]);
        $category = Category::create(['name' => $request->name]);
        return response()->json(['success' => true, 'category' => $category]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:category,name,' . $id,
        ]);
        $category = Category::findOrFail($id);
        $category->update(['name' => $request->name]);
        return response()->json(['success' => true, 'category' => $category]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['success' => true]);
    }

    public function json()
    {
        return response()->json(Category::all());
    }
} 