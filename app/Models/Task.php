<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'title_ar',
        'description',
        'description_ar',
        'assigned_to',
        'created_by',
        'department_id',
        'priority',
        'status',
        'creator_can_update_status',
        'category',
        'repeat_type',
        'due_date',
        'due_time',
        'due_datetime',
        'sla_hours',
        'start_datetime',
        'end_datetime',
        'actual_start_datetime',
        'actual_end_datetime',
        'last_repeated_at',
        'next_repeat_at',
        'is_repeat_active',
        'estimated_time',
        'task_template_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'due_datetime' => 'datetime',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'actual_start_datetime' => 'datetime',
        'actual_end_datetime' => 'datetime',
        'last_repeated_at' => 'datetime',
        'next_repeat_at' => 'datetime',
        'creator_can_update_status' => 'boolean',
        'is_repeat_active' => 'boolean',
        'sla_hours' => 'integer',
        'estimated_time' => 'float',
    ];

    /**
     * العلاقة مع المستخدم المكلف بالمهمة
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * العلاقة مع منشئ المهمة
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع القسم
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * العلاقة مع التعليقات
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * العلاقة مع قالب المهمة
     */
    public function taskTemplate(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class);
    }

    /**
     * الحصول على العنوان حسب اللغة الحالية
     */
    public function getDisplayTitleAttribute(): string
    {
        $title = app()->getLocale() === 'ar' ? $this->title_ar : $this->title;
        return $title ?? $this->title ?? '';
    }

    /**
     * الحصول على الوصف حسب اللغة الحالية
     */
    public function getDisplayDescriptionAttribute(): string
    {
        $description = app()->getLocale() === 'ar' ? $this->description_ar : $this->description;
        return $description ?? $this->description ?? '';
    }

    /**
     * التحقق من صلاحية المستخدم لتحديث حالة المهمة
     */
    public function canUserUpdateStatus($userId): bool
    {
        $user = User::find($userId);
        if (!$user || !$user->role) {
            return false;
        }

        // إذا كان المستخدم هو المكلف بالمهمة
        if ($this->assigned_to == $userId) {
            return true;
        }

        // إذا كان المستخدم هو منشئ المهمة ويمكنه تحديث الحالة
        if ($this->created_by == $userId && $this->creator_can_update_status) {
            return true;
        }

        // CEO و Head Manager يمكنهم تحديث حالة أي مهمة
        if (in_array($user->role->slug, ['ceo', 'head_manager'])) {
            return true;
        }

        // Manager يمكنه تحديث حالة مهام قسمه
        if ($user->role->slug === 'manager' && $this->department_id == $user->department_id) {
            return true;
        }

        // Team Leader يمكنه تحديث حالة مهام فريقه
        if ($user->role->slug === 'team_leader') {
            $assignedUser = User::find($this->assigned_to);
            $createdByUser = User::find($this->created_by);
            
            if (($assignedUser && $assignedUser->manager_id == $userId) || 
                ($createdByUser && $createdByUser->manager_id == $userId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * التحقق من صلاحية المستخدم لتحديث الأولوية
     */
    public function canUserUpdatePriority($userId): bool
    {
        $user = User::find($userId);
        if (!$user || !$user->role) {
            return false;
        }

        // منشئ المهمة يمكنه تحديث الأولوية
        if ($this->created_by == $userId) {
            return true;
        }

        // CEO و Head Manager يمكنهم تحديث أولوية أي مهمة
        if (in_array($user->role->slug, ['ceo', 'head_manager'])) {
            return true;
        }

        // Manager يمكنه تحديث أولوية مهام قسمه
        if ($user->role->slug === 'manager' && $this->department_id == $user->department_id) {
            return true;
        }

        // Team Leader يمكنه تحديث أولوية مهام فريقه
        if ($user->role->slug === 'team_leader') {
            $assignedUser = User::find($this->assigned_to);
            $createdByUser = User::find($this->created_by);
            
            if (($assignedUser && $assignedUser->manager_id == $userId) || 
                ($createdByUser && $createdByUser->manager_id == $userId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * حساب نسبة التقدم في المهمة
     */
    public function getProgressPercentage(): float
    {
        if (!$this->start_datetime || !$this->end_datetime) {
            return 0.0;
        }

        $now = now();
        $startTime = $this->start_datetime;
        $endTime = $this->end_datetime;
        
        // إذا لم تبدأ المهمة بعد
        if ($now->lt($startTime)) {
            return 0.0;
        }
        
        // إذا انتهت المهمة
        if ($now->gte($endTime)) {
            return 100.0;
        }
        
        // حساب النسبة المئوية
        $totalDuration = $endTime->diffInMinutes($startTime);
        $elapsedTime = $now->diffInMinutes($startTime);
        
        return round(($elapsedTime / $totalDuration) * 100, 2);
    }

    /**
     * التحقق من تجاوز 70% من وقت المهمة
     */
    public function isOverdueWarning(): bool
    {
        return $this->getProgressPercentage() >= 70.0 && $this->status !== 'completed';
    }

    /**
     * التحقق من تجاوز وقت المهمة بالكامل
     */
    public function isOverdue(): bool
    {
        return $this->getProgressPercentage() >= 100.0 && $this->status !== 'completed';
    }

    /**
     * Scope للمهام المكررة النشطة
     */
    public function scopeActiveRepeating($query)
    {
        return $query->where('repeat_type', '!=', 'one_time')
                     ->where('is_repeat_active', true);
    }

    /**
     * Scope للمهام المستحقة للتكرار
     */
    public function scopeDueForRepeat($query)
    {
        return $query->activeRepeating()
                     ->where('next_repeat_at', '<=', now());
    }

    /**
     * حساب تاريخ التكرار التالي
     */
    public function calculateNextRepeatDate()
    {
        if ($this->repeat_type === 'one_time') {
            return null;
        }

        $baseDate = $this->next_repeat_at ?? $this->due_date ?? now();

        switch ($this->repeat_type) {
            case 'daily':
                return $baseDate->addDay();
            case 'quarterly':
                return $baseDate->addMonths(3);
            case 'yearly':
                return $baseDate->addYear();
            default:
                return null;
        }
    }

    /**
     * حساب due_datetime من due_date و due_time
     */
    public function calculateDueDateTime()
    {
        if (!$this->due_date) {
            return null;
        }

        if ($this->due_time) {
            return \Carbon\Carbon::parse($this->due_date->format('Y-m-d') . ' ' . $this->due_time);
        }

        return $this->due_date;
    }

    /**
     * الحصول على الوقت المتبقي بالساعات
     */
    public function getRemainingHoursAttribute()
    {
        if (!$this->due_datetime) {
            return null;
        }

        $now = now();
        if ($this->due_datetime < $now) {
            return 0; // متأخرة
        }

        return $now->diffInHours($this->due_datetime);
    }

    /**
     * التحقق إذا كانت المهمة متأخرة عن SLA
     */
    public function isOverdueSla(): bool
    {
        if (!$this->due_datetime || $this->status === 'completed') {
            return false;
        }

        return $this->due_datetime < now();
    }

    /**
     * الحصول على نسبة الوقت المستخدم من SLA
     */
    public function getSlaUsagePercentage(): ?float
    {
        if (!$this->sla_hours || !$this->created_at || !$this->due_datetime) {
            return null;
        }

        $totalHours = $this->sla_hours;
        $elapsedHours = $this->created_at->diffInHours(now());

        if ($totalHours <= 0) {
            return 100;
        }

        return min(100, ($elapsedHours / $totalHours) * 100);
    }

    /**
     * حساب مدة المهمة المخططة بالساعات
     */
    public function getPlannedDurationHours(): ?float
    {
        if (!$this->start_datetime || !$this->end_datetime) {
            return null;
        }

        return $this->start_datetime->diffInHours($this->end_datetime);
    }

    /**
     * حساب المدة الفعلية للمهمة بالساعات
     */
    public function getActualDurationHours(): ?float
    {
        if (!$this->actual_start_datetime || !$this->actual_end_datetime) {
            return null;
        }

        return $this->actual_start_datetime->diffInHours($this->actual_end_datetime);
    }

    /**
     * التحقق إذا كانت المهمة جارية
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress' && $this->actual_start_datetime && !$this->actual_end_datetime;
    }

    /**
     * التحقق إذا كانت المهمة متأخرة عن الموعد المخطط
     */
    public function isOverduePlanned(): bool
    {
        return $this->end_datetime && $this->end_datetime < now() && $this->status !== 'completed';
    }

    /**
     * بدء تنفيذ المهمة
     */
    public function startTask(): bool
    {
        if ($this->status === 'pending' || $this->status === 'on_hold') {
            $this->update([
                'status' => 'in_progress',
                'actual_start_datetime' => now()
            ]);
            return true;
        }
        return false;
    }

    /**
     * إنهاء تنفيذ المهمة
     */
    public function completeTask(): bool
    {
        if ($this->status === 'in_progress') {
            $this->update([
                'status' => 'completed',
                'actual_end_datetime' => now()
            ]);
            return true;
        }
        return false;
    }

    /**
     * الحصول على الوقت المقدر بالدقائق
     */
    public function getEstimatedTimeInMinutesAttribute(): ?float
    {
        return $this->estimated_time ? $this->estimated_time * 60 : null;
    }

    /**
     * الحصول على الوقت المقدر بأيام العمل
     */
    public function getEstimatedTimeInWorkDaysAttribute(): ?float
    {
        return $this->estimated_time ? $this->estimated_time / 8 : null;
    }

    /**
     * التحقق إذا كانت المهمة مكتملة
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * حساب التقدم بناءً على الوقت المقدر
     * progress = (SUM(estimated_time of completed tasks) / 8) * 100
     */
    public static function calculateProgressForUser($userId, $date = null): float
    {
        $query = static::where('assigned_to', $userId)
                      ->where('status', 'completed');

        if ($date) {
            $query->whereDate('actual_end_datetime', $date);
        }

        $completedEstimatedTime = $query->sum('estimated_time') ?? 0;
        
        return ($completedEstimatedTime / 8) * 100; // 8 ساعات يوم عمل
    }

    /**
     * الحصول على إجمالي الوقت المقدر للمهام المكتملة لمستخدم في تاريخ معين
     */
    public static function getCompletedEstimatedTimeForUser($userId, $date = null): float
    {
        $query = static::where('assigned_to', $userId)
                      ->where('status', 'completed');

        if ($date) {
            $query->whereDate('actual_end_datetime', $date);
        }

        return $query->sum('estimated_time') ?? 0;
    }

    /**
     * الحصول على التقدم كنسبة مئوية
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->status === 'completed') {
            return 100;
        }

        if ($this->status === 'in_progress') {
            return 50; // نصف مكتملة
        }

        return 0;
    }
}
