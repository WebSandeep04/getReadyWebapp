@extends('layouts.app')

@section('title', 'Get Ready - Cart')

@section('content')
<div class="container py-5" style="margin-bottom: 80px;">
    <div class="row mb-2">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="bg-warning-subtle p-3 rounded-4 cart-header-icon" style="background-color: #fff7ed !important;">
                    <i class="bi bi-bag-heart text-warning" style="color: #f78c1c !important;"></i>
                </div>
                <div>
                    <h1 class="h2 fw-bold mb-0 text-dark">Your Shopping Bag</h1>
                    <p class="text-muted mb-0">Review your selected items and proceed to checkout</p>
                </div>
            </div>
            @if(request('payment') === 'success')
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mt-4" role="alert">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                        <div>
                            <strong class="d-block">Payment Successful!</strong>
                            <span>We've received your order and are preparing it for delivery.</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-alert="alert"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="cart-container">
        @if(Auth::check() && $cartItems->count() > 0)
            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Cart Items -->
                    @foreach($cartItems as $cartItem)
                        <div class="card mb-4 border-0 shadow-sm rounded-4 cart-item overflow-hidden" data-cart-item-id="{{ $cartItem->id }}">
                            <div class="card-body p-4">
                                <div class="row align-items-center g-4">
                                    <div class="col-md-3">
                                        <div class="position-relative">
                                            @if($cartItem->cloth->images->count() > 0)
                                                <img src="{{ asset('storage/' . $cartItem->cloth->images->first()->image_path) }}" 
                                                     alt="{{ $cartItem->cloth->title }}" class="img-fluid rounded-4 shadow-sm w-100 cart-item-img" style="height: 180px; object-fit: cover; border-radius: 10px;">
                                            @else
                                                <img src="{{ asset('images/1.jpg') }}" alt="{{ $cartItem->cloth->title }}" class="img-fluid rounded-4 shadow-sm w-100 cart-item-img" style="height: 180px; object-fit: cover; border-radius: 10px;">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="mb-1">
                                                <span class="badge {{ $cartItem->purchase_type === 'buy' ? 'bg-success' : 'bg-warning' }} rounded-pill px-3 py-2 shadow-sm mb-2 d-inline-block" style="font-size: 0.65rem; color:#fff; letter-spacing: 0.5px; {{ $cartItem->purchase_type !== 'buy' ? 'background-color: #f78c1c !important;' : '' }}">
                                                    {{ $cartItem->purchase_type === 'buy' ? 'PURCHASE' : 'RENTAL' }}
                                                </span>
                                                <h4 class="fw-bold text-dark mb-0" style="font-size: 1.4rem;">{{ $cartItem->cloth->title }}</h4>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 mb-2 text-muted small">
                                                <span class="bg-light px-2 py-1 rounded-2 border shadow-sm">Size: <span class="text-dark fw-semibold">{{ $cartItem->cloth->sizeRef->name ?? 'Unknown' }}</span></span>
                                                <span class="bg-light px-2 py-1 rounded-2 border shadow-sm">Condition: <span class="text-dark fw-semibold">{{ $cartItem->cloth->conditionRef->name ?? 'Unknown' }}</span></span>
                                            </div>

                                            @if($cartItem->purchase_type === 'buy')
                                                <div class="p-3 rounded-4 bg-success-subtle border border-success-subtle d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="text-success small fw-semibold d-block">PURCHASE PRICE</span>
                                                        <p class="text-success mb-0 x-small opacity-75">Ownership after delivery</p>
                                                    </div>
                                                    <span class="text-success h5 fw-bold mb-0">₹{{ number_format($cartItem->total_selling_price) }}</span>
                                                </div>
                                            @else
                                                <div class="p-3 rounded-4 bg-light border border-light-subtle shadow-sm">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="text-muted small fw-bold">RENTAL COST</span>
                                                        <span class="fw-bold fs-6" style="color: #f78c1c;">₹{{ number_format($cartItem->total_rental_cost) }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center text-muted small fw-semibold">
                                                        <i class="bi bi-calendar3" style="color: #f78c1c; font-size: 1.1em; margin-right: 8px;"></i> 
                                                        <span>{{ \Carbon\Carbon::parse($cartItem->rental_start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($cartItem->rental_end_date)->format('d M, Y') }} ({{ $cartItem->rental_days }} Days)</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex flex-column h-100 py-1">
                                            <div class="d-flex justify-content-between align-items-end w-100 mb-3">
                                                <div class="text-start">
                                                    {{-- Quantity option temporarily disabled
                                                    <label class="small fw-semibold text-muted text-uppercase mb-2 d-block">Quantity</label>
                                                    <div class="input-group input-group-sm shadow-sm" style="max-width: 130px; height: 38px;">
                                                        <button class="btn btn-outline-secondary qty-btn px-3 border-end-0" type="button" data-action="minus" style="border-radius: 12px 0 0 12px; background: #fff;">-</button>
                                                        <input type="number" 
                                                               class="form-control quantity-input text-center fw-bold bg-white border-start-0 border-end-0 fs-6" 
                                                               value="{{ $cartItem->quantity }}" 
                                                               min="1" 
                                                               max="{{ $cartItem->cloth->sku }}"
                                                               data-cart-item-id="{{ $cartItem->id }}"
                                                               readonly
                                                               style="width: 45px; pointer-events: none;">
                                                        <button class="btn btn-outline-secondary qty-btn px-3 border-start-0" type="button" data-action="plus" style="border-radius: 0 12px 12px 0; background: #fff;">+</button>
                                                    </div>
                                                    --}}
                                                    <input type="hidden" class="quantity-input" value="1" data-cart-item-id="{{ $cartItem->id }}" data-original-value="1">
                                                </div>
                                                <div class="text-end">
                                                    <p class="text-muted x-small mb-1 text-uppercase fw-semibold">Subtotal</p>
                                                    <h3 class="fw-bold text-dark mb-0" style="letter-spacing: -0.5px;">
                                                        @if($cartItem->purchase_type === 'buy')
                                                            ₹{{ number_format($cartItem->total_selling_price) }}
                                                        @else
                                                            ₹{{ number_format($cartItem->total_rental_cost ?? ($cartItem->cloth->display_rent_price * $cartItem->quantity)) }}
                                                        @endif
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end w-100">
                                                <button class="btn btn-link text-danger btn-sm p-0 text-decoration-none remove-from-cart-btn fw-bold d-flex align-items-center gap-1" 
                                                        data-cart-item-id="{{ $cartItem->id }}">
                                                    <i class="bi bi-trash3"></i> <span>Remove</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="col-lg-4">
                    <!-- Cart Summary -->
                    <div class="card border-0 shadow rounded-4 overflow-hidden sticky-top" style="top: 100px; z-index: 10;">
                        <div class="card-header bg-dark text-white p-4 border-0">
                            <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                                <i class="bi bi-receipt"></i> Order Summary
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            @php
                                $rentalItems = $cartItems->where('purchase_type', '!=', 'buy');
                                $buyItems = $cartItems->where('purchase_type', 'buy');
                                
                                $rentalSubtotal = $rentalItems->sum(function($item) {
                                    return $item->total_rental_cost ?? ($item->cloth->display_rent_price * $item->quantity);
                                });
                                $buySubtotal = $buyItems->sum('total_selling_price');
                                $total = $rentalSubtotal + $buySubtotal;
                                $securityDeposit = $rentalItems->sum(function($item) { 
                                    return $item->cloth->security_deposit * $item->quantity; 
                                });
                            @endphp

                            <!-- Delivery Info -->
                            <div class="p-3 rounded-4 bg-light mb-4 border border-light-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small fw-bold text-uppercase">Deliver to</span>
                                    <button class="btn btn-link btn-sm p-0 text-decoration-none fw-bold" onclick="openAddressModal()">Change</button>
                                </div>
                                @if(Auth::user()->address)
                                    <div class="d-flex align-items-start mt-2">
                                        <i class="bi bi-geo-alt text-primary me-2" style="font-size: 1.1rem; line-height: 1.2;"></i>
                                        <p class="text-dark small mb-0" id="displayAddress" style="line-height: 1.5;">{{ Auth::user()->address }}</p>
                                    </div>
                                    <input type="hidden" id="finalDeliveryAddress" value="{{ Auth::user()->address }}">
                                @else
                                    <div class="d-flex align-items-center gap-2 text-danger small">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <span>No address found in profile</span>
                                    </div>
                                    <a href="{{ route('profile') }}" class="btn btn-sm btn-outline-danger w-100 mt-2 rounded-pill">Add Address</a>
                                    <input type="hidden" id="finalDeliveryAddress" value="">
                                @endif
                            </div>
                            
                            <div class="d-flex flex-column gap-3 mb-4">
                                @if($rentalItems->count() > 0)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Rental Subtotal</span>
                                        <span class="fw-bold subtotal-amount">₹{{ number_format($rentalSubtotal) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center text-primary">
                                        <span class="small d-flex align-items-center gap-1">
                                            <i class="bi bi-shield-check"></i> Security Deposit
                                        </span>
                                        <span class="fw-bold security-deposit-amount">₹{{ number_format($securityDeposit) }}</span>
                                    </div>
                                @endif
                                
                                @if($buyItems->count() > 0)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Purchase Subtotal</span>
                                        <span class="fw-bold text-success">₹{{ number_format($buySubtotal) }}</span>
                                    </div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-1">
                                    <span class="h5 fw-bold mb-0">Total Pay</span>
                                    <span class="h4 fw-bold text-dark total-amount mb-0">₹{{ number_format($total + $securityDeposit) }}</span>
                                </div>
                            </div>

                            <!-- Payment Options -->
                            <div class="mb-4">
                                <label class="small fw-bold text-muted text-uppercase mb-3 d-block">Select Payment Method</label>
                                <div class="payment-options d-flex gap-3">
                                    <div class="payment-option flex-fill">
                                        <input type="radio" class="btn-check" name="payment_method" id="payment_online" value="online" checked>
                                        <label class="btn btn-outline-light text-dark w-100 p-3 rounded-4 d-flex flex-column align-items-center justify-content-center border gap-2 shadow-sm" for="payment_online">
                                            <i class="bi bi-credit-card-2-front fs-4 text-primary"></i>
                                            <span class="fw-bold small">ONLINE</span>
                                        </label>
                                    </div>
                                    <div class="payment-option flex-fill">
                                        <input type="radio" class="btn-check" name="payment_method" id="payment_cod" value="cod">
                                        <label class="btn btn-outline-light text-dark w-100 p-3 rounded-4 d-flex flex-column align-items-center justify-content-center border gap-2 shadow-sm" for="payment_cod">
                                            <i class="bi bi-cash-stack fs-4 text-success"></i>
                                            <span class="fw-bold small">COD</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <button class="btn btn-sell w-100 py-3 rounded-4 fw-bold shadow-sm mb-3"
                                    id="checkoutBtn"
                                    data-create-url="{{ route('checkout.create') }}"
                                    data-verify-url="{{ route('checkout.verify') }}">
                                <i class="bi bi-lock-fill me-2"></i>
                                Place Order securely
                            </button>
                            
                            <a href="/" class="btn btn-link w-100 text-muted text-decoration-none small fw-semibold">
                                <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-4 py-md-5">
                <div class="mb-3 d-inline-block p-3 p-md-4 rounded-circle bg-light">
                    <i class="bi bi-cart-x text-muted" style="font-size: 3.5rem;"></i>
                </div>
                <h2 class="fw-bold text-dark h3 h2-md">Your Bag is Empty</h2>
                <p class="text-muted mx-auto mb-4" style="max-width: 400px; font-size: 0.9rem;">Looks like you haven't added anything to your bag yet. Explore our premium collection and start your fashion journey!</p>
                <a href="/" class="btn btn-sell px-5 rounded-pill shadow">
                    Start Browsing
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Address Change Modal -->
<div class="modal fade rounded-4" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4 pb-2">
                <h5 class="modal-title fw-bold" id="addressModalLabel">
                    <i class="bi bi-geo-alt-fill text-primary me-2"></i> Update Delivery Address
                </h5>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Please provide the complete address where you want your items delivered.</p>
                <div class="form-group mb-0">
                    <textarea id="modalAddressInput" class="form-control rounded-3 border-light-subtle bg-light" rows="4" placeholder="Enter your full address (House No, Street, Landmark, City, Pincode)"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-2">
                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sell rounded-pill px-4 fw-bold shadow-sm" onclick="saveCustomAddress()">
                    Update Address
                </button>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #FFA500;
    --primary-light: #fff8f0;
    --sell-gradient: linear-gradient(135deg, #FFA500 0%, #FF7F50 100%);
}

.btn-sell {
    background: var(--sell-gradient);
    border: none;
    color: white;
}

.btn-sell:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 165, 0, 0.4);
    color: white;
}

.btn-sell:disabled {
    opacity: 0.7;
    background: #ccc;
}

.payment-options .btn-check {
    position: absolute;
    clip: rect(0,0,0,0);
    pointer-events: none;
}

.qty-btn {
    width: 40px !important;
    height: 38px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background-color: #fff !important;
    color: #333 !important;
    border: 1px solid #dee2e6 !important;
    padding: 0 !important;
    font-size: 1.2rem !important;
}

.qty-btn:hover {
    background-color: #f8f9fa !important;
    color: var(--primary-color) !important;
}

.quantity-input {
    height: 38px !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-color: #dee2e6 !important;
    box-shadow: none !important;
}

.cart-item {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.cart-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}

.quantity-input {
    border: 1.5px solid #e2e8f0;
}

.quantity-input:focus {
    border-color: var(--primary-color);
    box-shadow: none;
}

.payment-options .btn-check:checked + .btn {
    background-color: var(--primary-light);
    border-color: var(--primary-color) !important;
    color: var(--primary-color) !important;
}

.payment-options .btn-check + .btn .check-icon {
    display: none;
}

.payment-options .btn-check:checked + .btn .check-icon {
    display: block;
}

.payment-options .btn:hover {
    background-color: #f8fafc;
}

.sticky-top {
    transition: top 0.3s ease;
}

@media (max-width: 991.98px) {
    .sticky-top {
        position: relative !important;
        top: 0 !important;
    }
}

    .container.py-5 {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }
    .cart-header-icon {
        padding: 0.6rem !important;
        border-radius: 12px !important;
    }
    .cart-header-icon i {
        font-size: 1.25rem !important;
    }
    h1.h2 {
        font-size: 1.5rem !important;
        line-height: 1.2 !important;
    }
    .cart-container p.text-muted {
        font-size: 0.75rem !important;
    }
    .cart-item-img {
        height: 280px !important;
        margin-bottom: 0.75rem;
        width: 100% !important;
        object-fit: cover !important;
    }
    .cart-item .card-body {
        padding: 1rem !important;
    }
    .cart-item h4 {
        font-size: 1.2rem !important;
        margin-top: 0.5rem !important;
        line-height: 1.3;
    }
}

.cart-header-icon i {
    font-size: 1.75rem; /* Default/Desktop size */
}
</style>
@endsection

@section('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
$(document).ready(function() {
    // Custom quantity selector logic
    $('.qty-btn').click(function() {
        const $input = $(this).siblings('.quantity-input');
        const action = $(this).data('action');
        let val = parseInt($input.val());
        const min = parseInt($input.attr('min'));
        const max = parseInt($input.attr('max'));

        if (action === 'plus' && val < max) {
            $input.val(val + 1).trigger('change');
        } else if (action === 'minus' && val > min) {
            $input.val(val - 1).trigger('change');
        }
    });

    // Update quantity functionality
    $('.quantity-input').change(function() {
        const cartItemId = $(this).data('cart-item-id');
        const quantity = parseInt($(this).val());
        const maxQuantity = parseInt($(this).attr('max'));
        const $input = $(this);
        const $item = $(this).closest('.cart-item');
        
        if (quantity > maxQuantity) {
            showAlert('warning', 'Requested quantity exceeds available stock (' + maxQuantity + ')');
            $(this).val(maxQuantity);
            return;
        }

        // Update item total immediately
        updateItemTotal(cartItemId);
        
        // Update cart totals
        updateCartTotals();
        
        $.ajax({
            url: '/cart/update-quantity',
            type: 'POST',
            data: {
                cart_item_id: cartItemId,
                quantity: quantity,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update cart count in header
                    updateCartCount(response.cartCount);
                    showAlert('success', response.message);
                } else {
                    showAlert('danger', response.message);
                    // Reset to original value
                    $input.val($input.data('original-value'));
                    // Recalculate totals
                    updateCartTotals();
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred. Please try again.';
                showAlert('danger', message);
                // Reset to original value
                $input.val($input.data('original-value'));
                // Recalculate totals
                updateCartTotals();
            }
        });
    });

    // Remove from cart functionality
    $('.remove-from-cart-btn').click(function(e) {
        e.preventDefault();
        
        const cartItemId = $(this).data('cart-item-id');
        const $item = $(this).closest('.cart-item');
        
        if (confirm('Are you sure you want to remove this item from cart?')) {
            $.ajax({
                url: '/cart/remove',
                type: 'POST',
                data: {
                    cart_item_id: cartItemId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Update cart count
                        updateCartCount(response.cartCount);
                        
                        // Remove item from DOM
                        $item.fadeOut(function() {
                            $(this).remove();
                            
                            // Update cart totals
                            updateCartTotals();
                            
                            // Check if cart is empty
                            if ($('.cart-item').length === 0) {
                                $('.cart-container').html('<div class="text-center py-5"><h5>Your cart is empty</h5><a href="/" class="btn btn-warning">Continue Shopping</a></div>');
                            }
                        });
                        
                        showAlert('success', response.message);
                    } else {
                         showAlert('danger', response.message);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred. Please try again.';
                    showAlert('danger', message);
                }
            });
        }
    });

    // Initialize quantity inputs
    $('.quantity-input').each(function() {
        $(this).data('original-value', $(this).val());
    });
});

// Update item total price
function updateItemTotal(cartItemId) {
    const $item = $(`.cart-item[data-cart-item-id="${cartItemId}"]`);
    const quantity = parseInt($item.find('.quantity-input').val());
    
    // Check if this is a buy item
    const purchaseInfo = $item.find('.purchase-info');
    if (purchaseInfo.length > 0) {
        const totalCostText = purchaseInfo.find('.item-price').text();
        const totalCost = parseFloat(totalCostText.replace(/[^\d.]/g, ''));
        const total = totalCost * quantity;
        $item.find('.item-total').text('₹' + total.toFixed(2));
    } else {
        // Use rental cost if available, otherwise calculate from daily rate
        const rentalInfo = $item.find('.rental-info');
        if (rentalInfo.length > 0) {
            const totalCostText = rentalInfo.find('p:last').text();
            const totalCost = parseFloat(totalCostText.replace(/[^\d.]/g, ''));
            const total = totalCost * quantity;
            $item.find('.item-total').text('₹' + total.toFixed(2));
        } else {
            const price = parseFloat($item.find('.item-price').data('price'));
            const total = quantity * price;
            $item.find('.item-total').text('₹' + total.toFixed(2));
        }
    }
}

// Update cart totals
function updateCartTotals() {
    let rentalCost = 0;
    let buyCost = 0;
    let securityDeposit = 0;
    
    $('.cart-item').each(function() {
        const $item = $(this);
        const quantity = parseInt($item.find('.quantity-input').val());
        const deposit = parseFloat($item.find('.item-price').data('deposit') || 0);
        
        // Check if this is a buy item
        const purchaseInfo = $item.find('.purchase-info');
        if (purchaseInfo.length > 0) {
            const totalCostText = purchaseInfo.find('.item-price').text();
            const totalCost = parseFloat(totalCostText.replace(/[^\d.]/g, ''));
            buyCost += totalCost * quantity;
        } else {
            // Use rental cost if available, otherwise calculate from daily rate
            const rentalInfo = $item.find('.rental-info');
            if (rentalInfo.length > 0) {
                const totalCostText = rentalInfo.find('p:last').text();
                const totalCost = parseFloat(totalCostText.replace(/[^\d.]/g, ''));
                rentalCost += totalCost * quantity;
            } else {
                const price = parseFloat($item.find('.item-price').data('price'));
                rentalCost += price * quantity;
            }
            
            securityDeposit += deposit * quantity;
        }
    });
    
    const total = rentalCost + buyCost + securityDeposit;
    
    // Update display
    if (rentalCost > 0) {
        $('.subtotal-amount').text('₹' + rentalCost.toFixed(2));
        $('.security-deposit-amount').text('₹' + securityDeposit.toFixed(2));
    }
    if (buyCost > 0) {
        // If there's no purchase cost display, create one
        if ($('.purchase-cost-display').length === 0) {
            $('.subtotal-amount').after('<div class="d-flex justify-content-between mb-2"><span>Purchase Cost:</span><span class="fw-bold text-success purchase-cost-display">₹' + buyCost.toFixed(2) + '</span></div>');
        } else {
            $('.purchase-cost-display').text('₹' + buyCost.toFixed(2));
        }
    }
    $('.total-amount').text('₹' + total.toFixed(2));
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
// Show alert message
function showAlert(type, message) {
    const alertHtml = `
        <div class="premium-toast alert-${type}" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; background: white; padding: 16px 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-left: 5px solid ${type === 'success' ? '#10b981' : '#ef4444'}; display: none;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="bi bi-${type === 'success' ? 'check-circle-fill text-success' : 'exclamation-circle-fill text-danger'} me-3" style="font-size: 1.25rem;"></i>
                    <span class="fw-bold" style="font-size: 0.95rem; color: #1e293b;">${message}</span>
                </div>
                <button type="button" class="btn-close border-0 bg-transparent ms-3" onclick="$(this).closest('.premium-toast').fadeOut(400, function() { $(this).remove(); });" style="font-size: 0.8rem; opacity: 0.5;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    `;

    // Remove existing alerts
    $('.premium-toast').remove();

    // Add new alert
    const $alert = $(alertHtml);
    $('body').append($alert);
    $alert.fadeIn(400);

    // Auto-hide after 3 seconds
    setTimeout(function () {
        $alert.fadeOut(400, function() {
            $(this).remove();
        });
    }, 4000);
}

const checkoutBtn = document.getElementById('checkoutBtn');
if (checkoutBtn) {
    const createUrl = checkoutBtn.dataset.createUrl;
    const verifyUrl = checkoutBtn.dataset.verifyUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    checkoutBtn.addEventListener('click', async function() {
        const address = document.getElementById('finalDeliveryAddress').value.trim();
        
        if (!address) {
            alert('Please add a delivery address first.');
            return;
        }

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const btnText = paymentMethod === 'cod' ? 'Placing Order...' : 'Preparing Payment...';
        
        toggleCheckoutButton(true, btnText);
        
        try {
            const response = await fetch(createUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    payment_method: paymentMethod,
                    delivery_address: address
                })
            });
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to process order.');
            }

            if (paymentMethod === 'cod') {
                // Determine redirect URL properly
                console.log('COD Order Placed:', data);
                if (data.redirect) {
                     window.location.href = data.redirect;
                } else {
                     // Fallback if redirect is missing
                     showAlert('success', 'Order placed successfully!');
                     window.location.href = '/orders';
                }
            } else {
                launchRazorpayCheckout(data, verifyUrl, csrfToken);
            }

        } catch (error) {
            toggleCheckoutButton(false);
            showAlert('danger', error.message);
        }
    });
}

