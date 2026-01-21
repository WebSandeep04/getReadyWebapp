// Admin Frontend Management JavaScript

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Handle file input changes for image preview
    $('.setting-input[data-type="image"]').on('change', function() {
        const file = this.files[0];
        const card = $(this).closest('.setting-card');
        const preview = card.find('.image-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview.length === 0) {
                    card.find('.mb-2').html(`<img src="${e.target.result}" alt="Preview" class="image-preview d-block">`);
                } else {
                    preview.attr('src', e.target.result);
                }
            };
            reader.readAsDataURL(file);
        }
    });
});

function saveSetting(key, button) {
    const card = $(button).closest('.setting-card');
    const input = card.find('.setting-input');
    const type = input.data('type');
    const formData = new FormData();
    
    formData.append('key', key);
    formData.append('type', type);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    if (type === 'image') {
        const file = input[0].files[0];
        if (file) {
            formData.append('value', file);
        } else {
            formData.append('value', input.data('current-value'));
        }
    } else {
        formData.append('value', input.val());
    }
    
    // Disable button and show loading
    $(button).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');
    card.addClass('loading');
    
    $.ajax({
        url: '/admin/frontend/update',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Setting updated successfully!');
                
                // Update current value for image inputs
                if (type === 'image' && response.value) {
                    input.data('current-value', response.value);
                }
            } else {
                showAlert('danger', response.message || 'Error updating setting');
            }
        },
        error: function(xhr) {
            showAlert('danger', 'Error updating setting. Please try again.');
        },
        complete: function() {
            $(button).prop('disabled', false).html('<i class="bi bi-check"></i> Save');
            card.removeClass('loading');
        }
    });
}

function saveAllSettings() {
    const allInputs = $('.setting-input');
    let savedCount = 0;
    let totalCount = allInputs.length;
    
    if (totalCount === 0) {
        showAlert('info', 'No settings to save');
        return;
    }
    
    showAlert('info', 'Saving all settings...');
    
    allInputs.each(function(index) {
        const input = $(this);
        const card = input.closest('.setting-card');
        const key = card.data('setting-key');
        const type = input.data('type');
        const formData = new FormData();
        
        formData.append('key', key);
        formData.append('type', type);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        if (type === 'image') {
            const file = input[0].files[0];
            if (file) {
                formData.append('value', file);
            } else {
                formData.append('value', input.data('current-value'));
            }
        } else {
            formData.append('value', input.val());
        }
        
        $.ajax({
            url: '/admin/frontend/update',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                savedCount++;
                if (savedCount === totalCount) {
                    showAlert('success', `All settings saved successfully! (${savedCount}/${totalCount})`);
                }
            },
            error: function(xhr) {
                savedCount++;
                if (savedCount === totalCount) {
                    showAlert('warning', `Some settings may not have been saved. (${savedCount}/${totalCount})`);
                }
            }
        });
    });
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#alertContainer').html(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

// Auto-save functionality (optional)
let autoSaveTimer;
$('.setting-input').on('input', function() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
        // Uncomment the line below to enable auto-save
        // saveAllSettings();
    }, 3000); // Auto-save after 3 seconds of inactivity
}); 