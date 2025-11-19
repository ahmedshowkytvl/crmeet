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
        // إضافة عمود is_active لجدول asset_categories
        if (!Schema::hasColumn('asset_categories', 'is_active')) {
            Schema::table('asset_categories', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('price');
            });
        }

        // إضافة عمود is_active لجدول asset_locations
        if (!Schema::hasColumn('asset_locations', 'is_active')) {
            Schema::table('asset_locations', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('description_ar');
            });
        }

        // إضافة عمود is_active لجدول warehouses
        if (!Schema::hasColumn('warehouses', 'is_active')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('location');
            });
        }

        // تحديث جميع السجلات الموجودة لتكون نشطة
        DB::table('asset_categories')->update(['is_active' => true]);
        DB::table('asset_locations')->update(['is_active' => true]);
        DB::table('warehouses')->update(['is_active' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('asset_locations', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};










