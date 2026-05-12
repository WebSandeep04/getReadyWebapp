<?php

namespace App\Http\Controllers;

use App\Models\Cloth;
use App\Models\Brand;
use Illuminate\Http\Request;

use App\Models\Category;

class HomeController extends Controller
{
    /**
     * Show the home page with clothes data.
     */
    public function index(Request $request)
    {
        $categories = Category::all();
        $categoryId = $request->get('category_id');

        $query = Cloth::with(['images', 'user', 'brand'])
            ->where('is_available', true)
            ->where('is_approved', 1);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $clothes = $query->inRandomOrder()
            ->take(8)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('clothes.partials.products-grid', compact('clothes'))->render(),
                'category_url' => $categoryId ? route('clothes.index') . '?categories[]=' . $categoryId : route('clothes.index')
            ]);
        }

        $latestClothes = Cloth::with(['images', 'user', 'brand'])
            ->where('is_available', true)
            ->where('is_approved', 1)
            ->latest()
            ->take(12)
            ->get();

        $brands = Brand::all();
        $showHero = true;

        return view('home', compact('clothes', 'latestClothes', 'brands', 'showHero', 'categories'));
    }
} 