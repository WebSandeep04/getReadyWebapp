@extends('layouts.app')

@section('styles')
<style>
/* Category Slider Styles */
.category-slider-container {
    position: relative;
    padding: 10px 0;
    margin-bottom: 0.5rem;
    overflow: visible;
    width: 100%;
}

.category-slider {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 10px 5px;
    cursor: grab;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none;  /* IE and Edge */
    user-select: none;
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
}

.category-slider::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

.category-slider.dragging {
    cursor: grabbing;
    scroll-behavior: auto;
}

.category-pill {
    flex: 0 0 auto;
    padding: 8px 22px;
    background: #fff;
    border: 1px solid #eaeaec;
    border-radius: 50px;
    color: #282c3f !important;
    font-weight: 700;
    font-size: 13px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    text-decoration: none !important;
}

.category-pill:hover, .category-pill.active-pill {
    background: #FFA500;
    color: #fff !important;
    border-color: #FFA500;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 165, 0, 0.2);
}

.category-pill.active-pill {
    background: linear-gradient(135deg, #FFA500 0%, #FF7F50 100%);
    border: none;
}

/* Slider Arrows */
.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    background: #fff;
    border: 1px solid #eee;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
    color: #282c3f;
}

.slider-arrow:hover {
    background: #FFA500;
    color: #fff;
    border-color: #FFA500;
}

.arrow-left { left: -18px; }
.arrow-right { right: -18px; }

@media (max-width: 1200px) {
    .arrow-left { left: 0; }
    .arrow-right { right: 0; }
}
</style>
@endsection
<!-- before removing storage in public -->
 <!-- before implementing delivery self logic -->
@section('title', frontend_setting('site_title', 'Get Ready - Home'))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link rel="stylesheet" href="{{ asset('css/hero.css') }}">
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
<link rel="stylesheet" href="{{ asset('css/browse.css') }}">
@endpush

@section('content')
<!-- Hero Section Slider -->
<section class="hero-slider swiper">
  <div class="swiper-wrapper">
    <!-- Slide 1 -->
    <div class="swiper-slide" style="background: url('{{ asset('images/1.jpg') }}') center/cover no-repeat;">
    </div>
    <!-- Slide 2 -->
    <div class="swiper-slide" style="background: url('{{ asset('images/2.jpg') }}') center/cover no-repeat;">
    </div>
    <!-- Slide 3 -->
    <div class="swiper-slide" style="background: url('{{ asset('images/3.jpg') }}') center/cover no-repeat;">
    </div>
  </div>
  <!-- Swiper Navigation -->
  <div class="swiper-button-next"></div>
  <div class="swiper-button-prev"></div>
  <!-- Swiper Pagination -->
  <div class="swiper-pagination"></div>
</section>

<!-- Category Section -->
<section class="category-section py-5 bg-white">
  <div class="container">
    <div class="category-wrapper d-flex justify-content-center flex-wrap gap-5">
      <div class="category-item text-center">
        <a href="{{ url('/clothes?genders[]=Men') }}" class="text-decoration-none">
          <div class="category-img-wrapper mb-2">
            <img src="{{ asset('images/cat_men.png') }}" alt="Men" class="category-img shadow-sm" onerror="this.src='https://placehold.co/120x120?text=Men'">
          </div>
          <span class="category-name text-dark font-weight-bold">Men</span>
        </a>
      </div>
      <div class="category-item text-center">
        <a href="{{ url('/clothes?genders[]=Women') }}" class="text-decoration-none">
          <div class="category-img-wrapper mb-2">
            <img src="{{ asset('images/cat_women.png') }}" alt="Women" class="category-img shadow-sm" onerror="this.src='https://placehold.co/120x120?text=Women'">
          </div>
          <span class="category-name text-dark font-weight-bold">Women</span>
        </a>
      </div>
      <div class="category-item text-center">
        <a href="{{ url('/clothes?genders[]=Boy') }}" class="text-decoration-none">
          <div class="category-img-wrapper mb-2">
            <img src="{{ asset('images/cat_boy.png') }}" alt="Boy" class="category-img shadow-sm" onerror="this.src='https://placehold.co/120x120?text=Boy'">
          </div>
          <span class="category-name text-dark font-weight-bold">Boy</span>
        </a>
      </div>
      <div class="category-item text-center">
        <a href="{{ url('/clothes?genders[]=Girl') }}" class="text-decoration-none">
          <div class="category-img-wrapper mb-2">
            <img src="{{ asset('images/cat_girl.png') }}" alt="Girl" class="category-img shadow-sm" onerror="this.src='https://placehold.co/120x120?text=Girl'">
          </div>
          <span class="category-name text-dark font-weight-bold">Girl</span>
        </a>
      </div>
    </div>

    <div class="text-center mt-5">
      <a href="{{ route('clothes.index') }}" id="viewAllBtn" class="btn btn-warning rounded-pill px-5 py-3 font-weight-bold shadow-lg text-decoration-none">Check all Outfits</a>
    </div>
    
  </div>
