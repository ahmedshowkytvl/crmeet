<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SnipeItSyncLog extends Model
{
    use HasFactory;

    // Explicit table name to match existing DB table
    protected $table = 'snipeit_sync_logs';

    protected $fillable = [
        'type',
        'sync_type',
        'status',
        'started_at',
        'completed_at',
        'user_id',
        'synced_count',
        'created_count',
        'updated_count',
        'errors',
        'duration'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'errors' => 'array',
        'duration' => 'integer'
    ];

    /**
     * Get the user who initiated the sync
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sync type display name
     */
    public function getSyncTypeDisplayAttribute(): string
    {
        return match($this->sync_type) {
            'full' => 'مزامنة كاملة',
            'incremental' => 'مزامنة تدريجية',
            default => $this->sync_type
        };
    }

    /**
     * Get the status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'running' => 'جاري',
            'completed' => 'مكتمل',
            'failed' => 'فشل',
            default => $this->status
        };
    }

    /**
     * Get the status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'running' => 'badge-warning',
            'completed' => 'badge-success',
            'failed' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Get the type display name
     */
    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'assets' => 'الأصول',
            'users' => 'المستخدمين',
            'categories' => 'الفئات',
            'locations' => 'المواقع',
            'models' => 'النماذج',
            'suppliers' => 'الموردين',
            default => $this->type
        };
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return 'غير محدد';
        }

        if ($this->duration < 60) {
            return $this->duration . ' ثانية';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        if ($minutes < 60) {
            return $minutes . ' دقيقة ' . $seconds . ' ثانية';
        }

        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;

        return $hours . ' ساعة ' . $minutes . ' دقيقة';
    }

    /**
     * Check if sync is running
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if sync is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if sync failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if (!$this->synced_count) {
            return 0;
        }

        $errorCount = is_array($this->errors) ? count($this->errors) : 0;
        $successCount = $this->synced_count - $errorCount;

        return round(($successCount / $this->synced_count) * 100, 2);
    }

    /**
     * Scope for completed syncs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for running syncs
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for recent syncs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('started_at', '>=', Carbon::now()->subDays($days));
    }
}
