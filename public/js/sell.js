const steps = document.querySelectorAll(".step-content");
const indicators = document.querySelectorAll(".steps .step");
const nextBtn = document.getElementById("nextBtn");
const prevBtn = document.getElementById("prevBtn");
const submitBtn = document.getElementById("submitBtn");
const progressFill = document.getElementById("progressFill");

let currentStep = 0;

// Auto-set security deposit equal to rental price and show rent price suggestion
const purchaseValueInput = document.querySelector('input[name="selling_price"]');
const rentPriceInput = document.querySelector('input[name="rent_price"]');
const securityDepositInput = document.querySelector('input[name="security_deposit"]');
const mrpInput = document.querySelector('input[name="mrp"]');
const rentPriceSuggestion = document.getElementById('rent-price-suggestion');
const maxRentAmount = document.getElementById('max-rent-amount');

// Function to check if rent price exceeds suggested maximum and show/hide suggestion
function checkAndShowRentSuggestion() {
  const mrp = parseFloat(mrpInput.value) || 0;
  const sellingPrice = parseFloat(purchaseValueInput.value) || 0;
  const rentPrice = parseFloat(rentPriceInput.value) || 0;

  // const rentErrorMessage = document.getElementById('rent-error-message');
  const spErrorMessage = document.getElementById('sp-error-message');

  if (mrp > 0) {
    const maxRent = mrp * 0.2; // 20% of MRP
    if (maxRentAmount) maxRentAmount.textContent = Math.round(maxRent);

    // Rent Price Validation
    if (rentPrice > maxRent) {
      if (rentPriceSuggestion) rentPriceSuggestion.style.display = 'block';
      // if (rentErrorMessage) {
      //   rentErrorMessage.textContent = `Rent price should not exceed 20% of MRP (₹${Math.round(maxRent)})`;
      //   rentErrorMessage.style.display = 'block';
      // }
      rentPriceInput.classList.add('is-invalid');
    } else {
      if (rentPriceSuggestion) rentPriceSuggestion.style.display = 'none';
      // if (rentErrorMessage) rentErrorMessage.style.display = 'none';
      rentPriceInput.classList.remove('is-invalid');
    }

    // Selling Price Validation
    if (sellingPrice > mrp) {
      if (spErrorMessage) {
        spErrorMessage.textContent = 'Selling price should not exceed MRP';
        spErrorMessage.style.display = 'block';
      }
      purchaseValueInput.classList.add('is-invalid');
    } else {
      if (spErrorMessage) spErrorMessage.style.display = 'none';
      purchaseValueInput.classList.remove('is-invalid');
    }
  } else {
    if (rentPriceSuggestion) rentPriceSuggestion.style.display = 'none';
    // if (rentErrorMessage) rentErrorMessage.style.display = 'none';
    if (spErrorMessage) spErrorMessage.style.display = 'none';
  }
}

// Toggle Selling Price section based on "Available for Purchase" checkbox
const isPurchasedCheckbox = document.getElementById('is_purchased');
const sellingPriceSection = document.getElementById('selling_price_section');

if (isPurchasedCheckbox && sellingPriceSection) {
  isPurchasedCheckbox.addEventListener('change', function () {
    if (this.checked) {
      sellingPriceSection.style.display = 'block';
      if (purchaseValueInput) purchaseValueInput.required = true;
    } else {
      sellingPriceSection.style.display = 'none';
      if (purchaseValueInput) {
        purchaseValueInput.required = false;
        purchaseValueInput.value = ''; // Optional: clear value when hidden
        // Also hide rent suggestion if it was shown
        if (rentPriceSuggestion) rentPriceSuggestion.style.display = 'none';
      }
    }
  });
}

// Calculate and display maximum rent suggestion (20% of Selling Price)
// Only show when entered rent price exceeds the suggested maximum
if (purchaseValueInput && rentPriceInput && rentPriceSuggestion && maxRentAmount) {
  // Check when Selling Price changes
  purchaseValueInput.addEventListener('input', checkAndShowRentSuggestion);

  // Check when MRP changes
  mrpInput.addEventListener('input', checkAndShowRentSuggestion);

  // Check when rent price changes
  rentPriceInput.addEventListener('input', function () {
    checkAndShowRentSuggestion();
    // Also update security deposit (existing functionality)
    const rentPrice = parseFloat(this.value) || 0;
    if (securityDepositInput) {
      securityDepositInput.value = rentPrice;
    }
  });

  // Initialize on page load
  checkAndShowRentSuggestion();
  if (rentPriceInput && securityDepositInput) {
    securityDepositInput.value = rentPriceInput.value || 0;
  }
}


