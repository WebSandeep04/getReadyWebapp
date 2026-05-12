// Cart functionality
$(document).ready(function () {
    // Load cart items on page load to check rented status
    loadCartItems();

    // Refresh mini-cart on hover
    $('.cart-dropdown-wrapper').hover(function() {
        if ($(this).find('#cartDropdown').length > 0) {
            loadCartItems();
        }
    });

    // Skip generic interaction listeners on the full cart page to avoid conflicts with specialized scripts in cart.blade.php
    if (window.location.pathname === '/cart') {
        return;
    }

    // Add to cart functionality
    $('.add-to-cart-btn').click(function (e) {
        e.preventDefault();

        const clothId = $(this).data('cloth-id');
        const $btn = $(this);
        const originalText = $btn.text();

        // Show loading state
        $btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: '/cart/add',
            type: 'POST',
            data: {
                cloth_id: clothId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    // Update cart count
                    updateCartCount(response.cartCount);

                    // Show success message
                    showAlert('success', response.message);

                    // Update all buttons for this item to "RENTED"
                    updateAllRentButtons(clothId, true);

                    // Reload cart items to update the list
                    loadCartItems();
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    // User not logged in, redirect to login with intended redirect
                    window.location.href = '/login?redirect=' + encodeURIComponent(window.location.href);
                } else {
                    showAlert('danger', 'An error occurred. Please try again.');
                }
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    // Remove from cart functionality
    $('.remove-from-cart-btn').click(function (e) {
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
                success: function (response) {
                    if (response.success) {
                        // Update cart count
                        updateCartCount(response.cartCount);

                        // Get the cloth ID from the removed item
                        const clothId = $item.data('cloth-id');

                        // Update all buttons for this item back to "RENT NOW"
                        updateAllRentButtons(clothId, false);

                        // Reload cart items to update the list
                        loadCartItems();

                        // Remove item from DOM
                        $item.fadeOut(function () {
                            $(this).remove();

                            // Check if cart is empty
                            if ($('.cart-item').length === 0) {
                                $('.cart-container').html('<div class="text-center py-5"><h5>Your cart is empty</h5><a href="/" class="btn btn-warning">Continue Shopping</a></div>');
                            }
                        });

                        showAlert('success', response.message);
                    }
                },
                error: function () {
                    showAlert('danger', 'An error occurred. Please try again.');
                }
            });
        }
    });

    // Update quantity functionality
    $('.quantity-input').change(function () {
        const cartItemId = $(this).data('cart-item-id');
        const quantity = $(this).val();
        const $input = $(this);

        $.ajax({
            url: '/cart/update-quantity',
            type: 'POST',
            data: {
                cart_item_id: cartItemId,
                quantity: quantity,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    // Update cart count
                    updateCartCount(response.cartCount);

                    // Update total price for this item
                    updateItemTotal(cartItemId);

                    showAlert('success', response.message);
                }
            },
            error: function () {
                showAlert('danger', 'An error occurred. Please try again.');
                // Reset to original value
                $input.val($input.data('original-value'));
            }
        });
    });

    // Initialize quantity inputs
    $('.quantity-input').each(function () {
        $(this).data('original-value', $(this).val());
    });
});

// Load cart items and check rented status
function loadCartItems() {
    $.ajax({
        url: '/cart/items',
        type: 'GET',
        success: function (response) {
            if (response.cartItems) {
                window.cartItems = response.cartItems;
                checkRentedItems();
                renderMiniCart(response.cartItems, response.formatted_subtotal);
            }
        },
        error: function () {
            // If error, assume no items in cart
            window.cartItems = [];
            renderMiniCart([], '₹0');
        }
    });
}

