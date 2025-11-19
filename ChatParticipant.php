<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatParticipant extends Model
{
    protected $fillable = [
        'chat_room_id',
        'user_id',
        'role',
        'joined_at',
        'last_read_at',
        'is_muted',
        'is_archived',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_read_at' => 'datetime',
        'is_muted' => 'boolean',
        'is_archived' => 'boolean',
    ];

    // العلاقات
    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeMuted($query)
    {
        return $query->where('is_muted', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeMembers($query)
    {
        return $query->where('role', 'member');
    }

    // Methods
    public function markAsRead()
    {
        $this->update(['last_read_at' => now()]);
    }

    public function mute()
    {
        $this->update(['is_muted' => true]);
    }

    public function unmute()
    {
        $this->update(['is_muted' => false]);
    }

    public function archive()
    {
        $this->update(['is_archived' => true]);
    }

    public function unarchive()
    {
        $this->update(['is_archived' => false]);
    }

    public function promoteToAdmin()
    {
        $this->update(['role' => 'admin']);
    }

    public function demoteToMember()
    {
        $this->update(['role' => 'member']);
    }

    public function getUnreadCountAttribute()
    {
        if (!$this->last_read_at) {
            return $this->chatRoom->messages()->count();
        }

        return $this->chatRoom->messages()
            ->where('created_at', '>', $this->last_read_at)
            ->where('user_id', '!=', $this->user_id)
            ->count();
    }
}
