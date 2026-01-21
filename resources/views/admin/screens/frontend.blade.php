@extends('admin.layouts.app')

@section('title', 'Frontend Management')

@include('admin.components.setup-crud-styles')

@push('styles')
<style>
    .frontend-hero .btn-gradient {
        min-width: 190px;
    }
    .setting-card {
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 20px;
        background: rgba(255,255,255,0.88);
        border: 1px solid rgba(255,255,255,0.4);
        box-shadow: 0 18px 35px rgba(121, 134, 203, 0.18);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .setting-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 25px 45px rgba(99, 102, 241, 0.25);
    }
    .setting-label {
        font-weight: 600;
        color: #111827;
    }
    .setting-description {
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        padding: 0.65rem 0.9rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 0.2rem rgba(99,102,241,0.15);
    }
    .btn-save {
        background: linear-gradient(135deg, #2f57ef, #7b61ff);
        border: none;
        color: #fff;
        padding: 0.5rem 1.1rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .btn-save:disabled {
        opacity: 0.6;
    }
    .image-preview {
        max-width: 220px;
        max-height: 110px;
        border-radius: 12px;
        margin-top: 10px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.15);
    }
    .nav-tabs {
        border-bottom: none;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .nav-tabs .nav-link {
        border: none;
        border-radius: 999px;
        padding: 0.55rem 1.5rem;
        background: rgba(255,255,255,0.7);
        color: #6b7280;
        font-weight: 600;
    }
    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #2f57ef, #7b61ff);
        color: #fff;
        box-shadow: 0 10px 20px rgba(47, 87, 239, 0.3);
    }
    .tab-content {
        padding-top: 20px;
    }
    .loading {
        opacity: 0.55;
        pointer-events: none;
    }
    .alert {
        border-radius: 14px;
        border: none;
        box-shadow: 0 10px 20px rgba(34, 197, 94, 0.15);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 category-dashboard">
    <div class="row g-4">
        <div class="col-12">
            <div class="glass-card hero-card frontend-hero d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between p-4 p-lg-5">
                <div>
                    <p class="text-uppercase fw-semibold text-white-50 small mb-1">Experience · Frontend</p>
                    <h2 class="display-6 fw-bold text-white mb-3">Polish the storefront without touching code</h2>
                    <p class="text-white-50 mb-0">
                        Tweak hero copy, imagery, promos, and CTAs on the fly. 
                        Every change syncs instantly to the renter experience.
                    </p>
                </div>
                <button class="btn btn-gradient btn-lg shadow-sm mt-4 mt-lg-0" onclick="saveAllSettings()">
                    <i class="bi bi-magic me-2"></i>Save all changes
                </button>
            </div>
        </div>

        <div class="col-12">
            <div id="alertContainer"></div>
            <div class="glass-card p-4">
                <ul class="nav nav-tabs mb-3" id="frontendTabs" role="tablist">
                    @foreach($sections as $sectionKey => $sectionName)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                id="{{ $sectionKey }}-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#{{ $sectionKey }}-content" 
                                type="button" 
                                role="tab">
                            {{ $sectionName }}
                        </button>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="frontendTabContent">
                    @foreach($sections as $sectionKey => $sectionName)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                         id="{{ $sectionKey }}-content" 
                         role="tabpanel">
                        
                        <div class="row">
                            @foreach($settings->where('section', $sectionKey) as $setting)
                            <div class="col-md-6 col-lg-4">
                                <div class="setting-card" data-setting-key="{{ $setting->key }}">
                                    <div class="setting-label">{{ $setting->label }}</div>
                                    <div class="setting-description">{{ $setting->description }}</div>
                                    
                                    @if($setting->type === 'text')
                                    <input type="text" 
                                           class="form-control setting-input" 
                                           value="{{ $setting->value }}" 
                                           data-type="{{ $setting->type }}"
                                           placeholder="Enter {{ strtolower($setting->label) }}">
                                    
                                    @elseif($setting->type === 'textarea')
                                    <textarea class="form-control setting-input" 
                                              rows="3" 
                                              data-type="{{ $setting->type }}"
                                              placeholder="Enter {{ strtolower($setting->label) }}">{{ $setting->value }}</textarea>
                                    
                                    @elseif($setting->type === 'image')
                                    <div class="mb-2">
                                        @if($setting->value)
                                        <img src="{{ asset($setting->value) }}" 
                                             alt="{{ $setting->label }}" 
                                             class="image-preview d-block">
                                        @endif
                                    </div>
                                    <input type="file" 
                                           class="form-control setting-input" 
                                           accept="image/*"
                                           data-type="{{ $setting->type }}"
                                           data-current-value="{{ $setting->value }}">
                                    <small class="text-muted">Current: {{ $setting->value ?: 'No image set' }}</small>
                                    
                                    @endif
                                    
                                    <div class="mt-3">
                                        <button class="btn btn-save btn-sm" 
                                                onclick="saveSetting('{{ $setting->key }}', this)">
                                            <i class="bi bi-check"></i> Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
        url: '{{ route("admin.frontend.update") }}',
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
            url: '{{ route("admin.frontend.update") }}',
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
</script>
@endpush
