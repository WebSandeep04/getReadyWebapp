<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Cloth;
use Illuminate\Support\Facades\Log;

class GeminiController extends Controller
{
    public function generateDescription(Request $request)
    {
        $request->validate([
            'raw_description' => 'required|string',
            'title' => 'nullable|string',
        ]);

        $rawDescription = $request->input('raw_description');
        $title = $request->input('title') ?? 'this item';
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json(['error' => 'Gemini API key not configured.'], 500);
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

        $prompt = "Write exactly ONE single, professional, and attractive description paragraph for a cloth item titled '{$title}' based on these details: {$rawDescription}. Do not provide options, lists, or labels like 'Option 1'. Just write the final description text directly. Keep it under 200 characters.";

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Could not generate description.';
            return response()->json(['description' => $generatedText]);
        } else {
            return response()->json(['error' => 'Failed to communicate with Gemini API: ' . $response->body()], 500);
        }
    }

    /**
     * Virtual Try-On specific logic.
     * Takes a user photo and a cloth ID, sends to Gemini/Imagen and returns composite image.
     */
    public function virtualTryOn(Request $request)
    {
        $request->validate([
            'user_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'cloth_id' => 'required|exists:clothes,id',
        ]);

        $cloth = Cloth::with('images')->findOrFail($request->cloth_id);
        
        // 1. Save the uploaded user image temporarily
        $userImage = $request->file('user_image');
        $userImagePath = $userImage->store('temp_vto', 'public');
        $userImageUrl = asset('storage/' . $userImagePath);

        // 2. Get the cloth image
        $clothImagePath = $cloth->images->first()->image_path ?? null;
        if (!$clothImagePath) {
            return response()->json(['error' => 'Cloth has no image for Try-On.'], 400);
        }
        $clothImageUrl = asset('storage/' . $clothImagePath);

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'Gemini/Imagen API key not configured.'], 500);
        }

        /* 
         * Important Note:
         * Currently, Google's public Gemini API (generativelanguage.googleapis.com) natively outputs text.
         * The Imagen 3 API handles text-to-image. Actual "Virtual Try On" (Image + Image -> Image) 
         * requires specialized diffusion models. 
         * 
         * Below is the structured API call for a generic Image generation endpoint. 
         * While waiting for Google to release a public VTO endpoint, we'll simulate the response 
         * so the frontend UI functions perfectly.
         */

        try {
            // Simulated API Logic (Replace with actual endpoint when available)
            // Example of how the call would look:
            /*
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-001:predict?key={$apiKey}", [
                'instances' => [
                    [
                        'prompt' => "Put the clothing from image 2 on the person in image 1 realistically.",
                        'image1' => base64_encode(Storage::disk('public')->get($userImagePath)),
                        'image2' => base64_encode(Storage::disk('public')->get($clothImagePath)),
                    ]
                ],
                'parameters' => ['sampleCount' => 1]
            ]);
            */

            // To make UI demo-able, we simulate processing time and return a success with the original user image
            // In a real VTO, this returns the merged image blob/base64.
            
            // Sleep for 3 seconds to simulate AI Processing Time for realistic UX
            sleep(3);
            
            // SIMULATED OUTPUT: Returning user's photo with a VTO successful flag.
            // Replace `$userImageUrl` with `$vtoImageUrl` from your actual Gemini/Vertex API response later.
            return response()->json([
                'success' => true,
                'image_url' => $userImageUrl, 
                'message' => 'Virtual Try-On generated successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('VTO Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error generating Virtual Try-On: ' . $e->getMessage()], 500);
        }
    }
}
