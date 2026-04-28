<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\CentralStock;
use App\Models\Branch;
use App\Models\BranchStock;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $detergentCategory = InventoryCategory::where('name', 'Detergents')->first();
        $fabricSoftenerCategory = InventoryCategory::where('name', 'Fabric Softeners')->first();
        $bleachCategory = InventoryCategory::where('name', 'Bleach & Disinfectants')->first();

        if (!$detergentCategory || !$fabricSoftenerCategory || !$bleachCategory) {
            $this->command->warn('Categories not found. Please run InventoryCategorySeeder first.');
            return;
        }

        $items = [
            [
                'name' => 'Ariel Detergent Sachet',
                'brand' => 'Ariel',
                'category_id' => $detergentCategory->id,
                'distribution_unit' => 'sachet',
                'bulk_unit' => 'dozen',
                'units_per_bulk' => 12,
                'bulk_cost_price' => 171.00,
                'unit_cost_price' => 14.25,
                'description' => 'Ariel laundry detergent sachets',
                'initial_stock' => 240, // 20 dozen
            ],
            [
                'name' => 'Downy Fabric Softener',
                'brand' => 'Downy',
                'category_id' => $fabricSoftenerCategory->id,
                'distribution_unit' => 'sachet',
                'bulk_unit' => 'dozen',
                'units_per_bulk' => 12,
                'bulk_cost_price' => 180.00,
                'unit_cost_price' => 15.00,
                'description' => 'Downy fabric softener sachets',
                'initial_stock' => 180, // 15 dozen
            ],
            [
                'name' => 'Fabcon Fabric Conditioner',
                'brand' => 'Fabcon',
                'category_id' => $fabricSoftenerCategory->id,
                'distribution_unit' => 'sachet',
                'bulk_unit' => 'dozen',
                'units_per_bulk' => 12,
                'bulk_cost_price' => 150.00,
                'unit_cost_price' => 12.50,
                'description' => 'Fabcon fabric conditioner sachets',
                'initial_stock' => 120, // 10 dozen
            ],
            [
                'name' => 'Zonrox Bleach',
                'brand' => 'Zonrox',
                'category_id' => $bleachCategory->id,
                'distribution_unit' => 'bottle',
                'bulk_unit' => 'case',
                'units_per_bulk' => 10,
                'bulk_cost_price' => 450.00,
                'unit_cost_price' => 45.00,
                'description' => 'Zonrox color-safe bleach 1L bottles',
                'initial_stock' => 50, // 5 cases
            ],
        ];

        foreach ($items as $itemData) {
            $initialStock = $itemData['initial_stock'];
            unset($itemData['initial_stock']);

            $item = InventoryItem::firstOrCreate(
                ['name' => $itemData['name']],
                array_merge($itemData, ['is_active' => true])
            );

            // Create central stock
            CentralStock::firstOrCreate(
                ['inventory_item_id' => $item->id],
                [
                    'quantity_in_bulk' => $initialStock / $item->units_per_bulk,
                    'quantity_in_units' => $initialStock,
                    'reorder_point' => $initialStock * 0.2, // 20% of initial
                    'max_stock_level' => $initialStock * 3,
                ]
            );

            // Create branch stocks for each branch
            $branches = Branch::all();
            foreach ($branches as $branch) {
                BranchStock::firstOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'inventory_item_id' => $item->id,
                    ],
                    [
                        'current_stock' => 0,
                        'reorder_point' => 20,
                        'max_stock_level' => 100,
                    ]
                );
            }
        }

        $this->command->info('✅ Inventory items seeded successfully with central and branch stocks!');
    }
}
