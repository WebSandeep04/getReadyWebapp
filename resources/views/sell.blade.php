@extends('layouts.app-simple')

@section('title', 'Get Ready - Sell Cloth')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
<style>
  .availability-section {
    margin-bottom: 30px;
  }
  
  .alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    margin-bottom: 1rem;
  }
  
  .custom-control {
    margin-bottom: 15px;
  }
  
  .custom-control-input:checked ~ .custom-control-label::before {
    background-color: #ffc107;
    border-color: #ffc107;
  }
  
  #selling_price_section {
    margin-bottom: 15px;
  }
</style>
@endsection

@section('content')
<div class="sell-container py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="sell-card shadow-lg">
          <div class="sell-header text-center mb-2">
            <h2 class="font-weight-bold">List Your Outfit</h2>
            <p class="text-muted">Turn your wardrobe into an earning opportunity</p>
          </div>
          
          <div class="steps-wrapper mb-3">
            <div class="steps-progress">
              <div class="progress-bar-fill" id="progressFill"></div>
            </div>
            <div class="steps">
              <div class="step active" data-step="1">
                <span class="step-num">1</span>
                <span class="step-label">Basic Info</span>
              </div>
              <div class="step" data-step="2">
                <span class="step-num">2</span>
                <span class="step-label">Specs</span>
              </div>
              <div class="step" data-step="3">
                <span class="step-num">3</span>
                <span class="step-label">Pricing</span>
              </div>
              <div class="step" data-step="4">
                <span class="step-num">4</span>
                <span class="step-label">Images</span>
              </div>
            </div>
          </div>

  <form id="form" method="POST" action="{{ route('sell.store') }}" enctype="multipart/form-data">
    @csrf
    
    <div class="step-content active">
      <div class="form-group mb-2">
        <label class="d-block text-left font-weight-bold mb-1">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" placeholder="Title" value="{{ old('title') }}" required>
        @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
 
      <div class="row">
        <div class="col-6 mb-2">
          <label class="d-block text-left font-weight-bold mb-1">Category <span class="text-danger">*</span></label>
          <select name="category" required>
            <option value="">Select Category</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}" {{ old('category') == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
              </option>
            @endforeach
          </select>
          @error('category')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
 
        <div class="col-6 mb-2">
          <label class="d-block text-left font-weight-bold mb-1">User Type <span class="text-danger">*</span></label>
          <select name="gender" required>
            <option value="">Select User Type</option>
            <option value="Boy" {{ old('gender') == 'Boy' ? 'selected' : '' }}>Boy</option>
            <option value="Girl" {{ old('gender') == 'Girl' ? 'selected' : '' }}>Girl</option>
            <option value="Men" {{ old('gender') == 'Men' ? 'selected' : '' }}>Men</option>
            <option value="Women" {{ old('gender') == 'Women' ? 'selected' : '' }}>Women</option>
          </select>
          @error('gender')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
 
        <div class="col-12 mb-2">
          <label class="d-block text-left font-weight-bold mb-1">Brand <span class="text-danger">*</span></label>
          <select name="brand" required>
            <option value="">Select Brand</option>
            @foreach($brands as $brand)
              <option value="{{ $brand->id }}" {{ old('brand') == $brand->id ? 'selected' : '' }}>
                {{ $brand->name }}
              </option>
            @endforeach
          </select>
          @error('brand')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>

    <div class="step-content">
      <div class="row">
        <div class="col-6">
          <label class="d-block text-left font-weight-bold mb-1">Fabric Type <span class="text-danger">*</span></label>
          <select name="fabric" required>
            <option value="">Select Fabric Type</option>
            @foreach($fabric_types as $fabric)
              <option value="{{ $fabric->id }}" {{ old('fabric') == $fabric->id ? 'selected' : '' }}>
                {{ $fabric->name }}
              </option>
            @endforeach
          </select>
          @error('fabric')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-6">
          <label class="d-block text-left font-weight-bold mb-1">Color <span class="text-danger">*</span></label>
          <select name="color" required>
            <option value="">Select Color</option>
            @foreach($colors as $color)
              <option value="{{ $color->id }}" {{ old('color') == $color->id ? 'selected' : '' }}>
                {{ $color->name }}
              </option>
            @endforeach
          </select>
          @error('color')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-6">
          <label class="d-block text-left font-weight-bold mb-1">Size <span class="text-danger">*</span></label>
          <select name="size" required>
            <option value="">Select Size</option>
            @foreach($sizes as $size)
              <option value="{{ $size->id }}" {{ old('size') == $size->id ? 'selected' : '' }}
                data-chest="{{ $size->chest_bust }}"
                data-waist="{{ $size->waist }}"
                data-length="{{ $size->length }}"
                data-shoulder="{{ $size->shoulder }}"
                data-sleeve="{{ $size->sleeve_length }}">
                {{ $size->name }}
              </option>
            @endforeach
          </select>
          @error('size')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

      </div>
      
      <label class="d-block text-left font-weight-bold mb-1">Defects (Optional)</label>
      <textarea name="defects" placeholder="Any Defects">{{ old('defects') }}</textarea>
      @error('defects')<div class="text-danger small">{{ $message }}</div>@enderror

      <div class="measurements mt-3">
        <label><strong>Exact Measurements (for better fit understanding) (optional)</strong></label>
        
        <div class="unit-toggle mb-3 d-flex align-items-center" style="gap: 30px;">
          <div class="custom-control custom-radio">
            <input type="radio" id="unit_inch" name="measurement_unit" class="custom-control-input unit-selector" value="inch" {{ old('measurement_unit', 'inch') == 'inch' ? 'checked' : '' }}>
            <label class="custom-control-label" for="unit_inch" style="text-transform: none; font-size: 0.85rem;">Inch</label>
          </div>
          <div class="custom-control custom-radio">
            <input type="radio" id="unit_cm" name="measurement_unit" class="custom-control-input unit-selector" value="cm" {{ old('measurement_unit') == 'cm' ? 'checked' : '' }}>
            <label class="custom-control-label" for="unit_cm" style="text-transform: none; font-size: 0.85rem;">CM</label>
          </div>
        </div>

        <div class="row">
          <div class="col-6 mb-2">
            <label class="measurement-label font-weight-bold mb-1 d-block text-left">Chest/Bust</label>
            <input type="number" name="chest_bust" class="measurement-input" placeholder="Chest/Bust" value="{{ old('chest_bust') }}" step="0.1">
          </div>
          <div class="col-6 mb-2">
            <label class="measurement-label font-weight-bold mb-1 d-block text-left">Waist</label>
            <input type="number" name="waist" class="measurement-input" placeholder="Waist" value="{{ old('waist') }}" step="0.1">
          </div>
          <div class="col-6 mb-2">
            <label class="measurement-label font-weight-bold mb-1 d-block text-left">Length</label>
            <input type="number" name="length" class="measurement-input" placeholder="Length" value="{{ old('length') }}" step="0.1">
          </div>
          <div class="col-6 mb-2">
            <label class="measurement-label font-weight-bold mb-1 d-block text-left">Shoulder</label>
            <input type="number" name="shoulder" class="measurement-input" placeholder="Shoulder" value="{{ old('shoulder') }}" step="0.1">
          </div>
          <div class="col-6 mb-2">
            <label class="measurement-label font-weight-bold mb-1 d-block text-left">Sleeve Length</label>
            <input type="number" name="sleeve_length" class="measurement-input" placeholder="Sleeve Length" value="{{ old('sleeve_length') }}" step="0.1">
          </div>
          <div class="col-6 mb-2">
            <label class="d-block text-left font-weight-bold mb-1">Body Fit Type</label>
            <select name="body_type_fit">
              <option value="">Select Body Fit Type</option>
              @foreach($body_type_fits as $body_type_fit)
                <option value="{{ $body_type_fit->id }}" {{ old('body_type_fit') == $body_type_fit->id ? 'selected' : '' }}>
                  {{ $body_type_fit->name }}
                </option>
              @endforeach
            </select>
            @error('body_type_fit')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>
    </div>

    <div class="step-content">
      <div class="custom-control custom-checkbox mb-4">
        <input type="checkbox" class="custom-control-input" id="is_purchased" name="is_purchased" value="1" {{ old('is_purchased') ? 'checked' : '' }}>
        <label class="custom-control-label font-weight-bold" for="is_purchased" style="text-transform: none;">Available for Purchase</label>
      </div>

      <div class="row">
        <div class="col-md-6" id="selling_price_section" style="display: {{ old('is_purchased') ? 'block' : 'none' }};">
          <label class="d-block text-left font-weight-bold mb-1">Selling Price <span class="text-danger">*</span></label>
          <input type="number" name="selling_price" placeholder="Selling Price (₹)" value="{{ old('selling_price') }}">
          <div id="sp-error-message" class="text-danger small mt-1" style="display: none;"></div>
          @error('selling_price')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-6 mb-2">
          <label class="d-block text-left font-weight-bold mb-1">MRP (₹)</label>
          <input type="number" name="mrp" placeholder="MRP" value="{{ old('mrp') }}">
          @error('mrp')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-6 mb-2">
          <label class="d-block text-left font-weight-bold mb-1">Quantity <span class="text-danger">*</span></label>
          <input type="number" name="sku" placeholder="Qty" value="{{ old('sku', 1) }}" required>
          @error('sku')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
      
      <!-- Availability Management Section -->
      <div class="availability-section border-top pt-3">
        <div class="d-flex align-items-center mb-2">
          <h5 class="mb-0 font-weight-bold" style="font-size: 1rem;">📆 Availability Management</h5>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="font-weight-bold small text-uppercase" style="color: #2e7d32;">Available for Rent</span>
              <button type="button" class="btn btn-outline-success btn-xs py-1 px-2" style="font-size: 0.7rem;" onclick="addAvailabilityBlock('available')">
                <i class="fas fa-plus"></i> Add
              </button>
            </div>
            <div class="alert alert-success py-1 px-2 mb-2" style="font-size: 0.65rem; background-color: #f1f8e9; border: none; color: #2e7d32;">
              Leave empty if always available. Min 4 days.
            </div>
            <div id="available-dates"></div>
          </div>
          
          <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="font-weight-bold small text-uppercase" style="color: #e65100;">Personal Block Dates</span>
              <button type="button" class="btn btn-outline-warning btn-xs py-1 px-2" style="font-size: 0.7rem; color: #e65100; border-color: #e65100;" onclick="addAvailabilityBlock('blocked')">
                <i class="fas fa-plus"></i> Add
              </button>
            </div>
            <div class="alert alert-warning py-1 px-2 mb-2" style="font-size: 0.65rem; background-color: #fff3e0; border: none; color: #e65100;">
              Block dates to avoid rental conflicts.
            </div>
            <div id="blocked-dates"></div>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-6 mb-3">
          <label class="d-block text-left font-weight-bold mb-1">Outfit Condition <span class="text-danger">*</span></label>
          <select name="condition" required>
            <option value="">Select Outfit Condition</option>
            @foreach($garment_conditions as $condition)
              <option value="{{ $condition->id }}" {{ old('condition') == $condition->id ? 'selected' : '' }}>
                {{ $condition->name }}
              </option>
            @endforeach
          </select>
          @error('condition')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6 mb-3">
          <label class="d-block text-left font-weight-bold mb-1">Rent Price <span class="text-danger">*</span></label>
          <input type="number" name="rent_price" placeholder="Rent Price (₹)" value="{{ old('rent_price') }}" required>
          <div id="rent-error-message" class="text-danger small mt-1" style="display: none;"></div>
          <small class="text-muted" id="rent-price-suggestion" style="display: none;">Suggested maximum rent: ₹<span id="max-rent-amount">0</span></small>
          @error('rent_price')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
      
       <input type="hidden" name="security_deposit" id="security_deposit" value="{{ old('security_deposit') }}">
      @error('security_deposit')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="step-content">
      <div class="mb-4 text-center">
        <h4 class="font-weight-bold">Upload Outfit Images</h4>
        <p class="text-muted small">High-quality photos increase your chances of a quick rental</p>
      </div>
      
      <div class="image-upload-grid mb-4">
        <div class="image-upload-item">
          <label class="upload-box" for="img-1">
            <i class="bi bi-camera-fill mb-2"></i>
            <span>Main Photo</span>
            <input type="file" name="images[]" id="img-1" class="cloth-image-input" accept="image/*" required onchange="handleImagePreview(this, 1)">
            <div class="preview-overlay" id="preview-1"></div>
          </label>
        </div>
        <div class="image-upload-item">
          <label class="upload-box" for="img-2">
            <i class="bi bi-camera-fill mb-2"></i>
            <span>Side View</span>
            <input type="file" name="images[]" id="img-2" class="cloth-image-input" accept="image/*" required onchange="handleImagePreview(this, 2)">
            <div class="preview-overlay" id="preview-2"></div>
          </label>
        </div>
        <div class="image-upload-item">
          <label class="upload-box" for="img-3">
            <i class="bi bi-camera-fill mb-2"></i>
            <span>Back View</span>
            <input type="file" name="images[]" id="img-3" class="cloth-image-input" accept="image/*" required onchange="handleImagePreview(this, 3)">
            <div class="preview-overlay" id="preview-3"></div>
          </label>
        </div>
        <div class="image-upload-item">
          <label class="upload-box" for="img-4">
            <i class="bi bi-camera-fill mb-2"></i>
            <span>Detail Shot</span>
            <input type="file" name="images[]" id="img-4" class="cloth-image-input" accept="image/*" onchange="handleImagePreview(this, 4)">
            <div class="preview-overlay" id="preview-4"></div>
          </label>
        </div>
      </div>
      
      <div class="alert alert-light border small text-muted text-center">
        <i class="bi bi-info-circle mr-1"></i> You must upload at least 3 images.
      </div>

      <div class="d-flex justify-content-between align-items-center mb-1 mt-3">
        <label class="font-weight-bold mb-0">Description <span class="text-danger">*</span></label>
        <img src="{{ asset('images/icon/gemini_logo.jpeg') }}" alt="Generate with AI" data-toggle="modal" data-target="#aiDescriptionModal" title="Generate with AI" style="cursor: pointer; height: 30px; width: auto;">
      </div>
      <textarea name="description" id="description" placeholder="Description" required>{{ old('description') }}</textarea>
      @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="step-navigation">
      <button type="button" id="prevBtn" class="next-btn" style="display: none;">←</button> 
      <button type="button" id="nextBtn" class="next-btn">→</button>
    </div>
    <button type="submit" id="submitBtn" class="submit-btn" style="display: none;">Submit</button>
  </form>
        </div> <!-- end sell-card -->
      </div>
    </div>
  </div>