</section>

<!-- How it Works Section -->
<section class="how-it-works py-5 bg-white">
  <div class="container">
    <div class="text-center mb-3">
      <h2 class="display-5 font-weight-bold" style="color: #282c3f;">Elevate Your Wardrobe in <span class="text-warning">3 Simple Steps</span></h2>
    </div>

    <div class="row g-4">
      <!-- Step 1 -->
      <div class="col-md-4">
        <div class="step-card">
          <div class="step-img-wrapper">
            <span class="step-badge">1</span>
            <img src="{{ asset('images/step-1.jpg') }}?v=1" alt="Browse" class="step-img">
            <div class="step-overlay"></div>
          </div>
          <div class="step-content text-center mt-3">
            <h4 class="font-weight-bold mb-2">List Your Collection Effortlessly</h4> 
          </div>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="col-md-4">
        <div class="step-card">
          <div class="step-img-wrapper">
            <span class="step-badge">2</span>
            <img src="{{ asset('images/step-2.jpg') }}?v=1" alt="Rent" class="step-img">
            <div class="step-overlay"></div>
          </div>
          <div class="step-content text-center mt-3">
            <h4 class="font-weight-bold mb-2">Pickup & Pan-India Delivery</h4> 
          </div>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="col-md-4">
        <div class="step-card">
          <div class="step-img-wrapper">
            <span class="step-badge">3</span>
            <img src="{{ asset('images/step-3.jpg') }}?v=1" alt="Return" class="step-img">
            <div class="step-overlay"></div>
          </div>
          <div class="step-content text-center mt-3">
            <h4 class="font-weight-bold mb-2">Secure & Hassle-Free Earnings</h4> 
          </div>
        </div>
      </div>
    </div>

    <div class="text-center mt-5">
      <a href="/sell" class="btn btn-warning rounded-pill px-5 py-3 font-weight-bold shadow-lg">Easy to List & Earn</a>
    </div>
  </div>
</section>


<!-- Dynamic 3-Line Brand Marquee -->
@if(isset($brands) && $brands->count() > 0)
<section class="brands-marquee-section py-3 bg-white overflow-hidden">
  <div class="container-fluid px-0">
    <div class="section-header text-center mb-2">
      <h2 class="text-warning font-weight-bold display-4">Our Trusted Brands</h2>
      <p class="text-muted">Partnering with 70+ Premium Fashion Labels</p>
    </div>
    
    @php
      $chunks = $brands->chunk(ceil($brands->count() / 3));
    @endphp

    <div class="marquees-wrapper">
      @foreach($chunks as $index => $rowBrands)
        <div class="marquee-container {{ $index % 2 == 0 ? 'marquee-left' : 'marquee-right' }}">
          <div class="marquee-content">
            @foreach($rowBrands as $brand)
              <div class="brand-logo-item">
                <div class="brand-logo-inner shadow-sm">
                  @if($brand->logo)
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid">
                  @else
                    <div class="brand-placeholder-marquee">
                      <i class="bi bi-tag-fill text-warning"></i>
                      <span>{{ $brand->name }}</span>
                    </div>
                  @endif
                </div>
              </div>
            @endforeach
            <!-- Duplicate for seamless loop -->
            @foreach($rowBrands as $brand)
              <div class="brand-logo-item">
                <div class="brand-logo-inner shadow-sm">
                  @if($brand->logo)
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid">
                  @else
                    <div class="brand-placeholder-marquee">
                      <i class="bi bi-tag-fill text-warning"></i>
                      <span>{{ $brand->name }}</span>
                    </div>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- Occasion Section -->
