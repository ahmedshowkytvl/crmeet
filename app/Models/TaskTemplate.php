<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskTemplate extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'estimated_time',
        'department',
        'description',
        'description_ar',
        'is_active',
    ];

    protected $casts = [
        'estimated_time' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع المهام التي تستخدم هذا القالب
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * الحصول على الاسم حسب اللغة الحالية
     */
    public function getDisplayNameAttribute(): string
    {
        $name = app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
        return $name ?? $this->name ?? '';
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
     * تحويل الوقت المقدر من الساعات إلى دقائق
     */
    public function getEstimatedTimeInMinutesAttribute(): float
    {
        return $this->estimated_time * 60;
    }

    /**
     * تحويل الوقت المقدر من الساعات إلى أيام عمل
     */
    public function getEstimatedTimeInWorkDaysAttribute(): float
    {
        return $this->estimated_time / 8; // 8 ساعات يوم عمل
    }

    /**
     * Scope للقوالب النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للقوالب حسب القسم
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope للبحث في الأسماء
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('name_ar', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('description_ar', 'like', "%{$search}%");
        });
    }

    /**
     * الحصول على قائمة الأقسام المتاحة
     */
    public static function getAvailableDepartments(): array
    {
        return [
            'Contracting' => 'Contracting (Egypt-KSA-Global)',
            'IT' => 'IT',
            'Internet dep' => 'Internet dep',
            'Accounting' => 'Accounting',
            'Callcenter' => 'Callcenter',
            'Marketing' => 'Marketing',
        ];
    }

    /**
     * الحصول على اسم القسم بالعربية
     */
    public function getDepartmentNameAttribute(): string
    {
        $departments = [
            'Contracting' => 'المقاولات',
            'IT' => 'تكنولوجيا المعلومات',
            'Internet dep' => 'قسم الإنترنت',
            'Accounting' => 'المحاسبة',
            'Callcenter' => 'مركز الاتصال',
            'Marketing' => 'التسويق',
        ];

        return $departments[$this->department] ?? $this->department ?? 'غير محدد';
    }

    /**
     * التحقق من إمكانية حذف القالب
     */
    public function canBeDeleted(): bool
    {
        return $this->tasks()->count() === 0;
    }

    /**
     * الحصول على عدد المهام التي تستخدم هذا القالب
     */
    public function getUsageCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    /**
     * الحصول على إجمالي الوقت المقدر لجميع المهام التي تستخدم هذا القالب
     */
    public function getTotalEstimatedTimeAttribute(): float
    {
        return $this->tasks()->sum('estimated_time') ?? 0;
    }

    /**
     * إنشاء مهمة جديدة من هذا القالب
     */
    public function createTask(array $taskData = []): Task
    {
        $defaultData = [
            'title' => $this->name,
            'title_ar' => $this->name_ar,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'estimated_time' => $this->estimated_time,
            'task_template_id' => $this->id,
        ];

        return Task::create(array_merge($defaultData, $taskData));
    }
}