</div>




<style>
/* Summary Modal Styles - Slim & Grid */
#summaryModal .modal-content {
    border-radius: 0;
    border: none;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
}

#summaryModal .modal-header {
    background: #fff;
    border-bottom: 2px solid #f78c1c;
    padding: 12px 20px;
}

#summaryModal .modal-title {
    font-weight: 700;
    color: #333;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#summaryModal .modal-body {
    padding: 20px;
    max-height: 75vh;
    overflow-y: auto;
    background: #fff;
    text-align: left;
}

#summaryModal .modal-footer {
    border-top: 1px solid #eee;
    padding: 12px 20px;
    background: #f9f9f9;
}

#summaryModal .btn {
    border-radius: 0;
    font-size: 0.8rem;
    padding: 8px 16px;
    font-weight: 600;
}

#summaryModal .btn-primary {
    background-color: #f78c1c;
    border-color: #f78c1c;
}

#summaryModal .btn-primary:hover {
    background-color: #e87b11;
    border-color: #e87b11;
}

#summaryModal .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.summary-grid-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

@media (max-width: 576px) {
    .summary-grid-container {
        grid-template-columns: 1fr;
    }
}

.summary-item {
    margin-bottom: 0;
    display: flex;
    flex-direction: column;
    padding: 0;
    border-bottom: none;
    text-align: left;
    align-items: flex-start;
}

