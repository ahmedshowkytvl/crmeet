<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'access_level',
        'can_view_password',
        'can_edit_password',
        'can_delete_account',
        'assigned_at',
        'assigned_by',
        'revoked_at',
        'revoked_by',
        'revoke_reason',
    ];

    protected $casts = [
        'can_view_password' => 'boolean',
        'can_edit_password' => 'boolean',
        'can_delete_account' => 'boolean',
        'assigned_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Get the account for this assignment.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(PasswordAccount::class, 'account_id');
    }

    /**
     * Get the user for this assignment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who assigned this.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user who revoked this.
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Check if assignment is active (not revoked).
     */
    public function isActive(): bool
    {
        return is_null($this->revoked_at);
    }

    /**
     * Check if user can manage the account.
     */
    public function canManage(): bool
    {
        return $this->access_level === 'manage';
    }

    /**
     * Check if user has read-only access.
     */
    public function isReadOnly(): bool
    {
        return $this->access_level === 'read_only';
    }

    /**
     * Scope for active assignments.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Scope for revoked assignments.
     */
    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    /**
     * Scope for management level assignments.
     */
    public function scopeManagement($query)
    {
        return $query->where('access_level', 'manage');
    }

    /**
     * Scope for read-only assignments.
     */
    public function scopeReadOnly($query)
    {
        return $query->where('access_level', 'read_only');
    }

    /**
     * Scope for assignments by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for assignments by account.
     */
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }
}