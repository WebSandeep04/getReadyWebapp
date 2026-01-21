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
        $query = Cloth::with(['images', 'user'])
            ->where('is_approved', 1)
            ->where('is_available', true);

        // Filter by categories (multiple selection)
        if ($request->filled('categories')) {
            $categories = is_array($request->categories) ? $request->categories : [$request->categories];
            $query->whereIn('category', $categories);
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
            $query->whereIn('condition', $conditions);
        }

        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('rent_price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('rent_price', '<=', $request->price_max);
        }

        // Filter by deal type
        if ($request->filled('deal_type')) {
            if ($request->deal_type === 'rent') {
                // Only rental items (all items are rental by default)
            } elseif ($request->deal_type === 'purchase') {
                $query->where('is_purchased', true);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'default');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('rent_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('rent_price', 'desc');
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
        $sizes = Size::orderBy('name')->get();
        $conditions = ['Brand New', 'Like New', 'Excellent', 'Good', 'Fair'];
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
        
        return view('clothes.browse', compact('clothes', 'categories', 'sizes', 'conditions', 'genders', 'showFilters'));
    }
}
