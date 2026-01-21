<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\Msg91Service;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function __construct(private readonly Msg91Service $msg91Service)
    {
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        $showFilters = false;
        return view('register', compact('showFilters'));
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request)
    {
        // Default to mobile signup logic (since we are removing email signup)
        return $this->handleMobileSignup($request);
    }

    /**
     * Handle mobile signup with OTP
     */
    private function handleMobileSignup(Request $request)
    {
        // Step 1: Send OTP
        if ($request->has('send_otp')) {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^[0-9]{10,15}$/|unique:users,phone',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $phone = $request->phone;

            // Generate 6-digit OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store OTP in cache for 10 minutes
            $otpKey = 'signup_otp_' . $phone;
            Cache::put($otpKey, $otp, now()->addMinutes(10));

            try {
                $this->msg91Service->sendOtp($phone, $otp);
            } catch (\Throwable $exception) {
                \Log::error('Unable to send signup OTP via MSG91', [
                    'phone' => $phone,
                    'error' => $exception->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to send OTP right now. Please try again.',
                ], 500);
            }

            $response = [
                'success' => true,
                'message' => 'OTP sent to your mobile number.',
                'otp' => $otp // Always include OTP in response for development/testing
            ];

            return response()->json($response);
        }

        // Step 2: Verify OTP only (don't create account yet)
        if ($request->has('verify_otp') && !$request->has('city')) {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^[0-9]{10,15}$/|unique:users,phone',
                'otp' => 'required|string|size:6',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $phone = $request->phone;
            $otp = $request->otp;
            $otpKey = 'signup_otp_' . $phone;

            // Verify OTP
            $storedOtp = Cache::get($otpKey);

            if (!$storedOtp || $storedOtp !== $otp) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or expired OTP.'
                    ], 422);
                }
                return back()->withErrors([
                    'otp' => 'Invalid or expired OTP.',
                ])->withInput();
            }

            // OTP verified, store verification token (expires in 15 minutes)
            $verificationToken = Str::random(32);
            $verificationKey = 'signup_verified_' . $phone;
            Cache::put($verificationKey, $verificationToken, now()->addMinutes(15));
            
            // Clear OTP from cache
            Cache::forget($otpKey);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully!',
                    'verification_token' => $verificationToken
                ]);
            }
        }

        // Step 3: Create account with city, age, gender
        if ($request->has('city') || $request->has('age') || $request->has('gender')) {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^[0-9]{10,15}$/|unique:users,phone',
                'verification_token' => 'required|string',
                'city' => 'required|string|max:255',
                'age' => 'required|integer|min:1|max:120',
                'gender' => 'required|in:Boy,Girl,Men,Women',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $phone = $request->phone;
            $verificationKey = 'signup_verified_' . $phone;
            $storedToken = Cache::get($verificationKey);

            // Verify token
            if (!$storedToken || $storedToken !== $request->verification_token) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Verification token invalid or expired. Please start over.'
                    ], 422);
                }
                return back()->withErrors([
                    'verification_token' => 'Verification token invalid or expired.',
                ])->withInput();
            }

            // Check if user already exists (shouldn't happen, but safety check)
            if (User::where('phone', $phone)->exists()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User already exists.'
                    ], 422);
                }
                return back()->withErrors([
                    'phone' => 'User already exists.',
                ])->withInput();
            }

            // Create user account
            $user = User::create([
                'name' => 'User-' . substr($phone, -4),
                'email' => null, 
                'phone' => $phone,
                'address' => null,
                'city' => $request->city,
                'age' => $request->age,
                'gender' => $request->gender,
                'password' => Hash::make(Str::random(16)), // Random password for mobile signup
            ]);

            // Clear verification token from cache
            Cache::forget($verificationKey);

            // Login user
            Auth::login($user);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account created successfully!',
                    'redirect' => url('/')
                ]);
            }

            return redirect('/');
        }

        // Fallback: Old Step 2 behavior (for backward compatibility if needed)
        // This handles the case where OTP verification is submitted without verify_otp flag
        // Only run if we don't have Step 3 data
        if (!$request->has('city') && !$request->has('age') && !$request->has('gender')) {
            $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/|unique:users,phone',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $phone = $request->phone;
        $otp = $request->otp;
        $otpKey = 'signup_otp_' . $phone;

        // Verify OTP
        $storedOtp = Cache::get($otpKey);

        if (!$storedOtp || $storedOtp !== $otp) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP.'
                ], 422);
            }
            return back()->withErrors([
                'otp' => 'Invalid or expired OTP.',
            ])->withInput();
        }

        // OTP verified, store verification token
        $verificationToken = Str::random(32);
        $verificationKey = 'signup_verified_' . $phone;
        Cache::put($verificationKey, $verificationToken, now()->addMinutes(15));
        
        // Clear OTP from cache
        Cache::forget($otpKey);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully!',
                    'verification_token' => $verificationToken
                ]);
            }
        }
    }
} 