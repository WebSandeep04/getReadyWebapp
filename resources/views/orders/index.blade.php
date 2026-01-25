@extends('layouts.app')

@section('title', 'My Orders')

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
                                    <td>₹{{ number_format($order->total_amount, 2) }}</td>
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
                                            ₹{{ number_format($order->security_amount, 2) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->has_rental_items)
                                            <span class="badge bg-light text-dark">
                                                {{ \Carbon\Carbon::parse($order->rental_from)->format('d/m/Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($order->rental_to)->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'Confirmed' ? 'success' : ($order->status === 'Delivered' ? 'primary' : ($order->status === 'Cancelled' ? 'danger' : 'warning text-dark')) }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->shipment)
                                            <div class="small">
                                                <span class="fw-bold d-block">{{ $order->shipment->courier_name }}</span>
                                                <span class="text-muted d-block" style="font-size: 0.8em">AWB: {{ $order->shipment->waybill_number }}</span>
                                                @if($order->shipment->status)
                                                    <span class="badge bg-secondary mb-1">{{ $order->shipment->status }}</span>
                                                @endif
                                                @if($order->shipment->tracking_url)
                                                    <a href="{{ $order->shipment->tracking_url }}" target="_blank" class="btn btn-xs btn-outline-info p-0 px-1" style="font-size: 0.75rem;">Track</a>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
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

<!-- Rating Modal -->
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
    });
</script>

<style>
    .table thead th {
        letter-spacing: .06em;
    }
</style>
@endsection

