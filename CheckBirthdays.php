<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CheckBirthdayNotifications;

class CheckBirthdays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthdays:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for user birthdays and send notifications to all users.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting birthday check...');
        CheckBirthdayNotifications::dispatch();
        $this->info('Birthday check job dispatched successfully.');
    }
}