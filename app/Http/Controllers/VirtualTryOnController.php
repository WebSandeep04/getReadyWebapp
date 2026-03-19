<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Cloth;
use Illuminate\Support\Facades\Log;

class VirtualTryOnController extends Controller
{
    /**
     * Virtual Try-On specific logic using Fashn.ai API
     * Takes a user photo and a cloth ID, sends to Fashn.ai and returns composite image.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'user_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'cloth_id' => 'required|exists:clothes,id',
        ]);

        $cloth = Cloth::with(['images', 'category'])->findOrFail($request->cloth_id);
        
        // 1. Save the uploaded user image temporarily
        $userImage = $request->file('user_image');
        $userImagePath = $userImage->store('temp_vto', 'public');

        // 2. Get the cloth image
        $clothImagePath = $cloth->images->first()->image_path ?? null;
        if (!$clothImagePath) {
            return response()->json(['error' => 'Cloth has no image for Try-On.'], 400);
        }

        $apiKey = env('FASHN_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'Fashn.ai API key is missing. Please add FASHN_API_KEY to your .env file.'], 500);
        }

        try {
            // Read both images and convert them to Base64
            $userImageBase64 = 'data:image/jpeg;base64,' . base64_encode(Storage::disk('public')->get($userImagePath));
            $clothImageBase64 = 'data:image/jpeg;base64,' . base64_encode(Storage::disk('public')->get($clothImagePath));

            // Step 1: Start the Job on Fashn.ai (using product-to-model which auto-supports VTO)
            $initResponse = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->post("https://api.fashn.ai/v1/run", [
                'model_name' => 'product-to-model',
                'inputs' => [
                    'product_image' => $clothImageBase64,
                    'model_image'   => $userImageBase64,
                    'output_format' => 'jpeg', // Faster load times
                    'return_base64' => false   // False gives us a CDN URL directly containing the generated image
                ]
            ]);

            if (!$initResponse->successful()) {
                Log::error("Fashn API Error: " . $initResponse->body());
                return response()->json(['error' => 'Failed to initialize Fashn.ai process.'], 500);
            }

            // Step 2: Grab the generation ID to poll the status
            $jobId = $initResponse->json('id');
            if (!$jobId) {
                return response()->json(['error' => 'Invalid response from Fashn.ai.'], 500);
            }

            // Step 3: Poll until completion
            $status = 'starting';
            $resultImageUrl = null;
            $maxPolls = 20; // Maximum wait ~40 seconds
            $polls = 0;

            while (!in_array($status, ['completed', 'failed']) && $polls < $maxPolls) {
                sleep(2); // Wait 2 seconds before checking
                
                $statusResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$apiKey}"
                ])->get("https://api.fashn.ai/v1/status/{$jobId}");

                if ($statusResponse->successful()) {
                    $statusData = $statusResponse->json();
                    $status = $statusData['status'] ?? 'failed';

                    if ($status === 'completed') {
                        $resultImageUrl = $statusData['output'][0] ?? $statusData['image_url'] ?? null;
                    }
                }
                $polls++;
            }

            if ($status === 'completed' && $resultImageUrl) {
                return response()->json([
                    'success' => true,
                    'image_url' => $resultImageUrl,
                    'message' => 'Virtual Try-On completed successfully.'
                ]);
            }

            return response()->json(['error' => 'Fashn.ai API timed out or failed to complete.'], 500);

        } catch (\Exception $e) {
            Log::error('Fashn VTO Error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during VTO generation.'], 500);
        }
    }
}
