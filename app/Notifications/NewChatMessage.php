<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;

class NewChatMessage extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;
    protected $chatRoom;
    protected $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(ChatMessage $message, ChatRoom $chatRoom, User $sender)
    {
        $this->message = $message;
        $this->chatRoom = $chatRoom;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        // Add email notification if user has it enabled
        if ($notifiable->notification_preferences['email_chat_messages'] ?? true) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $messageContent = $this->getMessagePreview();
        $chatUrl = route('chat.show', $this->chatRoom->id);
        
        return (new MailMessage)
            ->subject(__('chat.messages.new_message_from', ['name' => $this->sender->name]))
            ->greeting(__('chat.messages.new_message'))
            ->line(__('chat.messages.new_message_from', ['name' => $this->sender->name]))
            ->line($messageContent)
            ->action(__('chat.messages.view_chat'), $chatUrl)
            ->line(__('chat.messages.thank_you'));
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'new_chat_message',
            'chat_room_id' => $this->chatRoom->id,
            'message_id' => $this->message->id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'message_preview' => $this->getMessagePreview(),
            'message_type' => $this->message->message_type,
            'chat_room_name' => $this->chatRoom->getDisplayName($notifiable->id),
            'created_at' => $this->message->created_at,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Get message preview based on message type
     */
    private function getMessagePreview(): string
    {
        switch ($this->message->message_type) {
            case 'text':
                return Str::limit($this->message->content, 100);
            case 'image':
                return __('chat.messages.image_message');
            case 'file':
                return __('chat.messages.file_message') . ': ' . $this->message->attachment_name;
            case 'contact':
                return __('chat.messages.contact_message') . ': ' . ($this->message->metadata['contact_name'] ?? '');
            default:
                return $this->message->content;
        }
    }
}
