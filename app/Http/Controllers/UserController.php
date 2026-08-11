<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\State;
use App\Models\City;

class UserController extends Controller
{
    public function index()
    {
        $showFilters = false;
        return view('admin.screens.user', [
            'showFilters' => $showFilters,
            'totalUsers' => User::count(),
        ]);
    }

    // Fetch all users (AJAX)
    public function fetch()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Update user (AJAX)
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'age' => 'nullable|integer|min:1|max:120',
            'is_gst' => 'required|boolean',
            'gstin' => 'nullable|string|max:15',
            'gst_number' => 'nullable|string|max:50',
            'gender' => 'required|in:Boy,Girl,Men,Women',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['name', 'email', 'phone', 'address', 'city', 'pincode', 'age', 'gstin', 'gst_number', 'gender', 'is_gst']);
        
        $user->update($data);
        return response()->json(['success' => true, 'user' => $user]);
    }

    // Delete user (AJAX)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['success' => true]);
    }

    public function profile()
    {
        $user = Auth::user();
        $showFilters = false;
        return view('profile', compact('user', 'showFilters'));
    }

    // Update user profile (AJAX)
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
            'pincode' => 'nullable|string|max:10',
            'is_gst' => 'required|boolean',
            'gstin' => 'required_if:is_gst,1|nullable|string|max:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'gender' => 'required|in:Boy,Girl,Men,Women',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'email', 'phone', 'address', 'gstin', 'gender', 'is_gst', 'state', 'city', 'pincode']);
        
        // Ensure GST is preserved
        $data['gst_number'] = $data['gstin'] ?? null;

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            
            $image = $request->file('profile_image');
            $path = $image->store('profile_images', 'public');
            $data['profile_image'] = $path;
        }

        // 💡 Security Enforcements: Lock identity fields to verified sources
        
        // Aadhaar always dictates the individual's full name if verified
        if ($user->is_aadhaar_verified) {
            if (isset($user->aadhaar_details['name'])) {
                $data['name'] = $user->aadhaar_details['name'];
            }
            
            // Aadhaar always dictates the primary address if verified
            if (isset($user->aadhaar_details['address']) && is_array($user->aadhaar_details['address'])) {
                $addr = $user->aadhaar_details['address'];
                $parts = [];
                if (!empty($addr['house'])) $parts[] = $addr['house'];
                if (!empty($addr['street'])) $parts[] = $addr['street'];
                if (!empty($addr['loc'])) $parts[] = $addr['loc'];
                if (!empty($addr['dist'])) $parts[] = $addr['dist'];
                if (!empty($addr['state'])) $parts[] = $addr['state'];
                if (!empty($addr['pc'])) $parts[] = $addr['pc'];
                
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

    // Update user address only (AJAX)
    public function updateAddress(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:255',
            'state' => 'required|string',
            'city' => 'required|string',
            'pincode' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['address', 'state', 'city', 'pincode']));

        return response()->json([
            'success' => true, 
            'message' => 'Address updated successfully!',
            'user' => $user->fresh()
        ]);
    }
}
