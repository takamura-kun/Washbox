<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Database\Seeder;

class ServiceItemSeeder extends Seeder
{
    public function run(): void
    {
        // Get inventory items
        $ariel = \App\Models\InventoryItem::where('name', 'Ariel Sachet')->first();
        $downy = \App\Models\InventoryItem::where('name', 'Downy Sachet')->first();
        $zonrox = \App\Models\InventoryItem::where('name', 'Zonrox Color Safe')->first();
        $oxiclean = \App\Models\InventoryItem::where('name', 'OxiClean Stain Remover')->first();

        // Configure existing services with max_weight_per_load
        // Premium Load
        $premiumLoad = Service::where('name', 'like', '%Premium%')->orWhere('name', 'like', '%premium%')->first();
        if ($premiumLoad) {
            $premiumLoad->update(['max_weight_per_load' => 5]);
            
            // Define items for Premium Load
            ServiceItem::updateOrCreate(
                ['service_id' => $premiumLoad->id, 'inventory_item_id' => $ariel->id],
                ['quantity' => 2]
            );
            ServiceItem::updateOrCreate(
                ['service_id' => $premiumLoad->id, 'inventory_item_id' => $downy->id],
                ['quantity' => 2]
            );
            ServiceItem::updateOrCreate(
                ['service_id' => $premiumLoad->id, 'inventory_item_id' => $zonrox->id],
                ['quantity' => 1]
            );
        }

        // Regular Load
        $regularLoad = Service::where('name', 'like', '%Regular%')->orWhere('name', 'like', '%regular%')->first();
        if ($regularLoad) {
            $regularLoad->update(['max_weight_per_load' => 5]);
            
            // Define items for Regular Load
            ServiceItem::updateOrCreate(
                ['service_id' => $regularLoad->id, 'inventory_item_id' => $ariel->id],
                ['quantity' => 1]
            );
            ServiceItem::updateOrCreate(
                ['service_id' => $regularLoad->id, 'inventory_item_id' => $downy->id],
                ['quantity' => 1]
            );
        }

        // Express Load
        $expressLoad = Service::where('name', 'like', '%Express%')->orWhere('name', 'like', '%express%')->first();
        if ($expressLoad) {
            $expressLoad->update(['max_weight_per_load' => 4]);
            
            // Define items for Express Load
            ServiceItem::updateOrCreate(
                ['service_id' => $expressLoad->id, 'inventory_item_id' => $ariel->id],
                ['quantity' => 2]
            );
            ServiceItem::updateOrCreate(
                ['service_id' => $expressLoad->id, 'inventory_item_id' => $zonrox->id],
                ['quantity' => 1]
            );
        }

        // Delicate Wash
        $delicate = Service::where('name', 'like', '%Delicate%')->orWhere('name', 'like', '%delicate%')->first();
        if ($delicate) {
            $delicate->update(['max_weight_per_load' => 3]);
            
            // Define items for Delicate Wash
            ServiceItem::updateOrCreate(
                ['service_id' => $delicate->id, 'inventory_item_id' => $ariel->id],
                ['quantity' => 1]
            );
            ServiceItem::updateOrCreate(
                ['service_id' => $delicate->id, 'inventory_item_id' => $downy->id],
                ['quantity' => 2]
            );
        }

        // Comforter/Heavy Items
        $comforter = Service::where('name', 'like', '%Comforter%')
            ->orWhere('name', 'like', '%comforter%')
            ->orWhere('name', 'like', '%Heavy%')
            ->orWhere('name', 'like', '%heavy%')
            ->first();
        if ($comforter) {
            $comforter->update(['max_weight_per_load' => 10]);
            
            // Define items for Comforter
            ServiceItem::updateOrCreate(
                ['service_id' => $comforter->id, 'inventory_item_id' => $ariel->id],
                ['quantity' => 3]
            );
            ServiceItem::updateOrCreate(
                ['service_id' => $comforter->id, 'inventory_item_id' => $downy->id],
                ['quantity' => 2]
            );
            ServiceItem::updateOrCreate(
                ['service_id' => $comforter->id, 'inventory_item_id' => $oxiclean->id],
                ['quantity' => 1]
            );
        }
    }
}
