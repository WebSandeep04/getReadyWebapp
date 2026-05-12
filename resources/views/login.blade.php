@extends('layouts.app-auth')

@section('title', 'Login - Get Ready')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<style>
  .otp-section { display: none; }
  .otp-section.show { display: block; }
  .otp-timer { color: #ffc107; font-size: 14px; margin-top: 5px; }

  /* Sleek Mini Footer for Login Page */
  .main-footer {
    background: transparent !important;
    position: fixed !important;
    bottom: 0 !important;
    width: 100% !important;
    z-index: 10 !important;
    padding: 15px 0 !important;
    border: none !important;
  }
  .main-footer .row:first-child { display: none !important; } /* Hide heavy info sections */
  .main-footer .row:last-child { display: flex !important; align-items: center !important; justify-content: space-between !important; }
  .main-footer hr { display: none !important; }
  .main-footer .text-muted { color: rgba(255, 255, 255, 0.9) !important; font-size: 13px !important; }
  
  /* Adding social icons to the same line */
  .footer-bottom-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 0 50px;
  }
  .social-icons-mini a { color: white !important; margin-left: 15px; font-size: 18px; transition: 0.3s; }
  .social-icons-mini a:hover { color: #FFA500 !important; }

  html, body, main, .flex-grow-1, footer, .bg-light, .container, .row {
    background: transparent !important;
    background-color: transparent !important;
  }
  html, body {
    height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    background-image: url("{{ asset('images/login.jpg') }}") !important;
    background-size: cover !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    background-attachment: fixed !important;
    overflow-x: hidden !important;
  }

  /* Direct Styles to bypass CSS loading issues */
  @media (min-width: 992px) {
    .login-page {
      height: 100vh !important;
      width: 100% !important;
      background: transparent !important;
      position: relative !important;
      display: flex !important;
      justify-content: flex-end !important;
      align-items: center !important;
      z-index: 2 !important;
    }
    .overlay {
      position: absolute !important;
      top: 0 !important; left: 0 !important;
      height: 100% !important; width: 100% !important;
       z-index: 1 !important;
    }
    .login-overlay {
      position: relative !important;
      z-index: 2 !important;
      margin-right: 5% !important;
      width: 100% !important;
      max-width: 450px !important;
    }
    .login-form {
      background: rgba(0, 0, 0, 0.2) !important;
      backdrop-filter: blur(25px) !important;
      -webkit-backdrop-filter: blur(25px) !important;
      border: 1px solid rgba(255, 255, 255, 0.15) !important;
      border-radius: 24px !important;
      padding: 45px !important;
      box-shadow: 0 40px 80px rgba(0, 0, 0, 0.4) !important;
      color: white !important;
      transition: all 0.3s ease;
    }
  }
  .login-form:hover {
    border-color: rgba(255, 165, 0, 0.3) !important;
  }
  .login-title { font-family: 'Outfit', sans-serif !important; font-weight: 700 !important; color: #FFA500 !important; }
  .btn-login {
    background: linear-gradient(135deg, #FFA500 0%, #FF7F50 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 12px !important;
    font-weight: 600 !important;
    box-shadow: 0 10px 20px rgba(255, 127, 80, 0.4) !important;
  }
  .form-control::placeholder {
    color: rgba(255, 255, 255, 0.8) !important;
  }
  .form-control {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    color: white !important;
    border-radius: 10px !important;
  }
  
</style>
@endsection

@section('content')
<div class="login-page">
  <div class="overlay"></div>
  
  <!-- Mobile Center Logo -->
  <div class="mobile-login-logo d-lg-none">
    @if(frontend_setting('site_logo'))
      <img src="{{ asset(frontend_setting('site_logo')) }}" alt="Logo" class="img-fluid">
    @else
      <span class="logo-text-gold">GET READY</span>
    @endif
  </div>
  <div class="login-overlay">
    <div class="login-form">
      <div class="mb-4">
        <h2 class="login-title mb-1" style="text-shadow: 0 2px 10px rgba(0,0,0,0.3);">Welcome Back</h2>
        <p class="text-white" style="opacity: 0.9;">Please login to your account</p>
      </div>

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
            <i class="bi bi-shield-check icon2"></i>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set background
        document.body.style.backgroundImage = "url('{{ asset('images/login.jpg') }}')";
        document.body.style.backgroundSize = "cover";
        document.body.style.backgroundPosition = "center";
        document.body.style.backgroundAttachment = "fixed";
        document.body.style.backgroundColor = "transparent";

        // Rearrange footer for professional look
        const footerContainer = document.querySelector('footer .container');
        if (footerContainer) {
            const isMobile = window.innerWidth < 768;
            if (isMobile) {
                footerContainer.innerHTML = `
                    <div class="row">
                        <div style="color:#000 !important; font-weight:bold" class="col-12 d-flex flex-column justify-content-center align-items-center">
                            <div class="mb-3">
                                <span class="small">© 2026 All rights reserved.</span>
                            </div>
                            <div class="social-icons-mini">
                                <a href="#" class="mx-2" style="color:#000 !important;"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="mx-2" style="color:#000 !important;"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="mx-2" style="color:#000 !important;"><i class="bi bi-twitter-x"></i></a>
                                <a href="#" class="mx-2" style="color:#000 !important;"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>`;
            } else {
                footerContainer.innerHTML = `
                    <div class="row">
                        <div style="color:#000 !important; font-weight:bold" class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <div class="mb-2 mb-md-0">
                                <span class="text-warning font-weight-bold mr-3" style="font-size: 1.1rem; letter-spacing: 1px;">GET READY</span>
                                <span class="small ">© 2026 All rights reserved.</span>
                                <span class="mx-3 ">|</span>
                                <span class="small"><i class="bi bi-envelope-at mr-1"></i> info@getready.com</span>
                                <span class="mx-2 ">|</span>
                                <span class="small"><i class="bi bi-telephone mr-1"></i> +1 (555) 123-4567</span>
                            </div>
                            <div class="social-icons-mini" >
                                <a href="#" class="mx-2 " style="color:#000 !important; font-weight:bold"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="mx-2 " style="color:#000 !important; font-weight:bold"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="mx-2 " style="color:#000 !important; font-weight:bold"><i class="bi bi-twitter-x"></i></a>
                                <a href="#" class="mx-2 " style="color:#000 !important; font-weight:bold"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>`;
            }
        }
    });
</script>
@endsection
