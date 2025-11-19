<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;

class TestBirthdayNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthdays:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test birthday notifications by creating a test user with today birthday';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): void
    {
        $this->info('Testing birthday notifications...');
        
        try {
            // Get first user and update their birthday to today
            $testUser = User::first();
            if (!$testUser) {
                $this->error('No users found in the system');
                return;
            }
            
            // Update user's birthday to today
            $testUser->update(['birthday' => Carbon::today()]);
            
            $this->info("Updated user: {$testUser->name} with ID: {$testUser->id} to have birthday today");
            
            // Send birthday notifications
            $this->info('Sending birthday notifications...');
            $notifications = $notificationService->notifyAllUsersAboutBirthday($testUser);
            
            $this->info("Sent " . count($notifications) . " birthday notifications!");
            
            // Show some notifications
            $recentNotifications = \App\Models\Notification::where('type', 'birthday')
                                                          ->latest()
                                                          ->take(3)
                                                          ->get();
            
            $this->info("\nRecent birthday notifications:");
            foreach ($recentNotifications as $notification) {
                $this->line("- {$notification->title}: {$notification->body}");
            }
            
        } catch (\Exception $e) {
            $this->error('Failed to test birthday notifications: ' . $e->getMessage());
        }
    }
}