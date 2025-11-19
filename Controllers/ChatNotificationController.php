<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * الحصول على عدد الرسائل غير المقروءة
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        
        $unreadCount = ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('user_id', '!=', $user->id)
        ->where('status', '!=', 'read')
        ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * الحصول على الإشعارات الحديثة
     */
    public function getRecentNotifications()
    {
        $user = Auth::user();
        
        $notifications = ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('user_id', '!=', $user->id)
        ->where('status', '!=', 'read')
        ->with(['user', 'chatRoom'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->map(function($message) use ($user) {
            return [
                'id' => $message->id,
                'content' => $message->message,
                'sender_name' => $message->user->name,
                'chat_room_name' => $message->chatRoom->getDisplayName($user->id),
                'created_at' => $message->created_at->diffForHumans(),
                'type' => $message->type
            ];
        });

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * تحديث حالة الإشعار كمقروء
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:chat_messages,id'
        ]);

        $user = Auth::user();
        $message = ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($request->message_id);

        if ($message->user_id !== $user->id) {
            $message->update(['status' => 'read', 'read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * تحديث جميع الإشعارات كمقروءة
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('user_id', '!=', $user->id)
        ->where('status', '!=', 'read')
        ->update([
            'status' => 'read',
            'read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * الحصول على إحصائيات الدردشة
     */
    public function getChatStats()
    {
        $user = Auth::user();
        
        $totalChats = ChatRoom::whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
        $unreadMessages = ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('user_id', '!=', $user->id)
        ->where('status', '!=', 'read')
        ->count();

        $totalMessages = ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        $onlineUsers = User::where('updated_at', '>', now()->subMinutes(5))->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_chats' => $totalChats,
                'unread_messages' => $unreadMessages,
                'total_messages' => $totalMessages,
                'online_users' => $onlineUsers
            ]
        ]);
    }
}