function launchRazorpayCheckout(data, verifyUrl, csrfToken) {
    const options = {
        key: data.razorpay.key,
        amount: data.order.amount_paise,
        currency: data.order.currency,
        name: 'Get Ready',
        description: `Order #${data.order.id}`,
        handler: function (response) {
            const paymentId = response.razorpay_payment_id || ('pay_' + Date.now());
            verifyPayment(data.order.id, paymentId, verifyUrl, csrfToken);
        },
        modal: {
            ondismiss: function () {
                toggleCheckoutButton(false);
            }
        },
        prefill: {
            name: data.customer.name || '',
            email: data.customer.email || '',
            contact: data.customer.contact || ''
        },
        theme: {
            color: '#4338ca'
        }
    };

    const rzp = new Razorpay(options);
    rzp.on('payment.failed', function (response){
        toggleCheckoutButton(false);
        showAlert('danger', response.error && response.error.description ? response.error.description : 'Payment failed. Please try again.');
    });
    rzp.open();
}

async function verifyPayment(orderId, paymentId, verifyUrl, csrfToken) {
    try {
        const response = await fetch(verifyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                order_id: orderId,
                razorpay_payment_id: paymentId
            })
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Unable to verify payment.');
        }
        showAlert('success', data.message || 'Payment successful!');
        if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            window.location.reload();
        }
    } catch (error) {
        toggleCheckoutButton(false);
        showAlert('danger', error.message);
    }
}

function toggleCheckoutButton(disabled, text) {
    if (!checkoutBtn) {
        return;
    }
    checkoutBtn.disabled = disabled;
    checkoutBtn.innerHTML = disabled
        ? `<span class="spinner-border spinner-border-sm me-2"></span>${text}`
        : '<i class="bi bi-credit-card me-2"></i>Place Order';
}

// Address Modal Logic
function openAddressModal() {
    // Populate modal with current selected address
    const currentAddr = $('#finalDeliveryAddress').val();
    $('#modalAddressInput').val(currentAddr);
    $('#addressModal').modal('show');
}

function saveCustomAddress() {
    const newAddr = $('#modalAddressInput').val().trim();
    if (!newAddr) {
        alert('Address cannot be empty.');
        return;
    }
    
    // Update Hidden Input and Display
    $('#finalDeliveryAddress').val(newAddr);
    $('#displayAddress').text(newAddr);
    
    // Check if it's different from profile (optional visual cue)
    // $('#displayAddress').addClass('text-primary'); 

    $('#addressModal').modal('hide');
}
</script>
@endsection
