<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $total = Role::count();
        return view('admin.screens.role', compact('total'));
    }

    public function json()
    {
        return response()->json(Role::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        Role::create([
            'name' => strtolower($request->name),
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
        ]);

        $role->update([
            'name' => strtolower($request->name),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        // Don't allow deleting 'admin' role if it's the last one or something, 
        // but for now simple delete:
        $role = Role::findOrFail($id);
        
        // Prevent deleting admin role for safety
        if ($role->name === 'admin') {
            return response()->json(['error' => 'Cannot delete admin role'], 403);
        }

        $role->delete();
        return response()->json(['success' => true]);
    }
}
