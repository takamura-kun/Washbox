<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;
use Carbon\Carbon;

class MarkAbsentStaff extends Command
{
    protected $signature = 'attendance:mark-absent {--date= : Specific date (Y-m-d format)}';

    protected $description = 'Mark staff as absent if they did not time in';

    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::yesterday();

        $this->info("Marking absent staff for {$date->format('Y-m-d')}...");

        Attendance::markAbsentForDate($date);

        $this->info('✓ Absent staff marked successfully!');
    }
}