// Show/hide buttons based on current step and update stepper UI
function updateButtons() {
  // Update progress bar
  const progress = (currentStep / (steps.length - 1)) * 100;
  if (progressFill) progressFill.style.width = `${progress}%`;

  // Update step indicators
  indicators.forEach((indicator, index) => {
    indicator.classList.remove("active", "completed");
    if (index === currentStep) {
      indicator.classList.add("active");
    } else if (index < currentStep) {
      indicator.classList.add("completed");
    }
  });

  if (currentStep === 0) {
    prevBtn.style.display = "none";
    nextBtn.style.display = "flex";
    submitBtn.style.display = "none";
  } else if (currentStep === steps.length - 1) {
    prevBtn.style.display = "flex";
    nextBtn.style.display = "none";
    submitBtn.style.display = "block";
  } else {
    prevBtn.style.display = "flex";
    nextBtn.style.display = "flex";
    submitBtn.style.display = "none";
  }
}

// Next button functionality
nextBtn.addEventListener("click", () => {
  if (currentStep < steps.length - 1) {
    steps[currentStep].classList.remove("active");
    indicators[currentStep].classList.remove("active");
    currentStep++;

    steps[currentStep].classList.add("active");
    indicators[currentStep].classList.add("active");
    updateButtons();
  }
});

// Previous button functionality
prevBtn.addEventListener("click", () => {
  if (currentStep > 0) {
    steps[currentStep].classList.remove("active");
    indicators[currentStep].classList.remove("active");
    currentStep--;

    steps[currentStep].classList.add("active");
    indicators[currentStep].classList.add("active");
    updateButtons();
  }
});

// Make indicators clickable to jump between steps freely
indicators.forEach((indicator, index) => {
  indicator.style.cursor = "pointer";
  indicator.addEventListener("click", () => {
    steps[currentStep].classList.remove("active");
    indicators[currentStep].classList.remove("active");
    currentStep = index;

    steps[currentStep].classList.add("active");
    indicators[currentStep].classList.add("active");
    updateButtons();
  });
});

// Form submission validation
const form = document.getElementById("form");
const imageInputs = document.querySelectorAll('.cloth-image-input');
const imagePreviewContainer = document.getElementById('imagePreviewContainer');

// Image preview logic for multiple separate inputs
if (imageInputs.length > 0) {
  imageInputs.forEach((input) => {
    input.addEventListener('change', function () {
      refreshImagePreviews();
    });
  });
}

function refreshImagePreviews() {
  imagePreviewContainer.innerHTML = '';
  imageInputs.forEach(input => {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'summary-image-preview';
        img.style.width = '80px';
        img.style.height = '80px';
        img.style.margin = '5px';
        imagePreviewContainer.appendChild(img);
      }
      reader.readAsDataURL(input.files[0]);
    }
  });
}

if (form) {
  form.setAttribute('novalidate', true);
  form.addEventListener("submit", function (e) {
    e.preventDefault(); // Always prevent default first

    // Prevent double submission
    if (form.getAttribute('data-submitting') === 'true') {
      return;
    }

    let isValid = true;
    let firstInvalidStep = -1;
    const allInputs = form.querySelectorAll('input:not([type="file"]), select, textarea');

    // Clear previous highlights
    allInputs.forEach(input => input.classList.remove('is-invalid'));

    let missingFields = [];
    allInputs.forEach(input => {
      if (input.hasAttribute('required') && !input.value.trim()) {
        isValid = false;
        input.classList.add('is-invalid');
        
        // Try to get a human-readable name for the field
        let fieldName = '';
        const label = input.closest('.form-group')?.querySelector('label');
        const rowLabel = input.closest('.row')?.parentElement?.querySelector('label');
        const colLabel = input.parentElement?.querySelector('label');
        
        if (label) fieldName = label.innerText.replace('*', '').trim();
        else if (rowLabel) fieldName = rowLabel.innerText.replace('*', '').trim();
        else if (colLabel) fieldName = colLabel.innerText.replace('*', '').trim();
        else fieldName = input.placeholder || input.name;
        
        if (fieldName && !missingFields.includes(fieldName)) missingFields.push(fieldName);
      } else if (input.checkValidity && !input.checkValidity()) {
        isValid = false;
        input.classList.add('is-invalid');
      }

      if (input.classList.contains('is-invalid') && firstInvalidStep === -1) {
        const stepParent = input.closest('.step-content');
        steps.forEach((stepEl, index) => {
          if (stepEl === stepParent) firstInvalidStep = index;
        });
      }
    });

    // Special check for images - Check if all required image inputs are filled
    let missingRequiredImage = false;
    imageInputs.forEach(imgInput => {
      if (imgInput && imgInput.required && imgInput.files.length === 0) {
        isValid = false;
        imgInput.classList.add('is-invalid');
        missingRequiredImage = true;
      }
    });

    if (missingRequiredImage) {
      missingFields.push("At least 3 images");
      if (firstInvalidStep === -1) firstInvalidStep = 3;
    }

    if (!isValid) {
      // Show an alert to the user so they know why it's not submitting
      alert('Please fill in the following required fields:\n- ' + missingFields.join('\n- '));

      if (firstInvalidStep !== -1 && firstInvalidStep !== currentStep) {
        steps[currentStep].classList.remove("active");
        indicators[currentStep].classList.remove("active");
        currentStep = firstInvalidStep;
        steps[currentStep].classList.add("active");
        indicators[currentStep].classList.add("active");
        updateButtons();
      }

      const firstInvalid = document.querySelector('.is-invalid');
      if (firstInvalid) {
        setTimeout(() => firstInvalid.focus(), 100);
      }
    } else {
      // Form is valid, show summary modal
      showSummary();
    }
  });
}

