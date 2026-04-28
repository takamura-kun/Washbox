<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\Laundry;
use App\Models\Branch;
use App\Models\Service;
use App\Models\User;

class StressTestSeeder extends Seeder
{
    public function run(): void
    {
        $customers = (int) ($this->command->ask('How many customers to generate?', 500));
        $laundries = (int) ($this->command->ask('How many laundries to generate?', 2000));

        $branchIds  = Branch::pluck('id')->toArray() ?: [1];
        $serviceIds = Service::pluck('id')->toArray() ?: [null];
        $userIds    = User::pluck('id')->toArray()    ?: [1];

        // ── Customers ────────────────────────────────────────────────────────
        $this->command->info("Generating {$customers} customers...");
        $start     = microtime(true);
        $chunkSize = 500;
        $now       = now()->toDateTimeString();

        for ($i = 0; $i < $customers; $i += $chunkSize) {
            $rows = [];
            $count = min($chunkSize, $customers - $i);
            for ($j = 0; $j < $count; $j++) {
                $branchId = $branchIds[array_rand($branchIds)];
                $rows[] = [
                    'name'                => 'Customer ' . ($i + $j + 1),
                    'email'               => 'customer_' . ($i + $j + 1) . '_' . Str::random(4) . '@test.com',
                    'phone'               => '09' . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT),
                    'password'            => bcrypt('Customer@123'),
                    'address'             => 'Test Address ' . ($i + $j + 1),
                    'branch_id'           => $branchId,
                    'preferred_branch_id' => $branchId,
                    'registration_type'   => mt_rand(0, 1) ? 'self_registered' : 'walk_in',
                    'is_active'           => 1,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
            DB::table('customers')->insert($rows);
            $this->command->line("  Customers: " . min($i + $chunkSize, $customers) . "/{$customers}");
        }

        $elapsed = round(microtime(true) - $start, 2);
        $this->command->info("  Done in {$elapsed}s — Total customers: " . Customer::count());

        // ── Laundries ────────────────────────────────────────────────────────
        $this->command->info("Generating {$laundries} laundries...");
        $start      = microtime(true);
        $customerIds = Customer::pluck('id')->toArray();
        $statuses   = ['received', 'processing', 'ready', 'paid', 'completed', 'cancelled'];

        for ($i = 0; $i < $laundries; $i += $chunkSize) {
            $rows  = [];
            $count = min($chunkSize, $laundries - $i);
            for ($j = 0; $j < $count; $j++) {
                $status   = $statuses[array_rand($statuses)];
                $loads    = mt_rand(1, 5);
                $price    = mt_rand(80, 200);
                $subtotal = $price * $loads;
                $rows[] = [
                    'tracking_number' => 'WB-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'customer_id'     => $customerIds[array_rand($customerIds)],
                    'branch_id'       => $branchIds[array_rand($branchIds)],
                    'service_id'      => $serviceIds[array_rand($serviceIds)],
                    'created_by'      => $userIds[array_rand($userIds)],
                    'weight'          => mt_rand(100, 1000) / 100,
                    'number_of_loads' => $loads,
                    'price_per_piece' => 0,
                    'subtotal'        => $subtotal,
                    'addons_total'    => 0,
                    'discount_amount' => 0,
                    'total_amount'    => $subtotal,
                    'status'          => $status,
                    'payment_status'  => in_array($status, ['paid', 'completed']) ? 'paid' : 'pending',
                    'payment_method'  => mt_rand(0, 1) ? 'cash' : 'gcash',
                    'received_at'     => date('Y-m-d H:i:s', mt_rand(strtotime('-6 months'), time())),
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }
            DB::table('laundries')->insert($rows);
            $this->command->line("  Laundries: " . min($i + $chunkSize, $laundries) . "/{$laundries}");
        }

        $elapsed = round(microtime(true) - $start, 2);
        $this->command->info("  Done in {$elapsed}s — Total laundries: " . Laundry::count());

        $this->command->newLine();
        $this->command->table(
            ['Model', 'Count'],
            [
                ['Customers', Customer::count()],
                ['Laundries', Laundry::count()],
            ]
        );
        $this->command->warn('Run "php artisan migrate:fresh --seed" to reset when done.');
    }
}
