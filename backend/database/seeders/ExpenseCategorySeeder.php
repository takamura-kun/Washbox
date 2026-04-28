<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Utilities' => 'Electricity, water, gas, internet',
            'Rent' => 'Branch rent and facility costs',
            'Maintenance' => 'Equipment maintenance and repairs',
            'Supplies' => 'Office and cleaning supplies',
            'Marketing' => 'Advertising and promotional expenses',
            'Transportation' => 'Fuel, vehicle maintenance, delivery',
            'Insurance' => 'Business and liability insurance',
            'Salaries' => 'Staff salaries and wages',
            'Training' => 'Employee training and development',
            'Miscellaneous' => 'Other business expenses',
        ];

        foreach ($categories as $name => $description) {
            ExpenseCategory::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                    'description' => $description,
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