.summary-label {
    font-weight: 800;
    color: #444;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 2px;
    text-align: left;
}

.summary-value {
    font-size: 0.85rem;
    color: #333;
    font-weight: 600;
    word-break: break-word;
    text-align: left;
}

.summary-image-preview {
    width: 65px;
    height: 65px;
    object-fit: cover;
    border-radius: 0;
    margin-right: 8px;
    margin-bottom: 8px;
    border: 1px solid #eee;
}

.summary-section-title {
    font-weight: 700;
    color: #f78c1c;
    margin-bottom: 12px;
    margin-top: 25px;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: 1px solid #fec;
    padding-bottom: 4px;
    display: flex;
    align-items: center;
}

.summary-section-title:first-child {
    margin-top: 0;
}

.summary-desc-box {
    background: #fcfcfc;
    padding: 10px;
    border: 1px solid #f0f0f0;
    color: #666;
    font-size: 0.8rem;
    line-height: 1.4;
    width: 100%;
}

.cloth-image-input {
    margin-bottom: 10px !important;
}

/* Premium AI Modal Styles */
.cloud-modal .modal-dialog {
    max-width: 550px;
}

.cloud-modal .modal-content {
    background-color: #fff;
    border: none;
    border-radius: 24px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    position: relative;
    padding: 0;
    overflow: hidden;
}

