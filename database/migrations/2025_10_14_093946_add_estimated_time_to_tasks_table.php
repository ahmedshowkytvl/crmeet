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
            $table->float('estimated_time')->nullable()->after('sla_hours'); // الوقت المقدر بالساعات
            $table->unsignedBigInteger('task_template_id')->nullable()->after('estimated_time'); // مرجع للقالب المستخدم
            $table->foreign('task_template_id')->references('id')->on('task_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['task_template_id']);
            $table->dropColumn(['estimated_time', 'task_template_id']);
        });
    }
};
