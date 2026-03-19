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
                            <textarea class="form-control" id="address" name="address" rows="2" 
                                      placeholder="Enter your address">{{ $user->address }}</textarea>
                            <div class="invalid-feedback" id="address-error"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="state_id" class="form-label fw-bold">State</label>
                                    <select class="form-control" id="state_id" name="state_id">
                                        <option value="">Select State</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->id }}" {{ $user->state_id == $state->id ? 'selected' : '' }}>
                                                {{ $state->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="state_id-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="city_id" class="form-label fw-bold">City</label>
                                    <select class="form-control" id="city_id" name="city_id">
                                        <option value="">Select City</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" {{ $user->city_id == $city->id ? 'selected' : '' }}>
                                                {{ $city->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="city_id-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="is_gst" class="form-label fw-bold">Business Type *</label>
                            <select class="form-control" id="is_gst" name="is_gst" required onchange="toggleGstField()">
                                <option value="0" {{ !$user->is_gst ? 'selected' : '' }}>Individual / Non-Business</option>
                                <option value="1" {{ $user->is_gst ? 'selected' : '' }}>Business (GST Available)</option>
                            </select>
                            <div class="invalid-feedback" id="is_gst-error"></div>
                        </div>

                        <div class="form-group mb-4" id="gst-container" style="display: {{ $user->is_gst ? 'block' : 'none' }};">
                            <label for="gstin" class="form-label fw-bold">GSTIN *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="gstin" name="gstin" 
                                       value="{{ $user->gstin ?? $user->gst_number }}" 
                                       placeholder="Enter 15-digit GSTIN (e.g., 27AAAAA0000A1Z5)"
                                       maxlength="15">
                                <button class="btn btn-warning" type="button" id="btn-verify-gst">Verify & Auto-fill</button>
                            </div>
                            <small class="text-muted d-block mt-1">Format: 15 characters (e.g., 27AAAAA0000A1Z5)</small>
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
                                        <p class="text-muted">{{ $user->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Last Updated:</strong></p>
                                        <p class="text-muted">{{ $user->updated_at->format('d/m/Y') }}</p>
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

    // Load cities when state changes
    $('#state_id').change(function() {
        const stateId = $(this).val();
        const $citySelect = $('#city_id');
        
        $citySelect.prop('disabled', true).html('<option value="">Loading...</option>');
        
        if (!stateId) {
            $citySelect.prop('disabled', false).html('<option value="">Select City</option>');
            return;
        }
        
        $.ajax({
            url: '{{ route("cities.json") }}',
            type: 'GET',
            data: { state_id: stateId },
            success: function(cities) {
                let html = '<option value="">Select City</option>';
                cities.forEach(function(city) {
                    html += `<option value="${city.id}">${city.name}</option>`;
                });
                $citySelect.prop('disabled', false).html(html);
            },
            error: function() {
                $citySelect.prop('disabled', false).html('<option value="">Select City</option>');
            }
        });
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

    // Reset cities dropdown
    const originalStateId = '{{ $user->state_id }}';
    if (originalStateId) {
        $('#state_id').val(originalStateId).trigger('change');
        // Note: The actual city selection will happen in the change event's success callback
        // This is a bit tricky with reset, but since it's an AJAX call, we'll manually set it after a delay
        setTimeout(() => {
            $('#city_id').val('{{ $user->city_id }}');
        }, 500);
    } else {
        $('#city_id').html('<option value="">Select City</option>');
    }
    
    // Clear errors
    $('.is-invalid').removeClass('is-invalid');
    $('#alert-container').empty();
}
function toggleGstField() {
    const isGst = document.getElementById('is_gst').value === '1';
    const gstContainer = document.getElementById('gst-container');
    const gstInput = document.getElementById('gstin');
    
    if (isGst) {
        gstContainer.style.display = 'block';
        gstInput.setAttribute('required', 'required');
    } else {
        gstContainer.style.display = 'none';
        gstInput.removeAttribute('required');
        gstInput.value = ''; // Clear value if not business
    }
}

$(document).ready(function() {
    $('#btn-verify-gst').click(function() {
        const gstin = $('#gstin').val();
        if(!gstin || gstin.length !== 15) {
            showAlert('danger', 'Please enter a valid 15-digit GSTIN first.');
            return;
        }

        const $btn = $(this);
        const originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...');

        $.ajax({
            url: '{{ route("verify.gst") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                gstin: gstin
            },
            success: function(response) {
                if(response.success && response.data && response.data.data) {
                    const gstData = response.data.data;
                    
                    // Auto-fill form fields
                    if (gstData.tradeName || gstData.legalName) {
                        $('#name').val(gstData.tradeName || gstData.legalName);
                    }
                    
                    if (gstData.pradr && gstData.pradr.addr) {
                        const addr = gstData.pradr.addr;
                        const addressParts = [];
                        if (addr.bno) addressParts.push(addr.bno);
                        if (addr.st) addressParts.push(addr.st);
                        if (addr.loc) addressParts.push(addr.loc);
                        if (addr.dst) addressParts.push(addr.dst);
                        if (addr.stcd) addressParts.push(addr.stcd);
                        if (addr.pncd) addressParts.push(addr.pncd);
                        
                        if (addressParts.length > 0) {
                            $('#address').val(addressParts.join(', '));
                        }
                    }
                    showAlert('success', 'GST details fetched and form auto-filled successfully! Please review and click Update Profile.');
                } else {
                    showAlert('warning', 'Could not fetch GST details or GSTIN is invalid.');
                }
            },
            error: function(xhr) {
                showAlert('danger', 'Error verifying GSTIN. Please check the GSTIN or try again later.');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endsection