<section class="occasion-section py-5 bg-light">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title-premium">Styles For Every <span class="text-warning">Occasion</span></h2>
      <p class="section-subtitle-premium">Handpicked outfits tailored for your special moments.</p>
      
      <div class="category-slider-container">
        <button class="slider-arrow arrow-left" onclick="scrollSlider(-200)"><i class="bi bi-chevron-left"></i></button>
        <div class="category-slider" id="categorySlider">
          <a href="{{ route('clothes.index') }}" class="category-pill active-pill" data-category-id="">All Outfits</a>
          @foreach($categories as $category)
            <a href="{{ route('clothes.index') }}?categories[]={{ $category->id }}" class="category-pill" data-category-id="{{ $category->id }}">{{ $category->name }}</a>
          @endforeach
        </div>
        <button class="slider-arrow arrow-right" onclick="scrollSlider(200)"><i class="bi bi-chevron-right"></i></button>
      </div>
    </div>

    <div class="products-grid" id="homeProductsGrid">
      @include('clothes.partials.products-grid', ['clothes' => $clothes])
    </div>

    <div class="text-center mt-5">
      <a href="{{ route('clothes.index') }}" id="viewAllBtn" class="btn btn-warning rounded-pill px-5 py-3 font-weight-bold shadow-lg text-decoration-none">Check all Outfits</a>
    </div>
  </div>
</section>

<!-- Recently Added Section -->
<section class="recently-added-section py-5 bg-white">
  <div class="container">
    <div class="mb-4 text-center">
      <h2 class="section-title-premium mb-1">Recently Added <span class="text-warning">Collections</span></h2>
      <p class="text-muted mb-0">Discover the freshest designer arrivals curated just for you.</p>
    </div>

    <div class="recently-added-scroll">
      @include('clothes.partials.products-grid', ['clothes' => $latestClothes])
    </div>

    <div class="text-center mt-4 d-lg-none">
      <a href="{{ route('clothes.index') }}?sort_by=newest" class="see-all-btn">See All</a>
    </div>
    <div class="text-center mt-4 d-none d-lg-block">
      <a href="{{ route('clothes.index') }}?sort_by=newest" class="see-all-btn">See All</a>
    </div>
  </div>
</section>

