<?php

namespace App\Http\Controllers;

use App\Models\Cloth;
use App\Models\Brand;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the home page with clothes data.
     */
    public function index(Request $request)
    {
        $clothes = Cloth::with(['images', 'user'])
            ->where('is_available', true)
            ->where('is_approved', 1)
            ->latest()
            ->take(8)
            ->get();

        $brands = Brand::whereNotNull('logo')->get();

        $showHero = true;

        return view('home', compact('clothes', 'brands', 'showHero'));
    }
} 