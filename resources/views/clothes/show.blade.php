@extends('layouts.app-simple')

@section('title', $cloth->title)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/product.css') }}" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="{{ asset('css/cloth-show.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
    .related-items {
        padding: 4rem 0;
        background: #f8fafc;
    }
    .section-title {
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-bottom: 2rem;
    }
    
    /* Swiper Custom Styles */
    .product-gallery {
        display: flex;
        flex-direction: row-reverse;
        gap: 15px;
        height: 720px;
    }
    
    .product-gallery__main {
        flex: 1;
        width: 0; /* Important for Swiper inside flex */
        height: 100%;
        position: relative;
    }
    
    .mainSwiper {
        height: 100%;
        border-radius: 14px;
        overflow: hidden;
    }
    
    .mainSwiper .swiper-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-gallery__thumbs {
        width: 100px;
        height: 100%;
    }
    
    .thumbSwiper {
        height: 100%;
    }
    
    .thumbSwiper .swiper-slide {
        width: 100%;
        height: 80px !important; /* Slightly smaller to fit better */
        opacity: 0.6;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .thumbSwiper .swiper-slide-thumb-active {
        opacity: 1;
    }
    
    .thumbSwiper .swiper-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 12px;
        border: 2px solid transparent;
    }
    
    .thumbSwiper .swiper-slide-thumb-active img {
        border-color: #FFA500;
    }

    .swiper-button-next, .swiper-button-prev {
        color: #fff;
        background: rgba(0,0,0,0.2);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        backdrop-filter: blur(4px);
    }
    
    .swiper-button-next:after, .swiper-button-prev:after {
        font-size: 18px;
        font-weight: bold;
    }

    @media (max-width: 992px) {
        .product-gallery {
            flex-direction: column;
            height: auto;
        }
        .product-gallery__main {
            width: 100%;
            height: 420px; /* Reduced from 500px */
        }
        .product-gallery__thumbs {
            width: 100%;
            height: 87px;
            margin-top: -12px;
        }
        .thumbSwiper .swiper-slide {
            width: 70px !important; /* Smaller thumbnails on mobile */
            height: 70px !important;
        }
    }
    
    @media (max-width: 576px) {
        .product-gallery__main {
            height: 380px; /* Reduced from 420px */
        }
    }
</style>
@endsection

@section('content')
<div id="alert-container"></div>

