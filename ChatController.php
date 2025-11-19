<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * عرض صفحة الدردشة الرئيسية
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedConversationId = $request->get('conversation');
        
        // Get conversations for the sidebar
        $conversations = $user->chatRooms()
            ->wherePivot('is_archived', false)
            ->with(['lastMessage', 'users' => function($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            }])
            ->orderBy('chat_rooms.last_message_at', 'desc')
            ->get();
        
        $conversations = $conversations->map(function($chatRoom) use ($user) {
                $unreadCount = $chatRoom->participants()
                    ->where('user_id', $user->id)
                    ->first()?->unread_count ?? 0;
                
                $otherParticipant = $chatRoom->getOtherParticipant($user->id);
                
                return [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'type' => $chatRoom->type,
                    'display_name' => $chatRoom->getDisplayName($user->id),
                    'avatar' => $chatRoom->avatar,
                    'last_message' => $chatRoom->lastMessage,
                    'last_message_at' => $chatRoom->last_message_at,
                    'created_at' => $chatRoom->created_at,
                    'unread_count' => $unreadCount,
                    'other_participant' => $otherParticipant,
                    'participant_count' => $chatRoom->users()->count()
                ];
            });

        // Get selected conversation or first conversation messages if available
        $firstConversation = null;
        $messages = collect();
        
        if ($selectedConversationId) {
            $firstConversation = $conversations->firstWhere('id', $selectedConversationId);
        } else {
            $firstConversation = $conversations->first();
        }
        
        if ($firstConversation && $firstConversation['id']) {
            $messages = ChatMessage::where('chat_room_id', $firstConversation['id'])
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get();
            
            $messages = $messages->map(function($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->message,
                        'user_name' => $message->user->name,
                        'user_avatar' => $message->user->profile_picture ? 
                            asset('storage/' . $message->user->profile_picture) : 
                            'https://ui-avatars.com/api/?name=' . urlencode($message->user->name) . '&color=7F9CF5&background=EBF4FF',
                        'time' => $message->created_at->diffForHumans(),
                        'is_own' => $message->user_id === auth()->id(),
                        'read_at' => $message->read_at
                    ];
                });
            $messages = $messages->toArray();
        }

        // Get dynamic statistics
        $totalMessages = ChatMessage::count();
        $totalUsers = User::count();
        $onlineUsers = User::where('updated_at', '>', now()->subMinutes(5))->count();
        // حساب الرسائل غير المقروءة
        $unreadMessages = 0;
        foreach ($conversations as $conversation) {
            $unreadMessages += $conversation['unread_count'];
        }

        return view('chat.index', compact('conversations', 'messages', 'firstConversation', 'totalMessages', 'totalUsers', 'onlineUsers', 'unreadMessages'));
    }

    /**
     * عرض صفحة بدء دردشة جديدة
     */
    public function startChat(Request $request)
    {
        $user = Auth::user();
        
        // الحصول على جميع الدردشات النشطة للمستخدم
        $chatRooms = $user->chatRooms()
            ->wherePivot('is_archived', false)
            ->with(['lastMessage', 'users' => function($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            }])
            ->orderBy('chat_rooms.last_message_at', 'desc')
            ->get();

        // الحصول على قائمة الموظفين للبحث
        $employees = User::activeEmployees()
            ->where('id', '!=', $user->id)
            ->select('id', 'name', 'name_ar', 'profile_picture')
            ->get();

        return view('chat.start', compact('chatRooms', 'employees'));
    }

    /**
     * بدء دردشة سريعة (بدون CSRF وبدون auth)
     */
    public function startQuickChat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // استخدام user ID 1 كـ default (admin)
        $currentUser = User::find(1);
        $targetUser = User::findOrFail($request->user_id);

        // التحقق من وجود دردشة خاصة بين المستخدمين
        $existingChat = ChatRoom::where('type', 'private')
            ->whereHas('participants', function($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })
            ->whereHas('participants', function($query) use ($targetUser) {
                $query->where('user_id', $targetUser->id);
            })
            ->first();

        if ($existingChat) {
            return response()->json(['redirect' => route('chat.show', $existingChat->id)]);
        }

        // إنشاء دردشة جديدة
        DB::beginTransaction();
        try {
            $chatRoom = ChatRoom::create([
                'name' => 'دردشة خاصة',
                'type' => 'private',
                'is_active' => true,
                'created_by' => $currentUser->id,
            ]);

            // إضافة المشاركين
            ChatParticipant::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $currentUser->id,
                'role' => 'member',
            ]);

            ChatParticipant::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $targetUser->id,
                'role' => 'member',
            ]);

            DB::commit();

            return response()->json(['redirect' => route('chat.show', $chatRoom->id)]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'حدث خطأ أثناء إنشاء الدردشة'], 500);
        }
    }

    /**
     * بدء دردشة مباشرة مع موظف (بدون CSRF)
     */
    public function startDirectChat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $targetUser = User::findOrFail($request->user_id);

        // التحقق من وجود دردشة خاصة بين المستخدمين
        $existingChat = ChatRoom::where('type', 'private')
            ->whereHas('participants', function($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })
            ->whereHas('participants', function($query) use ($targetUser) {
                $query->where('user_id', $targetUser->id);
            })
            ->first();

        if ($existingChat) {
            return response()->json(['redirect' => route('chat.show', $existingChat->id)]);
        }

        // إنشاء دردشة جديدة
        DB::beginTransaction();
        try {
            $chatRoom = ChatRoom::create([
                'name' => 'دردشة خاصة',
                'type' => 'private',
                'is_active' => true,
                'created_by' => $currentUser->id,
            ]);

            // إضافة المشاركين
            ChatParticipant::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $currentUser->id,
                'role' => 'member',
            ]);

            ChatParticipant::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $targetUser->id,
                'role' => 'member',
            ]);

            DB::commit();

            return response()->json(['redirect' => route('chat.show', $chatRoom->id)]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'حدث خطأ أثناء إنشاء الدردشة'], 500);
        }
    }

    /**
     * بدء دردشة جديدة مع موظف
     */
