<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatRoomId;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ChatMessage $message
    ) {
        $this->chatRoomId = $message->chat_room_id;
        // Load user relationship
        $this->message->load('user');
    }

    /**
     * Get the channels the event should broadcast on.
     * استخدام PresenceChannel لتمكين toOthers()
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.' . $this->chatRoomId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $user = $this->message->user;
        
        return [
            'id' => $this->message->id,
            'chat_room_id' => $this->message->chat_room_id,
            'chat_id' => $this->message->chat_room_id,
            'user_id' => $this->message->user_id,
            'sender_id' => $this->message->user_id,
            'content' => $this->message->message,
            'message' => $this->message->message,
            'type' => $this->message->type,
            'created_at' => $this->message->created_at->toISOString(),
            'updated_at' => $this->message->updated_at->toISOString(),
            // is_own سيتم حسابه في Frontend بناءً على user_id الحالي
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile_picture' => $user->profile_picture,
            ],
            'sender' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile_picture' => $user->profile_picture,
            ],
        ];
    }
}

