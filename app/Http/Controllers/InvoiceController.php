<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::where('issued_to_id', Auth::id())
            ->latest()
            ->paginate(15);
            
        return view('invoices.index', compact('invoices'));
    }

    public function download($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        $isAuthorized = false;

        // Check if Admin
        if (\Illuminate\Support\Facades\Session::has('admin_logged_in')) {
            $isAuthorized = true;
        } 
        elseif (Auth::check()) {
            $user = Auth::user();
            // Check if user is the issuer (Seller) or recipient (Buyer/Seller)
            if ($invoice->issued_to_id == $user->id || $invoice->issued_by_id == $user->id) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $path = 'public/' . $invoice->pdf_path;
        
        if (Storage::exists($path)) {
            return Storage::download($path);
        }
        
        abort(404, 'Invoice file not found.');
    }
}
