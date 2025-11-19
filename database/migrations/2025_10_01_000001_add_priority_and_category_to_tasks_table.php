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
            if (!Schema::hasColumn('tasks', 'priority')) {
                $table->string('priority')->default('medium')->after('status');
            }
            if (!Schema::hasColumn('tasks', 'category')) {
                $table->string('category')->nullable()->after('priority');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('tasks', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};



