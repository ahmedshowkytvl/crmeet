<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckTaskOverdueNotifications implements ShouldQueue
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
        try {
            // البحث عن المهام التي تجاوزت 70% من وقتها ولم تكتمل
            $overdueTasks = Task::whereNotNull('start_datetime')
                ->whereNotNull('end_datetime')
                ->where('status', '!=', 'completed')
                ->get()
                ->filter(function ($task) {
                    return $task->isOverdueWarning();
                });

            Log::info('Checking overdue tasks', [
                'total_tasks_checked' => Task::whereNotNull('start_datetime')
                    ->whereNotNull('end_datetime')
                    ->where('status', '!=', 'completed')
                    ->count(),
                'overdue_tasks_found' => $overdueTasks->count()
            ]);

            foreach ($overdueTasks as $task) {
                // التحقق من عدم وجود إشعار سابق لهذه المهمة في آخر 24 ساعة
                $recentNotification = Notification::where('user_id', $task->assigned_to)
                    ->where('type', 'task')
                    ->where('resource_type', 'task')
                    ->where('resource_id', $task->id)
                    ->where('title', 'تحذير: مهمة متأخرة')
                    ->where('created_at', '>=', now()->subDay())
                    ->first();

                if (!$recentNotification) {
                    // إنشاء إشعار للموظف المكلف
                    $notificationService->notifyTaskOverdueWarning(
                        $task, 
                        $task->assigned_to, 
                        $task->createdBy
                    );

                    // إنشاء إشعار لمدير الفريق (منشئ المهمة)
                    if ($task->created_by && $task->created_by !== $task->assigned_to) {
                        $notificationService->notifyTaskOverdueWarning(
                            $task, 
                            $task->created_by, 
                            $task->createdBy
                        );
                    }

                    Log::info('Overdue notification created', [
                        'task_id' => $task->id,
                        'task_title' => $task->display_title,
                        'assigned_to' => $task->assigned_to,
                        'created_by' => $task->created_by,
                        'progress_percentage' => $task->getProgressPercentage()
                    ]);
                } else {
                    Log::debug('Skipping notification - recent notification exists', [
                        'task_id' => $task->id,
                        'last_notification_id' => $recentNotification->id,
                        'last_notification_created' => $recentNotification->created_at
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to check overdue task notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}