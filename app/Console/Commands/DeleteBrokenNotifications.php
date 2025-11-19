<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class DeleteBrokenNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:delete-broken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete broken notifications with undefined content';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Deleting broken notifications...');
        
        try {
            // Delete notifications with undefined content
            $deleted = Notification::where(function($query) {
                $query->where('body', 'undefined')
                      ->orWhere('body', 'ez')
                      ->orWhere('title', 'undefined')
                      ->orWhere('title', 'ez')
                      ->orWhereNull('body')
                      ->orWhere('body', '');
            })->delete();
            
            $this->info("Deleted {$deleted} broken notifications.");
            
            // Show remaining notifications
            $remaining = Notification::count();
            $this->info("Remaining notifications: {$remaining}");
            
        } catch (\Exception $e) {
            $this->error('Failed to delete broken notifications: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}