// Cart functionality
$(document).ready(function () {
    // Load cart items on page load to check rented status
    loadCartItems();

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
            }
        },
        error: function () {
            // If error, assume no items in cart
            window.cartItems = [];
        }
    });
}

// Update all rent buttons for a specific item
function updateAllRentButtons(clothId, isRented) {
    const buttons = $(`.add-to-cart-btn[data-cloth-id="${clothId}"]`);

    buttons.each(function () {
        const $btn = $(this);

        if (isRented) {
            $btn.text('RENTED')
                .addClass('btn-success')
                .removeClass('btn-warning')
                .prop('disabled', true)
                .attr('title', 'Already in cart');
        } else {
            $btn.html('<i class="bi bi-cart-plus me-2"></i>RENT NOW')
                .removeClass('btn-success')
                .addClass('btn-warning')
                .prop('disabled', false)
                .removeAttr('title');
        }
    });
}

// Check which items are already in cart and update buttons
function checkRentedItems() {
    if (!window.cartItems) return;

    window.cartItems.forEach(function (item) {
        updateAllRentButtons(item.cloth_id, true);
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
    setTimeout(function () {
        $('.alert').fadeOut();
    }, 3000);
} 