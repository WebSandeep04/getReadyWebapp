@extends('layouts.app-auth')

@section('title', 'Sign Up - Get Ready')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
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
<div class="signup-page">
  <div class="overlay"></div>
<div class="container h-100">
  <div class="row h-100 align-items-center">
    <div class="col-lg-6"></div> <!-- Left side empty -->
    <div class="col-lg-6 d-flex justify-content-center">
      <div class="signup-form text-center">
        <h2 class="text-warning mb-4">SIGN UP</h2>

        <!-- Mobile Signup Form -->
        <form id="mobileSignupForm">
            @csrf
            <input type="hidden" name="signup_type" value="mobile">
            
            <!-- Step 1: Enter Details and Phone -->
            <div id="mobileSignupStep1">
                <div class="form-group mb-3 position-relative">
                    <input type="tel" name="phone" id="signupPhone" class="form-control" placeholder="Enter your Mobile No" pattern="[0-9]{10,15}" required>
                    <i class="bi bi-telephone icon"></i>
                    <div id="signupPhoneError" class="text-danger small" style="display:none;"></div>
                </div>
                <button type="button" id="sendSignupOtpBtn" class="btn btn-warning w-100 fw-bold">Send OTP</button>
            </div>

            <!-- Step 2: Enter OTP -->
            <div id="mobileSignupStep2" class="otp-section">
                <div class="form-group mb-3 position-relative">
                    <input type="text" name="otp" id="signupOtp" class="form-control" placeholder="Enter 6-digit OTP" maxlength="6" pattern="[0-9]{6}" required>
                    <i class="bi bi-shield-check icon"></i>
                    <div id="signupOtpError" class="text-danger small" style="display:none;"></div>
                    <div id="signupOtpTimer" class="otp-timer"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button type="button" id="resendSignupOtpBtn" class="btn btn-link text-warning p-0" style="display:none;">Resend OTP</button>
                </div>
                <button type="button" id="verifyOtpBtn" class="btn btn-warning w-100 fw-bold">Verify OTP</button>
            </div>

            <!-- Step 3: Enter City, Age, Gender -->
            <div id="mobileSignupStep3" class="otp-section">
                <input type="hidden" name="verification_token" id="verificationToken">
                <div class="form-group mb-3 position-relative">
                    <input type="text" name="city" id="signupCity" class="form-control" placeholder="Enter your City" required>
                    <i class="bi bi-geo-alt icon"></i>
                    <div id="signupCityError" class="text-danger small" style="display:none;"></div>
                </div>
                <div class="form-group mb-3 position-relative">
                    <input type="number" name="age" id="signupAge" class="form-control" placeholder="Enter your Age" min="1" max="120" required>
                    <i class="bi bi-calendar icon"></i>
                    <div id="signupAgeError" class="text-danger small" style="display:none;"></div>
                </div>
                <div class="form-group mb-3 position-relative">
                    <select name="gender" id="signupGender" class="form-control" required>
                        <option value="">Select User Type</option>
                        <option value="Boy">Boy</option>
                        <option value="Girl">Girl</option>
                        <option value="Men">Men</option>
                        <option value="Women">Women</option>
                    </select>
                    <i class="bi bi-person icon"></i>
                    <div id="signupGenderError" class="text-danger small" style="display:none;"></div>
                </div>

                <!-- Business Type Selection -->
                <div class="form-group mb-3 position-relative">
                    <select name="is_gst" id="signupIsGst" class="form-control" required onchange="toggleSignupGstField()">
                        <option value="" disabled>Select Business Type</option>
                        <option value="0" selected>Individual / Non-Business</option>
                        <option value="1">Business (GST Available)</option>
                    </select>
                    <i class="bi bi-briefcase icon"></i>
                    <div id="signupIsGstError" class="text-danger small" style="display:none;"></div>
                </div>

                <!-- GSTIN Field (Hidden by default) -->
                <div class="form-group mb-3 position-relative" id="signupGstinContainer" style="display:none;">
                    <input type="text" name="gstin" id="signupGstin" class="form-control" placeholder="Enter GSTIN (15 characters)" maxlength="15">
                    <i class="bi bi-receipt icon"></i>
                    <div id="signupGstinError" class="text-danger small" style="display:none;"></div>
                </div>

                <button type="submit" class="btn btn-warning w-100 fw-bold">Create Account</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

</div>
@endsection

@section('scripts')
<script>
    window.AuthConfig = {
        routes: {
            register: "{{ route('register') }}"
        }
    };

    function toggleSignupGstField() {
        const isGst = document.getElementById('signupIsGst').value === '1';
        const gstContainer = document.getElementById('signupGstinContainer');
        const gstInput = document.getElementById('signupGstin');
        
        if (isGst) {
            gstContainer.style.display = 'block';
            gstInput.setAttribute('required', 'required');
        } else {
            gstContainer.style.display = 'none';
            gstInput.removeAttribute('required');
            gstInput.value = ''; 
        }
    }
</script>
<script src="{{ asset('js/register.js') }}"></script>
@endsection
