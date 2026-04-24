<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\PickupRequest;

class MigratePickupRequestData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pickup:migrate-data {--dry-run : Show what would be migrated without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate existing pickup request data to standardized structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        } else {
            $this->info('🚀 Starting pickup request data migration...');
        }

        $this->migratePhoneNumbers($dryRun);
        $this->migrateLaundryIds($dryRun);
        $this->migrateNotesToInstructions($dryRun);
        $this->setDeliveryAddresses($dryRun);
        $this->initializeStatusHistory($dryRun);
        $this->setCompletedTimestamps($dryRun);
        $this->setPriorities($dryRun);
        $this->cleanupOldFields($dryRun);

        if ($dryRun) {
            $this->info('✅ Dry run completed - no changes made');
        } else {
            $this->info('✅ Migration completed successfully!');
        }
    }

    private function migratePhoneNumbers($dryRun)
    {
        $this->info('📞 Migrating phone numbers...');
        
        if (Schema::hasColumn('pickup_requests', 'contact_phone')) {
            $count = DB::table('pickup_requests')
                ->whereNull('phone_number')
                ->whereNotNull('contact_phone')
                ->count();
                
            $this->line("   Found {$count} records with contact_phone to migrate");
            
            if (!$dryRun && $count > 0) {
                DB::statement("
                    UPDATE pickup_requests 
                    SET phone_number = contact_phone 
                    WHERE phone_number IS NULL AND contact_phone IS NOT NULL
                ");
                $this->line("   ✓ Migrated {$count} phone numbers");
            }
        } else {
            $this->line("   ℹ No contact_phone column found - skipping");
        }
    }

    private function migrateLaundryIds($dryRun)
    {
        $this->info('🧺 Migrating laundry IDs...');
        
        if (Schema::hasColumn('pickup_requests', 'laundries_id')) {
            $count = DB::table('pickup_requests')
                ->whereNull('laundry_id')
                ->whereNotNull('laundries_id')
                ->count();
                
            $this->line("   Found {$count} records with laundries_id to migrate");
            
            if (!$dryRun && $count > 0) {
                DB::statement("
                    UPDATE pickup_requests 
                    SET laundry_id = laundries_id 
                    WHERE laundry_id IS NULL AND laundries_id IS NOT NULL
                ");
                $this->line("   ✓ Migrated {$count} laundry IDs");
            }
        } else {
            $this->line("   ℹ No laundries_id column found - skipping");
        }
    }

    private function migrateNotesToInstructions($dryRun)
    {
        $this->info('📝 Migrating notes to special instructions...');
        
        $count = DB::table('pickup_requests')
            ->whereNull('special_instructions')
            ->whereNotNull('notes')
            ->count();
            
        $this->line("   Found {$count} records with notes to migrate");
        
        if (!$dryRun && $count > 0) {
            DB::statement("
                UPDATE pickup_requests 
                SET special_instructions = notes 
                WHERE special_instructions IS NULL AND notes IS NOT NULL
            ");
            $this->line("   ✓ Migrated {$count} notes to special instructions");
        }
    }

    private function setDeliveryAddresses($dryRun)
    {
        $this->info('🏠 Setting delivery addresses...');
        
        $count = DB::table('pickup_requests')
            ->whereNull('delivery_address')
            ->count();
            
        $this->line("   Found {$count} records without delivery address");
        
        if (!$dryRun && $count > 0) {
            DB::statement("
                UPDATE pickup_requests 
                SET delivery_address = pickup_address,
                    delivery_latitude = latitude,
                    delivery_longitude = longitude
                WHERE delivery_address IS NULL
            ");
            $this->line("   ✓ Set delivery addresses for {$count} records");
        }
    }

    private function initializeStatusHistory($dryRun)
    {
        $this->info('📊 Initializing status history...');
        
        $count = DB::table('pickup_requests')
            ->whereNull('status_history')
            ->count();
            
        $this->line("   Found {$count} records without status history");
        
        if (!$dryRun && $count > 0) {
            $pickups = DB::table('pickup_requests')
                ->whereNull('status_history')
                ->select('id', 'status', 'created_at')
                ->get();
                
            foreach ($pickups as $pickup) {
                $history = [
                    [
                        'status' => 'pending',
                        'timestamp' => $pickup->created_at,
                        'changed_by' => null,
                        'user_name' => null,
                        'notes' => 'Initial status'
                    ]
                ];
                
                // Add current status if different from pending
                if ($pickup->status !== 'pending') {
                    $history[] = [
                        'status' => $pickup->status,
                        'timestamp' => $pickup->created_at,
                        'changed_by' => null,
                        'user_name' => null,
                        'notes' => 'Migrated status'
                    ];
                }
                
                DB::table('pickup_requests')
                    ->where('id', $pickup->id)
                    ->update(['status_history' => json_encode($history)]);
            }
            
            $this->line("   ✓ Initialized status history for {$count} records");
        }
    }

    private function setCompletedTimestamps($dryRun)
    {
        $this->info('⏰ Setting completed timestamps...');
        
        $count = DB::table('pickup_requests')
            ->where('status', 'picked_up')
            ->whereNull('completed_at')
            ->whereNotNull('picked_up_at')
            ->count();
            
        $this->line("   Found {$count} picked up records without completed timestamp");
        
        if (!$dryRun && $count > 0) {
            DB::statement("
                UPDATE pickup_requests 
                SET completed_at = picked_up_at 
                WHERE status = 'picked_up' 
                AND completed_at IS NULL 
                AND picked_up_at IS NOT NULL
            ");
            $this->line("   ✓ Set completed timestamps for {$count} records");
        }
    }

    private function setPriorities($dryRun)
    {
        $this->info('🎯 Setting default priorities...');
        
        if (Schema::hasColumn('pickup_requests', 'priority')) {
            $count = DB::table('pickup_requests')
                ->whereNull('priority')
                ->count();
                
            $this->line("   Found {$count} records without priority");
            
            if (!$dryRun && $count > 0) {
                DB::statement("
                    UPDATE pickup_requests 
                    SET priority = 'normal' 
                    WHERE priority IS NULL
                ");
                $this->line("   ✓ Set default priority for {$count} records");
            }
        } else {
            $this->line("   ℹ No priority column found - skipping");
        }
    }

    private function cleanupOldFields($dryRun)
    {
        $this->info('🧹 Cleaning up old fields...');
        
        if ($dryRun) {
            $this->line("   Would remove old inconsistent columns:");
            if (Schema::hasColumn('pickup_requests', 'contact_phone')) {
                $this->line("   - contact_phone");
            }
            if (Schema::hasColumn('pickup_requests', 'laundries_id')) {
                $this->line("   - laundries_id");
            }
        } else {
            $cleaned = 0;
            
            if (Schema::hasColumn('pickup_requests', 'contact_phone')) {
                Schema::table('pickup_requests', function ($table) {
                    $table->dropColumn('contact_phone');
                });
                $cleaned++;
                $this->line("   ✓ Removed contact_phone column");
            }
            
            if (Schema::hasColumn('pickup_requests', 'laundries_id')) {
                Schema::table('pickup_requests', function ($table) {
                    $table->dropIndex(['laundries_id']);
                    $table->dropColumn('laundries_id');
                });
                $cleaned++;
                $this->line("   ✓ Removed laundries_id column");
            }
            
            if ($cleaned === 0) {
                $this->line("   ℹ No old fields to clean up");
            }
        }
    }
}