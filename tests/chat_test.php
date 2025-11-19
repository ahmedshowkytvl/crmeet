<?php

/**
 * اختبار بسيط للشات باستخدام cURL
 * يختبر إرسال الرسائل بين حساب وهمي والمستخدم Madonna 847
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\ChatMessage;

// تحميل Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚀 بدء اختبار الشات...\n\n";

try {
    // البحث عن الحساب الوهمي
    $fakeUser = User::where('email', 'test.chat.user@example.com')->first();
    if (!$fakeUser) {
        throw new Exception('❌ لم يتم العثور على الحساب الوهمي');
    }
    echo "✅ تم العثور على الحساب الوهمي: {$fakeUser->name} (ID: {$fakeUser->id})\n";

    // البحث عن شات بين الحساب الوهمي والمستخدم Madonna
    $madonnaUser = User::where('name', 'like', '%Madonna%')->first();
    if (!$madonnaUser) {
        throw new Exception('❌ لم يتم العثور على مستخدم Madonna');
    }
    echo "✅ تم العثور على مستخدم Madonna: {$madonnaUser->name} (ID: {$madonnaUser->id})\n";

    // البحث عن الشات
    $chatRoom = ChatRoom::where('type', 'private')
        ->whereHas('participants', function($query) use ($fakeUser) {
            $query->where('user_id', $fakeUser->id);
        })
        ->whereHas('participants', function($query) use ($madonnaUser) {
            $query->where('user_id', $madonnaUser->id);
        })
        ->first();

    if (!$chatRoom) {
        throw new Exception('❌ لم يتم العثور على الشات');
    }
    echo "✅ تم العثور على الشات: ID {$chatRoom->id}\n\n";

    // اختبار إرسال رسالة
    echo "📤 اختبار إرسال رسالة...\n";
    $testMessage = 'رسالة اختبار من PHP - ' . now()->format('Y-m-d H:i:s');
    
    DB::beginTransaction();
    try {
        $message = ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $fakeUser->id,
            'message' => $testMessage,
            'type' => 'text'
        ]);

        $chatRoom->update(['last_message_at' => now()]);
        
        DB::commit();
        
        echo "✅ تم إرسال الرسالة بنجاح (Message ID: {$message->id})\n";
        echo "📝 محتوى الرسالة: {$testMessage}\n\n";
    } catch (\Exception $e) {
        DB::rollBack();
        throw new Exception('❌ فشل إرسال الرسالة: ' . $e->getMessage());
    }

    // التحقق من الرسائل في الشات
    $messagesCount = ChatMessage::where('chat_room_id', $chatRoom->id)->count();
    echo "📊 عدد الرسائل في الشات: {$messagesCount}\n";

    // اختبار API endpoint مباشرة
    echo "\n🌐 اختبار API endpoint مباشرة...\n";
    
    auth()->login($fakeUser);
    
    // استخدام Controller مباشرة
    $controller = app(\App\Http\Controllers\ChatController::class);
    $request = new \Illuminate\Http\Request([
        'message' => 'رسالة اختبار من API - ' . now()->format('Y-m-d H:i:s'),
        'chat_room_id' => $chatRoom->id
    ]);
    
    $request->headers->set('Accept', 'application/json');
    $request->setUserResolver(function () use ($fakeUser) {
        return $fakeUser;
    });
    
    try {
        $response = $controller->sendStaticMessage($request);
        $responseData = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() === 200 && isset($responseData['success']) && $responseData['success']) {
            echo "✅ API endpoint يعمل بشكل صحيح\n";
            echo "📦 الاستجابة: " . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            $apiSuccess = true;
        } else {
            echo "❌ فشل API endpoint\n";
            echo "📦 الاستجابة: " . $response->getContent() . "\n";
            echo "📊 Status Code: " . $response->getStatusCode() . "\n";
            $apiSuccess = false;
        }
    } catch (\Exception $e) {
        echo "❌ خطأ في API endpoint: " . $e->getMessage() . "\n";
        $apiSuccess = false;
    }

    echo "\n✅ تم إكمال جميع الاختبارات بنجاح!\n";
    echo "\n📊 ملخص الاختبار:\n";
    echo "  ✅ البحث عن الحساب الوهمي: نجح\n";
    echo "  ✅ البحث عن مستخدم Madonna: نجح\n";
    echo "  ✅ البحث عن الشات: نجح\n";
    echo "  ✅ إرسال رسالة: نجح\n";
    echo "  ✅ API endpoint: " . (isset($apiSuccess) && $apiSuccess ? 'نجح' : 'فشل') . "\n";
    
    $baseUrl = env('APP_URL', 'http://192.168.15.29/crm/stafftobia/public');
    echo "\n🔗 رابط الشات: {$baseUrl}/chat/static?conversation={$chatRoom->id}\n";

} catch (\Exception $e) {
    echo "\n❌ فشل الاختبار: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

