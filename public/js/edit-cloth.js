// Global functions
function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert" style="position: fixed; top: 100px; right: 20px; z-index: 9999; max-width: 400px;">
            <div class="d-flex align-items-center">
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2 fs-5"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    // Remove any existing alerts
    $('.alert-fixed-msg').remove();
    
    const $alert = $(alertHtml).addClass('alert-fixed-msg');
    $('body').append($alert);

    // Auto-dismiss after 5 seconds
    setTimeout(function () {
        $alert.fadeOut(300, function () {
            $(this).remove();
        });
    }, 5000);
}

function uploadImages(files) {
    const formData = new FormData();
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    formData.append('_method', 'PUT');

    for (let i = 0; i < files.length; i++) {
        formData.append('images[]', files[i]);
    }

    const $btn = $('.btn-premium-save');
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Uploading...');

    $.ajax({
        url: window.editClothUpdateUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.success) {
                showAlert('Gallery updated successfully!', 'success');
                location.reload();
            }
        },
        error: function (xhr, status, error) {
            showAlert('Failed to upload images.', 'danger');
        },
        complete: function() {
            $btn.prop('disabled', false).html(originalHtml);
        }
    });
}

function removeImage(imageId) {
    if (confirm('Are you sure you want to remove this image?')) {
        $.ajax({
            url: `/listed-clothes/images/${imageId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    $(`.edit-image-item[data-image-id="${imageId}"]`).fadeOut(300, function() {
                        $(this).remove();
                        // Check if we should show the add button
                        if ($('.edit-image-item').length < 4) {
                            $('#upload-placeholder').fadeIn(300);
                        }
                    });
                    showAlert('Image removed successfully!', 'success');
                }
            },
            error: function (xhr, status, error) {
                showAlert('Error removing image.', 'danger');
            }
        });
    }
}

// Handle purchase checkbox functionality
document.addEventListener('DOMContentLoaded', function () {
    const isPurchasedCheckbox = document.getElementById('is_purchased');
    const purchaseValueSection = document.getElementById('selling_price_section');
    const purchaseValueInput = document.querySelector('input[name="selling_price"]');

    const mrpInput = document.querySelector('input[name="mrp"]');
    const rentPriceInput = document.querySelector('input[name="rent_price"]');

    function checkAndShowRentSuggestion() {
        if (!mrpInput || !rentPriceInput) return;

        const mrp = parseFloat(mrpInput.value) || 0;
        const rentPrice = parseFloat(rentPriceInput.value) || 0;
        const sellingPrice = purchaseValueInput ? (parseFloat(purchaseValueInput.value) || 0) : 0;

        if (mrp > 0) {
            const maxRent = mrp * 0.2; // 20% of MRP

            // Rent Price Validation
            if (rentPrice > maxRent) {
                rentPriceInput.style.borderColor = '#ef4444';
            } else {
                rentPriceInput.style.borderColor = '#e2e8f0';
            }

            // Selling Price Validation
            if (purchaseValueInput && sellingPrice > mrp) {
                purchaseValueInput.style.borderColor = '#ef4444';
            } else if(purchaseValueInput) {
                purchaseValueInput.style.borderColor = '#e2e8f0';
            }
        }
    }

    if (mrpInput) mrpInput.addEventListener('input', checkAndShowRentSuggestion);
    if (purchaseValueInput) purchaseValueInput.addEventListener('input', checkAndShowRentSuggestion);
    if (rentPriceInput) {
        rentPriceInput.addEventListener('input', checkAndShowRentSuggestion);
        rentPriceInput.addEventListener('input', function () {
            const securityDepositInput = document.querySelector('input[name="security_deposit"]');
            if (securityDepositInput) {
                securityDepositInput.value = this.value;
            }
        });
    }

    // Initial check
    checkAndShowRentSuggestion();
});

// Availability block counters
let availableCounter = window.availableCounter || 0;
let blockedCounter = window.blockedCounter || 0;

function addAvailabilityBlock(type) {
    const container = document.getElementById(type === 'available' ? 'available-dates' : 'blocked-dates');
    const counter = type === 'available' ? availableCounter++ : blockedCounter++;
    const index = type === 'available' ? counter : counter + 100;

    const blockHtml = `
        <div class="availability-block shadow-sm" data-type="${type}">
            <button type="button" class="btn-remove-block" onclick="removeAvailabilityBlock(this)">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="row g-2">
                <div class="col-6">
                    <label class="extra-small fw-600 text-muted mb-1 d-block">Start Date</label>
                    <input type="date" class="form-control form-control-sm" name="availability_blocks[${index}][start_date]" required>
                </div>
                <div class="col-6">
                    <label class="extra-small fw-600 text-muted mb-1 d-block">End Date</label>
                    <input type="date" class="form-control form-control-sm" name="availability_blocks[${index}][end_date]" required>
                </div>
            </div>
            ${type === 'blocked' ? `
            <div class="mt-2">
                <label class="extra-small fw-600 text-muted mb-1 d-block">Reason (optional)</label>
                <input type="text" class="form-control form-control-sm" name="availability_blocks[${index}][reason]" placeholder="e.g. Personal use">
            </div>
            ` : `
            <div class="availability-error mt-2" style="display: none;"></div>
            `}
            <input type="hidden" name="availability_blocks[${index}][type]" value="${type}">
        </div>
    `;

    container.insertAdjacentHTML('beforeend', blockHtml);
}

function removeAvailabilityBlock(button) {
    if (confirm('Are you sure you want to remove this block?')) {
        $(button).closest('.availability-block').fadeOut(300, function() {
            $(this).remove();
        });
    }
}

// Make all functions globally available
window.showAlert = showAlert;
window.uploadImages = uploadImages;
window.removeImage = removeImage;
window.addAvailabilityBlock = addAvailabilityBlock;
window.removeAvailabilityBlock = removeAvailabilityBlock;

$(document).ready(function () {
    // Handle form submission
    $('#editClothForm').submit(function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');

        // Disable submit button and show loading
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i> SAVING...');

        $.ajax({
            url: window.editClothUpdateUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    showAlert('Changes saved successfully!', 'success');
                    setTimeout(function () {
                        window.location.href = window.listedClothesUrl;
                    }, 1000);
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = 'Failed to update changes.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                showAlert(errorMessage, 'danger');
            },
            complete: function () {
                submitBtn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-2"></i> UPDATE CHANGES');
            }
        });
    });

    // Handle image upload
    $('#image-upload').change(function () {
        const files = this.files;
        if (files.length > 0) {
            uploadImages(files);
        }
    });

    // Auto-fill and validate availability blocks
    function handleAvailabilityDateChange(block) {
        const startDateInput = block.find('input[name*="[start_date]"]');
        const endDateInput = block.find('input[name*="[end_date]"]');
        const errorDiv = block.find('.availability-error');

        const startDate = startDateInput.val();
        const endDate = endDateInput.val();

        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const daysDiff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

            if (daysDiff < 4) {
                errorDiv.text(`Minimum 4 days rental required. Currently: ${daysDiff} day(s).`).show();
                endDateInput.addClass('is-invalid');
            } else {
                errorDiv.hide();
                endDateInput.removeClass('is-invalid');
            }
        }
    }

    $(document).on('change', '.availability-block[data-type="available"] input[name*="[start_date]"]', function () {
        const startDate = $(this).val();
        if (startDate) {
            const start = new Date(startDate);
            const end = new Date(start);
            end.setDate(end.getDate() + 3);

            const year = end.getFullYear();
            const month = String(end.getMonth() + 1).padStart(2, '0');
            const day = String(end.getDate()).padStart(2, '0');
            const formattedEndDate = `${year}-${month}-${day}`;

            const block = $(this).closest('.availability-block');
            const endDateInput = block.find('input[name*="[end_date]"]');

            if (!endDateInput.val()) {
                endDateInput.val(formattedEndDate);
            }

            handleAvailabilityDateChange(block);
        }
    });

    $(document).on('change', '.availability-block[data-type="available"] input[name*="[end_date]"]', function () {
        const block = $(this).closest('.availability-block');
        handleAvailabilityDateChange(block);
    });

    // Initial check for existing blocks
    $('.availability-block[data-type="available"]').each(function() {
        handleAvailabilityDateChange($(this));
    });

    // Measurement unit conversion logic
    $('select[name="measurement_unit"]').on('change', function() {
        const unit = this.value;
        const isCm = unit === 'cm';
        const factor = isCm ? 2.54 : (1 / 2.54);

        const measurements = ['chest_bust', 'waist', 'length', 'shoulder', 'sleeve_length'];
        measurements.forEach(m => {
            const $input = $(`input[name="${m}"]`);
            if ($input.val()) {
                let val = parseFloat($input.val().replace(/[^0-9.]/g, ''));
                if (!isNaN(val)) {
                    $input.val((val * factor).toFixed(1).replace(/\.0$/, ''));
                }
            }
        });
    });
});