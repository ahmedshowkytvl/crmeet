<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'message',
        'target_type',
        'target_ids',
        'attached_event_id',
        'created_by'
    ];

    protected $casts = [
        'target_ids' => 'array',
    ];

    // Relationship with User (Creator)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with Event
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'attached_event_id');
    }

    // Scope for announcements visible to user
    public function scopeVisibleToUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('target_type', 'all')
              ->orWhereJsonContains('target_ids', $userId);
        });
    }

    // Scope for recent announcements
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Get target audience names
    public function getTargetAudienceAttribute()
    {
        if ($this->target_type === 'all') {
            return 'All Employees';
        }

        if ($this->target_ids) {
            $users = User::whereIn('id', $this->target_ids)->pluck('name')->toArray();
            return implode(', ', $users);
        }

        return 'No specific targets';
    }

    // Get formatted created date
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M d, Y \a\t h:i A');
    }

    // Get short message preview
    public function getMessagePreviewAttribute()
    {
        return \Str::limit($this->message, 100);
    }
}
