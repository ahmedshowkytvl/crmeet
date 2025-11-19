<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZohoTicketCache extends Model
{
    use HasFactory;

    protected $table = 'zoho_tickets_cache';

    protected $fillable = [
        'zoho_ticket_id',
        'ticket_number',
        'user_id',
        'closed_by_name',
        'subject',
        'status',
        'department_id',
        'created_at_zoho',
        'closed_at_zoho',
        'response_time_minutes',
        'thread_count',
        'raw_data'
    ];

    protected $casts = [
        'created_at_zoho' => 'datetime',
        'closed_at_zoho' => 'datetime',
        'raw_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Scopes
    public function scopeClosed($query)
    {
        return $query->where('status', 'Closed');
    }

    public function scopeExcludeAutoClose($query)
    {
        return $query->where('closed_by_name', '!=', 'Auto Close')
                     ->whereNotNull('closed_by_name');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at_zoho', [$startDate, $endDate]);
    }
}

