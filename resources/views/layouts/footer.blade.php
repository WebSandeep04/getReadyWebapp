<!-- Footer -->
<footer class="bg-light py-4">
  <div class="container">
    <div class="row">
      <div class="col-md-4 text-center text-md-start">
        <div class="logo font-weight-bold text-warning mb-2">{{ frontend_setting('footer_title', 'GET Ready') }}</div>
        <p class="small text-muted">{{ frontend_setting('footer_description', 'Your trusted partner in fashion rental. Quality, style, and convenience all in one place.') }}</p>
      </div>
      <div class="col-md-4 text-center">
        <h6 class="mb-3">Contact Information</h6>
        <div class="contact small text-muted">
          @if(frontend_setting('footer_email'))
            <p><i class="bi bi-envelope me-2"></i>{{ frontend_setting('footer_email') }}</p>
          @endif
          @if(frontend_setting('footer_phone'))
            <p><i class="bi bi-telephone me-2"></i>{{ frontend_setting('footer_phone') }}</p>
          @endif
          @if(frontend_setting('footer_address'))
            <p><i class="bi bi-geo-alt me-2"></i>{{ frontend_setting('footer_address') }}</p>
          @endif
        </div>
      </div>
      <div class="col-md-4 text-center text-md-end">
        <h6 class="mb-3">Follow Us</h6>
        <div class="social-links">
          @if(frontend_setting('social_facebook'))
            <a href="{{ frontend_setting('social_facebook') }}" class="text-muted mx-2" target="_blank">
              <i class="bi bi-facebook fs-5"></i>
            </a>
          @endif
          @if(frontend_setting('social_instagram'))
            <a href="{{ frontend_setting('social_instagram') }}" class="text-muted mx-2" target="_blank">
              <i class="bi bi-instagram fs-5"></i>
            </a>
          @endif
          @if(frontend_setting('social_twitter'))
            <a href="{{ frontend_setting('social_twitter') }}" class="text-muted mx-2" target="_blank">
              <i class="bi bi-twitter fs-5"></i>
            </a>
          @endif
        </div>
      </div>
    </div>
    <hr class="my-3">
    <div class="row">
      <div class="col-12 text-center">
        <p class="small text-muted mb-0">{{ frontend_setting('footer_copyright', '© 2024 GetReady. All rights reserved.') }}</p>
      </div>
    </div>
  </div>
</footer> 