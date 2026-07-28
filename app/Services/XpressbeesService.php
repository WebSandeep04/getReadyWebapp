<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FrontendSetting;

class XpressbeesService
{
    protected $baseUrl;

    public function __construct()
    {
        // Use sandbox or production URL based on config
        $this->baseUrl = config('services.xpressbees.base_url', 'https://shipment.xpressbees.com/api');
    }

    public function login()
    {
        $email = config('services.xpressbees.email');
        $password = config('services.xpressbees.password');

        if (empty($email) || empty($password)) {
            Log::error("Xpressbees credentials missing.");
            return null;
        }

        try {
            $response = Http::post($this->baseUrl . '/users/login', [
                'email' => $email,
                'password' => $password
            ]);

            if ($response->successful()) {
                // The new API usually returns the token directly in response or in data.
                // Depending on actual response structure, typically it's $response->json('data') or similar.
                // We'll try to extract token
                $data = $response->json();
                return $data['data'] ?? $data['token'] ?? null;
            }

            Log::error('Xpressbees Login Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Xpressbees Connection Error: ' . $e->getMessage());
            return null;
        }
    }


    public function createOrder($orderData)
    {
        $token = $this->login();

        if (!$token) {
            return null;
        }

        if (!isset($orderData['pickup'])) {
            Log::error('Xpressbees Create Order Failed: Pickup details are missing.');
            return ['status' => false, 'message' => 'Pickup details are missing.'];
        }



        Log::info('Xpressbees Create Order Request:', $orderData);
        $response = Http::withToken($token)->post($this->baseUrl . '/shipments2', $orderData);

        if ($response->successful()) {
            $json = $response->json();
            Log::info('Xpressbees Create Order Success Response:', $json);
            
            // Map the new API format to what the controllers expect
            if (isset($json['data'])) {
                return [
                    'status' => $json['status'] ?? true,
                    'awb_number' => $json['data']['awb_number'] ?? null,
                    'order_id' => $json['data']['order_id'] ?? null,
                    'label_url' => $json['data']['label'] ?? null,
                    'raw_data' => $json
                ];
            }
            return $json;
        }
        
        Log::error('Xpressbees Create Order Failed: ' . $response->body());
        return null;
    }

    public function createReturnOrder($orderData)
    {
        // Document says valid payment_type values: cod, prepaid & reverse
        $orderData['payment_type'] = 'reverse';
        return $this->createOrder($orderData);
    }

    public function trackShipment($awb)
    {
        $token = $this->login();

        if (!$token) return null;



        Log::info('Xpressbees Track Shipment Request for AWB: ' . $awb);
        $response = Http::withToken($token)->get($this->baseUrl . '/shipments2/track/' . $awb);

        if ($response->successful()) {
            Log::info('Xpressbees Track Shipment Success Response:', $response->json());
        } else {
            Log::error('Xpressbees Track Shipment Failed: ' . $response->body());
        }

        return $response->json();
    }
    public function cancelShipment($awb)
    {
        $token = $this->login();

        if (!$token) {
            return false;
        }

        Log::info('Xpressbees Cancel Shipment Request for AWB: ' . $awb);
        $response = Http::withToken($token)->post($this->baseUrl . '/shipments2/cancel', [
            'awb' => $awb
        ]);

        if ($response->successful()) {
            Log::info('Xpressbees Cancel Shipment Success Response:', $response->json());
            return true;
        }

        Log::error('Xpressbees Cancel Shipment Failed: ' . $response->body());
        return false;
    }
}
