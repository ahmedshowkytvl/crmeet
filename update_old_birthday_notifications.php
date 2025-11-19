<?php
/**
 * سكريبت لتحديث الإشعارات القديمة لأعياد الميلاد لإضافة metadata بالترجمتين
 * Script to update old birthday notifications to include metadata with both language translations
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Notification;
use App\Models\User;

echo "🔍 البحث عن الإشعارات القديمة لأعياد الميلاد...\n";
echo "Searching for old birthday notifications...\n\n";

// Find birthday notifications without metadata translations
$oldNotifications = Notification::where('type', 'birthday')
    ->get()
    ->filter(function($notification) {
        $metadata = $notification->metadata ?? [];
        return !isset($metadata['title_ar']) || !isset($metadata['title_en']) || 
               !isset($metadata['body_ar']) || !isset($metadata['body_en']);
    });

echo "✅ تم العثور على " . $oldNotifications->count() . " إشعار قديم\n";
echo "✅ Found " . $oldNotifications->count() . " old notifications\n\n";

$updatedCount = 0;
$skippedCount = 0;

foreach ($oldNotifications as $notification) {
    try {
        // Get metadata or create new
        $metadata = $notification->metadata ?? [];
        
        // Check if already has all translations (but still update if body contains name)
        $hasAllTranslations = isset($metadata['title_ar']) && isset($metadata['title_en']) && 
                             isset($metadata['body_ar']) && isset($metadata['body_en']);
        
        // Check if body still contains name (old format: "عيد ميلاد سعيد لـ [اسم]")
        $bodyContainsName = false;
        if ($notification->body) {
            $bodyContainsName = preg_match('/(?:لـ|to|for)\s+[^\s!]+/', $notification->body);
        }
        
        // If has translations but body still has name, update it
        if ($hasAllTranslations && !$bodyContainsName) {
            $skippedCount++;
            continue;
        }
        
        // Get actor (birthday user)
        $actor = $notification->actor;
        if (!$actor) {
            echo "⚠️  الإشعار #{$notification->id}: لا يوجد actor\n";
            $skippedCount++;
            continue;
        }
        
        // Determine current language from existing title/body
        $isArabicBody = preg_match('/[\x{0600}-\x{06FF}]/u', $notification->body ?? '');
        $isArabicTitle = preg_match('/[\x{0600}-\x{06FF}]/u', $notification->title ?? '');
        
        // Extract name from body if present (for old format: "عيد ميلاد سعيد لـ [اسم]")
        $bodyText = $notification->body ?? '';
        $actorName = $actor->name_ar ?: $actor->name;
        
        // Prepare translations
        $titleAr = 'عيد ميلاد سعيد! 🎉';
        $titleEn = 'Happy Birthday! 🎉';
        
        // Check if this is a self-notification (user notifying themselves)
        $isSelfNotification = ($notification->user_id === $notification->actor_id);
        
        if ($isSelfNotification) {
            // Calculate age
            $age = null;
            if ($actor->birthday || $actor->birth_date) {
                $birthday = $actor->birthday ?? $actor->birth_date;
                $age = \Carbon\Carbon::today()->year - \Carbon\Carbon::parse($birthday)->year;
            }
            
            $bodyAr = $age 
                ? "عيد ميلادك اليوم! نتمنى لك عيد ميلاد سعيد في السنة الـ {$age}! 🎂🎈"
                : "عيد ميلادك اليوم! عيد ميلاد سعيد! 🎂🎈";
            $bodyEn = $age
                ? "It's your birthday today! Happy {$age}th Birthday! 🎂🎈"
                : "It's your birthday today! Happy Birthday! 🎂🎈";
        } else {
            // Notification to others about someone's birthday
            $bodyAr = "اليوم هو عيد ميلاد! نتمنى له يوماً سعيداً! 🎂";
            $bodyEn = "It's their birthday today! We wish them a wonderful day! 🎂";
        }
        
        // Update metadata
        $metadata['title_ar'] = $titleAr;
        $metadata['title_en'] = $titleEn;
        $metadata['body_ar'] = $bodyAr;
        $metadata['body_en'] = $bodyEn;
        
        // Preserve existing metadata
        if (!isset($metadata['birthday_user_id'])) {
            $metadata['birthday_user_id'] = $actor->id;
        }
        if (!isset($metadata['birthday_user_name'])) {
            $metadata['birthday_user_name'] = $actor->name_ar ?: $actor->name;
        }
        if (!isset($metadata['notification_type'])) {
            $metadata['notification_type'] = 'birthday';
        }
        if ($isSelfNotification && !isset($metadata['is_self_notification'])) {
            $metadata['is_self_notification'] = true;
        }
        
        // Update notification
        $notification->metadata = $metadata;
        
        // Always update title and body to remove name from body (for consistency)
        // Use Arabic if current title/body is Arabic, otherwise use English
        if ($isArabicTitle || $isArabicBody) {
            // Keep Arabic as default, but use new format without name
            $notification->title = $titleAr;
            $notification->body = $bodyAr;
        } else {
            // Use English format without name
            $notification->title = $titleEn;
            $notification->body = $bodyEn;
        }
        
        $notification->save();
        
        $updatedCount++;
        echo "✅ تم تحديث الإشعار #{$notification->id}\n";
        
    } catch (\Exception $e) {
        echo "❌ خطأ في تحديث الإشعار #{$notification->id}: " . $e->getMessage() . "\n";
        $skippedCount++;
    }
}

echo "\n📊 الإحصائيات / Statistics:\n";
echo "   ✅ تم التحديث / Updated: {$updatedCount}\n";
echo "   ⏭️  تم التخطي / Skipped: {$skippedCount}\n";
echo "   📝 المجموع / Total: " . ($updatedCount + $skippedCount) . "\n\n";
echo "🎉 تم الانتهاء!\n";
echo "🎉 Done!\n";

