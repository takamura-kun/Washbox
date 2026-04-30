<?php

namespace App\Services;

use App\Models\Laundry;
use App\Models\Service;
use App\Models\InventoryItem;
use Illuminate\Support\Collection;

class LaundryInventoryService
{
    /**
     * Calculate number of loads needed based on weight and service max weight
     */
    public function calculateLoadsNeeded(float $weight, Service $service): int
    {
        if ($service->pricing_type !== 'per_load') {
            return 1; // Per-piece services don't split
        }

        $maxWeight = $service->max_weight_per_load ?? 5; // Default 5kg per load
        return (int) ceil($weight / $maxWeight);
    }

    /**
     * Get items needed for a laundry order
     * Returns array of [inventory_item_id => quantity_needed]
     */
    public function getItemsNeeded(Laundry $laundry): array
    {
        $service = $laundry->service;
        if (!$service) {
            return [];
        }

        $loadsNeeded = $laundry->number_of_loads ?? 1;
        $itemsNeeded = [];

        // Get all items for this service
        foreach ($service->serviceItems as $serviceItem) {
            $quantityPerLoad = $serviceItem->quantity;
            $totalQuantity = $quantityPerLoad * $loadsNeeded;
            $itemsNeeded[$serviceItem->inventory_item_id] = $totalQuantity;
        }

        return $itemsNeeded;
    }

    /**
     * Check if all items are in stock
     */
    public function hasAllItemsInStock(Laundry $laundry): bool
    {
        $itemsNeeded = $this->getItemsNeeded($laundry);

        foreach ($itemsNeeded as $itemId => $quantity) {
            $item = InventoryItem::find($itemId);
            if (!$item || !$item->hasStock($quantity)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Deduct inventory when laundry is processed
     */
    public function deductInventory(Laundry $laundry): bool
    {
        $itemsNeeded = $this->getItemsNeeded($laundry);

        // Check all items first
        if (!$this->hasAllItemsInStock($laundry)) {
            return false;
        }

        // Deduct all items
        foreach ($itemsNeeded as $itemId => $quantity) {
            $item = InventoryItem::find($itemId);
            $item->deductStock($quantity, 'laundry_' . $laundry->id);
        }

        return true;
    }

    /**
     * Get items that are low stock for this laundry
     */
    public function getLowStockItems(Laundry $laundry): Collection
    {
        $itemsNeeded = $this->getItemsNeeded($laundry);
        $lowStockItems = collect();

        foreach ($itemsNeeded as $itemId => $quantity) {
            $item = InventoryItem::find($itemId);
            if ($item && $item->isLowStock()) {
                $lowStockItems->push([
                    'item' => $item,
                    'quantity_needed' => $quantity,
                    'current_stock' => $item->current_stock,
                ]);
            }
        }

        return $lowStockItems;
    }

    /**
     * Get cost of items for a laundry order
     */
    public function calculateItemsCost(Laundry $laundry): float
    {
        $itemsNeeded = $this->getItemsNeeded($laundry);
        $totalCost = 0;

        foreach ($itemsNeeded as $itemId => $quantity) {
            $item = InventoryItem::find($itemId);
            if ($item) {
                $totalCost += $item->unit_cost * $quantity;
            }
        }

        return (float) $totalCost;
    }

    /**
     * Get formatted items list for display
     */
    public function getFormattedItemsList(Laundry $laundry): string
    {
        $itemsNeeded = $this->getItemsNeeded($laundry);
        $items = [];

        foreach ($itemsNeeded as $itemId => $quantity) {
            $item = InventoryItem::find($itemId);
            if ($item) {
                $items[] = $quantity . ' ' . $item->unit . ' of ' . $item->name;
            }
        }

        return implode(', ', $items) ?: 'No items';
    }
}
