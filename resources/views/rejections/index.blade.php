@extends('layouts.app')

@section('title', 'Rejected Items Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/listed-clothes.css') }}">
<style>
    :root {
        --rejection-red: #ef4444;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    .rejections-header {
        background: white;
        padding: 1.5rem 0;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .rejections-header {
            padding: 1rem 0;
            margin-bottom: 1rem;
        }
        .rejections-header h1 {
            font-size: 1.5rem !important;
        }
        .rejections-header p {
            font-size: 0.75rem !important;
        }
    }

    .rejection-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        padding: 1rem;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .rejection-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-shadow);
        border-color: #e2e8f0;
    }

    .item-thumb {
        width: 80px;
        height: 110px;
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .badge-premium {
        font-size: 0.6rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 4px 10px;
        border-radius: 50px;
        display: inline-block;
    }

    .badge-rejected {
        background: #fef2f2;
        color: #ef4444;
        border: 1px solid #fee2e2;
    }

    .badge-pending {
        background: #fffbeb;
        color: #f59e0b;
        border: 1px solid #fef3c7;
    }

    .reason-banner {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        border-left: 4px solid var(--rejection-red);
        font-size: 0.9rem;
        color: var(--text-dark);
        line-height: 1.4;
    }

    .btn-fix {
        background: linear-gradient(135deg, var(--premium-gold) 0%, #FF7F50 100%);
        color: white !important;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(255, 165, 0, 0.2);
    }

    .btn-fix:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 15px rgba(255, 165, 0, 0.3);
    }

    .empty-container {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 24px;
        box-shadow: var(--card-shadow);
    }

    .pricing-info {
        font-size: 0.72rem;
        color: var(--text-muted);
        background: #f1f5f9;
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 600;
    }
    .status-pulse {
        animation: pulse-animation 2s infinite;
    }

    @keyframes pulse-animation {
        0% { transform: scale(0.95); opacity: 0.7; }
        50% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.7; }
    }
</style>
@endsection

@section('content')
<div class="rejections-header">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="fw-bold text-dark mb-1">Manage Rejections</h2>
                <p class="text-muted mb-0 small">Review and address feedback from our moderation team.</p>
            </div>
            <div class="text-md-end">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">
                    <i class="bi bi-house-door me-2"></i> BACK TO HOME
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container pt-0 pb-5">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-alert="alert"></button>
        </div>
    @endif

    @php
        $totalRejected = $rejectedClothes->where('is_approved', -1)->count();
        $totalResubmitted = $rejectedClothes->where('is_approved', 0)->count();
    @endphp

    <!-- Stats Grid -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon icon-earnings" style="background: #fef2f2; color: #ef4444;">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-info">
                <p>Action Required</p>
                <h3>{{ $totalRejected }}</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-orders" style="background: #fffbeb; color: #f59e0b;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-info">
                <p>Under Review</p>
                <h3>{{ $totalResubmitted }}</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-transit" style="background: #f0fdf4; color: #10b981;">
                <i class="bi bi-check-all"></i>
            </div>
            <div class="stat-info">
                <p>Total Items</p>
                <h3>{{ $rejectedClothes->count() }}</h3>
            </div>
        </div>
    </div>

    @if($rejectedClothes->count() > 0)
        <div class="orders-container">
            @foreach($rejectedClothes as $cloth)
                <div class="sale-card">
                    <!-- Product Image -->
                    <div class="sale-image-group">
                        <img src="{{ $cloth->images->count() ? asset('storage/' . $cloth->images->first()->image_path) : asset('images/placeholder.jpg') }}" 
                             class="sale-image" alt="{{ $cloth->title }}">
                        @if($cloth->is_approved == -1)
                        <div class="item-count-badge">
                             <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        @endif
                        
                        <!-- Mobile-only Details -->
                        <div class="d-md-none mt-2">
                            <div class="extra-small fw-800 text-dark mb-1">ITEM #{{ str_pad($cloth->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="extra-small fw-800 {{ $cloth->is_approved == -1 ? 'text-danger' : 'text-warning' }}">
                                {{ $cloth->is_approved == -1 ? 'REJECTED' : 'RESUBMITTED' }}
                            </div>
                        </div>
                    </div>

                    <!-- Item Info -->
                    <div class="sale-info">
                        <div class="order-meta mb-3">
                            <span class="order-id d-none d-md-block">ITEM #{{ str_pad($cloth->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <span class="status-badge {{ $cloth->is_approved == -1 ? 'badge-rejected' : 'badge-pending' }}">
                                {{ $cloth->is_approved == -1 ? 'Rejected' : 'Resubmitted' }}
                            </span>
                            <span class="text-muted extra-small ms-auto">
                                <i class="bi bi-clock me-1"></i> {{ $cloth->updated_at->format('M d, Y') }}
                            </span>
                        </div>

                        <div class="row">
                            <div class="col-lg-7">
                                <div class="sale-items-list mb-3">
                                    <h5 class="sale-item-name mb-1">{{ $cloth->title }}</h5>
                                    <div class="d-flex align-items-center gap-2 text-muted extra-small fw-bold">
                                        <span>{{ $cloth->category->name ?? 'N/A' }}</span>
                                        <span>•</span>
                                        <span>{{ $cloth->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <!-- Issue Banner (Styled like Tracking Pill) -->
                                @if($cloth->is_approved == -1)
                                    <div class="tracking-pill" style="background: #fff5f5; border: 1px dashed #feb2b2;">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-exclamation-circle text-danger mr-3 mt-1" style="font-size: 1.1rem;"></i>
                                            <div>
                                                <span class="tracking-label text-danger">Issue Reported by Moderation</span>
                                                <span class="tracking-info text-dark" style="font-size: 0.8rem;">{{ $cloth->latest_rejection_reason }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="tracking-pill" style="background: #f8fafc; border: 1px dashed #e2e8f0;">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-clock-history text-warning mr-3 status-pulse" style="font-size: 1.1rem;"></i>
                                            <div>
                                                <span class="tracking-label">Awaiting Admin Review</span>
                                                <span class="tracking-info text-muted">We will notify you once approved</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                                <div class="sale-amount-section d-none d-md-block">
                                    <span class="label">Rental Price</span>
                                    <span class="value" style="color: var(--premium-gold);">₹{{ number_format($cloth->rent_price) }}</span>
                                </div>

                                <div class="d-flex gap-2 justify-content-lg-end justify-content-center mt-3">
                                    @if($cloth->is_approved == -1)
                                        <a href="{{ route('rejections.show', $cloth->id) }}" class="btn btn-sale-action px-4">
                                            <i class="bi bi-magic me-2"></i> FIX NOW
                                        </a>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sale-action px-4" disabled>
                                            <i class="bi bi-hourglass me-2"></i> PENDING
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-container">
            <div class="mb-4">
                <i class="bi bi-check2-circle text-success" style="font-size: 4rem;"></i>
            </div>
            <h2 class="fw-bold text-dark mb-2">No Rejected Items</h2>
            <p class="text-muted mx-auto" style="max-width: 450px;">
                Great job! You don't have any items requiring action right now. All your listings are either approved or in the review queue.
            </p>
            <a href="{{ route('listed.clothes') }}" class="btn btn-outline-dark rounded-pill px-4 mt-3">View My Listings</a>
        </div>
    @endif
</div>
@endsection

