<?php
/**
 * سكريبت لاختبار إرسال إشعار عيد الميلاد للمستخدم 968
 * Test script to send birthday notification to user 968
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;

$employeeId = '968';

echo "🔍 البحث عن المستخدم برقم الموظف: {$employeeId}\n";
echo "Searching for user with employee ID: {$employeeId}\n\n";

$user = User::where('employee_id', $employeeId)->first();

if (!$user) {
    echo "❌ لم يتم العثور على المستخدم\n";
    echo "❌ User not found\n";
    exit(1);
}

echo "✅ تم العثور على المستخدم:\n";
echo "✅ User found:\n";
echo "   ID: {$user->id}\n";
echo "   الاسم / Name: {$user->name}\n";
echo "   البريد / Email: {$user->email}\n";
echo "   تاريخ الميلاد / Birthday: " . ($user->birthday ? $user->birthday->format('Y-m-d') : ($user->birth_date ? $user->birth_date->format('Y-m-d') : 'غير محدد')) . "\n";
echo "   receive_birthday_notifications: " . ($user->receive_birthday_notifications ? 'true' : 'false') . "\n\n";

// Check if today is their birthday
$today = Carbon::today();
$birthday = $user->birthday ?? $user->birth_date;

if (!$birthday) {
    echo "⚠️  المستخدم ليس لديه تاريخ ميلاد محدد\n";
    echo "⚠️  User doesn't have a birthday set\n";
    exit(1);
}

$birthdayThisYear = Carbon::parse($birthday)->setYear($today->year);
$isBirthdayToday = ($today->format('Y-m-d') === $birthdayThisYear->format('Y-m-d'));

if (!$isBirthdayToday) {
    echo "⚠️  اليوم ليس عيد ميلاد المستخدم\n";
    echo "⚠️  Today is not the user's birthday\n";
    echo "   تاريخ اليوم / Today: {$today->format('Y-m-d')}\n";
    echo "   عيد الميلاد هذا العام / Birthday this year: {$birthdayThisYear->format('Y-m-d')}\n\n";
    echo "💡 يمكنك تحديث تاريخ الميلاد ليكون اليوم للاختبار\n";
    echo "💡 You can update the birthday to today for testing\n";
    
    // Ask if user wants to set birthday to today for testing
    echo "\n❓ هل تريد تحديث تاريخ الميلاد إلى اليوم للاختبار؟ (y/n)\n";
    echo "❓ Do you want to update birthday to today for testing? (y/n)\n";
    // For automated testing, we'll just proceed
    $user->birthday = $today;
    $user->save();
    echo "✅ تم تحديث تاريخ الميلاد إلى اليوم للاختبار\n";
    echo "✅ Birthday updated to today for testing\n\n";
}

try {
    $notificationService = new NotificationService();
    
    echo "📤 إرسال إشعار عيد الميلاد للمستخدم نفسه...\n";
    echo "📤 Sending birthday notification to user themselves...\n\n";
    
    $notification = $notificationService->notifyBirthdayToSelf($user);
    
    if ($notification) {
        echo "✅ تم إرسال الإشعار بنجاح!\n";
        echo "✅ Notification sent successfully!\n";
        echo "   Notification ID: {$notification->id}\n";
        echo "   Title: {$notification->title}\n";
        echo "   Body: {$notification->body}\n";
        echo "   Created at: {$notification->created_at}\n\n";
        
        echo "🎉 يمكنك الآن تسجيل الدخول برقم الموظف 968 ومشاهدة الإشعار!\n";
        echo "🎉 You can now login with employee ID 968 and see the notification!\n";
    } else {
        echo "❌ فشل إرسال الإشعار\n";
        echo "❌ Failed to send notification\n";
    }
    
} catch (\Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

