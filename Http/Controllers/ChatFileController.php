<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatParticipant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * رفع ملف للدردشة مع تحسينات الأداء والأمان
     */
    public function upload(Request $request)
    {
        $request->validate([
            'chat_room_id' => 'required|exists:chat_rooms,id',
            'file' => 'required|file|max:20480', // 20MB max
        ]);

        $user = Auth::user();
        $chatRoom = ChatRoom::whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($request->chat_room_id);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $mimeType = $file->getMimeType();

        // التحقق من نوع الملف المسموح
        $allowedTypes = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
            'archive' => ['zip', 'rar', '7z'],
            'video' => ['mp4', 'avi', 'mov', 'wmv'],
            'audio' => ['mp3', 'wav', 'ogg']
        ];

        $messageType = $this->getMessageType($mimeType, $extension);
        
        if (!$this->isAllowedFileType($extension, $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'نوع الملف غير مسموح'
            ], 400);
        }
        
        // إنشاء اسم فريد للملف مع timestamp
        $fileName = time() . '_' . Str::uuid() . '.' . $extension;
        
        // تحديد مجلد التخزين بناءً على نوع الملف
        $folder = $this->getStorageFolder($messageType);
        $path = $file->storeAs($folder, $fileName, 'public');

        DB::beginTransaction();
        try {
            $message = ChatMessage::create([
                'chat_room_id' => $chatRoom->id,
                'sender_id' => $user->id,
                'receiver_id' => $chatRoom->getOtherParticipant($user->id)?->id,
                'message_type' => $messageType,
                'content' => $messageType === 'image' ? 'صورة' : 'ملف مرفق',
                'attachment_url' => $path,
                'attachment_name' => $originalName,
                'attachment_size' => $size,
                'attachment_type' => $mimeType,
                'status' => 'sent',
            ]);

            $chatRoom->updateLastMessage();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message->load(['sender', 'receiver']),
                'file_url' => Storage::url($path),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            // حذف الملف في حالة فشل الحفظ
            Storage::disk('public')->delete($path);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء رفع الملف',
            ], 500);
        }
    }

    /**
     * تحميل ملف
     */
    public function download($messageId)
    {
        $user = Auth::user();
        $message = ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($messageId);

        if (!$message->attachment_url) {
            abort(404, 'الملف غير موجود');
        }

        $filePath = storage_path('app/public/' . $message->attachment_url);
        
        if (!file_exists($filePath)) {
            abort(404, 'الملف غير موجود');
        }

        return response()->download($filePath, $message->attachment_name);
    }

    /**
     * عرض صورة
     */
    public function viewImage($messageId)
    {
        $user = Auth::user();
        $message = ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('message_type', 'image')
        ->findOrFail($messageId);

        if (!$message->attachment_url) {
            abort(404, 'الصورة غير موجودة');
        }

        $filePath = storage_path('app/public/' . $message->attachment_url);
        
        if (!file_exists($filePath)) {
            abort(404, 'الصورة غير موجودة');
        }

        return response()->file($filePath);
    }

    /**
     * حذف ملف
     */
    public function delete($messageId)
    {
        $user = Auth::user();
        $message = ChatMessage::where('sender_id', $user->id)
            ->whereHas('chatRoom.participants', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($messageId);

        // حذف الملف من التخزين
        if ($message->attachment_url) {
            Storage::disk('public')->delete($message->attachment_url);
        }

        // حذف الرسالة
        $message->softDelete();

        return response()->json(['success' => true]);
    }

    /**
     * الحصول على معلومات الملف
     */
    public function getFileInfo($messageId)
    {
        $user = Auth::user();
        $message = ChatMessage::whereHas('chatRoom.participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($messageId);

        return response()->json([
            'id' => $message->id,
            'name' => $message->attachment_name,
            'size' => $message->attachment_size,
            'size_formatted' => $message->attachment_size_formatted,
            'type' => $message->attachment_type,
            'url' => $message->attachment_url ? Storage::url($message->attachment_url) : null,
            'is_image' => $message->message_type === 'image',
            'created_at' => $message->created_at,
        ]);
    }

    /**
     * تحديد نوع الرسالة بناءً على نوع الملف
     */
    private function getMessageType($mimeType, $extension)
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        if (in_array($mimeType, $imageTypes) || in_array(strtolower($extension), $imageExtensions)) {
            return 'image';
        }

        return 'file';
    }

    /**
     * الحصول على قائمة الملفات في دردشة
     */
    public function getFiles($chatRoomId)
    {
        $user = Auth::user();
        $chatRoom = ChatRoom::whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($chatRoomId);

        $files = $chatRoom->messages()
            ->whereIn('message_type', ['image', 'file'])
            ->whereNotNull('attachment_url')
            ->with(['sender'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($message) {
                return [
                    'id' => $message->id,
                    'name' => $message->attachment_name,
                    'size' => $message->attachment_size,
                    'size_formatted' => $message->attachment_size_formatted,
                    'type' => $message->attachment_type,
                    'url' => Storage::url($message->attachment_url),
                    'is_image' => $message->message_type === 'image',
                    'sender' => $message->sender->name,
                    'created_at' => $message->created_at,
                ];
            });

        return response()->json($files);
    }

    /**
     * التحقق من نوع الملف المسموح
     */
    private function isAllowedFileType($extension, $allowedTypes)
    {
        foreach ($allowedTypes as $types) {
            if (in_array(strtolower($extension), $types)) {
                return true;
            }
        }
        return false;
    }

    /**
     * تحديد مجلد التخزين بناءً على نوع الملف
     */
    private function getStorageFolder($messageType)
    {
        $folders = [
            'image' => 'chat/images',
            'document' => 'chat/documents',
            'video' => 'chat/videos',
            'audio' => 'chat/audio',
            'archive' => 'chat/archives',
            'file' => 'chat/files'
        ];

        return $folders[$messageType] ?? 'chat/files';
    }
}
