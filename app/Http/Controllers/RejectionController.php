<?php

namespace App\Http\Controllers;

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

class RejectionController extends Controller
{
    /**
     * Show rejection management page
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
            ->with(['images', 'user', 'categoryRef'])
            ->get();

        // Attach latest rejection reason to each cloth
        foreach ($rejectedClothes as $cloth) {
            $notification = $this->findRejectionNotification($cloth->id);
            $cloth->latest_rejection_reason = $notification ? ($notification->data['reject_reason'] ?? 'N/A') : 'No reason found';
        }

        return view('rejections.index', compact('rejectedClothes'));
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
            ->with(['images', 'availabilityBlocks'])
            ->findOrFail($id);

        // Get the rejection notification for this cloth
        $rejectionNotification = $this->findRejectionNotification($cloth->id);

        // Get data for dropdowns
        $categories = Category::orderBy('name')->get();
        $fabricTypes = FabricType::orderBy('name')->get();
        $colors = Color::orderBy('name')->get();
        $bottomTypes = BottomType::orderBy('name')->get();
        $fitTypes = BodyTypeFit::orderBy('name')->get();
        $sizes = Size::all();

        return view('rejections.show', compact(
            'cloth', 
            'rejectionNotification', 
            'categories', 
            'fabricTypes', 
            'colors', 
            'bottomTypes', 
            'fitTypes',
            'sizes'
        ));
    }

    /**
     * Update rejected item and resubmit for approval
     */
    public function update(Request $request, $id)
    {
        // Debug: Log the request
        \Log::info("Update request received for cloth ID: {$id}");
        \Log::info("Request data: " . json_encode($request->all()));

        $cloth = Cloth::where('user_id', Auth::id())
            ->where(function($query) {
                $query->where('is_approved', -1) // Rejected items
                      ->orWhere(function($q) {
                          $q->where('is_approved', null)
                            ->where('resubmission_count', '>', 0); // Re-approval items
                      });
            })
            ->findOrFail($id);

        // Debug: Log the found cloth
        \Log::info("Found cloth: " . json_encode($cloth->toArray()));

        // Validate the request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'category' => 'required|exists:category,id',
            'gender' => 'required|in:Boy,Girl,Men,Women',
            'brand' => 'nullable|string|max:255',
            'fabric' => 'nullable|exists:fabric_types,id',
            'color' => 'nullable|exists:colors,id',
            'bottom_type' => 'nullable|exists:bottom_types,id',
            'size' => 'required|exists:sizes,id',
            'fit_type' => 'nullable|exists:body_type_fits,id',
            'condition' => 'required|in:Brand New,Like New,Excellent,Good,Fair',
            'defects' => 'nullable|string',
            'rent_price' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'chest_bust' => 'nullable|numeric',
            'waist' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'shoulder' => 'nullable|numeric',
            'sleeve_length' => 'nullable|numeric',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
        ]);

        // Debug: Log validation passed
        \Log::info("Validation passed for cloth ID: {$id}");

        // Handle Image Deletions
        if ($request->filled('deleted_images')) {
            $imagesToDelete = \App\Models\ClothImage::whereIn('id', $request->input('deleted_images'))
                                                    ->where('cloth_id', $cloth->id) // Security: Ensure images belong to this cloth
                                                    ->get();

            foreach ($imagesToDelete as $img) {
                // Delete physical file
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($img->image_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($img->image_path);
                }
                // Delete DB record
                $img->delete();
            }
        }

        // Handle New Image Uploads
        if ($request->hasFile('new_images')) {
            // Optional: If you want to replace all images, uncomment the next line
            // \App\Models\ClothImage::where('cloth_id', $cloth->id)->delete();
            
            foreach ($request->file('new_images') as $image) {
                // Generate a unique filename
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                
                // Store the file in the 'public/clothes' directory
                $path = $image->storeAs('clothes', $filename, 'public');
                
                // Create ClothImage record
                \App\Models\ClothImage::create([
                    'cloth_id' => $cloth->id,
                    'image_path' => $path // Store relative path like 'clothes/filename.jpg'
                ]);
            }
        }

        // Update the cloth
        $updateData = $request->except(['new_images', 'deleted_images']); // Exclude non-column fields
        \Log::info("Update data: " . json_encode($updateData));
        
        $cloth->update($updateData);

        // Reset approval status to pending and increment resubmission count
        $cloth->is_approved = null; // null means pending
        $cloth->resubmission_count = $cloth->resubmission_count + 1; // Increment resubmission count
        $cloth->save();

        // Debug: Log the updated cloth
        \Log::info("Updated cloth: " . json_encode($cloth->toArray()));

        // Send notification to admin about resubmission
        Notification::create([
            'user_id' => 1, // Assuming admin user ID is 1, adjust as needed
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

        // Debug: Log successful update
        \Log::info("Successfully updated and resubmitted cloth ID: {$id}");

        // Redirect to rejections index with success message
        return redirect('/rejections')
            ->with('success', 'Item updated and resubmitted for approval successfully!');
    }

    /**
     * Get rejection details for AJAX
     */
    public function getRejectionDetails($id)
    {
        $cloth = Cloth::where('user_id', Auth::id())
            ->where(function($query) {
                $query->where('is_approved', -1) // Rejected items
                      ->orWhere(function($q) {
                          $q->where('is_approved', null)
                            ->where('resubmission_count', '>', 0); // Re-approval items
                      });
            })
            ->findOrFail($id);

        $rejectionNotification = $this->findRejectionNotification($cloth->id);

        return response()->json([
            'cloth' => $cloth,
            'rejection_reason' => $rejectionNotification ? $rejectionNotification->data['reject_reason'] : null,
            'rejected_at' => $rejectionNotification ? $rejectionNotification->created_at : null
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
            // Fallback: get all warning notifications and filter in PHP
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
