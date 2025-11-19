<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shoutout extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'recipient_name',
        'type',
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // Relationship with User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for public shoutouts
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    // Scope by type
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope for recent shoutouts
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Get formatted created date
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Get type icon
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'birthday' => '🎂',
            'achievement' => '🏆',
            'thanks' => '🙏',
            'general' => '💬',
            default => '💬'
        };
    }
}
