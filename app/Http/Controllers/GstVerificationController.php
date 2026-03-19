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
                
                $businessData = $this->imWalletService->extractBusinessData($data);

                if (!empty($businessData['name'])) {
                    $user->name = $businessData['name'];
                }
                if (!empty($businessData['address'])) {
                    $user->address = $businessData['address'];
                }
                
                $user->save();
            }

            return response()->json([
                'success' => true,
                'data' => $data,
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
