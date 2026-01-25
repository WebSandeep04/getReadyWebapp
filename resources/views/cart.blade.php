@extends('layouts.app')

@section('title', 'Get Ready - Cart')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="text-warning mb-4">
                <i class="bi bi-cart3 me-2"></i>
                Shopping Cart
            </h2>
            @if(request('payment') === 'success')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Payment received! We’re processing your order now.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="cart-container">
        @if(Auth::check() && $cartItems->count() > 0)
            <div class="row">
                <div class="col-lg-8">
                    <!-- Cart Items -->
                    @foreach($cartItems as $cartItem)
                        <div class="card mb-3 cart-item" data-cart-item-id="{{ $cartItem->id }}">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        @if($cartItem->cloth->images->count() > 0)
                                            <img src="{{ asset('storage/' . $cartItem->cloth->images->first()->image_path) }}" 
                                                 alt="{{ $cartItem->cloth->title }}" class="img-fluid rounded">
                                        @else
                                            <img src="{{ asset('images/1.jpg') }}" alt="{{ $cartItem->cloth->title }}" class="img-fluid rounded">
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="card-title">{{ $cartItem->cloth->title }}</h5>
                                        <p class="card-text text-muted">
                                            <small>Size: {{ $cartItem->cloth->sizeRef->name ?? 'Unknown' }}</small><br>
                                            <small>Condition: {{ $cartItem->cloth->conditionRef->name ?? 'Unknown' }}</small>
                                        </p>
                                        @if($cartItem->purchase_type === 'buy')
                                            <div class="purchase-info">
                                                <p class="text-success fw-bold item-price">
                                                    <i class="bi bi-bag-check me-1"></i>
                                                    Purchase Price: ₹{{ number_format($cartItem->total_purchase_cost) }}
                                                </p>
                                                <div class="alert alert-success">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    <small>This item will be purchased, not rented.</small>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-warning fw-bold item-price" data-price="{{ $cartItem->cloth->rent_price }}" data-deposit="{{ $cartItem->cloth->security_deposit }}">
                                                ₹{{ number_format($cartItem->cloth->rent_price) }} <small class="text-muted">(for 4 days)</small>
                                            </p>
                                            
                                            @if($cartItem->rental_start_date && $cartItem->rental_end_date)
                                                <div class="rental-info">
                                                    <p class="mb-1">
                                                        <strong>Rental Period:</strong><br>
                                                        <small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($cartItem->rental_start_date)->format('d/m/Y') }} - 
                                                            {{ \Carbon\Carbon::parse($cartItem->rental_end_date)->format('d/m/Y') }}
                                                        </small>
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Duration:</strong> {{ $cartItem->rental_days }} days
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Total Cost:</strong> ₹{{ number_format($cartItem->total_rental_cost) }}
                                                    </p>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex flex-column align-items-end">
                                            <div class="mb-2">
                                                <label for="quantity-{{ $cartItem->id }}" class="form-label">Quantity:</label>
                                                <input type="number" 
                                                       id="quantity-{{ $cartItem->id }}" 
                                                       class="form-control quantity-input" 
                                                       value="{{ $cartItem->quantity }}" 
                                                       min="1" 
                                                       max="{{ $cartItem->cloth->sku }}"
                                                       data-cart-item-id="{{ $cartItem->id }}">
                                            </div>
                                                                                         <p class="fw-bold item-total">
                                                 @if($cartItem->purchase_type === 'buy')
                                                     ₹{{ number_format($cartItem->total_purchase_cost) }}
                                                 @else
                                                     ₹{{ number_format($cartItem->total_rental_cost ?? ($cartItem->cloth->rent_price * $cartItem->quantity)) }}
                                                 @endif
                                             </p>
                                             <small class="text-muted">
                                                 @if($cartItem->purchase_type === 'buy')
                                                     (Purchase)
                                                 @elseif($cartItem->rental_days)
                                                     ({{ $cartItem->rental_days }} days)
                                                 @else
                                                     (per day)
                                                 @endif
                                             </small>
                                            <button class="btn btn-outline-danger btn-sm remove-from-cart-btn" 
                                                    data-cart-item-id="{{ $cartItem->id }}">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="col-lg-4">
                    <!-- Cart Summary -->
                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">Cart Summary</h5>
                        </div>
                        <div class="card-body">
                                                         @php
                                $rentalItems = $cartItems->where('purchase_type', '!=', 'buy');
                                $buyItems = $cartItems->where('purchase_type', 'buy');
                                
                                $rentalSubtotal = $rentalItems->sum(function($item) {
                                    return $item->total_rental_cost ?? ($item->cloth->rent_price * $item->quantity);
                                });
                                $buySubtotal = $buyItems->sum('total_purchase_cost');
                                $total = $rentalSubtotal + $buySubtotal;
                                $securityDeposit = $rentalItems->sum(function($item) { 
                                    return $item->cloth->security_deposit * $item->quantity; 
                                });
                            @endphp

                            <!-- Address Check -->
                            <div class="mb-3 border-bottom pb-3">
                                <span class="d-block fw-bold mb-1">Delivery Address:</span>
                                @if(Auth::user()->address)
                                    <p class="text-muted small mb-0" id="userAddress">{{ Auth::user()->address }}</p>
                                    <a href="{{ route('profile') }}" class="small text-primary text-decoration-none">Change</a>
                                @else
                                    <div class="alert alert-danger small mb-1 p-2">
                                        <i class="bi bi-geo-alt-fill me-1"></i> No address found!
                                    </div>
                                    <a href="{{ route('profile') }}" class="btn btn-sm btn-outline-danger w-100 mt-1">
                                         + Add Address
                                    </a>
                                @endif
                            </div>
                            
                            @if($rentalItems->count() > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Rental Cost:</span>
                                    <span class="fw-bold subtotal-amount">₹{{ number_format($rentalSubtotal) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Security Deposit:</span>
                                    <span class="fw-bold security-deposit-amount">₹{{ number_format($securityDeposit) }}</span>
                                </div>
                            @endif
                            
                            @if($buyItems->count() > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Purchase Cost:</span>
                                    <span class="fw-bold text-success">₹{{ number_format($buySubtotal) }}</span>
                                </div>
                            @endif
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="h5">Total:</span>
                                <span class="h5 text-warning total-amount">₹{{ number_format($total + $securityDeposit) }}</span>
                            </div>

                            <!-- Payment Method Selection -->
                            <div class="mb-3">
                                <label class="block text-gray-700 text-sm font-bold mb-2 fw-bold">Payment Method</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_online" value="online" checked>
                                    <label class="form-check-label" for="payment_online">
                                        Online Payment (Razorpay)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod">
                                    <label class="form-check-label" for="payment_cod">
                                        Cash on Delivery (COD)
                                    </label>
                                </div>
                            </div>
                            
                            <button class="btn btn-warning w-100 mb-2"
                                    id="checkoutBtn"
                                    data-has-address="{{ Auth::user()->address ? 'true' : 'false' }}"
                                    data-create-url="{{ route('checkout.create') }}"
                                    data-verify-url="{{ route('checkout.verify') }}">
                                <i class="bi bi-credit-card me-2"></i>
                                Place Order
                            </button>
                            
                            <a href="/" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left me-2"></i>
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-cart3 text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Your cart is empty</h4>
                <p class="text-muted">Add some items to your cart to get started!</p>
                <a href="/" class="btn btn-warning">
                    <i class="bi bi-arrow-left me-2"></i>
                    Continue Shopping
                </a>
            </div>
        @endif
    </div>
</div>

<style>
.cart-item {
    transition: all 0.3s ease;
}

.cart-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.quantity-input {
    width: 80px;
}

.item-total {
    color: #ffc107;
}

.rental-info {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
    border-left: 3px solid #ffc107;
}

.rental-info p {
    margin-bottom: 5px;
}

.rental-info strong {
    color: #495057;
}

.purchase-info {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
    border-left: 3px solid #28a745;
}

.purchase-info p {
    margin-bottom: 5px;
}

.purchase-info strong {
    color: #495057;
}
</style>
@endsection

@section('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
$(document).ready(function() {
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
    // Check if alert container exists, if not create it
    if ($('#alert-container').length === 0) {
        $('body').append('<div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 1050; min-width: 300px; max-width: 400px;"></div>');
    }

    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Add new alert
    $('#alert-container').append(alertHtml);
    
    console.log('Alert:', type, message);
    
    // Auto-hide after 3 seconds
    setTimeout(function() {
        $('#alert-container .alert').first().fadeOut(function() {
            $(this).remove();
        });
    }, 4000);
}

const checkoutBtn = document.getElementById('checkoutBtn');
if (checkoutBtn) {
    const createUrl = checkoutBtn.dataset.createUrl;
    const verifyUrl = checkoutBtn.dataset.verifyUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const hasAddress = checkoutBtn.dataset.hasAddress === 'true';

    checkoutBtn.addEventListener('click', async function() {
        if (!hasAddress) {
            if(confirm('You need to add a delivery address first. Go to Profile?')) {
                window.location.href = "{{ route('profile') }}";
            }
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
                    payment_method: paymentMethod
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
</script>
@endsection
