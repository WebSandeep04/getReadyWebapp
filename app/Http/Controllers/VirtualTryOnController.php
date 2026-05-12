<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Cloth;
use App\Models\VirtualTryOn;
use App\Jobs\ProcessFashnTryOn;
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
            // 2. Create the VTO history record in DB
            $vto = VirtualTryOn::create([
                'user_id' => auth()->id() ?? null,
                'cloth_id' => $cloth->id,
                'user_image_path' => $userImagePath,
                'status' => 'pending'
            ]);

            // 3. Dispatch the Queue Job to handle API interaction asynchronously
            ProcessFashnTryOn::dispatch($vto);

            return response()->json([
                'success' => true,
                'vto_id' => $vto->id,
                'message' => 'Virtual Try-On is processing in the background.'
            ]);

        } catch (\Exception $e) {
            Log::error('Fashn VTO Error: ' . $e->getMessage());
            Storage::disk('public')->delete($userImagePath);
            return response()->json(['error' => 'An error occurred during VTO initialization.'], 500);
        }
    }

    /**
     * Polling endpoint for the Frontend AJAX/ProgressBar
     */
    public function status($id)
    {
        $vto = VirtualTryOn::findOrFail($id);

        return response()->json([
            'id' => $vto->id,
            'job_id' => $vto->job_id,
            'status' => $vto->status,
            'result_image_url' => $vto->result_image_url,
            'error_message' => $vto->error_message
        ]);
    }
}
