<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BodyTypeFit;

class BodyTypeFitController extends Controller
{
    public function index()
    {
        return view('admin.screens.body_type_fit', [
            'total' => BodyTypeFit::count(),
        ]);
    }

    public function json()
    {
        return response()->json(BodyTypeFit::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:body_type_fits,name',
        ]);
        $bodyTypeFit = BodyTypeFit::create(['name' => $request->name]);
        return response()->json(['success' => true, 'body_type_fit' => $bodyTypeFit]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:body_type_fits,name,' . $id,
        ]);
        $bodyTypeFit = BodyTypeFit::findOrFail($id);
        $bodyTypeFit->update(['name' => $request->name]);
        return response()->json(['success' => true, 'body_type_fit' => $bodyTypeFit]);
    }

    public function destroy($id)
    {
        $bodyTypeFit = BodyTypeFit::findOrFail($id);
        $bodyTypeFit->delete();
        return response()->json(['success' => true]);
    }
} 