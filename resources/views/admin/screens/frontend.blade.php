@extends('admin.layouts.app')

@section('title', 'Frontend Management')
@section('page_title', 'Frontend Management')

@section('content')
<div class="container-fluid p-0">
    <!-- Stat Cards Row (Optional - added for consistency with other pages layout) -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-window-desktop"></i></div>
                <div class="stat-card__label">Total Sections</div>
                <div class="stat-card__value">{{ count($sections) }}</div>
                <small>Configurable areas</small>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-card__icon"><i class="bi bi-toggles"></i></div>
                <div class="stat-card__label">Total Settings</div>
                <div class="stat-card__value">{{ $settings->count() }}</div>
                <small>Customizable fields</small>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card h-100">
        <div class="card-header bg-white text-dark border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-sliders me-2"></i>Frontend Settings</h5>
                <button class="btn btn-sm btn-dark" onclick="saveAllSettings()">
                    <i class="bi bi-save me-1"></i>Save All Changes
                </button>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="px-4 mt-3"></div>

        <div class="card-body">
            <!-- Tabs -->
            <ul class="nav nav-tabs admin-tabs mb-4 border-bottom" id="frontendTabs" role="tablist">
                @foreach($sections as $sectionKey => $sectionName)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }} text-uppercase fw-bold small py-3 px-4 rounded-0" 
                            id="{{ $sectionKey }}-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#{{ $sectionKey }}-content" 
                            type="button" 
                            role="tab"
                            style="letter-spacing: 0.05em;">
                        {{ $sectionName }}
                    </button>
                </li>
                @endforeach
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="frontendTabContent">
                @foreach($sections as $sectionKey => $sectionName)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                     id="{{ $sectionKey }}-content" 
                     role="tabpanel">
                    
                    <div class="row g-4">
                        @foreach($settings->where('section', $sectionKey) as $setting)
                        <div class="col-md-6 col-lg-6">
                            <div class="setting-card card h-100 border bg-light bg-opacity-10" data-setting-key="{{ $setting->key }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <label class="form-label fw-bold text-dark mb-0">{{ $setting->label }}</label>
                                        <button class="btn btn-link text-muted p-0 save-btn-icon" 
                                                onclick="saveSetting('{{ $setting->key }}', this)"
                                                title="Save this setting">
                                            <i class="bi bi-check-lg" style="font-size: 1.2rem;"></i>
                                        </button>
                                    </div>
                                    <p class="text-muted small mb-3">{{ $setting->description }}</p>
                                    
                                    @if($setting->type === 'text')
                                    <input type="text" 
                                           class="form-control setting-input rounded-0 bg-white" 
                                           value="{{ $setting->value }}" 
                                           data-type="{{ $setting->type }}"
                                           placeholder="Enter {{ strtolower($setting->label) }}">
                                    
                                    @elseif($setting->type === 'textarea')
                                    <textarea class="form-control setting-input rounded-0 bg-white" 
                                              rows="4" 
                                              data-type="{{ $setting->type }}"
                                              placeholder="Enter {{ strtolower($setting->label) }}">{{ $setting->value }}</textarea>
                                    
                                    @elseif($setting->type === 'image')
                                    <div class="d-flex gap-3 align-items-start">
                                        <div class="image-preview-wrapper border bg-white p-1 d-flex align-items-center justify-content-center" 
                                             style="width: 120px; height: 80px; overflow: hidden; flex-shrink: 0;">
                                            @if($setting->value)
                                            <img src="{{ asset($setting->value) }}" 
                                                 alt="{{ $setting->label }}" 
                                                 class="image-preview" 
                                                 style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                            @else
                                            <span class="text-muted small">No Image</span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" 
                                                   class="form-control setting-input rounded-0 mb-1 bg-white" 
                                                   accept="image/*"
                                                   data-type="{{ $setting->type }}"
                                                   data-current-value="{{ $setting->value }}">
                                            <small class="text-xs text-muted">Recommended size: Depends on section</small>
                                        </div>
                                    </div>
                                    @endif
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
@endsection

@push('styles')
<style>
/* Global Monochrome Overrides from Admin Layout */
*, ::before, ::after { border-radius: 0 !important; }

/* Stat Cards */
.stat-card {
    position: relative;
    padding: 1rem;
    color: #000;
    background: #fff;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 90px;
    border: 1px solid #e5e7eb;
}
.stat-card__label {
    font-size: .65rem;
    font-weight: 700;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}
