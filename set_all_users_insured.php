<?php
/**
 * سكريبت تعيين حالة التأمين "insured" لجميع المستخدمين
 */

require_once __DIR__ . '/vendor/autoload.php';

// إعداد Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "🔄 بدء تحديث حالة التأمين لجميع المستخدمين...\n\n";

try {
    // التحقق من وجود العمود أولاً
    $table = DB::getTablePrefix() . 'users';
    $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'insurance_status'");
    
    if (empty($columns)) {
        echo "⚠️  العمود insurance_status غير موجود. جاري إنشاءه...\n";
        DB::statement("ALTER TABLE users ADD COLUMN insurance_status VARCHAR(20) CHECK (insurance_status IN ('insured', 'not_insured'))");
        echo "✅ تم إنشاء العمود\n";
    }
    
    // تحديث جميع المستخدمين إلى "insured"
    $totalUsers = User::count();
    $updated = DB::table('users')
                   ->where(function($query) {
                       $query->whereNull('insurance_status')
                             ->orWhere('insurance_status', '!=', 'insured');
                   })
                   ->update(['insurance_status' => 'insured', 'updated_at' => now()]);
    
    echo "✅ إجمالي المستخدمين: {$totalUsers}\n";
    echo "✅ تم تحديث {$updated} مستخدم إلى حالة 'insured'\n";
    echo "✅ تم إنجاز العملية بنجاح\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

