@extends('layouts.app-simple')

@section('title', 'Get Ready - Sell Cloth')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
<style>
  .availability-section {
    margin-bottom: 30px;
  }
  
  .availability-block {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    background: #f8f9fa;
    transition: all 0.3s ease;
    margin-bottom: 15px;
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
  
  .row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
  }
  
  .col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 0 10px;
  }
  
  @media (max-width: 768px) {
    .col-md-6 {
      flex: 0 0 100%;
      max-width: 100%;
    }
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
<div class="sell-logo">
  <img src="{{ asset('images/logo.png') }}" alt="Logo">
</div>

<div class="container">
  <div class="steps">
    <span class="step active">Outfit Info-Basic</span>
    <span class="step">Outfit Specifications</span>
    <span class="step">Availability & Pricing</span>
    <span class="step">Images</span>
  </div>

  <form id="form" method="POST" action="{{ route('sell.store') }}" enctype="multipart/form-data">
    @csrf
    
    <div class="step-content active">
      <label class="d-block text-left font-weight-bold mb-1">Title <span class="text-danger">*</span></label>
      <input type="text" name="title" placeholder="Title" value="{{ old('title') }}" required>
      @error('title')<div class="text-danger small">{{ $message }}</div>@enderror


      
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
      
      <label class="d-block text-left font-weight-bold mb-1">User Type <span class="text-danger">*</span></label>
      <select name="gender" required>
        <option value="">Select User Type</option>
        <option value="Boy" {{ old('gender') == 'Boy' ? 'selected' : '' }}>Boy</option>
        <option value="Girl" {{ old('gender') == 'Girl' ? 'selected' : '' }}>Girl</option>
        <option value="Men" {{ old('gender') == 'Men' ? 'selected' : '' }}>Men</option>
        <option value="Women" {{ old('gender') == 'Women' ? 'selected' : '' }}>Women</option>
      </select>
      @error('gender')<div class="text-danger small">{{ $message }}</div>@enderror
      
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

    <div class="step-content">
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
      
      <label class="d-block text-left font-weight-bold mb-1">Defects (Optional)</label>
      <textarea name="defects" placeholder="Any Defects">{{ old('defects') }}</textarea>
      @error('defects')<div class="text-danger small">{{ $message }}</div>@enderror

      <div class="measurements mt-3">
        <label><strong>Exact Measurements (for better fit understanding) (optional)</strong></label>
        <input type="text" name="chest_bust" placeholder="Chest/Bust (inches)" value="{{ old('chest_bust') }}">
        <input type="text" name="waist" placeholder="Waist (inches)" value="{{ old('waist') }}">
        <input type="text" name="length" placeholder="Length (inches)" value="{{ old('length') }}">
        <input type="text" name="shoulder" placeholder="Shoulder (inches)" value="{{ old('shoulder') }}">
        <input type="text" name="sleeve_length" placeholder="Sleeve Length (inches)" value="{{ old('sleeve_length') }}">
      </div>
      
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

    <div class="step-content">
      <div class="custom-control custom-checkbox mb-3">
        <input type="checkbox" class="custom-control-input" id="is_purchased" name="is_purchased" value="1" {{ old('is_purchased') ? 'checked' : '' }}>
        <label class="custom-control-label font-weight-bold" for="is_purchased">Available for Purchase</label>
      </div>

      <div id="selling_price_section" style="display: {{ old('is_purchased') ? 'block' : 'none' }};">
        <label class="d-block text-left font-weight-bold mb-1">Selling Price <span class="text-danger">*</span></label>
        <input type="number" name="selling_price" placeholder="Selling Price (₹)" value="{{ old('selling_price') }}">
        <div id="sp-error-message" class="text-danger small mt-1" style="display: none;"></div>
        @error('selling_price')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>

      <label class="d-block text-left font-weight-bold mb-1">MRP (Original Price)</label>
      <input type="number" name="mrp" placeholder="MRP (₹)" value="{{ old('mrp') }}">
      @error('mrp')<div class="text-danger small">{{ $message }}</div>@enderror

      <label class="d-block text-left font-weight-bold mb-1">Quantity <span class="text-danger">*</span></label>
      <input type="number" name="sku" placeholder="Quantity" value="{{ old('sku', 1) }}" required>
      @error('sku')<div class="text-danger small">{{ $message }}</div>@enderror
      
      <!-- Availability Management Section -->
      <div class="availability-section">
        <h4>📆 Availability Management</h4>
        <p class="text-muted">Manage when your cloth is available for rent or blocked for personal use</p>
        
        <div class="row">
          <div class="col-md-6">
            <h6>Available Dates</h6>
            <p class="text-muted small">Set specific dates when this cloth is available for rent</p>
            <div class="alert alert-info alert-sm">
              <i class="fas fa-info-circle"></i>
              <small>Tip: Leave empty if the cloth is always available. Minimum 4 days rental required.</small>
            </div>
            <div id="available-dates">
              <!-- Available dates will be added here dynamically -->
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
              <!-- Blocked dates will be added here dynamically -->
            </div>
            <button type="button" class="btn btn-warning btn-sm" onclick="addAvailabilityBlock('blocked')">
              <i class="fas fa-plus"></i> Add Blocked Date
            </button>
          </div>
        </div>
      </div>
      
      <label class="d-block text-left font-weight-bold mb-1">Rent Price <span class="text-danger">*</span></label>
      <input type="number" name="rent_price" placeholder="Rent Price (₹)" value="{{ old('rent_price') }}" required>
      <div id="rent-error-message" class="text-danger small mt-1" style="display: none;"></div>
      <small class="text-muted" id="rent-price-suggestion" style="display: none;">Suggested maximum rent: ₹<span id="max-rent-amount">0</span></small>
      @error('rent_price')<div class="text-danger small">{{ $message }}</div>@enderror
      
       <input type="hidden" name="security_deposit" id="security_deposit" value="{{ old('security_deposit') }}">
      @error('security_deposit')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="step-content">
      <div class="mb-2 text-left font-weight-bold">Upload up to 4 images (at least 1 required) <span class="text-danger">*</span>:</div>
      <input type="file" name="images[]" class="cloth-image-input" accept="image/*" required>
      <input type="file" name="images[]" class="cloth-image-input" accept="image/*">
      <input type="file" name="images[]" class="cloth-image-input" accept="image/*">
      <input type="file" name="images[]" class="cloth-image-input" accept="image/*">
      <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mb-3"></div>
      <small class="text-muted">You can upload up to 4 images. At least 1 is required.</small>
      @error('images.*')<div class="text-danger small">{{ $message }}</div>@enderror

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

/* Cloud Modal Styles */
.cloud-modal .modal-dialog {
    max-width: 500px;
    margin-top: 10vh;
}

.cloud-modal .modal-content {
    background-color: #fff;
    border: none;
    border-radius: 50px; /* Rounded main body */
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    position: relative;
    padding: 20px;
    overflow: visible; /* Allow pseudo-elements to stick out if we add bumps later */
}

/* Cloud Bumps Effect using Pseudo-elements */
.cloud-modal .modal-content::before,
.cloud-modal .modal-content::after {
    content: '';
    position: absolute;
    background: #fff;
    border-radius: 50%;
    z-index: -1;
}

/* Top Bump */
.cloud-modal .modal-content::before {
    width: 120px;
    height: 120px;
    top: -50px;
    left: 80px;
}

/* Right Bump */
.cloud-modal .modal-content::after {
    width: 100px;
    height: 100px;
    top: -30px;
    right: 60px;
}

.cloud-body {
    position: relative;
    z-index: 1; /* Keep content above bumps */
    text-align: center;
    padding: 10px;
}

.cloud-textarea {
    width: 100%;
    border: 2px dashed #bce0fd;
    border-radius: 20px;
    padding: 15px;
    resize: none;
    background: #f0f8ff; /* Light alice blue */
    color: #333;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s;
    height: 120px;
}

.cloud-textarea:focus {
    border-color: #007bff;
    background: #fff;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.1);
}

.cloud-btn {
    background: linear-gradient(135deg, #6dd5fa 0%, #2980b9 100%);
    border: none;
    border-radius: 30px;
    padding: 10px 30px;
    color: white;
    font-weight: bold;
    margin-top: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: transform 0.2s;
}

.cloud-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
}

.cloud-close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 1.5rem;
    color: #aaa;
    cursor: pointer;
    z-index: 10;
    transition: color 0.2s;
}

.cloud-close:hover {
    color: #333;
}

.cloud-title {
    font-family: 'Comic Sans MS', 'Cursive', sans-serif; /* Playful font for cloud theme */
    color: #2980b9;
    margin-bottom: 15px;
    font-size: 1.2rem;
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
      <span class="cloud-close" data-dismiss="modal">&times;</span>
      
      <div class="cloud-body">
        <h5 class="cloud-title">✨ Dream up a Description ✨</h5>
        
        <textarea class="cloud-textarea" id="rawDescription" 
          placeholder="Describe your outfit here... e.g. 'Red silk saree, worn once, perfect for weddings'"></textarea>

        <div id="aiLoading" class="mt-3" style="display: none;">
            <div class="spinner-border text-primary text-sm" role="status" style="width: 1.5rem; height: 1.5rem;"></div>
            <span class="ml-2 text-muted small">Floating ideas...</span>
        </div>

        <button type="button" class="cloud-btn" onclick="generateAiDescription()">
           Generate
        </button>
      </div>
    </div>
  </div>
</div>

<div class="decorative">
  <img src="{{ asset('images/footer.png') }}" alt="Decoration">
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
