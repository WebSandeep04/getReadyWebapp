// Global functions
function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Remove any existing alerts
    $('.alert').remove();
    
    // Add new alert at the top
    $('.container').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut(300, function() {
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
    
    $.ajax({
        url: window.editClothUpdateUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert('Images uploaded successfully!', 'success');
                location.reload();
            }
        },
        error: function(xhr, status, error) {
            showAlert('An error occurred while uploading images.', 'danger');
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
            success: function(response) {
                if (response.success) {
                    $(`.image-container[data-image-id="${imageId}"]`).remove();
                    showAlert('Image removed successfully!', 'success');
                }
            },
            error: function(xhr, status, error) {
                showAlert('An error occurred while removing the image.', 'danger');
            }
        });
    }
}

// Availability block counters
let availableCounter = window.availableCounter || 0;
let blockedCounter = window.blockedCounter || 0;

// Handle purchase checkbox functionality
document.addEventListener('DOMContentLoaded', function() {
    const isPurchasedCheckbox = document.getElementById('is_purchased');
    const purchaseValueSection = document.getElementById('purchase_value_section');
    const purchaseValueInput = document.querySelector('input[name="purchase_value"]');

    if (isPurchasedCheckbox && purchaseValueSection) {
        // Show/hide purchase value section based on checkbox state
        function togglePurchaseValueSection() {
            if (isPurchasedCheckbox.checked) {
                purchaseValueSection.style.display = 'block';
                if (purchaseValueInput) {
                    purchaseValueInput.required = true;
                }
            } else {
                purchaseValueSection.style.display = 'none';
                if (purchaseValueInput) {
                    purchaseValueInput.required = false;
                    purchaseValueInput.value = '';
                }
            }
        }
        
        // Add event listener
        isPurchasedCheckbox.addEventListener('change', togglePurchaseValueSection);
    }
});

function addAvailabilityBlock(type) {
    const container = document.getElementById(type === 'available' ? 'available-dates' : 'blocked-dates');
    const counter = type === 'available' ? availableCounter++ : blockedCounter++;
    const index = type === 'available' ? counter : counter + 100;
    
    const blockHtml = `
        <div class="availability-block mb-3" data-type="${type}">
            <div class="row">
                <div class="col-md-5">
                    <label class="small">Start Date</label>
                    <input type="date" class="form-control form-control-sm" name="availability_blocks[${index}][start_date]" required>
                </div>
                <div class="col-md-5">
                    <label class="small">End Date</label>
                    <input type="date" class="form-control form-control-sm" name="availability_blocks[${index}][end_date]" required>
                </div>
                <div class="col-md-2">
                    <label class="small">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeAvailabilityBlock(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            ${type === 'blocked' ? `
            <div class="row mt-2">
                <div class="col-12">
                    <label class="small">Reason (optional)</label>
                    <input type="text" class="form-control form-control-sm" name="availability_blocks[${index}][reason]" placeholder="e.g., Personal use, Maintenance">
                </div>
            </div>
            ` : ''}
            <input type="hidden" name="availability_blocks[${index}][type]" value="${type}">
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', blockHtml);
}

function removeAvailabilityBlock(button) {
    if (confirm('Are you sure you want to remove this availability block?')) {
        button.closest('.availability-block').remove();
    }
}

// Make all functions globally available
window.showAlert = showAlert;
window.uploadImages = uploadImages;
window.removeImage = removeImage;
window.addAvailabilityBlock = addAvailabilityBlock;
window.removeAvailabilityBlock = removeAvailabilityBlock;

$(document).ready(function() {
    // Handle form submission
    $('#editClothForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        $.ajax({
            url: window.editClothUpdateUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Cloth updated successfully!', 'success');
                    setTimeout(function() {
                        window.location.href = window.listedClothesUrl;
                    }, 1500);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred while updating the cloth.';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                } else if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
                
                showAlert(errorMessage, 'danger');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Cloth');
            }
        });
    });

    // Handle image upload
    $('#image-upload').change(function() {
        const files = this.files;
        if (files.length > 0) {
            uploadImages(files);
        }
    });

    // Drag and drop functionality
    const uploadArea = document.getElementById('upload-area');
    
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            uploadImages(files);
        }
    });
}); 