<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Laundry;
use App\Models\Branch;
use App\Models\Service;

class StressTestSeeder extends Seeder
{
    public function run(): void
    {
        $customers = (int) ($this->command->ask('How many customers to generate?', 500));
        $laundries = (int) ($this->command->ask('How many laundries to generate?', 2000));

        // Pre-checks
        if (Branch::count() === 0) {
            $this->command->error('No branches found. Run BranchSeeder first.');
            return;
        }
        if (Service::count() === 0) {
            $this->command->warn('No services found. Laundries will have null service_id.');
        }

        // Customers
        $this->command->info("Generating {$customers} customers...");
        $start = microtime(true);
        Customer::factory($customers)->create();
        $elapsed = round(microtime(true) - $start, 2);
        $this->command->info("  Done in {$elapsed}s — Total customers: " . Customer::count());

        // Laundries
        $this->command->info("Generating {$laundries} laundries...");
        $start = microtime(true);

        // Chunk inserts to avoid memory issues
        $chunkSize = 500;
        $chunks    = (int) ceil($laundries / $chunkSize);

        for ($i = 0; $i < $chunks; $i++) {
            $count = ($i === $chunks - 1) ? ($laundries - ($i * $chunkSize)) : $chunkSize;
            Laundry::factory($count)->create();
            $this->command->line("  Chunk " . ($i + 1) . "/{$chunks} inserted ({$count} records)");
        }

        $elapsed = round(microtime(true) - $start, 2);
        $this->command->info("  Done in {$elapsed}s — Total laundries: " . Laundry::count());

        // Summary
        $this->command->newLine();
        $this->command->table(
            ['Model', 'Count'],
            [
                ['Customers', Customer::count()],
                ['Laundries', Laundry::count()],
            ]
        );

        $this->command->info('Stress test data generated successfully!');
        $this->command->warn('Run "php artisan db:wipe && php artisan migrate --seed" to reset when done.');
    }
}