// Render the mini-cart dropdown content
function renderMiniCart(items, subtotal) {
    const $container = $('#mini-cart-items-container');
    const $subtotal = $('#mini-cart-subtotal');
    const $badge = $('#mini-cart-count-badge');
    
    if (!$container.length) return;

    $subtotal.text(subtotal);
    $badge.text(items.length + ' Items');

    if (items.length === 0) {
        $container.html('<div class="text-center py-5"><i class="bi bi-bag-x text-muted mb-2 d-block fs-3"></i><p class="text-muted small mb-0">Your bag is empty</p></div>');
        return;
    }

    let html = '';
    items.forEach(item => {
        html += `
            <div class="mini-cart-item d-flex align-items-start py-3 px-3 border-bottom">
                <div class="mini-cart-img-wrapper mr-3">
                    <img src="${item.image}" class="mini-cart-img" alt="${item.title}" style="width: 60px; height: 80px; object-fit: cover; border-radius: 8px;">
                </div>
                <div class="mini-cart-details flex-grow-1">
                    <h6 class="text-dark fw-bold mb-1" style="font-size: 0.9rem; line-height: 1.3;">${item.title}</h6>
                    <p class="text-muted mb-2" style="font-size: 0.75rem;">${item.purchase_type.toUpperCase()} · Size ${item.size}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">${item.quantity} × <span class="text-dark fw-bold">${item.formatted_price}</span></span>
                    </div>
                </div>
            </div>
        `;
    });
    $container.html(html);
}

// Update all rent buttons for a specific item
function updateAllRentButtons(clothId, isRented) {
    const buttons = $(`.add-to-cart-btn[data-cloth-id="${clothId}"], #productRentBtn[data-cloth-id="${clothId}"]`);

    buttons.each(function () {
        const $btn = $(this);

        if (isRented) {
            $btn.html('<i class="bi bi-check-circle-fill"></i> RENTED')
                .addClass('rented-button')
                .removeClass('rent-button btn-warning btn-success')
                .prop('disabled', true)
                .attr('title', 'Already in cart');
            
            // Also disable Buy button on the same card if it exists
            const $buyBtn = $btn.closest('.card').find('.add-to-cart-buy-btn[data-cloth-id="' + clothId + '"]');
            $buyBtn.prop('disabled', true);
        } else {
            $btn.html('<i class="bi bi-cart-plus me-2"></i>RENT NOW')
                .removeClass('btn-success')
                .addClass('btn-warning')
                .prop('disabled', false)
                .removeAttr('title');
            
            // Re-enable Buy button if not purchased
            const $buyBtn = $btn.closest('.card').find('.add-to-cart-buy-btn[data-cloth-id="' + clothId + '"]');
            if (!$buyBtn.data('purchased')) {
                $buyBtn.prop('disabled', false);
            }
        }
    });
}

// Update all buy buttons for a specific item
function updateAllBuyButtons(clothId, isPurchased) {
    const $buttons = $('.add-to-cart-buy-btn[data-cloth-id="' + clothId + '"]');
    $buttons.each(function() {
        const $btn = $(this);
        if (isPurchased) {
            $btn.html('<i class="bi bi-check-circle-fill"></i> PURCHASED')
                .addClass('purchased-button')
                .removeClass('buy-button btn-success btn-outline-dark')
                .prop('disabled', true)
                .attr('title', 'Already in cart')
                .data('purchased', true);
            
            // Also disable Rent button on the same card
            const $rentBtn = $btn.closest('.card').find('.add-to-cart-btn[data-cloth-id="' + clothId + '"]');
            $rentBtn.prop('disabled', true).text('RENTED');
        } else {
            $btn.html('BUY NOW')
                .removeClass('btn-success')
                .addClass('btn-outline-dark')
                .prop('disabled', false)
                .removeAttr('title')
                .data('purchased', false);
        }
    });
}

// Check which items are already in cart and update buttons
function checkRentedItems() {
    if (!window.cartItems) return;

    // First reset all buttons
    $('.add-to-cart-btn').each(function() {
        const id = $(this).data('cloth-id');
        updateAllRentButtons(id, false);
    });
    $('.add-to-cart-buy-btn').each(function() {
        const id = $(this).data('cloth-id');
        updateAllBuyButtons(id, false);
    });

    window.cartItems.forEach(function (item) {
        if (item.purchase_type === 'buy') {
            updateAllBuyButtons(item.cloth_id, true);
        } else {
            updateAllRentButtons(item.cloth_id, true);
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

// Update item total price
function updateItemTotal(cartItemId) {
    const $item = $(`.cart-item[data-cart-item-id="${cartItemId}"]`);
    const quantity = $item.find('.quantity-input').val();
    const price = parseFloat($item.find('.item-price').data('price'));
    const total = quantity * price;

    $item.find('.item-total').text('₹' + total.toFixed(2));
}

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