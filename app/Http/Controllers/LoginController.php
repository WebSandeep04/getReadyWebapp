<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Services\Msg91Service;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function __construct(private readonly Msg91Service $msg91Service)
    {
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        $showFilters = false;
        $previousUrl = url()->previous();
        $loginUrl = route('login');

        if ($previousUrl === $loginUrl) {
            $previousUrl = url('/');
        }

        $redirectTo = request()->query('redirect', $previousUrl);

        return view('login', compact('showFilters', 'redirectTo'));
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        // Default to mobile login logic (since we are removing email login)
        return $this->handleMobileLogin($request);
    }

    /**
     * Handle mobile login with OTP
     */
    private function handleMobileLogin(Request $request)
    {
        // Step 1: Send OTP
        if ($request->has('send_otp')) {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $phone = $request->phone;
            $user = User::where('phone', $phone)->first();

            // Generate 6-digit OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store OTP in cache for 10 minutes
            // Use same key format as RegisterController so we can interchange if needed, 
            // but sticking to login_otp works if we handle verification here.
            $otpKey = 'login_otp_' . $phone;
            Cache::put($otpKey, $otp, now()->addMinutes(10));

            try {
                $this->msg91Service->sendOtp($phone, $otp);
            } catch (\Throwable $exception) {
                \Log::error('Unable to send login OTP via MSG91', [
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

        // Step 2: Verify OTP and login
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
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
        $otpKey = 'login_otp_' . $phone;

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

        // OTP verified, check if user exists
        $user = User::where('phone', $phone)->first();

        // Clear OTP from cache
        Cache::forget($otpKey);

        if (!$user) {
            // New User: Generate verification token for registration
            $verificationToken = Str::random(32);
            // Use key format expected by RegisterController
            $verificationKey = 'signup_verified_' . $phone;
            Cache::put($verificationKey, $verificationToken, now()->addMinutes(15));
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'is_new_user' => true,
                    'message' => 'OTP verified. Please complete your profile.',
                    'verification_token' => $verificationToken
                ]);
            }
            return back()->with('error', 'Please enable Javascript to complete registration.');
        }

        // User exists: Login
        Auth::login($user, $request->filled('remember'));

        // Check for first-time login (if last_login_at is null) and send welcome notification if not already sent
        if (is_null($user->last_login_at)) {
            // Check if welcome notification already exists (to avoid duplicates if registration already sent it)
            $hasWelcomeNotification = \App\Models\Notification::where('user_id', $user->id)
                ->where('title', 'Welcome to GetReady!')
                ->exists();

            if (!$hasWelcomeNotification) {
                \App\Models\Notification::create([
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

        $request->session()->regenerate();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => $this->determineRedirectPath($request, true)
            ]);
        }

        return redirect()->intended($this->determineRedirectPath($request));
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * Determine safe redirect path after login.
     */
    private function determineRedirectPath(Request $request, bool $forAjax = false): string
    {
        $redirect = $request->input('redirect');

        if ($redirect && $this->isSafeRedirect($redirect)) {
            return $redirect;
        }

        $intended = $request->session()->pull('url.intended');
        if ($intended && $this->isSafeRedirect($intended)) {
            return $intended;
        }

        return $forAjax ? url()->previous() : url('/');
    }

    /**
     * Complete registration for new users.
     */
    public function completeRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
            'verification_token' => 'required|string',
            'city' => 'required|string|max:255',
            'age' => 'required|integer|min:1|max:120',
            'gender' => 'required|in:Boy,Girl,Men,Women',
            'is_gst' => 'required|boolean',
            'gstin' => 'required_if:is_gst,1|nullable|string|max:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = $request->phone;
        // The token key must match what was stored in handleMobileLogin
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

        // Create user account
        $user = User::create([
            'name' => 'User-' . substr($phone, -4),
            'email' => null, 
            'phone' => $phone,
            'address' => null,
            'city' => $request->city,
            'age' => $request->age,
            'gender' => $request->gender,
            'is_gst' => $request->is_gst,
            'gstin' => $request->gstin,
            'gst_number' => $request->gstin,
            'password' => \Illuminate\Support\Facades\Hash::make(Str::random(16)),
        ]);

        // Create welcome notification
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'title' => 'Welcome to GetReady!',
            'message' => 'We are excited to have you on board. Start your journey by listing your first item or exploring our collection.',
            'type' => 'success',
            'icon' => 'bi-emoji-smile',
            'read' => false
        ]);

        // Clear verification token from cache
        Cache::forget($verificationKey);

        // Login user
        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully!',
            'redirect' => url('/')
        ]);
    }

    private function isSafeRedirect(string $redirect): bool
    {
        // Allow redirects to relative paths or the app's own URL
        // Simple check to prevent open redirect vulnerabilities
        return Str::startsWith($redirect, ['/']) || Str::startsWith($redirect, url('/'));
    }
} 