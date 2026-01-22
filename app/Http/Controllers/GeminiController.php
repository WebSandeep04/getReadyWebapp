<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        $prompt = "Please write a professional and attractive description for a cloth item titled '{$title}' based on these details: {$rawDescription}. The description should be suitable for a rental/selling listing. strictly keep it under 200 characters.";

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
}
