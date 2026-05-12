@extends('layouts.app')

@section('title', 'Fix Rejected Item')

@section('styles')
<style>
    :root {
        --premium-gold: #FFA500;
        --premium-gold-dark: #FF8C00;
        --rejection-red: #ef4444;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --bg-light: #f8fafc;
        --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    body {
        background-color: #f1f5f9;
        font-family: 'Outfit', sans-serif;
    }

    .rejection-page-wrapper {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Left Side Sticky Sidebar */
    .rejection-sidebar {
        position: sticky;
        top: 100px;
    }

    .rejection-reason-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #fee2e2;
        box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.08);
        position: relative;
    }

    .rejection-reason-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 6px;
        background: linear-gradient(to bottom, #ef4444, #f87171);
    }

    .rejection-reason-header {
        background: linear-gradient(135deg, #fffcfc 0%, #fff5f5 100%);
        padding: 2rem;
        border-bottom: 1px solid rgba(239, 68, 68, 0.05);
    }

    /* Form Sections */
    .glass-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        box-shadow: var(--card-shadow);
        padding: 2rem;
        margin-bottom: 1.75rem;
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: var(--premium-gold);
        font-size: 1.25rem;
    }

    /* Custom Form Inputs */
    .form-label {
        display: block;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0rem !important;
        margin-top: 0.6rem;
    }

    .form-control, .form-select {
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        color: var(--text-dark);
        transition: all 0.2s ease;
        height: 50px;
    }

    textarea.form-control {
        height: auto;
        min-height: 100px;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--premium-gold);
        box-shadow: 0 0 0 4px rgba(255, 165, 0, 0.1);
        outline: none;
    }

    /* Image Gallery */
    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 12px;
    }

    .image-item {
        position: relative;
        aspect-ratio: 3/4;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid #f1f5f9;
        transition: all 0.3s ease;
    }

    .image-item:hover {
        border-color: var(--premium-gold);
        transform: translateY(-3px);
    }

    .image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .remove-img-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 22px;
        height: 22px;
        background: var(--rejection-red);
        color: white;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        cursor: pointer;
        z-index: 5;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    /* Upload Zone */
    .upload-box {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .upload-box:hover {
        border-color: var(--premium-gold);
        background: #fffcf5;
    }

    /* Measurement Inputs */
    .measurement-card {
        background: #fdfcfb;
        border: 1px solid #f3f4f6;
    }

    .unit-toggle {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 8px;
        display: inline-flex;
    }

    .unit-btn {
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .unit-btn.active {
        background: white;
        color: var(--premium-gold);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Submit Section */
    .submit-bar {
        position: sticky;
        bottom: 20px;
        background: white;
        padding: 1rem;
        border-radius: 16px;
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.05);
        z-index: 100;
        margin-top: 2rem;
    }

    .btn-premium {
        background: linear-gradient(135deg, var(--premium-gold) 0%, #FF7F50 100%);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(255, 165, 0, 0.2);
    }

    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 25px rgba(255, 165, 0, 0.3);
        color: white;
    }

    /* Badge */
    .new-badge {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(34, 197, 94, 0.9);
        color: white;
        font-size: 8px;
        font-weight: 800;
        text-align: center;
        padding: 2px 0;
        text-transform: uppercase;
    }

    @media (max-width: 991px) {
        .rejection-sidebar {
            position: static;
            margin-bottom: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="rejection-page-wrapper">
        <!-- Breadcrumb & Title -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-muted text-decoration-none small">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('rejections.index') }}" class="text-muted text-decoration-none small">Rejections</a></li>
                <li class="breadcrumb-item active small" aria-current="page">Fix Item</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Fix & Resubmit</h2>
                <p class="text-muted mb-0 small">Address the feedback and update your listing for re-approval.</p>
            </div>
            <a href="{{ route('rejections.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>

        <form action="{{ route('rejections.update', $cloth->id) }}" method="POST" enctype="multipart/form-data" id="rejectionForm">
            @csrf
            @method('PUT')

            <div class="row g-lg-5 g-4">
                <!-- LEFT COLUMN -->
                <div class="col-lg-4">
                    <div class="rejection-sidebar">
                        <!-- Rejection Reason Card -->
                        <div class="rejection-reason-card mb-4">
                            <div class="rejection-reason-header">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="bg-danger-subtle p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(239, 68, 68, 0.1);">
                                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="text-danger fw-bold extra-small text-uppercase tracking-wider">Rejection Feedback</span>
                                        <div class="text-muted small" style="font-size: 0.7rem;">From Admin Review</div>
                                    </div>
                                </div>
                                <h4 class="fw-bold text-dark mb-0 ps-1" style="line-height: 1.5; font-size: 1.4rem; letter-spacing: -0.02em;">
                                    "{{ $rejectionNotification->data['reject_reason'] ?? 'Needs review by seller' }}"
                                </h4>
                            </div>
                            <div class="p-3 bg-white">
                                <div class="d-flex align-items-center justify-content-between text-muted extra-small">
                                    <span><i class="bi bi-calendar-event me-1"></i> {{ $rejectionNotification->created_at->format('M d, Y') }}</span>
                                    <span><i class="bi bi-clock me-1"></i> {{ $rejectionNotification->created_at->format('h:i A') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Mini Gallery Card -->
                        <div class="glass-card">
                            <div class="section-title">
                                <i class="bi bi-images"></i>
                                <span>Current Gallery</span>
                            </div>
                            <div class="image-grid mb-3" id="current_gallery">
                                @foreach($cloth->images as $img)
                                    <div class="image-item" id="img-card-{{ $img->id }}">
                                        <img src="{{ asset('storage/' . $img->image_path) }}" alt="Product">
                                        <button type="button" class="remove-img-btn" onclick="removeImage({{ $img->id }})">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <div id="deleted_images_container"></div>

                            <div class="upload-box mt-3 position-relative">
                                <input type="file" name="new_images[]" multiple accept="image/*" 
                                       class="position-absolute top-0 start-0 w-100 h-100 cursor-pointer"
                                       onchange="previewImages(this)" style="z-index: 10; opacity: 0;">
                                <i class="bi bi-plus-circle-dotted fs-4 text-muted mb-1 d-block"></i>
                                <span class="text-muted small fw-bold">Add More Photos</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="col-lg-8">
                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                            <ul class="mb-0 small">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Basic Details Section -->
                    <div class="glass-card">
                        <div class="section-title">
                            <i class="bi bi-info-circle"></i>
                            <span>Product Information</span>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-5">
                                <label class="form-label">Item Title *</label>
                                <input type="text" class="form-control" name="title" value="{{ $cloth->title }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $cloth->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">User Type *</label>
                                <select class="form-select" name="gender" required>
                                    <option value="Men" {{ $cloth->gender == 'Men' ? 'selected' : '' }}>Men</option>
                                    <option value="Women" {{ $cloth->gender == 'Women' ? 'selected' : '' }}>Women</option>
                                    <option value="Boy" {{ $cloth->gender == 'Boy' ? 'selected' : '' }}>Boy</option>
                                    <option value="Girl" {{ $cloth->gender == 'Girl' ? 'selected' : '' }}>Girl</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description *</label>
                                <textarea class="form-control" name="description" rows="3" required>{{ $cloth->description }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Specifications Section -->
                    <div class="glass-card">
                        <div class="section-title">
                            <i class="bi bi-sliders"></i>
                            <span>Specifications</span>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-2">
                                <label class="form-label">Size *</label>
                                <select class="form-select" name="size" required>
                                    @foreach($sizes as $size)
                                        <option value="{{ $size->id }}" {{ $cloth->size_id == $size->id ? 'selected' : '' }}>{{ $size->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Condition *</label>
                                <select class="form-select" name="condition" required>
                                    @foreach($garmentConditions as $cond)
                                        <option value="{{ $cond->id }}" {{ $cloth->condition_id == $cond->id ? 'selected' : '' }}>{{ $cond->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Color</label>
                                <select class="form-select" name="color">
                                    <option value="">N/A</option>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}" {{ $cloth->color_id == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Brand</label>
                                <select class="form-select" name="brand">
                                    <option value="">N/A</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ $cloth->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fabric</label>
                                <select class="form-select" name="fabric">
                                    <option value="">N/A</option>
                                    @foreach($fabricTypes as $fabric)
                                        <option value="{{ $fabric->id }}" {{ $cloth->fabric_id == $fabric->id ? 'selected' : '' }}>{{ $fabric->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-7">
                                <label class="form-label">Defects / Notes</label>
                                <textarea class="form-control" name="defects" rows="2" placeholder="Any minor defects...">{{ $cloth->defects }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="glass-card">
                        <div class="section-title">
                            <i class="bi bi-wallet2"></i>
                            <span>Pricing</span>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Rent Price (₹) *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted border-end-0">₹</span>
                                    <input type="number" class="form-control border-start-0" name="rent_price" value="{{ $cloth->rent_price }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Security Deposit (₹) *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted border-end-0">₹</span>
                                    <input type="number" class="form-control border-start-0" name="security_deposit" value="{{ $cloth->security_deposit }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Measurements Section -->
                    <div class="glass-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="section-title mb-0">
                                <i class="bi bi-rulers"></i>
                                <span>Measurements</span>
                            </div>
                            <div class="unit-toggle" id="unitToggleContainer">
                                <input type="hidden" name="measurement_unit" id="measurement_unit" value="{{ $cloth->measurement_unit ?? 'inch' }}">
                                <span class="unit-btn {{ ($cloth->measurement_unit ?? 'inch') == 'inch' ? 'active' : '' }}" onclick="setUnit('inch')">INCH</span>
                                <span class="unit-btn {{ ($cloth->measurement_unit ?? 'inch') == 'cm' ? 'active' : '' }}" onclick="setUnit('cm')">CM</span>
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label small">Chest/Bust (<span class="unit-label">{{ $cloth->measurement_unit ?? 'inch' }}</span>)</label>
                                <input type="number" step="0.1" class="form-control measurement-input" name="chest_bust" value="{{ $cloth->chest_bust }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Waist (<span class="unit-label">{{ $cloth->measurement_unit ?? 'inch' }}</span>)</label>
                                <input type="number" step="0.1" class="form-control measurement-input" name="waist" value="{{ $cloth->waist }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Length (<span class="unit-label">{{ $cloth->measurement_unit ?? 'inch' }}</span>)</label>
                                <input type="number" step="0.1" class="form-control measurement-input" name="length" value="{{ $cloth->length }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Shoulder (<span class="unit-label">{{ $cloth->measurement_unit ?? 'inch' }}</span>)</label>
                                <input type="number" step="0.1" class="form-control measurement-input" name="shoulder" value="{{ $cloth->shoulder }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Sleeve (<span class="unit-label">{{ $cloth->measurement_unit ?? 'inch' }}</span>)</label>
                                <input type="number" step="0.1" class="form-control measurement-input" name="sleeve_length" value="{{ $cloth->sleeve_length }}">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="submit-bar text-center">
                        <button type="submit" class="btn btn-premium w-100 py-3" id="resubmitBtn">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i> RESUBMIT FOR APPROVAL
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    function removeImage(id) {
        if (confirm('Remove this image?')) {
            $(`#img-card-${id}`).fadeOut(300, function() {
                $(this).remove();
                $('#deleted_images_container').append(`<input type="hidden" name="deleted_images[]" value="${id}">`);
            });
        }
    }

    function previewImages(input) {
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const html = `
                        <div class="image-item">
                            <img src="${e.target.result}" style="opacity: 0.7;">
                            <div class="new-badge">New</div>
                        </div>
                    `;
                    $('#current_gallery').append(html);
                };
                reader.readAsDataURL(file);
            });
        }
    }

    function setUnit(unit) {
        const oldUnit = $('#measurement_unit').val();
        if (oldUnit === unit) return;

        $('#measurement_unit').val(unit);
        $('.unit-btn').removeClass('active');
        
        // Use filter to find exact text match for unit buttons
        $('.unit-btn').each(function() {
            if ($(this).text().trim().toLowerCase() === unit.toLowerCase()) {
                $(this).addClass('active');
            }
        });

        $('.unit-label').text(unit);
        
        const isToCm = unit === 'cm';
        const factor = isToCm ? 2.54 : (1 / 2.54);

        $('.measurement-input').each(function() {
            const input = $(this);
            const val = input.val();
            if (val && !isNaN(val)) {
                const newVal = (parseFloat(val) * factor).toFixed(1).replace(/\.0$/, '');
                input.val(newVal);
            }
        });
    }

    $(document).ready(function() {
        $('#rejectionForm').on('submit', function() {
            $('#resubmitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Processing...');
        });
    });
</script>
@endsection
@endsection
