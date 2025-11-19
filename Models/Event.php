<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'date',
        'location',
        'organizer',
        'image_url',
        'status',
        'is_featured',
        'created_by'
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_featured' => 'boolean',
    ];

    // Scope for upcoming events
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now())
                    ->where('status', 'upcoming')
                    ->orderBy('date', 'asc');
    }

    // Scope for recent events
    public function scopeRecent($query)
    {
        return $query->where('date', '<', now())
                    ->where('status', 'completed')
                    ->orderBy('date', 'desc');
    }

    // Scope for featured events
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Get formatted date
    public function getFormattedDateAttribute()
    {
        return $this->date->format('M d, Y');
    }

    // Get formatted time
    public function getFormattedTimeAttribute()
    {
        return $this->date->format('h:i A');
    }

    // Check if event is today
    public function getIsTodayAttribute()
    {
        return $this->date->isToday();
    }

    // Check if event is upcoming
    public function getIsUpcomingAttribute()
    {
        return $this->date->isFuture();
    }

    // Relationship with User (Creator)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with Announcements
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'attached_event_id');
    }

    // Get short description preview
    public function getDescriptionPreviewAttribute()
    {
        return \Str::limit($this->description, 150);
    }
}
