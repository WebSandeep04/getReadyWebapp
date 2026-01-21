@extends('layouts.app')

@section('title', 'Get Ready - Profile')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        My Profile
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Success/Error Messages -->
                    <div id="alert-container"></div>
                    
                    <!-- Profile Form -->
                    <form id="profile-form" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Profile Image Section -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <div class="profile-image-container" onclick="document.getElementById('profile_image').click();">
                                    @if($user->profile_image)
                                        <img id="profile-preview" src="{{ asset('storage/' . $user->profile_image) }}" 
                                             alt="Profile" class="rounded-circle profile-image">
                                    @else
                                        <div id="profile-preview" class="rounded-circle profile-image bg-secondary text-white d-flex align-items-center justify-content-center">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="profile-image-overlay">
                                        <i class="bi bi-camera"></i>
                                    </div>
                                </div>
                                <input type="file" id="profile_image" name="profile_image" class="d-none" accept="image/*">
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Click to change profile picture</small>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label fw-bold">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ $user->name }}" required>
                                    <div class="invalid-feedback" id="name-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label fw-bold">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ $user->email }}" required>
                                    <div class="invalid-feedback" id="email-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label fw-bold">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="{{ $user->phone }}" required>
                                    <div class="invalid-feedback" id="phone-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="gender" class="form-label fw-bold">User Type *</label>
                                    <select class="form-control" id="gender" name="gender" required>
                                        <option value="">Select User Type</option>
                                        <option value="Boy" {{ $user->gender == 'Boy' ? 'selected' : '' }}>Boy</option>
                                        <option value="Girl" {{ $user->gender == 'Girl' ? 'selected' : '' }}>Girl</option>
                                        <option value="Men" {{ $user->gender == 'Men' ? 'selected' : '' }}>Men</option>
                                        <option value="Women" {{ $user->gender == 'Women' ? 'selected' : '' }}>Women</option>
                                    </select>
                                    <div class="invalid-feedback" id="gender-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="address" class="form-label fw-bold">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" 
                                      placeholder="Enter your address">{{ $user->address }}</textarea>
                            <div class="invalid-feedback" id="address-error"></div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="gstin" class="form-label fw-bold">GSTIN</label>
                            <input type="text" class="form-control" id="gstin" name="gstin" 
                                   value="{{ $user->gstin }}" 
                                   placeholder="Enter 15-digit GSTIN (e.g., 27AAAAA0000A1Z5)"
                                   maxlength="15">
                            <small class="text-muted">Format: 15 characters (e.g., 27AAAAA0000A1Z5)</small>
                            <div class="invalid-feedback" id="gstin-error"></div>
                        </div>

                        <!-- Account Information -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Account Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Member Since:</strong></p>
                                        <p class="text-muted">{{ $user->created_at->format('F j, Y') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Last Updated:</strong></p>
                                        <p class="text-muted">{{ $user->updated_at->format('F j, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset
                            </button>
                            <button type="submit" class="btn btn-warning" id="update-btn">
                                <i class="bi bi-check-circle me-1"></i>
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
                if ($('#profile-preview').hasClass('bg-secondary')) {
                    $('#profile-preview').removeClass('bg-secondary d-flex align-items-center justify-content-center')
                                     .html(`<img src="${e.target.result}" alt="Profile" class="rounded-circle profile-image">`);
                } else {
                    $('#profile-preview img').attr('src', e.target.result);
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
        const $form = $(this);
        
        // Show loading state
        $btn.prop('disabled', true).addClass('btn-loading')
            .html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');
        $form.addClass('loading');
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('#alert-container').empty();
        
        $.ajax({
            url: '{{ route("profile.update") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    
                    // Update profile image in header if changed
                    if (response.user.profile_image) {
                        $('.auth-buttons img[alt="Profile"]').attr('src', '/storage/' + response.user.profile_image);
                    } else {
                        $('.auth-buttons .rounded-circle').html(response.user.name.charAt(0).toUpperCase());
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(field) {
                        const $field = $('#' + field);
                        $field.addClass('is-invalid');
                        $('#' + field + '-error').text(errors[field][0]);
                    });
                    showAlert('danger', 'Please correct the errors below.');
                } else {
                    showAlert('danger', 'An error occurred. Please try again.');
                }
            },
            complete: function() {
                // Reset loading state
                $btn.prop('disabled', false).removeClass('btn-loading')
                    .html('<i class="bi bi-check-circle me-1"></i>Update Profile');
                $form.removeClass('loading');
            }
        });
    });
});

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alert-container').html(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

function resetForm() {
    // Reset form to original values
    $('#profile-form')[0].reset();
    
    // Reset profile image
    if ('{{ $user->profile_image }}') {
        $('#profile-preview').html(`<img src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile" class="rounded-circle profile-image">`);
    } else {
        $('#profile-preview').html(`<div class="rounded-circle profile-image bg-secondary text-white d-flex align-items-center justify-content-center">{{ strtoupper(substr($user->name, 0, 1)) }}</div>`);
    }
    
    // Clear errors
    $('.is-invalid').removeClass('is-invalid');
    $('#alert-container').empty();
}
</script>
@endsection
