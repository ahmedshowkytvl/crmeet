<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Events\NotificationCreated;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * إنشاء إشعار لرسالة جديدة
     */
    public function notifyNewMessage($chatMessage, int $recipientUserId, User $senderUser): ?Notification
    {
        try {
            $notification = Notification::create([
                'user_id' => $recipientUserId,
                'type' => 'message',
                'title' => 'رسالة جديدة',
                'body' => "لديك رسالة جديدة من {$senderUser->name_ar}",
                'actor_id' => $senderUser->id,
                'resource_type' => 'chat_message',
                'resource_id' => $chatMessage->id,
                'link' => "/chat/{$chatMessage->chat_room_id}",
                'metadata' => [
                    'chat_room_id' => $chatMessage->chat_room_id,
                    'message_preview' => substr($chatMessage->content, 0, 100),
                    'message_type' => $chatMessage->message_type,
                ],
            ]);

            $notification->load('actor');
            
            // بث الإشعار
            event(new NotificationCreated($notification));

            Log::info('Notification created for new message', [
                'notification_id' => $notification->id,
                'recipient_user_id' => $recipientUserId,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create message notification', [
                'error' => $e->getMessage(),
                'recipient_user_id' => $recipientUserId,
            ]);
            
            return null;
        }
    }

    /**
     * إنشاء إشعار لمهمة مُسندة
     */
    public function notifyTaskAssigned($task, int $assignedToUserId, User $assignedByUser): ?Notification
    {
        try {
            $notification = Notification::create([
                'user_id' => $assignedToUserId,
                'type' => 'task',
                'title' => 'مهمة مسندة',
                'body' => "تم إسناد مهمة جديدة إليك: {$task->title}",
                'actor_id' => $assignedByUser->id,
                'resource_type' => 'task',
                'resource_id' => $task->id,
                'link' => "/tasks/{$task->id}",
                'metadata' => [
                    'task_title' => $task->title,
                    'task_priority' => $task->priority,
                    'task_due_date' => $task->due_date,
                    'task_category' => $task->category,
                ],
            ]);

            $notification->load('actor');
            
            event(new NotificationCreated($notification));

            Log::info('Notification created for task assignment', [
                'notification_id' => $notification->id,
                'task_id' => $task->id,
                'assigned_to_user_id' => $assignedToUserId,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create task notification', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'assigned_to_user_id' => $assignedToUserId,
            ]);
            
            return null;
        }
    }

    /**
     * إنشاء إشعار لجهاز/أصل مُسند
     */
    public function notifyAssetAssigned($asset, int $assignedToUserId, User $assignedByUser): ?Notification
    {
        try {
            $notification = Notification::create([
                'user_id' => $assignedToUserId,
                'type' => 'asset',
                'title' => 'جهاز مسند',
                'body' => "تم إسناد جهاز إليك: {$asset->name_ar}",
                'actor_id' => $assignedByUser->id,
                'resource_type' => 'asset',
                'resource_id' => $asset->id,
                'link' => "/assets/{$asset->id}",
                'metadata' => [
                    'asset_name' => $asset->name_ar,
                    'asset_code' => $asset->asset_code,
                    'asset_category' => $asset->category->name_ar ?? null,
                    'serial_number' => $asset->serial_number,
                ],
            ]);

            $notification->load('actor');
            
            event(new NotificationCreated($notification));

            Log::info('Notification created for asset assignment', [
                'notification_id' => $notification->id,
                'asset_id' => $asset->id,
                'assigned_to_user_id' => $assignedToUserId,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create asset notification', [
                'error' => $e->getMessage(),
                'asset_id' => $asset->id,
                'assigned_to_user_id' => $assignedToUserId,
            ]);
            
            return null;
        }
    }

    /**
     * إنشاء إشعار لمهمة منقولة
     */
    public function notifyTaskTransferred($task, int $newAssignedToUserId, User $transferredByUser, int $previousAssignedToUserId = null): ?Notification
    {
        try {
            $previousUser = null;
            if ($previousAssignedToUserId) {
                $previousUser = User::find($previousAssignedToUserId);
            }

            $title = 'مهمة منقولة';
            $body = "تم نقل المهمة '{$task->title}' إليك بواسطة {$transferredByUser->name_ar}";
            
            if ($previousUser && $previousUser->id != $newAssignedToUserId) {
                $body .= " من {$previousUser->name_ar}";
            }

            $notification = Notification::create([
                'user_id' => $newAssignedToUserId,
                'type' => 'task',
                'title' => $title,
                'body' => $body,
                'actor_id' => $transferredByUser->id,
                'resource_type' => 'task',
                'resource_id' => $task->id,
                'link' => "/tasks/{$task->id}",
                'metadata' => [
                    'task_title' => $task->title,
                    'task_priority' => $task->priority,
                    'task_due_date' => $task->due_date,
                    'task_category' => $task->category,
                    'task_status' => $task->status,
                    'previous_assigned_to' => $previousAssignedToUserId,
                    'previous_assigned_name' => $previousUser ? $previousUser->name_ar : null,
                    'notification_type' => 'task_transferred',
                ],
            ]);

            $notification->load('actor');
            
            event(new NotificationCreated($notification));

            Log::info('Notification created for task transfer', [
                'notification_id' => $notification->id,
                'task_id' => $task->id,
                'new_assigned_to_user_id' => $newAssignedToUserId,
                'previous_assigned_to_user_id' => $previousAssignedToUserId,
                'transferred_by_user_id' => $transferredByUser->id,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create task transfer notification', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'new_assigned_to_user_id' => $newAssignedToUserId,
                'previous_assigned_to_user_id' => $previousAssignedToUserId,
            ]);
            
            return null;
        }
    }

    /**
     * إنشاء إشعار لمهمة تجاوزت 70% من وقتها
     */
    public function notifyTaskOverdueWarning($task, int $userId, User $assignedByUser = null): ?Notification
    {
        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => 'task',
                'title' => 'تحذير: مهمة متأخرة',
                'body' => "المهمة '{$task->display_title}' تجاوزت 70% من وقتها المحدد ولم تكتمل بعد",
                'actor_id' => $assignedByUser ? $assignedByUser->id : null,
                'resource_type' => 'task',
                'resource_id' => $task->id,
                'link' => "/tasks/{$task->id}",
                'metadata' => [
                    'task_title' => $task->display_title,
                    'task_priority' => $task->priority,
                    'task_due_date' => $task->due_date,
                    'task_category' => $task->category,
                    'task_status' => $task->status,
                    'progress_percentage' => 70,
                ],
            ]);

            $notification->load('actor');
            
            event(new NotificationCreated($notification));

            Log::info('Notification created for overdue task warning', [
                'notification_id' => $notification->id,
                'task_id' => $task->id,
                'user_id' => $userId,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create overdue task notification', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'user_id' => $userId,
            ]);
            
            return null;
        }
    }

    /**
     * إنشاء إشعار لعيد ميلاد
     */
    public function notifyBirthday($birthdayUser, int $recipientUserId, User $actorUser = null): ?Notification
    {
        try {
            // Get recipient user
            $recipientUser = User::find($recipientUserId);
            if (!$recipientUser) {
                Log::warning("Recipient user not found: {$recipientUserId}");
                return null;
            }

            // Use current application locale (will be set based on user's session/language preference when viewing)
            // Store both languages in metadata and let the frontend decide
            $locale = $recipientUser->language ?? app()->getLocale() ?? 'ar';
            $isArabic = ($locale === 'ar');

            // Get birthday user name - will be used in actor_name, not in body
            $birthdayUserName = $isArabic 
                ? ($birthdayUser->name_ar ?: $birthdayUser->name)
                : ($birthdayUser->name ?: $birthdayUser->name_ar);

            // Store both languages in metadata for dynamic language switching
            $notification = Notification::create([
                'user_id' => $recipientUserId,
                'type' => 'birthday',
                'title' => $isArabic ? 'عيد ميلاد سعيد! 🎉' : 'Happy Birthday! 🎉', // Default to recipient's language
                'body' => $isArabic 
                    ? "اليوم هو عيد ميلاد! نتمنى له يوماً سعيداً! 🎂"
                    : "It's their birthday today! We wish them a wonderful day! 🎂", // Default to recipient's language
                'actor_id' => $actorUser ? $actorUser->id : $birthdayUser->id,
                'resource_type' => 'user',
                'resource_id' => $birthdayUser->id,
                'link' => "/users/{$birthdayUser->id}",
                'metadata' => [
                    'birthday_user_id' => $birthdayUser->id,
                    'birthday_user_name' => $birthdayUserName,
                    'birthday_date' => $birthdayUser->birthday,
                    'notification_type' => 'birthday',
                    // Store translations for both languages
                    'title_ar' => 'عيد ميلاد سعيد! 🎉',
                    'title_en' => 'Happy Birthday! 🎉',
                    'body_ar' => "اليوم هو عيد ميلاد! نتمنى له يوماً سعيداً! 🎂",
                    'body_en' => "It's their birthday today! We wish them a wonderful day! 🎂",
                ],
            ]);

            $notification->load('actor');
            
            event(new NotificationCreated($notification));

            Log::info('Notification created for birthday', [
                'notification_id' => $notification->id,
                'birthday_user_id' => $birthdayUser->id,
                'recipient_user_id' => $recipientUserId,
                'locale' => $locale,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create birthday notification', [
                'error' => $e->getMessage(),
                'birthday_user_id' => $birthdayUser->id,
                'recipient_user_id' => $recipientUserId,
            ]);
            
            return null;
        }
    }

    /**
     * إشعار صاحب عيد الميلاد بنفسه
     */
    public function notifyBirthdayToSelf(User $birthdayUser): ?Notification
    {
        try {
            // Use current application locale (will be set based on user's session/language preference when viewing)
            $locale = $birthdayUser->language ?? app()->getLocale() ?? 'ar';
            $isArabic = ($locale === 'ar');

            // Calculate age
            $age = null;
            if ($birthdayUser->birthday || $birthdayUser->birth_date) {
                $birthday = $birthdayUser->birthday ?? $birthdayUser->birth_date;
                $age = \Carbon\Carbon::today()->year - \Carbon\Carbon::parse($birthday)->year;
            }

            // Get birthday user name in appropriate language
            $birthdayUserName = $isArabic 
                ? ($birthdayUser->name_ar ?: $birthdayUser->name)
                : ($birthdayUser->name ?: $birthdayUser->name_ar);

            // Prepare notification text - Store both languages in metadata
            $titleAr = 'عيد ميلاد سعيد! 🎉';
            $titleEn = 'Happy Birthday! 🎉';
            $bodyAr = $age 
                ? "عيد ميلادك اليوم! نتمنى لك عيد ميلاد سعيد في السنة الـ {$age}! 🎂🎈"
                : "عيد ميلادك اليوم! عيد ميلاد سعيد! 🎂🎈";
            $bodyEn = $age
                ? "It's your birthday today! Happy {$age}th Birthday! 🎂🎈"
                : "It's your birthday today! Happy Birthday! 🎂🎈";

            $notification = Notification::create([
                'user_id' => $birthdayUser->id,
                'type' => 'birthday',
                'title' => $isArabic ? $titleAr : $titleEn, // Default to user's language
                'body' => $isArabic ? $bodyAr : $bodyEn, // Default to user's language
                'actor_id' => $birthdayUser->id,
                'resource_type' => 'user',
                'resource_id' => $birthdayUser->id,
                'link' => "/users/{$birthdayUser->id}",
                'metadata' => [
                    'birthday_user_id' => $birthdayUser->id,
                    'birthday_user_name' => $birthdayUserName,
                    'birthday_date' => $birthdayUser->birthday ?? $birthdayUser->birth_date,
                    'notification_type' => 'birthday',
                    'is_self_notification' => true,
                    'age' => $age,
                    // Store translations for both languages
                    'title_ar' => $titleAr,
                    'title_en' => $titleEn,
                    'body_ar' => $bodyAr,
                    'body_en' => $bodyEn,
                ],
            ]);

            $notification->load('actor');
            
            event(new NotificationCreated($notification));

            Log::info('Birthday self-notification created', [
                'notification_id' => $notification->id,
                'birthday_user_id' => $birthdayUser->id,
                'locale' => $locale,
                'age' => $age,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create birthday self-notification', [
                'error' => $e->getMessage(),
                'birthday_user_id' => $birthdayUser->id,
            ]);
            
            return null;
        }
    }

    /**
     * إشعار جميع المستخدمين بعيد ميلاد مستخدم
     */
    public function notifyAllUsersAboutBirthday($birthdayUser): array
    {
        $notifications = [];
        
        // Get users who want to receive birthday notifications
        // receive_birthday_notifications defaults to true, so null also means true
        $users = User::where('id', '!=', $birthdayUser->id)
                    ->where(function($query) {
                        $query->where('receive_birthday_notifications', true)
                              ->orWhereNull('receive_birthday_notifications');
                    })
                    ->get();
        
        Log::info("Found {$users->count()} users to notify about birthday (excluding users who disabled notifications)");
        
        foreach ($users as $user) {
            $notification = $this->notifyBirthday($birthdayUser, $user->id, $birthdayUser);
            if ($notification) {
                $notifications[] = $notification;
            }
        }
        
        Log::info("Created " . count($notifications) . " birthday notifications");
        return $notifications;
    }

    /**
     * إنشاء إشعار لإكمال مهمة
     */
    public function notifyTaskCompleted($task, User $completedByUser): ?Notification
    {
        try {
            // إرسال إشعار لمنشئ المهمة إذا لم يكن هو من أكملها
            if ($task->created_by != $completedByUser->id) {
                $notification = Notification::create([
                    'user_id' => $task->created_by,
                    'type' => 'task',
                    'title' => 'مهمة مكتملة',
                    'body' => "تم إكمال المهمة '{$task->title}' بواسطة {$completedByUser->name_ar}",
                    'actor_id' => $completedByUser->id,
                    'resource_type' => 'task',
                    'resource_id' => $task->id,
                    'link' => "/tasks/{$task->id}",
                    'metadata' => [
                        'task_title' => $task->title,
                        'task_priority' => $task->priority,
                        'task_due_date' => $task->due_date,
                        'task_category' => $task->category,
                        'task_status' => $task->status,
                        'completed_by' => $completedByUser->id,
                        'completed_by_name' => $completedByUser->name_ar,
                        'notification_type' => 'task_completed',
                    ],
                ]);

                $notification->load('actor');
                
                event(new NotificationCreated($notification));

                Log::info('Notification created for task completion', [
                    'notification_id' => $notification->id,
                    'task_id' => $task->id,
                    'completed_by_user_id' => $completedByUser->id,
                    'task_creator_id' => $task->created_by,
                ]);

                return $notification;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to create task completion notification', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'completed_by_user_id' => $completedByUser->id,
            ]);
            
            return null;
        }
    }

    /**
     * إنشاء إشعار لتعديل مهمة من قبل رئيس
     */
    public function notifyTaskUpdatedBySupervisor($task, int $assignedToUserId, User $updatedByUser): ?Notification
    {
        try {
            $notification = Notification::create([
                'user_id' => $assignedToUserId,
                'type' => 'task',
                'title' => 'تم تعديل مهمة',
                'body' => "تم تعديل المهمة '{$task->title}' بواسطة {$updatedByUser->name_ar}",
                'actor_id' => $updatedByUser->id,
                'resource_type' => 'task',
                'resource_id' => $task->id,
                'link' => "/tasks/{$task->id}",
                'metadata' => [
                    'task_title' => $task->title,
                    'task_priority' => $task->priority,
                    'task_due_date' => $task->due_date,
                    'task_category' => $task->category,
                    'task_status' => $task->status,
                    'updated_by' => $updatedByUser->id,
                    'updated_by_name' => $updatedByUser->name_ar,
                    'notification_type' => 'task_updated_by_supervisor',
                ],
            ]);

            $notification->load('actor');
            
            event(new NotificationCreated($notification));

            Log::info('Notification created for task update by supervisor', [
                'notification_id' => $notification->id,
                'task_id' => $task->id,
                'assigned_to_user_id' => $assignedToUserId,
                'updated_by_user_id' => $updatedByUser->id,
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create task update notification', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'assigned_to_user_id' => $assignedToUserId,
                'updated_by_user_id' => $updatedByUser->id,
            ]);
            
            return null;
        }
    }

    /**
     * إنشاء إشعار عام
     */
    public function createNotification(array $data): ?Notification
    {
        try {
            $notification = Notification::create($data);
            $notification->load('actor');
            
            event(new NotificationCreated($notification));

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create notification', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            
            return null;
        }
    }
}

