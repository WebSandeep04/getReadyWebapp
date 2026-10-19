<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Rules\ValidMumbaiPincode; 

class UserApiController extends Controller
{
        // Fetch logged-in user profile (API)
    public function getProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'address' => 'nullable|string|max:255',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'pincode' => ['nullable', 'string', 'max:10'], // Ensure this rule class exists
            'is_gst' => 'required|boolean',
            'gstin' => 'required_if:is_gst,1|nullable|string|max:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'gender' => 'required|in:Boy,Girl,Men,Women',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => $validator->errors()->first(), // Extract first error for the mobile popup
                'errors' => $validator->errors()
            ], 422);
        }
        $data = $request->only(['name', 'email', 'phone', 'address', 'gstin', 'gender', 'is_gst', 'state', 'city', 'pincode']);
        
        // Ensure GST is preserved
        $data['gst_number'] = $data['gstin'] ?? null;
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $image = $request->file('profile_image');
            $path = $image->store('profile_images', 'public');
            $data['profile_image'] = $path;
        }
        // Aadhaar logic...
        if ($user->is_aadhaar_verified) {
            if (isset($user->aadhaar_details['name'])) {
                $data['name'] = $user->aadhaar_details['name'];
            }
            if (isset($user->aadhaar_details['address']) && is_array($user->aadhaar_details['address'])) {
                $addr = $user->aadhaar_details['address'];
                $parts = array_filter([
                    $addr['house'] ?? '', $addr['street'] ?? '', 
                    $addr['loc'] ?? '', $addr['dist'] ?? '', 
                    $addr['state'] ?? '', $addr['pc'] ?? ''
                ]);
                $formattedAddress = implode(', ', $parts);
                if (!empty($formattedAddress)) {
                    $data['address'] = $formattedAddress;
                }
            }
        }
        $user->update($data);
        return response()->json([
            'success' => true, 
            'message' => 'Profile updated successfully!',
            'user' => $user->fresh()
        ]);
    }



    public function updateFcm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully.'
        ]);
    }

}