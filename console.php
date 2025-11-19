<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Zoho Integration Scheduled Commands
if (config('zoho.sync.enabled')) {
    // Sync tickets every 10 minutes
    Schedule::command('zoho:sync-tickets')
        ->everyTenMinutes()
        ->withoutOverlapping()
        ->onFailure(function () {
            \Log::error('Zoho sync tickets command failed');
        })
        ->onSuccess(function () {
            \Log::info('Zoho sync tickets completed successfully');
        });

    // Calculate statistics hourly
    Schedule::command('zoho:calculate-stats')
        ->hourly()
        ->withoutOverlapping()
        ->onFailure(function () {
            \Log::error('Zoho calculate stats command failed');
        });
}

// Task Management Scheduled Commands
// Check for overdue tasks every hour
Schedule::command('tasks:check-overdue')
    ->hourly()
    ->withoutOverlapping()
    ->onFailure(function () {
        \Log::error('Check overdue tasks command failed');
    })
    ->onSuccess(function () {
        \Log::info('Check overdue tasks completed successfully');
    });

// Birthday Notifications
// Check for birthdays every day at 9 AM
Schedule::command('birthdays:check')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Log::error('Check birthdays command failed');
    })
    ->onSuccess(function () {
        \Log::info('Check birthdays completed successfully');
    });
