<!-- Professional Black Footer -->
<footer class="main-footer py-5">
  <div class="container">
    <div class="row footer-top">
      <!-- Brand & Info -->
      <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
        <div class="footer-logo mb-4">
          @if(frontend_setting('site_logo'))
            <img src="{{ asset(frontend_setting('site_logo')) }}" alt="Logo" class="footer-main-logo">
          @else
            <span class="logo-text-gold">GET READY</span>
          @endif
        </div>
        <p class="footer-desc mb-4">
          {{ frontend_setting('footer_description', 'Your premier destination for high-end fashion rental. Experience luxury without the commitment.') }}
        </p>
        <div class="social-icons-pro d-flex gap-3">
          @if(frontend_setting('social_facebook'))
            <a href="{{ frontend_setting('social_facebook') }}" target="_blank"><i class="bi bi-facebook"></i></a>
          @endif
          @if(frontend_setting('social_instagram'))
            <a href="{{ frontend_setting('social_instagram') }}" target="_blank"><i class="bi bi-instagram"></i></a>
          @endif
          @if(frontend_setting('social_twitter'))
            <a href="{{ frontend_setting('social_twitter') }}" target="_blank"><i class="bi bi-twitter-x"></i></a>
          @endif
        </div>
      </div>

      <!-- Shop Links -->
      <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
        <h6 class="footer-heading mb-4">SHOP</h6>
        <ul class="footer-links list-unstyled">
          <li><a href="{{ url('/clothes?genders[]=Men') }}">Men</a></li>
          <li><a href="{{ url('/clothes?genders[]=Women') }}">Women</a></li>
          <li><a href="{{ url('/clothes?genders[]=Boy') }}">Boy</a></li>
          <li><a href="{{ url('/clothes?genders[]=Girl') }}">Girl</a></li>
        </ul>
      </div>

      <!-- Useful Links -->
      <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
        <h6 class="footer-heading mb-4">USEFUL LINKS</h6>
        <ul class="footer-links list-unstyled">
          <li><a href="{{ route('about') }}">About Us</a></li>
          <li><a href="{{ route('contact') }}">Contact Us</a></li>
          <li><a href="{{ route('terms') }}">T&C</a></li>
          <li><a href="{{ route('shipping') }}">Shipping & Delivery Policy</a></li>
          <li><a href="{{ route('returns') }}">Cancellation & Returns</a></li>
          <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
        </ul>
      </div>

      <!-- Experience App -->
      <div class="col-lg-4 col-md-6">
        <h6 class="footer-heading mb-4">EXPERIENCE APP ON MOBILE</h6>
        <div class="footer-app-badges d-flex flex-wrap gap-2 mb-4">
          <a href="#"><img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" height="40"></a>
        </div>
        <div class="footer-promise mt-4">
          <div class="d-flex align-items-center mb-4">
            <i class="bi bi-shield-check text-gold fs-2 mr-3"></i>
            <div>
              <h6 class="mb-0 text-white font-weight-bold">100% ORIGINAL</h6>
              <p class="mb-0 text-muted small">guarantee for all products</p>
            </div>
          </div>
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-arrow-repeat text-gold fs-2 mr-3"></i>
            <div>
              <h6 class="mb-0 text-white font-weight-bold">RETURN WITHIN 14 DAYS</h6>
              <p class="mb-0 text-muted small">of receiving your order</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <hr class="footer-divider">

    <!-- Unified Bottom Footer -->
    <div class="footer-bottom-pro d-flex flex-wrap justify-content-between align-items-center">
      <p class="copyright mb-0 text-white">
        © 2026 GetReady. All rights reserved.
      </p>
      <div class="footer-contact-info d-flex align-items-center">
        @if(frontend_setting('footer_email'))
          <span class="text-white mr-4"><i class="bi bi-envelope mr-2"></i>{{ frontend_setting('footer_email') }}</span>
        @endif
        @if(frontend_setting('footer_phone'))
          <span class="text-white"><i class="bi bi-telephone mr-2"></i>{{ frontend_setting('footer_phone') }}</span>
        @endif
      </div>
    </div>
  </div>
</footer>