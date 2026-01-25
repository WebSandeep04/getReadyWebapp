@extends('layouts.app')

@section('title', 'Fix Rejected Item')

@section('styles')
<style>
    .rejection-alert {
        border-left: 5px solid #dc3545;
        background: #fff5f5;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    
    .form-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        margin-bottom: 2rem;
        border: 1px solid #f0f0f0;
    }

    .form-section h5 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 1rem;
    }

    .form-label {
        font-weight: 600;
        color: #4a5568;
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 0.6rem 1rem;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .form-control:focus, .form-select:focus {
        border-color: #3182ce;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
    }

    .upload-zone {
        border: 2px dashed #cbd5e0;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8fafc;
        transition: all 0.2s;
        cursor: pointer;
    }

    .upload-zone:hover {
        border-color: #3182ce;
        background: #ebf8ff;
    }

    .current-image-card {
        transition: transform 0.2s;
        border: 1px solid #eee;
    }

    .current-image-card:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-danger fw-bold mb-1">
                        <i class="bi bi-tools me-2"></i>Fix & Resubmit
                    </h2>
                    <p class="text-muted mb-0">Update the details below to address the rejection reason.</p>
                </div>
                <!-- <a href="{{ route('rejections.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to List
                </a> -->
            </div>

            <!-- Rejection Alert -->
            @if($rejectionNotification)
                <div class="rejection-alert p-4 mb-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-exclamation-circle-fill text-danger fs-4"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <h5 class="text-danger fw-bold mb-2">Rejection Reason</h5>
                            <p class="mb-2 text-dark">{{ $rejectionNotification->data['reject_reason'] ?? 'No specific reason provided.' }}</p>
                            <div class="text-muted small border-top pt-2 mt-2">
                                <i class="bi bi-clock me-1"></i>Rejected on {{ $rejectionNotification->created_at->format('M d, Y \a\t h:i A') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger shadow-sm rounded-3 mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-x-circle-fill me-2 fs-5"></i>
                        <h6 class="mb-0 fw-bold">Please correct the following errors:</h6>
                    </div>
                    <ul class="mb-0 small ps-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('rejections.update', $cloth->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Images Section -->
                <div class="form-section">
                    <h5><i class="bi bi-camera me-2 text-primary"></i>Product Images</h5>
                    
                    <label class="form-label mb-3 d-block">Gallery</label>
                    <div class="d-flex gap-3 mb-4 overflow-auto pb-2" id="current_gallery" style="min-height: 100px;">
                        @foreach($cloth->images as $img)
                            <div class="position-relative current-image-item" id="img-card-{{ $img->id }}" style="width: 100px; height: 100px; flex-shrink: 0;">
                                <div class="rounded-3 overflow-hidden border w-100 h-100">
                                    <a href="{{ asset('storage/' . $img->image_path) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $img->image_path) }}" class="w-100 h-100 object-fit-cover">
                                    </a>
                                </div>
                                <button type="button" 
                                        class="btn btn-danger btn-sm position-absolute rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" 
                                        style="width: 24px; height: 24px; z-index: 10; top: 5px; right: 5px;"
                                        onclick="removeImage({{ $img->id }})">
                                    <i class="bi bi-x-lg" style="font-size: 12px; -webkit-text-stroke: 1px;"></i>
                                </button>
                            </div>
                        @endforeach
                        <!-- Dynamic Previews will appear here -->
                    </div>
                    <!-- Container for deleted image IDs -->
                    <div id="deleted_images_container"></div>

                    <div class="form-group">
                        <label class="form-label">Upload New Images (Optional)</label>
                        <div class="upload-zone position-relative">
                            <input type="file" id="new_images_input" name="new_images[]" multiple accept="image/*" 
                                   class="position-absolute top-0 start-0 w-100 h-100" 
                                   style="cursor: pointer; opacity: 0; z-index: 10;"
                                   onchange="previewImages(this)">
                            <div class="text-center">
                                <i class="bi bi-cloud-arrow-up fs-2 text-primary mb-2"></i>
                                <h6 class="fw-bold text-dark">Click or Drag & Drop to Upload</h6>
                                <p class="small text-muted mb-0">Supported: JPG, PNG, WEBP.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function removeImage(id) {
                        if (confirm('Are you sure you want to remove this image?')) {
                            // Hide the image card
                            document.getElementById('img-card-' + id).style.display = 'none';
                            
                            // Add hidden input to form
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'deleted_images[]';
                            input.value = id;
                            document.getElementById('deleted_images_container').appendChild(input);
                        }
                    }

                    function previewImages(input) {
                        var gallery = document.getElementById('current_gallery');
                        
                        // Remove existing previews
                        var previews = gallery.getElementsByClassName('preview-item');
                        while(previews.length > 0){
                            previews[0].parentNode.removeChild(previews[0]);
                        }

                        if (input.files) {
                            Array.from(input.files).forEach(function(file) {
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    var div = document.createElement('div');
                                    div.className = 'position-relative current-image-item preview-item';
                                    div.style.width = '100px';
                                    div.style.height = '100px';
                                    div.style.flexShrink = '0';
                                    
                                    div.innerHTML = `
                                        <div class="rounded-3 overflow-hidden border w-100 h-100 bg-light">
                                            <img src="${e.target.result}" class="w-100 h-100 object-fit-cover" style="opacity: 0.8;">
                                        </div>
                                        <div class="position-absolute bottom-0 start-0 w-100 text-center bg-success text-white small" style="font-size: 10px; padding: 2px;">New</div>
                                    `;
                                    gallery.appendChild(div);
                                }
                                reader.readAsDataURL(file);
                            });
                        }
                    }
                </script>

                <!-- Basic Info -->
                <div class="form-section">
                    <h5><i class="bi bi-tag me-2 text-primary"></i>Basic Details</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="title" class="form-label">Item Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ $cloth->title }}" required placeholder="e.g. Blue Denim Jacket">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="3" required placeholder="Describe the item, material, style, etc.">{{ $cloth->description }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $cloth->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender" class="form-label">User Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Target Audience</option>
                                    <option value="Men" {{ $cloth->gender == 'Men' ? 'selected' : '' }}>Men</option>
                                    <option value="Women" {{ $cloth->gender == 'Women' ? 'selected' : '' }}>Women</option>
                                    <option value="Boy" {{ $cloth->gender == 'Boy' ? 'selected' : '' }}>Boy</option>
                                    <option value="Girl" {{ $cloth->gender == 'Girl' ? 'selected' : '' }}>Girl</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="size" class="form-label">Size <span class="text-danger">*</span></label>
                                <select class="form-select" id="size" name="size" required>
                                    @foreach($sizes as $size)
                                        <option value="{{ $size->id }}" {{ $cloth->size_id == $size->id ? 'selected' : '' }}>{{ $size->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="condition" class="form-label">Condition <span class="text-danger">*</span></label>
                                <select class="form-select" id="condition" name="condition" required>
                                    @foreach($garmentConditions as $cond)
                                        <option value="{{ $cond->id }}" {{ $cloth->condition_id == $cond->id ? 'selected' : '' }}>{{ $cond->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <select class="form-select" name="brand">
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ $cloth->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <select class="form-select" name="color">
                                <option value="">Select Color</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color->id }}" {{ $cloth->color_id == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Attributes -->
                <div class="form-section">
                    <h5><i class="bi bi-sliders me-2 text-primary"></i>Attributes & Style</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fabric</label>
                            <select class="form-select" name="fabric">
                                <option value="">Select Fabric</option>
                                @foreach($fabricTypes as $fabric)
                                    <option value="{{ $fabric->id }}" {{ $cloth->fabric_id == $fabric->id ? 'selected' : '' }}>{{ $fabric->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bottom Type</label>
                            <select class="form-select" name="bottom_type">
                                <option value="">None</option>
                                @foreach($bottomTypes as $bt)
                                    <option value="{{ $bt->id }}" {{ $cloth->bottom_type_id == $bt->id ? 'selected' : '' }}>{{ $bt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fit Type</label>
                            <select class="form-select" name="fit_type">
                                <option value="">None</option>
                                @foreach($fitTypes as $ft)
                                    <option value="{{ $ft->id }}" {{ $cloth->fit_type_id == $ft->id ? 'selected' : '' }}>{{ $ft->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Defects / Notes</label>
                            <textarea class="form-control" name="defects" rows="3" placeholder="Describe any minor defects or specific care instructions...">{{ $cloth->defects }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Measurements (Optional) -->
                <div class="form-section">
                    <h5><i class="bi bi-rulers me-2 text-primary"></i>Measurements (Optional)</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="chest_bust" class="form-label">Chest/Bust (inches)</label>
                                <input type="number" class="form-control" id="chest_bust" name="chest_bust" 
                                       value="{{ $cloth->chest_bust }}" min="0" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="waist" class="form-label">Waist (inches)</label>
                                <input type="number" class="form-control" id="waist" name="waist" 
                                       value="{{ $cloth->waist }}" min="0" step="0.1">
                            </div>
                        </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="length" class="form-label">Length (inches)</label>
                                <input type="number" class="form-control" id="length" name="length" 
                                       value="{{ $cloth->length }}" min="0" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shoulder" class="form-label">Shoulder (inches)</label>
                                <input type="number" class="form-control" id="shoulder" name="shoulder" 
                                       value="{{ $cloth->shoulder }}" min="0" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sleeve_length" class="form-label">Sleeve Length (inches)</label>
                                <input type="number" class="form-control" id="sleeve_length" name="sleeve_length" 
                                       value="{{ $cloth->sleeve_length }}" min="0" step="0.1">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financials & Submit -->
                <div class="form-section">
                    <h5><i class="bi bi-wallet2 me-2 text-primary"></i>Pricing & Submit</h5>
                    <div class="row g-3">
                         <div class="col-md-6">
                            <label class="form-label">Rent Price (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">₹</span>
                                <input type="number" class="form-control border-start-0 ps-0" id="rent_price" name="rent_price" value="{{ $cloth->rent_price }}" required min="0">
                            </div>
                        </div>
                         <div class="col-md-6">
                            <label class="form-label">Security Deposit (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">₹</span>
                                <input type="number" class="form-control border-start-0 ps-0" id="security_deposit" name="security_deposit" value="{{ $cloth->security_deposit }}" required min="0">
                            </div>
                        </div>
                    </div>
                    
                    
                </div>
                <div class="mt-4">
                        <button type="submit" class="btn btn-success btn-lg w-100 shadow fw-bold p-3" id="submitBtn" style="letter-spacing: 0.5px;">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>UPDATE & RESUBMIT
                        </button>
                    </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle form submission
    $('form').on('submit', function(e) {
        var $submitBtn = $('#submitBtn');
        // Show loading state
        $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Processing...');
    });
    
    // Handle form errors
    @if($errors->any())
        // Scroll to error alert
        $('html, body').animate({
            scrollTop: $('.alert-danger').offset().top - 100
        }, 500);
    @endif
});
</script>
@endpush
@endsection
