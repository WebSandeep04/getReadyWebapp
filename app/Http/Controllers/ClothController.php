<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cloth;
use App\Models\Category;
use App\Models\FabricType;
use App\Models\Color;
use App\Models\Size;
use App\Models\Brand;
use App\Models\BodyTypeFit;
use Illuminate\Support\Facades\Auth;
use App\Models\ClothImage;
use App\Models\AvailabilityBlock;
use Illuminate\Support\Facades\Storage;
use App\Models\GarmentCondition;
use App\Models\BottomType;

class ClothController extends Controller
{
    public function show($id)
    {
        $cloth = Cloth::with([
            'user',
            'images',
            'availabilityBlocks',
            'reviews.user',
            'reviews.replies.user',
            'questions.user',
            'questions.answerer',
            'questions.replies.user'
        ])->findOrFail($id);
        
        // Convert IDs to names for display
        if ($cloth->category) {
            $category = Category::find($cloth->category);
            $cloth->category = $category ? $category->name : 'Unknown';
        }
        
        if ($cloth->fabric) {
            $fabric = FabricType::find($cloth->fabric);
            $cloth->fabric = $fabric ? $fabric->name : 'Unknown';
        }
        
        if ($cloth->color) {
            $color = Color::find($cloth->color);
            $cloth->color = $color ? $color->name : 'Unknown';
        }
        
        if ($cloth->size) {
                // The Size model uses the 'sizes' table    
            $size = Size::where('id', $cloth->size)->first();
            $cloth->size = $size ? $size->name : 'Unknown';
        }
        
        if ($cloth->bottom_type) {
            $bottomType = BottomType::find($cloth->bottom_type);
            $cloth->bottom_type = $bottomType ? $bottomType->name : 'Unknown';
        }
        
        if ($cloth->fit_type) {
            $bodyTypeFit = BodyTypeFit::find($cloth->fit_type);
            $cloth->fit_type = $bodyTypeFit ? $bodyTypeFit->name : 'Unknown';
        }
        
        // Get user's existing review if logged in
        $userReview = null;
        if (Auth::check()) {
            $userReview = $cloth->reviews()->where('user_id', Auth::id())->first();
        }
        
        $showFilters = false;
        return view('clothes.show', compact('cloth', 'showFilters', 'userReview'));
    }

    public function index()
    {
        $clothes = Cloth::where('user_id', Auth::id())->with('images')->get();
        $sizes = Size::all();
        $showFilters = false;
        
        return view('clothes.index', compact('clothes', 'sizes', 'showFilters'));
    }

    public function edit($id)
    {
        $cloth = Cloth::where('user_id', Auth::id())->with(['images', 'availabilityBlocks'])->findOrFail($id);
        $sizes = Size::all();
        
        // Get data for dropdowns
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $fabricTypes = FabricType::orderBy('name')->get();
        $colors = Color::orderBy('name')->get();
        $fitTypes = BodyTypeFit::orderBy('name')->get();

        $showFilters = true;   
        
        return view('clothes.edit', compact('cloth', 'sizes', 'brands', 'categories', 'fabricTypes', 'colors', 'fitTypes', 'showFilters'));
    }

