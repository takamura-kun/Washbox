<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{InventoryItem, RetailSale, InventoryAdjustment, StockHistory, Branch};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $itemId = $request->input('item_id');
        $branchId = $request->input('branch_id');
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Ensure date range includes full days
        $dateFrom = $dateFrom . ' 00:00:00';
        $dateTo = $dateTo . ' 23:59:59';

        $items = InventoryItem::active()->orderBy('name')->get();
        $branches = Branch::active()->get();

        // Get selected item details
        $selectedItem = $itemId ? InventoryItem::find($itemId) : null;

        // Summary data
        $summary = $this->getMovementSummary($itemId, $branchId, $dateFrom, $dateTo);

        // Detailed movements
        $movements = $this->getDetailedMovements($itemId, $branchId, $dateFrom, $dateTo);

        return view('admin.inventory.movements.index', compact(
            'items',
            'branches',
            'selectedItem',
            'summary',
            'movements',
            'itemId',
            'branchId',
            'dateFrom',
            'dateTo'
        ));
    }

    private function getMovementSummary($itemId, $branchId, $dateFrom, $dateTo)
    {
        $query = $itemId ? "AND inventory_item_id = {$itemId}" : "";
        $branchQuery = $branchId ? "AND branch_id = {$branchId}" : "";

        // Retail Sales (Outgoing)
        $retailSales = RetailSale::query()
            ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(quantity) as total_quantity, SUM(total_amount) as total_value')
            ->first();

        // Adjustments (Outgoing - damaged, expired, lost, theft, spoilage)
        $adjustmentsOut = InventoryAdjustment::query()
            ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('type', ['damaged', 'expired', 'lost', 'theft', 'spoilage'])
            ->where('status', 'approved')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(quantity) as total_quantity, SUM(value_loss) as total_value')
            ->first();

        // Adjustments (Incoming - found, correction)
        $adjustmentsIn = InventoryAdjustment::query()
            ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('type', ['found', 'correction'])
            ->where('status', 'approved')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(quantity) as total_quantity')
            ->first();

        // Stock History (Usage in laundry services)
        $laundryUsage = StockHistory::query()
            ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('type', 'usage')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(ABS(quantity)) as total_quantity')
            ->first();

        return [
            'retail_sales' => [
                'quantity' => $retailSales->total_quantity ?? 0,
                'value' => $retailSales->total_value ?? 0,
            ],
            'laundry_usage' => [
                'quantity' => $laundryUsage->total_quantity ?? 0,
            ],
            'damaged' => [
                'quantity' => InventoryAdjustment::query()
                    ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->where('type', 'damaged')
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('quantity'),
                'value' => InventoryAdjustment::query()
                    ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->where('type', 'damaged')
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('value_loss'),
            ],
            'expired' => [
                'quantity' => InventoryAdjustment::query()
                    ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->where('type', 'expired')
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('quantity'),
                'value' => InventoryAdjustment::query()
                    ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->where('type', 'expired')
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('value_loss'),
            ],
            'lost' => [
                'quantity' => InventoryAdjustment::query()
                    ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->where('type', 'lost')
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('quantity'),
                'value' => InventoryAdjustment::query()
                    ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->where('type', 'lost')
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('value_loss'),
            ],
            'theft' => [
                'quantity' => InventoryAdjustment::query()
                    ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->where('type', 'theft')
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('quantity'),
                'value' => InventoryAdjustment::query()
                    ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->where('type', 'theft')
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('value_loss'),
            ],
            'total_outgoing' => [
                'quantity' => ($retailSales->total_quantity ?? 0) + 
                             ($laundryUsage->total_quantity ?? 0) + 
                             ($adjustmentsOut->total_quantity ?? 0),
                'value' => ($retailSales->total_value ?? 0) + 
                          ($adjustmentsOut->total_value ?? 0),
            ],
        ];
    }

    private function getDetailedMovements($itemId, $branchId, $dateFrom, $dateTo)
    {
        $movements = collect();

        // Retail Sales
        $retailSales = RetailSale::with(['inventoryItem', 'branch'])
            ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->map(fn($sale) => [
                'date' => $sale->created_at,
                'type' => 'Retail Sale',
                'item' => $sale->item_name ?? $sale->inventoryItem?->name ?? 'Unknown',
                'branch' => $sale->branch?->name ?? 'Unknown',
                'quantity' => $sale->quantity,
                'value' => $sale->total_amount,
                'reference' => $sale->sale_number,
            ]);
        $movements = $movements->concat($retailSales);

        // Adjustments
        $adjustments = InventoryAdjustment::with(['item', 'branch'])
            ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'approved')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->map(fn($adj) => [
                'date' => $adj->created_at,
                'type' => ucfirst($adj->type),
                'item' => $adj->item?->name ?? 'Unknown',
                'branch' => $adj->branch?->name ?? 'Unknown',
                'quantity' => $adj->quantity,
                'value' => $adj->value_loss,
                'reference' => "ADJ-{$adj->id}",
            ]);
        $movements = $movements->concat($adjustments);

        // Stock History (Laundry Usage)
        $usage = StockHistory::with(['inventoryItem', 'branch'])
            ->when($itemId, fn($q) => $q->where('inventory_item_id', $itemId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('type', 'usage')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->map(fn($hist) => [
                'date' => $hist->created_at,
                'type' => 'Laundry Usage',
                'item' => $hist->inventoryItem?->name ?? 'Unknown',
                'branch' => $hist->branch?->name ?? 'N/A',
                'quantity' => abs($hist->quantity),
                'value' => null,
                'reference' => $hist->notes ?? 'N/A',
            ]);
        $movements = $movements->concat($usage);

        return $movements->sortByDesc('date')->take(100);
    }
}
