<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEmail extends Model
{
    protected $table = 'employee_emails';
    
    protected $fillable = [
        'employee_id',
        'email_address',
        'email_type',
        'is_primary',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع الموظف
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * الحصول على نوع الإيميل بالعربية
     */
    public function getEmailTypeArabicAttribute(): string
    {
        return match($this->email_type) {
            'work' => 'عمل',
            'personal' => 'شخصي',
            'other' => 'أخرى',
            default => 'غير محدد'
        };
    }

    /**
     * Scope للحصول على الإيميلات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', '=', true);
    }

    /**
     * Scope للحصول على الإيميل الأساسي
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
