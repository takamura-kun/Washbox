<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\BranchStock;
use App\Models\InventoryPurchase;
use App\Models\InventoryCostHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryPurchaseService
{
    /**
     * Record a new purchase for a specific branch and update inventory
     */
    public function recordPurchase(
        int $branchId,
        InventoryItem $item,
        string $purchaseUnit,
        int $quantityPurchased,
        float $costPrice,
        string $purchaseDate,
        ?string $supplier = null,
        ?string $notes = null,
        ?int $createdBy = null
    ): InventoryPurchase {
        try {
            return DB::transaction(function () use (
                $branchId,
                $item,
                $purchaseUnit,
                $quantityPurchased,
                $costPrice,
                $purchaseDate,
                $supplier,
                $notes,
                $createdBy
            ) {
                // Calculate total cost
                $totalCost = $quantityPurchased * $costPrice;

                // Create purchase record
                $purchase = InventoryPurchase::create([
                    'inventory_item_id' => $item->id,
                    'branch_id' => $branchId,
                    'purchase_unit' => $purchaseUnit,
                    'quantity_purchased' => $quantityPurchased,
                    'cost_price' => $costPrice,
                    'total_cost' => $totalCost,
                    'purchase_date' => $purchaseDate,
                    'supplier' => $supplier,
                    'notes' => $notes,
                    'created_by' => $createdBy,
                ]);

                // Get or create branch stock
                $branchStock = $item->getOrCreateBranchStock($branchId);

                // Track cost price change if different
                $oldCostPrice = $branchStock->cost_price;
                if ($oldCostPrice != $costPrice) {
                    $this->trackCostChange(
                        $branchId,
                        $item,
                        $oldCostPrice,
                        $costPrice,
                        $purchaseDate,
                        $costPrice > ($oldCostPrice ?? 0) ? 'price_increase' : 'price_decrease',
                        "New purchase at ₱{$costPrice}",
                        $createdBy
                    );
                }

                // Update branch stock cost price to latest
                $branchStock->update(['cost_price' => $costPrice]);

                // Add stock to branch inventory
                $branchStock->addStock(
                    (float) $quantityPurchased,
                    "purchase_recorded"
                );

                Log::info("Purchase recorded for branch {$branchId}, item {$item->id}: {$quantityPurchased} {$purchaseUnit} @ ₱{$costPrice}");

                return $purchase;
            });
        } catch (\Exception $e) {
            Log::error('Error recording purchase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Track cost price change for a branch
     */
    public function trackCostChange(
        int $branchId,
        InventoryItem $item,
        ?float $oldCostPrice,
        float $newCostPrice,
        string $effectiveDate,
        string $reason = 'other',
        ?string $notes = null,
        ?int $changedBy = null
    ): InventoryCostHistory {
        try {
            if ($reason === 'other' && $oldCostPrice) {
                $reason = $newCostPrice > $oldCostPrice ? 'price_increase' : 'price_decrease';
            }

            $history = InventoryCostHistory::create([
                'inventory_item_id' => $item->id,
                'branch_id' => $branchId,
                'old_cost_price' => $oldCostPrice,
                'new_cost_price' => $newCostPrice,
                'effective_date' => $effectiveDate,
                'reason' => $reason,
                'notes' => $notes,
                'changed_by' => $changedBy,
            ]);

            Log::info("Cost change tracked for branch {$branchId}, item {$item->id}: ₱{$oldCostPrice} → ₱{$newCostPrice}");

            return $history;
        } catch (\Exception $e) {
            Log::error('Error tracking cost change: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get average cost price for a branch
     */
    public function getAverageCostPrice(int $branchId, InventoryItem $item): float
    {
        $purchases = $item->purchases()
            ->where('branch_id', $branchId)
            ->get();

        if ($purchases->isEmpty()) {
            $branchStock = $item->branchStocks()->where('branch_id', $branchId)->first();
            return (float) ($branchStock?->cost_price ?? $item->unit_cost);
        }

        $totalCost = $purchases->sum('total_cost');
        $totalQuantity = $purchases->sum('quantity_purchased');

        if ($totalQuantity == 0) {
            return (float) $item->unit_cost;
        }

        return (float) ($totalCost / $totalQuantity);
    }

    /**
     * Get latest purchase price for a branch
     */
    public function getLatestPurchasePrice(int $branchId, InventoryItem $item): ?float
    {
        $latestPurchase = $item->purchases()
            ->where('branch_id', $branchId)
            ->latest('purchase_date')
            ->first();

        return $latestPurchase ? (float) $latestPurchase->cost_price : null;
    }

    /**
     * Get total spent on an item in a branch
     */
    public function getTotalSpent(int $branchId, InventoryItem $item): float
    {
        return (float) $item->purchases()
            ->where('branch_id', $branchId)
            ->sum('total_cost');
    }

    /**
     * Get purchase history for a branch
     */
    public function getPurchaseHistory(int $branchId, InventoryItem $item, ?string $startDate = null, ?string $endDate = null)
    {
        $query = $item->purchases()->where('branch_id', $branchId);

        if ($startDate && $endDate) {
            $query->whereBetween('purchase_date', [$startDate, $endDate]);
        }

        return $query->latest('purchase_date')->get();
    }

    /**
     * Get cost history for a branch
     */
    public function getCostHistory(int $branchId, InventoryItem $item, ?string $startDate = null, ?string $endDate = null)
    {
        $query = $item->costHistory()->where('branch_id', $branchId);

        if ($startDate && $endDate) {
            $query->whereBetween('effective_date', [$startDate, $endDate]);
        }

        return $query->latest('effective_date')->get();
    }

    /**
     * Get profit margin for an item in a branch
     */
    public function getProfitMargin(int $branchId, InventoryItem $item, float $sellingPrice): float
    {
        $averageCost = $this->getAverageCostPrice($branchId, $item);
        if ($sellingPrice == 0) {
            return 0;
        }
        return (($sellingPrice - $averageCost) / $sellingPrice) * 100;
    }

    /**
     * Get profit per unit for an item in a branch
     */
    public function getProfitPerUnit(int $branchId, InventoryItem $item, float $sellingPrice): float
    {
        $averageCost = $this->getAverageCostPrice($branchId, $item);
        return $sellingPrice - $averageCost;
    }
}
