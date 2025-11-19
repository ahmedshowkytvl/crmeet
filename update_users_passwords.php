<?php

/**
 * سكريبت لتحديث كلمات المرور للمستخدمين المحددين إلى "password"
 * 
 * المستخدمون:
 * 1. Amr.Atef@eetglobal.net
 * 2. m.anwar@egyptexpresstvl.com
 * 3. kareem@eetglobal.net
 * 4. Khaled_ahmed@egyptexpresstvl.com
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== بدء تحديث كلمات المرور للمستخدمين ===\n\n";
    
    // قائمة المستخدمين المطلوب تحديثهم
    $userEmails = [
        'Amr.Atef@eetglobal.net',
        'm.anwar@egyptexpresstvl.com',
        'kareem@eetglobal.net',
        'Khaled_ahmed@egyptexpresstvl.com'
    ];
    
    $password = 'password';
    $hashedPassword = Hash::make($password);
    
    // تحديث كل مستخدم
    $updatedUsers = [];
    $notFoundUsers = [];
    
    foreach ($userEmails as $email) {
        echo "معالجة المستخدم: {$email}\n";
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            echo "  ✗ المستخدم غير موجود\n\n";
            $notFoundUsers[] = $email;
            continue;
        }
        
        // تحديث كلمة المرور
        $user->password = $hashedPassword;
        $user->save();
        
        echo "  ✓ تم تحديث كلمة المرور بنجاح\n";
        echo "    - الاسم: {$user->name}\n";
        echo "    - البريد: {$user->email}\n";
        echo "    - معرف المستخدم: {$user->id}\n";
        echo "    - كلمة المرور: {$password}\n\n";
        
        $updatedUsers[] = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
    
    // ملخص النتائج
    echo "=== ملخص النتائج ===\n\n";
    echo "المستخدمون المحدثون بنجاح: " . count($updatedUsers) . "\n";
    foreach ($updatedUsers as $user) {
        echo "  ✓ {$user['email']} (ID: {$user['id']}) - كلمة المرور: {$password}\n";
    }
    
    if (count($notFoundUsers) > 0) {
        echo "\nالمستخدمون غير الموجودين: " . count($notFoundUsers) . "\n";
        foreach ($notFoundUsers as $email) {
            echo "  ✗ {$email}\n";
        }
    }
    
    echo "\n=== تم الانتهاء بنجاح ===\n";
    echo "جميع المستخدمين المحدثين لديهم الآن كلمة المرور: {$password}\n";
    
} catch (\Exception $e) {
    echo "\n❌ خطأ: " . $e->getMessage() . "\n";
    echo "السطر: " . $e->getLine() . "\n";
    echo "الملف: " . $e->getFile() . "\n";
    exit(1);
}