function showSummary() {
  const summaryContent = document.getElementById('summaryContent');
  let html = '';

  const getSelectText = (name) => {
    const el = form.querySelector(`[name="${name}"]`);
    if (el && el.selectedIndex !== -1 && el.options[el.selectedIndex]) {
      return el.options[el.selectedIndex].text;
    }
    return '';
  };

  const getValue = (name) => {
    const el = form.querySelector(`[name="${name}"]`);
    return el ? el.value : '';
  };

  // Section 1: Basic Info
  html += '<div class="summary-section-title"><i class="fas fa-tag mr-2"></i> Outfit Info</div>';
  html += '<div class="summary-grid-container">';
  html += `<div class="summary-item"><span class="summary-label">Title</span><span class="summary-value">${getValue('title')}</span></div>`;
  html += `<div class="summary-item"><span class="summary-label">Category</span><span class="summary-value">${getSelectText('category')}</span></div>`;
  html += `<div class="summary-item"><span class="summary-label">User Type</span><span class="summary-value">${getSelectText('gender')}</span></div>`;
  html += `<div class="summary-item"><span class="summary-label">Brand</span><span class="summary-value">${getSelectText('brand')}</span></div>`;
  html += '</div>';

  // Section 2: Specifications
  html += '<div class="summary-section-title"><i class="fas fa-tshirt mr-2"></i> Specifications</div>';
  html += '<div class="summary-grid-container">';
  html += `<div class="summary-item"><span class="summary-label">Fabric</span><span class="summary-value">${getSelectText('fabric')}</span></div>`;
  html += `<div class="summary-item"><span class="summary-label">Color</span><span class="summary-value">${getSelectText('color')}</span></div>`;
  html += `<div class="summary-item"><span class="summary-label">Size</span><span class="summary-value">${getSelectText('size')}</span></div>`;
  html += `<div class="summary-item"><span class="summary-label">Condition</span><span class="summary-value">${getSelectText('condition')}</span></div>`;

  const defects = getValue('defects');
  if (defects) {
    html += `<div class="summary-item" style="grid-column: span 2;"><span class="summary-label">Defects</span><span class="summary-value">${defects}</span></div>`;
  }
  html += '</div>';

  // Measurements
  const chest = getValue('chest_bust');
  const waist = getValue('waist');
  const length = getValue('length');
  const shoulder = getValue('shoulder');
  const sleeve = getValue('sleeve_length');
  const bodyFit = getSelectText('body_type_fit');

  if (chest || waist || length || shoulder || sleeve || bodyFit) {
    const unit = document.querySelector('.unit-selector:checked')?.value || 'inch';
    html += '<div class="summary-section-title"><i class="fas fa-ruler-combined mr-2"></i> Fit & Measurements</div>';
    html += '<div class="summary-grid-container">';
    if (bodyFit) html += `<div class="summary-item"><span class="summary-label">Body Fit</span><span class="summary-value">${bodyFit}</span></div>`;
    if (chest) html += `<div class="summary-item"><span class="summary-label">Chest/Bust</span><span class="summary-value">${chest} ${unit}</span></div>`;
    if (waist) html += `<div class="summary-item"><span class="summary-label">Waist</span><span class="summary-value">${waist} ${unit}</span></div>`;
    if (length) html += `<div class="summary-item"><span class="summary-label">Length</span><span class="summary-value">${length} ${unit}</span></div>`;
    if (shoulder) html += `<div class="summary-item"><span class="summary-label">Shoulder</span><span class="summary-value">${shoulder} ${unit}</span></div>`;
    if (sleeve) html += `<div class="summary-item"><span class="summary-label">Sleeve Length</span><span class="summary-value">${sleeve} ${unit}</span></div>`;
    html += '</div>';
  }

  // Section 3: Pricing & Availability
  html += '<div class="summary-section-title"><i class="fas fa-hand-holding-usd mr-2"></i> Pricing</div>';
  html += '<div class="summary-grid-container">';
  const isPurchased = document.getElementById('is_purchased').checked;
  html += `<div class="summary-item"><span class="summary-label">For Purchase</span><span class="summary-value">${isPurchased ? 'Yes' : 'No'}</span></div>`;
  if (isPurchased) {
    html += `<div class="summary-item"><span class="summary-label">Selling Price</span><span class="summary-value">₹${getValue('selling_price')}</span></div>`;
  }
  html += `<div class="summary-item"><span class="summary-label">MRP</span><span class="summary-value">₹${getValue('mrp')}</span></div>`;
  html += `<div class="summary-item"><span class="summary-label">Rent Price</span><span class="summary-value">₹${getValue('rent_price')}</span></div>`;
  html += `<div class="summary-item"><span class="summary-label">Quantity</span><span class="summary-value">${getValue('sku')}</span></div>`;
  html += '</div>';

  // Section: Availability Dates
  const availableBlocks = document.querySelectorAll('#available-dates .availability-block');
  const blockedBlocks = document.querySelectorAll('#blocked-dates .availability-block');

  if (availableBlocks.length > 0 || blockedBlocks.length > 0) {
    html += '<div class="summary-section-title"><i class="fas fa-calendar-alt mr-2"></i> Availability Schedule</div>';
    html += '<div class="summary-grid-container">';
    if (availableBlocks.length > 0) {
      html += '<div class="summary-item" style="grid-column: span 2;"><span class="summary-label">Available Dates</span>';
      availableBlocks.forEach(block => {
        const start = block.querySelector('[name*="[start_date]"]').value;
        const end = block.querySelector('[name*="[end_date]"]').value;
        html += `<span class="summary-value small">• ${start} to ${end}</span>`;
      });
      html += '</div>';
    }

    if (blockedBlocks.length > 0) {
      html += '<div class="summary-item" style="grid-column: span 2;"><span class="summary-label">Blocked Dates</span>';
      blockedBlocks.forEach(block => {
        const start = block.querySelector('[name*="[start_date]"]').value;
        const end = block.querySelector('[name*="[end_date]"]').value;
        const reason = block.querySelector('[name*="[reason]"]').value;
        html += `<span class="summary-value small">• ${start} to ${end} ${reason ? `(${reason})` : ''}</span>`;
      });
      html += '</div>';
    }
    html += '</div>';
  }

  // Section 4: Images & Description
  html += '<div class="summary-section-title"><i class="fas fa-align-left mr-2"></i> Description & Images</div>';
  html += '<div class="summary-item mb-3">';
  html += '<span class="summary-label">Description</span>';
  html += `<div class="summary-desc-box">${getValue('description')}</div>`;
  html += '</div>';

  // Images summary
  html += '<div class="summary-item">';
  html += '<span class="summary-label">Selected Images</span>';
  html += '<div class="d-flex flex-wrap mt-2">';
  const previewImgs = imagePreviewContainer.querySelectorAll('img');
  if (previewImgs.length > 0) {
    previewImgs.forEach(img => {
      html += `<img src="${img.src}" class="summary-image-preview">`;
    });
  } else {
    html += '<span class="text-muted small">No images selected</span>';
  }
  html += '</div></div>';

  summaryContent.innerHTML = html;
  $('#summaryModal').modal('show');
}

