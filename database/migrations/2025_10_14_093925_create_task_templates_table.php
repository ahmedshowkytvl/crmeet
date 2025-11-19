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
        Schema::create('task_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المهمة (action_name)
            $table->string('name_ar')->nullable(); // اسم المهمة بالعربية
            $table->float('estimated_time')->default(0); // الوقت المقدر بالساعات (action_wait)
            $table->string('department')->nullable(); // القسم (Contracting, IT, Internet dep, Accounting, Callcenter, Marketing)
            $table->text('description')->nullable(); // وصف إضافي للمهمة
            $table->text('description_ar')->nullable(); // الوصف بالعربية
            $table->boolean('is_active')->default(true); // حالة القالب (نشط/غير نشط)
            $table->timestamps();
            
            // إضافة فهرس للاسم لضمان عدم التكرار
            $table->unique(['name', 'department']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};
