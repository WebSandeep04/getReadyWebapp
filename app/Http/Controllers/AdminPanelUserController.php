<?php

namespace App\Http\Controllers;

use App\Models\AdminPanelUser;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminPanelUserController extends Controller
{
    public function index()
    {
        $total = AdminPanelUser::count();
        $roles = Role::all();
        return view('admin.screens.admin_panel_user', compact('total', 'roles'));
    }

    public function json()
    {
        return response()->json(AdminPanelUser::with('role')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:admin_panel_users,username',
            'email' => 'required|email|unique:admin_panel_users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        AdminPanelUser::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $user = AdminPanelUser::findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:admin_panel_users,username,' . $id,
            'email' => 'required|email|unique:admin_panel_users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $user = AdminPanelUser::findOrFail($id);
        
        // Prevent deleting the very last admin for safety
        if ($user->username === 'admin') {
             // In a real app we might check if they are the only one left
        }

        $user->delete();
        return response()->json(['success' => true]);
    }
}
