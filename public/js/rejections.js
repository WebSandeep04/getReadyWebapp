// Rejection popup functionality
$(document).ready(function () {
    // Load rejected items on page load
    loadRejectedItems();

    // Load rejected items on hover of the wrapper to ensure fresh data
    $('.cart-dropdown-wrapper').hover(function() {
        if ($(this).find('#rejectionDropdown').length > 0) {
            loadRejectedItems();
        }
    });
});

/**
 * Load rejected items via AJAX
 */
function loadRejectedItems() {
    $.ajax({
        url: '/rejections-list',
        type: 'GET',
        success: function (response) {
            if (response.rejectedItems) {
                renderMiniRejections(response.rejectedItems);
                updateRejectionCount(response.count);
            }
        },
        error: function () {
            renderMiniRejections([]);
        }
    });
}

/**
 * Render the mini-rejection dropdown content
 */
function renderMiniRejections(items) {
    const $container = $('#mini-rejection-items-container');
    const $badge = $('#mini-rejection-count-badge');
    
    if (!$container.length) return;

    $badge.text(items.length + ' Items');

    if (items.length === 0) {
        $container.html('<div class="text-center py-5"><i class="bi bi-check-circle text-muted mb-2 d-block fs-3"></i><p class="text-muted small mb-0">No rejected items</p></div>');
        return;
    }

    let html = '';
    items.forEach(item => {
        html += `
            <a href="${item.url}" class="mini-cart-item d-flex align-items-start py-3 px-3 border-bottom text-decoration-none">
                <div class="mini-cart-img-wrapper mr-3">
                    <img src="${item.image}" class="mini-cart-img" alt="${item.title}">
                </div>
                <div class="mini-cart-details flex-grow-1">
                    <h6 class="text-dark fw-bold mb-1">${item.title}</h6>
                    <p class="text-muted mb-1" style="font-size: 0.75rem;">${item.category}</p>
                    <p class="text-danger mb-1 fw-bold" style="font-size: 0.75rem; line-height: 1.2;">Reason: ${item.reason}</p>
                    <span class="text-muted extra-small">${item.rejected_at}</span>
                </div>
            </a>
        `;
    });
    $container.html(html);
}

/**
 * Update rejection count badge in header
 */
function updateRejectionCount(count) {
    const $rejectionCount = $('#rejection-count');
    if ($rejectionCount.length > 0) {
        $rejectionCount.text(count);
        if (count > 0) {
            $rejectionCount.show();
        } else {
            $rejectionCount.hide();
        }
    }
}
