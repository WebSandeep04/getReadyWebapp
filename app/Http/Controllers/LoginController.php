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
        $registerUrl = route('register');

        if ($previousUrl === $loginUrl || $previousUrl === $registerUrl) {
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

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this mobile number.'
                ], 404);
            }

            // Generate 6-digit OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store OTP in cache for 10 minutes
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

        // OTP verified, find user and login
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.'
                ], 404);
            }
            return back()->withErrors([
                'phone' => 'User not found.',
            ])->withInput();
        }

        // Clear OTP from cache
        Cache::forget($otpKey);

        // Login user
        Auth::login($user, $request->filled('remember'));
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

    private function isSafeRedirect(string $redirect): bool
    {
        return Str::startsWith($redirect, ['/']) || Str::startsWith($redirect, url('/'));
    }
} 