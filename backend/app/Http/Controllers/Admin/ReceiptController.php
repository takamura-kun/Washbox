<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laundry;

class ReceiptController extends Controller
{
    public function show($laundryId)
    {
        try {
            $laundry = Laundry::with(['customer', 'branch', 'service', 'staff', 'inventoryItems', 'promotion'])
                ->findOrFail($laundryId);
            
            // Create addons alias for backward compatibility with the view
            $laundry->addons = $laundry->inventoryItems;
            
            return view('admin.receipts.show', compact('laundry'));
        } catch (\Exception $e) {
            \Log::error('Receipt Show Error: ' . $e->getMessage());
            return back()->with('error', 'Laundry record not found or has missing data.');
        }
    }
}
