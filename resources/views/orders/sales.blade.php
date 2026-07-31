@extends('layouts.app')

@section('title', 'My Sales Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/listed-clothes.css') }}">
@endsection

@section('content')
<div class="container py-lg-5 pt-0 pb-5">
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

                <div class="sale-card position-relative">
                    @php
                        $sellerInvoices = $order->invoices->filter(function($inv) {
                            return $inv->issued_to_id == auth()->id();
                        });
                    @endphp
                    
                    @if($sellerInvoices->isNotEmpty())
                        <div class="dropdown position-absolute" style="top: 1.25rem; right: 1.25rem; z-index: 10;">
                            <button class="btn btn-light btn-sm dropdown-toggle rounded-pill fw-bold" type="button" data-toggle="dropdown" style="font-size: 0.75rem; padding: 0.35rem 0.75rem; background: #f8f9fa; border: 1px solid #e9ecef; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <i class="bi bi-file-earmark-pdf text-danger"></i> Invoices
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow border-0 rounded-3 p-2 mt-2" style="min-width: 160px;">
                                @foreach($sellerInvoices as $inv)
                                    <a class="dropdown-item rounded-2 py-2 d-flex align-items-center" href="{{ route('invoices.download', $inv->id) }}">
                                        <i class="bi bi-download me-2 text-primary"></i>
                                        <span class="small fw-medium">
                                            @if($inv->type == 'rent_sale') {{ $inv->invoice_number }}
                                            @elseif($inv->type == 'platform_fee_seller') {{ $inv->invoice_number }}
                                            @else Invoice #{{ $inv->invoice_number }} @endif
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

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

                                @if($canRate)
                                    @if($hasRated)
                                        <button class="btn btn-premium-success btn-sale-action opacity-75" disabled>
                                            <i class="bi bi-check-circle-fill"></i> RATED
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('ratings.store') }}" method="POST" id="rateForm">
                @csrf
                <input type="hidden" name="order_id" id="rating_order_id">
                <input type="hidden" name="rating" id="rating_value" required>
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="font-size: 1.1rem;">Rate Buyer</h5>
                    <button type="button" class="btn-close ms-auto" data-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.2rem; color: #94a3b8;">&times;</button>
                </div>
                <div class="modal-body py-3 px-3">
                    <div class="p-2 bg-light rounded-3 mb-3 text-center border">
                        <span class="text-muted small d-block mb-0">Rating Buyer: <strong class="text-dark" id="rating_buyer_name">--</strong></span>
                    </div>
                    
                    <div class="text-center mb-3">
                        <p class="text-muted fw-semibold small mb-2">How was your experience?</p>
                        <div class="rating-stars mb-0 d-flex justify-content-center" style="font-size: 2rem; gap: 10px !important;">
                            <i class="bi bi-star cursor-pointer text-warning" data-value="1"></i>
                            <i class="bi bi-star cursor-pointer text-warning" data-value="2"></i>
                            <i class="bi bi-star cursor-pointer text-warning" data-value="3"></i>
                            <i class="bi bi-star cursor-pointer text-warning" data-value="4"></i>
                            <i class="bi bi-star cursor-pointer text-warning" data-value="5"></i>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="extra-small fw-bold text-uppercase letter-spacing-1 text-muted mb-2 d-block">Review (Optional)</label>
                        <textarea name="review" class="form-control border rounded-4 p-3" rows="4" placeholder="Share your experience dealing with this buyer..." style="font-size: 0.85rem;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-3 pb-3 pt-0 d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal" style="height: 38px; font-weight: 600; font-size: 0.85rem;">Close</button>
                    <button type="submit" class="btn btn-premium rounded-pill px-3" style="height: 38px; font-weight: 700; min-width: 120px; font-size: 0.85rem;">Submit Rating</button>
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
                            const ratedBadge = $('<button class="btn btn-premium-success btn-sale-action opacity-75" disabled><i class="bi bi-check-circle-fill"></i> RATED</button>');
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
