<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action_type',
        'module',
        'record_id',
        'record_name',
        'details',
        'ip_address',
        'device_info',
        'user_agent',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by action type.
     */
    public function scopeActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope for filtering by module.
     */
    public function scopeModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by user.
     */
    public function scopeUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for searching in details.
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('user_name', 'like', "%{$searchTerm}%")
              ->orWhere('module', 'like', "%{$searchTerm}%")
              ->orWhere('action_type', 'like', "%{$searchTerm}%")
              ->orWhere('record_name', 'like', "%{$searchTerm}%")
              ->orWhere('ip_address', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Get formatted action type.
     */
    public function getFormattedActionTypeAttribute()
    {
        $actionTypes = [
            'create' => 'إنشاء',
            'update' => 'تحديث',
            'delete' => 'حذف',
            'login' => 'تسجيل دخول',
            'logout' => 'تسجيل خروج',
            'view' => 'عرض',
            'export' => 'تصدير',
            'import' => 'استيراد',
            'archive' => 'أرشفة',
            'restore' => 'استعادة',
            'approve' => 'موافقة',
            'reject' => 'رفض',
            'assign' => 'تعيين',
            'unassign' => 'إلغاء تعيين',
        ];

        return $actionTypes[$this->action_type] ?? $this->action_type;
    }

    /**
     * Get formatted module name.
     */
    public function getFormattedModuleAttribute()
    {
        $modules = [
            'employees' => 'الموظفين',
            'events' => 'الأحداث',
            'announcements' => 'الإعلانات',
            'tasks' => 'المهام',
            'departments' => 'الأقسام',
            'suppliers' => 'الموردين',
            'contacts' => 'جهات الاتصال',
            'assets' => 'الأصول',
            'password_accounts' => 'حسابات كلمات المرور',
            'chat' => 'الدردشة',
            'notifications' => 'الإشعارات',
            'zoho' => 'زوهو',
            'system' => 'النظام',
        ];

        return $modules[$this->module] ?? $this->module;
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'success' ? 'badge-success' : 'badge-danger';
    }

    /**
     * Get status text.
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 'success' ? 'نجح' : 'فشل';
    }
}

