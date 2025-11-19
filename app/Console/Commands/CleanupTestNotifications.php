<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupTestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up test notifications created during testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up test notifications...');
        
        try {
            // Delete test birthday notifications
            $deleted = Notification::where('type', 'birthday')
                                  ->where('title', 'عيد ميلاد سعيد! 🎉')
                                  ->delete();
            
            $this->info("Deleted {$deleted} test birthday notifications.");
            
            // Show remaining notifications
            $remaining = Notification::count();
            $this->info("Remaining notifications: {$remaining}");
            
        } catch (\Exception $e) {
            $this->error('Failed to cleanup test notifications: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}