public function startChatWithUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $targetUser = User::findOrFail($request->user_id);

        // التحقق من وجود دردشة خاصة بين المستخدمين
        $existingChat = ChatRoom::where('type', 'private')
            ->whereHas('participants', function($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })
            ->whereHas('participants', function($query) use ($targetUser) {
                $query->where('user_id', $targetUser->id);
            })
            ->first();

        if ($existingChat) {
            if ($request->expectsJson()) {
                return response()->json(['redirect' => route('chat.show', $existingChat->id)]);
            }
            return redirect()->route('chat.show', $existingChat->id);
        }

        // إنشاء دردشة جديدة
        DB::beginTransaction();
        try {
            $chatRoom = ChatRoom::create([
                'name' => 'دردشة خاصة',
                'type' => 'private',
                'is_active' => true,
                'created_by' => $currentUser->id,
            ]);

            // إضافة المشاركين
            ChatParticipant::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $currentUser->id,
                'role' => 'member',
            ]);

            ChatParticipant::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $targetUser->id,
                'role' => 'member',
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['redirect' => route('chat.show', $chatRoom->id)]);
            }
            return redirect()->route('chat.show', $chatRoom->id);
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->expectsJson()) {
                return response()->json(['error' => 'حدث خطأ أثناء إنشاء الدردشة'], 500);
            }
            return back()->with('error', 'حدث خطأ أثناء إنشاء الدردشة');
        }
    }

    /**
     * عرض دردشة محددة
     */
    public function show($id)
    {
        $user = Auth::user();
        $chatRoom = ChatRoom::whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        // الحصول على الرسائل
        $messages = $chatRoom->messages()
            ->with(['user'])
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        // الحصول على المشاركين الآخرين
        $otherParticipants = $chatRoom->users()
            ->where('user_id', '!=', $user->id)
            ->get();

        // تحديث آخر قراءة
        $participant = $chatRoom->participants()
            ->where('user_id', $user->id)
            ->first();
        
        if ($participant) {
            $participant->markAsRead();
        }

        return view('chat.show', compact('chatRoom', 'messages', 'otherParticipants'));
    }

    /**
     * البحث في الدردشات
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $user = Auth::user();

        if (empty($query)) {
            return response()->json([]);
        }

        // البحث في أسماء المشاركين
        $chatRooms = $user->chatRooms()
            ->whereHas('users', function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('name_ar', 'like', "%{$query}%");
            })
            ->with(['lastMessage', 'users' => function($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            }])
            ->limit(10)
            ->get();

        return response()->json($chatRooms);
    }

    /**
     * البحث عن موظفين للدردشة
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        $user = Auth::user();

        if (empty($query)) {
            return response()->json([]);
        }

        $users = User::activeEmployees()
            ->where('id', '!=', $user->id)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('name_ar', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'name_ar', 'profile_picture', 'job_title', 'email')
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    /**
     * Send message in static chat
     */
    public function sendStaticMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'chat_room_id' => 'required|exists:chat_rooms,id'
        ]);

        $message = ChatMessage::create([
            'chat_room_id' => $request->chat_room_id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'type' => 'text'
        ]);

        // Update last_message_at
        ChatRoom::where('id', $request->chat_room_id)
            ->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'content' => $message->message,
                'user_name' => auth()->user()->name,
                'user_avatar' => auth()->user()->profile_picture ? 
                    asset('storage/' . auth()->user()->profile_picture) : 
                    'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&color=7F9CF5&background=EBF4FF',
                'time' => $message->created_at->diffForHumans(),
                'is_own' => true
            ]
        ]);
    }

    public function getUsers()
    {
        try {
            $users = User::where('id', '!=', auth()->id())
                ->select('id', 'name', 'email', 'profile_picture', 'job_title', 'updated_at')
                ->orderBy('name')
                ->get();

            $users = $users->map(function($user) {
                $isOnline = $user->updated_at && $user->updated_at->diffInMinutes(now()) < 5;
                
                return [
                    'id' => $user->id,
                    'name' => $user->name ?? 'Unknown User',
                    'email' => $user->email,
                    'role' => $user->job_title ?? 'موظف',
                    'avatar' => $user->profile_picture ? asset('storage/' . $user->profile_picture) : null,
                    'status' => $isOnline ? 'online' : 'offline',
                    'last_activity' => $user->updated_at ? $user->updated_at->diffForHumans() : 'لم يظهر مؤخراً'
                ];
            });

            return response()->json($users);
        } catch (\Exception $e) {
            \Log::error('Error in getUsers: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ في تحميل المستخدمين'], 500);
        }
    }

    /**
     * Static Chat Interface - Display static version with real data
     */
    public function staticChat(Request $request)
    {
        $user = Auth::user();
        $selectedConversationId = $request->get('conversation');
        
        // Get conversations for the sidebar
        $conversations = $user->chatRooms()
            ->wherePivot('is_archived', false)
            ->with(['lastMessage', 'users' => function($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            }])
            ->orderBy('chat_rooms.last_message_at', 'desc')
            ->get();
        \Log::info('Found ' . $conversations->count() . ' conversations');
        $conversations = $conversations->map(function($chatRoom) use ($user) {
                $unreadCount = $chatRoom->participants()
                    ->where('user_id', $user->id)
                    ->first()?->unread_count ?? 0;
                
                $otherParticipant = $chatRoom->getOtherParticipant($user->id);
                
                return [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'type' => $chatRoom->type,
                    'display_name' => $chatRoom->getDisplayName($user->id),
                    'avatar' => $chatRoom->avatar,
                    'last_message' => $chatRoom->lastMessage,
                    'last_message_at' => $chatRoom->last_message_at,
                    'created_at' => $chatRoom->created_at,
                    'unread_count' => $unreadCount,
                    'other_participant' => $otherParticipant,
                    'participant_count' => $chatRoom->users()->count()
                ];
            });

        // Get selected conversation or first conversation messages if available
        $firstConversation = null;
        $messages = collect();
        
        if ($selectedConversationId) {
            $firstConversation = $conversations->firstWhere('id', $selectedConversationId);
        } else {
            $firstConversation = $conversations->first();
        }
        
        if ($firstConversation && $firstConversation['id']) {
            \Log::info('Loading messages for conversation: ' . $firstConversation['id']);
            $messages = ChatMessage::where('chat_room_id', $firstConversation['id'])
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get();
            \Log::info('Found ' . $messages->count() . ' messages');
            $messages = $messages->map(function($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->message,
                        'user_name' => $message->user->name,
                        'user_avatar' => $message->user->profile_picture ? 
                            asset('storage/' . $message->user->profile_picture) : 
                            'https://ui-avatars.com/api/?name=' . urlencode($message->user->name) . '&color=7F9CF5&background=EBF4FF',
                        'time' => $message->created_at->diffForHumans(),
                        'is_own' => $message->user_id === auth()->id(),
                        'read_at' => $message->read_at
                    ];
                });
            \Log::info('Mapped messages count: ' . $messages->count());
            $messages = $messages->toArray();
        }

        // Get dynamic statistics
        $totalMessages = ChatMessage::count();
        $totalUsers = User::count();
        $onlineUsers = User::where('updated_at', '>', now()->subMinutes(5))->count();
        // حساب الرسائل غير المقروءة
        $unreadMessages = 0;
        foreach ($conversations as $conversation) {
            $unreadMessages += $conversation['unread_count'];
        }

        
        return view('chat.static', compact('conversations', 'messages', 'firstConversation', 'totalMessages', 'totalUsers', 'onlineUsers', 'unreadMessages'));
    }

    /**
     * جلب قائمة المحادثات مع البيانات المحدثة
     */
    public function getConversations(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type'); // private, group, or all
        
        $query = $user->chatRooms()
            ->wherePivot('is_archived', false)
            ->with(['lastMessage', 'users' => function($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            }]);
        
        // Filter by type if specified
        if ($type && $type !== 'all') {
            $query->where('chat_rooms.type', $type);
        }
        
        $chatRooms = $query->orderBy('chat_rooms.last_message_at', 'desc')
            ->get()
            ->map(function($chatRoom) use ($user) {
                $unreadCount = $chatRoom->participants()
                    ->where('user_id', $user->id)
                    ->first()?->unread_count ?? 0;
                
                $otherParticipant = $chatRoom->getOtherParticipant($user->id);
                
                return [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'type' => $chatRoom->type,
                    'display_name' => $chatRoom->getDisplayName($user->id),
                    'avatar' => $chatRoom->avatar,
                    'last_message' => $chatRoom->lastMessage ? [
                        'content' => $chatRoom->lastMessage->message,
                        'created_at' => $chatRoom->lastMessage->created_at,
                    ] : null,
                    'last_message_at' => $chatRoom->last_message_at,
                    'unread_count' => $unreadCount,
                    'other_participant' => $otherParticipant ? [
                        'id' => $otherParticipant->id,
                        'name' => $otherParticipant->name,
                        'profile_picture' => $otherParticipant->profile_picture,
                    ] : null,
                    'participant_count' => $chatRoom->users()->count()
                ];
            });

        return response()->json([
            'success' => true,
            'conversations' => $chatRooms,
            'counts' => [
                'all' => $user->chatRooms()->wherePivot('is_archived', false)->count(),
                'private' => $user->chatRooms()->wherePivot('is_archived', false)->where('chat_rooms.type', 'private')->count(),
                'group' => $user->chatRooms()->wherePivot('is_archived', false)->where('chat_rooms.type', 'group')->count(),
            ]
        ]);
    }

    /**
     * جلب رسائل دردشة محددة
     */
    public function getMessages($chatId)
    {
        try {
            $user = Auth::user();
            
            // التحقق من وجود المستخدم
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
            }
            
            // البحث عن غرفة الدردشة
            $chatRoom = ChatRoom::whereHas('participants', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->find($chatId);
            
            if (!$chatRoom) {
                return response()->json(['success' => false, 'message' => 'Chat room not found or access denied'], 404);
            }

            // جلب الرسائل
            $messages = $chatRoom->messages()
                ->with(['user'])
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($message) {
                    return [
                        'id' => $message->id,
                        'chat_id' => $message->chat_room_id,
                        'user_id' => $message->user_id,
                        'message' => $message->message,
                        'content' => $message->message, // للتوافق مع الكود القديم
                        'type' => $message->type,
                        'created_at' => $message->created_at->toISOString(),
                        'updated_at' => $message->updated_at->toISOString(),
                        'user' => [
                            'id' => $message->user->id,
                            'name' => $message->user->name,
                            'profile_picture' => $message->user->profile_picture
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'messages' => $messages,
                'chat' => [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'type' => $chatRoom->type
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getMessages: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء دردشة جديدة
     */
    public function createChat(Request $request)
    {
        \Log::info('🚀 بدء إنشاء محادثة جديدة', [
            'request_data' => $request->all(),
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'غير معروف'
        ]);

        try {
            $request->validate([
                'type' => 'required|in:private,group',
                'users' => 'required|array|min:1',
                'name' => 'required_if:type,group|string|max:255'
            ]);
            \Log::info('✅ تم التحقق من البيانات بنجاح');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ فشل في التحقق من البيانات:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'بيانات غير صحيحة', 'details' => $e->errors()], 422);
        }

        $user = Auth::user();
        $userIds = $request->users;
        
        \Log::info('👤 بيانات المستخدم الحالي:', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);
        
        \Log::info('👥 المستخدمين المطلوبين:', $userIds);

        // التحقق من صحة المستخدمين
        $validUsers = User::whereIn('id', $userIds)
            ->where('id', '!=', $user->id)
            ->get();

        \Log::info('🔍 التحقق من المستخدمين:', [
            'requested_count' => count($userIds),
            'valid_count' => $validUsers->count(),
            'valid_users' => $validUsers->pluck('name', 'id')->toArray()
        ]);

        if ($validUsers->count() !== count($userIds)) {
            \Log::error('❌ بعض المستخدمين غير صالحين:', [
                'requested_ids' => $userIds,
                'valid_users' => $validUsers->pluck('id')->toArray(),
                'missing_users' => array_diff($userIds, $validUsers->pluck('id')->toArray())
            ]);
            return response()->json(['error' => 'بعض المستخدمين غير صالحين'], 400);
        }

        DB::beginTransaction();
        try {
            \Log::info('🔄 بدء معاملة قاعدة البيانات');
            
            if ($request->type === 'private') {
                \Log::info('🔍 البحث عن محادثة خاصة موجودة');
                // التحقق من وجود دردشة خاصة
                $existingChat = ChatRoom::where('type', 'private')
                    ->whereHas('participants', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->whereHas('participants', function($query) use ($userIds) {
                        $query->whereIn('user_id', $userIds);
                    })
                    ->first();

                if ($existingChat) {
                    \Log::info('✅ تم العثور على محادثة موجودة:', [
                        'chat_id' => $existingChat->id,
                        'chat_name' => $existingChat->name
                    ]);
                    return response()->json(['redirect' => route('chat.show', $existingChat->id)]);
                }
                \Log::info('ℹ️ لم يتم العثور على محادثة موجودة، سيتم إنشاء محادثة جديدة');
            }

            // إنشاء الدردشة
            $chatRoomData = [
                'name' => $request->type === 'group' ? $request->name : 'دردشة خاصة',
                'type' => $request->type,
                'created_by' => $user->id,
            ];
            
            \Log::info('🏗️ إنشاء المحادثة:', $chatRoomData);
            
            $chatRoom = ChatRoom::create($chatRoomData);
            
            \Log::info('✅ تم إنشاء المحادثة بنجاح:', [
                'chat_id' => $chatRoom->id,
                'chat_name' => $chatRoom->name,
                'chat_type' => $chatRoom->type
            ]);

            // إضافة المشاركين
            $participants = collect([$user->id])->merge($userIds);
            \Log::info('👥 إضافة المشاركين:', [
                'participants' => $participants->toArray(),
                'chat_id' => $chatRoom->id
            ]);
            
            foreach ($participants as $userId) {
                $participantData = [
                    'chat_room_id' => $chatRoom->id,
                    'user_id' => $userId,
                    'role' => $userId === $user->id ? 'admin' : 'member',
                    'joined_at' => now(),
                ];
                
                \Log::info('➕ إضافة مشارك:', $participantData);
                
                ChatParticipant::create($participantData);
            }

            \Log::info('✅ تم إضافة جميع المشاركين بنجاح');
            DB::commit();

            \Log::info('🎉 تم إنشاء المحادثة بنجاح:', [
                'chat_id' => $chatRoom->id,
                'chat_name' => $chatRoom->name,
                'participants_count' => $participants->count()
            ]);

            return response()->json([
                'id' => $chatRoom->id,
                'type' => $chatRoom->type,
                'name' => $chatRoom->name,
                'redirect' => route('chat.show', $chatRoom->id)
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('💥 خطأ في إنشاء المحادثة:', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'حدث خطأ أثناء إنشاء الدردشة: ' . $e->getMessage()], 500);
        }
    }

    /**
     * تحديث حالة القراءة
     */
    public function markAsRead($chatId)
    {
        $user = Auth::user();
        $participant = ChatParticipant::where('chat_room_id', $chatId)
            ->where('user_id', $user->id)
            ->first();

        if ($participant) {
            $participant->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'المشارك غير موجود'], 404);
    }

    /**
     * البحث العام المحسن في الدردشات والرسائل
     */
    public function globalSearch(Request $request)
    {
        $query = $request->get('q');
        $user = Auth::user();

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'conversations' => [],
                'messages' => []
            ]);
        }

        // البحث في الدردشات مع تحسين الأداء
        $chatRooms = $user->chatRooms()
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhereHas('users', function($subQ) use ($query) {
                      $subQ->where('name', 'like', "%{$query}%")
                           ->orWhere('name_ar', 'like', "%{$query}%")
                           ->orWhere('email', 'like', "%{$query}%");
                  });
            })
            ->with(['lastMessage', 'users' => function($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            }])
            ->limit(10)
            ->get()
            ->map(function($chatRoom) use ($user) {
                return [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->getDisplayName($user->id),
                    'type' => $chatRoom->type,
                    'last_message' => $chatRoom->lastMessage ? [
                        'content' => $chatRoom->lastMessage->message,
                        'created_at' => $chatRoom->lastMessage->created_at,
                    ] : null,
                    'participant_count' => $chatRoom->users()->count()
                ];
            });

        // البحث في الرسائل مع تحسين الأداء
        $messages = ChatMessage::whereHas('chatRoom.participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('message', 'like', "%{$query}%")
            ->with(['user', 'chatRoom'])
            ->orderBy('created_at', 'desc')
            ->limit(15)
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
            'conversations' => $chatRooms,
            'messages' => $messages,
            'query' => $query
        ]);
    }

    /**
     * أرشفة دردشة
     */
    public function archive($id)
    {
        $user = Auth::user();
        $participant = ChatParticipant::where('chat_room_id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $participant->archive();

        return back()->with('success', 'تم أرشفة الدردشة بنجاح');
    }

    /**
     * إلغاء أرشفة دردشة
     */
    public function unarchive($id)
    {
        $user = Auth::user();
        $participant = ChatParticipant::where('chat_room_id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $participant->unarchive();

        return back()->with('success', 'تم إلغاء أرشفة الدردشة بنجاح');
    }

    /**
     * كتم صوت دردشة
     */
    public function mute($id)
    {
        $user = Auth::user();
        $participant = ChatParticipant::where('chat_room_id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $participant->mute();

        return back()->with('success', 'تم كتم صوت الدردشة بنجاح');
    }

    /**
     * إلغاء كتم صوت دردشة
     */
    public function unmute($id)
    {
        $user = Auth::user();
        $participant = ChatParticipant::where('chat_room_id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $participant->unmute();

        return back()->with('success', 'تم إلغاء كتم صوت الدردشة بنجاح');
    }

    /**
     * الحصول على إحصائيات الدردشة
     */
    public function getStats()
    {
        $user = Auth::user();
        
        $totalChats = $user->chatRooms()->count();
        $unreadMessages = ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('sender_id', '!=', $user->id)
        ->where('status', '!=', 'read')
        ->count();

        return response()->json([
            'total_chats' => $totalChats,
            'unread_messages' => $unreadMessages,
        ]);
    }

    /**
     * بدء دردشة سريعة مع مستخدم
     */
    public function quickChat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $targetUser = User::findOrFail($request->user_id);

        // التحقق من وجود دردشة خاصة بين المستخدمين
        $existingChat = ChatRoom::where('type', 'private')
            ->whereHas('participants', function($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })
            ->whereHas('participants', function($query) use ($targetUser) {
                $query->where('user_id', $targetUser->id);
            })
            ->first();

        if ($existingChat) {
            return redirect()->route('chat.show', $existingChat->id);
        }

        // إنشاء دردشة جديدة
        DB::beginTransaction();
        try {
            $chatRoom = ChatRoom::create([
                'name' => 'دردشة خاصة',
                'type' => 'private',
                'is_active' => true,
                'created_by' => $currentUser->id,
            ]);

            // إضافة المشاركين
            ChatParticipant::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $currentUser->id,
                'role' => 'member',
            ]);

            ChatParticipant::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $targetUser->id,
                'role' => 'member',
            ]);

            DB::commit();

            return redirect()->route('chat.show', $chatRoom->id);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'حدث خطأ أثناء إنشاء الدردشة');
        }
    }

    /**
     * حذف محادثة بالكامل
     */
    public function deleteConversation($conversationId)
    {
        try {
            $user = Auth::user();
            
            // التحقق من وجود المحادثة
            $chatRoom = ChatRoom::findOrFail($conversationId);
            
            // التحقق من أن المستخدم مشارك في المحادثة
            $isParticipant = $chatRoom->participants()
                ->where('user_id', $user->id)
                ->exists();
            
            if (!$isParticipant) {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية لحذف هذه المحادثة'
                ], 403);
            }
            
            DB::beginTransaction();
            
            // حذف جميع الرسائل
            $chatRoom->messages()->delete();
            
            // حذف جميع المشاركين
            $chatRoom->participants()->delete();
            
            // حذف المحادثة
            $chatRoom->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المحادثة بنجاح'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error deleting conversation: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المحادثة'
            ], 500);
        }
    }
}
