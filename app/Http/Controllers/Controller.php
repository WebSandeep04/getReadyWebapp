<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    protected function cleanPhone(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->phone;
            // Remove +91 or 0 from start
            $phone = preg_replace('/^(?:\+91|0)/', '', $phone);
            // Take only the first 10 digits
            $phone = substr($phone, 0, 10);
            $request->merge(['phone' => $phone]);
        }
    }
}
