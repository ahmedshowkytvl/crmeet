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
        Schema::table('users', function (Blueprint $table) {
            // إضافة الأعمدة المفقودة
            $table->string('phone_home', 20)->nullable()->after('phone_work');
            $table->string('extension', 10)->nullable()->after('avaya_extension');
            $table->string('work_location', 100)->nullable()->after('office_address');
            $table->string('office_room', 50)->nullable()->after('work_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // حذف الأعمدة المضافة
            $table->dropColumn(['phone_home', 'extension', 'work_location', 'office_room']);
        });
    }
};
