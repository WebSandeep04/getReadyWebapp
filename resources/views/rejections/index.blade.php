@extends('layouts.app')

@section('title', 'Rejected Items Management')

@push('styles')
<style>
    .rejection-card {
        border-left: 4px solid #dc3545;
        transition: all 0.3s ease;
    }
    
    .rejection-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .rejection-badge {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
    }
    
    .item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #dee2e6;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #dee2e6;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-danger mb-1">
                        <i class="bi bi-x-circle me-2"></i>Rejected Items
                    </h2>
                    <p class="text-muted mb-0">Manage and resubmit your rejected items for approval</p>
                </div>
                <a href="{{ route('listed.clothes') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to My Items
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($rejectedClothes->count() > 0)
                <div class="row">
                    @foreach($rejectedClothes as $cloth)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card rejection-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="flex-shrink-0 me-3">
                                            @if($cloth->images->count() > 0)
                                                <img src="{{ asset('storage/' . $cloth->images->first()->image_path) }}" 
                                                     alt="{{ $cloth->title }}" class="item-image">
                                            @else
                                                <img src="{{ asset('images/1.jpg') }}" 
                                                     alt="{{ $cloth->title }}" class="item-image">
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1">{{ $cloth->title }}</h6>
                                            <p class="text-muted small mb-2">{{ $cloth->category }}</p>
                                            <span class="rejection-badge">
                                                <i class="bi bi-x-circle me-1"></i>Rejected
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="row text-center mb-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Rent Price</small>
                                            <strong class="text-success">₹{{ number_format($cloth->rent_price) }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Security</small>
                                            <strong class="text-info">₹{{ number_format($cloth->security_deposit) }}</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <a href="{{ route('rejections.show', $cloth->id) }}" 
                                           class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-eye me-1"></i>View Details & Fix
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-check-circle"></i>
                    <h4>No Rejected Items</h4>
                    @if(session('success'))
                        <p class="text-success mb-3">{{ session('success') }}</p>
                    @endif
                    <p class="text-muted">Great! You don't have any rejected items at the moment.</p>
                    <a href="{{ route('sell') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Add New Item
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
