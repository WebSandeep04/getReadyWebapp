@extends('layouts.app')

@section('title', 'Rejected Items Management')

@section('styles')
<style>
    .rejection-card {
        border-left: 4px solid #dc3545;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .rejection-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    
    .rejection-badge {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        border-radius: 20px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 1rem;
        background: #f8f9fa;
        border-radius: 12px;
        margin-top: 2rem;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #adb5bd;
        display: block;
    }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <div>
                    <h2 class="text-danger mb-1 fw-bold">
                        <i class="bi bi-exclamation-octagon me-2"></i>Rejected Items
                    </h2>
                    <p class="text-muted mb-0">Items requiring your attention</p>
                </div>
                <!-- <a href="{{ route('listed.clothes') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back to My Items
                </a> -->
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(isset($rejectedClothes) && $rejectedClothes->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-3 py-3" style="width: 80px;">Image</th>
                                        <th class="py-3">Details</th>
                                        <th class="py-3">Category</th>
                                        <th class="py-3">Pricing</th>
                                        <th class="py-3">Status</th>
                                        <th class="text-end pe-3 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rejectedClothes as $cloth)
                                        <tr style="vertical-align: middle;">
                                            <td class="ps-3 py-2">
                                                <div style="width: 50px; height: 50px; border-radius: 6px; overflow: hidden; border: 1px solid #eee;">
                                                    @if($cloth->images->count() > 0)
                                                        <img src="{{ asset('storage/' . $cloth->images->first()->image_path) }}" 
                                                             alt="{{ $cloth->title }}" 
                                                             style="width: 100%; height: 100%; object-fit: cover;">
                                                    @else
                                                        <img src="{{ asset('images/placeholder.jpg') }}" 
                                                             alt="{{ $cloth->title }}" 
                                                             style="width: 100%; height: 100%; object-fit: cover;">
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-2">
                                                <h6 class="mb-0 fw-bold text-dark">{{ $cloth->title }}</h6>
                                                <small class="text-muted" style="font-size: 0.75rem;">ID: #{{ $cloth->id }}</small>
                                            </td>
                                            <td class="py-2">
                                                <span class="text-dark">{{ $cloth->categoryRef->name ?? 'Unknown' }}</span>
                                            </td>
                                            <td class="py-2">
                                                <div style="line-height: 1.2;">
                                                    <div class="small text-nowrap">Rent: <span class="fw-semibold text-success">₹{{ number_format($cloth->rent_price) }}</span></div>
                                                    <div class="small text-nowrap text-muted">Dep: ₹{{ number_format($cloth->security_deposit) }}</div>
                                                </div>
                                            </td>
                                            <td class="py-2">
                                                <span class="badge bg-danger text-white rounded-pill px-3">
                                                    Rejected
                                                </span>
                                            </td>
                                            <td class="text-end pe-3 py-2">
                                                <a href="{{ route('rejections.show', $cloth->id) }}" 
                                                   class="btn btn-danger btn-sm text-white shadow-sm" 
                                                   data-bs-toggle="tooltip" title="Fix & Resubmit">
                                                    <i class="bi bi-wrench-adjustable"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div class="mb-3 text-muted opacity-25">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h4 class="fw-bold text-dark">No Rejected Items</h4>
                    <p class="text-muted mb-4">Great job! All your listings are approved or pending.</p>
                    <!-- <a href="{{ route('sell') }}" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-plus-lg me-1"></i>List New Item
                    </a> -->
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
