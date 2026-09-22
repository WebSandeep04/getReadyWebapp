<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cloth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClothApiController extends Controller
{
    public function store(Request $request)
    {
        // 1. Basic Validation (Adjust as needed for your API)
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required',
            'rent_price' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // 2. Map React Native fields to your Cloth database columns
            $cloth = Cloth::create([
                // For API testing without auth, you can hardcode a user_id like 1. 
                // Once you add mobile login, change this to Auth::id() or get from Sanctum token.
                'user_id' => Auth::id() ?? 1, 
                
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'gender' => $request->user_type, // React Native sends user_type
                'brand_id' => $request->brand_id,
                'fabric_id' => $request->fabric_type_id, // React Native sends fabric_type_id
                'color_id' => $request->color_id,
                'size_id' => $request->size_id,
                'fit_type_id' => $request->body_fit_id, // React Native sends body_fit_id
                'condition_id' => $request->garment_condition_id, // RN sends garment_condition_id
                'defects' => $request->defects,
                
                // Pricing
                'rent_price' => $request->rent_price,
                'mrp' => $request->mrp,
                'selling_price' => $request->selling_price,
                'is_purchased' => $request->is_purchased == '1' ? 1 : 0,
                'security_deposit' => 0, // Default since it's not in the mobile form yet
                'sku' => rand(1000, 9999), // Generate a random SKU for now
                
                // State
                'is_available' => true,
                'is_approved' => null, // Pending approval
                
                // Measurements (converted to CM by React Native)
                'chest_bust' => $request->chest,
                'waist' => $request->waist,
                'length' => $request->length,
                'shoulder' => $request->shoulder,
                'sleeve_length' => $request->sleeve_length,
                'measurement_unit' => 'cm', // RN auto-converts to cm
            ]);

            // 3. Handle Photos and save to cloth_images table
            $photoFields = ['main_photo', 'side_photo', 'back_photo', 'detail_photo'];
            
            foreach ($photoFields as $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('clothes', 'public');
                    $cloth->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cloth item submitted successfully',
                'data' => $cloth->load('images') // Return the cloth with its images
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Cloth Submission Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save: ' . $e->getMessage()
            ], 500);
        }
    }

        public function getHomeFeed(Request $request)
    {
        try {
            // 1. Start the base query
            // We eager-load 'category', 'brand', and 'images' to prevent N+1 query issues.
            $query = Cloth::with(['category', 'brand', 'images'])
                ->where('is_available', true)
                ->where('is_approved', 1); // Uncomment this when you start approving clothes

            // 2. Apply Filters from Request

            // Array Filters (Categories, Brands, Sizes, etc.)
            if ($request->has('categories') && !empty($request->categories)) {
                $query->whereIn('category_id', $request->categories);
            }
            if ($request->has('brands') && !empty($request->brands)) {
                $query->whereIn('brand_id', $request->brands);
            }
            if ($request->has('sizes') && !empty($request->sizes)) {
                $query->whereIn('size_id', $request->sizes);
            }
            if ($request->has('colors') && !empty($request->colors)) {
                $query->whereIn('color_id', $request->colors);
            }
            if ($request->has('fabrics') && !empty($request->fabrics)) {
                $query->whereIn('fabric_id', $request->fabrics);
            }
            if ($request->has('conditions') && !empty($request->conditions)) {
                $query->whereIn('condition_id', $request->conditions);
            }
            if ($request->has('genders') && !empty($request->genders)) {
                // Assuming 'gender' handles 'User Type' like 'Men', 'Women', etc.
                $query->whereIn('gender', $request->genders); 
            }
            if ($request->has('fits') && !empty($request->fits)) {
                $query->whereIn('fit_type_id', $request->fits);
            }
            if ($request->has('bottoms') && !empty($request->bottoms)) {
                $query->whereIn('bottom_type_id', $request->bottoms);
            }

            // Price/Rent Range Filters
            if ($request->has('price_min') && $request->price_min !== '') {
                $query->where('rent_price', '>=', $request->price_min);
            }
            if ($request->has('price_max') && $request->price_max !== '') {
                $query->where('rent_price', '<=', $request->price_max);
            }
            if ($request->has('mrp_min') && $request->mrp_min !== '') {
                $query->where('mrp', '>=', $request->mrp_min);
            }
            if ($request->has('mrp_max') && $request->mrp_max !== '') {
                $query->where('mrp', '<=', $request->mrp_max);
            }

            // Search Keyword Filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Deal Type Filter (Rent vs Buy)
            if ($request->has('deal_type') && $request->deal_type !== 'all') {
                if ($request->deal_type === 'rent') {
                    $query->whereNotNull('rent_price')->where('rent_price', '>', 0);
                } elseif ($request->deal_type === 'buy') {
                    $query->where('is_purchased', 1);
                }
            }

            // Boolean/Feature Filters
            if ($request->has('is_cleaned') && $request->is_cleaned == '1') {
                $query->where('is_cleaned', 1);
            }
            if ($request->has('status') && $request->status !== 'any') {
                if ($request->status === 'available') {
                    $query->where('is_purchased', 0);
                } elseif ($request->status === 'sold') {
                    $query->where('is_purchased', 1);
                }
            }

            // Priority / Sorting
            if ($request->has('sort_by') && $request->sort_by === 'rdm_low') {
                // If you want to sort by rent-to-mrp ratio, you'd add raw order logic here.
                // For now, let's keep it simple or fallback to latest.
                $query->latest(); 
            } else {
                $query->latest();
            }

            // 3. Fetch the data
            $clothes = $query->take(20)->get();

            // 4. Map the database models to the JSON structure the app expects
            $formattedClothes = $clothes->map(function ($cloth) {
                
                // Find the first image path, if any exist
                $mainImagePath = $cloth->images->first() ? $cloth->images->first()->image_path : null;

                return [
                    'id' => (string) $cloth->id,
                    'title' => $cloth->title,
                    // Fallback to empty string if brand is null
                    'brand' => $cloth->brand ? $cloth->brand->name : '', 
                    // Format prices to include the currency symbol
                    'rent' => '₹' . number_format($cloth->rent_price, 2),
                    // Show selling price if available, otherwise show MRP
                    'buy' => $cloth->is_purchased && $cloth->selling_price 
                                ? '₹' . number_format($cloth->selling_price, 2) 
                                : '₹' . number_format($cloth->mrp, 2),
                    // Generate full URL for the main photo.
                    'image' => $mainImagePath ? asset('storage/' . $mainImagePath) : null,
                    // Include category ID and Name so the app can filter by "Wedding Wear", etc.
                    'category_id' => $cloth->category_id,
                    'category_name' => $cloth->category ? $cloth->category->name : '',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedClothes,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('API Home Feed Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch home feed: ' . $e->getMessage()
            ], 500);
        }
    }

        public function apiShow(Request $request, $id)
    {
        try {
            // Fetch the individual cloth with all its relationships
            $cloth = Cloth::with([
                'images', 
                'user', 
                'category', 
                'brand', 
                'fabric', 
                'color', 
                'size', 
                'bottomType', 
                'fitType', 
                'condition',
                'availabilityBlocks', 
                'reviews.user', 
                'reviews.replies.user', 
                'questions.user',
                'questions.answerer',
                'questions.replies.user'
            ])->findOrFail($id);
            
            $categoryId = $cloth->category_id;
            
            // Handle authenticated user checks for the API
            $userReview = null;
            $canReview = false;
            
            // Check if user is authenticated via API token (e.g., Sanctum)
            $user = $request->user('sanctum'); 

            if ($user) {
                // Get user's existing review
                $userReview = $cloth->reviews()->where('user_id', $user->id)->first();
                
                // Check if user can review
                $canReview = \App\Models\Order::where('buyer_id', $user->id)
                    ->whereHas('items', function($query) use ($id) {
                        $query->where('cloth_id', $id);
                    })->exists();
            }
            
            // Get related clothes
            $relatedClothes = Cloth::where('category_id', $categoryId)
                ->where('id', '!=', $id)
                ->where('is_approved', 1)
                ->with(['images', 'category', 'brand', 'size', 'condition'])
                ->inRandomOrder()
                ->take(6)
                ->get();
            
            // Return everything as a JSON response
            return response()->json([
                'success' => true,
                'data' => [
                    'cloth' => $cloth,
                    'userReview' => $userReview,
                    'canReview' => $canReview,
                    'relatedClothes' => $relatedClothes,
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the ID doesn't exist
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        } catch (\Exception $e) {
            // Handle general errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function myClothes()
    {
        try {
            $user = \Illuminate\Support\Facades\Auth::user();
            
            $clothes = \App\Models\Cloth::with(['category', 'size', 'condition', 'images'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json([
                'success' => true,
                'clothes' => $clothes
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching user clothes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching your clothes.'
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $user = $request->user('sanctum') ?: Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $cloth = Cloth::where('user_id', $user->id)->findOrFail($id);

            DB::beginTransaction();

            $updateData = $request->only([
                'title', 'description', 'gender', 'defects', 
                'rent_price', 'selling_price', 'security_deposit', 'mrp', 'sku',
                'chest_bust', 'waist', 'length', 'shoulder', 'sleeve_length', 'measurement_unit'
            ]);

            if ($request->has('category_id')) $updateData['category_id'] = $request->category_id;
            if ($request->has('brand_id')) $updateData['brand_id'] = $request->brand_id;
            if ($request->has('fabric_type_id')) $updateData['fabric_id'] = $request->fabric_type_id;
            if ($request->has('color_id')) $updateData['color_id'] = $request->color_id;
            if ($request->has('size_id')) $updateData['size_id'] = $request->size_id;
            if ($request->has('body_fit_id')) $updateData['fit_type_id'] = $request->body_fit_id;
            if ($request->has('garment_condition_id')) $updateData['condition_id'] = $request->garment_condition_id;
            
            if ($request->has('user_type')) $updateData['gender'] = $request->user_type;

            if ($request->has('is_cleaned')) $updateData['is_cleaned'] = $request->is_cleaned == '1' ? 1 : 0;
            if ($request->has('is_purchased')) $updateData['is_purchased'] = $request->is_purchased == '1' ? 1 : 0;

            $updateData['is_approved'] = null;
            $updateData['resubmission_count'] = ($cloth->resubmission_count ?? 0) + 1;

            $cloth->update($updateData);

            $photoFields = ['main_photo', 'side_photo', 'back_photo', 'detail_photo'];
            foreach ($photoFields as $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('clothes', 'public');
                    $cloth->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('clothes', 'public');
                    $cloth->images()->create([
                        'image_path' => $imagePath
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cloth updated successfully',
                'data' => $cloth->fresh()->load('images')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Cloth Update Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateAvailability(Request $request, $id)
    {
        $user = $request->user('sanctum') ?: Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $cloth = Cloth::where('user_id', $user->id)->findOrFail($id);
            
            if ($request->has('availability_blocks')) {
                $cloth->availabilityBlocks()->delete();
                
                foreach ($request->availability_blocks as $block) {
                    if (!empty($block['start_date']) && !empty($block['end_date'])) {
                        $cloth->availabilityBlocks()->create([
                            'start_date' => $block['start_date'],
                            'end_date' => $block['end_date'],
                            'type' => $block['type'] ?? 'blocked',
                            'reason' => $block['reason'] ?? null,
                        ]);
                    }
                }
            } else {
                $cloth->availabilityBlocks()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Dates updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update dates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user('sanctum') ?: Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $cloth = Cloth::where('user_id', $user->id)->findOrFail($id);
            
            foreach ($cloth->images as $image) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($image->image_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image_path);
                }
            }
            
            $cloth->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cloth deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cloth: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyImage(Request $request, $imageId)
    {
        $user = $request->user('sanctum') ?: Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $image = \App\Models\ClothImage::whereHas('cloth', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($imageId);
            
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($image->image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image_path);
            }
            
            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }
}
