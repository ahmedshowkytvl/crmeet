<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // إضافة الحقول العربية
            if (!Schema::hasColumn('tasks', 'title_ar')) {
                $table->string('title_ar')->nullable()->after('title');
            }
            if (!Schema::hasColumn('tasks', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
            
            // إضافة حقل التكرار
            if (!Schema::hasColumn('tasks', 'repeat_type')) {
                $table->enum('repeat_type', ['one_time', 'daily', 'quarterly', 'yearly'])
                      ->default('one_time')
                      ->after('category');
            }
            
            // إضافة معرف القسم
            if (!Schema::hasColumn('tasks', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null')->after('assigned_to');
            }
            
            // حقل لتحديد إذا كان المنشئ يمكنه تحديث الحالة
            if (!Schema::hasColumn('tasks', 'creator_can_update_status')) {
                $table->boolean('creator_can_update_status')->default(false)->after('status');
            }
            
            // تاريخ آخر تكرار للمهمة
            if (!Schema::hasColumn('tasks', 'last_repeated_at')) {
                $table->timestamp('last_repeated_at')->nullable()->after('due_date');
            }
            
            // تاريخ التكرار التالي
            if (!Schema::hasColumn('tasks', 'next_repeat_at')) {
                $table->timestamp('next_repeat_at')->nullable()->after('last_repeated_at');
            }
            
            // حالة المهمة المكررة (نشطة/موقوفة)
            if (!Schema::hasColumn('tasks', 'is_repeat_active')) {
                $table->boolean('is_repeat_active')->default(true)->after('next_repeat_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'is_repeat_active')) {
                $table->dropColumn('is_repeat_active');
            }
            if (Schema::hasColumn('tasks', 'next_repeat_at')) {
                $table->dropColumn('next_repeat_at');
            }
            if (Schema::hasColumn('tasks', 'last_repeated_at')) {
                $table->dropColumn('last_repeated_at');
            }
            if (Schema::hasColumn('tasks', 'creator_can_update_status')) {
                $table->dropColumn('creator_can_update_status');
            }
            if (Schema::hasColumn('tasks', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
            if (Schema::hasColumn('tasks', 'repeat_type')) {
                $table->dropColumn('repeat_type');
            }
            if (Schema::hasColumn('tasks', 'description_ar')) {
                $table->dropColumn('description_ar');
            }
            if (Schema::hasColumn('tasks', 'title_ar')) {
                $table->dropColumn('title_ar');
            }
        });
    }
};





