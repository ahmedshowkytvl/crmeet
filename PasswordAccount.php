<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class PasswordAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'username',
        'email',
        'password',
        'url',
        'notes',
        'notes_ar',
        'requires_2fa',
        'expires_at',
        'is_shared',
        'is_active',
        'category',
        'category_ar',
        'category_id',
        'icon',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'requires_2fa' => 'boolean',
        'is_shared' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'date',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the user who created this account.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the category for this account.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PasswordCategory::class, 'category_id');
    }

    /**
     * Get all assignments for this account.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(PasswordAssignment::class, 'account_id');
    }

    /**
     * Get all users assigned to this account.
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'password_assignments', 'account_id', 'user_id')
                    ->withPivot(['access_level', 'can_view_password', 'can_edit_password', 'can_delete_account', 'assigned_at', 'assigned_by'])
                    ->withTimestamps();
    }

    /**
     * Get audit logs for this account.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(PasswordAuditLog::class, 'account_id');
    }

    /**
     * Get password history for this account.
     */
    public function passwordHistory(): HasMany
    {
        return $this->hasMany(PasswordHistory::class, 'account_id');
    }

    /**
     * Encrypt password when setting.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt password when getting.
     */
    public function getPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Return as-is if decryption fails
        }
    }

    /**
     * Get the display name based on current locale.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
        return $name ?? $this->name ?? '';
    }

    /**
     * Get the display notes based on current locale.
     */
    public function getDisplayNotesAttribute(): string
    {
        $notes = app()->getLocale() === 'ar' ? $this->notes_ar : $this->notes;
        return $notes ?? $this->notes ?? '';
    }

    /**
     * Get the display category based on current locale.
     */
    public function getDisplayCategoryAttribute(): string
    {
        // If we have a category relationship, use it
        if ($this->category) {
            return $this->category->display_name ?? '';
        }
        
        // Fallback to old category fields
        $category = app()->getLocale() === 'ar' ? $this->category_ar : $this->category;
        return $category ?? $this->category ?? '';
    }

    /**
     * Check if password is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if password is expiring soon (within 7 days).
     */
    public function isExpiringSoon(): bool
    {
        return $this->expires_at && $this->expires_at->isBefore(Carbon::now()->addDays(7));
    }

    /**
     * Get days until expiration.
     */
    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        
        return Carbon::now()->diffInDays($this->expires_at, false);
    }

    /**
     * Scope for active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for expired accounts.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }

    /**
     * Scope for expiring soon accounts.
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('expires_at', '>=', Carbon::now())
                    ->where('expires_at', '<=', Carbon::now()->addDays(7));
    }

    /**
     * Scope for shared accounts.
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    /**
     * Scope for accounts by category.
     */
    public function scopeByCategory($query, $category)
    {
        // If category is numeric, search by category_id
        if (is_numeric($category)) {
            return $query->where('category_id', $category);
        }
        
        // Otherwise search by category name
        return $query->where('category', $category);
    }

    /**
     * Scope for accounts by category ID.
     */
    public function scopeByCategoryId($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for accounts assigned to user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->whereHas('assignments', function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->whereNull('revoked_at');
        });
    }

    /**
     * Scope for accounts created by user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

}