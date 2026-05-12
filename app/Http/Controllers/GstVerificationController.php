<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ImWalletService;
use Illuminate\Support\Facades\Log;

class GstVerificationController extends Controller
{
    public function __construct(private readonly ImWalletService $imWalletService)
    {
    }

    /**
     * Verify GST Details using IM Wallet API
     */
    public function verifyGst(Request $request)
    {
        $request->validate([
            'gstin' => 'required|string|size:15'
        ]);

        $gstin = $request->gstin;

        try {
            $data = $this->imWalletService->getGstDetails($gstin);

            // Auto Update user profile if user is logged in
            if (auth()->check() && !empty($data['data'])) {
                $user = auth()->user();
                $user->gstin = $gstin;
                $user->gst_number = $gstin;
                $user->is_gst = 1; // Explicitly flag them as a GST business!
                
                $businessData = $this->imWalletService->extractBusinessData($data);
                // Only update state and city if they are currently null/empty
                if (empty($user->state) && !empty($businessData['state'])) {
                    $user->state = $businessData['state'];
                }
                if (empty($user->city) && !empty($businessData['city'])) {
                    $user->city = $businessData['city'];
                }



                
                // Save entire GST details for future reference
                $user->gst_details = $data;

                // Save extra mapped fields
                $user->gst_legal_name = $businessData['legal_name'] ?? null;
                $user->gst_trade_name = $businessData['trade_name'] ?? null;
                $user->gst_constitution_of_business = $businessData['constitution_of_business'] ?? null;
                $user->gst_status = $businessData['status'] ?? null;
                $user->gst_registration_date = $businessData['registration_date'] ?? null;
                $user->gst_principal_address = $businessData['principal_address'] ?? null;
                $user->gst_nature_of_business = $businessData['nature_of_business'] ?? null;
                $user->gst_members = $businessData['members'] ?? null;



                $user->save();
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'businessData' => $businessData ?? $this->imWalletService->extractBusinessData($data),
                'message' => 'GST details fetched successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verifying GSTIN or API is unavailable. ' . $e->getMessage()
            ], 500);
        }
    }
}
