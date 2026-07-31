@extends('layouts.app')

@section('title', 'My Orders Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/listed-clothes.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Specific styles for Orders Dashboard */
    .tracking-pill {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 0.5rem;
    }
    .tracking-info { font-size: 0.75rem; font-weight: 600; color: #475569; }
    .tracking-label { font-size: 0.65rem; text-transform: uppercase; color: #94a3b8; display: block; }
    .btn-track { font-size: 0.7rem; font-weight: 800; color: #3b82f6; text-decoration: none; }

    /* Premium Extension Modal Styles */
    #extensionModal .modal-content {
        overflow: hidden;
        border-radius: 28px;
    }
    #extensionModal .modal-header {
        background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
        padding: 1.5rem 2rem;
    }
    #extensionModal .current-status-card {
        background: #f0f7ff;
        border: 1px solid #e0eeff;
        padding: 1.25rem;
        border-radius: 20px;
        height: 100%;
    }
    #extensionModal .date-selection-box {
        background: #ffffff;
        border: 2px solid #f1f5f9;
        border-radius: 18px;
        padding: 1.15rem 1rem;
        transition: all 0.3s ease;
        height: 100%;
    }
    #extensionModal .date-selection-box:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    #extensionModal .quote-receipt {
        background: #fafafa;
        border: 1px dashed #e2e8f0;
        border-radius: 20px;
        padding: 1.5rem;
    }
    #extensionModal .btn-premium-action {
        padding: 12px 30px;
        font-weight: 700;
        border-radius: 100px;
        transition: all 0.3s ease;
    }
    #extensionModal .btn-premium-pay {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
    }
    #extensionModal .btn-premium-pay:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(37, 99, 235, 0.3);
    }
    #extensionModal .btn-premium-pay:disabled {
        background: #e2e8f0;
        color: #94a3b8;
        box-shadow: none;
    }
</style>
@endsection

