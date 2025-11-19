<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserZohoStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_type',
        'period_date',
        'tickets_closed_count',
        'avg_response_time_minutes',
        'tickets_per_hour',
        'total_threads_count',
        'performance_score',
        'last_synced_at'
    ];

    protected $casts = [
        'period_date' => 'date',
        'last_synced_at' => 'datetime',
        'avg_response_time_minutes' => 'decimal:2',
        'tickets_per_hour' => 'decimal:2',
        'performance_score' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeDaily($query)
    {
        return $query->where('period_type', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('period_type', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('period_type', 'monthly');
    }

    public function scopeForPeriod($query, $periodType, $periodDate)
    {
        return $query->where('period_type', $periodType)
                     ->where('period_date', $periodDate);
    }
}

