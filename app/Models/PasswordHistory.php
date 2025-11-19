<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class PasswordHistory extends Model
{
    use HasFactory;
    
    protected $table = 'password_histories';

    protected $fillable = [
        'account_id',
        'old_password',
        'new_password',
        'changed_by',
        'change_reason',
        'change_reason_ar',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Get the account for this password history.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(PasswordAccount::class, 'account_id');
    }

    /**
     * Get the user who changed the password.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Encrypt old password when setting.
     */
    public function setOldPasswordAttribute($value)
    {
        $this->attributes['old_password'] = Crypt::encryptString($value);
    }

    /**
     * Encrypt new password when setting.
     */
    public function setNewPasswordAttribute($value)
    {
        $this->attributes['new_password'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt old password when getting.
     */
    public function getOldPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Return as-is if decryption fails
        }
    }

    /**
     * Decrypt new password when getting.
     */
    public function getNewPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Return as-is if decryption fails
        }
    }

    /**
     * Get the display reason based on current locale.
     */
    public function getDisplayReasonAttribute(): string
    {
        $reason = app()->getLocale() === 'ar' ? $this->change_reason_ar : $this->change_reason;
        return $reason ?? $this->change_reason ?? '';
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
    public function scopeChangedBy($query, $userId)
    {
        return $query->where('changed_by', $userId);
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('changed_at', [$startDate, $endDate]);
    }

    /**
     * Scope for recent changes.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }
}