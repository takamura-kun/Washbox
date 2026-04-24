<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryCostHistoryController extends Controller
{
    public function index($item)
    {
        return response()->json(['message' => 'Cost history feature coming soon']);
    }

    public function create($item)
    {
        return view('admin.inventory.cost-history.create', compact('item'));
    }

    public function store(Request $request, $item)
    {
        return redirect()->back()->with('success', 'Cost history recorded');
    }

    public function edit($item, $costHistory)
    {
        return view('admin.inventory.cost-history.edit', compact('item', 'costHistory'));
    }

    public function update(Request $request, $item, $costHistory)
    {
        return redirect()->back()->with('success', 'Cost history updated');
    }

    public function destroy($item, $costHistory)
    {
        return redirect()->back()->with('success', 'Cost history deleted');
    }

    public function chartData($item)
    {
        return response()->json(['labels' => [], 'data' => []]);
    }
}
