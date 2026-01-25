@extends('layouts.app-auth')

@section('title', 'Login - Get Ready')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<style>
  .otp-section {
    display: none;
  }
  .otp-section.show {
    display: block;
  }
  .otp-timer {
    color: #ffc107;
    font-size: 14px;
    margin-top: 5px;
  }
</style>
@endsection

@section('content')
<div class="login-page">
  <div class="overlay"></div>
  <div class="login-overlay">
    <div class="login-form">
      <h2 class="login-title">LOGIN</h2>

      <!-- Mobile Login Form -->
      <form id="mobileLoginForm">
        @csrf
        <input type="hidden" name="login_type" value="mobile">
        <input type="hidden" name="redirect" value="{{ old('redirect', $redirectTo ?? request('redirect')) }}">
        
        <!-- Step 1: Enter Phone Number -->
        <div id="mobileLoginStep1">
          <div class="form-group position-relative mb-3">
            <input type="tel" name="phone" id="loginPhone" class="form-control" placeholder="Enter Your Mobile Number" pattern="[0-9]{10,15}" required>
            <i class="bi bi-telephone icon"></i>
            <div id="loginPhoneError" class="text-danger small" style="display:none;"></div>
          </div>
          <button type="button" id="sendLoginOtpBtn" class="btn-login w-100 text-white">Send OTP</button>
        </div>

        <!-- Step 2: Enter OTP -->
        <div id="mobileLoginStep2" class="otp-section">
          <div class="form-group position-relative mb-3">
            <input type="text" name="otp" id="loginOtp" class="form-control" placeholder="Enter 6-digit OTP" maxlength="6" pattern="[0-9]{6}" required>
            <i class="bi bi-shield-check icon"></i>
            <div id="loginOtpError" class="text-danger small" style="display:none;"></div>
            <div id="loginOtpTimer" class="otp-timer"></div>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="rememberMeMobile" name="remember">
              <label class="form-check-label" for="rememberMeMobile">Remember me</label>
            </div>
            <button type="button" id="resendLoginOtpBtn" class="btn btn-link text-warning p-0" style="display:none;">Resend OTP</button>
          </div>
          <button type="submit" class="btn-login w-100 text-white">Verify OTP</button>
        </div>

        <!-- Step 3: Complete Profile (For New Users) -->
        <div id="mobileLoginStep3" style="display:none;">
            <input type="hidden" name="verification_token" id="verificationToken">
            
            <div class="form-group position-relative mb-3">
                <input type="text" name="city" id="city" class="form-control" placeholder="City">
                <i class="bi bi-geo-alt icon"></i>
            </div>
            
            <div class="form-group position-relative mb-3">
                <input type="number" name="age" id="age" class="form-control" placeholder="Age" min="10" max="100">
                <i class="bi bi-calendar icon"></i>
            </div>
            
            <div class="form-group position-relative mb-3">
                <select name="gender" id="gender" class="form-control">
                    <option value="" disabled selected>Select User Type</option>
                    <option value="Men">Men</option>
                    <option value="Women">Women</option>
                    <!-- <option value="Boy">Boy</option>
                    <option value="Girl">Girl</option> -->
                </select>
                <i class="bi bi-person icon"></i>
            </div>
            
            <div class="form-group position-relative mb-3">
                <select name="is_gst" id="is_gst" class="form-control">
                    <option value="0">Individual / Non-Business</option>
                    <option value="1">Business (GST Available)</option>
                </select>
                <i class="bi bi-briefcase icon"></i>
            </div>
            
            <div class="form-group position-relative mb-3" id="gstinGroup" style="display:none;">
                <input type="text" name="gstin" id="gstin" class="form-control" placeholder="Enter GSTIN" maxlength="15">
                <i class="bi bi-receipt icon"></i>
            </div>
            
            <button type="submit" class="btn-login w-100 text-white">Complete Registration</button>
        </div>
      </form>


    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    window.AuthConfig = {
        routes: {
            login: "{{ route('login') }}"
        }
    };
</script>
<script src="{{ asset('js/login.js') }}"></script>
@endsection
