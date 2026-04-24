<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryCategory;
use App\Models\ExpenseCategory;

class InventoryFinanceSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Inventory Categories
        $inventoryCategories = [
            ['name' => 'Detergents', 'is_active' => true],
            ['name' => 'Fabric Conditioners', 'is_active' => true],
            ['name' => 'Bleach & Whiteners', 'is_active' => true],
            ['name' => 'Packaging Materials', 'is_active' => true],
            ['name' => 'LPG & Fuel', 'is_active' => true],
            ['name' => 'Cleaning Supplies', 'is_active' => true],
            ['name' => 'Office Supplies', 'is_active' => true],
        ];

        foreach ($inventoryCategories as $category) {
            InventoryCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        // Seed Expense Categories
        $expenseCategories = [
            ['name' => 'Supplies', 'slug' => 'supplies', 'is_system' => true, 'is_active' => true],
            ['name' => 'Utilities', 'slug' => 'utilities', 'is_system' => false, 'is_active' => true],
            ['name' => 'Rent', 'slug' => 'rent', 'is_system' => false, 'is_active' => true],
            ['name' => 'Salaries', 'slug' => 'salaries', 'is_system' => true, 'is_active' => true],
            ['name' => 'Maintenance', 'slug' => 'maintenance', 'is_system' => false, 'is_active' => true],
            ['name' => 'Miscellaneous', 'slug' => 'miscellaneous', 'is_system' => false, 'is_active' => true],
        ];

        foreach ($expenseCategories as $category) {
            ExpenseCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
