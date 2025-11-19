<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up broken or invalid notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting notification cleanup...');
        
        try {
            // Find and delete notifications with undefined or null body
            $brokenNotifications = Notification::where(function($query) {
                $query->whereNull('body')
                      ->orWhere('body', '')
                      ->orWhere('body', 'undefined')
                      ->orWhere('body', 'ez')
                      ->orWhere('title', 'undefined')
                      ->orWhere('title', '');
            });
            
            $count = $brokenNotifications->count();
            
            if ($count > 0) {
                $brokenNotifications->delete();
                $this->info("Deleted {$count} broken notifications.");
            } else {
                $this->info('No broken notifications found.');
            }
            
            // Show current notification count
            $totalNotifications = Notification::count();
            $this->info("Total notifications remaining: {$totalNotifications}");
            
            // Show recent notifications
            $recentNotifications = Notification::latest()->take(5)->get();
            if ($recentNotifications->count() > 0) {
                $this->info("\nRecent notifications:");
                foreach ($recentNotifications as $notification) {
                    $this->line("- ID: {$notification->id} | Type: {$notification->type} | Title: {$notification->title}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error('Failed to cleanup notifications: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}