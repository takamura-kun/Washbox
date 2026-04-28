<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryStockCountController extends Controller
{
    public function index()
    {
        return view('admin.inventory.stock-counts.index');
    }

    public function create()
    {
        return view('admin.inventory.stock-counts.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.inventory-stock-counts.index')->with('success', 'Stock count recorded');
    }

    public function show($id)
    {
        return view('admin.inventory.stock-counts.show', compact('id'));
    }

    public function edit($id)
    {
        return view('admin.inventory.stock-counts.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('admin.inventory-stock-counts.index')->with('success', 'Stock count updated');
    }

    public function destroy($id)
    {
        return redirect()->route('admin.inventory-stock-counts.index')->with('success', 'Stock count deleted');
    }
}
