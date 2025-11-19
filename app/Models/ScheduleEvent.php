<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class ScheduleEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'timezone',
        'location',
        'event_type',
        'status',
        'priority',
        'is_recurring',
        'recurring_pattern',
        'recurring_rules',
        'recurring_end_date',
        'parent_event_id',
        'meeting_room_id',
        'user_id',
        'color',
        'reminders',
        'external_calendar_id',
        'external_calendar_type',
        'last_synced_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'recurring_end_date' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_recurring' => 'boolean',
        'recurring_rules' => 'array',
        'reminders' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meetingRoom()
    {
        return $this->belongsTo(MeetingRoom::class);
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'event_user')
                    ->withPivot(['role', 'rsvp_status', 'responded_at', 'response_note'])
                    ->withTimestamps();
    }

    public function parentEvent()
    {
        return $this->belongsTo(ScheduleEvent::class, 'parent_event_id');
    }

    public function recurringEvents()
    {
        return $this->hasMany(ScheduleEvent::class, 'parent_event_id');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now())
                     ->whereIn('status', ['scheduled', 'confirmed']);
    }

    public function scopePast($query)
    {
        return $query->where('end_time', '<', now());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereHas('attendees', function ($q2) use ($userId) {
                  $q2->where('users.id', $userId);
              });
        });
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_time', [$startDate, $endDate])
              ->orWhereBetween('end_time', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('start_time', '<=', $startDate)
                     ->where('end_time', '>=', $endDate);
              });
        });
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    // Helper Methods
    public function isPast()
    {
        return $this->end_time->isPast();
    }

    public function isUpcoming()
    {
        return $this->start_time->isFuture();
    }

    public function duration()
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function isOwner($userId)
    {
        return $this->user_id == $userId;
    }

    public function isAttendee($userId)
    {
        return $this->attendees()->where('users.id', $userId)->exists();
    }

    public function canEdit($userId)
    {
        return $this->isOwner($userId) || 
               $this->attendees()->where('users.id', $userId)
                               ->wherePivot('role', 'organizer')
                               ->exists();
    }

    // Generate recurring events
    public function generateRecurringEvents($endDate = null)
    {
        if (!$this->is_recurring || !$this->recurring_pattern) {
            return [];
        }

        $endDate = $endDate ? Carbon::parse($endDate) : ($this->recurring_end_date ?? now()->addYear());
        $events = [];
        $currentDate = $this->start_time->copy();

        while ($currentDate->lte($endDate)) {
            if ($currentDate->eq($this->start_time)) {
                $currentDate = $this->getNextRecurrenceDate($currentDate);
                continue;
            }

            $event = $this->replicate();
            $duration = $this->start_time->diffInMinutes($this->end_time);
            $event->start_time = $currentDate;
            $event->end_time = $currentDate->copy()->addMinutes($duration);
            $event->parent_event_id = $this->id;
            $event->save();

            // Copy attendees
            foreach ($this->attendees as $attendee) {
                $event->attendees()->attach($attendee->id, [
                    'role' => $attendee->pivot->role,
                    'rsvp_status' => 'pending',
                ]);
            }

            $events[] = $event;
            $currentDate = $this->getNextRecurrenceDate($currentDate);
        }

        return $events;
    }

    protected function getNextRecurrenceDate($currentDate)
    {
        $rules = $this->recurring_rules ?? [];
        $interval = $rules['interval'] ?? 1;

        switch ($this->recurring_pattern) {
            case 'daily':
                return $currentDate->addDays($interval);
            case 'weekly':
                return $currentDate->addWeeks($interval);
            case 'monthly':
                return $currentDate->addMonths($interval);
            case 'yearly':
                return $currentDate->addYears($interval);
            default:
                return $currentDate->addDay();
        }
    }

    // Check for conflicts with other events
    public function hasConflict($excludeEventId = null)
    {
        $query = static::where(function ($q) {
            $q->where(function ($q2) {
                $q2->whereBetween('start_time', [$this->start_time, $this->end_time])
                   ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                   ->orWhere(function ($q3) {
                       $q3->where('start_time', '<=', $this->start_time)
                          ->where('end_time', '>=', $this->end_time);
                   });
            })
            ->whereIn('status', ['scheduled', 'confirmed']);
        });

        if ($excludeEventId) {
            $query->where('id', '!=', $excludeEventId);
        }

        // Check for room conflicts
        if ($this->meeting_room_id) {
            $query->where('meeting_room_id', $this->meeting_room_id);
        }

        return $query->exists();
    }
}
