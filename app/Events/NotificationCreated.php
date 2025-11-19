<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    /**
     * Create a new event instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->notification->user_id),
        ];
    }

    /**
     * اسم الحدث المبثوث
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * البيانات المبثوثة
     */
    public function broadcastWith(): array
    {
        return [
            'notification' => [
                'id' => $this->notification->id,
                'type' => $this->notification->type,
                'title' => $this->notification->title,
                'body' => $this->notification->body,
                'actor_id' => $this->notification->actor_id,
                'actor_name' => $this->notification->actor_name,
                'actor_avatar' => $this->notification->actor_avatar,
                'resource_type' => $this->notification->resource_type,
                'resource_id' => $this->notification->resource_id,
                'link' => $this->notification->link,
                'metadata' => $this->notification->metadata,
                'is_read' => $this->notification->is_read,
                'created_at' => $this->notification->created_at->toISOString(),
                'time_ago' => $this->notification->time_ago,
            ]
        ];
    }
}

