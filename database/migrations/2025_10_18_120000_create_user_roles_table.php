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
        // إنشاء جدول user_roles للصلاحيات المتعددة
        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->boolean('active')->default(true);
                $table->timestamp('assigned_at')->useCurrent();
                $table->unsignedBigInteger('assigned_by')->nullable();
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
                
                // فهرس فريد لمنع تكرار الأدوار للمستخدم الواحد
                $table->unique(['user_id', 'role_id']);
                
                // فهارس لتحسين الأداء
                $table->index(['user_id', 'active']);
                $table->index(['role_id', 'active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
