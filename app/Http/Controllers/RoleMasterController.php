<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleMasterController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('module');
        return view('admin.screens.role_master', compact('roles', 'permissions'));
    }

    public function getRolePermissions($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json([
            'permission_ids' => $role->permissions->pluck('id')
        ]);
    }

    public function saveRolePermissions(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'array',
        ]);

        $role = Role::findOrFail($request->role_id);
        $role->permissions()->sync($request->permission_ids ?? []);

        return response()->json(['success' => true]);
    }
}
