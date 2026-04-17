<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cloth;
use App\Models\Notification;
use App\Services\PriceCalculatorService;
use Illuminate\Support\Facades\Storage;
use App\Models\ClothImage;

class ClothController extends Controller
{
    public function clothApproval()
    {
        return view('admin.screens.cloth_approval');
    }

    // Fetch all clothes (AJAX)
    public function fetchClothes(Request $request)
    {
        $query = Cloth::with([
            'images', 
            'user', 
            'category', 
            'brand',
            'fabric', 
            'color', 
            'size', 
            'bottomType', 
            'fitType',
            'condition'
        ]);
        
        // Apply status filter if provided
        if ($request->has('status')) {
            $status = $request->get('status');
            switch ($status) {
                case 'pending':
                    $query->where('is_approved', null);
                    break;
                case 'approved':
                    $query->where('is_approved', 1);
                    break;
                case 'rejected':
                    $query->where('is_approved', -1);
                    break;
                case 're-approval':
                    $query->where('is_approved', null)
                          ->where('resubmission_count', '>', 0); // Items that have been resubmitted
                    break;
            }
        }
        
        if ($request->has('limit')) {
            $query->limit($request->limit);
        }
        
        $clothesLimited = $query->latest()->get();
        
        // Convert objects to display format efficiently
        $formattedClothes = $clothesLimited->map(function ($cloth) {
            // Map relationships to flat names
            $cloth->category_name = $cloth->category ? $cloth->category->name : 'Unknown';
            $cloth->brand_name = $cloth->brand ? $cloth->brand->name : 'Unknown';
            $cloth->fabric_name = $cloth->fabric ? $cloth->fabric->name : 'Unknown';
            $cloth->color_name = $cloth->color ? $cloth->color->name : 'Unknown';
            $cloth->size_name = $cloth->size ? $cloth->size->name : 'Unknown';
            $cloth->bottom_type_name = $cloth->bottomType ? $cloth->bottomType->name : 'Unknown';
            $cloth->fit_type_name = $cloth->fitType ? $cloth->fitType->name : 'Unknown';
            $cloth->condition_name = $cloth->condition ? $cloth->condition->name : 'Unknown';
            
            // Format timestamps
            $cloth->created_at_formatted = $cloth->created_at ? $cloth->created_at->toISOString() : null;
            $cloth->updated_at_formatted = $cloth->updated_at ? $cloth->updated_at->toISOString() : null;
            
            // Convert to array and remove relationship objects to avoid [object Object] in JS
            $data = $cloth->toArray();
            
            // Explicitly unset relationships to ensure flattened string versions are used
            $relationsToUnset = [
                'category', 'brand', 'fabric', 'color', 'size', 
                'bottomType', 'bottom_type', 'fitType', 'fit_type', 'condition'
            ];
            
            foreach ($relationsToUnset as $rel) {
                if (isset($data[$rel]) && (is_array($data[$rel]) || is_object($data[$rel]))) {
                    unset($data[$rel]);
                }
            }

            // Re-assign flattened names
            $data['category'] = $cloth->category_name;
            $data['brand'] = $cloth->brand_name;
            $data['fabric'] = $cloth->fabric_name;
            $data['color'] = $cloth->color_name;
            $data['size'] = $cloth->size_name;
            $data['bottom_type'] = $cloth->bottom_type_name;
            $data['fit_type'] = $cloth->fit_type_name;
            $data['condition'] = $cloth->condition_name;
            $data['user_name'] = $cloth->user ? $cloth->user->name : 'Unknown';
            
            // Full Pricing Breakdown for Admin (using standard 20/20 display model for simplicity)
            $pricing = (new PriceCalculatorService())->calculate($cloth, 4);
            $data['display_rent_price'] = $pricing['base_rent'] + $pricing['buyer_comm'];
            $data['seller_rent'] = $pricing['base_rent'] - $pricing['seller_comm'];
            $data['base_rent'] = $pricing['base_rent'];
            
            // Purchase Pricing
            if ($cloth->is_purchased) {
                $purchasePricing = (new PriceCalculatorService())->calculatePurchase($cloth);
                $data['display_selling_price'] = $purchasePricing['base_price'] + $purchasePricing['buyer_comm'];
                $data['seller_selling_price'] = $purchasePricing['base_price'] - $purchasePricing['seller_comm'];
                $data['base_selling_price'] = $cloth->selling_price;
            } else {
                $data['display_selling_price'] = null;
                $data['seller_selling_price'] = null;
                $data['base_selling_price'] = null;
            }
            
            // Intermediate prices for transparency
            $data['buyer_see_rent'] = $data['display_rent_price'];
            $data['seller_see_rent'] = $data['seller_rent'];
            
            return $data;
        });

        // Global Stats
        $stats = [
            'total' => Cloth::count(),
            'approved' => Cloth::where('is_approved', 1)->count(),
            'pending' => Cloth::where('is_approved', null)->where('resubmission_count', 0)->count(),
            'reapproval' => Cloth::where('is_approved', null)->where('resubmission_count', '>', 0)->count(),
            'rejected' => Cloth::where('is_approved', -1)->count(),
            'total_rent' => Cloth::sum('rent_price'),
            'total_security' => Cloth::sum('security_deposit'),
            'total_purchase' => Cloth::where('is_purchased', 1)->sum('selling_price'),
        ];
        
        return response()->json([
            'clothes' => $formattedClothes,
            'stats' => $stats
        ]);
    }

