@extends('layouts.app')

@section('title', 'Edit Outfit - Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/listed-clothes.css') }}">
<style>
    :root {
        --premium-gold: #f78c1c;
        --card-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.07);
    }

    .edit-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .glass-card {
        background: white;
        border-radius: 24px;
        border: 1px solid #f1f5f9;
        box-shadow: var(--card-shadow);
        padding: 2.5rem;
        margin-bottom: 2rem;
        
        
    }


    .form-check-input {
        position: absolute;
        margin-top: .3rem;
        margin-left: -2.5rem;
    }






    .form-section-title {
        font-weight: 800;
        color: #1e293b;
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-title i {
        color: var(--premium-gold);
    }

    .premium-input-group {
        margin-bottom: 1.5rem;
    }

    .premium-input-group label {
        display: block;
        font-weight: 700;
        color: #475569;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control, .form-select {
        height: 50px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        padding: 0 1.25rem;
        font-weight: 500;
        transition: all 0.2s;
        background: #f8fafc;
    }

    textarea.form-control {
        height: auto;
        padding: 1rem 1.25rem;
    }

    .form-control:focus {
        border-color: var(--premium-gold);
        background: white;
        box-shadow: 0 0 0 4px rgba(247, 140, 28, 0.1);
    }

    /* Sticky Sidebar Actions */
    .sticky-sidebar {
        position: sticky;
        top: 100px;
    }

    .action-card {
        background: #1e293b;
        border-radius: 24px;
        padding: 2rem;
        color: white;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .image-grid-edit {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 1rem;
    }
    @media (max-width: 768px) {
        .image-grid-edit {
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }
    }

    @media (max-width: 991px) {
        .btn-premium-save {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            width: calc(100% - 40px);
            margin-top: 0;
            z-index: 1050;
            padding: 0.75rem 1.5rem;
            font-size: 0.85rem;
            border-radius: 50px;
            box-shadow: 0 12px 30px rgba(247, 140, 28, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .sticky-sidebar {
            position: static;
        }

        .edit-container {
            padding-bottom: 100px;
        }
    }

    .edit-image-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid rgba(255,255,255,0.1);
    }

    .edit-image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .remove-btn-overlay {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 24px;
        height: 24px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
    }

    .remove-btn-overlay:hover {
        transform: scale(1.15);
    }

    .upload-btn-placeholder {
        aspect-ratio: 1;
        border: 2px dashed rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        color: rgba(255,255,255,0.6);
        font-size: 0.7rem;
        text-align: center;
        padding: 5px;
    }

    .upload-btn-placeholder:hover {
        background: rgba(255,255,255,0.05);
        border-color: var(--premium-gold);
        color: white;
    }

    .btn-premium-save {
        background: linear-gradient(135deg, #f78c1c 0%, #e87b11 100%);
        color: white;
        border: none;
        border-radius: 14px;
        padding: 1rem;
        font-weight: 800;
        width: 90%;
        margin-bottom: 41px;
        margin-top: 1.5rem;
        letter-spacing: 1px;
        box-shadow: 0 10px 20px rgba(247, 140, 28, 0.3);
        transition: all 0.3s;
    }

    .btn-premium-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(247, 140, 28, 0.4);
        color: white;
    }

    /* AI Button Mini */
    .ai-btn-mini {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border-radius: 10px;
        padding: 4px 12px;
        font-size: 0.75rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: none;
    }
</style>
<!-- Re-using AI Modal Styles from sell page -->
<style>
/* Premium AI Modal Styles */
.cloud-modal .modal-content {
    background-color: #fff;
    border: none;
    border-radius: 24px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    padding: 0;
    overflow: hidden;
}
.cloud-header {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    padding: 2rem 1.5rem;
    text-align: center;
    color: white;
}
.cloud-body { padding: 2rem; text-align: center; }
.cloud-textarea {
    width: 100%; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 1.25rem;
    resize: none; background: #f8fafc; color: #1e293b; font-size: 1rem;
    outline: none; transition: all 0.3s; height: 140px; margin-bottom: 1.5rem;
}
.cloud-btn {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    border: none; border-radius: 12px; padding: 0.8rem 2.5rem;
    color: white; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;
}
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="edit-container">
        <!-- Breadcrumb / Header -->
        <div class="management-header d-flex justify-content-between align-items-center mb-4">
            <div class="management-title">
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-1 bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ route('listed.clothes') }}" class="text-muted">My Listings</a></li>
                        <li class="breadcrumb-item active fw-bold" aria-current="page">Edit Outfit</li>
                    </ol>
                </nav>
                <h2 class="fw-800 text-dark mb-0">Management Center</h2>
            </div>
            <a href="{{ route('listed.clothes') }}" class="btn-close-management">
                <i class="bi bi-x"></i> <span>Close Edit</span>
            </a>
        </div>

        <form id="editClothForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            
            <div class="row g-4">
                <!-- Left Column: Form Details -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="glass-card">
                        <div class="form-section-title">
                            <i class="bi bi-info-circle-fill"></i> Basic Information
                        </div>
                        
                        <div class="premium-input-group">
                            <label>Outfit Title *</label>
                            <input type="text" class="form-control" name="title" value="{{ $cloth->title }}" required placeholder="e.g. Designer Silk Wedding Saree">
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6 col-6 premium-input-group">
                                <label>Category *</label>
                                <select class="form-select" name="category" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $cloth->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-6 premium-input-group">
                                <label>User Type *</label>
                                <select class="form-select" name="gender" required>
                                    <option value="Boy" {{ $cloth->gender == 'Boy' ? 'selected' : '' }}>Boy</option>
                                    <option value="Girl" {{ $cloth->gender == 'Girl' ? 'selected' : '' }}>Girl</option>
                                    <option value="Men" {{ $cloth->gender == 'Men' ? 'selected' : '' }}>Men</option>
                                    <option value="Women" {{ $cloth->gender == 'Women' ? 'selected' : '' }}>Women</option>
                                </select>
                            </div>
                        </div>

                        <div class="premium-input-group mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="mb-0">Description *</label>
                                <button type="button" class="ai-btn-mini" data-toggle="modal" data-target="#aiDescriptionModal">
                                    <i class="bi bi-stars"></i> AI IMPROVE
                                </button>
                            </div>
                            <textarea class="form-control" name="description" rows="4" required>{{ $cloth->description }}</textarea>
                        </div>
                    </div>

                    <!-- Specifications -->
                    <div class="glass-card">
                        <div class="form-section-title">
                            <i class="bi bi-sliders"></i> Specifications & Fit
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6 col-6 premium-input-group">
                                <label>Brand</label>
                                <select class="form-select" name="brand">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ $cloth->brand_id == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-6 premium-input-group">
                                <label>Fabric</label>
                                <select class="form-select" name="fabric">
                                    <option value="">Select Fabric</option>
                                    @foreach($fabricTypes as $fabricType)
                                        <option value="{{ $fabricType->id }}" {{ $cloth->fabric_id == $fabricType->id ? 'selected' : '' }}>
                                            {{ $fabricType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4 col-6 premium-input-group">
                                <label>Color</label>
                                <select class="form-select" name="color">
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}" {{ $cloth->color_id == $color->id ? 'selected' : '' }}>
                                            {{ $color->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 col-6 premium-input-group">
                                <label>Standard Size *</label>
                                <select class="form-select" name="size" required>
                                    @foreach($sizes as $size)
                                        <option value="{{ $size->id }}" {{ $cloth->size_id == $size->id ? 'selected' : '' }}>
                                            {{ $size->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 col-12 premium-input-group">
                                <label>Condition *</label>
                                <select class="form-select" name="condition" required>
                                    @foreach($garmentConditions as $condition)
                                        <option value="{{ $condition->id }}" {{ $cloth->condition_id == $condition->id ? 'selected' : '' }}>
                                            {{ $condition->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="glass-card">
                        <div class="form-section-title">
                            <i class="bi bi-currency-rupee"></i> Pricing & Deposits
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6 col-6 premium-input-group">
                                <label>Rent Price *</label>
                                <input type="number" class="form-control" name="rent_price" value="{{ $cloth->rent_price }}" required>
                            </div>
                            <div class="col-md-6 col-6 premium-input-group">
                                <label>Security Deposit *</label>
                                <input type="number" class="form-control" name="security_deposit" value="{{ $cloth->security_deposit }}" required>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-4 premium-input-group">
                                <label>Original MRP</label>
                                <input type="number" class="form-control" name="mrp" value="{{ $cloth->mrp }}">
                            </div>
                            <div class="col-md-4 premium-input-group">
                                <label>Quantity (SKU) *</label>
                                <input type="number" class="form-control" name="sku" value="{{ $cloth->sku ?? 1 }}" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mb-0" style="padding-left: 3.5rem;">
                                    <input class="form-check-input" type="checkbox" id="is_purchased" name="is_purchased" value="1" {{ $cloth->is_purchased ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; margin-left:-2.55rem cursor: pointer;">
                                    <label class="form-check-label fw-bold ms-2" for="is_purchased" style="text-transform: none; margin-top:7px">Enable Selling</label>
                                </div>
                            </div>
                        </div>
                        <div id="selling_price_section" class="mt-3" style="display: {{ $cloth->is_purchased ? 'block' : 'none' }};">
                            <div class="premium-input-group">
                                <label>Selling Price (₹)</label>
                                <input type="number" class="form-control" name="selling_price" value="{{ $cloth->selling_price }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sidebar Actions -->
                <div class="col-lg-4">
                    <div class="sticky-sidebar">
                        <div class="action-card mb-4">
                            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-images"></i> Media Gallery
                            </h5>
                            <p class="extra-small text-white-50 mb-3">High quality images help you rent faster.</p>
                            
                            <div class="image-grid-edit" id="current-images">
                                @foreach($cloth->images as $image)
                                    <div class="edit-image-item" data-image-id="{{ $image->id }}">
                                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="Outfit">
                                        <span class="remove-btn-overlay" onclick="removeImage({{ $image->id }})">
                                            <i class="bi bi-x"></i>
                                        </span>
                                    </div>
                                @endforeach
                                
                                <div id="upload-placeholder" class="upload-btn-placeholder" onclick="document.getElementById('image-upload').click()" style="{{ $cloth->images->count() >= 4 ? 'display: none;' : '' }}">
                                    <i class="bi bi-plus-circle fs-4 mb-1"></i>
                                    <span>Add More</span>
                                </div>
                            </div>
                            <input type="file" id="image-upload" multiple accept="image/*" style="display: none;">

                            <hr class="my-4 border-white-10" style="opacity: 0.1;">
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="small fw-bold">Availability Status</span>
                                <span class="badge rounded-pill bg-success px-3">Live</span>
                            </div>

                            <button type="submit" class="btn btn-premium-save">
                                <i class="bi bi-cloud-arrow-up me-2"></i> UPDATE CHANGES
                            </button>
                        </div>

                        <!-- Availability Quick Access -->
                        <div class="glass-card p-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-calendar-check me-2"></i> Rental Blocks</h6>
                            <p class="extra-small text-muted mb-3">You have {{ $cloth->availabilityBlocks->count() }} active date blocks.</p>
                            <a href="#availability-section" class="btn btn-light btn-sm w-100 rounded-pill fw-bold">Manage Calendar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full Width Measurement Section -->
            <div class="glass-card mt-4" id="measurement-section">
                <div class="form-section-title">
                    <i class="bi bi-rulers"></i> Detailed Measurements
                </div>
                <div class="row g-2">
                    <div class="col-4 col-md-2 premium-input-group">
                        <label>Bust/Chest</label>
                        <input type="text" class="form-control" name="chest_bust" value="{{ $cloth->chest_bust }}">
                    </div>
                    <div class="col-4 col-md-2 premium-input-group">
                        <label>Waist</label>
                        <input type="text" class="form-control" name="waist" value="{{ $cloth->waist }}">
                    </div>
                    <div class="col-4 col-md-2 premium-input-group">
                        <label>Length</label>
                        <input type="text" class="form-control" name="length" value="{{ $cloth->length }}">
                    </div>
                    <div class="col-4 col-md-2 premium-input-group">
                        <label>Shoulder</label>
                        <input type="text" class="form-control" name="shoulder" value="{{ $cloth->shoulder }}">
                    </div>
                    <div class="col-4 col-md-2 premium-input-group">
                        <label>Sleeve</label>
                        <input type="text" class="form-control" name="sleeve_length" value="{{ $cloth->sleeve_length }}">
                    </div>
                    <div class="col-4 col-md-2 premium-input-group">
                        <label>Unit</label>
                        <select class="form-select" name="measurement_unit">
                            <option value="inch" {{ $cloth->measurement_unit == 'inch' ? 'selected' : '' }}>Inches</option>
                            <option value="cm" {{ $cloth->measurement_unit == 'cm' ? 'selected' : '' }}>CM</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Availability Management -->
            <div class="glass-card mt-4" id="availability-section">
                <div class="form-section-title">
                    <i class="bi bi-calendar-range"></i> Manage Availability
                </div>
                
                <div class="row">
                    <!-- Available Dates -->
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="p-3 rounded-4" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.1);">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold text-success mb-0">RENTAL PERIODS</h6>
                                <button type="button" class="btn btn-premium-success btn-sm rounded-pill px-3" onclick="addAvailabilityBlock('available')">
                                    <i class="bi bi-plus-lg me-1"></i> ADD
                                </button>
                            </div>
                            <div id="available-dates">
                                @php $availableCounter = 0; @endphp
                                @foreach($cloth->availabilityBlocks->where('type', 'available') as $index => $block)
                                    <div class="availability-block mb-3 p-3 border-0 shadow-sm rounded-4 position-relative" data-type="available">
                                        <button type="button" class="btn-remove-block" onclick="removeAvailabilityBlock(this)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="extra-small fw-600 text-muted mb-1 d-block">Start Date</label>
                                                <input type="date" class="form-control form-control-sm" name="availability_blocks[{{ $index }}][start_date]" value="{{ $block->start_date->format('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="extra-small fw-600 text-muted mb-1 d-block">End Date</label>
                                                <input type="date" class="form-control form-control-sm" name="availability_blocks[{{ $index }}][end_date]" value="{{ $block->end_date->format('Y-m-d') }}" required>
                                            </div>
                                        </div>
                                        <input type="hidden" name="availability_blocks[{{ $index }}][type]" value="available">
                                    </div>
                                    @php $availableCounter = $index + 1; @endphp
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Blocked Dates -->
                    <div class="col-lg-6">
                        <div class="p-3 rounded-4" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.1);">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold text-danger mb-0">BLOCKED DATES</h6>
                                <button type="button" class="btn btn-premium-danger btn-sm rounded-pill px-3" onclick="addAvailabilityBlock('blocked')">
                                    <i class="bi bi-plus-lg me-1"></i> ADD
                                </button>
                            </div>
                            <div id="blocked-dates">
                                @php $blockedCounter = 100; @endphp
                                @foreach($cloth->availabilityBlocks->where('type', 'blocked') as $index => $block)
                                    <div class="availability-block mb-3 p-3 border-0 shadow-sm rounded-4 position-relative" data-type="blocked">
                                        <button type="button" class="btn-remove-block" onclick="removeAvailabilityBlock(this)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="extra-small fw-600 text-muted mb-1 d-block">Start Date</label>
                                                <input type="date" class="form-control form-control-sm" name="availability_blocks[{{ $index + 100 }}][start_date]" value="{{ $block->start_date->format('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="extra-small fw-600 text-muted mb-1 d-block">End Date</label>
                                                <input type="date" class="form-control form-control-sm" name="availability_blocks[{{ $index + 100 }}][end_date]" value="{{ $block->end_date->format('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <label class="extra-small fw-600 text-muted mb-1 d-block">Reason</label>
                                                <input type="text" class="form-control form-control-sm" name="availability_blocks[{{ $index + 100 }}][reason]" value="{{ $block->reason }}" placeholder="Personal use, etc.">
                                            </div>
                                        </div>
                                        <input type="hidden" name="availability_blocks[{{ $index + 100 }}][type]" value="blocked">
                                    </div>
                                    @php $blockedCounter = $index + 101; @endphp
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- AI Description Modal (Modernized) -->
<div class="modal fade cloud-modal" id="aiDescriptionModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="cloud-header">
        <h5 class="cloud-title text-white">✨ AI IMPROVE ✨</h5>
        <p class="text-white-50 small mb-0">Describe your outfit simply, we'll make it professional</p>
      </div>
      <div class="cloud-body">
        <textarea class="cloud-textarea" id="rawDescription" 
          placeholder="e.g. Red silk saree, golden border, worn once, perfect for grand weddings"></textarea>

        <div id="aiLoading" class="mb-4" style="display: none;">
            <div class="spinner-border text-primary" role="status" style="width: 1.5rem; height: 1.5rem;"></div>
            <p class="mt-2 text-muted small fw-bold">Crafting description...</p>
        </div>

        <button type="button" class="cloud-btn w-100" onclick="generateAiDescription()">
           <i class="bi bi-stars"></i> Generate Now
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
    window.editClothUpdateUrl = '{{ route("listed.clothes.update", $cloth->id) }}';
    window.listedClothesUrl = '{{ route("listed.clothes") }}';
    window.availableCounter = {{ $availableCounter ?? 0 }};
    window.blockedCounter = {{ $blockedCounter ?? 100 }};
</script>
<script src="{{ asset('js/edit-cloth.js') }}"></script>
<script>
function generateAiDescription() {
    const rawDescription = $('#rawDescription').val();
    if (!rawDescription) { alert('Please enter some keywords.'); return; }
    $('#aiLoading').show();
    $.ajax({
        url: '{{ route("generate.description") }}',
        method: 'POST',
        data: { raw_description: rawDescription, title: $('input[name="title"]').val(), _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.description) {
                $('textarea[name="description"]').val(response.description);
                $('#aiDescriptionModal').modal('hide');
            }
        },
        error: function(xhr) { alert('AI Error: ' + (xhr.responseJSON?.error || 'Failed to generate')); },
        complete: function() { $('#aiLoading').hide(); }
    });
}

$(document).ready(function() {
    $('#is_purchased').on('change', function() {
        $('#selling_price_section').toggle(this.checked);
    });
});
</script>
@endsection
