<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceArea;
use App\Models\Branch;

class ServiceAreasSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();

        foreach ($branches as $branch) {
            // Free delivery areas
            $freeAreas = [
                [
                    'area_name' => 'Dumaguete City',
                    'area_type' => 'city',
                    'is_free' => true,
                    'delivery_fee' => 0.00,
                    'coverage_notes' => 'All barangays within Dumaguete City',
                ],
                [
                    'area_name' => 'Sibulan',
                    'area_type' => 'municipality',
                    'is_free' => true,
                    'delivery_fee' => 0.00,
                    'coverage_notes' => 'All barangays within Sibulan municipality',
                ],
            ];

            // Paid delivery areas (nearby municipalities)
            $paidAreas = [
                [
                    'area_name' => 'Bacong',
                    'area_type' => 'municipality',
                    'is_free' => false,
                    'delivery_fee' => 50.00,
                    'coverage_notes' => 'Delivery fee applies',
                ],
                [
                    'area_name' => 'Valencia',
                    'area_type' => 'municipality',
                    'is_free' => false,
                    'delivery_fee' => 80.00,
                    'coverage_notes' => 'Delivery fee applies',
                ],
                [
                    'area_name' => 'Dauin',
                    'area_type' => 'municipality',
                    'is_free' => false,
                    'delivery_fee' => 100.00,
                    'coverage_notes' => 'Delivery fee applies',
                ],
            ];

            foreach ($freeAreas as $area) {
                ServiceArea::create([
                    'branch_id' => $branch->id,
                    ...$area,
                    'is_active' => true,
                ]);
            }

            foreach ($paidAreas as $area) {
                ServiceArea::create([
                    'branch_id' => $branch->id,
                    ...$area,
                    'is_active' => true,
                ]);
            }
        }
    }
}