    public function update(Request $request, $id)
    {
        $cloth = Cloth::where('user_id', Auth::id())->findOrFail($id);
        
        // Handle image uploads
        if ($request->hasFile('images')) {
            $request->validate([
                'images.*' => 'image|mimes:jpeg,png,jpg,gif'
            ]);
            
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('clothes', 'public');
                $cloth->images()->create([
                    'image_path' => $imagePath
                ]);
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Images uploaded successfully'
                ]);
            }
        }
        
        // Handle cloth details update
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'gender' => 'required|in:Boy,Girl,Men,Women',
            'brand' => 'nullable|string|max:255',
            'fabric' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'chest_bust' => 'nullable|string|max:50',
            'waist' => 'nullable|string|max:50',
            'length' => 'nullable|string|max:50',
            'shoulder' => 'nullable|string|max:50',
            'sleeve_length' => 'nullable|string|max:50',
            'size' => 'required|exists:sizes,id',
            'fit_type' => 'nullable|string|max:255',
            'condition' => 'required|in:Brand New,Like New,Excellent,Good,Fair',
            'defects' => 'nullable|string',
            'is_cleaned' => 'boolean',
            'rent_price' => 'required|numeric|min:0',
            'is_purchased' => 'boolean',
            'purchase_value' => 'required_if:is_purchased,1|nullable|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
        ]);

        // Prepare update data
        $updateData = $request->only([
            'title', 'description', 'category', 'gender', 'brand', 'fabric', 'color', 
            'chest_bust', 'waist', 'length', 'shoulder', 
            'sleeve_length', 'size', 'fit_type', 'condition', 'defects', 
            'rent_price', 'is_purchased', 'purchase_value', 'security_deposit'
        ]);
        
        // Handle checkboxes
        $updateData['is_cleaned'] = $request->has('is_cleaned') ? 1 : 0;
        $updateData['is_purchased'] = $request->has('is_purchased') ? 1 : 0;
        
        // Reset approval status on update
        $updateData['is_approved'] = null;
        $updateData['resubmission_count'] = ($cloth->resubmission_count ?? 0) + 1;
        
        $cloth->update($updateData);

        // Handle availability blocks
        if ($request->has('availability_blocks')) {
            // Validate availability blocks
            $request->validate([
                'availability_blocks.*.start_date' => 'required|date',
                'availability_blocks.*.end_date' => 'required|date|after_or_equal:availability_blocks.*.start_date',
                'availability_blocks.*.type' => 'required|in:available,blocked',
                'availability_blocks.*.reason' => 'nullable|string|max:255',
            ]);
            
            // Delete existing availability blocks
            $cloth->availabilityBlocks()->delete();
            
            // Create new availability blocks
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
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cloth updated successfully',
                'cloth' => $cloth->fresh()
            ]);
        }

        return redirect()->route('listed.clothes')->with('success', 'Cloth updated successfully');
    }

    public function destroy($id)
    {
        $cloth = Cloth::where('user_id', Auth::id())->findOrFail($id);
        $cloth->delete();

        return redirect()->route('listed.clothes')->with('success', 'Cloth deleted successfully');
    }

    public function create()
    {
        $categories = Category::all();
        $fabric_types = FabricType::all();
        $colors = Color::all();
        $sizes = Size::all();
        $body_type_fits = BodyTypeFit::all();
        $garment_conditions = GarmentCondition::all();
        $brands = Brand::all();
        $showFilters = false;
        return view('sell', compact('categories', 'fabric_types', 'colors', 'sizes', 'body_type_fits', 'garment_conditions', 'brands', 'showFilters'));
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|exists:category,id',
            'gender' => 'required|in:Boy,Girl,Men,Women',
            'brand' => 'required|string|max:255',
            'fabric' => 'required|exists:fabric_types,id',
            'color' => 'required|exists:colors,id',
            'size' => 'required|exists:sizes,id',
            'body_type_fit' => 'nullable|exists:body_type_fits,id',
            'condition' => 'required|in:Brand New,Like New,Excellent,Good,Fair',
            'defects' => 'nullable|string',
            'purchase_value' => 'required|numeric|min:0',
            'rent_price' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $mrp = $request->input('purchase_value');
                    if ($mrp && $value > ($mrp * 0.2)) {
                        $maxRent = $mrp * 0.2;
                        $fail("Rent price should not exceed 20% of MRP. Maximum suggested rent: ₹" . number_format($maxRent, 2));
                    }
                },
            ],
            'security_deposit' => 'required|numeric|min:0',
            'images' => 'required|array|min:1|max:4',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif',
            'availability_blocks' => 'nullable|array',
            'availability_blocks.*.start_date' => 'required_with:availability_blocks|date',
            'availability_blocks.*.end_date' => 'required_with:availability_blocks|date|after_or_equal:availability_blocks.*.start_date',
            'availability_blocks.*.type' => 'required_with:availability_blocks|in:available,blocked',
            'availability_blocks.*.reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create the cloth record
        $cloth = Cloth::create([
            'user_id' => Auth::id(),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'gender' => $request->input('gender'),
            'brand' => $request->input('brand'),
            'fabric' => $request->input('fabric'),
            'color' => $request->input('color'),
            'size' => $request->input('size'),
            'fit_type' => $request->input('body_type_fit'),
            'condition' => $request->input('condition'),
            'defects' => $request->input('defects'),
            'purchase_value' => $request->input('purchase_value'),
            'rent_price' => $request->input('rent_price'),
            'is_purchased' => 1, // Always set to 1
            'security_deposit' => $request->input('security_deposit'),
            'is_available' => true,
            'is_approved' => null, // Explicitly set to pending
            'chest_bust' => $request->input('chest_bust'),
            'waist' => $request->input('waist'),
            'length' => $request->input('length'),
            'shoulder' => $request->input('shoulder'),
            'sleeve_length' => $request->input('sleeve_length'),
        ]);

        // Handle availability blocks
        if ($request->has('availability_blocks')) {
            $availableBlocks = [];
            $blockedBlocks = [];
            
            // Separate available and blocked blocks
            foreach ($request->availability_blocks as $block) {
                if (!empty($block['start_date']) && !empty($block['end_date'])) {
                    if (($block['type'] ?? 'blocked') === 'available') {
                        $availableBlocks[] = $block;
                    } else {
                        $blockedBlocks[] = $block;
                    }
                }
            }
            
            // Process available blocks first and auto-create delivery/pickup blocks
            foreach ($availableBlocks as $block) {
                $startDate = \Carbon\Carbon::parse($block['start_date']);
                $endDate = \Carbon\Carbon::parse($block['end_date']);
                
                // Validate minimum 4 days rental
                $daysDiff = $startDate->diffInDays($endDate) + 1;
                if ($daysDiff < 4) {
                    return redirect()->back()
                        ->withErrors(['availability_blocks' => "Minimum 4 days rental required. Selected period: {$daysDiff} day(s)."])
                        ->withInput();
                }
                
                // Create the available block
                $cloth->availabilityBlocks()->create([
                    'start_date' => $block['start_date'],
                    'end_date' => $block['end_date'],
                    'type' => 'available',
                    'reason' => $block['reason'] ?? null,
                ]);
            }
            
            // Process manually added blocked blocks (excluding auto-created ones)
            foreach ($blockedBlocks as $block) {
                // Skip if it's an auto-blocked block (already created above)
                $reason = $block['reason'] ?? '';
                if (strpos($reason, 'Auto-blocked') === false) {
                    $cloth->availabilityBlocks()->create([
                        'start_date' => $block['start_date'],
                        'end_date' => $block['end_date'],
                        'type' => 'blocked',
                        'reason' => $block['reason'] ?? null,
                    ]);
                }
            }
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('clothes', 'public');
                
                // Store image record in cloth_images table
                $cloth->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect('/')->with('success', 'Your item has been listed successfully!');
    }

    public function destroyImage($imageId)
    {
        $image = ClothImage::whereHas('cloth', function($query) {
            $query->where('user_id', Auth::id());
        })->findOrFail($imageId);
        
        // Delete the file from storage
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }
} 