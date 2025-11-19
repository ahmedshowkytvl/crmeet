<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MeetingRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'capacity',
        'location',
        'location_ar',
        'amenities',
        'is_available',
        'availability_schedule',
        'image',
        'hourly_rate',
        'created_by',
    ];

    protected $casts = [
        'amenities' => 'array',
        'availability_schedule' => 'array',
        'is_available' => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function events()
    {
        return $this->hasMany(ScheduleEvent::class, 'meeting_room_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeUnavailable($query)
    {
        return $query->where('is_available', false);
    }

    // Check if room is available for a specific time slot
    public function isAvailableFor($startTime, $endTime, $excludeEventId = null)
    {
        $query = $this->events()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($q2) use ($startTime, $endTime) {
                    // Check for overlapping events
                    $q2->whereBetween('start_time', [$startTime, $endTime])
                       ->orWhereBetween('end_time', [$startTime, $endTime])
                       ->orWhere(function ($q3) use ($startTime, $endTime) {
                           $q3->where('start_time', '<=', $startTime)
                              ->where('end_time', '>=', $endTime);
                       });
                })
                ->whereIn('status', ['scheduled', 'confirmed']);
            });

        if ($excludeEventId) {
            $query->where('id', '!=', $excludeEventId);
        }

        return $query->count() === 0;
    }

    // Get available time slots for a specific date
    public function getAvailableTimeSlots($date, $duration = 60)
    {
        // This is a simplified version - you can enhance it based on availability_schedule
        $startOfDay = $date->copy()->setTime(9, 0);
        $endOfDay = $date->copy()->setTime(17, 0);
        
        $bookedSlots = $this->events()
            ->whereDate('start_time', $date)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->get()
            ->map(function ($event) {
                return [
                    'start' => $event->start_time,
                    'end' => $event->end_time,
                ];
            });

        // Generate available slots (simplified - can be enhanced)
        $availableSlots = [];
        $current = $startOfDay->copy();
        
        while ($current->copy()->addMinutes($duration)->lte($endOfDay)) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($duration);
            
            $isBooked = $bookedSlots->contains(function ($slot) use ($slotStart, $slotEnd) {
                return ($slotStart->between($slot['start'], $slot['end']) ||
                       $slotEnd->between($slot['start'], $slot['end']) ||
                       ($slotStart->lte($slot['start']) && $slotEnd->gte($slot['end'])));
            });
            
            if (!$isBooked) {
                $availableSlots[] = [
                    'start' => $slotStart->toDateTimeString(),
                    'end' => $slotEnd->toDateTimeString(),
                ];
            }
            
            $current->addMinutes(30); // Check every 30 minutes
        }
        
        return $availableSlots;
    }
}
