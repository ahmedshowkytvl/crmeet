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
        // حذف الجدول القديم إذا كان موجوداً
        Schema::dropIfExists('notifications');
        
        // جدول الإشعارات الرئيسي
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('المستخدم المستلم');
            $table->enum('type', ['message', 'task', 'asset'])->comment('نوع الإشعار');
            $table->string('title')->comment('عنوان الإشعار');
            $table->text('body')->comment('محتوى الإشعار');
            $table->unsignedBigInteger('actor_id')->nullable()->comment('المستخدم الذي قام بالإجراء');
            $table->string('resource_type', 100)->nullable()->comment('نوع المورد');
            $table->unsignedBigInteger('resource_id')->nullable()->comment('معرف المورد');
            $table->string('link', 500)->nullable()->comment('رابط الانتقال');
            $table->json('metadata')->nullable()->comment('بيانات إضافية');
            $table->boolean('is_read')->default(false)->comment('هل تم القراءة');
            $table->timestamp('read_at')->nullable()->comment('وقت القراءة');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('actor_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes للأداء
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['type', 'user_id', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
        });

        // جدول تفضيلات الإشعارات
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['message', 'task', 'asset']);
            $table->boolean('enabled')->default(true);
            $table->boolean('sound_enabled')->default(true);
            $table->boolean('browser_enabled')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notifications');
    }
};

