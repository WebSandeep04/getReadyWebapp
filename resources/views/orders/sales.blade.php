@extends('layouts.app')

@section('title', 'My Sales')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-warning">
                <i class="bi bi-cash-coin me-2"></i>My Sales
            </h2>
            <p class="text-muted mb-0">Track your sold and rented items and manage incoming orders.</p>
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
                <i class="bi bi-inbox text-muted" style="font-size:3rem;"></i>
                <h4 class="mt-3">No sales yet</h4>
                <p class="text-muted">Once someone buys or rents your items, they will appear here.</p>
                <a href="{{ route('listed.clothes') }}" class="btn btn-warning">
                    <i class="bi bi-tag-fill me-1"></i>Manage Listings
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
                                <th>Order #</th>
                                <th>Items</th>
                                <th>Buyer</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php
                                    // For rentals, seller can only rate buyer after item is Returned.
                                    // For purchases, seller can rate after Delivered.
                                    $requiredStatus = $order->has_rental_items ? ['Returned'] : ['Delivered', 'Returned'];
                                    $canRate = in_array($order->status, $requiredStatus);

                                    // Check if I (Seller) have already rated the Buyer for this order
                                    $hasRated = \App\Models\Rating::where('order_id', $order->id)
                                        ->where('rater_id', auth()->id())
                                        ->exists();
                                @endphp
                                <tr>
                                    <td class="fw-semibold">GR-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($order->items as $item)
                                                <li>{{ $item->cloth->title }} ({{ $item->cloth->size->name ?? 'N/A' }})</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($order->buyer->profile_image)
                                                <img src="{{ asset('storage/' . $order->buyer->profile_image) }}" class="rounded-circle me-2" width="24" height="24">
                                            @else
                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center text-white small" style="width: 24px; height: 24px;">{{ substr($order->buyer->name, 0, 1) }}</div>
                                            @endif
                                            <span>{{ $order->buyer->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        {{-- Calculate amount relevant to this seller --}}
                                        @php
                                            $sellerTotal = $order->items->sum('price');
                                        @endphp
                                        ₹{{ number_format($sellerTotal, 2) }}
                                    </td>
                                    <td>
                                        @if($order->has_rental_items && $order->has_purchase_items)
                                            <span class="badge bg-primary">Mixed</span>
                                        @elseif($order->has_rental_items)
                                            <span class="badge bg-info text-dark">Rental</span>
                                        @elseif($order->has_purchase_items)
                                            <span class="badge bg-success">Sale</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'Confirmed' ? 'success' : ($order->status === 'Delivered' ? 'primary' : ($order->status === 'Cancelled' ? 'danger' : 'warning text-dark')) }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
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
                    <h5 class="modal-title" id="rateModalLabel">Rate Buyer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="rating_order_id">
                    
                    <div class="alert alert-light text-center border">
                        Rating buyer: <strong id="rating_buyer_name"></strong>
                    </div>

                    <div class="mb-3 text-center">
                        <label class="form-label d-block">How was your experience?</label>
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
                        <textarea class="form-control" id="review" name="review" rows="3" placeholder="Share your experience dealing with this buyer..."></textarea>
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
            const buyerName = button.data('buyer-name');
            const modal = $(this);
            
            modal.find('#rating_order_id').val(activeOrderId);
            modal.find('#rating_buyer_name').text(buyerName);
            
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
                        // Use a simple alert or toast if preferred, but updating UI is key
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
