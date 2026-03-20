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
    public function getGstDetails(string $gstin): array
    {
        $response = Http::withHeaders([
            'userCode' => env('IMWALLET_USER_CODE'),
            'webToken' => env('IMWALLET_WEB_TOKEN'),
            'Content-Type' => 'application/json'
        ])->post('https://partner.imwallet.in/web_services/verificationSuit/walletBased/getGstDetails.jsp', [
            'gstin' => $gstin
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        $response->throw();
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
            'legal_name' => null,
            'trade_name' => null,
            'constitution_of_business' => null,
            'status' => null,
            'registration_date' => null,
            'principal_address' => null,
            'nature_of_business' => null,
        ];

        if (empty($apiResponse['data'])) {
            return $data;
        }

        $gstData = $apiResponse['data'];

        // If the new 'result' structure is present
        if (isset($gstData['result']['taxpayerDetails'])) {
            $taxpayer = $gstData['result']['taxpayerDetails'];
            $data['legal_name'] = $taxpayer['lgnm'] ?? null;
            $data['trade_name'] = $taxpayer['tradeNam'] ?? null;
            $data['name'] = $data['trade_name'] ?? $data['legal_name'] ?? null;
            $data['constitution_of_business'] = $taxpayer['ctb'] ?? null;
            $data['status'] = $taxpayer['sts'] ?? null;
            $data['registration_date'] = $taxpayer['rgdt'] ?? null;
            $data['nature_of_business'] = $taxpayer['nba'] ?? null;
        } else {
            // Extract Business Name (old structure fallback)
            if (isset($gstData['tradeName']) || isset($gstData['legalName'])) {
                $data['trade_name'] = $gstData['tradeName'] ?? null;
                $data['legal_name'] = $gstData['legalName'] ?? null;
                $data['name'] = $data['trade_name'] ?? $data['legal_name'] ?? null;
            }
        }

        // Extract Address
        if (isset($gstData['result']['business_places']['pradr']['adr'])) {
            $data['principal_address'] = $gstData['result']['business_places']['pradr']['adr'];
            $data['address'] = $data['principal_address'];
        } elseif (isset($gstData['pradr']['addr'])) {
            // old structure fallback
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
                $data['principal_address'] = $fullAddress;
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
    public function getAadhaarKycUrl(string $redirectUrl): array
    {
        $response = Http::withHeaders([
            'userCode' => env('IMWALLET_USER_CODE'),
            'webToken' => env('IMWALLET_WEB_TOKEN'),
            'Content-Type' => 'application/json'
        ])->post('https://partner.imwallet.in/web_services/verificationSuit/walletBased/unifiedKyc/getUrl.jsp', [
            'redirectUrl' => $redirectUrl
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        $response->throw();
    }
}
