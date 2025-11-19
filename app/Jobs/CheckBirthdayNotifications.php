<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CheckBirthdayNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        Log::info('Starting CheckBirthdayNotifications job.');

        $today = Carbon::today();
        
        // Find users whose birthday is today (check both birthday and birth_date fields)
        $birthdayUsers = User::where(function($query) use ($today) {
                            $query->where(function($q) use ($today) {
                                $q->whereNotNull('birthday')
                                  ->whereMonth('birthday', $today->month)
                                  ->whereDay('birthday', $today->day);
                            })->orWhere(function($q) use ($today) {
                                $q->whereNotNull('birth_date')
                                  ->whereMonth('birth_date', $today->month)
                                  ->whereDay('birth_date', $today->day);
                            });
                        })->get();

        Log::info("Found {$birthdayUsers->count()} users with birthday today.");

        foreach ($birthdayUsers as $birthdayUser) {
            $cacheKey = "birthday_notifications_sent_{$birthdayUser->id}_{$today->format('Y-m-d')}";
            
            // Check if notifications were already sent today
            if (!Cache::has($cacheKey)) {
                Log::info("Sending birthday notifications for user: {$birthdayUser->name} (ID: {$birthdayUser->id})");
                
                try {
                    // Send notification to birthday user themselves (if they want to receive notifications)
                    if ($birthdayUser->receive_birthday_notifications !== false) {
                        $selfNotification = $notificationService->notifyBirthdayToSelf($birthdayUser);
                        if ($selfNotification) {
                            Log::info("Sent birthday self-notification to user: {$birthdayUser->name} (ID: {$birthdayUser->id})");
                        }
                    }
                    
                    // Send notifications to all other users
                    $notifications = $notificationService->notifyAllUsersAboutBirthday($birthdayUser);
                    
                    Log::info("Sent " . count($notifications) . " birthday notifications for user {$birthdayUser->name}");
                    
                    // Cache for 24 hours to prevent duplicate notifications
                    Cache::put($cacheKey, true, now()->addHours(24));
                    
                } catch (\Exception $e) {
                    Log::error("Failed to send birthday notifications for user {$birthdayUser->name}: " . $e->getMessage());
                }
            } else {
                Log::info("Birthday notifications already sent today for user: {$birthdayUser->name}");
            }
        }

        Log::info('CheckBirthdayNotifications job finished.');
    }
}