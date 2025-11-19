<?php

/**
 * تسجيل الدخول بحساب Madonna 847 وإرسال رد على الشات
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\ChatMessage;

// تحميل Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚀 بدء تسجيل الدخول وإرسال رد على الشات...\n\n";

try {
    // البحث عن المستخدم Madonna (847) - ID: 120
    $user = User::find(120); // Madonna Nashaat Anwer Seha
    
    if (!$user) {
        // محاولة البحث بطرق أخرى
        $user = User::where('email', 'marketing+120@egyptexpresstvl.com')
            ->orWhere('EmployeeCode', 847)
            ->orWhere('employee_id', '847')
            ->where('name', 'like', '%Madonna%')
            ->first();
    }
    
    if (!$user) {
        throw new Exception('❌ لم يتم العثور على المستخدم Madonna 847');
    }
    
    echo "✅ تم العثور على المستخدم: {$user->name} (ID: {$user->id}, Email: {$user->email})\n";
    
    // تسجيل الدخول
    Auth::login($user);
    echo "✅ تم تسجيل الدخول بنجاح\n\n";
    
    // البحث عن الشات (ID: 78)
    $chatRoom = ChatRoom::find(78);
    
    if (!$chatRoom) {
        throw new Exception('❌ لم يتم العثور على الشات (ID: 78)');
    }
    
    // التحقق من أن المستخدم مشارك في الشات
    $isParticipant = $chatRoom->participants()
        ->where('user_id', $user->id)
        ->exists();
    
    if (!$isParticipant) {
        throw new Exception('❌ المستخدم غير مشارك في هذا الشات');
    }
    
    echo "✅ تم العثور على الشات: ID {$chatRoom->id}\n";
    
    // قراءة آخر رسالة
    $lastMessage = ChatMessage::where('chat_room_id', $chatRoom->id)
        ->orderBy('created_at', 'desc')
        ->with('user')
        ->first();
    
    if ($lastMessage) {
        echo "\n📨 آخر رسالة في الشات:\n";
        echo "   النص: {$lastMessage->message}\n";
        echo "   من: {$lastMessage->user->name}\n";
        echo "   الوقت: {$lastMessage->created_at->diffForHumans()}\n";
    }
    
    // إرسال رد
    $replyMessage = 'شكراً لك! تم استلام رسالتك - ' . now()->format('Y-m-d H:i:s');
    
    echo "\n📤 إرسال رد: \"{$replyMessage}\"\n";
    
    DB::beginTransaction();
    try {
        $message = ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'message' => $replyMessage,
            'type' => 'text'
        ]);

        // Update last_message_at
        $chatRoom->update(['last_message_at' => now()]);
        
        DB::commit();
        
        echo "✅ تم إرسال الرسالة بنجاح (Message ID: {$message->id})\n";
        echo "📝 محتوى الرسالة: {$replyMessage}\n";
        
        // التحقق من الرسائل
        $messagesCount = ChatMessage::where('chat_room_id', $chatRoom->id)->count();
        echo "\n📊 عدد الرسائل في الشات: {$messagesCount}\n";
        
        // اختبار API endpoint
        echo "\n🌐 اختبار API endpoint...\n";
        
        $controller = app(\App\Http\Controllers\ChatController::class);
        $request = new \Illuminate\Http\Request([
            'message' => 'رسالة اختبار إضافية من API - ' . now()->format('Y-m-d H:i:s'),
            'chat_room_id' => $chatRoom->id
        ]);
        
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $response = $controller->sendStaticMessage($request);
        $responseData = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() === 200 && isset($responseData['success']) && $responseData['success']) {
            echo "✅ API endpoint يعمل بشكل صحيح\n";
            echo "📦 الرسالة المرسلة: {$responseData['message']['content']}\n";
        } else {
            echo "❌ فشل API endpoint\n";
            echo "📦 الاستجابة: " . $response->getContent() . "\n";
        }
        
        echo "\n✅ تم إكمال المهمة بنجاح!\n";
        echo "\n📊 الملخص:\n";
        echo "  ✅ تسجيل الدخول: نجح\n";
        echo "  ✅ البحث عن الشات: نجح\n";
        echo "  ✅ إرسال الرسالة: نجح\n";
        echo "  ✅ API endpoint: " . ($response->getStatusCode() === 200 ? 'نجح' : 'فشل') . "\n";
        
        $baseUrl = env('APP_URL', 'http://192.168.15.29/crm/stafftobia/public');
        echo "\n🔗 رابط الشات: {$baseUrl}/chat/static?conversation={$chatRoom->id}\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        throw new Exception('❌ فشل إرسال الرسالة: ' . $e->getMessage());
    }

} catch (\Exception $e) {
    echo "\n❌ فشل العملية: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