// Final submission from modal
const finalSubmitBtn = document.getElementById('finalSubmitBtn');
if (finalSubmitBtn) {
  finalSubmitBtn.addEventListener('click', function () {
    form.setAttribute('data-submitting', 'true');
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

    // Disable cancel button in modal
    const modalCancelBtn = document.querySelector('#summaryModal .btn-secondary');
    if (modalCancelBtn) modalCancelBtn.disabled = true;

    form.submit();
  });
}

// Initialize button visibility
updateButtons();

// Availability management functionality
let availableCounter = 0;
let blockedCounter = 0;

// Remove validation formatting on input or change
document.addEventListener('input', function (e) {
  if (e.target.classList && e.target.classList.contains('is-invalid')) {
    e.target.classList.remove('is-invalid');
  }
});
document.addEventListener('change', function (e) {
  if (e.target.classList && e.target.classList.contains('is-invalid')) {
    e.target.classList.remove('is-invalid');
  }
});

function addAvailabilityBlock(type) {
  const container = document.getElementById(type === 'available' ? 'available-dates' : 'blocked-dates');
  const counter = type === 'available' ? ++availableCounter : ++blockedCounter;
  const index = type === 'available' ? counter - 1 : counter + 99; // Use different index ranges

  const blockHtml = `
    <div class="availability-block p-2 mb-2 border rounded bg-light position-relative" data-type="${type}" data-index="${index}" style="border-style: dashed !important;">
      <button type="button" class="btn btn-link text-danger p-0 position-absolute" style="top: 2px; right: 5px; z-index: 10; text-decoration: none;" onclick="removeAvailabilityBlock(this)">
        <i class="fas fa-times-circle"></i>
      </button>
      <div class="row g-2">
        <div class="col-6">
          <label class="small mb-0" style="font-size: 0.65rem; font-weight: 800; color: #666;">START DATE</label>
          <input type="date" class="form-control form-control-sm" name="availability_blocks[${index}][start_date]" required style="font-size: 0.75rem; padding: 4px 8px; margin-bottom: 0;">
        </div>
        <div class="col-6">
          <label class="small mb-0" style="font-size: 0.65rem; font-weight: 800; color: #666;">END DATE</label>
          <input type="date" class="form-control form-control-sm" name="availability_blocks[${index}][end_date]" required style="font-size: 0.75rem; padding: 4px 8px; margin-bottom: 0;">
        </div>
      </div>
      ${type === 'blocked' ? `
        <div class="row g-2 mt-1">
          <div class="col-12">
            <input type="text" class="form-control form-control-sm" name="availability_blocks[${index}][reason]" placeholder="Reason (optional)" style="font-size: 0.75rem; padding: 4px 8px; margin-bottom: 0;">
          </div>
        </div>
      ` : ''}
      <input type="hidden" name="availability_blocks[${index}][type]" value="${type}">
      ${type === 'available' ? `
        <div class="mt-1">
          <div class="text-danger" id="availability-error-${index}" style="display: none; font-size: 0.65rem; font-weight: 600;"></div>
        </div>
      ` : ''}
    </div>
  `;

  container.insertAdjacentHTML('beforeend', blockHtml);

  // Add event listeners for available dates
  if (type === 'available') {
    const blockElement = container.querySelector(`[data-index="${index}"]`);
    const startDateInput = blockElement.querySelector('input[name*="[start_date]"]');
    const endDateInput = blockElement.querySelector('input[name*="[end_date]"]');

    // Function to handle date changes
    const handleDateChange = () => {
      const startDate = startDateInput.value;
      const endDate = endDateInput.value;

      if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const daysDiff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

        const errorDiv = document.getElementById(`availability-error-${index}`);

        // Check minimum 4 days
        if (daysDiff < 4) {
          errorDiv.textContent = `Minimum 4 days rental required. Currently: ${daysDiff} day(s).`;
          errorDiv.style.display = 'block';
          return;
        } else {
          errorDiv.style.display = 'none';
        }
      }
    };

    // Auto-select end date when start date is selected (minimum 4 days)
    startDateInput.addEventListener('change', function () {
      const startDate = this.value;
      if (startDate) {
        const start = new Date(startDate);
        // Set end date to 4 days after start (minimum rental period)
        const end = new Date(start);
        end.setDate(end.getDate() + 3); // +3 because we count both start and end days (4 days total)

        // Format date as YYYY-MM-DD
        const year = end.getFullYear();
        const month = String(end.getMonth() + 1).padStart(2, '0');
        const day = String(end.getDate()).padStart(2, '0');
        const formattedEndDate = `${year}-${month}-${day}`;

        endDateInput.value = formattedEndDate;

        // Trigger validation
        handleDateChange();
      }
    });

    // Auto-calculate when end date changes
    endDateInput.addEventListener('change', function () {
      // Trigger validation when end date is manually changed
      handleDateChange();
    });
  }
}

