<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;

class DisableMaintenanceMode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable maintenance mode and restore system access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SystemSetting::set('maintenance_mode', '0', 'boolean', 'maintenance');
        
        $this->info('✓ Maintenance mode has been disabled.');
        $this->info('✓ System is now accessible to all users.');
        
        return Command::SUCCESS;
    }
}