.cloud-header {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    padding: 2rem 1.5rem;
    text-align: center;
    color: white;
}

.cloud-body {
    padding: 2rem;
    text-align: center;
}

.cloud-textarea {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.25rem;
    resize: none;
    background: #f8fafc;
    color: #1e293b;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s;
    height: 140px;
    margin-bottom: 1.5rem;
}

.cloud-textarea:focus {
    border-color: #6366f1;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.cloud-btn {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    border: none;
    border-radius: 12px;
    padding: 0.8rem 2.5rem;
    color: white;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.cloud-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
}

.cloud-close {
    position: absolute;
    top: 1rem;
    right: 1.25rem;
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    z-index: 10;
    transition: all 0.2s;
}

.cloud-close:hover {
    color: white;
    transform: scale(1.1);
}

.cloud-title {
    font-weight: 800;
    color: white;
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
    letter-spacing: -0.025em;
}

.cloud-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    font-weight: 500;
}
</style>

<!-- Summary Modal -->
<div class="modal fade" id="summaryModal" tabindex="-1" role="dialog" aria-labelledby="summaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="summaryModalLabel"><i class="fas fa-clipboard-list mr-2" style="color:#f78c1c;"></i> Review Your Listing</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="summaryContent">
        <!-- Summary will be injected here via JavaScript -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit Details</button>
        <button type="button" id="finalSubmitBtn" class="btn btn-primary px-4 py-2">
            Confirm & Post Listing <i class="fas fa-check-circle ml-1"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade cloud-modal" id="aiDescriptionModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="cloud-header">
        <span class="cloud-close" data-dismiss="modal">&times;</span>
        <h5 class="cloud-title">✨ AI Magic ✨</h5>
        <p class="cloud-subtitle">Let our AI dream up the perfect description for your outfit</p>
      </div>
      
      <div class="cloud-body">
        <textarea class="cloud-textarea" id="rawDescription" 
          placeholder="Describe your outfit in a few words... e.g. 'Red silk saree, worn once, perfect for weddings'"></textarea>

        <div id="aiLoading" class="mb-4" style="display: none;">
            <div class="spinner-border text-primary" role="status" style="width: 1.5rem; height: 1.5rem; color: #6366f1 !important;"></div>
            <p class="mt-2 text-muted small fw-bold">Generating premium description...</p>
        </div>

        <button type="button" class="cloud-btn" onclick="generateAiDescription()">
           <i class="bi bi-stars"></i> Generate Now
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/sell.js') }}"></script>
<script>
function generateAiDescription() {
    const rawDescription = $('#rawDescription').val();
    if (!rawDescription) {
        alert('Please tell me a little bit about the outfit!');
        return;
    }

    $('#aiLoading').show();
    $('.cloud-btn').prop('disabled', true); // Disable button
    
    const title = $('input[name="title"]').val();
    
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
                // Formatting the response nicely in the text area
                $('textarea[name="description"]').val(response.description);
                $('#aiDescriptionModal').modal('hide');
            }
        },
        error: function(xhr) {
            alert('Oops! ' + (xhr.responseJSON?.error || 'Something went wrong.'));
        },
        complete: function() {
            $('#aiLoading').hide();
            $('.cloud-btn').prop('disabled', false);
        }
    });
}
</script>
@endsection
