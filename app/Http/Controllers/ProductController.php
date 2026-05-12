<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cloth;
use App\Models\Category;
use App\Models\Size;
use App\Models\GarmentCondition;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Start with approved and available clothes
        $query = Cloth::with(['images', 'user', 'category', 'brand', 'size', 'color', 'fabric', 'condition', 'fitType', 'bottomType'])
            ->where('is_approved', 1)
            ->where('is_available', true);

        // Filter by categories (multiple selection)
        if ($request->filled('categories')) {
            $categories = is_array($request->categories) ? $request->categories : [$request->categories];
            $query->whereIn('category_id', $categories);
        }

        // Filter by gender (multiple selection)
        if ($request->filled('genders')) {
            $genders = is_array($request->genders) ? $request->genders : [$request->genders];
            $query->whereIn('gender', $genders);
        }

        // Filter by status (available/sold)
        if ($request->filled('status')) {
            if ($request->status === 'available') {
                $query->where('is_available', true);
            } elseif ($request->status === 'sold') {
                $query->where('is_available', false);
            }
            // 'any' means no filter
        }

        // Filter by condition (multiple selection)
        if ($request->filled('conditions')) {
            $conditions = is_array($request->conditions) ? $request->conditions : [$request->conditions];
            $query->whereIn('condition_id', $conditions);
        }

        // Filter by Brands
        if ($request->filled('brands')) {
            $brands = is_array($request->brands) ? $request->brands : [$request->brands];
            $query->whereIn('brand_id', $brands);
        }

        // Filter by Fabrics
        if ($request->filled('fabrics')) {
            $fabrics = is_array($request->fabrics) ? $request->fabrics : [$request->fabrics];
            $query->whereIn('fabric_id', $fabrics);
        }

        // Filter by Colors
        if ($request->filled('colors')) {
            $colors = is_array($request->colors) ? $request->colors : [$request->colors];
            $query->whereIn('color_id', $colors);
        }

        // Filter by Sizes
        if ($request->filled('sizes')) {
            $sizes = is_array($request->sizes) ? $request->sizes : [$request->sizes];
            $query->whereIn('size_id', $sizes);
        }

        // Filter by Fit Types
        if ($request->filled('fits')) {
            $fits = is_array($request->fits) ? $request->fits : [$request->fits];
            $query->whereIn('fit_type_id', $fits);
        }

        // Filter by Bottom Types
        if ($request->filled('bottoms')) {
            $bottoms = is_array($request->bottoms) ? $request->bottoms : [$request->bottoms];
            $query->whereIn('bottom_type_id', $bottoms);
        }

        // Filter by Is Cleaned
        if ($request->filled('is_cleaned')) {
            $query->where('is_cleaned', true);
        }

        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('rent_price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('rent_price', '<=', $request->price_max);
        }

        // Date Range Availability Filter
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $d1 = $request->from_date;
            $d2 = $request->to_date;

            // 1. Availability Logic (Whitelist)
            // Show if: (No explicit availability defined) OR (Explicit range OVERLAPS with request)
            $query->where(function($q) use ($d1, $d2) {
                // Case A: Generally available (no whitelist blocks)
                $q->whereDoesntHave('availabilityBlocks', function($aq) {
                    $aq->where('type', 'available');
                })
                // Case B: Whitelist blocks exist - check for ANY OVERLAP
                // (BlockStart <= ReqEnd) AND (BlockEnd >= ReqStart)
                ->orWhereHas('availabilityBlocks', function($aq) use ($d1, $d2) {
                    $aq->where('type', 'available')
                      ->where('start_date', '<=', $d2)
                      ->where('end_date', '>=', $d1);
                });
            });

            // 2. Blocking Logic (Blacklist) - Blocked Dates
            // Hide ONLY if the block covers the ENTIRE requested range (Full Enclosure)
            // Exclude if: (BlockStart <= ReqStart) AND (BlockEnd >= ReqEnd)
            $query->whereDoesntHave('availabilityBlocks', function($q) use ($d1, $d2) {
                $q->where('type', 'blocked')
                  ->where('start_date', '<=', $d1)
                  ->where('end_date', '>=', $d2);
            });

            // 3. Occupancy Logic (Blacklist) - Existing Orders
            // Hide ONLY if the order covers the ENTIRE requested range
            // Exclude if: (OrderStart <= ReqStart) AND (OrderEnd >= ReqEnd)
            $query->whereDoesntHave('orderItems.order', function($oq) use ($d1, $d2) {
                $oq->whereIn('status', ['Confirmed', 'Delivered'])
                   ->where('rental_from', '<=', $d1)
                   ->where('rental_to', '>=', $d2);
            });
        }

        // Filter by deal type
        if ($request->filled('deal_type')) {
            if ($request->deal_type === 'rent') {
                // Only rental items (all items are rental by default in current logic)
            } elseif ($request->deal_type === 'purchase') {
                $query->where('is_purchased', true);
            }
        }
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('brand', function($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Seller Rating
        if ($request->filled('seller_rating')) {
            $minRating = $request->seller_rating;
            $query->whereHas('user', function($uq) use ($minRating) {
                $uq->whereRaw('(SELECT COALESCE(AVG(rating), 0) FROM ratings WHERE ratings.rated_user_id = users.id) >= ?', [$minRating]);
            });
        }

        // Filter by Product Rating
        if ($request->filled('product_rating')) {
            $minRating = $request->product_rating;
            $query->whereRaw('(SELECT COALESCE(AVG(rating), 0) FROM product_reviews WHERE product_reviews.cloth_id = clothes.id) >= ?', [$minRating]);
        }

        // Filter by MRP Range
        if ($request->filled('mrp_min')) {
            $query->where('mrp', '>=', $request->mrp_min);
        }
        if ($request->filled('mrp_max')) {
            $query->where('mrp', '<=', $request->mrp_max);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'default');
        
        // Handle RDM Priority override
        if ($request->input('rdm_priority') == '1') {
            $sortBy = 'rdm_low';
        }

        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('rent_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('rent_price', 'desc');
                break;
            case 'mrp_low':
                $query->orderBy('mrp', 'asc');
                break;
            case 'mrp_high':
                $query->orderBy('mrp', 'desc');
                break;
            case 'rating_high':
                $query->orderByRaw('(SELECT COALESCE(AVG(rating), 0) FROM product_reviews WHERE product_reviews.cloth_id = clothes.id) desc');
                break;
            case 'rating_low':
                $query->orderByRaw('(SELECT COALESCE(AVG(rating), 0) FROM product_reviews WHERE product_reviews.cloth_id = clothes.id) asc');
                break;
            case 'rdm_low':
                $query->orderByRaw('rent_price / NULLIF(mrp, 0) asc')
                    ->orderBy('mrp', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Pagination
        $perPage = 12;
        $clothes = $query->paginate($perPage)->appends($request->query());

        // Get filter options
        $categories = Category::orderBy('name')->get();
        $sizes = \App\Models\Size::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $fabrics = \App\Models\FabricType::orderBy('name')->get();
        $colors = \App\Models\Color::orderBy('name')->get();
        $fits = \App\Models\BodyTypeFit::orderBy('name')->get();
        $bottoms = \App\Models\BottomType::orderBy('name')->get();
        $conditions = GarmentCondition::all();
        $genders = ['Boy', 'Girl', 'Men', 'Women'];

        $showFilters = false;
        
        // Return JSON for AJAX requests
        if ($request->ajax()) {
            $html = view('clothes.partials.products-grid', compact('clothes'))->render();
            $pagination = view('clothes.partials.pagination', compact('clothes'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $clothes->total()
            ]);
        }
        
        return view('clothes.browse', compact('clothes', 'categories', 'sizes', 'brands', 'fabrics', 'colors', 'fits', 'bottoms', 'conditions', 'genders', 'showFilters'));
    }
}
