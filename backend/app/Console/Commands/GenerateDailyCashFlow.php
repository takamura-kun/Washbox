<?php

namespace App\Console\Commands;

use App\Models\CashFlowRecord;
use App\Models\Branch;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateDailyCashFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashflow:generate-daily {--date= : Specific date to generate (Y-m-d format)} {--branch= : Specific branch ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily cash flow records for all branches or a specific branch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::yesterday();
        $branchId = $this->option('branch');

        $this->info("Generating cash flow records for {$date->format('Y-m-d')}...");

        if ($branchId) {
            // Generate for specific branch
            $this->generateForBranch($date, $branchId);
        } else {
            // Generate for all branches
            $this->generateForAllBranches($date);
        }

        $this->info('Cash flow generation completed!');
    }

    /**
     * Generate cash flow for all branches
     */
    private function generateForAllBranches(Carbon $date)
    {
        $branches = Branch::active()->get();

        $this->info("Processing {$branches->count()} branches...");

        foreach ($branches as $branch) {
            $this->generateForBranch($date, $branch->id);
        }

        // Also generate for company-wide (null branch_id)
        $this->generateForBranch($date, null);
    }

    /**
     * Generate cash flow for a specific branch
     */
    private function generateForBranch(Carbon $date, $branchId = null)
    {
        try {
            // Check if record already exists
            $existing = CashFlowRecord::where('record_date', $date)
                ->where('branch_id', $branchId)
                ->first();

            if ($existing) {
                $this->warn("Cash flow record already exists for " . 
                    ($branchId ? "Branch ID {$branchId}" : "Company-wide") . 
                    " on {$date->format('Y-m-d')}. Updating...");
                
                // Regenerate to update values
                CashFlowRecord::generateForDate($date, $branchId);
                $this->info("✓ Updated: " . ($branchId ? "Branch ID {$branchId}" : "Company-wide"));
            } else {
                CashFlowRecord::generateForDate($date, $branchId);
                $this->info("✓ Generated: " . ($branchId ? "Branch ID {$branchId}" : "Company-wide"));
            }
        } catch (\Exception $e) {
            $this->error("✗ Failed for " . ($branchId ? "Branch ID {$branchId}" : "Company-wide") . ": " . $e->getMessage());
        }
    }
}
