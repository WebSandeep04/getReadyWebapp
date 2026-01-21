@extends('layouts.app')

@section('title', 'Fix Rejected Item')

@push('styles')
<style>
    .rejection-alert {
        border-left: 4px solid #dc3545;
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        border-color: #ffc107;
        color: #856404;
    }
    
    .rejection-alert .alert-heading {
        color: #dc3545;
        font-weight: 600;
    }
    
    .form-section {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-danger mb-1">
                        <i class="bi bi-tools me-2"></i>Fix Rejected Item
                    </h2>
                    <p class="text-muted mb-0">Review the rejection reason and update your item</p>
                </div>
                <a href="{{ route('rejections.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Rejected Items
                </a>
            </div>

            <!-- Rejection Alert -->
            @if($rejectionNotification)
                <div class="alert rejection-alert alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-x-circle me-1"></i>Item Rejected
                            </h6>
                            <p class="mb-2">{{ $rejectionNotification->data['reject_reason'] ?? 'No specific reason provided.' }}</p>
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>Rejected on {{ $rejectionNotification->created_at->format('M d, Y \a\t g:i A') }}
                            </small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>Please fix the following errors:
                    </h6>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Update Form -->
            <form action="{{ route('rejections.update', $cloth->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-section">
                    <h5><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="{{ $cloth->title }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="category" class="form-label">Category *</label>
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
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="gender" class="form-label">User Type *</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="">Select User Type</option>
                                    <option value="Boy" {{ $cloth->gender == 'Boy' ? 'selected' : '' }}>Boy</option>
                                    <option value="Girl" {{ $cloth->gender == 'Girl' ? 'selected' : '' }}>Girl</option>
                                    <option value="Men" {{ $cloth->gender == 'Men' ? 'selected' : '' }}>Men</option>
                                    <option value="Women" {{ $cloth->gender == 'Women' ? 'selected' : '' }}>Women</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" 
                                       value="{{ $cloth->brand }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="size" class="form-label">Size *</label>
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
                            <div class="form-group mb-3">
                                <label for="condition" class="form-label">Condition *</label>
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
                            <div class="form-group mb-3">
                                <label for="fabric" class="form-label">Fabric Type</label>
                                <select class="form-control" id="fabric" name="fabric">
                                    <option value="">Select Fabric Type</option>
                                    @foreach($fabricTypes as $fabric)
                                        <option value="{{ $fabric->id }}" {{ $cloth->fabric == $fabric->id ? 'selected' : '' }}>
                                            {{ $fabric->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="color" class="form-label">Color</label>
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
                            <div class="form-group mb-3">
                                <label for="bottom_type" class="form-label">Bottom Type</label>
                                <select class="form-control" id="bottom_type" name="bottom_type">
                                    <option value="">Select Bottom Type</option>
                                    @foreach($bottomTypes as $bottomType)
                                        <option value="{{ $bottomType->id }}" {{ $cloth->bottom_type == $bottomType->id ? 'selected' : '' }}>
                                            {{ $bottomType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="fit_type" class="form-label">Fit Type</label>
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
                    </div>

                    <div class="form-group mb-3">
                        <label for="defects" class="form-label">Defects (if any)</label>
                        <textarea class="form-control" id="defects" name="defects" rows="3" 
                                  placeholder="Describe any defects or issues...">{{ $cloth->defects }}</textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-rulers me-2"></i>Measurements (Optional)</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="chest_bust" class="form-label">Chest/Bust (inches)</label>
                                <input type="number" class="form-control" id="chest_bust" name="chest_bust" 
                                       value="{{ $cloth->chest_bust }}" min="0" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="waist" class="form-label">Waist (inches)</label>
                                <input type="number" class="form-control" id="waist" name="waist" 
                                       value="{{ $cloth->waist }}" min="0" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="length" class="form-label">Length (inches)</label>
                                <input type="number" class="form-control" id="length" name="length" 
                                       value="{{ $cloth->length }}" min="0" step="0.1">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="shoulder" class="form-label">Shoulder (inches)</label>
                                <input type="number" class="form-control" id="shoulder" name="shoulder" 
                                       value="{{ $cloth->shoulder }}" min="0" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="sleeve_length" class="form-label">Sleeve Length (inches)</label>
                                <input type="number" class="form-control" id="sleeve_length" name="sleeve_length" 
                                       value="{{ $cloth->sleeve_length }}" min="0" step="0.1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-currency-dollar me-2"></i>Pricing</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="rent_price" class="form-label">Rent Price (₹) *</label>
                                <input type="number" class="form-control" id="rent_price" name="rent_price" 
                                       value="{{ $cloth->rent_price }}" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="security_deposit" class="form-label">Security Deposit (₹) *</label>
                                <input type="number" class="form-control" id="security_deposit" name="security_deposit" 
                                       value="{{ $cloth->security_deposit }}" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="submit" class="btn btn-success me-2" id="submitBtn">
                                <i class="bi bi-check-circle me-1"></i>Update & Resubmit
                            </button>
                            <a href="{{ route('rejections.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>Your item will be resubmitted for admin approval
                        </small>
                    </div>
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
        var originalText = $submitBtn.html();
        
        // Show loading state
        $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Processing...');
        
        // Form will submit normally
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
