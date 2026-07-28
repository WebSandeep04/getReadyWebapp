<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FrontendSetting;

class FrontendController extends Controller
{
    // Frontend Management
    public function frontend()
    {
        $settings = FrontendSetting::all();
        $sections = [
            'general' => 'General Settings',
            'logo' => 'Logo & Assets',
            'hero' => 'Hero Section',
            'about' => 'About Section',
            'footer' => 'Footer Info',
            'social' => 'Social Links',
        ];
        return view('admin.screens.frontend', compact('sections', 'settings'));
    }

    // Update frontend setting (AJAX)
    public function updateFrontendSetting(Request $request)
    {
        $request->validate([
            'section' => 'required|string',
            'key' => 'required|string',
        ]);
        
        $setting = FrontendSetting::updateOrCreate(
            ['section' => $request->section, 'key' => $request->key],
            ['value' => $request->value]
        );
        
        return response()->json(['success' => true]);
    }

    // Get frontend settings by section (AJAX)
    public function getFrontendSettings($section)
    {
        $settings = FrontendSetting::where('section', $section)->pluck('value', 'key');
        return response()->json($settings);
    }
}