    public function approveCloth($id)
    {
        $cloth = Cloth::with('user')->findOrFail($id);
        
        // Prevent approving rejected items
        if ($cloth->is_approved === -1) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot approve a rejected item. User must resubmit it first.'
            ], 400);
        }

        $cloth->is_approved = 1; // Use integer 1 instead of true
        $cloth->save();

        // Send notification to the user
        if ($cloth->user) {
            Notification::create([
                'user_id' => $cloth->user->id,
                'title' => 'Item Approved',
                'message' => "Your item '{$cloth->title}' has been approved and is now live on our platform!",
                'type' => 'success',
                'icon' => 'bi-check-circle',
                'data' => [
                    'cloth_id' => $cloth->id,
                    'cloth_title' => $cloth->title
                ]
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function rejectCloth(Request $request, $id)
    {
        $request->validate([
            'reject_reason' => 'required|string|max:500'
        ]);

        $cloth = Cloth::with('user')->findOrFail($id);
        
        // Prevent rejecting approved items
        if ($cloth->is_approved === 1) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot reject an approved item. Please approve it first.'
            ], 400);
        }
        
        // Allow rejecting pending, rejected, and re-approval items
        $cloth->is_approved = -1; // Use integer -1 for rejected
        $cloth->save();

        // Send notification to the user with rejection reason
        if ($cloth->user) {
            Notification::create([
                'user_id' => $cloth->user->id,
                'title' => 'Item Rejected',
                'message' => "Your item '{$cloth->title}' has been rejected. Reason: {$request->reject_reason}. Please review and resubmit.",
                'type' => 'warning',
                'icon' => 'bi-exclamation-triangle',
                'data' => [
                    'cloth_id' => $cloth->id,
                    'cloth_title' => $cloth->title,
                    'reject_reason' => $request->reject_reason
                ]
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function getRejectionReason($id)
    {
        $notifications = Notification::where('type', 'warning')
            ->whereRaw("JSON_EXTRACT(data, '$.cloth_id') = ?", [$id])
            ->whereRaw("JSON_EXTRACT(data, '$.reject_reason') IS NOT NULL")
            ->orderByDesc('created_at')
            ->get();

        if ($notifications->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No rejection reasons found for this item.'
            ], 404);
        }

        $reasons = $notifications->map(function ($n) {
            return [
                'reason' => $n->data['reject_reason'] ?? null,
                'rejected_at' => $n->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'reasons' => $reasons,
        ]);
    }

    public function uploadImages(Request $request, $id)
    {
        $cloth = Cloth::findOrFail($id);

        if ($cloth->is_approved === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify images for an already approved item.'
            ], 400);
        }

        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('clothes', 'public');
                $clothImage = $cloth->images()->create(['image_path' => $path]);
                $uploadedImages[] = [
                    'id' => $clothImage->id,
                    'image_path' => $path
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'images' => $uploadedImages
        ]);
    }

    public function destroyImage($imageId)
    {
        $image = ClothImage::with('cloth')->findOrFail($imageId);
        
        if ($image->cloth && $image->cloth->is_approved === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete images for an already approved item.'
            ], 400);
        }
        
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
