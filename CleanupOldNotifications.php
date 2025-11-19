<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:cleanup 
                            {--days=90 : عدد الأيام للإشعارات القديمة}
                            {--unread : حذف حتى غير المقروءة}';

    /**
     * The console command description.
     */
    protected $description = 'تنظيف الإشعارات القديمة من قاعدة البيانات';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $includeUnread = $this->option('unread');

        $this->info("جاري تنظيف الإشعارات الأقدم من {$days} يوم...");

        $query = Notification::where('created_at', '<', now()->subDays($days));

        // حذف المقروءة فقط افتراضياً
        if (!$includeUnread) {
            $query->where('is_read', true);
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('لا توجد إشعارات قديمة للحذف.');
            return Command::SUCCESS;
        }

        if ($this->confirm("سيتم حذف {$count} إشعار. هل تريد المتابعة؟", true)) {
            $deleted = $query->delete();

            $this->info("✓ تم حذف {$deleted} إشعار بنجاح.");
            
            // عرض إحصائيات
            $remaining = Notification::count();
            $this->line("الإشعارات المتبقية: {$remaining}");

            return Command::SUCCESS;
        }

        $this->warn('تم إلغاء العملية.');
        return Command::FAILURE;
    }
}

