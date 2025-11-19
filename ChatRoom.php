<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChatRoom extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'created_by',
        'avatar',
        'is_active',
        'last_message_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    // العلاقات
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'desc');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ChatParticipant::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants')
                    ->withPivot(['role', 'joined_at', 'last_read_at', 'is_muted', 'is_archived'])
                    ->withTimestamps();
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class)->latest();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }

    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    // Methods
    public function getOtherParticipant($userId)
    {
        return $this->users()->where('user_id', '!=', $userId)->first();
    }

    public function getDisplayName($userId = null)
    {
        if ($this->type === 'private' && $userId) {
            $otherUser = $this->getOtherParticipant($userId);
            return $otherUser ? $otherUser->name : $this->name;
        }
        
        return $this->name ?: 'دردشة جماعية';
    }

    public function getDisplayAvatar($userId = null)
    {
        if ($this->type === 'private' && $userId) {
            $otherUser = $this->getOtherParticipant($userId);
            return $otherUser ? $otherUser->profile_picture : $this->avatar;
        }
        
        return $this->avatar;
    }

    public function updateLastMessage()
    {
        $lastMessage = $this->messages()->first();
        if ($lastMessage) {
            $this->update(['last_message_at' => $lastMessage->created_at]);
        }
    }
}
