<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إضافة عمود inventory_status لجدول assets
        if (!Schema::hasColumn('assets', 'inventory_status')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->string('inventory_status', 50)->default('in_stock')->after('status');
            });
        }

        // تحديث جميع السجلات الموجودة لتكون في المخزون
        DB::table('assets')->update(['inventory_status' => 'in_stock']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('inventory_status');
        });
    }
};










