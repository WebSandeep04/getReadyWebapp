<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class Msg91Service
{
    /**
     * Send an OTP to the given mobile number via MSG91.
     *
     * @throws RuntimeException
     */
    public function sendOtp(string $phone, string $otp): void
    {
        if (app()->environment('local', 'testing')) {
            Log::info("MSG91 skipped in {$this->currentEnvironment()} environment for {$phone}. OTP: {$otp}");
            return;
        }

        $config = config('services.msg91');

        if (empty($config['key']) || empty($config['template_id'])) {
            throw new RuntimeException('MSG91 credentials are not configured.');
        }

        $endpoint = $config['base_url'] ?? 'https://api.msg91.com/api/v5/otp';
        $mobile = $this->formatMobile($phone, $config['country_code'] ?? null);

        $payload = [
            'mobile' => $mobile,
            'otp' => $otp,
            'template_id' => $config['template_id'],
        ];

        if (!empty($config['sender'])) {
            $payload['sender'] = $config['sender'];
        }

        $response = Http::withHeaders([
            'authkey' => $config['key'],
            'content-type' => 'application/json',
        ])->post($endpoint, $payload);

        if ($response->failed()) {
            Log::error('MSG91 OTP send failed', [
                'phone' => $phone,
                'payload' => $payload,
                'response' => $response->json(),
            ]);

            throw new RuntimeException('Unable to send OTP at the moment. Please try again.');
        }
    }

    private function formatMobile(string $phone, ?string $countryCode): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? $phone;

        if ($countryCode && !Str::startsWith($digits, $countryCode)) {
            return $countryCode . $digits;
        }

        return $digits;
    }

    private function currentEnvironment(): string
    {
        return app()->environment();
    }
}

