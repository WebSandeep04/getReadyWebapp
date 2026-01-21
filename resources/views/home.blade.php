@extends('layouts.app')

@section('title', frontend_setting('site_title', 'Get Ready - Home'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hero.css') }}">
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero text-center d-flex align-items-center justify-content-center" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset(frontend_setting('hero_image', 'images/main.png')) }}') center/cover; height: 100vh; min-height: 100vh;">
  <div class="container text-center d-flex flex-column align-items-center justify-content-center">
    <h1 class="text-white display-4 mb-3 text-center">{{ frontend_setting('hero_title', 'Welcome to GetReady') }}</h1>
    <h3 class="text-white mb-3 text-center">{{ frontend_setting('hero_subtitle', 'Your premier destination for fashion rental') }}</h3>
    <p class="text-white mb-4 text-center">{{ frontend_setting('hero_description', 'Discover amazing fashion pieces for your special occasions. Rent, wear, and return with ease.') }}</p>
    <a href="{{ frontend_setting('hero_button_url', '/clothes') }}" class="btn btn-warning btn-lg text-center">{{ frontend_setting('hero_button_text', 'Start Shopping') }}</a>
  </div>
</section>

<!-- About -->
<section class="about text-center py-4">
  <h2 class="text-warning">{{ frontend_setting('about_title', 'About Us') }}</h2>
  <p>{{ frontend_setting('about_content', 'Celebrate every occasion in style — without compromise. At GetReady, we make it easy to buy, sell, or rent premium outfits for weddings, festivals, and events. Smart fashion choices for modern wardrobes.') }}</p>
</section>

<!-- Brands Carousel -->
@if($brands->count() > 0)
<section class="brands-carousel-section py-5 bg-white">
  <div class="container">
    <h2 class="text-center text-warning mb-4">Our Brands</h2>
    <div id="brandsCarousel" class="carousel slide" data-ride="carousel" data-interval="3000">
      <div class="carousel-inner">
        @php
          $brandsPerSlide = 5;
          $totalSlides = ceil($brands->count() / $brandsPerSlide);
        @endphp
        @for($i = 0; $i < $totalSlides; $i++)
          <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
            <div class="row justify-content-center align-items-center">
              @foreach($brands->slice($i * $brandsPerSlide, $brandsPerSlide) as $brand)
                <div class="col-6 col-md-2 text-center mb-3">
                  <div class="brand-logo-wrapper">
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="brand-logo">
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endfor
      </div>
      @if($totalSlides > 1)
        <a class="carousel-control-prev" href="#brandsCarousel" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#brandsCarousel" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
        <ol class="carousel-indicators">
          @for($i = 0; $i < $totalSlides; $i++)
            <li data-target="#brandsCarousel" data-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}"></li>
          @endfor
        </ol>
      @endif
    </div>
  </div>
</section>
@endif

<!-- Most Loved -->
<section class="most-loved text-center py-4 bg-light">
  <h2 class="text-warning mb-3">Most Loved</h2>
  <div class="tabs mb-4">
    <button class="tab btn btn-outline-warning active" onclick="switchTab('men')">Men</button>
    <button class="tab btn btn-outline-warning" onclick="switchTab('women')">Women</button>
    <button class="tab btn btn-outline-warning" onclick="switchTab('kids')">Kids</button>
  </div>

  <div id="mostLovedCarousel" class="carousel slide w-75 mx-auto" data-ride="carousel">
    <ol class="carousel-indicators">
      <li data-target="#mostLovedCarousel" data-slide-to="0" class="active"></li>
      <li data-target="#mostLovedCarousel" data-slide-to="1"></li>
      <li data-target="#mostLovedCarousel" data-slide-to="2"></li>
    </ol>

    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="{{ asset('images/lehenga.jpg') }}" class="d-block mx-auto" alt="Outfit 1" style="height: 400px;">
      </div>
      <div class="carousel-item">
        <img src="{{ asset('images/lehenga.jpg') }}" class="d-block mx-auto" alt="Outfit 2" style="height: 400px;">
      </div>
      <div class="carousel-item">
        <img src="{{ asset('images/lehenga.jpg') }}" class="d-block mx-auto" alt="Outfit 3" style="height: 400px;">
      </div>
    </div>

    <a class="carousel-control-prev" href="#mostLovedCarousel" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#mostLovedCarousel" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>
  </div>
</section>

<!-- Occasion -->
<section class="occasion text-center py-4">
  <h2 class="text-warning mb-3">Choose Your Outfits According To Your Occasion</h2>
  <div class="occasion-tabs mb-3">
    <button class="btn btn-outline-secondary mx-1">Wedding</button>
    <button class="btn btn-outline-secondary mx-1">Corporate Event</button>
    <button class="btn btn-outline-secondary mx-1">Party</button>
    <button class="btn btn-outline-secondary mx-1">Others</button>
  </div>

  <div class="container">
    <div class="row justify-content-center">
      @forelse($clothes as $cloth)
        <div class="col-6 col-md-3 mb-3">
          <div class="card h-100">
            <a href="{{ route('clothes.show', $cloth->id) }}">
              @if($cloth->images->count() > 0)
                <img src="{{ asset('storage/' . $cloth->images->first()->image_path) }}" alt="{{ $cloth->title }}" class="card-img-top">
              @else
                <img src="{{ asset('images/1.jpg') }}" alt="{{ $cloth->title }}" class="card-img-top">
              @endif
            </a>
            <div class="card-body d-flex flex-column">
              <h6 class="card-title">{{ $cloth->title }}</h6>
              @if($cloth->user && $cloth->user->average_rating > 0)
                  <div class="mb-2">
                      <span class="badge badge-light border text-warning border-warning" title="Seller Rating">
                          <i class="bi bi-star-fill text-warning"></i> {{ $cloth->user->average_rating }}
                      </span>
                  </div>
              @endif
                              <p class="card-text text-warning fw-bold">₹{{ number_format($cloth->rent_price) }}</p>
                <div class="d-flex flex-column gap-1 mt-auto">
                  <!-- <button class="btn btn-warning btn-sm add-to-cart-btn" data-cloth-id="{{ $cloth->id }}" style="cursor: pointer;">
                    <i class="bi bi-cart-plus me-1"></i>Rent
                  </button>
                  @if($cloth->is_purchased)
                    <button class="btn btn-success btn-sm add-to-cart-buy-btn" data-cloth-id="{{ $cloth->id }}" style="cursor: pointer; background-color: #28a745; border-color: #28a745;">
                      <i class="bi bi-bag-check me-1"></i>Buy - ₹{{ number_format($cloth->purchase_value) }}
                    </button>
                  @endif -->
                </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12 text-center">
          <p>No clothes available at the moment.</p>
        </div>
      @endforelse
    </div>
  </div>

  <button class="btn btn-warning mt-3">Load More</button>
</section>

@section('scripts')
<script>
$(document).ready(function() {
    // On load, disable buttons for items already in the user's cart
    loadCartItems();

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
            total_purchase_cost: purchaseValue,
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
                } else {
                    showAlert('danger', response.message);
                    $btn.prop('disabled', false).html('<i class="bi bi-bag-check me-1"></i>Buy - ₹' + purchaseValue.toLocaleString());
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    window.location.href = '/login';
                } else {
                    showAlert('danger', 'An error occurred. Please try again.');
                }
                $btn.prop('disabled', false).html('<i class="bi bi-bag-check me-1"></i>Buy - ₹' + purchaseValue.toLocaleString());
            }
        });
    });
});

