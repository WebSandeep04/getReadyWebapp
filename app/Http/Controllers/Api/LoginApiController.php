<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Services\Msg91Service;
use App\Services\ImWalletService;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class LoginApiController extends Controller
{
    public function __construct(
        private readonly Msg91Service $msg91Service,
        private readonly ImWalletService $imWalletService
    ) {
    }

    /**
     * Step 1: Send OTP to Mobile Number
     */
    public function sendOtp(Request $request)
    {
        // Remove non-numeric characters if needed
        $request->merge([
            'phone' => preg_replace('/[^0-9]/', '', $request->phone)
        ]);

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = $request->phone;
        
        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in cache for 10 minutes
        $otpKey = 'login_otp_' . $phone;
        Cache::put($otpKey, $otp, now()->addMinutes(10));

        try {
            $this->msg91Service->sendOtp($phone, $otp);
        } catch (Throwable $exception) {
            Log::error('Unable to send login API OTP via MSG91', [
                'phone' => $phone,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send OTP right now. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to your mobile number.',
            // Remove 'otp' from response in production for security
            'otp' => config('app.env') !== 'production' ? $otp : null 
        ]);
    }

    /**
     * Step 2: Verify OTP and Login or Prompt Registration
     */
    public function verifyOtp(Request $request)
    {
        $request->merge([
            'phone' => preg_replace('/[^0-9]/', '', $request->phone)
        ]);

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = $request->phone;
        $otp = $request->otp;
        $otpKey = 'login_otp_' . $phone;

        // Verify OTP
        $storedOtp = Cache::get($otpKey);

        if (!$storedOtp || $storedOtp !== $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
                'errors' => ['otp' => ['Invalid or expired OTP.']]
            ], 422);
        }

        // OTP verified, clear from cache
        Cache::forget($otpKey);

        $user = User::where('phone', $phone)->first();

        // If user doesn't exist, they need to register
        if (!$user) {
            $verificationToken = Str::random(32);
            $verificationKey = 'signup_verified_' . $phone;
            Cache::put($verificationKey, $verificationToken, now()->addMinutes(15));
            
            return response()->json([
                'success' => true,
                'is_new_user' => true,
                'message' => 'OTP verified. Please complete your profile.',
                'data' => [
                    'verification_token' => $verificationToken,
                    'phone' => $phone
                ]
            ]);
        }

        // User exists: Process Login

        // Check for first-time login
        if (is_null($user->last_login_at)) {
            $hasWelcomeNotification = Notification::where('user_id', $user->id)
                ->where('title', 'Welcome to GetReady!')
                ->exists();

            if (!$hasWelcomeNotification) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Welcome to GetReady!',
                    'message' => 'We are excited to have you on board. Start your journey by listing your first item or exploring our collection.',
                    'type' => 'success',
                    'icon' => 'bi-emoji-smile',
                    'read' => false
                ]);
            }
        }

        // Update last login timestamp
        $user->last_login_at = now();
        $user->save();

        // Generate API Token (Sanctum)
        $token = $user->createToken('mobile_app_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'is_new_user' => false,
            'message' => 'Login successful!',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ]);
    }

    /**
     * Step 3: Complete Registration for new users
     */
    public function completeRegistration(Request $request)
    {
        $request->merge([
            'phone' => preg_replace('/[^0-9]/', '', $request->phone)
        ]);

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
            'verification_token' => 'required|string',
            'age' => 'required|integer|min:1|max:120',
            'gender' => 'required|in:Boy,Girl,Men,Women',
            'is_gst' => 'required|boolean',
            'gstin' => 'required_if:is_gst,1|nullable|string|max:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = $request->phone;
        $verificationKey = 'signup_verified_' . $phone;
        $storedToken = Cache::get($verificationKey);

        // Verify token
        if (!$storedToken || $storedToken !== $request->verification_token) {
            return response()->json([
                'success' => false,
                'message' => 'Verification token invalid or expired. Please start over.'
            ], 422);
        }

        // Check if user already exists
        if (User::where('phone', $phone)->exists()) {
             return response()->json([
                'success' => false,
                'message' => 'User already exists.'
            ], 422);
        }

        // Define default user data
        $userData = [
            'name' => 'User-' . substr($phone, -4),
            'email' => null, 
            'phone' => $phone,
            'address' => null,
            'age' => $request->age,
            'gender' => $request->gender,
            'is_gst' => $request->is_gst,
            'gstin' => $request->is_gst == 1 ? $request->gstin : null,
            'gst_number' => $request->is_gst == 1 ? $request->gstin : null,
            'password' => Hash::make(Str::random(16)),
        ];

        // Fetch GST Details if GST is enabled
        if ($request->is_gst == 1 && !empty($request->gstin)) {
            try {
                $gstDetails = $this->imWalletService->getGstDetails($request->gstin);

                if (empty($gstDetails['data'])) {
                    throw new Exception('Invalid GST Number or no data returned.');
                }

                $userData['gst_details'] = $gstDetails;
                $businessData = $this->imWalletService->extractBusinessData($gstDetails);

                if (empty($userData['state']) && !empty($businessData['state'])) {
                    $userData['state'] = $businessData['state'];
                }
                if (empty($userData['city']) && !empty($businessData['city'])) {
                    $userData['city'] = $businessData['city'];
                }
                
                $userData['gst_legal_name'] = $businessData['legal_name'] ?? null;
                $userData['gst_trade_name'] = $businessData['trade_name'] ?? null;
                $userData['gst_constitution_of_business'] = $businessData['constitution_of_business'] ?? null;
                $userData['gst_status'] = $businessData['status'] ?? null;
                $userData['gst_registration_date'] = $businessData['registration_date'] ?? null;
                $userData['gst_principal_address'] = $businessData['principal_address'] ?? null;
                $userData['gst_nature_of_business'] = $businessData['nature_of_business'] ?? null;
                $userData['gst_members'] = $businessData['members'] ?? null;

            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid GST Number or unable to verify right now. Please try again.',
                    'errors' => [
                        'gstin' => ['Invalid GST Number or unable to verify right now. Please try again.']
                    ]
                ], 422);
            }
        }

        // Create user account
        $user = User::create($userData);

        // Create welcome notification
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Welcome to GetReady!',
            'message' => 'We are excited to have you on board. Start your journey by listing your first item or exploring our collection.',
            'type' => 'success',
            'icon' => 'bi-emoji-smile',
            'read' => false
        ]);

        // Clear verification token
        Cache::forget($verificationKey);

        // Generate API Token (Sanctum)
        $token = $user->createToken('mobile_app_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully!',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ]);
    }

    /**
     * Log the user out (Revoke token)
     */
    public function logout(Request $request)
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.'
        ]);
    }
}
