<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\StockHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionInventoryService
{
    /**
     * Process inventory deduction when a promotion is applied to a laundry order
     */
    public function processPromotionUsage(Promotion $promotion, int $branchId, int $loads = 1, ?int $laundryId = null): array
    {
        try {
            DB::beginTransaction();

            $deductions = [];
            $errors = [];

            // Get all active promotion items
            $promotionItems = $promotion->promotionItems()->where('is_active', true)->with('inventoryItem')->get();

            if ($promotionItems->isEmpty()) {
                DB::commit();
                return [
                    'success' => true,
                    'message' => 'No inventory items to deduct',
                    'deductions' => [],
                ];
            }

            // Check stock availability first
            foreach ($promotionItems as $item) {
                $quantityNeeded = $item->quantity_per_use * $loads;
                $branchStock = $item->inventoryItem->branchStocks()
                    ->where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->first();

                if (!$branchStock) {
                    $errors[] = "No stock record for {$item->inventoryItem->name} at this branch";
                    continue;
                }

                if ($branchStock->current_stock < $quantityNeeded) {
                    $errors[] = "{$item->inventoryItem->name}: Need {$quantityNeeded}, Available {$branchStock->current_stock}";
                }
            }

            // If any errors, rollback
            if (!empty($errors)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Insufficient inventory',
                    'errors' => $errors,
                ];
            }

            // Deduct inventory
            foreach ($promotionItems as $item) {
                $inventoryItem = $item->inventoryItem;
                $quantityNeeded = $item->quantity_per_use * $loads;

                $branchStock = $inventoryItem->branchStocks()
                    ->where('branch_id', $branchId)
                    ->first();

                // Deduct stock
                $branchStock->decrement('current_stock', $quantityNeeded);

                // Create stock history
                StockHistory::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'branch_id' => $branchId,
                    'type' => 'usage',
                    'quantity' => -$quantityNeeded,
                    'reference_type' => 'promotion',
                    'reference_id' => $promotion->id,
                    'notes' => "Promotion: {$promotion->name}" . ($laundryId ? " (Laundry #{$laundryId})" : ''),
                    'performed_by' => auth()->id(),
                ]);

                $deductions[] = [
                    'item_id' => $inventoryItem->id,
                    'item_name' => $inventoryItem->name,
                    'quantity' => $quantityNeeded,
                    'unit' => $inventoryItem->distribution_unit ?? 'unit',
                    'remaining_stock' => $branchStock->current_stock,
                ];

                Log::info("Inventory deducted for promotion", [
                    'promotion_id' => $promotion->id,
                    'item' => $inventoryItem->name,
                    'quantity' => $quantityNeeded,
                    'branch_id' => $branchId,
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Inventory deducted successfully',
                'deductions' => $deductions,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deducting promotion inventory: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error processing inventory: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Check if promotion has sufficient inventory available
     */
    public function checkInventoryAvailability(Promotion $promotion, int $branchId, int $loads = 1): array
    {
        $promotionItems = $promotion->promotionItems()->where('is_active', true)->with('inventoryItem')->get();

        if ($promotionItems->isEmpty()) {
            return [
                'available' => true,
                'message' => 'No inventory items required',
            ];
        }

        $unavailableItems = [];

        foreach ($promotionItems as $item) {
            $quantityNeeded = $item->quantity_per_use * $loads;
            $branchStock = $item->inventoryItem->branchStocks()
                ->where('branch_id', $branchId)
                ->first();

            if (!$branchStock || $branchStock->current_stock < $quantityNeeded) {
                $unavailableItems[] = [
                    'item' => $item->inventoryItem->name,
                    'needed' => $quantityNeeded,
                    'available' => $branchStock->current_stock ?? 0,
                ];
            }
        }

        if (!empty($unavailableItems)) {
            return [
                'available' => false,
                'message' => 'Insufficient inventory for promotion',
                'unavailable_items' => $unavailableItems,
            ];
        }

        return [
            'available' => true,
            'message' => 'All items available',
        ];
    }

    /**
     * Get promotion items summary
     */
    public function getPromotionItemsSummary(Promotion $promotion, int $branchId): array
    {
        $items = [];

        foreach ($promotion->promotionItems()->where('is_active', true)->with('inventoryItem')->get() as $item) {
            $branchStock = $item->inventoryItem->branchStocks()
                ->where('branch_id', $branchId)
                ->first();

            $items[] = [
                'name' => $item->inventoryItem->name,
                'quantity_per_use' => $item->quantity_per_use,
                'unit' => $item->inventoryItem->distribution_unit ?? 'unit',
                'current_stock' => $branchStock->current_stock ?? 0,
                'available' => ($branchStock->current_stock ?? 0) >= $item->quantity_per_use,
            ];
        }

        return $items;
    }
}
