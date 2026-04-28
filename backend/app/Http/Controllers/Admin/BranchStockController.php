<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\InventoryItem;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchStockController extends Controller
{
    public function update(Request $request, BranchStock $branchStock)
    {
        $validated = $request->validate([
            'reorder_point' => 'required|integer|min:0',
            'max_level' => 'nullable|integer|min:0',
        ]);

        $branchStock->update($validated);

        return back()->with('success', 'Branch stock settings updated successfully');
    }

    public function bulkUpdate(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'stocks' => 'required|array',
            'stocks.*.branch_id' => 'required|exists:branches,id',
            'stocks.*.reorder_point' => 'required|integer|min:0',
            'stocks.*.max_level' => 'nullable|integer|min:0',
        ]);

        foreach ($validated['stocks'] as $stockData) {
            $branchStock = $item->getOrCreateBranchStock($stockData['branch_id']);
            $branchStock->update([
                'reorder_point' => $stockData['reorder_point'],
                'max_level' => $stockData['max_level'],
            ]);
        }

        return back()->with('success', 'Branch stock settings updated for all branches');
    }
}