// Function to create delivery and pickup blocks automatically
function createDeliveryPickupBlocks(startDate, endDate, availableIndex) {
  // Logic removed as per user request
  return;
}

function removeAvailabilityBlock(button) {
  const block = button.closest('.availability-block');
  const blockType = block.getAttribute('data-type');
  const availableIndex = block.getAttribute('data-delivery-for') || block.getAttribute('data-pickup-for');

  // If removing an available block, also remove associated delivery/pickup blocks
  if (blockType === 'available') {
    const index = block.getAttribute('data-index');
    // Remove associated delivery blocks
    document.querySelectorAll(`[data-delivery-for="${index}"]`).forEach(deliveryBlock => {
      deliveryBlock.remove();
    });
    // Remove associated pickup blocks
    document.querySelectorAll(`[data-pickup-for="${index}"]`).forEach(pickupBlock => {
      pickupBlock.remove();
    });
  }

  // If removing a delivery/pickup block, check if we need to recreate it
  if (availableIndex) {
    const availableBlock = document.querySelector(`[data-index="${availableIndex}"]`);
    if (availableBlock) {
      const startDateInput = availableBlock.querySelector('input[name*="[start_date]"]');
      const endDateInput = availableBlock.querySelector('input[name*="[end_date]"]');
      if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
        // Recreate the block after a short delay to allow removal first
        setTimeout(() => {
          createDeliveryPickupBlocks(startDateInput.value, endDateInput.value, availableIndex);
        }, 100);
      }
    }
  }

  block.remove();
}