<!-- Professional About Us Section - Reimagined -->
<section class="about-premium py-5 bg-white">
  <div class="container">
    <div class="text-center mb-5">
      <span class="text-warning font-weight-bold text-uppercase letter-spacing-2 mb-2 d-block">Why GetReady?</span>
      <h2 class="display-4 font-weight-bold" style="color: #282c3f;">Fashion Rental, <span class="text-warning">Reimagined</span></h2>
    </div>

    <div class="row g-3 justify-content-center">
      <!-- Card 1: Curated Luxury -->
      <div class="col-lg-4 col-md-6">
        <div class="feature-card h-100 p-4 border-0 shadow-sm rounded-lg transition-all hover-up">
          <div class="card-icon-wrapper mb-3">
            <i class="bi bi-gem text-warning"></i>
          </div>
          <div class="feature-content">
            <h5 class="font-weight-bold mb-2">Curated Luxury</h5>
            <p class="text-muted mb-0">Hand-picked designer pieces from prestigious fashion houses for your special moments.</p>
          </div>
        </div>
      </div>

      <!-- Card 2: Sustainable Style -->
      <div class="col-lg-4 col-md-6">
        <div class="feature-card h-100 p-4 border-0 shadow-sm rounded-lg active-card transition-all hover-up">
          <div class="card-icon-wrapper mb-3">
            <i class="bi bi-recycle text-warning"></i>
          </div>
          <div class="feature-content">
            <h5 class="font-weight-bold mb-2">Sustainable Style</h5>
            <p class="text-muted mb-0">Join the revolution of conscious consumption. Renting reduces waste responsibly.</p>
          </div>
        </div>
      </div>

      <!-- Card 3: Pristine Quality -->
      <div class="col-lg-4 col-md-6">
        <div class="feature-card h-100 p-4 border-0 shadow-sm rounded-lg transition-all hover-up">
          <div class="card-icon-wrapper mb-3">
            <i class="bi bi-stars text-warning"></i>
          </div>
          <div class="feature-content">
            <h5 class="font-weight-bold mb-2">Pristine Quality</h5>
            <p class="text-muted mb-0">Every outfit undergoes a 7-step sanitization process for showroom condition.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Interactive Stats Row -->
    <div class="row mt-5 pt-4 border-top text-center">
      <div class="col-md-4 mb-3">
        <h2 class="font-weight-bold text-warning mb-1">5000+</h2>
        <p class="text-muted small text-uppercase font-weight-bold">Designer Outfits</p>
      </div>
      <div class="col-md-4 mb-3">
        <h2 class="font-weight-bold text-warning mb-1">24hr</h2>
        <p class="text-muted small text-uppercase font-weight-bold">Express Delivery</p>
      </div>
      <div class="col-md-4 mb-3">
        <h2 class="font-weight-bold text-warning mb-1">100%</h2>
        <p class="text-muted small text-uppercase font-weight-bold">Fit Guarantee</p>
      </div>
    </div>
  </div>
</section>

<!-- Get the App Section -->
<section class="get-app-section py-0   overflow-hidden">
  <div class="get-app-card border-0">
    <div class="container">
      <div class="row align-items-center">
        <!-- Phone Mockup Side -->
        <div class="col-lg-6 text-center position-relative py-5">
          <div class="mockup-bg-circle"></div>
          <img src="{{ asset('images/app_mockup.png') }}" alt="App Mockup" class="img-fluid app-mockup-img">
        </div>
        
        <!-- Content Side -->
        <div class="col-lg-6 py-5 px-lg-5">
          <div class="app-info text-dark">
            <h2 class="display-4 font-weight-bold mb-3" style="color: #282c3f;">Get the App</h2>
            <p class="lead mb-4" style="color: #696e79; font-size:16px;">Experience premium fashion rental at your fingertips. Join our community of over 5 million fashion enthusiasts.</p>
            
            <div class="app-links d-flex flex-wrap gap-3 mb-5">
              <a href="#" class="store-badge">
                <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" height="48">
              </a>
               
            </div>

            <div class="qr-section d-flex align-items-center">
              <div class="qr-box p-2 bg-white rounded shadow-sm mr-4">
                <img src="{{ asset('images/demo_qr.png') }}" alt="QR Code" width="110" height="110">
              </div>
              <div class="qr-text">
                <h6 class="mb-1 font-weight-bold">Scan to Download</h6>
                <p class="small text-muted mb-0">Point your camera to the QR code to install the app instantly.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Swiper Hero Slider
    const heroSwiper = new Swiper('.hero-slider', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
    });

    // Initialize Swiper Brands Slider
    const brandsSwiper = new Swiper('.brands-swiper', {
        slidesPerView: 2,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.brands-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.brands-next',
            prevEl: '.brands-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 3,
            },
            768: {
                slidesPerView: 4,
            },
            1024: {
                slidesPerView: 5,
            },
        },
    });


    // Buy button functionality for home page
    $('.add-to-cart-buy-btn').click(function(e) {
        e.preventDefault();
        
        const clothId = $(this).data('cloth-id');
        const $btn = $(this);
        
        // Show loading state
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Processing...');
        
        // Get purchase value from button text
        const buttonText = $btn.text();
        const purchaseValue = parseFloat(buttonText.match(/₹([\d,]+)/)[1].replace(/,/g, ''));
        
        const requestData = {
            cloth_id: clothId,
            purchase_type: 'buy',
            total_selling_price: purchaseValue,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $.ajax({
            url: '/cart/add',
            type: 'POST',
            data: requestData,
            success: function(response) {
                if (response.success) {
                    // Update cart count
                    updateCartCount(response.cartCount);
                    
                    // Show success message
                    showAlert('success', response.message);
                    
                    // Update button state
                    $btn.prop('disabled', true).html('<i class="bi bi-check me-1"></i>Purchased');

                    // Also disable the Rent button on the same card
                    const $rentBtn = $btn.closest('.card').find('.add-to-cart-btn[data-cloth-id="' + clothId + '"]');
                    $rentBtn.prop('disabled', true).text('RENTED');

                    // Refresh mini-cart
                    loadCartItems();
                } else {
                    showAlert('danger', response.message);
                    $btn.prop('disabled', false).html('<i class="bi bi-bag-check me-1"></i>Buy - ₹' + purchaseValue.toLocaleString());
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(window.location.href);
                } else {
                    showAlert('danger', 'An error occurred. Please try again.');
                }
                $btn.prop('disabled', false).html('<i class="bi bi-bag-check me-1"></i>Buy - ₹' + purchaseValue.toLocaleString());
            }
        });
    });
});


