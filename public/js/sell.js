const steps = document.querySelectorAll(".step-content");
const indicators = document.querySelectorAll(".steps .step");
const nextBtn = document.getElementById("nextBtn");
const prevBtn = document.getElementById("prevBtn");
const submitBtn = document.getElementById("submitBtn");

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
}


// Show/hide buttons based on current step
function updateButtons() {
  if (currentStep === 0) {
    // First step - hide previous button, show next button
    prevBtn.style.display = "none";
    nextBtn.style.display = "block";
    submitBtn.style.display = "none";
  } else if (currentStep === steps.length - 1) {
    // Last step - show previous button, hide next button, show submit button
    prevBtn.style.display = "block";
    nextBtn.style.display = "none";
    submitBtn.style.display = "block";
  } else {
    // Middle steps - show both previous and next buttons, hide submit button
    prevBtn.style.display = "block";
    nextBtn.style.display = "block";
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
if (form) {
  form.setAttribute('novalidate', true);
  form.addEventListener("submit", function (e) {
    let isValid = true;
    let firstInvalidStep = -1;
    const allInputs = form.querySelectorAll('input, select, textarea');

    // Clear previous highlights
    allInputs.forEach(input => input.classList.remove('is-invalid'));

    allInputs.forEach(input => {
      // We skip disabled or hidden input if they don't apply to current flow
      // But they're visible if their section container is visible, they are standard inputs.
      if (!input.checkValidity()) {
        isValid = false;
        input.classList.add('is-invalid');
        if (firstInvalidStep === -1) {
          const stepParent = input.closest('.step-content');
          steps.forEach((stepEl, index) => {
            if (stepEl === stepParent) firstInvalidStep = index;
          });
        }
      }
    });

    if (!isValid) {
      e.preventDefault(); // Stop submission
      if (firstInvalidStep !== -1 && firstInvalidStep !== currentStep) {
        steps[currentStep].classList.remove("active");
        indicators[currentStep].classList.remove("active");
        currentStep = firstInvalidStep;
        steps[currentStep].classList.add("active");
        indicators[currentStep].classList.add("active");
        updateButtons();
      }

      // Optionally focus the first invalid input if helpful
      const firstInvalid = document.querySelector('.is-invalid');
      if (firstInvalid) {
        setTimeout(() => firstInvalid.focus(), 100);
      }
    }
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
    <div class="availability-block" data-type="${type}" data-index="${index}">
      <div class="row">
        <div class="col-md-5">
          <label class="small">Start Date</label>
          <input type="date" class="form-control form-control-sm availability-date-input" name="availability_blocks[${index}][start_date]" required>
        </div>
        <div class="col-md-5">
          <label class="small">End Date</label>
          <input type="date" class="form-control form-control-sm availability-date-input" name="availability_blocks[${index}][end_date]" required>
        </div>
        <div class="col-md-2">
          <label class="small">&nbsp;</label>
          <button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeAvailabilityBlock(this)">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
      ${type === 'blocked' ? `
        <div class="row mt-2">
          <div class="col-12">
            <label class="small">Reason (optional)</label>
            <input type="text" class="form-control form-control-sm" name="availability_blocks[${index}][reason]" placeholder="e.g., Personal use, Maintenance">
          </div>
        </div>
      ` : ''}
      <input type="hidden" name="availability_blocks[${index}][type]" value="${type}">
      ${type === 'available' ? `
        <div class="row mt-2">
          <div class="col-12">
            <small class="text-info">
              <i class="fas fa-info-circle"></i> 
              Minimum 4 days rental required.
            </small>
            <div class="text-danger small mt-1" id="availability-error-${index}" style="display: none;"></div>
          </div>
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
