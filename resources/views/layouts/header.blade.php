<!-- Header -->
<header>
  <nav class="top-nav d-flex justify-content-between align-items-center px-3 py-2 bg-light">
    <a href="{{ url('/') }}" class="logo font-weight-bold text-warning h4" style="text-decoration: none;">
      @if(frontend_setting('site_logo'))
        <img src="{{ asset(frontend_setting('site_logo')) }}" alt="{{ frontend_setting('site_logo_alt', 'GetReady Logo') }}" style="height: 40px; margin-right: 10px;">
      @endif
      <!-- {{ frontend_setting('footer_title', 'GET Ready') }} -->
    </a>
    <div class="auth-buttons">
      @if(Auth::check())
      @else
        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm mx-1">Login</a>
      @endif
      @if(Auth::check())
      <div class="dropdown d-inline-block">
        <a href="#" class="header-icon-btn position-relative dropdown-toggle" data-toggle="dropdown" title="Notifications" id="notification-toggle">
          <i class="bi bi-bell"></i>
          @if(Auth::user()->unreadNotificationsCount() > 0)
            <span id="notification-badge" class="header-badge">
              {{ Auth::user()->unreadNotificationsCount() }}
            </span>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow border-0" style="width: 320px; max-height: 400px; overflow-y: auto;" id="notification-dropdown">
          <div class="dropdown-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Notifications</h6>
            <button class="btn btn-sm btn-outline-primary" id="mark-all-read">Mark All Read</button>
          </div>
          <div class="dropdown-divider"></div>
          <div id="notifications-list">
            <div class="text-center py-3">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="sr-only">Loading...</span>
              </div>
              <div class="mt-2">Loading notifications...</div>
            </div>
          </div>

        </div>
      </div>
      @endif
      @if(Auth::check())
      <a href="{{ route('cart') }}" class="header-icon-btn position-relative" title="Cart">
        <i class="bi bi-bag"></i>
        @if(Auth::user()->cartItems()->count() > 0)
          <span id="cart-count" class="header-badge">
            {{ Auth::user()->cartItems()->count() }}
          </span>
        @endif
      </a>
      
      <!-- Rejection Management Link -->
      <a href="{{ route('rejections.index') }}" class="header-icon-btn position-relative" title="Rejected Items">
        <i class="bi bi-x-circle"></i>
        @php
            $rejectedCount = Auth::user()->clothes()
                    ->where(function($query) {
                        $query->where('is_approved', 0) // Rejected items
                              ->orWhere(function($q) {
                                  $q->where('is_approved', null)
                                    ->where('resubmission_count', '>', 0); // Re-approval items
                              });
                    })
                    ->count(); // Count both rejected and re-approval items
        @endphp
        @if($rejectedCount > 0)
            <span class="header-badge">
                {{ $rejectedCount }}
            </span>
        @endif
      </a>
      @endif
      @if(!request()->routeIs('sell'))
        @if(Auth::check())
          <a href="{{ route('sell') }}" class="btn btn-sell btn-warning btn-sm mx-1">Sell</a>
        @else
          <a href="{{ route('login', ['redirect' => route('sell')]) }}" class="btn btn-sell btn-warning btn-sm mx-1">Sell</a>
        @endif
      @endif
      @if(Auth::check())
      <div class="dropdown d-inline-block">
        <a href="#" class="header-icon-btn dropdown-toggle" id="profileDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Profile">
          @if(Auth::user()->profile_image)
            <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile" class="header-profile-img">
          @else
            <i class="bi bi-person-circle"></i>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
          <a class="dropdown-item" href="{{ route('listed.clothes') }}">Listed Clothes</a>
          <a class="dropdown-item" href="{{ route('orders.sales') }}">My Sales</a>
          <a class="dropdown-item" href="{{ route('orders.index') }}">My Orders</a>
          <a class="dropdown-item" href="{{ route('profile') }}">Profile</a>
          <div class="dropdown-divider"></div>
          <form action="{{ route('logout') }}" method="POST" class="dropdown-item p-0">
            @csrf
            <button type="submit" class="btn btn-link w-100 text-left px-3 py-2">Logout</button>
          </form>
        </div>
      </div>
      @endif
    </div>
  </nav>

  <!-- @if(isset($showHero) && $showHero)
  <div class="hero text-center text-white py-5">
    <h1>Sell and Purchase <br>Occasional Wears <span class="text-warning">Online</span></h1>
    <button class="btn btn-warning mt-3">Explore Now</button>
  </div>
  @endif -->
</header> 