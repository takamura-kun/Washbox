<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\LaundryNotificationService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send unclaimed laundry reminders daily at 9 AM
Schedule::call(function () {
    app(LaundryNotificationService::class)->sendUnclaimedReminders();
})->dailyAt('09:00')->name('send-unclaimed-reminders');

// Generate daily cash flow records at 1 AM (for previous day)
Schedule::command('cashflow:generate-daily')
    ->dailyAt('01:00')
    ->name('generate-daily-cashflow')
    ->onSuccess(function () {
        \Log::info('Daily cash flow records generated successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to generate daily cash flow records');
    });

// Mark absent staff at 11:59 PM daily
Schedule::command('attendance:mark-absent')
    ->dailyAt('23:59')
    ->name('mark-absent-staff')
    ->onSuccess(function () {
        \Log::info('Absent staff marked successfully');
    })
    ->onFailure(function () {
        \Log::error('Failed to mark absent staff');
    });
