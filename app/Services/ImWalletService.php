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
        $payload = ['gstin' => $gstin];
        Log::info('IM Wallet GST Details Request:', $payload);

        $response = Http::withHeaders([
            'userCode' => env('IMWALLET_USER_CODE'),
            'webToken' => env('IMWALLET_WEB_TOKEN'),
            'Content-Type' => 'application/json'
        ])->post('https://partner.imwallet.in/web_services/verificationSuit/walletBased/getGstDetails.jsp', $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            Log::info('IM Wallet GST Details Response:', $responseData ?? []);
            return $responseData;
        }

        Log::error('IM Wallet GST Details Error:', $response->json() ?? ['status' => $response->status()]);
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
            'members' => null,
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
            $data['members'] = $taxpayer['mbr'] ?? null;
        } else {
            // Extract Business Name (old structure fallback)
            if (isset($gstData['tradeName']) || isset($gstData['legalName'])) {
                $data['trade_name'] = $gstData['tradeName'] ?? null;
                $data['legal_name'] = $gstData['legalName'] ?? null;
                $data['name'] = $data['trade_name'] ?? $data['legal_name'] ?? null;
            }
        }

        // Extract robust State, City and Address Strings
        $data['state'] = null;
        $data['city'] = null;

        // Try extracting exact State/City mapping
        if (isset($gstData['result']['business_places']['pradr']['addr'])) {
            $addrOpts = $gstData['result']['business_places']['pradr']['addr'];
            if (is_array($addrOpts)) {
                $data['state'] = $addrOpts['stcd'] ?? null;
                $data['city'] = $addrOpts['dst'] ?? null;
            }
        } elseif (isset($gstData['result']['business_places'][0]['pradr']['addr'])) {
            $firstPlace = $gstData['result']['business_places'][0];
            $data['state'] = $firstPlace['pradr']['addr']['stcd'] ?? null;
            $data['city'] = $firstPlace['pradr']['addr']['dst'] ?? null;
        }

        // Extract primary address string securely across all known payload formats
        if (isset($gstData['result']['business_places']['pradr']['adr'])) {
            $data['principal_address'] = $gstData['result']['business_places']['pradr']['adr'];
            $data['address'] = $data['principal_address'];
        } elseif (isset($gstData['result']['taxpayerDetails']['pradr']['adr'])) {
            $data['principal_address'] = $gstData['result']['taxpayerDetails']['pradr']['adr'];
            $data['address'] = $data['principal_address'];
        } elseif (isset($gstData['pradr']['addr'])) {
            // old structure fallback
            $addr = $gstData['pradr']['addr'];
            $addressParts = [];
            if (!empty($addr['bno'])) $addressParts[] = $addr['bno'];
            if (!empty($addr['st'])) $addressParts[] = $addr['st'];
            if (!empty($addr['loc'])) $addressParts[] = $addr['loc'];
            if (!empty($addr['dst'])) {
                $addressParts[] = $addr['dst'];
                if (!$data['city']) $data['city'] = $addr['dst'];
            }
            if (!empty($addr['stcd'])) {
                $addressParts[] = $addr['stcd'];
                if (!$data['state']) $data['state'] = $addr['stcd'];
            }
            if (!empty($addr['pncd'])) $addressParts[] = $addr['pncd'];

            $fullAddress = implode(', ', $addressParts);
            if ($fullAddress) {
                $data['principal_address'] = $fullAddress;
                $data['address'] = $fullAddress;
            }
        }

        // 💡 Logic: If standard keys failed, attempt to smartly extract State & City from the formatted right-side of the string!
        if (!$data['state'] || !$data['city']) {
            if ($data['principal_address']) {
                $parts = array_map('trim', explode(',', $data['principal_address']));
                $count = count($parts);
                // Usually formats are: ..., City, State, Pincode
                // Let's grab them if there are at least 3 parts and the last part is a number (Pincode)
                if ($count >= 3 && is_numeric(str_replace(' ', '', $parts[$count - 1]))) {
                    if (!$data['state']) {
                        $data['state'] = $parts[$count - 2]; // 2nd from end (e.g. Uttar Pradesh)
                    }
                    if (!$data['city']) {
                        $data['city'] = preg_replace('/\s?(District|Nagar)$/i', '', $parts[$count - 3]); // 3rd from end (e.g. Kanpur)
                    }
                }
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
        $payload = ['redirectUrl' => $redirectUrl];
        Log::info('IM Wallet Aadhaar KYC URL Request:', $payload);

        $response = Http::withHeaders([
            'userCode' => env('IMWALLET_USER_CODE'),
            'webToken' => env('IMWALLET_WEB_TOKEN'),
            'Content-Type' => 'application/json'
        ])->post('https://partner.imwallet.in/web_services/verificationSuit/walletBased/unifiedKyc/getUrl.jsp', $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            Log::info('IM Wallet Aadhaar KYC URL Response:', $responseData ?? []);
            return $responseData;
        }

        Log::error('IM Wallet Aadhaar KYC URL Error:', $response->json() ?? ['status' => $response->status()]);
        $response->throw();
    }

    /**
     * Get Status of Aadhaar KYC using Unified Transaction ID
     *
     * @param string $unifiedTransactionId
     * @return array
     */
    public function getAadhaarKycStatus(string $unifiedTransactionId): array
    {
        $payload = ['unifiedTransactionId' => $unifiedTransactionId];
        Log::info('IM Wallet Aadhaar KYC Status Request:', $payload);

        $response = Http::withHeaders([
            'userCode' => env('IMWALLET_USER_CODE'),
            'webToken' => env('IMWALLET_WEB_TOKEN'),
            'Content-Type' => 'application/json'
        ])->post('https://partner.imwallet.in/web_services/verificationSuit/walletBased/unifiedKyc/getStatus.jsp', $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            Log::info('IM Wallet Aadhaar KYC Status Response:', $responseData ?? []);
            return $responseData;
        }

        Log::error('IM Wallet Aadhaar KYC Status Error:', $response->json() ?? ['status' => $response->status()]);
        $response->throw();
    }
}
