<?php

namespace Database\Seeders;

use App\Models\InventoryCategory;
use Illuminate\Database\Seeder;

class InventoryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Detergents',
                'description' => 'Laundry detergents and washing powders',
                'is_active' => true,
            ],
            [
                'name' => 'Fabric Softeners',
                'description' => 'Fabric softeners and conditioners',
                'is_active' => true,
            ],
            [
                'name' => 'Bleach & Disinfectants',
                'description' => 'Bleach, disinfectants, and sanitizers',
                'is_active' => true,
            ],
            [
                'name' => 'Stain Removers',
                'description' => 'Stain removal products',
                'is_active' => true,
            ],
            [
                'name' => 'Packaging Materials',
                'description' => 'Bags, boxes, and packaging supplies',
                'is_active' => true,
            ],
            [
                'name' => 'Cleaning Supplies',
                'description' => 'General cleaning supplies',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            InventoryCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
