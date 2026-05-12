@extends('layouts.app')

@section('title', 'My Listed Clothes')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/listed-clothes.css') }}">
@endsection

@section('content')
<div class="container py-2">
    <!-- Management Header -->
    <div class="management-header">
        <div class="management-title">
            <h2>My Listed Clothes</h2>
            <p>Manage, track, and optimize your fashion collection</p>
        </div>
        <div class="management-actions">
            <a href="{{ route('sell') }}" class="btn btn-premium px-4 py-2">
                <i class="bi bi-plus-lg me-2"></i> LIST NEW ITEM
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($clothes->count() > 0)
        <div class="listings-container">
            @foreach($clothes as $cloth)
                <div class="listing-card">
                    <!-- Image Wrapper -->
                    <div class="listing-image-wrapper">
                        @if($cloth->images->count() > 0)
                            <img src="{{ asset('storage/' . $cloth->images->first()->image_path) }}" alt="{{ $cloth->title }}">
                        @else
                            <div class="bg-light w-100 h-100 d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted fs-2"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Content Section -->
                    <div class="listing-content">
                        <div class="listing-main-info">
                            <div class="listing-header-mobile mb-2">
                                <h4 class="listing-title-mobile mb-0">{{ \Illuminate\Support\Str::words($cloth->title, 6, '...') }}</h4>
                            </div>
                            
                            <div class="listing-meta">
                                <span class="meta-pill">{{ $cloth->category->name ?? 'Category' }}</span>
                                <span class="meta-pill">{{ $cloth->gender }}</span>
                                <span class="meta-pill">Size: {{ $cloth->size->name ?? '?' }}</span>
                                <span class="meta-pill">{{ $cloth->condition->name ?? 'Condition' }}</span>
                            </div>
                        </div>

                        <div class="listing-footer mt-0 mt-md-3 d-flex justify-content-between align-items-end">
                            <div class="listing-id text-muted extra-small">
                                <i class="bi bi-hash"></i> SKU: {{ $cloth->id }} | Updated: {{ $cloth->updated_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Breakdown -->
                    <div class="pricing-info">
                        <div class="price-row">
                            <span class="text-muted small">Rent Entered</span>
                            <span class="fw-bold">₹{{ number_format($cloth->rent_price, 0) }}</span>
                        </div>
                        <div class="price-row">
                            <span class="text-muted small">Platform Fee (20%)</span>
                            <span class="text-danger">-₹{{ number_format($cloth->rent_price * 0.20, 0) }}</span>
                        </div>
                        <div class="price-row final">
                            <span class="small">Your Earnings</span>
                            <span>₹{{ number_format($cloth->seller_rent, 0) }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="action-stack">
                        <!-- Status Icon (Top of Stack) -->
                        <div class="status-icon-wrapper mb-1">
                            @if($cloth->is_approved == 1)
                                <div class="status-icon approved" title="Approved">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                            @elseif($cloth->is_approved == -1)
                                <div class="status-icon rejected" title="Rejected">
                                    <i class="bi bi-x-circle-fill"></i>
                                </div>
                            @elseif($cloth->is_approved === null && $cloth->resubmission_count > 0)
                                <div class="status-icon resubmitted" title="Resubmitted">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </div>
                            @else
                                <div class="status-icon pending" title="Pending Approval">
                                    <i class="bi bi-clock"></i>
                                </div>
                            @endif
                        </div>

                        <a href="{{ route('clothes.show', $cloth->id) }}" class="btn-action btn-view" title="View Listing">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('listed.clothes.edit', $cloth->id) }}" class="btn-action btn-edit" title="Edit Listing">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('listed.clothes.destroy', $cloth->id) }}" method="POST" onsubmit="return confirm('Delete this listing?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" title="Delete Listing">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5 bg-white rounded-5 shadow-sm">
            <div class="mb-4">
                <i class="bi bi-bag-plus text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
            </div>
            <h3 class="fw-bold text-dark">No clothes listed yet</h3>
            <p class="text-muted mb-4">Start your fashion earning journey today!</p>
            <a href="{{ route('sell') }}" class="btn btn-premium btn-lg px-5 py-3">
                List Your First Item
            </a>
        </div>
    @endif
</div>
@endsection
