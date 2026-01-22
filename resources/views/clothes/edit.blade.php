@extends('layouts.app')

@section('title', 'Edit Cloth')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/listed-clothes.css') }}">
<style>
    /* Ensure proper spacing for fixed navigation on edit page */
    .container {
        margin-top: 70px;
    }

    .top-nav {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1030 !important;
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    }
    

    .image-preview {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        margin: 5px;
        border: 2px solid #ddd;
    }
    .image-container {
        position: relative;
        display: inline-block;
        margin: 5px;
    }
    .remove-image {
        position: absolute;
        top: -5px;
        right: -5px;
        background: red;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        text-align: center;
        line-height: 20px;
        cursor: pointer;
        font-size: 12px;
    }
    .upload-area {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        background: #f9f9f9;
        cursor: pointer;
        transition: all 0.3s;
    }
    .upload-area:hover {
        border-color: #007bff;
        background: #f0f8ff;
    }
    .upload-area.dragover {
        border-color: #007bff;
        background: #e3f2fd;
    }
    .availability-block {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    .availability-block:hover {
        border-color: #007bff;
        background: #f0f8ff;
    }
    .availability-block .form-control-sm {
        font-size: 0.875rem;
    }
    .availability-block .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .alert-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    
    /* Rejection Alert Styling */
    #rejectionAlert {
        border-left: 4px solid #dc3545;
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        border-color: #ffc107;
        color: #856404;
    }
    
    #rejectionAlert .alert-heading {
        color: #dc3545;
        font-weight: 600;
    }
    
    #rejectionAlert .bi-exclamation-triangle-fill {
        color: #dc3545;
    }
    
    #rejectionAlert .btn-close {
        filter: invert(1);
    }
    
    #rejectionAlert:hover {
        background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-warning">Edit Cloth</h2>
                <a href="{{ route('listed.clothes') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Listed Clothes
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <!-- <h5 class="mb-0">Edit Cloth Details</h5> -->
                </div>
                <div class="card-body">
                    <form id="editClothForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{ $cloth->title }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Category *</label>
                                    <select class="form-control" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ $cloth->category == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="description" class="mb-0">Description *</label>
                                        <img src="{{ asset('images/icon/gemini_logo.jpeg') }}" alt="Generate with AI" data-toggle="modal" data-target="#aiDescriptionModal" title="Generate with AI" style="cursor: pointer; height: 30px; width: auto;">
                                    </div>
                                    <textarea class="form-control" id="description" name="description" rows="3" required>{{ $cloth->description ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender">User Type *</label>
                                    <select class="form-control" id="gender" name="gender" required>
                                        <option value="">Select User Type</option>
                                        <option value="Boy" {{ $cloth->gender == 'Boy' ? 'selected' : '' }}>Boy</option>
                                        <option value="Girl" {{ $cloth->gender == 'Girl' ? 'selected' : '' }}>Girl</option>
                                        <option value="Men" {{ $cloth->gender == 'Men' ? 'selected' : '' }}>Men</option>
                                        <option value="Women" {{ $cloth->gender == 'Women' ? 'selected' : '' }}>Women</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fabric">Fabric Type</label>
                                    <select class="form-control" id="fabric" name="fabric">
                                        <option value="">Select Fabric Type</option>
                                        @foreach($fabricTypes as $fabricType)
                                            <option value="{{ $fabricType->id }}" {{ $cloth->fabric == $fabricType->id ? 'selected' : '' }}>
                                                {{ $fabricType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Color</label>
                                    <select class="form-control" id="color" name="color">
                                        <option value="">Select Color</option>
                                        @foreach($colors as $color)
                                            <option value="{{ $color->id }}" {{ $cloth->color == $color->id ? 'selected' : '' }}>
                                                {{ $color->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="size">Size *</label>
                                    <select class="form-control" id="size" name="size" required>
                                        <option value="">Select Size</option>
                                        @foreach($sizes as $size)
                                            <option value="{{ $size->id }}" {{ $cloth->size == $size->id ? 'selected' : '' }}>
                                                {{ $size->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand">Brand</label>
                                    <select class="form-control" id="brand" name="brand">
                                        <option value="">Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->name }}" {{ $cloth->brand == $brand->name ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fit_type">Fit Type</label>
                                    <select class="form-control" id="fit_type" name="fit_type">
                                        <option value="">Select Fit Type</option>
                                        @foreach($fitTypes as $fitType)
                                            <option value="{{ $fitType->id }}" {{ $cloth->fit_type == $fitType->id ? 'selected' : '' }}>
                                                {{ $fitType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="condition">Condition *</label>
                                    <select class="form-control" id="condition" name="condition" required>
                                        <option value="">Select Condition</option>
                                        <option value="Brand New" {{ $cloth->condition == 'Brand New' ? 'selected' : '' }}>Brand New</option>
                                        <option value="Like New" {{ $cloth->condition == 'Like New' ? 'selected' : '' }}>Like New</option>
                                        <option value="Excellent" {{ $cloth->condition == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                        <option value="Good" {{ $cloth->condition == 'Good' ? 'selected' : '' }}>Good</option>
                                        <option value="Fair" {{ $cloth->condition == 'Fair' ? 'selected' : '' }}>Fair</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rent_price">Rent Price (₹) *</label>
                                    <input type="number" class="form-control" id="rent_price" name="rent_price" value="{{ $cloth->rent_price }}" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_purchased" name="is_purchased" value="1" {{ $cloth->is_purchased ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_purchased">Available for Purchase</label>
                                    </div>
                                </div>
                                <div class="form-group" id="purchase_value_section" style="display: {{ $cloth->is_purchased ? 'block' : 'none' }};">
                                    <label for="purchase_value">Purchase Value (₹)</label>
                                    <input type="number" class="form-control" id="purchase_value" name="purchase_value" value="{{ $cloth->purchase_value }}" min="0" step="0.01">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="security_deposit">Security Deposit (₹) *</label>
                                    <input type="number" class="form-control" id="security_deposit" name="security_deposit" value="{{ $cloth->security_deposit }}" min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="chest_bust">Chest/Bust (inches)</label>
                                    <input type="text" class="form-control" id="chest_bust" name="chest_bust" value="{{ $cloth->chest_bust }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="waist">Waist (inches)</label>
                                    <input type="text" class="form-control" id="waist" name="waist" value="{{ $cloth->waist }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="length">Length (inches)</label>
                                    <input type="text" class="form-control" id="length" name="length" value="{{ $cloth->length }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shoulder">Shoulder (inches)</label>
                                    <input type="text" class="form-control" id="shoulder" name="shoulder" value="{{ $cloth->shoulder }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sleeve_length">Sleeve Length (inches)</label>
                                    <input type="text" class="form-control" id="sleeve_length" name="sleeve_length" value="{{ $cloth->sleeve_length }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="defects">Defects (if any)</label>
                            <textarea class="form-control" id="defects" name="defects" rows="3">{{ $cloth->defects }}</textarea>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_cleaned" name="is_cleaned" value="1" {{ $cloth->is_cleaned ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_cleaned">Is Cleaned</label>
                            </div>
                        </div>

                        <!-- Availability Section -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">📆 Availability Management</h5>
                                <small class="text-muted">Manage when your cloth is available for rent or blocked for personal use</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Available Dates</h6>
                                        <p class="text-muted small">Set specific dates when this cloth is available for rent</p>
                                        <div class="alert alert-info alert-sm">
                                            <i class="fas fa-info-circle"></i>
                                            <small>Tip: Leave empty if the cloth is always available. Add specific dates for limited availability.</small>
                                        </div>
                                        <div id="available-dates">
                                            @foreach($cloth->availabilityBlocks->where('type', 'available') as $index => $block)
                                                <div class="availability-block mb-3" data-type="available">
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <label class="small">Start Date</label>
                                                            <input type="date" class="form-control form-control-sm" name="availability_blocks[{{ $index }}][start_date]" value="{{ $block->start_date->format('Y-m-d') }}">
                                                        </div>
                                                        <div class="col-md-5">
                                                            <label class="small">End Date</label>
                                                            <input type="date" class="form-control form-control-sm" name="availability_blocks[{{ $index }}][end_date]" value="{{ $block->end_date->format('Y-m-d') }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">&nbsp;</label>
                                                            <button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeAvailabilityBlock(this)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="availability_blocks[{{ $index }}][type]" value="available">
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm" onclick="addAvailabilityBlock('available')">
                                            <i class="fas fa-plus"></i> Add Available Date
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h6>Blocked Dates</h6>
                                        <p class="text-muted small">Set dates when you plan to use the cloth yourself</p>
                                        <div class="alert alert-warning alert-sm">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <small>Tip: Block dates when you'll be using the cloth personally to avoid rental conflicts.</small>
                                        </div>
                                        <div id="blocked-dates">
                                            @foreach($cloth->availabilityBlocks->where('type', 'blocked') as $index => $block)
                                                <div class="availability-block mb-3" data-type="blocked">
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <label class="small">Start Date</label>
                                                            <input type="date" class="form-control form-control-sm" name="availability_blocks[{{ $index + 100 }}][start_date]" value="{{ $block->start_date->format('Y-m-d') }}">
                                                        </div>
                                                        <div class="col-md-5">
                                                            <label class="small">End Date</label>
                                                            <input type="date" class="form-control form-control-sm" name="availability_blocks[{{ $index + 100 }}][end_date]" value="{{ $block->end_date->format('Y-m-d') }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="small">&nbsp;</label>
                                                            <button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeAvailabilityBlock(this)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-2">
                                                        <div class="col-12">
                                                            <label class="small">Reason (optional)</label>
                                                            <input type="text" class="form-control form-control-sm" name="availability_blocks[{{ $index + 100 }}][reason]" value="{{ $block->reason }}" placeholder="e.g., Personal use, Maintenance">
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="availability_blocks[{{ $index + 100 }}][type]" value="blocked">
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="addAvailabilityBlock('blocked')">
                                            <i class="fas fa-plus"></i> Add Blocked Date
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Cloth
                            </button>
                            <a href="{{ route('listed.clothes') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Images Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Manage Images</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="current-images">
                                @foreach($cloth->images as $image)
                                    <div class="image-container" data-image-id="{{ $image->id }}">
                                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="Cloth Image" class="image-preview">
                                        <span class="remove-image" onclick="removeImage({{ $image->id }})">×</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="upload-area" id="upload-area" onclick="document.getElementById('image-upload').click()">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="mb-0">Click to upload new images</p>
                                <small class="text-muted">Drag and drop images here</small>
                            </div>
                            <input type="file" id="image-upload" multiple accept="image/*" style="display: none;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Description Modal -->
<div class="modal fade" id="aiDescriptionModal" tabindex="-1" role="dialog" aria-labelledby="aiDescriptionModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="aiDescriptionModalLabel">Generate Description with AI</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="rawDescription">Enter basic details (keywords, condition, style):</label>
          <textarea class="form-control" id="rawDescription" rows="4" placeholder="e.g. Blue silk saree, worn once, golden border, perfect for weddings"></textarea>
        </div>
        <div id="aiLoading" class="text-center" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
          </div>
          <p class="mt-2">Generating description...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="generateAiDescription()">Generate</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript Variables -->
<script>
    // Pass PHP variables to JavaScript
    window.editClothUpdateUrl = '{{ route("listed.clothes.update", $cloth->id) }}';
    window.listedClothesUrl = '{{ route("listed.clothes") }}';
    window.availableCounter = {{ $cloth->availabilityBlocks->where('type', 'available')->count() }};
    window.blockedCounter = {{ $cloth->availabilityBlocks->where('type', 'blocked')->count() }};
</script>

<!-- Include external JavaScript file -->
<script src="{{ asset('js/edit-cloth.js') }}"></script>

<!-- Rejection Message Handler -->
<script>
$(document).ready(function() {
    // Check if there's a rejection message in sessionStorage
    const rejectionReason = sessionStorage.getItem('rejectionReason');
    const notificationId = sessionStorage.getItem('rejectionNotificationId');
    
    if (rejectionReason && rejectionReason.trim() !== '') {
        try {
            // Display the rejection alert
            $('#rejectionMessage').text(rejectionReason);
            $('#rejectionAlert').show();
            
            // Clear the sessionStorage after displaying
            sessionStorage.removeItem('rejectionReason');
            sessionStorage.removeItem('rejectionNotificationId');
            
            // Scroll to the alert
            $('#rejectionAlert')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Auto-hide the alert after 10 seconds
            setTimeout(function() {
                $('#rejectionAlert').fadeOut();
            }, 10000);
        } catch (error) {
            console.error('Error displaying rejection message:', error);
        }
    }
});
</script>

<script>
function generateAiDescription() {
    const rawDescription = $('#rawDescription').val();
    if (!rawDescription) {
        alert('Please enter some details.');
        return;
    }

    $('#aiLoading').show();
    
    const title = $('#title').val();
    
    $.ajax({
        url: '{{ route("generate.description") }}',
        method: 'POST',
        data: {
            raw_description: rawDescription,
            title: title,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.description) {
                // Use ID selector for the textarea in edit page
                $('#description').val(response.description);
                $('#aiDescriptionModal').modal('hide');
            }
        },
        error: function(xhr) {
            alert('Error generating description: ' + (xhr.responseJSON?.error || 'Unknown error'));
        },
        complete: function() {
            $('#aiLoading').hide();
        }
    });
}
</script>

@endsection 