<!-- Header -->
<header>
  <nav class="top-nav d-flex align-items-center px-4">
    <!-- Logo -->
    <a href="{{ url('/') }}" class="logo-wrapper">
      @if(frontend_setting('site_logo'))
        <img src="{{ asset(frontend_setting('site_logo')) }}" alt="Logo" class="main-logo">
      @else
        <span class="logo-text">GET READY</span>
      @endif
    </a>

    <!-- Categories (User Types) -->
    <div class="nav-categories d-none d-lg-flex ml-4">
      <a href="{{ url('/clothes?genders[]=Men') }}" class="nav-link">MEN</a>
      <a href="{{ url('/clothes?genders[]=Women') }}" class="nav-link">WOMEN</a>
      <a href="{{ url('/clothes?genders[]=Boy') }}" class="nav-link">BOY</a>
      <a href="{{ url('/clothes?genders[]=Girl') }}" class="nav-link">GIRL</a>
    </div>

    <!-- Search Bar (Centered & Functional) -->
    <div class="search-container flex-grow-1 mx-5">
      <div class="search-wrapper">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Search for outfits, brands and more" class="search-input" id="main-search-input">
      </div>
      <!-- AJAX Search Results Dropdown -->
      <div class="search-results-dropdown" id="search-results-dropdown">
        <div class="search-results-list custom-scrollbar" id="search-results-list">
          <!-- Results will be injected here -->
        </div>
        <a href="#" class="search-all-btn" id="see-all-results-btn">
          See All Results <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
    <!-- Right Actions (Mobile Notification & Burger) -->
    <div class="header-actions-mobile d-lg-none">
      @if(Auth::check())
      <!-- Notifications -->
      <div class="dropdown">
        <a href="#" class="action-link" data-toggle="dropdown" id="notification-toggle-mobile">
          <div class="position-relative">
            <i class="bi bi-bell"></i>
            @if(Auth::user()->unreadNotificationsCount() > 0)
              <span class="action-badge-mobile">{{ Auth::user()->unreadNotificationsCount() }}</span>
            @endif
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 notification-dropdown-custom">
          <div class="dropdown-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Notifications</h6>
            <button class="btn btn-sm btn-link text-warning" id="mark-all-read-mobile">Mark All Read</button>
          </div>
          <div class="dropdown-divider"></div>
          <div id="notifications-list-mobile">
             <!-- Dynamically filled -->
          </div>
        </div>
      </div>

      <!-- Rejected Items (Quick Access) -->
      <div class="dropdown ml-2">
        <a href="{{ route('rejections.index') }}" class="action-link">
          <div class="position-relative">
            <i class="bi bi-x-circle"></i>
            @php
                $rejectedCount = Auth::user()->clothes()
                        ->where(function($query) {
                            $query->where('is_approved', -1)
                                  ->orWhere(function($q) {
                                      $q->where('is_approved', null)
                                        ->where('resubmission_count', '>', 0);
                                  });
                        })
                        ->count();
            @endphp
            @if($rejectedCount > 0)
              <span class="action-badge-mobile" style="background: #ff0000 !important;">{{ $rejectedCount }}</span>
            @endif
          </div>
        </a>
      </div>

      <!-- Burger Menu (Profile) -->
      <div class="dropdown ml-2">
        <a href="#" class="action-link" data-toggle="dropdown">
          <i class="bi bi-list"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 profile-dropdown-custom" style="top: 100%; margin-top: 10px !important;">
          <div class="profile-dropdown-header">
            <h6>Account</h6>
            <div class="user-name">{{ Auth::user()->name }}</div>
          </div>
          <a class="dropdown-item" href="{{ route('listed.clothes') }}"><i class="bi bi-list-stars"></i> My Listings</a>
          <a class="dropdown-item" href="{{ route('orders.sales') }}"><i class="bi bi-graph-up-arrow"></i> Sales Dashboard</a>
          <a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-bag"></i> My Orders</a>
          <a class="dropdown-item" href="{{ route('invoices.index') }}"><i class="bi bi-receipt"></i> My Invoices</a>
          <a class="dropdown-item" href="{{ route('transactions.index') }}"><i class="bi bi-cash-stack"></i> My Transactions</a>
          <a class="dropdown-item" href="{{ route('rejections.index') }}"><i class="bi bi-x-circle"></i> Rejected Items</a>
          <a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-gear"></i> Profile Settings</a>
          <div class="dropdown-divider"></div>
          <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="dropdown-item logout-btn border-0 bg-transparent w-100 text-left">
              <i class="bi bi-box-arrow-right"></i> Logout
            </button>
          </form>
        </div>
      </div>
      @else
        <a href="{{ route('login') }}" class="action-link">
          <i class="bi bi-bell"></i>
        </a>
        <a href="{{ route('login') }}" class="action-link ml-2">
          <i class="bi bi-list"></i>
        </a>
      @endif
    </div>
    <!-- Right Actions (Desktop Only) -->
    <div class="header-actions d-none d-lg-flex align-items-center">
      
      @if(Auth::check())
      <!-- Notifications -->
      <div class="dropdown header-action-item">
        <a href="#" class="action-link" data-toggle="dropdown" id="notification-toggle">
          <div class="position-relative">
            <i class="bi bi-bell"></i>
            @if(Auth::user()->unreadNotificationsCount() > 0)
              <span class="action-badge">{{ Auth::user()->unreadNotificationsCount() }}</span>
            @endif
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 notification-dropdown-custom" id="notification-dropdown">
          <div class="dropdown-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Notifications</h6>
            <button class="btn btn-sm btn-link text-warning" id="mark-all-read">Mark All Read</button>
          </div>
          <div class="dropdown-divider"></div>
          <div id="notifications-list">
             <!-- Dynamically filled -->
          </div>
        </div>
      </div>

      <!-- Bag/Cart -->
      <div class="header-action-item dropdown cart-dropdown-wrapper">
        <a href="{{ route('cart') }}" class="action-link" id="cartDropdown">
          <div class="position-relative">
            <i class="bi bi-bag"></i>
            @php
              $cartCount = Auth::user()->cartItems()->count();
            @endphp
            <span class="action-badge" id="cart-count" style="{{ $cartCount > 0 ? '' : 'display: none;' }}">
              {{ $cartCount }}
            </span>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 mini-cart-dropdown p-0" aria-labelledby="cartDropdown">
          <div class="mini-cart-header p-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Shopping Bag</h6>
            <span class="badge bg-light text-dark rounded-pill py-1 px-2" id="mini-cart-count-badge">{{ $cartCount }} Items</span>
          </div>
          <div class="mini-cart-items custom-scrollbar" id="mini-cart-items-container" style="max-height: 350px; overflow-y: auto;">
             <!-- Dynamically filled via JS -->
             <div class="text-center py-4 text-muted small">Loading items...</div>
          </div>
          <div class="mini-cart-footer p-3 bg-light rounded-bottom">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-muted small fw-bold">SUBTOTAL</span>
              <span class="fw-bold text-dark h6 mb-0" id="mini-cart-subtotal">₹0</span>
            </div>
            <div class="row g-2">
              <div class="col-12">
                <a href="{{ route('cart') }}" class="btn-view-bag-premium w-100">VIEW BAG</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Rejections/Rejected Items -->
      <div class="header-action-item dropdown cart-dropdown-wrapper">
        <a href="{{ route('rejections.index') }}" class="action-link" id="rejectionDropdown">
          <div class="position-relative">
            <i class="bi bi-x-circle"></i>
            @php
                $rejectedCount = Auth::user()->clothes()
                        ->where(function($query) {
                            $query->where('is_approved', -1)
                                  ->orWhere(function($q) {
                                      $q->where('is_approved', null)
                                        ->where('resubmission_count', '>', 0);
                                  });
                        })
                        ->count();
            @endphp
            <span class="action-badge" id="rejection-count" style="{{ $rejectedCount > 0 ? '' : 'display: none;' }}">
              {{ $rejectedCount }}
            </span>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 mini-cart-dropdown p-0" aria-labelledby="rejectionDropdown">
          <div class="mini-cart-header p-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Rejected Items</h6>
            <span class="badge bg-light text-dark rounded-pill py-1 px-2" id="mini-rejection-count-badge">{{ $rejectedCount }} Items</span>
          </div>
          <div class="mini-cart-items custom-scrollbar" id="mini-rejection-items-container" style="max-height: 350px; overflow-y: auto;">
             <!-- Dynamically filled via JS -->
             <div class="text-center py-4 text-muted small">Loading items...</div>
          </div>
          <div class="mini-cart-footer p-3 bg-light rounded-bottom">
            <div class="row g-2">
              <div class="col-12">
                <a href="{{ route('rejections.index') }}" class="btn-view-bag-premium w-100">REJECTIONS</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif

      <!-- Sell Button -->
      <a href="{{ Auth::check() ? route('sell') : route('login', ['redirect' => route('sell')]) }}" class="btn-sell-premium mx-3">SELL</a>

      <!-- Profile -->
      <div class="dropdown header-action-item">
        <a href="#" class="action-link" id="profileDropdown" data-toggle="dropdown">
          @if(Auth::check() && Auth::user()->profile_image)
            <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" class="header-profile-img-mini" alt="User">
          @else
            <i class="bi bi-person-circle"></i>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 profile-dropdown-custom">
          @if(Auth::check())
            <div class="profile-dropdown-header">
              <h6>Account</h6>
              <div class="user-name">{{ Auth::user()->name }}</div>
            </div>
            <a class="dropdown-item" href="{{ route('listed.clothes') }}"><i class="bi bi-list-stars"></i> My Listings</a>
            <a class="dropdown-item" href="{{ route('orders.sales') }}"><i class="bi bi-graph-up-arrow"></i> Sales Dashboard</a>
            <a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-bag-heart"></i> My Orders</a>
            <a class="dropdown-item" href="{{ route('invoices.index') }}"><i class="bi bi-receipt"></i> My Invoices</a>
            <a class="dropdown-item" href="{{ route('transactions.index') }}"><i class="bi bi-cash-stack"></i> My Transactions</a>
            <a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person-gear"></i> Profile Settings</a>
            <div class="dropdown-divider"></div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
              @csrf
              <button type="submit" class="dropdown-item logout-btn border-0 bg-transparent w-100 text-left">
                <i class="bi bi-box-arrow-right"></i> Logout
              </button>
            </form>
          @else
            <div class="p-4 text-center">
              <h6 class="fw-bold mb-1">Welcome</h6>
              <p class="small text-muted mb-3">To access account and orders</p>
              <a href="{{ route('login') }}" class="btn-sell-premium btn-sm w-100 py-2">LOGIN / SIGNUP</a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </nav>
