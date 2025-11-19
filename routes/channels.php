<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatRoom;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| هنا يتم تسجيل جميع قنوات البث للتطبيق.
| يتم استخدام broadcast auth route لتأكيد أن المستخدم
| لديه صلاحية الاستماع على القناة.
|
*/

// قناة الإشعارات الخاصة بالمستخدم
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// قناة الدردشة - فقط للمشاركين في المحادثة (Presence Channel لاستخدام toOthers())
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    $chatRoom = ChatRoom::find($roomId);
    
    if (!$chatRoom) {
        return false;
    }
    
    // التحقق من أن المستخدم مشارك في المحادثة
    $isParticipant = $chatRoom->participants()
        ->where('user_id', $user->id)
        ->exists();
    
    if ($isParticipant) {
        // إرجاع بيانات المستخدم (مطلوب لـ Presence Channel)
        return [
            'id' => $user->id,
            'name' => $user->name,
            'profile_picture' => $user->profile_picture,
        ];
    }
    
    return false;
});

