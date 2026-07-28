@extends('layouts.app')

@section('title', 'Get Ready - Profile Settings')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
<style>
    .sticky-top { top: 100px; z-index: 10; }
</style>
@endsection

@section('content')
<div class="container py-4">
    <!-- Success/Error Messages (Top Fixed) -->
    <div id="alert-container" style="position: fixed; top: 100px; right: 20px; z-index: 9999; max-width: 350px;"></div>

    <form id="profile-form" enctype="multipart/form-data">
        @csrf
        <div class="profile-layout">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar-wrapper">
                    @if($user->profile_image)
                        <img id="profile-preview" src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile" class="profile-avatar">
                    @elseif($user->aadhaar_image_base64)
                        <img id="profile-preview" src="data:image/jpeg;base64,{{ $user->aadhaar_image_base64 }}" alt="Profile" class="profile-avatar">
                    @else
                        <div id="profile-preview" class="profile-avatar-placeholder">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="avatar-edit-btn" onclick="document.getElementById('profile_image').click();">
                        <i class="bi bi-camera-fill"></i>
                    </div>
                    <input type="file" id="profile_image" name="profile_image" class="d-none" accept="image/*">
                </div>

                <div class="profile-user-info">
                    <h4>{{ $user->name }}</h4>
                    <p class="mb-0 text-truncate px-3">{{ $user->email }}</p>
                    <div class="mt-2">
                        @if($user->is_aadhaar_verified)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small">
                                <i class="bi bi-patch-check-fill me-1"></i> Verified
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 small">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Pending
                            </span>
                        @endif
                    </div>
                </div>

                <div class="profile-nav">
                    <div class="profile-nav-item active">
                        <i class="bi bi-person-badge"></i> Personal Info
                    </div>
                    <div class="profile-nav-item" onclick="window.scrollTo({top: document.getElementById('business-card').offsetTop - 100, behavior: 'smooth'})">
                        <i class="bi bi-building"></i> Business & KYC
                    </div>
                    <div class="profile-nav-item" onclick="window.scrollTo({top: document.getElementById('account-info-card').offsetTop - 100, behavior: 'smooth'})">
                        <i class="bi bi-clock-history"></i> Activity
                    </div>
                </div>

                <div class="sidebar-footer border-top">
                    <button type="submit" class="btn btn-premium flex-fill shadow-sm" id="update-btn">
                        SAVE CHANGES
                    </button>
                    <button type="button" class="btn btn-link text-muted fw-bold small" onclick="resetForm()">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="profile-content">
                <!-- Personal Info Card -->
                <div class="profile-main-card">
                    <h5 class="section-title">
                        <i class="bi bi-person-circle"></i> Personal Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6 col-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-6 col-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>
                        <div class="col-md-6 col-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="{{ $user->phone }}" required>
                            <div class="invalid-feedback" id="phone-error"></div>
                        </div>
                        <div class="col-md-6 col-6">
                            <label for="gender" class="form-label">User Type</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select</option>
                                <option value="Boy" {{ $user->gender == 'Boy' ? 'selected' : '' }}>Boy</option>
                                <option value="Girl" {{ $user->gender == 'Girl' ? 'selected' : '' }}>Girl</option>
                                <option value="Men" {{ $user->gender == 'Men' ? 'selected' : '' }}>Men</option>
                                <option value="Women" {{ $user->gender == 'Women' ? 'selected' : '' }}>Women</option>
                            </select>
                            <div class="invalid-feedback" id="gender-error"></div>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Primary Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter your full address" {{ $user->is_aadhaar_verified ? 'readonly' : '' }}>{{ $user->address }}</textarea>
                            @if($user->is_aadhaar_verified)
                                <div class="mt-1 small text-success fw-bold">
                                    <i class="bi bi-shield-check"></i> Aadhaar Verified
                                </div>
                            @endif
                            <div class="invalid-feedback" id="address-error"></div>
                        </div>
                        <div class="col-md-4 col-4">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" placeholder="State" value="{{ $user->state }}">
                            <div class="invalid-feedback" id="state-error"></div>
                        </div>
                        <div class="col-md-4 col-4">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" placeholder="City" value="{{ $user->city }}">
                            <div class="invalid-feedback" id="city-error"></div>
                        </div>
                        <div class="col-md-4 col-4">
                            <label for="pincode" class="form-label">Pincode</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" placeholder="Pincode" value="{{ $user->pincode }}" maxlength="6">
                            <div class="invalid-feedback" id="pincode-error"></div>
                        </div>
                    </div>
                </div>

                <!-- Business & KYC Card -->
                <div class="profile-main-card" id="business-card">
                    <h5 class="section-title">
                        <i class="bi bi-shield-lock-fill"></i> Business & KYC
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="is_gst" class="form-label">Business Type</label>
                            <select class="form-select" id="is_gst" name="is_gst" required onchange="toggleGstField()">
                                <option value="0" {{ !$user->is_gst ? 'selected' : '' }}>Individual / Non-Business</option>
                                <option value="1" {{ $user->is_gst ? 'selected' : '' }}>Business (GST Available)</option>
                            </select>
                        </div>

                        <!-- GST Section -->
                        <div class="col-12" id="gst-container" style="display: {{ $user->is_gst ? 'block' : 'none' }};">
                            <label for="gstin" class="form-label">GSTIN Number</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="gstin" name="gstin" 
                                       value="{{ $user->gstin ?? $user->gst_number }}" 
                                       placeholder="15-digit GSTIN" maxlength="15" 
                                       {{ $user->is_gst && $user->gst_legal_name ? 'readonly' : '' }}>
                                @if($user->is_gst && $user->gst_legal_name)
                                    <button class="btn btn-success px-4" type="button" disabled>
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                @else
                                    <button class="btn btn-dark px-4 fw-bold" type="button" id="btn-verify-gst">VERIFY</button>
                                @endif
                            </div>
                            <div class="invalid-feedback" id="gstin-error"></div>
                            
                            @if($user->gst_legal_name)
                                <div class="mt-3 p-3 bg-light border-start border-4 border-success rounded-end">
                                    <p class="mb-1 small text-muted text-uppercase fw-bold">Legal Business Name</p>
                                    <h6 class="fw-bold mb-0">{{ $user->gst_legal_name }}</h6>
                                </div>
                            @endif
                        </div>

                        <!-- Aadhaar KYC Section -->
                        <div class="col-12">
                            <label for="aadhaar_number" class="form-label">Aadhaar Number (KYC)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="aadhaar_number" name="aadhaar_number" 
                                       value="{{ $user->aadhaar_masked_number ?? $user->aadhaar_number }}" 
                                       placeholder="12-digit Aadhaar Number" maxlength="12"
                                       {{ $user->is_aadhaar_verified ? 'readonly' : '' }}>
                                @if($user->is_aadhaar_verified)
                                    <button class="btn btn-success px-4" type="button" disabled>
                                        <i class="bi bi-shield-fill-check"></i>
                                    </button>
                                @else
                                    <button class="btn btn-dark px-4 fw-bold" type="button" id="btn-verify-aadhaar">KYC</button>
                                @endif
                            </div>
                            @if($user->is_aadhaar_verified)
                                <div class="mt-3 p-3 bg-light border-start border-4 border-info rounded-end">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <p class="mb-1 small text-muted text-uppercase fw-bold">KYC Status</p>
                                            <h6 class="fw-bold mb-0 text-info">IDENTITY VERIFIED VIA DIGILOCKER</h6>
                                        </div>
                                        @if($user->aadhaar_pdf_link)
                                            <div class="col-auto">
                                                <a href="{{ $user->aadhaar_pdf_link }}" target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3 fw-bold">
                                                    VIEW PDF
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <small class="text-muted d-block mt-2">Verify via IM Wallet to unlock premium features and increase trust score.</small>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Account Status Card -->
                <div class="profile-main-card" id="account-info-card">
                    <h5 class="section-title">
                        <i class="bi bi-clock-history"></i> Account Activity
                    </h5>
                    <div class="row g-2">
                        <div class="col-md-6 col-6">
                            <div class="p-2 bg-light rounded-4 border h-100">
                                <span class="d-block extra-small text-muted text-uppercase fw-bold mb-1">MEMBER SINCE</span>
                                <h6 class="fw-bold mb-0 small">{{ $user->created_at->format('d M, Y') }}</h6>
                            </div>
                        </div>
                        <div class="col-md-6 col-6">
                            <div class="p-2 bg-light rounded-4 border h-100">
                                <span class="d-block extra-small text-muted text-uppercase fw-bold mb-1">LAST UPDATE</span>
                                <h6 class="fw-bold mb-0 small">{{ $user->updated_at->diffForHumans(null, true) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>



@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Profile image preview
    $('#profile_image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = $('#profile-preview');
                if (preview.hasClass('profile-avatar-placeholder')) {
                    preview.removeClass('profile-avatar-placeholder').addClass('profile-avatar')
                           .html(`<img src="${e.target.result}" alt="Profile" class="w-100 h-100 object-fit-cover rounded-circle">`);
                } else if(preview.is('img')) {
                    preview.attr('src', e.target.result);
                } else {
                    preview.find('img').attr('src', e.target.result);
                }
            }
            reader.readAsDataURL(file);
        }
    });

    // Form submission
    $('#profile-form').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const $btn = $('#update-btn');
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>SAVING...');
        $('.is-invalid').removeClass('is-invalid');
        
        $.ajax({
            url: '{{ route("profile.update") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('success', '<i class="bi bi-check-circle-fill me-2"></i> Profile updated successfully!');
                    if (response.user.profile_image) {
                        $('.auth-buttons img[alt="Profile"]').attr('src', '/storage/' + response.user.profile_image);
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}-error`).text(errors[field][0]);
                    });
                    showAlert('danger', 'Please fix the errors in the form.');
                } else {
                    showAlert('danger', 'Something went wrong. Please try again.');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('SAVE CHANGES');
            }
        });
    });
});

function showAlert(type, message) {
    const alertHtml = `<div class="alert alert-${type} shadow-lg border-0 rounded-4 p-3 mb-3 slide-in-right">${message}</div>`;
    $('#alert-container').append(alertHtml);
    setTimeout(() => {
        $('#alert-container .alert:first').fadeOut(400, function() { $(this).remove(); });
    }, 4000);
}

function resetForm() {
    $('#profile-form')[0].reset();
    location.reload();
}

function toggleGstField() {
    const isGst = $('#is_gst').val() === '1';
    $('#gst-container').toggle(isGst);
}

$(document).ready(function() {
    $('#btn-verify-gst').click(function() {
        const gstin = $('#gstin').val();
        if(!gstin || gstin.length !== 15) {
            showAlert('warning', 'Enter a valid 15-digit GSTIN.');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '{{ route("verify.gst") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', gstin: gstin },
            success: function(response) {
                if(response.success) {
                    showAlert('success', 'GST details verified! Click Save to finalize.');
                    location.reload(); // Reload to show verified info
                } else {
                    showAlert('danger', 'GSTIN verification failed.');
                }
            },
            error: function() {
                showAlert('danger', 'Network error during GST verification.');
            },
            complete: function() {
                $btn.prop('disabled', false).html('VERIFY');
            }
        });
    });

    $('#btn-verify-aadhaar').click(function() {
        const num = $('#aadhaar_number').val();
        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '{{ route("aadhaar.start") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', aadhaar_number: num },
            success: function(res) {
                if(res.success && res.url) window.location.href = res.url;
                else showAlert('danger', 'KYC initialization failed.');
            },
            error: function() {
                showAlert('danger', 'KYC service unavailable.');
            },
            complete: function() {
                $btn.prop('disabled', false).html('KYC');
            }
        });
    });

    $('#aadhaar_number').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
    });
});
</script>
@endsection
