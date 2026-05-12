@extends('layouts.app')

@section('title', 'My Sales Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/listed-clothes.css') }}">
@endsection

@section('content')
<div class="container py-5">
    <!-- Management Header -->
    <div class="management-header">
        <div class="management-title">
            <h2>My Sales Dashboard</h2>
            <p>Track your earnings and manage incoming rental/sale requests</p>
        </div>
        <div class="management-actions">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-house-door me-2"></i> BACK TO HOME
            </a>
        </div>
    </div>

    @if(!$orders->isEmpty())
        @php
            $totalEarnings = 0;
            $activeOrdersCount = 0;
            foreach($orders as $order) {
                $totalEarnings += $order->items->sum('price');
                if(!in_array($order->status, ['Delivered', 'Cancelled', 'Returned'])) {
                    $activeOrdersCount++;
                }
            }
        @endphp

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-earnings">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-info">
                    <p>Total Revenue</p>
                    <h3>₹{{ number_format($totalEarnings, 0) }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-orders">
                    <i class="bi bi-bag-check"></i>
                </div>
                <div class="stat-info">
                    <p>Total Sales</p>
                    <h3>{{ $orders->total() }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-transit">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="stat-info">
                    <p>Active Orders</p>
                    <h3>{{ $activeOrdersCount }}</h3>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="orders-container">
            @foreach($orders as $order)
                @php
                    $requiredStatus = $order->has_rental_items ? ['Returned'] : ['Delivered', 'Returned'];
                    $canRate = in_array($order->status, $requiredStatus);
                    $hasRated = \App\Models\Rating::where('order_id', $order->id)->where('rater_id', auth()->id())->exists();
                    $firstItem = $order->items->first();
                @endphp

                <div class="sale-card">
                    <!-- Product Image & Count -->
                    <div class="sale-image-group">
                        @if($firstItem && $firstItem->cloth && $firstItem->cloth->images->count() > 0)
                            <img src="{{ asset('storage/' . $firstItem->cloth->images->first()->image_path) }}" class="sale-image" alt="Product">
                        @else
                            <div class="sale-image bg-light d-flex align-items-center justify-content-center text-muted">
                                <i class="bi bi-image"></i>
                            </div>
                        @endif
                        @if($order->items->count() > 1)
                            <div class="item-count-badge">+{{ $order->items->count() - 1 }}</div>
                        @endif

                        <!-- Mobile-only Info below image -->
                        <div class="sale-mobile-meta d-md-none mt-1">
                            <div class="order-id-mobile mb-1">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="d-flex flex-column gap-1 align-items-center">
                                @if($order->has_rental_items)
                                    <span class="badge badge-pending extra-small px-1 py-0">RENTAL</span>
                                @endif
                                @if($order->has_purchase_items)
                                    <span class="badge badge-approved extra-small px-1 py-0">SALE</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Order Info -->
                    <div class="sale-info">
                        <div class="order-meta">
                            <span class="order-id d-none d-md-block">ORD #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <div class="buyer-pill">
                                <div class="buyer-avatar">{{ substr($order->buyer->name, 0, 1) }}</div>
                                <span>{{ $order->buyer->name }}</span>
                            </div>
                            <span class="text-muted extra-small ms-auto">
                                <i class="bi bi-calendar3 me-1"></i> {{ $order->created_at->format('M d, Y') }}
                            </span>
                        </div>

                        <div class="sale-items-list">
                            <h5 class="sale-item-name mb-1">
                                {{ $firstItem->cloth->title ?? 'Deleted Item' }}
                                @if($order->items->count() > 1)
                                    <span class="text-muted small">& {{ $order->items->count() - 1 }} more items</span>
                                @endif
                            </h5>
                            <div class="d-flex gap-2 align-items-center mt-2">
                                <div class="d-none d-md-flex gap-2">
                                    @if($order->has_rental_items)
                                        <span class="badge badge-pending">RENTAL</span>
                                    @endif
                                    @if($order->has_purchase_items)
                                        <span class="badge badge-approved">SALE</span>
                                    @endif
                                </div>
                                <span class="status-badge {{ strtolower($order->status) == 'delivered' ? 'badge-approved' : (strtolower($order->status) == 'cancelled' ? 'badge-rejected' : 'badge-pending') }}">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </div>

                        <div class="sale-footer">
                            <div class="sale-amount-section">
                                <span class="label">You Earned</span>
                                <span class="value">₹{{ number_format($order->items->sum('price'), 2) }}</span>
                            </div>

                            <div class="sale-actions">
                                @php
                                    $sellerInvoices = $order->invoices->filter(function($inv) {
                                        return $inv->issued_by_id == auth()->id() || ($inv->type == 'platform_fee_seller' && $inv->issued_to_id == auth()->id());
                                    });
                                @endphp
                                
                                @if($sellerInvoices->isNotEmpty())
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sale-action dropdown-toggle" type="button" data-toggle="dropdown">
                                            <i class="bi bi-file-earmark-pdf"></i> Invoices
                                        </button>
                                        <div class="dropdown-menu shadow-sm border-0 rounded-4 p-2">
                                            @foreach($sellerInvoices as $inv)
                                                <a class="dropdown-item rounded-3" href="{{ route('invoices.download', $inv->id) }}">
                                                    <i class="bi bi-download me-2"></i>
                                                    @if($inv->type == 'rent_sale') Tax Invoice
                                                    @elseif($inv->type == 'platform_fee_seller') Commission
                                                    @else Invoice #{{ $inv->invoice_number }} @endif
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($canRate)
                                    @if($hasRated)
                                        <button class="btn btn-outline-success btn-sale-action" disabled>
                                            <i class="bi bi-check-circle"></i> RATED
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-premium btn-sale-action px-3" data-toggle="modal" data-target="#rateModal" data-order-id="{{ $order->id }}" data-buyer-name="{{ $order->buyer->name }}">
                                            <i class="bi bi-star"></i> RATE BUYER
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    @else
        <div class="text-center py-5 bg-white rounded-5 shadow-sm mt-4">
            <div class="mb-4">
                <i class="bi bi-bag-x text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
            </div>
            <h3 class="fw-bold text-dark">No sales yet</h3>
            <p class="text-muted mb-4">Your collection is waiting for its first admirer!</p>
            <a href="{{ route('listed.clothes') }}" class="btn btn-premium btn-lg px-5 py-3">
                Manage Your Listings
            </a>
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
