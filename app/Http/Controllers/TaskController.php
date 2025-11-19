<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * عرض قائمة المهام
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Task::with(['assignedTo', 'createdBy', 'department']);

        // تطبيق الفلاتر
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('repeat_type')) {
            $query->where('repeat_type', $request->repeat_type);
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // فلترة المهام حسب الصلاحيات
        if (!$this->isCeo($user) && !$this->isHeadManager($user)) {
            // إذا لم يكن CEO أو Head Manager، يرى فقط المهام المتعلقة به
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
                
                // إذا كان Manager، يرى مهام قسمه
                if ($this->isManager($user)) {
                    $q->orWhere('department_id', $user->department_id);
                }
                
                // إذا كان Team Leader، يرى مهام فريقه
                if ($this->isTeamLeader($user)) {
                    $q->orWhereHas('assignedTo', function($subQ) use ($user) {
                        $subQ->where('manager_id', $user->id);
                    })->orWhereHas('createdBy', function($subQ) use ($user) {
                        $subQ->where('manager_id', $user->id);
                    });
                }
            });
        }

        $tasks = $query->latest()->paginate(15);
        $users = $this->getAvailableUsers($user);
        $departments = Department::orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'users', 'departments'));
    }

    /**
     * عرض صفحة إنشاء مهمة جديدة
     */
    public function create()
    {
        $user = Auth::user();
        $users = $this->getAvailableUsers($user);
        $departments = Department::orderBy('name')->get();
        
        return view('tasks.create', compact('users', 'departments'));
    }

    /**
     * حفظ مهمة جديدة
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // التحقق من صلاحية المستخدم لتكليف شخص آخر
        if ($request->assigned_to != $user->id) {
            if (!$this->canAssignToUser($user, $request->assigned_to)) {
                return back()->with('error', __('messages.no_permission_to_assign_task'))->withInput();
            }
        }

        // معالجة وقت الاستحقاق قبل التحقق
        if ($request->has('due_time') && !empty($request->due_time)) {
            $dueTime = $request->due_time;
            
            // تحويل تنسيق 12 ساعة إلى 24 ساعة إذا لزم الأمر
            if (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $dueTime, $matches)) {
                $hours = intval($matches[1]);
                $minutes = $matches[2];
                $period = strtoupper($matches[3]);
                
                if ($period === 'AM' && $hours === 12) {
                    $hours = 0;
                } elseif ($period === 'PM' && $hours !== 12) {
                    $hours += 12;
                }
                
                $request->merge(['due_time' => sprintf('%02d:%s', $hours, $minutes)]);
            }
        }

        $rules = [
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'category' => 'nullable|string|max:100',
            'repeat_type' => 'required|in:one_time,daily,quarterly,yearly',
            'due_date' => 'required|date|after_or_equal:today',
            'due_time' => 'nullable|date_format:H:i',
            'sla_hours' => 'nullable|integer|min:1',
            'start_datetime' => 'nullable|date|after_or_equal:now',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'estimated_time' => 'nullable|numeric|min:0',
            'task_template_id' => 'nullable|exists:task_templates,id',
        ];

        // إذا كانت المهمة للمستخدم نفسه، يمكنه تحديد الحالة
        if ($request->assigned_to == $user->id) {
            $rules['status'] = 'required|in:pending,in_progress,completed,on_hold';
        }

        $validated = $request->validate($rules, [
            'title.required' => __('messages.task_title_required'),
            'description.required' => __('messages.task_description_required'),
            'assigned_to.required' => __('messages.assigned_to_required'),
            'assigned_to.exists' => __('messages.invalid_user'),
            'priority.required' => __('messages.priority_required'),
            'repeat_type.required' => __('messages.repeat_type_required'),
            'due_date.required' => __('messages.due_date_required'),
            'due_date.after_or_equal' => __('messages.due_date_must_be_today_or_later'),
            'due_time.date_format' => 'تنسيق وقت الاستحقاق يجب أن يكون بصيغة 24 ساعة (مثال: 14:30)',
        ]);

        $data = $validated;
        $data['created_by'] = $user->id;
        
        // تحديد القسم
        $assignedUser = User::find($request->assigned_to);
        $data['department_id'] = $assignedUser->department_id;

        // تحديد إذا كان المنشئ يمكنه تحديث الحالة (إذا كانت المهمة له)
        $data['creator_can_update_status'] = ($request->assigned_to == $user->id);

        // إذا لم يتم تحديد الحالة، نضعها كـ pending
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        // حساب due_datetime بناءً على الطريقة المختارة
        try {
            $data['due_datetime'] = $this->calculateDueDateTimeFromInput($request);
        } catch (\Exception $e) {
            Log::error('Error calculating due_datetime: ' . $e->getMessage());
            // قيمة افتراضية في حالة الخطأ
            $data['due_datetime'] = now()->addDays(1);
        }

        // حساب تاريخ التكرار التالي إذا كانت المهمة متكررة
        if ($data['repeat_type'] !== 'one_time') {
            $data['next_repeat_at'] = $this->calculateNextRepeatDate($data['due_date'], $data['repeat_type']);
            $data['is_repeat_active'] = true;
        }

        $task = Task::create($data);

        // إرسال إشعار للموظف المكلف إذا كانت المهمة لشخص آخر
        if ($request->assigned_to != $user->id) {
            $notificationService = app(NotificationService::class);
            $assignedUser = User::find($request->assigned_to);
            
            $notificationService->notifyTaskAssigned($task, $assignedUser->id, $user);
        }

        return redirect()->route('tasks.index')->with('success', __('messages.task_created_successfully'));
    }

    /**
     * عرض تفاصيل مهمة
     */
    public function show(Task $task)
    {
        // التحقق من صلاحية المستخدم لعرض المهمة
        $user = Auth::user();
        if (!$this->canViewTask($user, $task)) {
            abort(403, __('messages.unauthorized_access'));
        }

        $task->load(['assignedTo', 'createdBy', 'department']);
        return view('tasks.show', compact('task'));
    }

    /**
     * عرض صفحة تعديل مهمة
     */
    public function edit(Task $task)
    {
        $user = Auth::user();
        
        // التحقق من صلاحية المستخدم لتعديل المهمة
        if (!$this->canEditTask($user, $task)) {
            abort(403, __('messages.unauthorized_access'));
        }

        $users = $this->getAvailableUsers($user);
        $departments = Department::orderBy('name')->get();
        
        return view('tasks.edit', compact('task', 'users', 'departments'));
    }

    /**
     * تحديث مهمة
     */
    public function update(Request $request, Task $task)
    {
        $user = Auth::user();
        
        // التحقق من صلاحية المستخدم لتعديل المهمة
        if (!$this->canEditTask($user, $task)) {
            abort(403, __('messages.unauthorized_access'));
        }

        // معالجة وقت الاستحقاق قبل التحقق
        if ($request->has('due_time') && !empty($request->due_time)) {
            $dueTime = $request->due_time;
            
            // تحويل تنسيق 12 ساعة إلى 24 ساعة إذا لزم الأمر
            if (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $dueTime, $matches)) {
                $hours = intval($matches[1]);
                $minutes = $matches[2];
                $period = strtoupper($matches[3]);
                
                if ($period === 'AM' && $hours === 12) {
                    $hours = 0;
                } elseif ($period === 'PM' && $hours !== 12) {
                    $hours += 12;
                }
                
                $request->merge(['due_time' => sprintf('%02d:%s', $hours, $minutes)]);
            }
        }

        $rules = [
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'category' => 'nullable|string|max:100',
            'repeat_type' => 'required|in:one_time,daily,quarterly,yearly',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
            'sla_hours' => 'nullable|integer|min:1',
            'start_datetime' => 'nullable|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
        ];

        // التحقق من صلاحية تحديث الحالة
        if ($task->canUserUpdateStatus($user->id)) {
            $rules['status'] = 'required|in:pending,in_progress,completed,on_hold';
        }

        // التحقق من صلاحية تحديث الأولوية
        if ($task->canUserUpdatePriority($user->id)) {
            $rules['priority'] = 'required|in:low,medium,high';
        }

        $validated = $request->validate($rules, [
            'title.required' => __('messages.task_title_required'),
            'description.required' => __('messages.task_description_required'),
            'assigned_to.required' => __('messages.assigned_to_required'),
            'due_date.required' => __('messages.due_date_required'),
            'due_time.date_format' => 'تنسيق وقت الاستحقاق يجب أن يكون بصيغة 24 ساعة (مثال: 14:30)',
        ]);

        $data = $validated;
        
        // تحديث القسم إذا تغير المستخدم المكلف
        if ($request->assigned_to != $task->assigned_to) {
            $assignedUser = User::find($request->assigned_to);
            $data['department_id'] = $assignedUser->department_id;
        }

        // حساب due_datetime بناءً على الطريقة المختارة
        try {
            $data['due_datetime'] = $this->calculateDueDateTimeFromInput($request);
        } catch (\Exception $e) {
            Log::error('Error calculating due_datetime: ' . $e->getMessage());
            // قيمة افتراضية في حالة الخطأ
            $data['due_datetime'] = now()->addDays(1);
        }

        // تحديث تاريخ التكرار التالي إذا تغير نوع التكرار أو تاريخ الاستحقاق
        if ($data['repeat_type'] !== 'one_time' && 
            ($data['repeat_type'] != $task->repeat_type || $data['due_date'] != $task->due_date)) {
            $data['next_repeat_at'] = $this->calculateNextRepeatDate($data['due_date'], $data['repeat_type']);
        } elseif ($data['repeat_type'] === 'one_time') {
            $data['next_repeat_at'] = null;
            $data['is_repeat_active'] = false;
        }

        // عدم السماح بتغيير منشئ المهمة
        unset($data['created_by']);
        
        // حفظ المكلف السابق قبل التحديث
        $previousAssignedTo = $task->assigned_to;
        
        $task->update($data);

        // إرسال إشعار إذا تم تغيير المكلف
        if ($request->assigned_to != $previousAssignedTo && $request->assigned_to != $user->id) {
            $notificationService = app(NotificationService::class);
            $assignedUser = User::find($request->assigned_to);
            
            // إذا كانت المهمة لها مكلف سابق، نرسل إشعار نقل
            if ($previousAssignedTo && $previousAssignedTo != $request->assigned_to) {
                $notificationService->notifyTaskTransferred($task, $assignedUser->id, $user, $previousAssignedTo);
            } else {
                // إذا لم تكن هناك مهمة مكلفة سابقاً، نرسل إشعار إسناد
                $notificationService->notifyTaskAssigned($task, $assignedUser->id, $user);
            }
        }

        // إرسال إشعار للمكلف إذا تم تعديل المهمة من قبل رئيس
        if ($this->isTeamLeaderOrAbove($user) && $task->assigned_to != $user->id) {
            $notificationService = app(NotificationService::class);
            $assignedUser = User::find($task->assigned_to);
            
            if ($assignedUser) {
                $notificationService->notifyTaskUpdatedBySupervisor($task, $assignedUser->id, $user);
            }
        }

        return redirect()->route('tasks.index')->with('success', __('messages.task_updated_successfully'));
    }

    /**
     * حذف مهمة
     */
    public function destroy(Task $task)
    {
        $user = Auth::user();
        
        // فقط منشئ المهمة أو CEO/Head Manager يمكنه حذف المهمة
        if ($task->created_by != $user->id && !$this->isCeo($user) && !$this->isHeadManager($user)) {
            abort(403, __('messages.unauthorized_access'));
        }

        $task->delete();
        return redirect()->route('tasks.index')->with('success', __('messages.task_deleted_successfully'));
    }

    /**
     * تحديث حالة المهمة فقط
     */
    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        
        if (!$task->canUserUpdateStatus($user->id)) {
            return back()->with('error', __('messages.no_permission_to_update_status'));
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,on_hold',
        ]);

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        // إرسال إشعار عند إكمال المهمة
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyTaskCompleted($task, $user);
        }

        return back()->with('success', __('messages.status_updated_successfully'));
    }

    /**
     * التحقق من أن المستخدم CEO
     */
    private function isCeo($user): bool
    {
        if (!$user->role) {
            return false;
        }
        
        return $user->role->slug === 'ceo';
    }

    /**
     * التحقق من أن المستخدم Head Manager
     */
    private function isHeadManager($user): bool
    {
        if (!$user->role) {
            return false;
        }
        
        return $user->role->slug === 'head_manager';
    }

    /**
     * التحقق من أن المستخدم Manager
     */
    private function isManager($user): bool
    {
        if (!$user->role) {
            return false;
        }
        
        return $user->role->slug === 'manager';
    }

    /**
     * التحقق من أن المستخدم Team Leader
     */
    private function isTeamLeader($user): bool
    {
        if (!$user->role) {
            return false;
        }
        
        return $user->role->slug === 'team_leader';
    }

    /**
     * التحقق من أن المستخدم Employee
     */
    private function isEmployee($user): bool
    {
        if (!$user->role) {
            return false;
        }
        
        return $user->role->slug === 'employee';
    }

    /**
     * التحقق من أن المستخدم CEO أو Head Manager
     */
    private function isCeoOrHeadManager($user): bool
    {
        return $this->isCeo($user) || $this->isHeadManager($user);
    }

    /**
     * التحقق من أن المستخدم مدير أو أعلى
     */
    private function isManagerOrAbove($user): bool
    {
        return $this->isCeo($user) || $this->isHeadManager($user) || $this->isManager($user);
    }

    /**
     * التحقق من أن المستخدم Team Leader أو أعلى
     */
    private function isTeamLeaderOrAbove($user): bool
    {
        return $this->isCeo($user) || $this->isHeadManager($user) || $this->isManager($user) || $this->isTeamLeader($user);
    }

    /**
     * الحصول على المستخدمين المتاحين حسب الصلاحيات
     */
    private function getAvailableUsers($user)
    {
        if ($this->isCeo($user)) {
            // CEO يمكنه تكليف أي شخص
            return User::where('is_archived', false)->get();
        } elseif ($this->isHeadManager($user)) {
            // Head Manager يمكنه تكليف أي شخص
            return User::where('is_archived', false)->get();
        } elseif ($this->isManager($user)) {
            // Manager يمكنه تكليف أشخاص في قسمه
            return User::where('department_id', $user->department_id)
                       ->where('is_archived', false)
                       ->get();
        } elseif ($this->isTeamLeader($user)) {
            // Team Leader يمكنه تكليف أعضاء فريقه
            return User::where('manager_id', $user->id)
                       ->where('is_archived', false)
                       ->get();
        } else {
            // Employee يمكنه فقط تكليف نفسه
            return User::where('id', $user->id)->get();
        }
    }

    /**
     * التحقق من صلاحية تكليف مستخدم معين
     */
    private function canAssignToUser($currentUser, $targetUserId): bool
    {
        if ($this->isCeo($currentUser) || $this->isHeadManager($currentUser)) {
            return true;
        }

        if ($this->isManager($currentUser)) {
            $targetUser = User::find($targetUserId);
            return $targetUser && $targetUser->department_id == $currentUser->department_id;
        }

        if ($this->isTeamLeader($currentUser)) {
            $targetUser = User::find($targetUserId);
            return $targetUser && $targetUser->manager_id == $currentUser->id;
        }

        // Employee يمكنه فقط تكليف نفسه
        return $currentUser->id == $targetUserId;
    }

    /**
     * التحقق من صلاحية عرض المهمة
     */
    private function canViewTask($user, $task): bool
    {
        // CEO و Head Manager يمكنهم رؤية كل المهام
        if ($this->isCeo($user) || $this->isHeadManager($user)) {
            return true;
        }

        // يمكن للمستخدم رؤية المهمة إذا كان مكلف بها أو أنشأها
        if ($task->assigned_to == $user->id || $task->created_by == $user->id) {
            return true;
        }

        // Manager يمكنه رؤية مهام قسمه
        if ($this->isManager($user) && $task->department_id == $user->department_id) {
            return true;
        }

        // Team Leader يمكنه رؤية مهام فريقه
        if ($this->isTeamLeader($user)) {
            $assignedUser = User::find($task->assigned_to);
            $createdByUser = User::find($task->created_by);
            
            // يمكنه رؤية مهام أعضاء فريقه
            if ($assignedUser && $assignedUser->manager_id == $user->id) {
                return true;
            }
            if ($createdByUser && $createdByUser->manager_id == $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * التحقق من صلاحية تعديل المهمة
     */
    private function canEditTask($user, $task): bool
    {
        // منشئ المهمة يمكنه تعديلها
        if ($task->created_by == $user->id) {
            return true;
        }

        // المكلف بالمهمة يمكنه تعديل الحالة فقط
        if ($task->assigned_to == $user->id) {
            return true;
        }

        // CEO و Head Manager يمكنهم تعديل أي مهمة
        if ($this->isCeo($user) || $this->isHeadManager($user)) {
            return true;
        }

        // Manager يمكنه تعديل مهام قسمه
        if ($this->isManager($user) && $task->department_id == $user->department_id) {
            return true;
        }

        // Team Leader يمكنه تعديل مهام فريقه
        if ($this->isTeamLeader($user)) {
            $assignedUser = User::find($task->assigned_to);
            $createdByUser = User::find($task->created_by);
            
            // يمكنه تعديل مهام أعضاء فريقه
            if ($assignedUser && $assignedUser->manager_id == $user->id) {
                return true;
            }
            if ($createdByUser && $createdByUser->manager_id == $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * حساب تاريخ التكرار التالي
     */
    private function calculateNextRepeatDate($dueDate, $repeatType)
    {
        $date = \Carbon\Carbon::parse($dueDate);

        switch ($repeatType) {
            case 'daily':
                return $date->addDay();
            case 'quarterly':
                return $date->addMonths(3);
            case 'yearly':
                return $date->addYear();
            default:
                return null;
        }
    }

    /**
     * حساب due_datetime من المدخلات
     */
    private function calculateDueDateTimeFromInput(Request $request)
    {
        // إذا تم تحديد عدد الساعات
        if ($request->filled('sla_hours')) {
            $hours = (int)$request->sla_hours;
            // التأكد من أن القيمة صحيحة
            if ($hours <= 0) {
                $hours = 24; // قيمة افتراضية
            }
            return now()->addHours($hours);
        }

        // إذا تم تحديد التاريخ والوقت
        if ($request->filled('due_date')) {
            $date = $request->due_date;
            $time = $request->filled('due_time') ? $request->due_time : '23:59';
            
            return \Carbon\Carbon::parse($date . ' ' . $time);
        }

        return null;
    }
}