// Category Slider Global Logic
(function() {
    function initSlider() {
        const slider = document.getElementById('categorySlider');
        if (!slider) return;

        let isDown = false;
        let startX;
        let scrollLeft;
        let moved = false;

        // Mouse Events
        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            moved = false;
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
            slider.style.cursor = 'grabbing';
            slider.style.scrollBehavior = 'auto';
        });

        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.style.cursor = 'grab';
            slider.style.scrollBehavior = 'smooth';
        });

        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.style.cursor = 'grab';
            slider.style.scrollBehavior = 'smooth';
        });

        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2;
            if (Math.abs(walk) > 10) moved = true;
            slider.scrollLeft = scrollLeft - walk;
        });

        // Click Prevention
        slider.addEventListener('click', (e) => {
            if (moved) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);

        // Arrow Scroll Function
        window.scrollSlider = function(amount) {
            slider.style.scrollBehavior = 'smooth';
            slider.scrollLeft += amount;
        };

        // AJAX Filtering Logic
        const categoryPills = slider.querySelectorAll('.category-pill');
        const productsGrid = document.getElementById('homeProductsGrid');
        const viewAllBtn = document.getElementById('viewAllBtn');

        categoryPills.forEach(pill => {
            pill.addEventListener('click', function(e) {
                // Only act if not dragged
                if (moved) return;
                
                e.preventDefault();
                const categoryId = this.getAttribute('data-category-id');

                // Update active state
                categoryPills.forEach(p => p.classList.remove('active-pill'));
                this.classList.add('active-pill');

                // Show loading state in grid
                productsGrid.style.opacity = '0.5';

                // Fetch filtered products
                fetch(`/?category_id=${categoryId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    productsGrid.innerHTML = data.html;
                    productsGrid.style.opacity = '1';
                    if (viewAllBtn) {
                        viewAllBtn.href = data.category_url;
                    }
                })
                .catch(error => {
                    console.error('Error fetching categories:', error);
                    productsGrid.style.opacity = '1';
                });
            });
        });
        
        console.log("Category Slider Initialized Successfully");
    }

    // Attempt to initialize multiple times to ensure success
    setTimeout(initSlider, 100);
    window.addEventListener('load', initSlider);
})();
</script>
@endsection