// Load cart items and disable corresponding buttons
function loadCartItems() {
    $.ajax({
        url: '/cart/items',
        type: 'GET',
        success: function(response) {
            if (response.cartItems) {
                response.cartItems.forEach(function(item) {
                    if (item.purchase_type === 'buy') {
                        updateAllBuyButtons(item.cloth_id, true);
                    } else {
                        updateAllRentButtons(item.cloth_id, true);
                    }
                });
            }
        }
    });
}

// Disable/enabled Rent buttons for a cloth id
function updateAllRentButtons(clothId, isRented) {
    const $buttons = $('.add-to-cart-btn[data-cloth-id="' + clothId + '"]');
    $buttons.each(function() {
        const $btn = $(this);
        if (isRented) {
            $btn.text('RENTED').prop('disabled', true).attr('title', 'Already in cart');
            // Also disable Buy button on the same card
            const $buyBtn = $btn.closest('.card').find('.add-to-cart-buy-btn[data-cloth-id="' + clothId + '"]');
            $buyBtn.prop('disabled', true);
        } else {
            $btn.html('<i class="bi bi-cart-plus me-1"></i>Rent').prop('disabled', false).removeAttr('title');
            const $buyBtn = $btn.closest('.card').find('.add-to-cart-buy-btn[data-cloth-id="' + clothId + '"]');
            $buyBtn.prop('disabled', false);
        }
    });
}

// Disable/enabled Buy buttons for a cloth id
function updateAllBuyButtons(clothId, isPurchased) {
    const $buttons = $('.add-to-cart-buy-btn[data-cloth-id="' + clothId + '"]');
    $buttons.each(function() {
        const $btn = $(this);
        if (isPurchased) {
            $btn.text('PURCHASED').prop('disabled', true).attr('title', 'Already purchased');
            // Also disable Rent button on the same card
            const $rentBtn = $btn.closest('.card').find('.add-to-cart-btn[data-cloth-id="' + clothId + '"]');
            $rentBtn.prop('disabled', true).text('RENTED');
        } else {
            // Re-enable
            // We reconstruct button text conservatively without price to avoid parsing issues
            $btn.html('<i class="bi bi-bag-check me-1"></i>Buy').prop('disabled', false).removeAttr('title');
            const $rentBtn = $btn.closest('.card').find('.add-to-cart-btn[data-cloth-id="' + clothId + '"]');
            $rentBtn.prop('disabled', false).html('<i class="bi bi-cart-plus me-1"></i>Rent');
        }
    });
}

// Update cart count in header
function updateCartCount(count) {
    const $cartCount = $('#cart-count');
    if ($cartCount.length > 0) {
        $cartCount.text(count);
        if (count > 0) {
            $cartCount.show();
        } else {
            $cartCount.hide();
        }
    }
}

// Show alert message
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('body').prepend(alertHtml);
    
    // Auto-hide after 3 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 3000);
}
</script>
@endsection
