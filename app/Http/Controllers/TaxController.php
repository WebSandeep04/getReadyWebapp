<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tax;

class TaxController extends Controller
{
    public function index()
    {
        return view('admin.screens.tax', [
            'total' => Tax::count(),
        ]);
    }

    public function json()
    {
        return response()->json(Tax::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:taxes,name',
            'percentage' => 'required|numeric|min:0|max:100',
            'status' => 'nullable|integer|in:0,1',
        ]);

        $tax = Tax::create([
            'name' => $request->name,
            'percentage' => $request->percentage,
            'status' => $request->status ?? 1,
        ]);

        return response()->json(['success' => true, 'tax' => $tax]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:taxes,name,' . $id,
            'percentage' => 'required|numeric|min:0|max:100',
            'status' => 'nullable|integer|in:0,1',
        ]);

        $tax = Tax::findOrFail($id);
        $tax->update([
            'name' => $request->name,
            'percentage' => $request->percentage,
            'status' => $request->status ?? $tax->status,
        ]);

        return response()->json(['success' => true, 'tax' => $tax]);
    }

    public function toggleStatus($id)
    {
        $tax = Tax::findOrFail($id);
        $tax->status = $tax->status == 1 ? 0 : 1;
        $tax->save();

        return response()->json(['success' => true, 'status' => $tax->status]);
    }

    public function destroy($id)
    {
        $tax = Tax::findOrFail($id);
        $tax->delete();
        return response()->json(['success' => true]);
    }
}
