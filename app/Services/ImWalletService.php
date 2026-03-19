<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImWalletService
{
    /**
     * Fetch GST Details from IM Wallet API
     *
     * @param string $gstin
     * @return array|null
     */
    public function getGstDetails(string $gstin): ?array
    {
        try {
            $response = Http::withHeaders([
                'userCode' => env('IMWALLET_USER_CODE', 'IMAPIXXX'),
                'webToken' => env('IMWALLET_WEB_TOKEN', 'XXX'),
                'Content-Type' => 'application/json',
                'Cookie' => 'JSESSIONID=' . env('IMWALLET_JSESSIONID', '2D4C230492EA6A7AA4593DED170FDA3E')
            ])->post('https://partner.imwallet.in/web_services/verificationSuit/walletBased/getGstDetails.jsp', [
                'gstin' => $gstin
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('IM Wallet API Error: HTTP ' . $response->status() . ' - ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('IM Wallet API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract formatted business data from the API response
     *
     * @param array $apiResponse
     * @return array
     */
    public function extractBusinessData(array $apiResponse): array
    {
        $data = [
            'name' => null,
            'address' => null,
        ];

        if (empty($apiResponse['data'])) {
            return $data;
        }

        $gstData = $apiResponse['data'];

        // Extract Business Name
        if (isset($gstData['tradeName']) || isset($gstData['legalName'])) {
            $data['name'] = $gstData['tradeName'] ?? $gstData['legalName'] ?? null;
        }

        // Extract Address
        if (isset($gstData['pradr']['addr'])) {
            $addr = $gstData['pradr']['addr'];
            $addressParts = [];
            if (!empty($addr['bno'])) $addressParts[] = $addr['bno'];
            if (!empty($addr['st'])) $addressParts[] = $addr['st'];
            if (!empty($addr['loc'])) $addressParts[] = $addr['loc'];
            if (!empty($addr['dst'])) $addressParts[] = $addr['dst'];
            if (!empty($addr['stcd'])) $addressParts[] = $addr['stcd'];
            if (!empty($addr['pncd'])) $addressParts[] = $addr['pncd'];

            $fullAddress = implode(', ', $addressParts);
            if ($fullAddress) {
                $data['address'] = $fullAddress;
            }
        }

        return $data;
    }

    /**
     * Get Unified KYC URL for Aadhaar Verification
     *
     * @param string $redirectUrl
     * @return array|null
     */
    public function getAadhaarKycUrl(string $redirectUrl): ?array
    {
        try {
            $response = Http::withHeaders([
                'userCode' => env('IMWALLET_USER_CODE', 'IMAPIXXX'),
                'webToken' => env('IMWALLET_WEB_TOKEN', 'XXX'),
                'Content-Type' => 'application/json',
                'Cookie' => 'JSESSIONID=' . env('IMWALLET_JSESSIONID', '983966F203450D5770B464A3920DDD2C') // Using the JSESSIONID provided by user or use env
            ])->post('https://partner.imwallet.in/web_services/verificationSuit/walletBased/unifiedKyc/getUrl.jsp', [
                'redirectUrl' => $redirectUrl
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('IM Wallet Aadhaar KYC API Error: HTTP ' . $response->status() . ' - ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('IM Wallet Aadhaar KYC Exception: ' . $e->getMessage());
            return null;
        }
    }
}
