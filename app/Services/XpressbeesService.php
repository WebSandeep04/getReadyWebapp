<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XpressbeesService
{
    protected $baseUrl;

    public function __construct()
    {
        // Use sandbox or production URL based on config
        $this->baseUrl = config('services.xpressbees.base_url', 'https://api.xpressbees.com/v1');
    }

    public function login()
    {
        $email = config('services.xpressbees.email');
        $password = config('services.xpressbees.password');

        if (empty($email) || empty($password)) {
            Log::warning("Xpressbees credentials missing. Using MOCK mode.");
            return 'MOCK_TOKEN';
        }

        try {
            $response = Http::post($this->baseUrl . '/login', [
                'email' => $email,
                'password' => $password
            ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }

            Log::error('Xpressbees Login Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Xpressbees Connection Error: ' . $e->getMessage());
            return 'MOCK_TOKEN'; // Fallback to mock for local testing
        }
    }

    public function createOrder($orderData)
    {
        $token = $this->login();

        if (!$token) {
            return null;
        }

        // MOCK MODE
        if ($token === 'MOCK_TOKEN') {
            Log::info("Xpressbees [MOCK]: Creating Order #" . ($orderData['order_number'] ?? 'unknown'));
            // Simulate random processing time
            sleep(1); 
            
            return [
                'status' => true,
                'message' => 'Order created successfully (Mock)',
                'awb_number' => 'XB' . rand(100000999, 999999999),
                'order_id' => $orderData['order_number'],
                'label_url' => 'https://www.xpressbees.com/track', // Dummy URL
            ];
        }

        $response = Http::withToken($token)->post($this->baseUrl . '/orders', $orderData);

        if ($response->successful()) {
            return $response->json();
        }
        
        Log::error('Xpressbees Create Order Failed: ' . $response->body());
        return null;
    }

    public function trackShipment($awb)
    {
        $token = $this->login();

        if (!$token) return null;

        // MOCK MODE
        if ($token === 'MOCK_TOKEN') {
            // Return random statuses for testing based on AWB
            // Use AWB to deterministically pick a status so it doesn't flip-flop every second
            // But for demo, random change is better
            
            $statuses = ['In Transit', 'Out for Delivery', 'Delivered'];
            $status = $statuses[rand(0, 2)];
            
            return [
                'data' => [
                    'status' => $status,
                    'current_status' => $status,
                    'scans' => [
                        ['location' => 'Mumbai Hub', 'date' => now()->subDays(1)->toDateTimeString(), 'status' => 'Picked Up'],
                        ['location' => 'Delhi Hub', 'date' => now()->toDateTimeString(), 'status' => $status],
                    ]
                ]
            ];
        }

        $response = Http::withToken($token)->get($this->baseUrl . '/track/' . $awb);

        return $response->json();
    }
}
