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
  
  #purchase_value_section {
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
    <span class="step">Garment Specifications</span>
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
          <option value="{{ $brand->name }}" {{ old('brand') == $brand->name ? 'selected' : '' }}>
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
          <option value="{{ $size->id }}" {{ old('size') == $size->id ? 'selected' : '' }}>
            {{ $size->name }}
          </option>
        @endforeach
      </select>
      @error('size')<div class="text-danger small">{{ $message }}</div>@enderror
      
      <label class="d-block text-left font-weight-bold mb-1">Garment Condition <span class="text-danger">*</span></label>
      <select name="condition" required>
        <option value="">Select Garment Condition</option>
        <option value="Brand New" {{ old('condition') == 'Brand New' ? 'selected' : '' }}>Brand New</option>
        <option value="Like New" {{ old('condition') == 'Like New' ? 'selected' : '' }}>Like New</option>
        <option value="Excellent" {{ old('condition') == 'Excellent' ? 'selected' : '' }}>Excellent</option>
        <option value="Good" {{ old('condition') == 'Good' ? 'selected' : '' }}>Good</option>
        <option value="Fair" {{ old('condition') == 'Fair' ? 'selected' : '' }}>Fair</option>
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
      <select name="body_type_fit" required>
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
      <label class="d-block text-left font-weight-bold mb-1">MRP <span class="text-danger">*</span></label>
      <input type="number" name="purchase_value" placeholder="MRP (₹)" value="{{ old('purchase_value') }}" required>
      @error('purchase_value')<div class="text-danger small">{{ $message }}</div>@enderror
      
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
      <small class="text-muted" id="rent-price-suggestion" style="display: none;">Suggested maximum rent: ₹<span id="max-rent-amount">0</span> (20% of MRP)</small>
      @error('rent_price')<div class="text-danger small">{{ $message }}</div>@enderror
      
      <label class="d-block text-left font-weight-bold mb-1">Security Deposit <span class="text-danger">*</span></label>
      <input type="number" name="security_deposit" placeholder="Security Deposit (₹)" value="{{ old('security_deposit') }}" required>
      <small class="text-muted">Security deposit will be automatically set equal to the rental price.</small>
      @error('security_deposit')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="step-content">
      <div class="mb-2 text-left font-weight-bold">Upload up to 4 images (at least 1 required) <span class="text-danger">*</span>:</div>
      <input type="file" name="images[]" accept="image/*" required>
      <input type="file" name="images[]" accept="image/*">
      <input type="file" name="images[]" accept="image/*">
      <input type="file" name="images[]" accept="image/*">
      <small class="text-muted">You can upload up to 4 images. At least 1 is required.</small>
      @error('images.*')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="step-navigation">
      <button type="button" id="prevBtn" class="next-btn" style="display: none;">←</button> 
      <button type="button" id="nextBtn" class="next-btn">→</button>
    </div>
    <button type="submit" id="submitBtn" class="submit-btn" style="display: none;">Submit</button>
  </form>
</div>

<div class="decorative">
  <img src="{{ asset('images/footer.png') }}" alt="Decoration">
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/sell.js') }}"></script>
@endsection
