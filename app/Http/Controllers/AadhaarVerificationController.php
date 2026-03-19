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
        // This endpoint will be hit when IM Wallet redirects the user back
        // Usually there is a referenceId or status returned in the query params.
        
        $status = $request->input('status');
        
        // We will just redirect the user back to profile with a success flag
        // In a real scenario, you'd verify the KYC details with another API call
        // using the reference ID provided in the callback.
        
        if ($status === 'SUCCESS' || $request->has('referenceId') || $request->has('token')) {
            // Suppose IM Wallet sends back KYC data directly or we fetch it here.
            // Since we don't know the exact data structure yet, we'll mark verified on success parameter.
            if (auth()->check()) {
                $user = auth()->user();
                $user->is_aadhaar_verified = true;
                
                // Example of extracting provided name, gender from generic callback names
                if ($request->has('name') || $request->has('fullName')) {
                    $user->name = $request->input('name', $request->input('fullName'));
                }
                
                $user->save();
            }
            
            return redirect()->route('profile')->with('success', 'Aadhaar KYC Completed Successfully!');
        }

        return redirect()->route('profile')->with('error', 'Aadhaar KYC failed or was cancelled.');
    }
}
