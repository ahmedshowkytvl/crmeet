<?php
// ملف لتشغيل migration يدوياً
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

// إعداد قاعدة البيانات
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'crm_stafftobia', // استبدل باسم قاعدة البيانات الصحيح
    'username' => 'root', // استبدل باسم المستخدم الصحيح
    'password' => '', // استبدل بكلمة المرور الصحيحة
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    // إضافة الحقول الجديدة
    Capsule::schema()->table('users', function (Blueprint $table) {
        // التحقق من وجود الحقول قبل إضافتها
        if (!Capsule::schema()->hasColumn('users', 'phone_mobile')) {
            $table->string('phone_mobile')->nullable()->after('phone_personal');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'phone_emergency')) {
            $table->string('phone_emergency')->nullable()->after('phone_mobile');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'whatsapp')) {
            $table->string('whatsapp')->nullable()->after('phone_emergency');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'telegram')) {
            $table->string('telegram')->nullable()->after('whatsapp');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'skype')) {
            $table->string('skype')->nullable()->after('telegram');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'facebook')) {
            $table->string('facebook')->nullable()->after('skype');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'instagram')) {
            $table->string('instagram')->nullable()->after('facebook');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'employee_id')) {
            $table->string('employee_id')->nullable()->after('job_title');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'hire_date')) {
            $table->date('hire_date')->nullable()->after('employee_id');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'work_location')) {
            $table->string('work_location')->nullable()->after('hire_date');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'office_room')) {
            $table->string('office_room')->nullable()->after('work_location');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'extension')) {
            $table->string('extension')->nullable()->after('office_room');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'birth_date')) {
            $table->date('birth_date')->nullable()->after('birthday');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'nationality')) {
            $table->string('nationality')->nullable()->after('birth_date');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'address')) {
            $table->text('address')->nullable()->after('nationality');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'city')) {
            $table->string('city')->nullable()->after('address');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'country')) {
            $table->string('country')->nullable()->after('city');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'postal_code')) {
            $table->string('postal_code')->nullable()->after('country');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'skills')) {
            $table->text('skills')->nullable()->after('bio');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'interests')) {
            $table->text('interests')->nullable()->after('skills');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'languages')) {
            $table->string('languages')->nullable()->after('interests');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'show_phone_work')) {
            $table->boolean('show_phone_work')->default(true)->after('notification_preferences');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'show_phone_personal')) {
            $table->boolean('show_phone_personal')->default(false)->after('show_phone_work');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'show_phone_mobile')) {
            $table->boolean('show_phone_mobile')->default(true)->after('show_phone_personal');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'show_email')) {
            $table->boolean('show_email')->default(true)->after('show_phone_mobile');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'show_address')) {
            $table->boolean('show_address')->default(false)->after('show_email');
        }
        
        if (!Capsule::schema()->hasColumn('users', 'show_social_media')) {
            $table->boolean('show_social_media')->default(true)->after('show_address');
        }
    });
    
    echo "تم إضافة حقول بطاقة الاتصال الشاملة بنجاح!\n";
    
} catch (Exception $e) {
    echo "خطأ في إضافة الحقول: " . $e->getMessage() . "\n";
}
?>
