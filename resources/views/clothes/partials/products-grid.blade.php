@forelse($clothes as $cloth)
    <div class="product-card">
        <a href="{{ route('clothes.show', $cloth->id) }}" class="product-link">
            <div class="product-image-wrapper">
                <div class="product-image">
                    @if($cloth->images->count() > 0)
                        <img src="{{ asset('storage/' . $cloth->images->first()->image_path) }}" 
                             alt="{{ $cloth->title }}" 
                             class="img-fluid product-img main-img">
                        @if($cloth->images->count() > 1)
                            <img src="{{ asset('storage/' . $cloth->images[1]->image_path) }}" 
                                 alt="{{ $cloth->title }}" 
                                 class="img-fluid product-img hover-img">
                        @endif
                    @else
                        <img src="{{ asset('images/1.jpg') }}" 
                             alt="{{ $cloth->title }}" 
                             class="img-fluid product-img">
                    @endif
                </div>
                @if($cloth->is_purchased)
                    <span class="badge badge-buy" title="Available for Buy">
                        <i class="bi bi-handbag"></i>
                    </span>
                @endif
            </div>
            <div class="product-info">
                <h6 class="product-title">{{ $cloth->title }}</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    @if($cloth->brand)
                        <p class="product-brand mb-0">{{ $cloth->brand->name ?? 'Unknown' }}</p>
                    @else
                        <span></span>
                    @endif
                    
                    @if($cloth->user && $cloth->user->average_rating > 0)
                        <span class="badge badge-light border text-warning" title="Seller Rating">
                            <i class="bi bi-star-fill text-warning"></i> {{ $cloth->user->average_rating }}
                        </span>
                    @endif
                </div>
                <div class="product-pricing">
                    <div class="price-row">
                        <span class="price-label"><i class="bi bi-calendar2-check me-1"></i> RENT:</span>
                        <span class="rent-price">₹{{ number_format($cloth->display_rent_price) }}</span>
                    </div>
                    @if($cloth->is_purchased && $cloth->selling_price)
                        <div class="price-row">
                            <span class="price-label"><i class="bi bi-handbag me-1"></i> BUY:</span>
                            <span class="buy-price">₹{{ number_format($cloth->selling_price) }}</span>
                        </div>
                    @endif
                    @if($cloth->mrp)
                        <div class="price-row">
                            <span class="price-label"><i class="bi bi-bookmark-star me-1"></i> MRP:</span>
                            <span class="text-muted small"><del>₹{{ number_format($cloth->mrp) }}</del></span>
                        </div>
                    @endif
                </div>
            </div>
        </a>
    </div>
@empty
    <div class="no-products text-center py-5 w-100">
        <div class="mb-4">
            <i class="bi bi-search text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
        </div>
        <h4 class="text-muted fw-bold">No products found</h4>
        <p class="text-muted mb-4">We couldn't find any clothes matching your selected dates or filters.</p>
    </div>
@endforelse

