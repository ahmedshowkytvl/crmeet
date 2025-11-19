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
        Schema::table('contact_categories', function (Blueprint $table) {
            // إضافة الأعمدة الناقصة
            if (!Schema::hasColumn('contact_categories', 'name_en')) {
                $table->string('name_en')->nullable()->after('name');
            }
            if (!Schema::hasColumn('contact_categories', 'description_en')) {
                $table->text('description_en')->nullable()->after('description');
            }
            if (!Schema::hasColumn('contact_categories', 'color')) {
                $table->string('color', 7)->default('#6366f1')->after('description_en');
            }
            if (!Schema::hasColumn('contact_categories', 'icon')) {
                $table->string('icon', 50)->default('folder')->after('color');
            }
            if (!Schema::hasColumn('contact_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('icon');
            }
            if (!Schema::hasColumn('contact_categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
            
            // تغيير اسم العمود name_ar إلى name (إذا كان موجوداً)
            if (Schema::hasColumn('contact_categories', 'name_ar')) {
                $table->dropColumn('name_ar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_categories', function (Blueprint $table) {
            // حذف الأعمدة عند التراجع
            $table->dropColumn([
                'name_en',
                'description_en',
                'color',
                'icon',
                'is_active',
                'sort_order'
            ]);
        });
    }
};
