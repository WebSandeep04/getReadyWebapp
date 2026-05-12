<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\VirtualTryOn;

class ProcessFashnTryOn implements ShouldQueue
{
    use Queueable;

    public $vto;
    public $tries = 50;

    /**
     * Create a new job instance.
     */
    public function __construct(VirtualTryOn $vto)
    {
        $this->vto = $vto;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (in_array($this->vto->status, ['completed', 'failed'])) {
            return;
        }

        $apiKey = env('FASHN_API_KEY');

        // Step 1: Initialize Job if not created
        if (!$this->vto->job_id) {
            $cloth = $this->vto->cloth;
            $userImageUrl = url('storage/' . $this->vto->user_image_path);
            $clothImagePath = $cloth->images->first()->image_path ?? null;
            $clothImageUrl = url('storage/' . $clothImagePath);

            $category = 'tops';
            if ($cloth->category) {
                $name = strtolower($cloth->category->name);

                if (str_contains($name, 'jean') || str_contains($name, 'pant') || str_contains($name, 'trouser')) {
                    $category = 'bottoms';
                } elseif (str_contains($name, 'dress') || str_contains($name, 'gown')) {
                    $category = 'full';
                } elseif (str_contains($name, 'shirt') || str_contains($name, 'tshirt') || str_contains($name, 'top')) {
                    $category = 'tops';
                }
            }

            $initResponse = Http::timeout(60)->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->post("https://api.fashn.ai/v1/run", [
                'model_name' => 'tryon-v1',
                'inputs' => [
                    'model_image'   => $userImageUrl,
                    'garment_image' => $clothImageUrl,
                    'category'      => $category,
                    'output_format' => 'jpeg',
                    'return_base64' => false
                ]
            ]);

            Log::info('Queue Fashn Init', $initResponse->json() ?? []);

            if (!$initResponse->successful() || !$initResponse->json('id')) {
                $this->failVTO('Failed to initialize Fashn.ai process.');
                return;
            }

            $this->vto->update([
                'job_id' => $initResponse->json('id'),
                'status' => 'processing'
            ]);

            // Release job back to queue to check status in 5 seconds
            $this->release(5);
            return;
        }

        // Step 2: Poll Fashn API for job_id
        $statusResponse = Http::timeout(60)->withHeaders([
            'Authorization' => "Bearer {$apiKey}"
        ])->get("https://api.fashn.ai/v1/status/{$this->vto->job_id}");

        if ($statusResponse->successful()) {
            $statusData = $statusResponse->json();
            $status = $statusData['status'] ?? 'failed';

            if ($status === 'completed') {
                $resultImageUrl = $statusData['output'][0]['url'] 
                    ?? $statusData['output'][0] 
                    ?? $statusData['image_url'] 
                    ?? null;

                $this->vto->update([
                    'status' => 'completed',
                    'result_image_url' => $resultImageUrl,
                ]);

                // Cleanup temp image upon success
                if (Storage::disk('public')->exists($this->vto->user_image_path)) {
                    Storage::disk('public')->delete($this->vto->user_image_path);
                }
            } elseif ($status === 'failed' || $status === 'canceled') {
                Log::error('Fashn Job Failed in Queue', $statusData);
                $this->failVTO($statusData['error'] ?? 'API internal failure.');
            } else {
                // 'starting' or 'processing': Check back in 5 seconds
                $this->release(5);
            }
        } else {
            Log::warning('Fashn API Status Request Failed', ['body' => $statusResponse->body()]);
            // Retry later
            $this->release(5);
        }
    }

    private function failVTO($message)
    {
        $this->vto->update([
            'status' => 'failed',
            'error_message' => $message,
        ]);
        if (Storage::disk('public')->exists($this->vto->user_image_path)) {
            Storage::disk('public')->delete($this->vto->user_image_path);
        }
    }
}
