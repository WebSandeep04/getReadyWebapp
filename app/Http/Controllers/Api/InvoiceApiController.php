<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class InvoiceApiController extends Controller
{
    /**
     * Get a paginated list of invoices for the authenticated user.
     */
        public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Fetch invoices where the user is either the recipient OR the issuer!
        $invoices = \App\Models\Invoice::where(function ($query) use ($userId) {
                $query->where('issued_to_id', $userId)
                      ->orWhere('issued_by_id', $userId);
            })
            ->latest()
            ->get(); 

        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }


    /**
     * Get the details of a specific invoice.
     */
    public function show($id)
    {
        $invoice = Invoice::findOrFail($id);
        $user = Auth::user();
        
        // Check if user is the issuer (Seller) or recipient (Buyer/Seller)
        if ($invoice->issued_to_id == $user->id || $invoice->issued_by_id == $user->id) {
            return response()->json([
                'success' => true,
                'data' => $invoice
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access to invoice.'
        ], 403);
    }

    /**
     * Download the invoice PDF.
     */
        /**
     * Download the invoice PDF.
     */
    public function download(Request $request, $id)
    {
        // 1. Get the token from the URL query string: ?token=...
        $token = $request->query('token');
        
        // 2. Find the user manually using Sanctum
        $user = null;
        if ($token) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable; // Gets the User model
            }
        }

        // 3. If no valid user is found, stop here
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated or invalid token.'
            ], 401);
        }

        $invoice = Invoice::findOrFail($id);
        
        // Check if user is the issuer (Seller) or recipient (Buyer/Seller)
        if ($invoice->issued_to_id == $user->id || $invoice->issued_by_id == $user->id) {
            $path = 'public/' . $invoice->pdf_path;
            
            if (Storage::exists($path)) {
                return Storage::download($path);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Invoice file not found.'
            ], 404);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access to invoice.'
        ], 403);
    }

}
