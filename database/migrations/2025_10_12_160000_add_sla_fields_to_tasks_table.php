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
            // عدد الساعات المتوقعة للإنجاز (SLA)
            if (!Schema::hasColumn('tasks', 'sla_hours')) {
                $table->integer('sla_hours')->nullable()->after('due_date');
            }
            
            // وقت الاستحقاق (بالإضافة للتاريخ)
            if (!Schema::hasColumn('tasks', 'due_time')) {
                $table->time('due_time')->nullable()->after('sla_hours');
            }
            
            // حقل لتخزين timestamp الاستحقاق الكامل (تاريخ + وقت)
            if (!Schema::hasColumn('tasks', 'due_datetime')) {
                $table->timestamp('due_datetime')->nullable()->after('due_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'due_datetime')) {
                $table->dropColumn('due_datetime');
            }
            if (Schema::hasColumn('tasks', 'due_time')) {
                $table->dropColumn('due_time');
            }
            if (Schema::hasColumn('tasks', 'sla_hours')) {
                $table->dropColumn('sla_hours');
            }
        });
    }
};

