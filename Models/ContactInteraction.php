<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'user_id',
        'interaction_type', // call, email, meeting, visit, other
        'subject',
        'description',
        'date',
        'duration', // بالدقائق
        'outcome', // successful, pending, failed
        'notes',
        'follow_up_date',
        'priority', // low, medium, high
    ];

    protected $casts = [
        'date' => 'datetime',
        'follow_up_date' => 'date',
    ];

    // العلاقات
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('interaction_type', $type);
    }

    public function scopeByOutcome($query, $outcome)
    {
        return $query->where('outcome', $outcome);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('follow_up_date', '>=', now());
    }
}
