@extends('layouts.app')

@section('title', 'Browse Products - Get Ready')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/browse.css') }}">
<style>
    .date-picker { background-color: #fff !important; cursor: pointer; }
    .filter-group-spacing { margin-top: 1.5rem; }
    /* Mobile date filter styles */
    @media (max-width: 768px) {
        .header-date-filter {
            display: flex !important;
            width: 100%;
            margin-top: 10px;
        }
        .header-date-filter .input-group {
            flex: 1;
        }
    }
</style>
@endsection

@section('content')
<div class="browse-container">
    <div class="container-fluid">
        <div class="row">
            <!-- Left Sidebar - Filters -->
            <div class="col-md-3 col-lg-2 sidebar-filters">
                <div class="filter-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="filter-title mb-0">Filter by</h5>
                        <a href="{{ route('clothes.index') }}" class="text-warning small text-decoration-none">Clear all</a>
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="filter-group">
                        <h6 class="filter-group-title">Category</h6>
                        <form id="filterForm" method="GET" action="{{ route('clothes.index') }}">
                            @foreach($categories as $category)
                                <div class="filter-item">
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                                               {{ in_array($category->id, (array)request('categories', [])) ? 'checked' : '' }}>
                                        <span class="checkmark"></span>
                                        <span class="filter-label">{{ $category->name }}</span>
                                    </label>
                                </div>
                            @endforeach

                            <!-- Gender Filter -->
                            <h6 class="filter-group-title filter-group-spacing">User Type</h6>
                            @foreach($genders as $gender)
                                <div class="filter-item">
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="genders[]" value="{{ $gender }}" 
                                               {{ in_array($gender, (array)request('genders', [])) ? 'checked' : '' }}>
                                        <span class="checkmark"></span>
                                        <span class="filter-label">{{ $gender }}</span>
                                    </label>
                                </div>
                            @endforeach

                            <!-- Status Filter -->
                            <h6 class="filter-group-title filter-group-spacing">Status</h6>
                            <div class="filter-item">
                                <label class="filter-radio">
                                    <input type="radio" name="status" value="any" 
                                           {{ request('status', 'any') === 'any' ? 'checked' : '' }}>
                                    <span class="radio-mark"></span>
                                    <span class="filter-label">Any</span>
                                </label>
                            </div>
                            <div class="filter-item">
                                <label class="filter-radio">
                                    <input type="radio" name="status" value="available" 
                                           {{ request('status') === 'available' ? 'checked' : '' }}>
                                    <span class="radio-mark"></span>
                                    <span class="filter-label">Available</span>
                                </label>
                            </div>
                            <div class="filter-item">
                                <label class="filter-radio">
                                    <input type="radio" name="status" value="sold" 
                                           {{ request('status') === 'sold' ? 'checked' : '' }}>
                                    <span class="radio-mark"></span>
                                    <span class="filter-label">Sold</span>
                                </label>
                            </div>

                            <!-- Condition Filter -->
                            <h6 class="filter-group-title filter-group-spacing">Condition</h6>
                            @foreach($conditions as $condition)
                                <div class="filter-item">
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="conditions[]" value="{{ $condition->id }}" 
                                               {{ in_array($condition->id, (array)request('conditions', [])) ? 'checked' : '' }}>
                                        <span class="checkmark"></span>
                                        <span class="filter-label">{{ $condition->name }}</span>
                                    </label>
                                </div>
                            @endforeach

                            <!-- Seller Rating Filter -->
                            <!-- Seller Rating Filter -->
                            <h6 class="filter-group-title filter-group-spacing">Seller Rating</h6>
                            <div class="filter-item">
                                <div class="star-rating-filter" data-input="seller_rating">
                                    <input type="hidden" name="seller_rating" id="seller_rating" value="{{ request('seller_rating') }}">
                                    @php $currentSellerRating = request('seller_rating', 0); @endphp
                                    @for($i=1; $i<=5; $i++)
                                        <i class="bi bi-star{{ $i <= $currentSellerRating ? '-fill text-warning' : '' }} star-filter-icon" data-rating="{{ $i }}" style="cursor:pointer; font-size:1.2rem; color: #ccc;"></i>
                                    @endfor
                                    <span class="ms-2 small text-muted clear-rating" style="cursor:pointer; display:{{ $currentSellerRating ? 'inline' : 'none' }};" data-input="seller_rating">Clear</span>
                                </div>
                            </div>

                            <!-- Product Rating Filter -->
                            <h6 class="filter-group-title filter-group-spacing">Product Rating</h6>
                            <div class="filter-item">
                                <div class="star-rating-filter" data-input="product_rating">
                                    <input type="hidden" name="product_rating" id="product_rating" value="{{ request('product_rating') }}">
                                    @php $currentProductRating = request('product_rating', 0); @endphp
                                    @for($i=1; $i<=5; $i++)
                                        <i class="bi bi-star{{ $i <= $currentProductRating ? '-fill text-warning' : '' }} star-filter-icon" data-rating="{{ $i }}" style="cursor:pointer; font-size:1.2rem; color: #ccc;"></i>
                                    @endfor
                                    <span class="ms-2 small text-muted clear-rating" style="cursor:pointer; display:{{ $currentProductRating ? 'inline' : 'none' }};" data-input="product_rating">Clear</span>
                                </div>
                            </div>

                            <!-- MRP Range Filter -->
                            <h6 class="filter-group-title filter-group-spacing">MRP Range</h6>
                            <div class="range-slider-container mb-3">
                                <div class="range-slider">
                                    <div class="range-track"></div>
                                    <input type="range" class="range-min" min="0" max="50000" step="100" name="mrp_min" value="{{ request('mrp_min', 0) }}">
                                    <input type="range" class="range-max" min="0" max="50000" step="100" name="mrp_max" value="{{ request('mrp_max', 50000) }}">
                                </div>
                                <div class="range-values mt-2 d-flex justify-content-between">
                                    <span class="text-muted small">₹<span class="min-val">{{ request('mrp_min', 0) }}</span></span>
                                    <span class="text-muted small">₹<span class="max-val">{{ request('mrp_max', 50000) }}</span></span>
                                </div>
                            </div>

                            <!-- Rent Price Range Filter -->
                            <h6 class="filter-group-title filter-group-spacing">Rent Range</h6>
                            <div class="range-slider-container mb-3">
                                <div class="range-slider">
                                    <div class="range-track"></div>
                                    <input type="range" class="range-min" min="0" max="20000" step="50" name="price_min" value="{{ request('price_min', 0) }}">
                                    <input type="range" class="range-max" min="0" max="20000" step="50" name="price_max" value="{{ request('price_max', 20000) }}">
                                </div>
                                <div class="range-values mt-2 d-flex justify-content-between">
                                    <span class="text-muted small">₹<span class="min-val">{{ request('price_min', 0) }}</span></span>
                                    <span class="text-muted small">₹<span class="max-val">{{ request('price_max', 20000) }}</span></span>
                                </div>
                            </div>

                            <!-- Preserve search and sort -->
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            @if(request('sort_by'))
                                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Filter Bar -->
                <div class="top-filters mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="deal-type-filters">
                            <label class="filter-radio">
                                <input type="radio" name="deal_type" value="all" 
                                       {{ request('deal_type', 'all') === 'all' ? 'checked' : '' }} 
                                       class="deal-type-filter">
                                <span class="radio-mark"></span>
                                <span class="filter-label">All</span>
                            </label>
                            <label class="filter-radio">
                                <input type="radio" name="deal_type" value="rent" 
                                       {{ request('deal_type') === 'rent' ? 'checked' : '' }} 
                                       class="deal-type-filter">
                                <span class="radio-mark"></span>
                                <span class="filter-label">Rent</span>
                            </label>
                            <label class="filter-radio">
                                <input type="radio" name="deal_type" value="purchase" 
                                       {{ request('deal_type') === 'purchase' ? 'checked' : '' }} 
                                       class="deal-type-filter">
                                <span class="radio-mark"></span>
                                <span class="filter-label">Buy</span>
                            </label>
                        </div>
                        
                        <!-- Search Bar -->
                        <div class="search-section">
                            <div class="search-input-wrapper">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" 
                                       name="search" 
                                       id="searchInput" 
                                       class="form-control search-input" 
                                       placeholder="Search for clothes..." 
                                       value="{{ request('search') }}"
                                       autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="header-date-filter d-none d-md-flex align-items-center gap-1">
                            <div class="input-group input-group-sm" style="width: 140px;">
                                <span class="input-group-text bg-white border-end-0 text-warning"><i class="bi bi-calendar-range"></i></span>
                                <input type="date" id="fromDateFilter" class="form-control border-start-0 ps-0 text-muted" 
                                       placeholder="From" value="{{ request('from_date') }}" style="font-size: 0.8rem;">
                            </div>
                            <div class="input-group input-group-sm" style="width: 130px;">
                                <input type="date" id="toDateFilter" class="form-control text-muted" 
                                       placeholder="To" value="{{ request('to_date') }}" style="font-size: 0.8rem;" min="{{ request('from_date') }}">
                            </div>
                        </div>
                        <div class="sort-section">
                            <select name="sort_by" id="sortBy" class="form-select sort-filter">
                                <option value="default" {{ request('sort_by', 'default') === 'default' ? 'selected' : '' }}>Sort by default</option>
                                <option value="price_low" {{ request('sort_by') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort_by') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="mrp_low" {{ request('sort_by') === 'mrp_low' ? 'selected' : '' }}>MRP: Low to High</option>
                                <option value="mrp_high" {{ request('sort_by') === 'mrp_high' ? 'selected' : '' }}>MRP: High to Low</option>
                                <option value="rating_high" {{ request('sort_by') === 'rating_high' ? 'selected' : '' }}>Rating: High to Low</option>
                                <option value="rating_low" {{ request('sort_by') === 'rating_low' ? 'selected' : '' }}>Rating: Low to High</option>
                                <option value="newest" {{ request('sort_by') === 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ request('sort_by') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-warning" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted loading-text">Loading products...</p>
                </div>

                <!-- Product Grid -->
                <div class="products-grid" id="productsGrid">
                    @include('clothes.partials.products-grid', ['clothes' => $clothes])
                </div>

                <!-- Pagination -->
                <div id="paginationWrapper">
                    @include('clothes.partials.pagination', ['clothes' => $clothes])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Handle Date Input Changes (Native)
    $('#fromDateFilter').on('change', function() {
        const fromDate = $(this).val();
        $('#toDateFilter').attr('min', fromDate); // Enforce min date on To field
        
        // If To Date is earlier than new From Date, clear it
        if ($('#toDateFilter').val() && $('#toDateFilter').val() < fromDate) {
            $('#toDateFilter').val('');
        }
        
        loadProducts(1);
    });

    $('#toDateFilter').on('change', function() {
        loadProducts(1);
    });

    let filterTimeout;
    const $productsGrid = $('#productsGrid');
    const $paginationWrapper = $('#paginationWrapper');
    const $loadingIndicator = $('#loadingIndicator');
    
    // Debounced function to prevent too many AJAX calls
    function debounce(func, wait) {
        return function(...args) {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
    
    // Collect all filter values
    function getFilterData() {
        const filters = {
            categories: [],
            genders: [],
            conditions: [],
            status: $('input[name="status"]:checked').val() || 'any',
            seller_rating: $('#seller_rating').val() || '',
            product_rating: $('#product_rating').val() || '',
            mrp_min: $('input[name="mrp_min"]').val() || '',
            mrp_max: $('input[name="mrp_max"]').val() || '',
            price_min: $('input[name="price_min"]').val() || '',
            price_max: $('input[name="price_max"]').val() || '',
            deal_type: $('input[name="deal_type"]:checked').val() || 'all',
            sort_by: $('#sortBy').val() || 'default',
            search: $('.search-section input[name="search"]').val() || '',
            from_date: $('#fromDateFilter').val() || '',
            to_date: $('#toDateFilter').val() || ''
        };
        
        // Collect checked checkboxes
        $('input[name="categories[]"]:checked').each(function() {
            filters.categories.push($(this).val());
        });
        
        $('input[name="genders[]"]:checked').each(function() {
            filters.genders.push($(this).val());
        });
        
        $('input[name="conditions[]"]:checked').each(function() {
            filters.conditions.push($(this).val());
        });
        
        return filters;
    }
    
    // Load products via AJAX
    function loadProducts(page = 1) {
        const filters = getFilterData();
        filters.page = page;
        
        // Show loading indicator
        $loadingIndicator.show();
        $productsGrid.hide();
        $paginationWrapper.hide();
        
        // Update URL without reload
        const queryString = $.param(filters);
        window.history.pushState({filters: filters}, '', '{{ route("clothes.index") }}?' + queryString);
        
        $.ajax({
            url: '{{ route("clothes.index") }}',
            method: 'GET',
            data: filters,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $productsGrid.html(response.html);
                    $paginationWrapper.html(response.pagination);
                    
                    // Scroll to top of products
                    $('html, body').animate({
                        scrollTop: $productsGrid.offset().top - 100
                    }, 300);
                }
            },
            error: function(xhr) {
                console.error('Error loading products:', xhr);
                $productsGrid.html('<div class="col-12 text-center py-5"><h4 class="text-danger">Error loading products</h4><p class="text-muted">Please try again</p></div>');
            },
            complete: function() {
                $loadingIndicator.hide();
                $productsGrid.show();
                $paginationWrapper.show();
            }
        });
    }
    
    // Debounced filter update
    const debouncedLoadProducts = debounce(loadProducts, 300);
    
    // Handle all filter changes
    $(document).on('change', '#filterForm input[type="checkbox"], #filterForm input[type="radio"], #filterForm input[type="range"]', function() {
        debouncedLoadProducts(1);
    });

    // Handle range slider dual thumbs visually
    $('.range-slider-container').each(function() {
        let container = $(this);
        let minInput = container.find('.range-min');
        let maxInput = container.find('.range-max');
        let minValDisp = container.find('.min-val');
        let maxValDisp = container.find('.max-val');
        
        container.on('input', '.range-min', function() {
            if(parseInt(minInput.val()) > parseInt(maxInput.val())) {
                minInput.val(maxInput.val());
            }
            minValDisp.text(minInput.val());
        });
        container.on('input', '.range-max', function() {
            if(parseInt(maxInput.val()) < parseInt(minInput.val())) {
                maxInput.val(minInput.val());
            }
            maxValDisp.text(maxInput.val());
        });
    });
    
    // Star Rating Interactivity
    $('.star-filter-icon').on('mouseenter', function() {
        let rating = $(this).data('rating');
        let container = $(this).closest('.star-rating-filter');
        container.find('.star-filter-icon').each(function() {
            if ($(this).data('rating') <= rating) {
                $(this).removeClass('bi-star').addClass('bi-star-fill text-warning');
            } else {
                $(this).removeClass('bi-star-fill text-warning').addClass('bi-star');
            }
        });
    });

    $('.star-rating-filter').on('mouseleave', function() {
        let container = $(this);
        let currentRating = container.find('input[type="hidden"]').val() || 0;
        container.find('.star-filter-icon').each(function() {
            if ($(this).data('rating') <= currentRating) {
                $(this).removeClass('bi-star').addClass('bi-star-fill text-warning');
            } else {
                $(this).removeClass('bi-star-fill text-warning').addClass('bi-star');
            }
        });
    });

    $('.star-filter-icon').on('click', function() {
        let rating = $(this).data('rating');
        let container = $(this).closest('.star-rating-filter');
        container.find('input[type="hidden"]').val(rating);
        container.find('.clear-rating').show();
        debouncedLoadProducts(1);
    });

    $('.clear-rating').on('click', function() {
        let container = $(this).closest('.star-rating-filter');
        container.find('input[type="hidden"]').val('');
        $(this).hide();
        container.trigger('mouseleave'); // reset visual to 0
        debouncedLoadProducts(1);
    });

    // Handle deal type changes
    $(document).on('change', 'input[name="deal_type"]', function() {
        loadProducts(1);
    });
    
    // Handle sort changes
    $(document).on('change', '#sortBy', function() {
        loadProducts(1);
    });
    
    // Handle search on keyup with debouncing
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        const searchValue = $(this).val();
        
        // Debounce search - wait 500ms after user stops typing
        searchTimeout = setTimeout(function() {
            debouncedLoadProducts(1);
        }, 500);
    });
    
    // Clear search on escape key
    $('#searchInput').on('keydown', function(e) {
        if (e.key === 'Escape') {
            $(this).val('');
            debouncedLoadProducts(1);
        }
    });
    
    // Handle pagination clicks
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            const page = new URL(url).searchParams.get('page') || 1;
            loadProducts(page);
        }
    });
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.filters) {
            // Restore filters from state
            const filters = e.state.filters;
            
            // Restore checkboxes
            $('input[type="checkbox"]').prop('checked', false);
            filters.categories.forEach(id => $(`input[name="categories[]"][value="${id}"]`).prop('checked', true));
            filters.genders.forEach(val => $(`input[name="genders[]"][value="${val}"]`).prop('checked', true));
            filters.conditions.forEach(val => $(`input[name="conditions[]"][value="${val}"]`).prop('checked', true));
            
            // Restore radios
            $(`input[name="status"][value="${filters.status}"]`).prop('checked', true);
            $(`input[name="deal_type"][value="${filters.deal_type}"]`).prop('checked', true);
            
            // Restore Star Ratings
            function restoreStarUI(inputId, value) {
                let $input = $(`#${inputId}`);
                $input.val(value);
                let $container = $input.closest('.star-rating-filter');
                if (value) {
                    $container.find('.clear-rating').show();
                } else {
                    $container.find('.clear-rating').hide();
                }
                $container.trigger('mouseleave');
            }
            if (filters.seller_rating !== undefined) restoreStarUI('seller_rating', filters.seller_rating);
            if (filters.product_rating !== undefined) restoreStarUI('product_rating', filters.product_rating);
            
            // Restore number inputs
            $('input[name="mrp_min"]').val(filters.mrp_min);
            $('input[name="mrp_max"]').val(filters.mrp_max);
            $('input[name="price_min"]').val(filters.price_min);
            $('input[name="price_max"]').val(filters.price_max);

            $('.range-slider-container').has('input[name="mrp_min"]').find('.min-val').text(filters.mrp_min || 0);
            $('.range-slider-container').has('input[name="mrp_max"]').find('.max-val').text(filters.mrp_max || 50000);
            $('.range-slider-container').has('input[name="price_min"]').find('.min-val').text(filters.price_min || 0);
            $('.range-slider-container').has('input[name="price_max"]').find('.max-val').text(filters.price_max || 20000);

            // Restore select
            $('#sortBy').val(filters.sort_by);
            $('.search-section input[name="search"]').val(filters.search);

            // Restore dates
            if (filters.from_date) {
                $('#fromDateFilter').val(filters.from_date);
                $('#toDateFilter').attr('min', filters.from_date);
            }
            if (filters.to_date) {
                $('#toDateFilter').val(filters.to_date);
            }
            
            loadProducts(filters.page || 1);
        }
    });
});
</script>
@endsection

