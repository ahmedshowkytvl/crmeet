<?php

use Illuminate\Support\Facades\Broadcast;

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

