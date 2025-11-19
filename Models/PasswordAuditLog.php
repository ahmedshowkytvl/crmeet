<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'action',
        'description',
        'description_ar',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'performed_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the account for this audit log.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(PasswordAccount::class, 'account_id');
    }

    /**
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the display description based on current locale.
     */
    public function getDisplayDescriptionAttribute(): string
    {
        $description = app()->getLocale() === 'ar' ? $this->description_ar : $this->description;
        return $description ?? $this->description ?? '';
    }

    /**
     * Scope for specific action.
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific account.
     */
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope for specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    /**
     * Scope for recent logs.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Get action labels in both languages.
     */
    public static function getActionLabels(): array
    {
        return [
            'viewed' => ['en' => 'Viewed', 'ar' => 'تم العرض'],
            'created' => ['en' => 'Created', 'ar' => 'تم الإنشاء'],
            'updated' => ['en' => 'Updated', 'ar' => 'تم التحديث'],
            'deleted' => ['en' => 'Deleted', 'ar' => 'تم الحذف'],
            'assigned' => ['en' => 'Assigned', 'ar' => 'تم التخصيص'],
            'unassigned' => ['en' => 'Unassigned', 'ar' => 'تم إلغاء التخصيص'],
            'password_changed' => ['en' => 'Password Changed', 'ar' => 'تم تغيير كلمة المرور'],
            'expired' => ['en' => 'Expired', 'ar' => 'انتهت الصلاحية'],
            'expiring_soon' => ['en' => 'Expiring Soon', 'ar' => 'تنتهي قريباً'],
        ];
    }

    /**
     * Get localized action label.
     */
    public function getActionLabelAttribute(): string
    {
        $labels = self::getActionLabels();
        $locale = app()->getLocale();
        return $labels[$this->action][$locale] ?? $this->action;
    }
}