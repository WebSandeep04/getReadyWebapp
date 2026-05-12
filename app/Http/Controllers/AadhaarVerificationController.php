<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ImWalletService;
use Illuminate\Support\Facades\Log;

class AadhaarVerificationController extends Controller
{
    public function __construct(private readonly ImWalletService $imWalletService)
    {
    }

    /**
     * Start the Aadhaar KYC process
     * Generates the KYC URL from IM Wallet and returns it to the frontend.
     */
    public function startKyc(Request $request)
    {
        // Even if they pass Aadhaar initially, the unified KYC generates a URL for verification.
        $aadhaarNumber = $request->input('aadhaar_number');
        if (auth()->check() && $aadhaarNumber) {
            $user = auth()->user();
            $user->aadhaar_number = $aadhaarNumber;
            // Optionally save it now, even if not verified yet
            $user->save();
        }

        $redirectUrl = route('aadhaar.callback');

        try {
            $kycUrlData = $this->imWalletService->getAadhaarKycUrl($redirectUrl);

            if (!isset($kycUrlData['data']['url']) && !isset($kycUrlData['url'])) {
                throw new \Exception('API returned success but no URL was found.');
            }

            $url = $kycUrlData['data']['url'] ?? $kycUrlData['url'] ?? $kycUrlData['data'];

            return response()->json([
                'success' => true,
                'url' => is_string($url) ? $url : (is_array($url) ? ($url['url'] ?? '') : ''),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to generate KYC URL. Please try again later. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Callback from IM Wallet after Aadhaar KYC
     */
    public function callback(Request $request)
    {
        // Log the entire callback payload from IM Wallet
        Log::info('IM Wallet Aadhaar KYC Callback Payload:', $request->all());

        // This endpoint will be hit when IM Wallet redirects the user back
        // Usually there is a referenceId or status returned in the query params.
        
        $status = $request->input('status');
        $unifiedTransactionId = $request->input('unifiedTransactionId');
        
        if (strtolower((string)$status) === 'success' || $request->has('referenceId') || $request->has('token') || $unifiedTransactionId) {
            if (auth()->check()) {
                $user = auth()->user();
                
                if ($unifiedTransactionId) {
                    try {
                        // "Now take unified id and hit other api"
                        $statusData = $this->imWalletService->getAadhaarKycStatus($unifiedTransactionId);
                        
                        if (isset($statusData['data'])) {
                            $data = $statusData['data'];
                            
                            $user->is_aadhaar_verified = true;
                            $user->aadhaar_masked_number = $data['maskedAdharNumber'] ?? null;
                            $user->aadhaar_address = $data['address'] ?? null;
                            $user->aadhaar_dob = $data['dob'] ?? null;
                            $user->aadhaar_care_of = $data['careOf'] ?? null;
                            $user->aadhaar_xml_link = $data['link'] ?? null;
                            $user->aadhaar_pdf_link = $data['pdfLink'] ?? null;
                            $user->aadhaar_image_base64 = $data['image'] ?? null;
                            $user->aadhaar_details = $data;
                            
                            // 💡 Logic: Always update internal state and city with robust Aadhaar string data
                            if (isset($data['address']) && is_array($data['address'])) {
                                $addr = $data['address'];
                                if (!empty($addr['state'])) {
                                    $user->state = $addr['state'];
                                }
                                if (!empty($addr['dist'])) {
                                    $user->city = $addr['dist'];
                                }
                            }
                            
                            // 💡 Logic: Always automatically set the main address to the Aadhaar address
                            if (isset($data['address']) && is_array($data['address'])) {
                                $addr = $data['address'];
                                $parts = [];
                                if (!empty($addr['house'])) $parts[] = $addr['house'];
                                if (!empty($addr['street'])) $parts[] = $addr['street'];
                                if (!empty($addr['loc'])) $parts[] = $addr['loc'];
                                if (!empty($addr['dist'])) $parts[] = $addr['dist'];
                                if (!empty($addr['state'])) $parts[] = $addr['state'];
                                if (!empty($addr['pc'])) $parts[] = $addr['pc'];
                                
                                $formattedAddress = implode(', ', $parts);
                                if (!empty($formattedAddress)) {
                                    $user->address = $formattedAddress;
                                }
                            }
                            
                            // 💡 Logic: Aadhaar name is always the pure individual's full name
                            if (!empty($data['name'])) {
                                $user->name = $data['name'];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to fetch Aadhaar Status during callback: ' . $e->getMessage());
                    }
                } else {
                    // Fallback to minimal info extraction if no unified ID present
                    $user->is_aadhaar_verified = true;
                    if ($request->has('name') || $request->has('fullName')) {
                        $user->name = $request->input('name', $request->input('fullName', $user->name));
                    }
                }
                
                $user->save();
            }
            
            return redirect()->route('profile')->with('success', 'Aadhaar KYC Completed Successfully!');
        }

        return redirect()->route('profile')->with('error', 'Aadhaar KYC failed or was cancelled.');
    }
}
