<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // جلب جميع المهام حسب الصلاحيات
        $query = Task::with(['assignedTo', 'createdBy', 'department']);
        
        // تطبيق الفلاتر حسب الصلاحيات
        // مؤقتاً: عرض جميع المهام للاختبار
        if (false && $user->role && $user->role->slug === 'employee') {
            // الموظف العادي يرى فقط مهامه
            $query->where('assigned_to', $user->id);
        } elseif (false && $user->role && in_array($user->role->slug, ['manager', 'head_manager'])) {
            // المدير يرى مهام فريقه
            $teamMemberIds = User::where('manager_id', $user->id)->pluck('id')->toArray();
            $teamMemberIds[] = $user->id;
            $query->whereIn('assigned_to', $teamMemberIds);
        }
        // المدير العام والمدير التنفيذي يرون جميع المهام
        
        $allTasks = $query->get();
        
        // Debug: إضافة معلومات للتشخيص
        Log::info('Task Dashboard Debug', [
            'user_id' => $user->id,
            'user_role' => $user->role ? $user->role->slug : 'no_role',
            'total_tasks_found' => $allTasks->count(),
            'marketing_tasks' => $allTasks->where('category', 'marketing')->count(),
            'development_tasks' => $allTasks->where('category', 'development')->count(),
        ]);
        
        // تصنيف المهام حسب النوع
        $marketingTasks = $allTasks->where('category', 'marketing');
        $developmentTasks = $allTasks->where('category', 'development');
        $supportTasks = $allTasks->where('category', 'support');
        $salesTasks = $allTasks->where('category', 'sales');
        $designTasks = $allTasks->where('category', 'design');
        $communicationTasks = $allTasks->where('category', 'communication');
        $researchTasks = $allTasks->where('category', 'research');
        $generalTasks = $allTasks->where('category', 'general');
        
        // جلب المهام المكتملة مؤخراً (آخر 30 يوم)
        $recentCompletedTasks = $allTasks
            ->where('status', 'completed')
            ->where('actual_end_datetime', '>=', now()->subDays(30))
            ->sortByDesc('actual_end_datetime')
            ->take(10);
        
        // إحصائيات المهام المكتملة حسب النوع للرسم البياني
        $completedTasksByCategory = [
            'marketing' => $marketingTasks->where('status', 'completed')->count(),
            'development' => $developmentTasks->where('status', 'completed')->count(),
            'support' => $supportTasks->where('status', 'completed')->count(),
            'sales' => $salesTasks->where('status', 'completed')->count(),
        ];
        
        // إحصائيات تقدم المهام حسب النوع
        $taskProgressByType = [];
        $categories = [
            'marketing' => $marketingTasks,
            'development' => $developmentTasks,
            'support' => $supportTasks,
            'sales' => $salesTasks,
            'design' => $designTasks,
            'communication' => $communicationTasks,
            'research' => $researchTasks,
            'general' => $generalTasks,
        ];
        
        foreach ($categories as $category => $tasks) {
            if ($tasks->count() > 0) {
                $completedCount = $tasks->where('status', 'completed')->count();
                $progressPercentage = round(($completedCount / $tasks->count()) * 100, 1);
                
                $taskProgressByType[$category] = [
                    'total' => $tasks->count(),
                    'completed' => $completedCount,
                    'pending' => $tasks->where('status', 'pending')->count(),
                    'in_progress' => $tasks->where('status', 'in_progress')->count(),
                    'progress_percentage' => $progressPercentage,
                ];
            }
        }
        
        // إحصائيات إضافية
        $totalTasks = $allTasks->count();
        $completedTasksCount = $allTasks->where('status', 'completed')->count();
        $pendingTasksCount = $allTasks->where('status', 'pending')->count();
        $inProgressTasksCount = $allTasks->where('status', 'in_progress')->count();
        
        // معدل الإنجاز
        $completionRate = $totalTasks > 0 ? round(($completedTasksCount / $totalTasks) * 100, 1) : 0;
        
        // المهام المتأخرة
        $overdueTasks = $allTasks->filter(function ($task) {
            return $task->due_date && $task->due_date < now() && $task->status !== 'completed';
        });
        
        return view('task-dashboard', compact(
            'marketingTasks',
            'developmentTasks', 
            'supportTasks',
            'salesTasks',
            'designTasks',
            'communicationTasks',
            'researchTasks',
            'generalTasks',
            'recentCompletedTasks',
            'completedTasksByCategory',
            'taskProgressByType',
            'totalTasks',
            'completedTasksCount',
            'pendingTasksCount',
            'inProgressTasksCount',
            'completionRate',
            'overdueTasks'
        ));
    }
    
    /**
     * جلب بيانات المهام المكتملة مؤخراً للرسم البياني
     */
    public function getRecentCompletedTasksData()
    {
        $user = Auth::user();
        
        $query = Task::where('status', 'completed')
            ->where('actual_end_datetime', '>=', now()->subDays(30));
        
        // تطبيق الفلاتر حسب الصلاحيات
        if ($user->role && $user->role->slug === 'employee') {
            $query->where('assigned_to', $user->id);
        } elseif ($user->role && in_array($user->role->slug, ['manager', 'head_manager'])) {
            $teamMemberIds = User::where('manager_id', $user->id)->pluck('id')->toArray();
            $teamMemberIds[] = $user->id;
            $query->whereIn('assigned_to', $teamMemberIds);
        }
        
        $completedTasks = $query->get();
        
        // تجميع البيانات حسب الأسبوع
        $weeklyData = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $weekTasks = $completedTasks->filter(function ($task) use ($weekStart, $weekEnd) {
                return $task->actual_end_datetime && 
                       $task->actual_end_datetime >= $weekStart && 
                       $task->actual_end_datetime <= $weekEnd;
            });
            
            $weeklyData[] = [
                'week' => $weekStart->format('M d'),
                'marketing' => $weekTasks->where('category', 'marketing')->count(),
                'development' => $weekTasks->where('category', 'development')->count(),
                'support' => $weekTasks->where('category', 'support')->count(),
                'sales' => $weekTasks->where('category', 'sales')->count(),
            ];
        }
        
        return response()->json($weeklyData);
    }
}
