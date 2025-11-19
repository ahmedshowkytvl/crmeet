<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewChatMessage;

class ChatMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $request->headers->set('Accept', 'application/json');
            return $next($request);
        });
    }

    /**
     * إرسال رسالة نصية
     */
    public function sendText(Request $request)
    {
        $request->validate([
            'chat_room_id' => 'required|exists:chat_rooms,id',
            'content' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        $chatRoom = ChatRoom::whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($request->chat_room_id);

        DB::beginTransaction();
        try {
            $message = ChatMessage::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $user->id,
                'message' => $request->content,
                'type' => 'text',
            ]);

            // تحديث آخر رسالة في الدردشة
            $chatRoom->updateLastMessage();

            // إرسال إشعارات للمشاركين الآخرين (مؤقتاً معطل)
            // $otherParticipants = $chatRoom->participants()
            //     ->where('user_id', '!=', $user->id)
            //     ->with('user')
            //     ->get();

            // foreach ($otherParticipants as $participant) {
            //     $participant->user->notify(new NewChatMessage($message, $chatRoom, $user));
            // }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'chat_room_id' => $message->chat_room_id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'type' => $message->type,
                    'created_at' => $message->created_at->toIso8601String(),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'name_ar' => $user->name_ar,
                        'profile_picture' => $user->profile_picture,
                    ]
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            // Log the error for debugging
            \Log::error('Chat message sending error: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'chat_room_id' => $request->chat_room_id ?? null,
                'content_length' => strlen($request->content ?? ''),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الرسالة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * إرسال جهة اتصال
     */
    public function sendContact(Request $request)
    {
        $request->validate([
            'chat_room_id' => 'required|exists:chat_rooms,id',
            'contact_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $chatRoom = ChatRoom::whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($request->chat_room_id);

        $contact = User::findOrFail($request->contact_id);

        DB::beginTransaction();
        try {
            $message = ChatMessage::create([
                'chat_room_id' => $chatRoom->id,
                'sender_id' => $user->id,
                'receiver_id' => $chatRoom->getOtherParticipant($user->id)?->id,
                'message_type' => 'contact',
                'content' => "تم مشاركة جهة الاتصال: {$contact->name}",
                'metadata' => [
                    'contact_id' => $contact->id,
                    'contact_name' => $contact->name,
                    'contact_name_ar' => $contact->name_ar,
                    'contact_email' => $contact->email,
                    'contact_phone' => $contact->primaryPhone?->phone_number,
                    'contact_position' => $contact->position,
                    'contact_position_ar' => $contact->position_ar,
                    'contact_avatar' => $contact->profile_picture,
                ],
                'status' => 'sent',
            ]);

            $chatRoom->updateLastMessage();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message->load(['user']),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال جهة الاتصال',
            ], 500);
        }
    }

    /**
     * الحصول على الرسائل
     */
    public function getMessages(Request $request, $chatRoomId)
    {
        $user = Auth::user();
        $chatRoom = ChatRoom::whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($chatRoomId);

        $messages = $chatRoom->messages()
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($messages);
    }

    /**
     * تحديث حالة الرسالة كمقروءة
     */
    public function markAsRead(Request $request, $messageId)
    {
        $user = Auth::user();
        $message = ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($messageId);

        if ($message->sender_id !== $user->id) {
            $message->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * تحديث جميع الرسائل في دردشة كمقروءة
     */
    public function markAllAsRead(Request $request, $chatRoomId)
    {
        $user = Auth::user();
        $chatRoom = ChatRoom::whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($chatRoomId);

        // تحديث آخر قراءة للمشارك
        $participant = $chatRoom->participants()
            ->where('user_id', $user->id)
            ->first();
        
        if ($participant) {
            $participant->markAsRead();
        }

        // تحديث حالة الرسائل
        ChatMessage::where('chat_room_id', $chatRoomId)
            ->where('sender_id', '!=', $user->id)
            ->where('status', '!=', 'read')
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * تعديل رسالة
     */
    public function edit(Request $request, $messageId)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        $message = ChatMessage::where('sender_id', $user->id)
            ->whereHas('chatRoom.participants', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($messageId);

        $message->edit($request->content);

        return response()->json([
            'success' => true,
            'message' => $message->load(['sender', 'receiver']),
        ]);
    }

    /**
     * حذف رسالة
     */
    public function delete($messageId)
    {
        $user = Auth::user();
        $message = ChatMessage::where('sender_id', $user->id)
            ->whereHas('chatRoom.participants', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($messageId);

        $message->softDelete();

        return response()->json(['success' => true]);
    }

    /**
     * البحث في الرسائل
     */
    public function search(Request $request, $chatRoomId)
    {
        $query = $request->get('q');
        $user = Auth::user();

        if (empty($query)) {
            return response()->json([]);
        }

        $chatRoom = ChatRoom::whereHas('participants', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($chatRoomId);

        $messages = $chatRoom->messages()
            ->where('content', 'like', "%{$query}%")
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($messages);
    }

    /**
     * الحصول على إحصائيات الرسائل
     */
    public function getStats($chatRoomId)
    {
        $user = Auth::user();
        $chatRoom = ChatRoom::whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($chatRoomId);

        $totalMessages = $chatRoom->messages()->count();
        $unreadMessages = $chatRoom->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('status', '!=', 'read')
            ->count();

        return response()->json([
            'total_messages' => $totalMessages,
            'unread_messages' => $unreadMessages,
        ]);
    }
}
