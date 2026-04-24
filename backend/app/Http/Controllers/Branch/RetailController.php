<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\RetailSale;
use App\Models\BranchStock;
use App\Models\InventoryItem;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetailController extends Controller
{
    private function getBranchId()
    {
        if (auth()->guard('branch')->check()) {
            return auth()->guard('branch')->user()->id;
        }
        return auth()->user()->branch_id;
    }

    public function index(Request $request)
    {
        $branchId = $this->getBranchId();
        
        $query = RetailSale::where('branch_id', $branchId)
            ->with(['inventoryItem', 'seller']);

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->latest()->paginate(20);

        // Summary
        $summary = [
            'total_sales' => RetailSale::where('branch_id', $branchId)->count(),
            'today_sales' => RetailSale::where('branch_id', $branchId)->whereDate('created_at', today())->count(),
            'total_revenue' => RetailSale::where('branch_id', $branchId)->sum('total_amount'),
            'today_revenue' => RetailSale::where('branch_id', $branchId)->whereDate('created_at', today())->sum('total_amount'),
        ];

        return view('branch.retail.index', compact('sales', 'summary'));
    }

    public function create()
    {
        $branchId = $this->getBranchId();
        
        // Get available items with stock
        $items = BranchStock::where('branch_id', $branchId)
            ->where('current_stock', '>', 0)
            ->with(['inventoryItem' => function($q) {
                $q->where('is_active', true);
            }])
            ->get()
            ->filter(function($stock) {
                return $stock->inventoryItem !== null;
            });

        return view('branch.retail.create', compact('items'));
    }

    public function store(Request $request)
    {
        $branchId = $this->getBranchId();

        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,gcash,card',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Check stock availability
            $branchStock = BranchStock::where('branch_id', $branchId)
                ->where('inventory_item_id', $validated['inventory_item_id'])
                ->lockForUpdate()
                ->first();

            if (!$branchStock || $branchStock->current_stock < $validated['quantity']) {
                return back()->with('error', 'Insufficient stock available!');
            }

            $inventoryItem = InventoryItem::find($validated['inventory_item_id']);

            // Create retail sale
            $sale = RetailSale::create([
                'sale_number' => RetailSale::generateSaleNumber(),
                'branch_id' => $branchId,
                'inventory_item_id' => $validated['inventory_item_id'],
                'item_name' => $inventoryItem->name,
                'quantity' => $validated['quantity'],
                'unit_price' => $validated['unit_price'],
                'total_amount' => $validated['quantity'] * $validated['unit_price'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'],
                'sold_by' => auth()->id(),
            ]);

            // Deduct stock
            $branchStock->decrement('current_stock', $validated['quantity']);
            $branchStock->update(['last_updated_at' => now()]);

            // Record financial transaction
            FinancialTransaction::create([
                'branch_id' => $branchId,
                'transaction_type' => 'retail_sale',
                'transactionable_type' => RetailSale::class,
                'transactionable_id' => $sale->id,
                'amount' => $sale->total_amount,
                'payment_method' => $validated['payment_method'],
                'description' => "Retail sale: {$inventoryItem->name} x{$validated['quantity']}",
                'transaction_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('branch.retail.index')
                ->with('success', 'Sale recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record sale: ' . $e->getMessage());
        }
    }

    public function quickSale(Request $request)
    {
        $branchId = $this->getBranchId();

        $validated = $request->validate([
            'items' => 'required|json',
            'payment_method' => 'required|in:cash,gcash,card',
        ]);

        $items = json_decode($validated['items'], true);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty']);
        }

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                // Check stock availability
                $branchStock = BranchStock::where('branch_id', $branchId)
                    ->where('inventory_item_id', $item['id'])
                    ->lockForUpdate()
                    ->first();

                if (!$branchStock || $branchStock->current_stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false, 
                        'message' => "Insufficient stock for {$item['name']}"
                    ]);
                }

                $inventoryItem = InventoryItem::find($item['id']);

                // Create retail sale
                $sale = RetailSale::create([
                    'sale_number' => RetailSale::generateSaleNumber(),
                    'branch_id' => $branchId,
                    'inventory_item_id' => $item['id'],
                    'item_name' => $inventoryItem->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_amount' => $item['quantity'] * $item['price'],
                    'payment_method' => $validated['payment_method'],
                    'sold_by' => auth()->id(),
                ]);

                // Deduct stock
                $branchStock->decrement('current_stock', $item['quantity']);
                $branchStock->update(['last_updated_at' => now()]);

                // Record financial transaction
                FinancialTransaction::create([
                    'branch_id' => $branchId,
                    'transaction_type' => 'retail_sale',
                    'transactionable_type' => RetailSale::class,
                    'transactionable_id' => $sale->id,
                    'amount' => $sale->total_amount,
                    'payment_method' => $validated['payment_method'],
                    'description' => "Retail sale: {$inventoryItem->name} x{$item['quantity']}",
                    'transaction_date' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Sale completed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Failed to complete sale: ' . $e->getMessage()
            ]);
        }
    }

    public function show(RetailSale $retail)
    {
        $branchId = $this->getBranchId();
        
        if ($retail->branch_id !== $branchId) {
            abort(403, 'Unauthorized access');
        }

        $retail->load(['inventoryItem', 'seller', 'branch']);

        return view('branch.retail.show', compact('retail'));
    }
}
