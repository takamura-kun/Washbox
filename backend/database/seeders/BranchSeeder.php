<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name'     => 'WashBox Laundry Services - Sibulan',
                'code'     => 'SBL',
                'address'  => 'Poblacion, Sibulan, Negros Oriental',
                'city'     => 'Sibulan',
                'province' => 'Negros Oriental',
                'phone'    => '09171234567',
                'email'    => 'sibulan@washbox.com',
                'is_active'=> true,
                'username' => 'branch_sibulan',
                'password' => Hash::make('Branch@123'),
            ],
            [
                'name'     => 'WashBox Laundry Services - Dumaguete',
                'code'     => 'DGT',
                'address'  => 'Rizal Boulevard, Dumaguete City',
                'city'     => 'Dumaguete',
                'province' => 'Negros Oriental',
                'phone'    => '09181234567',
                'email'    => 'dumaguete@washbox.com',
                'is_active'=> true,
                'username' => 'branch_dumaguete',
                'password' => Hash::make('Branch@123'),
            ],
            [
                'name'     => 'WashBox Laundry Services - Bais',
                'code'     => 'BAS',
                'address'  => 'National Highway, Bais City',
                'city'     => 'Bais',
                'province' => 'Negros Oriental',
                'phone'    => '09191234567',
                'email'    => 'bais@washbox.com',
                'is_active'=> true,
                'username' => 'branch_bais',
                'password' => Hash::make('Branch@123'),
            ],
        ];

        foreach ($branches as $data) {
            Branch::firstOrCreate(['code' => $data['code']], $data);
        }

        $this->command->info('✅ Branches seeded: ' . Branch::count());
    }
}
