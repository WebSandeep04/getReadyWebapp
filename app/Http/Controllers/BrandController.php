<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        return view('admin.screens.brand', [
            'total' => Brand::count(),
        ]);
    }

    public function json()
    {
        $brands = Brand::all()->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'logo' => $brand->logo ? asset('storage/' . $brand->logo) : null,
                'created_at' => $brand->created_at,
                'updated_at' => $brand->updated_at,
            ];
        });
        return response()->json($brands);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoPath = $logo->store('brands', 'public');
            $data['logo'] = $logoPath;
        }

        $brand = Brand::create($data);
        
        return response()->json([
            'success' => true, 
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'logo' => $brand->logo ? asset('storage/' . $brand->logo) : null,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $brand = Brand::findOrFail($id);
        $data = ['name' => $request->name];

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }
            
            $logo = $request->file('logo');
            $logoPath = $logo->store('brands', 'public');
            $data['logo'] = $logoPath;
        }

        $brand->update($data);
        
        return response()->json([
            'success' => true, 
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'logo' => $brand->logo ? asset('storage/' . $brand->logo) : null,
            ]
        ]);
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        
        // Delete logo file if exists
        if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
            Storage::disk('public')->delete($brand->logo);
        }
        
        $brand->delete();
        return response()->json(['success' => true]);
    }
}
