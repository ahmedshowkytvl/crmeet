<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('إنشاء إشعارات تجريبية...');

        // الحصول على أول مستخدم
        $user = User::first();

        if (!$user) {
            $this->command->error('لا يوجد مستخدمين في قاعدة البيانات!');
            $this->command->info('قم بإنشاء مستخدم أولاً: php artisan make:user');
            return;
        }

        // إنشاء 10 إشعارات رسائل
        Notification::factory()
            ->count(10)
            ->message()
            ->for($user)
            ->create(['actor_id' => $user->id]);

        $this->command->info('✓ تم إنشاء 10 إشعارات رسائل');

        // إنشاء 8 إشعارات مهام
        Notification::factory()
            ->count(8)
            ->task()
            ->for($user)
            ->create(['actor_id' => $user->id]);

        $this->command->info('✓ تم إنشاء 8 إشعارات مهام');

        // إنشاء 5 إشعارات أجهزة
        Notification::factory()
            ->count(5)
            ->asset()
            ->for($user)
            ->create(['actor_id' => $user->id]);

        $this->command->info('✓ تم إنشاء 5 إشعارات أجهزة');

        // إنشاء بعض الإشعارات غير المقروءة
        Notification::factory()
            ->count(5)
            ->unread()
            ->for($user)
            ->create(['actor_id' => $user->id]);

        $this->command->info('✓ تم إنشاء 5 إشعارات غير مقروءة');

        $total = Notification::where('user_id', $user->id)->count();
        $unread = Notification::where('user_id', $user->id)->where('is_read', false)->count();

        $this->command->info("✓ المجموع: {$total} إشعار ({$unread} غير مقروء)");
    }
}

