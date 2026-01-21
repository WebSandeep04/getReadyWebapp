<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'gstin' => 'nullable|string|max:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'gender' => 'required|in:Boy,Girl,Men,Women',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user->update($request->only(['name', 'email', 'phone', 'address', 'gstin', 'gender']));
        return response()->json(['success' => true, 'user' => $user]);
    }

    // Delete user (AJAX)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['success' => true]);
    }

    // Show user profile page
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
            'gstin' => 'nullable|string|max:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'gender' => 'required|in:Boy,Girl,Men,Women',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()]);
        }

        $data = $request->only(['name', 'email', 'phone', 'address', 'gstin', 'gender']);

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

        $user->update($data);

        return response()->json([
            'success' => true, 
            'message' => 'Profile updated successfully!',
            'user' => $user->fresh()
        ]);
    }
}