@section('content')
<div class="container py-lg-5 pt-0 pb-5">
    <!-- Management Header -->
    <div class="management-header">
        <div class="management-title">
            <h2>My Orders Dashboard</h2>
            <p>Manage your rentals, track shipments, and view order history</p>
        </div>
        <div class="management-actions">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-house-door me-2"></i> BACK TO HOME
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm p-4 mb-4" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm p-4 mb-4" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                <div class="fw-medium">{{ session('error') }}</div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm p-4 mb-4" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                <div class="fw-medium">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(!$orders->isEmpty())
        @php
            $totalSpent = 0;
            $activeRentals = 0;
            foreach($orders as $order) {
                $totalSpent += $order->total_amount;
                if($order->has_rental_items && !in_array($order->status, ['Returned', 'Cancelled'])) {
                    $activeRentals++;
                }
            }
        @endphp

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-earnings">
                    <i class="bi bi-credit-card"></i>
                </div>
                <div class="stat-info">
                    <p>Total Investment</p>
                    <h3>₹{{ number_format($totalSpent, 0) }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-orders">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stat-info">
                    <p>Active Rentals</p>
                    <h3>{{ $activeRentals }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-transit">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-info">
                    <p>Total Orders</p>
                    <h3>{{ $orders->total() }}</h3>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="orders-container">
            @foreach($orders as $order)
                @php
                    $latestPayment = $order->payments->sortByDesc('paid_at')->first();
                    $canRate = in_array($order->status, ['Delivered', 'Returned']);
                    $hasRated = \App\Models\Rating::where('order_id', $order->id)->where('rater_id', auth()->id())->exists();
                    $firstItem = $order->items->first();

                    $blockedDates = [];
                    $availableDates = [];
                    if($order->has_rental_items) {
                        foreach($order->items as $item) {
                            if($item->cloth && $item->cloth->availabilityBlocks) {
                                foreach($item->cloth->availabilityBlocks as $block) {
                                    $b = ['from' => \Carbon\Carbon::parse($block->start_date)->format('Y-m-d'), 'to' => \Carbon\Carbon::parse($block->end_date)->format('Y-m-d')];
                                    if($block->type == 'blocked') $blockedDates[] = $b;
                                    else if($block->type == 'available') $availableDates[] = $b;
                                }
                            }
                        }
                    }
                @endphp

                <div class="sale-card position-relative">
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
                        $visibleInvoices = collect();
                        if ($showMainInvoices) $visibleInvoices = $visibleInvoices->concat($mainInvoices);
                        if ($extInvoices->isNotEmpty()) $visibleInvoices = $visibleInvoices->concat($extInvoices);
                    @endphp
                    
                    @if($visibleInvoices->isNotEmpty())
                        <div class="dropdown position-absolute" style="top: 1.25rem; right: 1.25rem; z-index: 10;">
                            <button class="btn btn-light btn-sm dropdown-toggle rounded-pill fw-bold" type="button" data-toggle="dropdown" style="font-size: 0.75rem; padding: 0.35rem 0.75rem; background: #f8f9fa; border: 1px solid #e9ecef; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <i class="bi bi-file-earmark-pdf text-danger"></i> Invoices
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow border-0 rounded-3 p-2 mt-2" style="min-width: 160px;">
                                @foreach($visibleInvoices as $inv)
                                    <a class="dropdown-item rounded-2 py-2 d-flex align-items-center" href="{{ route('invoices.download', $inv->id) }}">
                                        <i class="bi bi-download me-2 text-primary"></i>
                                        <span class="small fw-medium">
                                            @if($inv->type == 'rent_sale') Tax Invoice
                                            @elseif($inv->type == 'platform_fee_buyer') Service Fee
                                            @else Invoice #{{ $inv->invoice_number }} @endif
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Product Image & Type -->
                    <div class="sale-image-group">
                        @if($firstItem && $firstItem->cloth && $firstItem->cloth->images->count() > 0)
                            <img src="{{ asset('storage/' . $firstItem->cloth->images->first()->image_path) }}" class="sale-image" alt="Product">
                        @else
                            <div class="sale-image bg-light d-flex align-items-center justify-content-center text-muted">
                                <i class="bi bi-image"></i>
                            </div>
                        @endif
                        <div class="item-count-badge">
                            @if($order->has_rental_items && $order->has_purchase_items) <i class="bi bi-collection"></i>
                            @elseif($order->has_rental_items) <i class="bi bi-calendar-event"></i>
                            @else <i class="bi bi-bag"></i> @endif
                        </div>

                        <!-- Mobile-only Dates & ORD ID below image -->
                        <div class="d-md-none mt-2 d-flex flex-column    ">
                            <div class="extra-small fw-800 text-dark mb-1">
                                ORD #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            </div>
                            @if($order->has_rental_items)
                                <div class="extra-small fw-800 text-primary">
                                    <i class="bi bi-calendar-range me-1"></i><br>{{ \Carbon\Carbon::parse($order->rental_from)->format('d M') }} - {{ \Carbon\Carbon::parse($order->rental_to)->format('d M') }}
                                </div>
                                <div class="extra-small fw-800 text-danger">
                                    <i class="bi bi-arrow-return-left me-1"></i><br>Return: {{ ($order->return_date ?: \Carbon\Carbon::parse($order->rental_to)->addDay())->format('d M') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Order Info -->
                    <div class="sale-info">
                        <div class="order-meta">
                            <span class="order-id d-none d-md-block">ORD #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <span class="status-badge {{ strtolower($order->status) == 'delivered' ? 'badge-approved' : (strtolower($order->status) == 'cancelled' ? 'badge-rejected' : 'badge-pending') }}">
                                {{ $order->status }}
                            </span>
                            <div class="ms-auto d-flex align-items-center gap-3">
                                <span class="text-muted extra-small">
                                    <i class="bi bi-clock me-1"></i> {{ $order->created_at->format('M d, Y') }}
                                </span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-7">
                                <div class="sale-items-list mb-3">
                                    <h5 class="sale-item-name mb-1">
                                        {{ $firstItem->cloth->title ?? 'Deleted Item' }}
                                        @if($order->items->count() > 1)
                                            <span class="text-muted small">& {{ $order->items->count() - 1 }} more items</span>
                                        @endif
                                    </h5>
                                    
                                    @if($order->has_rental_items)
                                        <div class="d-none d-md-flex align-items-center gap-3 mt-2 text-primary fw-bold small">
                                            <span><i class="bi bi-calendar-range me-1"></i> {{ \Carbon\Carbon::parse($order->rental_from)->format('d M') }} - {{ \Carbon\Carbon::parse($order->rental_to)->format('d M, Y') }}</span>
                                            <span class="text-muted fw-normal">|</span>
                                            <span class="text-danger"><i class="bi bi-arrow-return-left me-1"></i> Return: {{ ($order->return_date ?: \Carbon\Carbon::parse($order->rental_to)->addDay())->format('d M') }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Tracking Info -->
                                @if($order->shipments->isNotEmpty())
                                    <div class="row g-2">
                                        @foreach($order->shipments as $shipment)
                                            <div class="col-sm-6">
                                                <div class="tracking-pill">
                                                    <div>
                                                        <span class="tracking-label">{{ $shipment->type === 'reverse' ? 'Return Shipment' : 'Outgoing Shipment' }}</span>
                                                        <span class="tracking-info">AWB: {{ $shipment->waybill_number }}</span>
                                                    </div>
                                                    @if($shipment->tracking_url)
                                                        <a href="{{ $shipment->tracking_url }}" target="_blank" class="btn-track">TRACK</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted extra-small fw-bold mt-2">
                                        <i class="bi bi-hourglass-split me-1"></i> PREPARING FOR SHIPMENT...
                                    </div>
                                @endif
                            </div>

                            <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                                <div class="sale-amount-section mb-4 d-none d-md-block">
                                    <span class="label">Total Paid</span>
                                    <span class="value">₹{{ number_format($order->total_amount)}}</span>
                                    <div class="extra-small fw-bold mt-1 {{ $latestPayment ? 'text-success' : 'text-warning' }}">
                                        <i class="bi {{ $latestPayment ? 'bi-check-circle-fill' : 'bi-hourglass-split' }}"></i>
                                        {{ $latestPayment ? strtoupper($latestPayment->payment_method) : 'PENDING PAYMENT' }}
                                    </div>
                                </div>

                                <!-- Mobile Footer Row: Price Left, Extend Right -->
                                <div class="d-md-none mt-3 d-flex justify-content-between align-items-center pt-3 border-top">
                                    <div>
                                        <span class="text-muted extra-small fw-800 d-block mb-1">TOTAL PAID</span>
                                        <span class="fs-5 fw-800 text-dark">₹{{ number_format($order->total_amount + ($order->has_rental_items ? $order->security_amount : 0)) }}</span>
                                        @if($latestPayment)
                                            <div class="extra-small text-success fw-800">
                                                <i class="bi bi-check-circle-fill"></i> RAZORPAY
                                            </div>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        @if($order->has_rental_items && $order->status === 'Delivered')
                                            <button type="button" class="btn btn-premium px-3 py-2 rounded-pill fw-900 shadow-sm" style="font-size: 0.72rem;" data-toggle="modal" data-target="#extensionModal"
                                                data-order-id="{{ $order->id }}" 
                                                data-current-to="{{ ($order->return_date ?? \Carbon\Carbon::parse($order->rental_to)->addDay())->format('d M Y') }}"
                                                data-rental-to="{{ \Carbon\Carbon::parse($order->rental_to)->format('Y-m-d') }}"
                                                data-blocked-dates="{{ json_encode($blockedDates) }}"
                                                data-available-dates="{{ json_encode($availableDates) }}">
                                                <i class="bi bi-calendar-plus me-1"></i> EXTEND
                                            </button>
                                            @if($order->delivered_at && \Carbon\Carbon::parse($order->delivered_at)->addMinutes(2)->isFuture())
                                                <button type="button" class="btn btn-premium return-trigger px-3 py-2 rounded-pill fw-900 shadow-sm" style="font-size: 0.72rem;" data-toggle="modal" data-target="#returnModal" data-order-id="{{ $order->id }}">
                                                    <i class="bi bi-box-arrow-left me-1"></i> RETURN
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-premium early-return-trigger px-3 py-2 rounded-pill fw-900 shadow-sm" style="font-size: 0.72rem;" data-toggle="modal" data-target="#earlyReturnModal" data-order-id="{{ $order->id }}" data-max-date="{{ \Carbon\Carbon::parse($order->rental_to)->format('Y-m-d') }}">
                                                <i class="bi bi-clock-history me-1"></i> EARLY RETURN
                                            </button>
                                            <button type="button" class="btn btn-premium buy-rental-trigger px-3 py-2 rounded-pill fw-900 shadow-sm" style="font-size: 0.72rem;" data-order-id="{{ $order->id }}" data-cloth-id="{{ $firstItem->cloth_id ?? '' }}">
                                                <i class="bi bi-cart-check me-1"></i> BUY RENTAL
                                            </button>
                                        @endif

                                        @if($canRate || $order->status === 'Delivered')
                                            @if($hasRated)
                                                <button class="btn btn-outline-success btn-sale-action px-3" style="font-size: 0.72rem; height: 34px;" disabled>
                                                    <i class="bi bi-check-circle-fill me-1"></i> RATED
                                                </button>
                                            @elseif($canRate)
                                                <button type="button" class="btn btn-premium btn-sale-action px-3" style="font-size: 0.72rem; height: 34px;" data-toggle="modal" data-target="#rateModal" data-order-id="{{ $order->id }}">
                                                    <i class="bi bi-star me-1"></i> RATE ORDER
                                                </button>
                                            @endif
                                        @endif

                                        @if(in_array($order->status, ['Pending', 'Confirmed', 'Order Confirmed & Shipment Created']))
                                            <button type="button" class="btn btn-outline-danger px-3 py-2 rounded-pill fw-900 shadow-sm" style="font-size: 0.72rem;" data-toggle="modal" data-target="#cancelOrderModal" data-cancel-url="{{ route('orders.cancel', $order->id) }}">
                                                <i class="bi bi-x-circle me-1"></i> CANCEL
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="sale-actions justify-content-lg-end d-none d-md-flex">
                                    @if($order->has_rental_items && $order->status === 'Delivered')
                                         <button type="button" class="btn btn-premium btn-sale-action" data-toggle="modal" data-target="#extensionModal"
                                            data-order-id="{{ $order->id }}" 
                                            data-current-to="{{ ($order->return_date ?? \Carbon\Carbon::parse($order->rental_to)->addDay())->format('d M Y') }}"
                                            data-rental-to="{{ \Carbon\Carbon::parse($order->rental_to)->format('Y-m-d') }}"
                                            data-blocked-dates="{{ json_encode($blockedDates) }}"
                                            data-available-dates="{{ json_encode($availableDates) }}">
                                            <i class="bi bi-calendar-plus"></i> EXTEND
                                         </button>
                                         @if($order->delivered_at && \Carbon\Carbon::parse($order->delivered_at)->addMinutes(2)->isFuture())
                                             <button type="button" class="btn btn-premium btn-sale-action return-trigger" data-toggle="modal" data-target="#returnModal" data-order-id="{{ $order->id }}">
                                                <i class="bi bi-box-arrow-left"></i> RETURN
                                             </button>
                                         @endif
                                         <button type="button" class="btn btn-premium btn-sale-action early-return-trigger" data-toggle="modal" data-target="#earlyReturnModal" data-order-id="{{ $order->id }}" data-max-date="{{ \Carbon\Carbon::parse($order->rental_to)->format('Y-m-d') }}">
                                            <i class="bi bi-clock-history"></i> EARLY RETURN
                                         </button>
                                         <button type="button" class="btn btn-premium btn-sale-action buy-rental-trigger" data-order-id="{{ $order->id }}" data-cloth-id="{{ $firstItem->cloth_id ?? '' }}">
                                            <i class="bi bi-cart-check"></i> BUY RENTAL
                                         </button>
                                    @endif

                                    @if($canRate || $order->status === 'Delivered')
                                        @if($hasRated)
                                            <button class="btn btn-premium-success btn-sale-action opacity-75" disabled>
                                                <i class="bi bi-check-circle-fill"></i> RATED
                                            </button>
                                        @elseif($canRate)
                                            <button type="button" class="btn btn-premium btn-sale-action" data-toggle="modal" data-target="#rateModal" data-order-id="{{ $order->id }}">
                                                <i class="bi bi-star"></i> RATE ORDER
                                            </button>
                                        @endif
                                    @endif

                                    @if(in_array($order->status, ['Pending', 'Confirmed', 'Order Confirmed & Shipment Created']))
                                        <button type="button" class="btn btn-outline-danger btn-sale-action" data-toggle="modal" data-target="#cancelOrderModal" data-cancel-url="{{ route('orders.cancel', $order->id) }}">
                                            <i class="bi bi-x-circle"></i> CANCEL
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($orders->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-5 bg-white rounded-5 shadow-sm mt-4">
            <div class="mb-4">
                <i class="bi bi-bag-plus text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
            </div>
            <h3 class="fw-bold text-dark">Your bag is empty</h3>
            <p class="text-muted mb-4">Discover curated fashion and start your rental journey today!</p>
            <a href="{{ route('home') }}" class="btn btn-premium btn-lg px-5 py-3">
                Start Shopping
            </a>
        </div>
    @endif
</div>


<!-- All existing modals preserved below -->
<!-- Early Return Date Selection Modal -->
<div class="modal fade" id="earlyReturnModal" tabindex="-1" aria-labelledby="earlyReturnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="earlyReturnForm" action="" method="POST">
                @csrf
                <div class="modal-header border-0 align-items-center">
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-1">Schedule Early Return</h4>
                        <p class="text-muted small mb-0">Select the date you will return the item early.</p>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8;">&times;</button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="alert alert-warning rounded-4 border-0 p-3 small mb-4 d-flex gap-3 align-items-center">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <span>Please note that no refunds are issued for early returns as per our rental policy.</span>
                    </div>
                    
                    <div class="date-selection-box d-flex align-items-center mb-2">
                        <div class="me-3 text-primary d-flex align-items-center justify-content-center" style="min-width: 24px;">
                            <i class="bi bi-calendar-check fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="d-block text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-1">Return Date</span>
                            <input type="text" id="new_return_date" name="new_return_date" class="form-control border-0 p-0 fw-bold bg-transparent" placeholder="Choose a date" readonly required style="box-shadow: none; font-size: 0.9rem;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-3">
                    <button type="button" class="btn btn-light btn-premium-action px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-premium btn-premium-action flex-grow-1">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rate Modal -->
<div class="modal fade" id="rateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="rateForm" action="{{ route('ratings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="order_id" id="rating_order_id">
                <input type="hidden" name="rating" id="rating_value" required>
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="font-size: 1.1rem;">Rate Your Experience</h5>
                    <button type="button" class="btn-close ms-auto" data-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.2rem; color: #94a3b8;">&times;</button>
                </div>
                <div class="modal-body py-3 px-3">
                    <div class="p-2 bg-light rounded-3 mb-3 text-center border">
                        <span class="text-muted small d-block mb-0">Rating Order: <strong class="text-dark">ORD #<span id="display_order_id">--</span></strong></span>
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
                        <textarea name="comment" class="form-control border rounded-4 p-3" rows="4" placeholder="Share your thoughts about the items or service..." style="font-size: 0.85rem;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-3 pb-3 pt-0 d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-dismiss="modal" style="height: 38px; font-weight: 600; font-size: 0.85rem;">Close</button>
                    <button type="submit" class="btn btn-premium rounded-pill px-3" style="height: 38px; font-weight: 700; min-width: 120px; font-size: 0.85rem;">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 align-items-center">
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-1">Instant Return</h4>
                        <p class="text-muted small mb-0">Report issues and return immediately</p>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8;">&times;</button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="alert alert-warning rounded-4 border-0 p-3 small mb-4 d-flex gap-3 align-items-center">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <span>If you received damaged items or have concerns, please report it within 2 minutes of delivery for an instant return.</span>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold small text-uppercase letter-spacing-1">Issue Category</label>
                        <select name="return_reason" class="form-control bg-light border-0 rounded-3" style="box-shadow: none;" required>
                            <option value="">Select a reason</option>
                            <option value="Damaged Item">Damaged Item</option>
                            <option value="Wrong Size/Color">Wrong Size/Color</option>
                            <option value="Poor Condition">Poor Condition</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold small text-uppercase letter-spacing-1">Detailed Description</label>
                        <textarea name="return_details" class="form-control bg-light border-0 rounded-3" rows="4" placeholder="Please describe the issue in detail..." style="box-shadow: none;" required></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label fw-bold small text-uppercase letter-spacing-1">Upload Images (Optional)</label>
                        <input type="file" name="return_images[]" multiple class="form-control bg-light border-0 rounded-3" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-3">
                    <button type="button" class="btn btn-light btn-premium-action px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-premium btn-premium-action flex-grow-1" style="background: #ef4444; color: white;">Request Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Buy Rental Modal -->
<div class="modal fade" id="buyRentalModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 align-items-center">
                <div>
                    <h4 class="modal-title fw-bold text-dark mb-1">Buy Rental Outright</h4>
                    <p class="text-muted small mb-0">Keep this item forever instead of returning it.</p>
                </div>
                <button type="button" class="btn-close ms-auto" data-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8;">&times;</button>
            </div>
            <div class="modal-body px-4 py-4">
                <div id="buy_rental_loading" class="text-center py-4">
                    <div class="spinner-border text-success mb-3" role="status"></div>
                    <p class="text-muted small fw-medium">Checking eligibility and calculating quote...</p>
                </div>
                
                <div id="buy_rental_error" class="alert alert-danger d-none rounded-4 border-0 p-3 small mb-0"></div>

                <div id="buy_rental_quote_container" class="d-none">
                    <div class="alert alert-success rounded-4 border-0 p-3 small mb-4 d-flex gap-3 align-items-center">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <span>You can purchase this item outright! Your security deposit will be adjusted against the purchase price.</span>
                    </div>
                    
                    <div class="quote-receipt">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary-subtle border-dashed">
                            <h6 class="fw-bold small text-uppercase mb-0">Purchase Quote</h6>
                        </div>
                        
                        <div id="buy_rental_items" class="mb-3"></div>
                        
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary-subtle" style="border-top-style: dashed !important;">
                            <div>
                                <span class="d-block text-muted extra-small text-uppercase fw-bold">Amount Due</span>
                                <span class="fs-4 fw-bold text-dark">₹<span id="buy_rental_amount_due">0.00</span></span>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 extra-small">TAX INCLUDED</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-3">
                <button type="button" class="btn btn-light btn-premium-action px-4" data-dismiss="modal">Cancel</button>
                <button type="button" id="proceed_buy_rental" class="btn btn-premium-success btn-premium-action flex-grow-1 d-none">Proceed to Buy <i class="bi bi-arrow-right ms-2"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 align-items-center">
                <div>
                    <h4 class="modal-title fw-bold text-dark mb-1">Cancel Order</h4>
                    <p class="text-muted small mb-0">Are you sure you want to cancel this order?</p>
                </div>
                <button type="button" class="btn-close ms-auto" data-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8;">&times;</button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="alert alert-warning rounded-4 border-0 p-3 small mb-0 d-flex gap-3 align-items-center">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <span>A full refund will be initiated immediately to your original payment method.</span>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-3">
                <button type="button" class="btn btn-light btn-premium-action px-4" data-dismiss="modal">Keep Order</button>
                <form id="cancelOrderForm" action="" method="POST" class="m-0 flex-grow-1">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-premium-action w-100 fw-bold border-0" style="background: #ef4444; color: white;">Confirm Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Extension Modal Redesign -->
<div class="modal fade" id="extensionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 align-items-center">
                <div>
                    <h4 class="modal-title fw-bold text-dark mb-1">Extend Rental</h4>
                    <p class="text-muted small mb-0">Keep your favorite outfits a bit longer</p>
                </div>
                <button type="button" class="btn-close ms-auto" data-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8;">&times;</button>
            </div>
            
            <div class="modal-body px-4 py-4">
                <div class="row g-3 mb-4">
                    <!-- Current Status Card -->
                    <div class="col-md-6">
                        <div class="current-status-card d-flex align-items-center gap-3">
                            <div class="status-icon" style="min-width: 44px; height: 44px; background: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6; font-size: 1.1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-1">Current Return</span>
                                <span class="fw-bold text-dark" id="current_return_date" style="font-size: 0.9rem;">--</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date Selection -->
                    <div class="col-md-6">
                        <div class="date-selection-box d-flex align-items-center">
                            <div class="me-3 text-primary d-flex align-items-center justify-content-center" style="min-width: 24px;">
                                <i class="bi bi-calendar-plus fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <span class="d-block text-muted extra-small text-uppercase fw-bold letter-spacing-1 mb-1">New Return Date</span>
                                <input type="text" id="extension_date" class="form-control border-0 p-0 fw-bold bg-transparent" placeholder="Choose a date" readonly style="box-shadow: none; font-size: 0.9rem;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary (Receipt Style) -->
                <div id="quote_container" class="d-none">
                    <div class="quote-receipt">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary-subtle border-dashed">
                            <h6 class="fw-bold small text-uppercase mb-0">Extension Quote</h6>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3" id="days_badge">-- Days</span>
                        </div>
                        
                        <div id="quote_items" class="mb-3"></div>
                        
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary-subtle" style="border-top-style: dashed !important;">
                            <div>
                                <span class="d-block text-muted extra-small text-uppercase fw-bold">Total Additional Amount</span>
                                <span class="fs-4 fw-bold text-dark">₹<span id="total_extension_amount">0.00</span></span>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 extra-small">TAX INCLUDED</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Availability Alert -->
                <div id="availability_alert" class="alert alert-danger d-none rounded-4 border-0 p-3 mt-3">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <span class="small fw-medium">The selected dates are currently unavailable for some items in your order.</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-3">
                <button type="button" class="btn btn-light btn-premium-action px-4" data-dismiss="modal">Cancel</button>
                <button type="button" id="proceed_extension" class="btn btn-premium-pay btn-premium-action flex-grow-1" disabled>
                    Proceed to Pay <i class="bi bi-arrow-right ms-2"></i>
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
        let earlyReturnDatePicker = null;

        // Bootstrap 4 compatibility fix for data attributes
        $('.early-return-trigger').on('click', function() {
            const btn = $(this);
            const orderId = btn.data('order-id');
            const maxDate = btn.data('max-date');
            
            $('#earlyReturnForm').attr('action', `/orders/${orderId}/early-return`);
            
            if (earlyReturnDatePicker) {
                earlyReturnDatePicker.destroy();
            }

            earlyReturnDatePicker = flatpickr("#new_return_date", {
                minDate: "today",
                maxDate: maxDate,
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d M Y",
                disableMobile: "true"
            });
        });

        $('#rateModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            activeOrderId = button.data('order-id');
            $(this).find('#rating_order_id').val(activeOrderId);
            $(this).find('#display_order_id').text(String(activeOrderId).padStart(5, '0'));
            $(this).find('form')[0].reset();
            resetStars();
        });

        // AJAX Rating
        $('#rateForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Submitting...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#rateModal').modal('hide');
                        location.reload();
                    } else {
                        alert(res.message || 'Error submitting review.');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error submitting review. Please try again.';
                    alert(msg);
                },
                complete: function() {
                    btn.prop('disabled', false).text('Submit Review');
                }
            });
        });

        // Stars Logic
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

        function updateStars(v) {
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= v) {
                    s.classList.replace('bi-star', 'bi-star-fill');
                } else {
                    s.classList.replace('bi-star-fill', 'bi-star');
                }
            });
        }
        function resetStars() {
            stars.forEach(s => s.classList.replace('bi-star-fill', 'bi-star'));
            ratingInput.value = '';
        }

        // Return Modal
        $('#returnModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const orderId = button.data('order-id');
            $(this).find('form').attr('action', `/orders/${orderId}/return-request`);
        });

        // Extension logic (AJAX)
        let selectedOrderId = null;
        let selectedDays = null;
        let extensionDatePicker = null;
        let currentRentalToDate = null;

        $('#extensionModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            selectedOrderId = button.data('order-id');
            const currentReturnDate = button.data('current-to');
            const currentRentalTo = button.data('rental-to');
            
            $(this).find('#current_return_date').text(currentReturnDate);
            currentRentalToDate = new Date(currentRentalTo);
            currentRentalToDate.setHours(0,0,0,0);
            
            const minDate = new Date(currentRentalToDate);
            minDate.setDate(minDate.getDate() + 1);

            if (extensionDatePicker) extensionDatePicker.destroy();

            const blockedDates = button.data('blocked-dates') || [];
            const availableDates = button.data('available-dates') || [];

            const disableFunction = function(date) {
                if (availableDates.length > 0) {
                    let isAvailable = false;
                    for (let i = 0; i < availableDates.length; i++) {
                        let from = new Date(availableDates[i].from);
                        let to = new Date(availableDates[i].to);
                        from.setHours(0,0,0,0);
                        to.setHours(23,59,59,999);
                        if (date >= from && date <= to) {
                            isAvailable = true;
                            break;
                        }
                    }
                    if (!isAvailable) return true;
                }
                
                for (let i = 0; i < blockedDates.length; i++) {
                    let from = new Date(blockedDates[i].from);
                    let to = new Date(blockedDates[i].to);
                    from.setHours(0,0,0,0);
                    to.setHours(23,59,59,999);
                    if (date >= from && date <= to) {
                        return true;
                    }
                }
                
                return false;
            };

            extensionDatePicker = flatpickr("#extension_date", {
                minDate: minDate,
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d M Y",
                disableMobile: "true",
                disable: [disableFunction],
                onChange: function(dates, dateStr) {
                    if (dates.length > 0) {
                        const newDate = dates[0];
                        newDate.setHours(0,0,0,0);
                        const diff = Math.abs(newDate - currentRentalToDate);
                        selectedDays = Math.round(diff / (1000 * 60 * 60 * 24));
                        fetchQuote(selectedOrderId, selectedDays);
                    }
                }
            });

            $('#quote_container, #availability_alert').addClass('d-none');
            $('#proceed_extension').prop('disabled', true);
        });

        function fetchQuote(id, d) {
            $.get(`/orders/${id}/extension-quote`, { days: d }, function(res) {
                if(res.success) {
                    $('#quote_container').removeClass('d-none');
                    $('#total_extension_amount').text(res.quote.total_additional_amount.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    $('#days_badge').text(d + (d === 1 ? ' Day' : ' Days'));
                    
                    let html = '';
                    res.quote.items.forEach(i => {
                        html += `<div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted fw-medium">${i.cloth_title}</span>
                            <span class="fw-bold text-dark">₹${i.pricing.total_buyer_pay}</span>
                        </div>`;
                    });
                    $('#quote_items').html(html);

                    if(res.is_available) {
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
            }, function(res) {
                if(res.success) {
                    const opt = {
                        "key": res.key,
                        "amount": res.razorpay_order.amount,
                        "currency": "INR",
                        "name": "GetReady Rental",
                        "description": "Rental Extension",
                        "handler": function (p) { verifyExt(res.extension_id, p.razorpay_payment_id); },
                        "prefill": { "name": "{{ auth()->user()->name }}", "email": "{{ auth()->user()->email }}" },
                        "theme": { "color": "#0dcaf0" }
                    };
                    new Razorpay(opt).open();
                }
            }).always(() => btn.prop('disabled', false).html('Proceed to Pay <i class="bi bi-arrow-right ms-1"></i>'));
        });

        function verifyExt(id, pid) {
            $.post('/orders/extension/verify', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                extension_id: id,
                razorpay_payment_id: pid
            }, function(res) {
                if(res.success) location.reload();
            });
        }

        // Buy Rental Logic
        let activeBuyOrderId = null;
        let activeBuyClothId = null;
        let activeBuyOrderItemId = null;

        $('.buy-rental-trigger').on('click', function() {
            const btn = $(this);
            activeBuyOrderId = btn.data('order-id');
            activeBuyClothId = btn.data('cloth-id');

            $('#buy_rental_loading').removeClass('d-none');
            $('#buy_rental_error, #buy_rental_quote_container, #proceed_buy_rental').addClass('d-none');
            $('#buyRentalModal').modal('show');

            $.get(`/orders/${activeBuyOrderId}/purchase-eligibility`, { cloth_id: activeBuyClothId }, function(res) {
                $('#buy_rental_loading').addClass('d-none');
                if(res.success && res.is_eligible) {
                    $('#buy_rental_quote_container, #proceed_buy_rental').removeClass('d-none');
                    const quote = res.conversion_quote;
                    $('#buy_rental_amount_due').text(quote.amount_due.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    
                    let html = `
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted fw-medium">
                                Total Purchase Value 
                                <i class="bi bi-info-circle ms-1 text-primary" style="cursor: pointer;" onclick="$('#price_distribution').slideToggle('fast')" title="Click to see breakdown"></i>
                            </span>
                            <span class="fw-bold text-dark">₹${quote.total_purchase_value.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
                        </div>
                        <div id="price_distribution" class="bg-white rounded-3 p-2 mb-2 border border-secondary-subtle" style="display: none; font-size: 0.75rem;">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Item Base Price</span>
                                <span>₹${quote.pricing_breakdown.base_price.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Platform Fee</span>
                                <span>₹${quote.pricing_breakdown.buyer_comm.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Item Tax (18%)</span>
                                <span>₹${quote.pricing_breakdown.item_tax_fee.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Platform Fee GST (18%)</span>
                                <span>₹${quote.pricing_breakdown.buyer_comm_gst.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted fw-medium">Rent Already Paid</span>
                            <span class="fw-bold text-success">- ₹${quote.paid_rent.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted fw-medium">Security Deposit Held</span>
                            <span class="fw-bold text-success">- ₹${quote.security_deposit.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
                        </div>
                    `;
                    $('#buy_rental_items').html(html);
                }
            }).fail(function(xhr) {
                $('#buy_rental_loading').addClass('d-none');
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Item not eligible for purchase.';
                $('#buy_rental_error').text(msg).removeClass('d-none');
            });
        });

        $('#proceed_buy_rental').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

            $.post(`/orders/${activeBuyOrderId}/convert-to-purchase`, {
                _token: $('meta[name="csrf-token"]').attr('content'),
                cloth_id: activeBuyClothId
            }, function(res) {
                if(res.success) {
                    activeBuyOrderItemId = res.order_item_id;
                    if(!res.requires_payment) {
                        verifyBuy(activeBuyOrderItemId, null);
                        return;
                    }
                    if(!res.key || res.key.includes('dummy')) {
                        if(confirm("Test mode: Simulate successful payment?")) {
                            verifyBuy(activeBuyOrderItemId, 'mock_' + Date.now());
                        } else {
                            btn.prop('disabled', false).html('Proceed to Buy');
                        }
                        return;
                    }

                    const opt = {
                        "key": res.key,
                        "amount": res.razorpay_order.amount,
                        "currency": "INR",
                        "name": "GetReady Rental",
                        "description": "Buy Rental Outright",
                        "handler": function (p) { verifyBuy(activeBuyOrderItemId, p.razorpay_payment_id); },
                        "prefill": { "name": "{{ auth()->user()->name }}", "email": "{{ auth()->user()->email }}" },
                        "theme": { "color": "#198754" }
                    };
                    const rzp = new Razorpay(opt);
                    rzp.on('payment.failed', function (response){
                        btn.prop('disabled', false).html('Proceed to Buy');
                    });
                    rzp.open();
                } else {
                    alert(res.message || 'Error initiating purchase.');
                    btn.prop('disabled', false).html('Proceed to Buy');
                }
            }).fail(function(xhr) {
                alert('Error initiating purchase. Please try again.');
                btn.prop('disabled', false).html('Proceed to Buy');
            });
        });

        function verifyBuy(orderItemId, paymentId) {
            $.post('/orders/conversion/verify', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                order_item_id: orderItemId,
                razorpay_payment_id: paymentId
            }, function(res) {
                if(res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'Error verifying purchase.');
                    $('#proceed_buy_rental').prop('disabled', false).html('Proceed to Buy');
                }
            }).fail(function() {
                alert('Server error verifying purchase.');
                $('#proceed_buy_rental').prop('disabled', false).html('Proceed to Buy');
            });
        }

        // Set action for cancel modal
        $('#cancelOrderModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var url = button.data('cancel-url');
            var form = $(this).find('#cancelOrderForm');
            form.attr('action', url);
        });
    });
</script>
@endsection
