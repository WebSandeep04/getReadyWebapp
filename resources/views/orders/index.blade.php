@extends('layouts.app')

@section('title', 'My Orders')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .table thead th {
        letter-spacing: .06em;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-warning">
                <i class="bi bi-bag-check me-2"></i>My Orders
            </h2>
            <p class="text-muted mb-0">Track payments, rental periods and statuses for every checkout.</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Home
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

    @if(request()->get('payment') === 'success')
        <div class="alert alert-success alert-dismissible border-0 shadow-sm fade show" role="alert" style="background: #e6fffa; color: #234e52;">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill mr-2 h4 mb-0"></i>
                <div>
                    <strong>Payment Successful!</strong> Your order has been placed successfully. You can track its status below.
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-card-list text-muted" style="font-size:3rem;"></i>
                <h4 class="mt-3">No orders yet</h4>
                <p class="text-muted">Browse the catalog and complete a checkout to see it listed here.</p>
                <a href="{{ route('home') }}" class="btn btn-warning">
                    <i class="bi bi-bag-plus me-1"></i>Start Shopping
                </a>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>#</th>
                                <th>Total</th>
                                <th>Order Type</th>
                                <th>Security</th>
                                <th>Rental Window</th>
                                <th>Status</th>
                                <th>Tracking</th>
                                <th>Payment</th>
                                <th>Placed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php
                                    $latestPayment = $order->payments->sortByDesc('paid_at')->first();
                                    $canRate = in_array($order->status, ['Delivered', 'Returned']);
                                    // Check if already rated (simple check, ideally eager loaded)
                                    $hasRated = \App\Models\Rating::where('order_id', $order->id)->where('rater_id', auth()->id())->exists();
                                @endphp
                                <tr>
                                    <td class="fw-semibold">GR-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        @if($order->has_rental_items && $order->has_purchase_items)
                                            <span class="badge bg-primary">Mixed</span>
                                        @elseif($order->has_rental_items)
                                            <span class="badge bg-info text-dark"><i class="bi bi-calendar-week me-1"></i>Rental</span>
                                        @elseif($order->has_purchase_items)
                                            <span class="badge bg-success"><i class="bi bi-bag-check me-1"></i>Purchase</span>
                                        @else
                                            <span class="badge bg-secondary">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->has_rental_items)
                                            {{ number_format($order->security_amount, 2) }}
                                        @else
                                            <span class="text-muted">â€”</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->has_rental_items)
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="badge bg-light text-dark mb-1">
                                                    {{ \Carbon\Carbon::parse($order->rental_from)->format('d/m/Y') }}
                                                    -
                                                    {{ \Carbon\Carbon::parse($order->rental_to)->format('d/m/Y') }}
                                                </span>
                                                <small class="text-muted" style="font-size: 0.7rem;">Return: {{ ($order->return_date ?: \Carbon\Carbon::parse($order->rental_to)->addDay())->format('d/m/Y') }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">â€”</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'Confirmed' ? 'success' : ($order->status === 'Delivered' ? 'primary' : ($order->status === 'Cancelled' ? 'danger' : 'warning text-dark')) }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $orderShipments = $order->shipments;
                                        @endphp
                                        @if($orderShipments->isNotEmpty())
                                            @foreach($orderShipments as $shipment)
                                                <div class="small {{ !$loop->first ? 'mt-2 pt-2 border-top' : '' }}">
                                                    <span class="fw-bold d-block text-{{ $shipment->type === 'reverse' ? 'info' : 'success' }}">
                                                        {{ $shipment->type === 'reverse' ? 'Returning' : 'Outgoing' }}
                                                    </span>
                                                    <span class="text-muted d-block" style="font-size: 0.8em">AWB: {{ $shipment->waybill_number }}</span>
                                                    @if($shipment->status)
                                                        <span class="badge bg-secondary mb-1">{{ $shipment->status }}</span>
                                                    @endif
                                                    @if($shipment->tracking_url)
                                                        <a href="{{ $shipment->tracking_url }}" target="_blank" class="btn btn-xs btn-outline-info p-0 px-1" style="font-size: 0.75rem;">Track</a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">â€”</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($latestPayment)
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold text-success">{{ $latestPayment->payment_status }}</span>
                                                <small class="text-muted">{{ $latestPayment->payment_method }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y, h:i A') }}</td>
                                    <td class='text-nowrap'>
                                         @php
                                             $allInvoices = $order->invoices->where('issued_to_id', auth()->id());
                                             $mainInvoices = $allInvoices->whereNull('order_extension_id');
                                             $extInvoices = $allInvoices->whereNotNull('order_extension_id');
                                             
                                             $showMainInvoices = $mainInvoices->isNotEmpty();
                                             if ($order->status === 'Delivered') {
                                                 $showMainInvoices = $showMainInvoices && ($order->delivered_at && $order->delivered_at->addMinutes(2)->isPast());
                                             } elseif (in_array($order->status, ['Pending', 'Confirmed', 'Shipped', 'Order Confirmed & Shipment Created'])) {
                                                 $showMainInvoices = false;
                                             }

                                             // Extension invoices should be shown immediately upon payment
                                             $showExtInvoices = $extInvoices->isNotEmpty();
                                             
                                             $visibleInvoices = collect();
                                             if ($showMainInvoices) $visibleInvoices = $visibleInvoices->concat($mainInvoices);
                                             if ($showExtInvoices) $visibleInvoices = $visibleInvoices->concat($extInvoices);
                                         @endphp
                                         <div class="d-flex align-items-center gap-3">
                                             @if($visibleInvoices->isNotEmpty())
                                                 <div class="dropdown">
                                                 <button class="btn btn-sm btn-outline-secondary px-2 border-0" type="button" data-toggle="dropdown" aria-expanded="false" title="Download Invoices">
                                                     <i class="bi bi-file-earmark-text h5 mb-0"></i>
                                                 </button>
                                                 <div class="dropdown-menu">
                                                     @foreach($visibleInvoices as $inv)
                                                         @php
                                                             $extPrefix = $inv->order_extension_id ? 'Extension: ' : '';
                                                         @endphp
                                                         <a class="dropdown-item" href="{{ route('invoices.download', $inv->id) }}">
                                                             {{ $extPrefix }}
                                                             @if($inv->type == 'rent_sale') Tax Invoice (Items)
                                                             @elseif($inv->type == 'platform_fee_buyer') Service Fee (Platform)
                                                             @else Invoice #{{ $inv->invoice_number }} @endif
                                                         </a>
                                                     @endforeach
                                                 </div>
                                             </div>
                                         @endif

                                        @if($canRate || $order->status === 'Delivered')

                                                @if($hasRated)
                                                    <span class="text-success h5 mb-0" title="Rated successfully"><i class="bi bi-check-circle-fill"></i></span>
                                                @elseif($canRate)
                                                    <button type="button" class="btn btn-sm btn-warning rounded-circle shadow-sm px-2" data-toggle="modal" data-target="#rateModal" data-order-id="{{ $order->id }}" title="Rate Seller">
                                                        <i class="bi bi-star"></i>
                                                    </button>
                                                @endif

                                                  @if($order->status === 'Delivered')
                                                     @php
                                                         $deliveredAt = $order->delivered_at;
                                                         $canReport = $deliveredAt && $deliveredAt->addMinutes(2)->isFuture();
                                                     @endphp
                                                     
                                                     @if($canReport)
                                                        <button type="button" class="btn btn-sm btn-outline-danger px-2 border-0" data-toggle="modal" data-target="#returnModal" data-order-id="{{ $order->id }}" title="Report Issue">
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                        </button>
                                                     @endif
                                                 @elseif($order->status === 'Return Requested')
                                                     <span class="badge bg-warning text-dark font-weight-normal px-2" style="font-size: 0.65rem;">Requested</span>
                                                @endif
                                                
                                                @php
                                                    $returnDate = $order->return_date ? \Carbon\Carbon::parse($order->return_date) : \Carbon\Carbon::parse($order->rental_to)->addDay();
                                                    $isRentalEnded = $returnDate->isPast() && !$returnDate->isToday();
                                                @endphp

                                                @if($order->has_rental_items && !$isRentalEnded && !in_array($order->status, ['Cancelled', 'Returned']))
                                                    <button type="button" class="btn btn-sm btn-outline-info px-2 border-0" data-toggle="modal" data-target="#extensionModal" title="Extend Rental" 
                                                        data-order-id="{{ $order->id }}" 
                                                        data-current-to="{{ ($order->return_date ?? \Carbon\Carbon::parse($order->rental_to)->addDay())->format('d M Y') }}"
                                                        data-rental-to="{{ \Carbon\Carbon::parse($order->rental_to)->format('Y-m-d') }}">
                                                        <i class="bi bi-calendar-plus h5 mb-0"></i>
                                                    </button>
                                                @endif

                                         @else
                                             <span class="text-muted small">Available after delivery</span>
                                         @endif
                                         </div>
                                     </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white">
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Return Request Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST" id="returnForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">Report an Issue / Request Return</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="return_reason" class="form-label">Issue Category</label>
                        <select class="form-control" name="return_reason" id="return_reason" required>
                            <option value="">Select a reason</option>
                            <option value="Damaged Item">Damaged Item</option>
                            <option value="Wrong Item Received">Wrong Item Received</option>
                            <option value="Inaccurate Description">Inaccurate Description</option>
                            <option value="Size/Fit Issue">Size/Fit Issue</option>
                            <option value="Quality Issue">Quality/Condition Issue</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="return_details" class="form-label">Provide more details</label>
                        <textarea class="form-control" id="return_details" name="return_details" rows="4" placeholder="Describe the problem in detail..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="return_images" class="form-label">Upload Evidence Images (Max 3)</label>
                        <input type="file" class="form-control-file" name="return_images[]" id="return_images" multiple accept="image/*">
                        <small class="text-muted">Max file size: 2MB per image.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="rateModal" tabindex="-1" aria-labelledby="rateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('ratings.store') }}" method="POST" id="rateForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rateModalLabel">Rate Your Experience</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="rating_order_id">
                    
                    <div class="mb-3 text-center">
                        <label class="form-label d-block">How would you rate the seller?</label>
                        <div class="rating-stars" style="font-size: 2rem; color: #ffc107; cursor: pointer;">
                            <i class="bi bi-star" data-value="1"></i>
                            <i class="bi bi-star" data-value="2"></i>
                            <i class="bi bi-star" data-value="3"></i>
                            <i class="bi bi-star" data-value="4"></i>
                            <i class="bi bi-star" data-value="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="rating_value" required>
                    </div>

                    <div class="mb-3">
                        <label for="review" class="form-label">Review (Optional)</label>
                        <textarea class="form-control" id="review" name="review" rows="3" placeholder="Share your experience..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Rating</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Extension Modal -->
<div class="modal fade" id="extensionModal" tabindex="-1" aria-labelledby="extensionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="extensionModalLabel"><i class="bi bi-calendar-plus me-2"></i>Extend Rental Period</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-1"></i>Extensions are billed at the standard daily rate (Base Rent / 4).
                </div>

                <div class="mb-4">
                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Current Return Date</label>
                    <span id="current_return_date" class="h6 mb-0">â€”</span>
                </div>

                <div class="mb-3">
                    <label for="extension_date" class="form-label fw-bold">Select New Return Date</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-calendar-event"></i></span>
                        <input type="text" id="extension_date" class="form-control bg-white" placeholder="Pick a date" readonly>
                    </div>
                </div>

                <div id="quote_container" class="mt-4 p-3 border rounded bg-light d-none">
                    <h6 class="mb-3 border-bottom pb-2">Price Breakdown</h6>
                    <div id="quote_items"></div>
                    <div class="d-flex justify-content-between font-weight-bold mt-2 pt-2 border-top">
                        <span>Total Additional Amount:</span>
                        <span id="total_extension_amount" class="text-primary">0.00</span>
                    </div>
                    <div class="mt-3 small text-muted">
                        <span>New Return Date:</span>
                        <span id="new_return_date" class="font-weight-bold ml-1">â€”</span>
                    </div>
                </div>

                <div id="availability_alert" class="alert alert-danger mt-3 d-none">
                    <i class="bi bi-calendar-x me-1"></i>Selected extension is not available for one or more items.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="proceed_extension" class="btn btn-warning" disabled>
                    Proceed to Pay <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let activeOrderId = null;
        let activeButton = null;

        // Use jQuery for Bootstrap 4 modal events
        $('#rateModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            activeButton = button;
            activeOrderId = button.data('order-id');
            const modal = $(this);
            modal.find('#rating_order_id').val(activeOrderId);
            
            // Reset form
            modal.find('form')[0].reset();
            resetStars();
            // Clear any previous error messages
            modal.find('.alert-danger').remove();
        });

        // AJAX Form Submission
        $('#rateForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.text();
            
            submitBtn.prop('disabled', true).text('Submitting...');
            form.find('.alert-danger').remove();

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#rateModal').modal('hide');
                        if (activeButton) {
                            const ratedBadge = $('<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Rated</span>');
                            activeButton.parent().html(ratedBadge);
                        }
                    }
                },
                error: function(xhr) {
                    let message = 'An error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    const errorAlert = '<div class="alert alert-danger">' + message + '</div>';
                    form.find('.modal-body').prepend(errorAlert);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalBtnText);
                }
            });
        });

        const stars = document.querySelectorAll('.rating-stars i');
        const ratingInput = document.getElementById('rating_value');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                ratingInput.value = value;
                updateStars(value);
            });
            
            star.addEventListener('mouseover', function() {
                updateStars(this.getAttribute('data-value'));
            });
            
            star.addEventListener('mouseout', function() {
                updateStars(ratingInput.value || 0);
            });
        });

        function updateStars(value) {
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= value) {
                    s.classList.remove('bi-star');
                    s.classList.add('bi-star-fill');
                } else {
                    s.classList.remove('bi-star-fill');
                    s.classList.add('bi-star');
                }
            });
        }
        
        function resetStars() {
            stars.forEach(s => {
                s.classList.remove('bi-star-fill');
                s.classList.add('bi-star');
            });
            ratingInput.value = '';
        }
        // Return Modal Logic
        $('#returnModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const orderId = button.data('order-id');
            const modal = $(this);
            const form = modal.find('form');
            
            // Dynamically set action URL
            form.attr('action', `/orders/${orderId}/return-request`);
            form[0].reset();
        });

        // Extension Logic
        let selectedOrderId = null;
        let selectedDays = null;
        let extensionDatePicker = null;
        let currentRentalToDate = null;

        $('#extensionModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            selectedOrderId = button.data('order-id');
            const currentReturnDate = button.data('current-to'); // E.g., "28 Feb 2026"
            const currentRentalTo = button.data('rental-to'); // E.g., "2026-02-27"
            
            const modal = $(this);
            modal.find('#current_return_date').text(currentReturnDate);
            
            // Parse actual rental end date
            currentRentalToDate = new Date(currentRentalTo);
            currentRentalToDate.setHours(0, 0, 0, 0);
            
            const minExtensionDate = new Date(currentRentalToDate);
            minExtensionDate.setDate(minExtensionDate.getDate() + 1);

            // Initialize or Refresh Flatpickr
            if (extensionDatePicker) {
                extensionDatePicker.destroy();
            }

            extensionDatePicker = flatpickr("#extension_date", {
                minDate: minExtensionDate,
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d M Y",
                disableMobile: "true",
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        const newDate = selectedDates[0];
                        newDate.setHours(0, 0, 0, 0);
                        const diffTime = Math.abs(newDate - currentRentalToDate);
                        selectedDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                        fetchQuote(selectedOrderId, selectedDays);
                    }
                }
            });

            // Reset state
            selectedDays = null;
            modal.find('#extension_date').val('');
            $('#quote_container, #availability_alert').addClass('d-none');
            $('#proceed_extension').prop('disabled', true);
        });

        function fetchQuote(orderId, days) {
            $.get(`/orders/${orderId}/extension-quote`, { days: days }, function(response) {
                if(response.success) {
                    $('#quote_container').removeClass('d-none');
                    $('#new_return_date').text(new Date(response.new_return_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }));
                    $('#total_extension_amount').text('' + response.quote.total_additional_amount.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    
                    let itemsHtml = '';
                    response.quote.items.forEach(item => {
                        itemsHtml += `<div class="d-flex justify-content-between small mb-1">
                            <span>${item.cloth_title}</span>
                            <span>${item.pricing.total_buyer_pay}</span>
                        </div>`;
                    });
                    $('#quote_items').html(itemsHtml);

                    if(response.is_available) {
                        $('#availability_alert').addClass('d-none');
                        $('#proceed_extension').prop('disabled', false);
                    } else {
                        $('#availability_alert').removeClass('d-none');
                        $('#proceed_extension').prop('disabled', true);
                    }
                }
            });
        }

        $('#proceed_extension').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

            $.post(`/orders/${selectedOrderId}/extend`, { 
                _token: $('meta[name="csrf-token"]').attr('content'),
                days: selectedDays 
            }, function(response) {
                if(response.success) {
                    const options = {
                        "key": response.key,
                        "amount": response.razorpay_order.amount,
                        "currency": response.razorpay_order.currency,
                        "name": "GetReady Rental Extension",
                        "description": "Extend Rental Period by " + selectedDays + " days",
                        "handler": function (paymentResponse) {
                            verifyExtensionPayment(response.extension_id, paymentResponse.razorpay_payment_id);
                        },
                        "prefill": {
                            "name": "{{ auth()->user()->name }}",
                            "email": "{{ auth()->user()->email }}"
                        },
                        "theme": { "color": "#ffc107" }
                    };
                    const rzp = new Razorpay(options);
                    rzp.open();
                }
            }).always(function() {
                btn.prop('disabled', false).html('Proceed to Pay <i class="bi bi-arrow-right ms-1"></i>');
            });
        });

        function verifyExtensionPayment(extensionId, paymentId) {
            $.post('/orders/extension/verify', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                extension_id: extensionId,
                razorpay_payment_id: paymentId
            }, function(response) {
                if(response.success) {
                    $('#extensionModal').modal('hide');
                    alert(response.message);
                    location.reload();
                }
            });
        }
    });
</script>

<style>
    .table thead th {
        letter-spacing: .06em;
    }
</style>
@endsection
                                     </td>