.stat-card__value {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0;
    color: #111827;
    line-height: 1.2;
}
.stat-card__icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 1.25rem;
    color: #000;
    opacity: 1;
}
.stat-card small { color: #9ca3af; font-weight: 500; font-size: 0.7rem; }

/* Tabs */
.nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #6b7280;
    transition: all 0.2s;
    background: transparent;
    font-size: 0.75rem;
}
.nav-tabs .nav-link:hover {
    color: #111827;
}
.nav-tabs .nav-link.active {
    color: #000;
    background: transparent;
    border-bottom: 2px solid #000;
}

/* Forms & Cards */
.card { border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); background: #fff; }
.setting-card {
    transition: border-color 0.2s;
    border: 1px solid #e5e7eb !important;
}
.setting-card:hover { border-color: #000 !important; }

.form-label {
    font-size: 0.75rem;
}

.form-control {
    border: 1px solid #d1d5db;
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    box-shadow: none !important;
}
.form-control:focus {
    border-color: #000;
    background-color: #fff;
}

/* Buttons */
.btn { font-size: 0.75rem; font-weight: 500; padding: 0.35rem 0.75rem; transition: all 0.2s; }
.btn-dark { background: #111827; border: 1px solid #111827; color: #fff; }
.btn-dark:hover { background: #000; border-color: #000; transform: translateY(-1px); }

.save-btn-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid transparent;
    transition: all 0.2s;
    font-size: 0.9rem;
}
.save-btn-icon:hover {
    background: #f3f4f6;
    color: #000 !important;
    border-color: #e5e7eb;
}

.text-muted { color: #6b7280 !important; font-size: 0.75rem !important; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle file input changes for image preview
    $(document).on('change', '.setting-input[data-type="image"]', function() {
        const file = this.files[0];
        const card = $(this).closest('.setting-card');
        const previewImg = card.find('img.image-preview');
        const previewWrapper = card.find('.image-preview-wrapper');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (previewImg.length) {
                    previewImg.attr('src', e.target.result);
                } else {
                    previewWrapper.html(`<img src="${e.target.result}" alt="Preview" class="image-preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">`);
                }
            };
            reader.readAsDataURL(file);
        }
    });
});

window.saveSetting = function(key, button) {
    const card = $(button).closest('.setting-card');
    const input = card.find('.setting-input');
    const type = input.data('type');
    const formData = new FormData();
    
    formData.append('key', key);
    formData.append('type', type);
    
    if (type === 'image') {
        const file = input[0].files[0];
        if (file) {
            formData.append('value', file);
        } else {
            const currentVal = input.data('current-value');
            if (currentVal) formData.append('value', currentVal);
        }
    } else {
        formData.append('value', input.val());
    }
    
    // UI Feedback
    const originalIcon = $(button).html();
    $(button).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" style="width: 1em; height: 1em;"></span>');
    
    $.ajax({
        url: '{{ route("admin.frontend.update") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Saved successfully');
                if (type === 'image' && response.value) {
                    input.data('current-value', response.value);
                }
            } else {
                showAlert('danger', response.message || 'Failed to save');
            }
        },
        error: function() {
            showAlert('danger', 'Error saving setting');
        },
        complete: function() {
            setTimeout(() => {
                $(button).prop('disabled', false).html(originalIcon);
            }, 500);
        }
    });
};

window.saveAllSettings = function() {
    const $mainBtn = $('button[onclick="saveAllSettings()"]');
    const originalText = $mainBtn.html();
    $mainBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
    
    const promises = [];
    
    $('.setting-input').each(function() {
        const input = $(this);
        const card = input.closest('.setting-card');
        const key = card.data('setting-key');
        const type = input.data('type');
        const formData = new FormData();
        
        formData.append('key', key);
        formData.append('type', type);
        
        if (type === 'image') {
            const file = input[0].files[0];
            if (file) formData.append('value', file);
            else {
                const currentVal = input.data('current-value');
                if(currentVal) formData.append('value', currentVal);
            }
        } else {
            formData.append('value', input.val());
        }
        
        const p = $.ajax({
            url: '{{ route("admin.frontend.update") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        }).then(
            res => res.success ? null : Promise.reject('Fail'),
            err => Promise.reject(err)
        );
        promises.push(p);
    });
    
    Promise.allSettled(promises).then(results => {
        const rejected = results.filter(r => r.status === 'rejected');
        if (rejected.length === 0) {
            showAlert('success', 'All settings saved successfully');
        } else {
            showAlert('warning', `Saved with ${rejected.length} errors`);
        }
        $mainBtn.prop('disabled', false).html(originalText);
    });
};

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show border-0 rounded-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'} me-2"></i>
                <div>${message}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#alertContainer').html(alertHtml);
    setTimeout(() => $('.alert').fadeOut(), 3000);
}
</script>
@endpush