<!-- Measurements Modal -->
<div class="modal fade" id="measurementsModal" tabindex="-1" aria-labelledby="measurementsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="measurementsModalLabel"> Measurements</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted small mb-3">All measurements are in {{ $cloth->measurement_unit ?? 'inch' }}.</p>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span>Chest / Bust</span>
            <span class="font-weight-bold text-dark">{{ $cloth->chest_bust ?? '—' }} {{ $cloth->measurement_unit == 'cm' ? 'cm' : 'in' }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span>Waist</span>
            <span class="font-weight-bold text-dark">{{ $cloth->waist ?? '—' }} {{ $cloth->measurement_unit == 'cm' ? 'cm' : 'in' }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span>Length</span>
            <span class="font-weight-bold text-dark">{{ $cloth->length ?? '—' }} {{ $cloth->measurement_unit == 'cm' ? 'cm' : 'in' }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span>Shoulder</span>
            <span class="font-weight-bold text-dark">{{ $cloth->shoulder ?? '—' }} {{ $cloth->measurement_unit == 'cm' ? 'cm' : 'in' }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span>Sleeve Length</span>
            <span class="font-weight-bold text-dark">{{ $cloth->sleeve_length ?? '—' }} {{ $cloth->measurement_unit == 'cm' ? 'cm' : 'in' }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<section class="product-hero container">
  <div class="row g-4 align-items-start">
    <div class="col-lg-7">
      <div class="product-gallery">
        <div class="product-gallery__main">
          <div class="swiper mainSwiper">
            <div class="swiper-wrapper">
              @if($cloth->images->count())
                @foreach($cloth->images as $image)
                  <div class="swiper-slide">
                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $cloth->title }}">
                  </div>
                @endforeach
              @else
                <div class="swiper-slide">
                  <img src="{{ asset('images/lehenga.jpg') }}" alt="{{ $cloth->title }}">
                </div>
              @endif
            </div>
          </div>
          
          <div class="floating-badge shadow-sm" style="background: rgba(255,255,255,0.95); color: #1e293b; border: 1px solid rgba(0,0,0,0.05); font-weight: 600; backdrop-filter: blur(5px); z-index: 10;">
            <i class="bi bi-patch-check-fill text-primary" style="font-size: 1.1rem;"></i> QC Passed
          </div>
        </div>

        @if($cloth->images->count() > 1)
          <div class="product-gallery__thumbs">
            <div class="swiper thumbSwiper">
              <div class="swiper-wrapper">
                @foreach($cloth->images as $image)
                  <div class="swiper-slide">
                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="thumb">
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endif
      </div>

      <div class="card shadow-sm mt-4 p-4">
        <div class="d-flex flex-wrap gap-2 mb-4">
          <span class="chip-premium chip-category"><i class="bi bi-tag-fill"></i>{{ $cloth->category->name ?? 'Premium Wear' }}</span>
          <span class="chip-premium chip-gender"><i class="bi bi-gender-ambiguous"></i>{{ $cloth->gender }}</span>
          <span class="chip-premium chip-color"><i class="bi bi-palette"></i>{{ $cloth->color->name ?? 'Not specified' }}</span>
          <span class="chip-premium chip-size"><i class="bi bi-rulers"></i>Size {{ $cloth->size->name ?? $cloth->size }}</span>
        </div>

        <div class="product-header mb-2">
          <h1 class="product-title fw-bold mb-1" style="font-size: 2.2rem; letter-spacing: -0.01em;">{{ $cloth->title }}</h1>
          <p class="text-muted mb-0 d-flex align-items-center gap-2">
            <span class="fw-semibold text-dark">{{ $cloth->brand->name ?? 'Independent Designer' }}</span>
            <span class="opacity-25">|</span>
            <span class="small text-uppercase tracking-wider">Product Code: #{{ str_pad($cloth->id, 5, '0', STR_PAD_LEFT) }}</span>
          </p>
        </div>

        <div class="info-specs-grid mb-4">
          <div class="spec-item">
            <div class="spec-label">FIT TYPE</div>
            <div class="spec-value">{{ $cloth->fitType->name ?? 'Regular fit' }}</div>
          </div>
          <div class="spec-item">
            <div class="spec-label">CONDITION</div>
            <div class="spec-value d-flex align-items-center gap-1">
              <i class="bi bi-shield-check text-success"></i>  {{ $cloth->condition->name ?? $cloth->condition }}
            </div>
          </div>
          <div class="spec-item">
            <div class="spec-label d-flex align-items-center gap-2">
              MEASUREMENTS
              <button type="button" class="btn btn-link p-0 text-primary" data-toggle="modal" data-target="#measurementsModal">
                 <i class="bi bi-info-circle"></i>
              </button>
            </div>
            <div class="spec-value">
              Chest {{ $cloth->chest_bust ?? '—' }}{{ $cloth->measurement_unit == 'cm' ? 'cm' : 'in' }} · Waist {{ $cloth->waist ?? '—' }}{{ $cloth->measurement_unit == 'cm' ? 'cm' : 'in' }}
            </div>
          </div>
        </div>

        <div class="product-description-refined">
          <div class="row g-4">
            <div class="col-md-6">
              <h6 class="text-uppercase small fw-bold text-muted mb-2 ls-1">Fabric & Highlights</h6>
              <p class="mb-0 text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                Crafted from {{ strtolower($cloth->fabric->name ?? 'premium fabric') }} with a {{ strtolower($cloth->color->name ?? 'multi') }} tone finish. Features a signature {{ strtolower($cloth->bottomType->name ?? 'designer') }} silhouette.
              </p>
            </div>
            <div class="col-md-6">
              <h6 class="text-uppercase small fw-bold text-muted mb-2 ls-1">Owner's Notes</h6>
              <p class="mb-0 text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                <i class="bi bi-chat-left-quote text-primary-subtle me-2"></i>{{ $cloth->defects ?? 'This piece is in pristine condition with no visible flaws.' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Extended Details & Care -->
      <div class="card shadow-sm mt-4 p-4">
        <h5 class="fw-bold mb-4">Product Details & Care</h5>
        <div class="row g-4">
          <div class="col-md-6">
            <ul class="assurance-list">
              <li><i class="bi bi-droplet-fill text-primary"></i> Professional Dry Clean Only</li>
              <li><i class="bi bi-stars text-primary"></i> Hand-finished embroidery</li>
              <li><i class="bi bi-shield-check text-primary"></i> 100% Authentic Designer Wear</li>
            </ul>
          </div>
          <div class="col-md-6">
            <div class="p-3 rounded-4 bg-light">
              <p class="small text-muted mb-0">
                <i class="bi bi-info-circle-fill me-2"></i>
                All items undergo a multi-step quality check and sanitization process before being dispatched.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="sticky-lg-top" style="top: 90px;">
        <div class="summary card shadow-lg border-0">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div class="w-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="price-title-area">
                    <p class="text-uppercase text-muted small mb-1 fw-bold" style=" font-size: 0.9rem;">RENTAL PRICE</p>
                  </div>
                  <div class="status-area text-end">
                    <div class="availability-status mb-2">
                      @if($cloth->is_purchased)
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1" style="font-size: 0.8rem; font-weight: 600;">
                          <i class="bi bi-handbag me-1"></i> READY TO BUY
                        </span>
                      @elseif($cloth->availabilityBlocks->where('type', 'available')->count() > 0)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.75rem; font-weight: 700;">
                          <i class="bi bi-calendar-check me-1"></i> AVAILABLE
                        </span>
                      @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.8rem; font-weight: 600;">
                          <i class="bi bi-check-circle me-1"></i> READY TO RENT
                        </span>
                      @endif
                    </div>
                    <div class="trust-badge">
                      @if($cloth->user && $cloth->user->average_rating > 0)
                        <span class="badge bg-white text-warning border border-warning px-2 py-1 rounded-pill shadow-sm" style="font-size: 0.85rem; font-weight: 700;">
                          <i class="bi bi-star-fill me-1"></i> {{ $cloth->user->average_rating }} Rating
                        </span>
                      @else
                        <span class="badge bg-success-subtle text-success py-1 rounded-pill" style="font-size: 0.9rem; letter-spacing: 0.02em; font-weight: 700;">
                          <i class="bi bi-patch-check-fill me-1"></i> TRUSTED OWNER
                        </span>
                      @endif
                    </div>
                  </div>
                </div>

                <div class="price-container ">
                  <div class="d-flex align-items-baseline gap-2">
                    <h2 class="mb-0 text-dark fw-bold" style="font-size: 2.2rem; letter-spacing: -0.02em; font-weight: 800;">₹{{ number_format($cloth->display_rent_price) }}</h2>
                    <span class="text-muted fw-medium" style="font-size: 1.0rem;"> /4 days</span>
                  </div>
                  <p class="text-muted small mt-1 mb-2" style="font-size: 0.95rem;">₹{{ number_format($cloth->display_rent_price / 4) }} per additional day (after 4 days)</p>
                  
                    <div class="financial-details pt-2 mt-2 border-top" style="border-color: #f1f5f9 !important;">
                      @if($cloth->mrp)
                        <p class="text-muted small mb-1" style="font-size: 0.85rem;">Retail Price (MRP): <del class="text-secondary">₹{{ number_format($cloth->mrp) }}</del></p>
                      @endif
                      
                      @if($cloth->is_purchased && $cloth->selling_price)
                        <p class="text-dark small mb-1 fw-semibold" style="font-size: 0.95rem;">
                          <i class="bi bi-handbag text-info me-1"></i> Buy Price: <span class="fw-bold text-info">₹{{ number_format($cloth->selling_price) }}</span>
                        </p>
                      @endif

                      <p class="text-dark small mb-0 fw-semibold" style="font-size: 0.9rem;">
                        <i class="bi bi-shield-lock text-success me-1"></i> Refundable Security Deposit: <span class="fw-bold">₹{{ number_format($cloth->security_deposit) }}</span>
                      </p>
                    </div>
                  
                  <div class="availability-dates-row mt-3 p-2 rounded-3" style="background: #f8fafc; border: 1.5px dashed #e2e8f0;">
                    <span class="text-muted small fw-bold me-2" style="font-size: 0.8rem; ">BOOKING WINDOW:</span>
                    @if($cloth->availabilityBlocks->where('type', 'available')->count() > 0)
                      @foreach($cloth->availabilityBlocks->where('type', 'available') as $block)
                        <span class="fw-bold text-success"  style="font-size: 0.9rem; font-weight: 800;">
                          {{ \Carbon\Carbon::parse($block->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($block->end_date)->format('d M') }}
                        </span>
                      @endforeach
                    @else
                      <span class="small text-success fw-bold" style="font-size: 0.9rem; font-weight: 800;">Flexible Booking Available</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="date-picker-sidebar mt-4 mb-4">
              <div class="row g-2">
                <div class="col-6">
                  <label for="start_date" class="form-label small fw-bold text-muted mb-1 ls-1">START DATE</label>
                  <div class="date-input-wrapper">
                    <i class="bi bi-calendar-event"></i>
                    <input type="text" class="form-control form-control-sm bg-white" id="start_date" name="start_date" placeholder="Select" readonly="readonly" required>
                  </div>
                </div>
                <div class="col-6">
                  <label for="end_date" class="form-label small fw-bold text-muted mb-1 ls-1">END DATE</label>
                  <div class="date-input-wrapper">
                    <i class="bi bi-calendar-check"></i>
                    <input type="text" class="form-control form-control-sm bg-white" id="end_date" name="end_date" placeholder="Select" readonly="readonly" required>
                  </div>
                </div>
              </div>
              
              <div class="rental-summary mt-3" id="rental-summary" style="display:none; background: #f8fafc; border-radius: 12px; padding: 15px;">
                <div class="d-flex justify-content-between small mb-1">
                  <span class="text-muted">Duration</span>
                  <span id="rental-details-duration" class="fw-bold">0 days</span>
                </div>
                <div id="rental-cost-breakdown" class="small"></div>
                <div class="d-flex justify-content-between small mb-1">
                  <span class="text-muted">Rental Cost</span>
                  <span id="rental-details-cost" class="fw-bold">₹0</span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                  <span class="text-muted">Security Deposit</span>
                  <span class="fw-bold">₹{{ number_format($cloth->security_deposit) }}</span>
                </div>
                <hr class="my-2 opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold">Total Amount</span>
                  <span class="h5 mb-0 fw-bold text-primary">₹<span id="total-price">0</span></span>
                </div>
              </div>
            </div>

            @if($cloth->sku > 0)
              <div class="d-grid gap-3">
                <button class="rent-button add-to-cart-btn w-100" data-cloth-id="{{ $cloth->id }}" id="productRentBtn" disabled>
                  <i class="bi bi-calendar2-plus me-2"></i> Select dates to rent
                </button>
                
                @if($cloth->is_purchased && $cloth->selling_price)
                  <button class="buy-button add-to-cart-buy-btn mt-2 w-100" data-cloth-id="{{ $cloth->id }}" id="productBuyBtn">
                    <i class="bi bi-handbag-fill me-2"></i> Buy Now - ₹{{ number_format($cloth->selling_price) }}
                  </button>
                @endif
              </div>
            @endif

            <div class="assurance-grid mt-4">
              <div class="assurance-card">
                <i class="bi bi-truck"></i>
                <span>Free Pick & Drop</span>
              </div>
              <div class="assurance-card">
                <i class="bi bi-stars"></i>
                <span>Freshly Cleaned</span>
              </div>
              <div class="assurance-card">
                <i class="bi bi-shield-lock"></i>
                <span>Secure Payments</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Related Products Section -->
@if(isset($relatedClothes) && $relatedClothes->count() > 0)
<section class="related-items">
    <div class="container">
        <h3 class="section-title">Similar Styles You'll Love</h3>
        <div class="row g-4">
            @foreach($relatedClothes as $related)
                <div class="col-6 col-md-3">
                    <a href="{{ route('clothes.show', $related->id) }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                            @if($related->images->count())
                                <img src="{{ asset('storage/' . $related->images->first()->image_path) }}" class="card-img-top" alt="{{ $related->title }}" style="height: 250px; object-fit: cover;">
                            @endif
                            <div class="card-body p-3">
                                <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $related->title }}</h6>
                                <p class="text-primary fw-bold mb-0">₹{{ number_format($related->rent_price) }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Swiper
    var swiperThumbs = new Swiper(".thumbSwiper", {
        spaceBetween: 10,
        slidesPerView: "auto",
        freeMode: true,
        watchSlidesProgress: true,
        direction: window.innerWidth > 992 ? "vertical" : "horizontal",
    });
    
    var swiperMain = new Swiper(".mainSwiper", {
        spaceBetween: 10,
        thumbs: {
            swiper: swiperThumbs,
        },
        grabCursor: true,
        loop: true,
    });

    // Update swiper direction on resize
    window.addEventListener('resize', function() {
        swiperThumbs.changeDirection(window.innerWidth > 992 ? "vertical" : "horizontal");
    });

    // Flatpickr initialization
    const disabledDates = @json($cloth->availabilityBlocks->where('type', 'blocked')->map(function($block) {
        return [
            'from' => $block->start_date->format('Y-m-d'),
            'to' => $block->end_date->format('Y-m-d')
        ];
    }));

    const config = {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        minDate: "today",
        disable: disabledDates,
        onChange: function(selectedDates, dateStr, instance) {
            calculateRental();
        }
    };

    const startPicker = flatpickr("#start_date", config);
    const endPicker = flatpickr("#end_date", config);

    let currentRentalDays = 0;
    let currentRentalCost = 0;

    function calculateRental() {
        const start = startPicker.selectedDates[0];
        const end = endPicker.selectedDates[0];

        if (start && end) {
            if (end <= start) {
                alert("End date must be after start date");
                endPicker.clear();
                return;
            }

            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            currentRentalDays = diffDays;
            $('#rental-details-duration').text(diffDays + " days");
            
            const basePrice = {{ $cloth->display_rent_price }};
            const dailyRate = basePrice / 4;
            let totalPrice = basePrice;
            
            if (diffDays > 4) {
                totalPrice += (diffDays - 4) * dailyRate;
            }

            currentRentalCost = totalPrice;
            const securityDeposit = {{ $cloth->security_deposit }};
            const grandTotal = totalPrice + securityDeposit;

            $('#rental-details-cost').text("₹" + totalPrice.toLocaleString());
            $('#total-price').text(grandTotal.toLocaleString());
            $('#rental-summary').slideDown();
            $('#productRentBtn').prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i> Add to Bag');
        }
    }

    // Add to cart logic
    $('#productRentBtn').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // Prevent generic cart.js listener from firing

        const $btn = $(this);
        const clothId = $btn.data('cloth-id');
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        if (!startDate || !endDate) {
            showAlert('danger', 'Please select rental dates first');
            return;
        }

        // Disable button and show loading state
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Adding...');

        $.ajax({
            url: '{{ route("cart.add") }}',
            method: 'POST',
            data: {
                cloth_id: clothId,
                rental_start_date: startDate,
                rental_end_date: endDate,
                total_rental_cost: currentRentalCost,
                rental_days: currentRentalDays,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update cart count and mini-cart
                    if (typeof updateCartCount === 'function') updateCartCount(response.cartCount);
                    if (typeof loadCartItems === 'function') loadCartItems();
                    
                    // Show success feedback
                    showAlert('success', response.message);
                    
                    // Update button state
                    $btn.html('<i class="bi bi-check-circle-fill me-2"></i> RENTED')
                        .addClass('rented-button')
                        .removeClass('rent-button add-to-cart-btn');
                    
                    // Re-enable after a short delay if they want to change dates (which updates the item)
                    setTimeout(() => {
                        $btn.prop('disabled', false);
                    }, 1000);
                } else {
                    showAlert('danger', response.message || 'Error adding to bag');
                    $btn.prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i> Add to Bag');
                }
            },
            error: function(xhr) {
                console.error('Cart Error:', xhr.responseText);
                let errorMessage = 'Error adding to bag. Please try again.';
                
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 401) {
                    errorMessage = 'Please login to add items to bag.';
                    window.location.href = '{{ route("login") }}?redirect=' + encodeURIComponent(window.location.href);
                }
                
                showAlert('danger', errorMessage);
                $btn.prop('disabled', false).html('<i class="bi bi-calendar2-plus me-2"></i> Add to Bag');
            }
        });
    });

    // Buy Now logic
    $('#productBuyBtn').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const $btn = $(this);
        const clothId = $btn.data('cloth-id');

        // Disable button and show loading state
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Adding...');

        $.ajax({
            url: '{{ route("cart.add") }}',
            method: 'POST',
            data: {
                cloth_id: clothId,
                purchase_type: 'buy',
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    if (typeof updateCartCount === 'function') updateCartCount(response.cartCount);
                    if (typeof loadCartItems === 'function') loadCartItems();
                    
                    showAlert('success', response.message);
                    
                    $btn.html('<i class="bi bi-check-circle-fill me-2"></i> PURCHASED')
                        .addClass('purchased-button')
                        .removeClass('buy-button add-to-cart-buy-btn');
                } else {
                    showAlert('danger', response.message || 'Error adding to bag');
                    $btn.prop('disabled', false).html('<i class="bi bi-handbag-fill me-2"></i> Buy Now');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error adding to bag. Please try again.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert('danger', errorMessage);
                $btn.prop('disabled', false).html('<i class="bi bi-handbag-fill me-2"></i> Buy Now');
            }
        });
    });
});
</script>
@endsection