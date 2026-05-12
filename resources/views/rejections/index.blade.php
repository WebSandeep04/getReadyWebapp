@extends('layouts.app')

@section('title', 'Rejected Items Management')

@section('styles')
<style>
    :root {
        --premium-gold: #FFA500;
        --rejection-red: #ef4444;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    body {
        background-color: #f8fafc;
        font-family: 'Outfit', sans-serif;
    }

    .rejections-header {
        background: white;
        padding: 2.5rem 0;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 2rem;
    }

    .rejection-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        padding: 1.25rem;
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
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 5px 12px;
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
        font-size: 0.8rem;
        color: var(--text-muted);
        background: #f1f5f9;
        padding: 2px 10px;
        border-radius: 6px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="rejections-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold text-dark mb-1">Manage Rejections</h1>
                <p class="text-muted mb-0 small">Review and address feedback from our moderation team.</p>
            </div>
            <div class="text-end">
                <span class="badge bg-danger text-white rounded-pill px-3 py-2 fw-bold small">
                    {{ $rejectedClothes->count() }} ACTION REQUIRED
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($rejectedClothes->count() > 0)
        <div class="row g-3">
            @foreach($rejectedClothes as $cloth)
                <div class="col-12">
                    <div class="rejection-card">
                        <div class="row align-items-center g-3">
                            <!-- Image Thumb -->
                            <div class="col-auto">
                                <img src="{{ $cloth->images->count() ? asset('storage/' . $cloth->images->first()->image_path) : asset('images/placeholder.jpg') }}" 
                                     class="item-thumb" alt="{{ $cloth->title }}">
                            </div>

                            <!-- Details Column -->
                            <div class="col-md-3">
                                <div class="mb-1">
                                    @if($cloth->is_approved == -1)
                                        <span class="badge-premium badge-rejected">Rejected</span>
                                    @else
                                        <span class="badge-premium badge-pending">Resubmitted</span>
                                    @endif
                                </div>
                                <h6 class="fw-bold text-dark mb-1">{{ $cloth->title }}</h6>
                                <div class="small text-muted mb-2">{{ $cloth->category->name ?? 'N/A' }} • ID: #{{ $cloth->id }}</div>
                                <div class="d-flex gap-2">
                                    <span class="pricing-info">Rent: ₹{{ number_format($cloth->rent_price) }}</span>
                                </div>
                            </div>

                            <!-- Reason Column -->
                            <div class="col-md">
                                @if($cloth->is_approved == -1)
                                    <div class="reason-banner">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-info-circle-fill text-danger mt-1"></i>
                                            <div>
                                                <div class="fw-bold small text-danger text-uppercase letter-spacing-1 mb-1">Issue Found:</div>
                                                <div class="text-dark">{{ $cloth->latest_rejection_reason }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-2">
                                        <div class="text-muted small"><i class="bi bi-hourglass-split me-1"></i> Awaiting re-review by admin</div>
                                        <div class="text-muted extra-small">Last updated on {{ $cloth->updated_at->format('d M, Y') }}</div>
                                    </div>
                                @endif
                            </div>

                            <!-- Action Column -->
                            <div class="col-md-auto text-end">
                                @if($cloth->is_approved == -1)
                                    <a href="{{ route('rejections.show', $cloth->id) }}" class="btn-fix text-decoration-none">
                                        <i class="bi bi-magic me-1"></i> FIX NOW
                                    </a>
                                @else
                                    <div class="text-muted small px-3">
                                        {{ $cloth->updated_at->diffForHumans() }}
                                    </div>
                                @endif
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
ion