</header>

<!-- Mobile Bottom Navigation -->
<div class="mobile-bottom-nav d-lg-none">
  <div class="bottom-nav-wrapper d-flex justify-content-between align-items-center">
    <a href="{{ url('/') }}" class="nav-item @if(Request::is('/')) active @endif">
      <div class="icon-wrapper">
        <i class="bi bi-house"></i>
      </div>
    </a>
    
    <a href="{{ route('listed.clothes') }}" class="nav-item @if(Route::is('listed.clothes')) active @endif">
      <div class="icon-wrapper">
        <i class="bi bi-list-stars"></i>
      </div>
    </a>

    <a href="{{ route('cart') }}" class="nav-item @if(Route::is('cart')) active @endif">
      <div class="icon-wrapper">
        <i class="bi bi-bag"></i>
        <span class="nav-badge" id="cart-count-mobile" style="{{ Auth::check() && Auth::user()->cartItems()->count() > 0 ? '' : 'display: none;' }}">
          {{ Auth::check() ? Auth::user()->cartItems()->count() : 0 }}
        </span>
      </div>
    </a>

    <a href="{{ route('sell') }}" class="nav-item sell-center">
      <div class="sell-icon-inner">
        <i class="bi bi-plus-lg"></i>
      </div>
    </a>

    <a href="{{ route('orders.sales') }}" class="nav-item @if(Route::is('orders.sales')) active @endif">
      <div class="icon-wrapper">
        <i class="bi bi-graph-up-arrow icon-small-adjust"></i>
      </div>
    </a>

    <a href="{{ route('orders.index') }}" class="nav-item @if(Route::is('orders.index')) active @endif">
      <div class="icon-wrapper">
        <i class="bi bi-bag-check"></i>
      </div>
    </a>

    <a href="{{ route('profile') }}" class="nav-item @if(Route::is('profile')) active @endif">
      <div class="icon-wrapper">
        @if(Auth::check() && Auth::user()->profile_image)
          <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" class="nav-profile-img" alt="User">
        @else
          <i class="bi bi-person-circle"></i>
        @endif
      </div>
    </a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;

    const prefix = "Search for ";
    const items = [
        "Premium Sherwani...",
        "Designer Lehenga...",
        "Party Wear...",
        "Wedding Suits...",
        "Luxury Outfits...",
        "Blazers..."
    ];
    
    let i = 0;
    let j = 0;
    let currentText = "";
    let isDeleting = false;
    let speed = 100;

    function typeEffect() {
        let currentItem = items[i];
        
        if (isDeleting) {
            currentText = currentItem.substring(0, j - 1);
            j--;
            speed = 50;
        } else {
            currentText = currentItem.substring(0, j + 1);
            j++;
            speed = 100;
        }

        searchInput.setAttribute('placeholder', prefix + currentText);

        if (!isDeleting && j === currentItem.length) {
            isDeleting = true;
            speed = 1500; // Wait before deleting
        } else if (isDeleting && i < items.length && isDeleting && j === 0) {
            isDeleting = false;
            i = (i + 1) % items.length;
            speed = 500; // Wait before typing next
        }

        setTimeout(typeEffect, speed);
    }

    typeEffect();

    // AJAX Search Implementation
    const searchInputEl = document.getElementById('main-search-input');
    const dropdownEl = document.getElementById('search-results-dropdown');
    const resultsListEl = document.getElementById('search-results-list');
    const seeAllBtn = document.getElementById('see-all-results-btn');
    let searchTimeout = null;

    searchInputEl.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            dropdownEl.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`/ajax-search?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsListEl.innerHTML = '';
                    
                    const hasCategories = data.categories && data.categories.length > 0;
                    const hasProducts = data.products && data.products.length > 0;

                    if (hasCategories || hasProducts) {
                        // Render Categories
                        if (hasCategories) {
                            resultsListEl.insertAdjacentHTML('beforeend', '<div class="search-section-title">Suggested Categories</div>');
                            let catHtml = '<div class="search-categories-wrapper">';
                            data.categories.forEach(cat => {
                                catHtml += `
                                    <a href="${cat.url}" class="search-category-tag">
                                        <i class="bi bi-tag"></i> ${cat.name}
                                    </a>
                                `;
                            });
                            catHtml += '</div>';
                            resultsListEl.insertAdjacentHTML('beforeend', catHtml);
                        }

                        // Render Products
                        if (hasProducts) {
                            resultsListEl.insertAdjacentHTML('beforeend', '<div class="search-section-title">Matching Outfits</div>');
                            data.products.forEach(item => {
                                const resultItem = `
                                    <a href="${item.url}" class="search-result-item">
                                        <img src="${item.image}" class="search-result-img" alt="${item.title}">
                                        <div class="search-result-info">
                                            <span class="search-result-title">${item.title}</span>
                                            <span class="search-result-meta">${item.brand} | ${item.category}</span>
                                        </div>
                                        <div class="search-result-price">₹${item.price}</div>
                                    </a>
                                `;
                                resultsListEl.insertAdjacentHTML('beforeend', resultItem);
                            });
                        }
                        
                        seeAllBtn.href = `/clothes?search=${encodeURIComponent(query)}`;
                        seeAllBtn.style.display = 'flex';
                        dropdownEl.style.display = 'block';
                    } else {
                        resultsListEl.innerHTML = `
                            <div class="no-results">
                                <i class="bi bi-search"></i>
                                No results found for "${query}"
                            </div>
                        `;
                        seeAllBtn.style.display = 'none';
                        dropdownEl.style.display = 'block';
                    }
                })
                .catch(err => console.error('Search error:', err));
        }, 300);
    });

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        if (!searchInputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
            dropdownEl.style.display = 'none';
        }
    });

    // Prevent search form submission on enter if we want to handle it
    searchInputEl.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            window.location.href = `/clothes?search=${encodeURIComponent(this.value.trim())}`;
        }
    });
});
</script>