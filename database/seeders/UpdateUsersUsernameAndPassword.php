<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateUsersUsernameAndPassword extends Seeder
{
    /**
     * Run the database seeds.
     * تحديث جميع المستخدمين بـ username وكلمة مرور موحدة
     */
    public function run(): void
    {
        // كلمة المرور الموحدة لجميع المستخدمين
        $defaultPassword = 'P@ssW0rd';
        $hashedPassword = Hash::make($defaultPassword);

        echo "\n🔄 بدء تحديث بيانات المستخدمين...\n\n";

        // جلب جميع المستخدمين
        $users = User::all();
        $totalUsers = $users->count();
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($users as $user) {
            try {
                $needsUpdate = false;
                $updates = [];

                // 1. التحقق من وجود username
                if (empty($user->username)) {
                    // توليد username من employee_id أو name أو email
                    $username = $this->generateUsername($user);
                    
                    // التأكد من أن username فريد
                    $username = $this->ensureUniqueUsername($username, $user->id);
                    
                    $updates['username'] = $username;
                    $needsUpdate = true;
                    echo "✓ المستخدم #{$user->id} - سيتم إضافة username: {$username}\n";
                }

                // 2. تحديث كلمة المرور
                $updates['password'] = $hashedPassword;
                $needsUpdate = true;

                // تنفيذ التحديث
                if ($needsUpdate) {
                    $user->update($updates);
                    $updated++;
                    
                    $displayName = $user->name_ar ?? $user->name ?? $user->email;
                    echo "✅ تم تحديث: {$displayName}";
                    if (isset($updates['username'])) {
                        echo " - Username: {$updates['username']}";
                    }
                    echo "\n";
                } else {
                    $skipped++;
                }

            } catch (\Exception $e) {
                $errors++;
                echo "❌ خطأ في تحديث المستخدم #{$user->id}: {$e->getMessage()}\n";
            }
        }

        echo "\n";
        echo "═══════════════════════════════════════════\n";
        echo "📊 ملخص التحديث:\n";
        echo "═══════════════════════════════════════════\n";
        echo "✅ إجمالي المستخدمين: {$totalUsers}\n";
        echo "✅ تم التحديث: {$updated}\n";
        echo "⏭️ تم التخطي: {$skipped}\n";
        if ($errors > 0) {
            echo "❌ الأخطاء: {$errors}\n";
        }
        echo "═══════════════════════════════════════════\n";
        echo "\n";
        echo "🔐 كلمة المرور الافتراضية لجميع المستخدمين: {$defaultPassword}\n";
        echo "\n";
    }

    /**
     * توليد username من بيانات المستخدم
     */
    private function generateUsername($user): string
    {
        // الأولوية: employee_id > EmployeeCode > name > email
        
        // محاولة استخدام employee_id
        if (!empty($user->employee_id)) {
            $username = 'emp_' . preg_replace('/[^a-zA-Z0-9]/', '', $user->employee_id);
            return strtolower($username);
        }

        // محاولة استخدام EmployeeCode
        if (!empty($user->EmployeeCode)) {
            return 'emp_' . $user->EmployeeCode;
        }

        // محاولة استخدام الاسم
        if (!empty($user->name)) {
            $nameParts = explode(' ', $user->name);
            $username = strtolower(implode('_', array_slice($nameParts, 0, 2)));
            $username = preg_replace('/[^a-z0-9_]/', '', $username);
            if (!empty($username)) {
                return $username;
            }
        }

        // محاولة استخدام الاسم العربي
        if (!empty($user->name_ar)) {
            // تحويل الاسم العربي إلى transliteration بسيط
            return 'user_' . $user->id;
        }

        // استخدام البريد الإلكتروني
        if (!empty($user->email)) {
            $emailParts = explode('@', $user->email);
            return strtolower($emailParts[0]);
        }

        // في حالة عدم وجود أي بيانات، استخدام ID
        return 'user_' . $user->id;
    }

    /**
     * التأكد من أن username فريد
     */
    private function ensureUniqueUsername($username, $userId): string
    {
        $originalUsername = $username;
        $counter = 1;

        while (User::where('username', $username)
                   ->where('id', '!=', $userId)
                   ->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }
}

