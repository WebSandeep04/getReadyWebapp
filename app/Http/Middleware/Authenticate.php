<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        
        // Store the intended URL in session so user can be redirected after login
        $request->session()->put('url.intended', $request->fullUrl());
        
        // Return login route with redirect parameter
        return route('login', ['redirect' => $request->fullUrl()]);
    }
} 