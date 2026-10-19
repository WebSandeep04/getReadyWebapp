<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cloth;
use App\Models\Notification;
use App\Models\Category;
use App\Models\FabricType;
use App\Models\Color;
use App\Models\Size;
use App\Models\BottomType;
use App\Models\BodyTypeFit;
use App\Models\Brand;
use App\Models\GarmentCondition;

class RejectionApiController extends Controller
{
    /**
     * Get rejected items for mobile app
     */
    public function index()
    {
        $rejectedClothes = Cloth::where('user_id', Auth::id())
            ->where(function($query) {
                $query->where('is_approved', -1) // Rejected items
                      ->orWhere(function($q) {
                          $q->where('is_approved', null)
                            ->where('resubmission_count', '>', 0); // Re-approval items
                      });
            })
            ->with(['images', 'category'])
            ->get();

        $items = $rejectedClothes->map(function($cloth) {
            $notification = $this->findRejectionNotification($cloth->id);
            $reason = $notification ? ($notification->data['reject_reason'] ?? 'N/A') : 'No reason found';
            
            return [
                'id' => $cloth->id,
                'title' => $cloth->title,
                'image' => $cloth->images->count() ? asset('storage/' . $cloth->images->first()->image_path) : null,
                'category' => $cloth->category->name ?? 'N/A',
                'reason' => $reason,
                'rejected_at' => $notification ? $notification->created_at : null,
                'is_approved' => $cloth->is_approved,
                'rent_price' => (float)$cloth->rent_price,
                'updated_at' => $cloth->updated_at,
                'resubmission_count' => $cloth->resubmission_count,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'rejectedItems' => $items,
                'count' => $items->count()
            ]
        ]);
    }

    /**
     * Show specific rejected item with rejection details
     */
    public function show($id)
    {
        $cloth = Cloth::where('user_id', Auth::id())
            ->where(function($query) {
                $query->where('is_approved', -1) // Rejected items
                      ->orWhere(function($q) {
                          $q->where('is_approved', null)
                            ->where('resubmission_count', '>', 0); // Re-approval items
                      });
            })
            ->with(['images', 'availabilityBlocks', 'category', 'brand', 'fabric', 'color', 'bottomType', 'size', 'fitType', 'condition'])
            ->find($id);
            
        if (!$cloth) {
            return response()->json([
                'success' => false,
                'message' => 'Rejected item not found'
            ], 404);
        }

        $rejectionNotification = $this->findRejectionNotification($cloth->id);

        return response()->json([
            'success' => true,
            'data' => [
                'cloth' => $cloth,
                'rejection_reason' => $rejectionNotification ? ($rejectionNotification->data['reject_reason'] ?? null) : null,
                'rejected_at' => $rejectionNotification ? $rejectionNotification->created_at : null,
            ]
        ]);
    }
    