// Auto-fill measurements when size is selected
document.addEventListener('change', function (e) {
  if (e.target && e.target.name === 'size') {
    const selectedOption = e.target.options[e.target.selectedIndex];
    if (selectedOption) {
      const chest = selectedOption.getAttribute('data-chest') || '';
      const waist = selectedOption.getAttribute('data-waist') || '';
      const length = selectedOption.getAttribute('data-length') || '';
      const shoulder = selectedOption.getAttribute('data-shoulder') || '';
      const sleeve = selectedOption.getAttribute('data-sleeve') || '';

      const chestInput = document.querySelector('input[name="chest_bust"]');
      const waistInput = document.querySelector('input[name="waist"]');
      const lengthInput = document.querySelector('input[name="length"]');
      const shoulderInput = document.querySelector('input[name="shoulder"]');
      const sleeveInput = document.querySelector('input[name="sleeve_length"]');

      if (chestInput) chestInput.value = chest;
      if (waistInput) waistInput.value = waist;
      if (lengthInput) lengthInput.value = length;
      if (shoulderInput) shoulderInput.value = shoulder;
      if (sleeveInput) sleeveInput.value = sleeve;
    }
  }
});
// Measurement unit toggle and conversion
const unitSelectors = document.querySelectorAll('.unit-selector');
const measurementInputs = document.querySelectorAll('.measurement-input');
const measurementLabels = document.querySelectorAll('.measurement-label');

unitSelectors.forEach(selector => {
  selector.addEventListener('change', function () {
    const unit = this.value;
    const isCm = unit === 'cm';
    const factor = isCm ? 2.54 : (1 / 2.54);

    measurementLabels.forEach(label => {
      // Logic to update label text with unit removed as per user request
    });

    measurementInputs.forEach(input => {
      // Update placeholder
      const placeholder = input.getAttribute('placeholder').replace(/\s*\((inch|inches|cm)\)/gi, '');
      input.setAttribute('placeholder', `${placeholder} (${unit})`);

      // Convert value if it exists
      if (input.value) {
        let val = parseFloat(input.value.replace(/[^0-9.]/g, ''));
        if (!isNaN(val)) {
          input.value = (val * factor).toFixed(1).replace(/\.0$/, '');
        }
      }
    });
  });
});

// Initialize placeholders and labels
const currentUnit = document.querySelector('.unit-selector:checked')?.value || 'inch';
measurementLabels.forEach(label => {
  // Initialization logic removed to keep labels clean
});
measurementInputs.forEach(input => {
  const placeholder = input.getAttribute('placeholder').replace(/\s*\((inch|inches|cm)\)/gi, '');
  input.setAttribute('placeholder', `${placeholder} (${currentUnit})`);
});

function handleImagePreview(input, id) {
  const preview = document.getElementById(`preview-${id}`);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.style.backgroundImage = `url(${e.target.result})`;
      preview.style.display = 'block';
    }
    reader.readAsDataURL(input.files[0]);
  }
}
