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
        Schema::table('phone_types', function (Blueprint $table) {
            if (!Schema::hasColumn('phone_types', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('name_ar');
            }
            if (!Schema::hasColumn('phone_types', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('slug');
            }
            if (!Schema::hasColumn('phone_types', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phone_types', function (Blueprint $table) {
            if (Schema::hasColumn('phone_types', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
            if (Schema::hasColumn('phone_types', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('phone_types', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};