    /**
     * Get reference data (categories, colors, etc.) for updating a rejected item
     */
    public function getFormData()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => Category::orderBy('name')->get(),
                'fabricTypes' => FabricType::orderBy('name')->get(),
                'colors' => Color::orderBy('name')->get(),
                'bottomTypes' => BottomType::orderBy('name')->get(),
                'fitTypes' => BodyTypeFit::orderBy('name')->get(),
                'sizes' => Size::all(),
                'brands' => Brand::orderBy('name')->get(),
                'garmentConditions' => GarmentCondition::all(),
            ]
        ]);
    }

    /**
     * Update rejected item and resubmit for approval
     */
    public function update(Request $request, $id)
    {
        $cloth = Cloth::where('user_id', Auth::id())
            ->where(function($query) {
                $query->where('is_approved', -1) // Rejected items
                      ->orWhere(function($q) {
                          $q->where('is_approved', null)
                            ->where('resubmission_count', '>', 0); // Re-approval items
                      });
            })
            ->find($id);
            
        if (!$cloth) {
            return response()->json([
                'success' => false,
                'message' => 'Rejected item not found'
            ], 404);
        }

        // Validate the request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'category' => 'required|exists:category,id',
            'gender' => 'required|in:Boy,Girl,Men,Women',
            'brand' => 'nullable|exists:brands,id',
            'fabric' => 'nullable|exists:fabric_types,id',
            'color' => 'nullable|exists:colors,id',
            'bottom_type' => 'nullable|exists:bottom_types,id',
            'size' => 'required|exists:sizes,id',
            'fit_type' => 'nullable|exists:body_type_fits,id',
            'condition' => 'required|exists:garment_conditions,id',
            'defects' => 'nullable|string',
            'rent_price' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'chest_bust' => 'nullable|numeric',
            'waist' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'shoulder' => 'nullable|numeric',
            'sleeve_length' => 'nullable|numeric',
            'measurement_unit' => 'nullable|string|in:inch,cm',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
        ]);

        // Handle Image Deletions (if provided as array of IDs)
        if ($request->filled('deleted_images') && is_array($request->input('deleted_images'))) {
            $imagesToDelete = \App\Models\ClothImage::whereIn('id', $request->input('deleted_images'))
                                                    ->where('cloth_id', $cloth->id) 
                                                    ->get();

            foreach ($imagesToDelete as $img) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($img->image_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($img->image_path);
                }
                $img->delete();
            }
        }

        // Handle New Image Uploads
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('clothes', $filename, 'public');
                
                \App\Models\ClothImage::create([
                    'cloth_id' => $cloth->id,
                    'image_path' => $path
                ]);
            }
        }

        $updateData = $request->except(['new_images', 'deleted_images']);
        
        $mapping = [
            'category' => 'category_id',
            'brand' => 'brand_id',
            'fabric' => 'fabric_id',
            'color' => 'color_id',
            'bottom_type' => 'bottom_type_id',
            'size' => 'size_id',
            'fit_type' => 'fit_type_id',
            'condition' => 'condition_id',
        ];
        
        foreach ($mapping as $requestKey => $dbColumn) {
            if (isset($updateData[$requestKey])) {
                $updateData[$dbColumn] = $updateData[$requestKey];
                unset($updateData[$requestKey]);
            }
        }

        $cloth->update($updateData);

        // Reset approval status to pending and increment resubmission count
        $cloth->is_approved = null; 
        $cloth->resubmission_count = $cloth->resubmission_count + 1; 
        $cloth->save();

        // Send notification to admin about resubmission
        Notification::create([
            'user_id' => 1, // Admin ID
            'title' => 'Item Resubmitted for Approval',
            'message' => "Item '{$cloth->title}' has been updated and resubmitted for approval.",
            'type' => 'info',
            'icon' => 'bi-arrow-clockwise',
            'data' => [
                'cloth_id' => $cloth->id,
                'cloth_title' => $cloth->title,
                'action' => 'resubmitted'
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item updated and resubmitted for approval successfully!',
            'data' => [
                'cloth' => $cloth->load('images')
            ]
        ]);
    }

    /**
     * Find rejection notification for a specific cloth
     */
    private function findRejectionNotification($clothId)
    {
        try {
            return Notification::where('user_id', Auth::id())
                ->where('type', 'warning')
                ->whereRaw("JSON_EXTRACT(data, '$.cloth_id') = ?", [$clothId])
                ->whereRaw("JSON_EXTRACT(data, '$.reject_reason') IS NOT NULL")
                ->latest()
                ->first();
        } catch (\Exception $e) {
            return Notification::where('user_id', Auth::id())
                ->where('type', 'warning')
                ->latest()
                ->get()
                ->filter(function ($notification) use ($clothId) {
                    return isset($notification->data['cloth_id']) 
                        && $notification->data['cloth_id'] == $clothId
                        && isset($notification->data['reject_reason']);
                })
                ->first();
        }
    }
}
