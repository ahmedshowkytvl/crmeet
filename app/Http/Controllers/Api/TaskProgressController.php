<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TaskProgressController extends Controller
{
    /**
     * حساب التقدم لمستخدم معين
     */
    public function getUserProgress(Request $request): JsonResponse
    {
        $userId = $request->get('user_id');
        $date = $request->get('date', now()->format('Y-m-d'));
        
        if (!$userId) {
            return response()->json([
                'error' => 'User ID is required'
            ], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        try {
            $progress = Task::calculateProgressForUser($userId, $date);
            $completedTime = Task::getCompletedEstimatedTimeForUser($userId, $date);
            
            // الحصول على المهام المكتملة لهذا اليوم
            $completedTasks = Task::where('assigned_to', $userId)
                ->where('status', 'completed')
                ->whereDate('actual_end_datetime', $date)
                ->with('taskTemplate')
                ->get();

            // حساب إحصائيات إضافية
            $stats = [
                'total_completed_tasks' => $completedTasks->count(),
                'total_estimated_time' => $completedTime,
                'progress_percentage' => round($progress, 2),
                'work_day_completion' => round($completedTime / 8, 2), // 8 ساعات يوم عمل
                'remaining_hours' => max(0, 8 - $completedTime), // ساعات متبقية ليوم العمل
                'efficiency' => $completedTime > 8 ? round(($completedTime / 8) * 100, 2) : round(($completedTime / 8) * 100, 2)
            ];

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'name_ar' => $user->name_ar
                ],
                'date' => $date,
                'stats' => $stats,
                'completed_tasks' => $completedTasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->display_title,
                        'estimated_time' => $task->estimated_time,
                        'template_name' => $task->taskTemplate ? $task->taskTemplate->display_name : null,
                        'completed_at' => $task->actual_end_datetime->format('Y-m-d H:i:s')
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error calculating progress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حساب التقدم لجميع المستخدمين في تاريخ معين
     */
    public function getAllUsersProgress(Request $request): JsonResponse
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        try {
            $users = User::whereHas('assignedTasks', function ($query) use ($date) {
                $query->where('status', 'completed')
                      ->whereDate('actual_end_datetime', $date);
            })->with(['department'])->get();

            $progressData = $users->map(function ($user) use ($date) {
                $progress = Task::calculateProgressForUser($user->id, $date);
                $completedTime = Task::getCompletedEstimatedTimeForUser($user->id, $date);
                
                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_name_ar' => $user->name_ar,
                    'department' => $user->department ? $user->department->name : 'غير محدد',
                    'progress_percentage' => round($progress, 2),
                    'completed_time' => round($completedTime, 2),
                    'work_day_completion' => round($completedTime / 8, 2),
                    'efficiency' => $completedTime > 8 ? round(($completedTime / 8) * 100, 2) : round(($completedTime / 8) * 100, 2)
                ];
            });

            // ترتيب حسب التقدم (الأعلى أولاً)
            $progressData = $progressData->sortByDesc('progress_percentage')->values();

            return response()->json([
                'success' => true,
                'date' => $date,
                'total_users' => $progressData->count(),
                'average_progress' => $progressData->avg('progress_percentage'),
                'users_progress' => $progressData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error calculating all users progress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حساب التقدم لفترة زمنية معينة
     */
    public function getProgressForPeriod(Request $request): JsonResponse
    {
        $userId = $request->get('user_id');
        $startDate = $request->get('start_date', now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfWeek()->format('Y-m-d'));
        
        if (!$userId) {
            return response()->json([
                'error' => 'User ID is required'
            ], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            
            $dailyProgress = [];
            $totalProgress = 0;
            $totalCompletedTime = 0;
            $workingDays = 0;

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                // تخطي عطلة نهاية الأسبوع
                if ($date->isWeekend()) {
                    continue;
                }
                
                $workingDays++;
                $dateStr = $date->format('Y-m-d');
                
                $progress = Task::calculateProgressForUser($userId, $dateStr);
                $completedTime = Task::getCompletedEstimatedTimeForUser($userId, $dateStr);
                
                $dailyProgress[] = [
                    'date' => $dateStr,
                    'progress_percentage' => round($progress, 2),
                    'completed_time' => round($completedTime, 2),
                    'work_day_completion' => round($completedTime / 8, 2)
                ];
                
                $totalProgress += $progress;
                $totalCompletedTime += $completedTime;
            }

            $averageProgress = $workingDays > 0 ? $totalProgress / $workingDays : 0;
            $averageCompletedTime = $workingDays > 0 ? $totalCompletedTime / $workingDays : 0;

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'name_ar' => $user->name_ar
                ],
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'working_days' => $workingDays
                ],
                'summary' => [
                    'total_progress' => round($totalProgress, 2),
                    'average_progress' => round($averageProgress, 2),
                    'total_completed_time' => round($totalCompletedTime, 2),
                    'average_completed_time' => round($averageCompletedTime, 2),
                    'total_work_days_completion' => round($totalCompletedTime / 8, 2),
                    'average_efficiency' => round(($averageCompletedTime / 8) * 100, 2)
                ],
                'daily_progress' => $dailyProgress
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error calculating period progress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إحصائيات القوالب المستخدمة
     */
    public function getTemplateStats(Request $request): JsonResponse
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        try {
            $templateStats = Task::where('status', 'completed')
                ->whereDate('actual_end_datetime', $date)
                ->whereNotNull('task_template_id')
                ->with('taskTemplate')
                ->get()
                ->groupBy('task_template_id')
                ->map(function ($tasks, $templateId) {
                    $template = $tasks->first()->taskTemplate;
                    if (!$template) return null;
                    
                    return [
                        'template_id' => $template->id,
                        'template_name' => $template->name,
                        'template_name_ar' => $template->name_ar,
                        'department' => $template->department,
                        'estimated_time' => $template->estimated_time,
                        'usage_count' => $tasks->count(),
                        'total_time' => $tasks->sum('estimated_time'),
                        'average_completion_time' => $tasks->avg('estimated_time')
                    ];
                })
                ->filter()
                ->values()
                ->sortByDesc('usage_count');

            return response()->json([
                'success' => true,
                'date' => $date,
                'template_stats' => $templateStats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error getting template stats: ' . $e->getMessage()
            ], 500);
        }
    }
}