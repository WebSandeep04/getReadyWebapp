<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Session::has('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $admin = \App\Models\AdminPanelUser::where('username', $request->username)->first();

        if ($admin && \Illuminate\Support\Facades\Hash::check($request->password, $admin->password)) {
            Session::put('admin_logged_in', true);
            Session::put('admin_id', $admin->id);
            Session::put('admin_name', $admin->name);
            Session::put('admin_role', $admin->role->name);
            return redirect()->route('admin.dashboard')->with('success', 'Logged in successfully');
        }

        return back()->withErrors(['login_error' => 'Invalid credentials']);
    }

    public function logout()
    {
        Session::forget('admin_logged_in');
        return redirect()->route('admin.login')->with('success', 'Logged out successfully');
    }
}
