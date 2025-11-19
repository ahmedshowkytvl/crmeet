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
            // تاريخ ووقت البداية
            if (!Schema::hasColumn('tasks', 'start_datetime')) {
                $table->timestamp('start_datetime')->nullable()->after('due_datetime');
            }
            
            // تاريخ ووقت الانتهاء
            if (!Schema::hasColumn('tasks', 'end_datetime')) {
                $table->timestamp('end_datetime')->nullable()->after('start_datetime');
            }
            
            // تاريخ بدء التنفيذ الفعلي
            if (!Schema::hasColumn('tasks', 'actual_start_datetime')) {
                $table->timestamp('actual_start_datetime')->nullable()->after('end_datetime');
            }
            
            // تاريخ انتهاء التنفيذ الفعلي
            if (!Schema::hasColumn('tasks', 'actual_end_datetime')) {
                $table->timestamp('actual_end_datetime')->nullable()->after('actual_start_datetime');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'actual_end_datetime')) {
                $table->dropColumn('actual_end_datetime');
            }
            if (Schema::hasColumn('tasks', 'actual_start_datetime')) {
                $table->dropColumn('actual_start_datetime');
            }
            if (Schema::hasColumn('tasks', 'end_datetime')) {
                $table->dropColumn('end_datetime');
            }
            if (Schema::hasColumn('tasks', 'start_datetime')) {
                $table->dropColumn('start_datetime');
            }
        });
    }
};




