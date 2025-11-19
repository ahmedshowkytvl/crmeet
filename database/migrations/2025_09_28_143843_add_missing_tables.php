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
        // إنشاء جدول المهام
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('assigned_to');
                $table->unsignedBigInteger('created_by');
                $table->string('status')->default('pending');
                $table->date('due_date')->nullable();
                $table->timestamps();
                
                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول طلبات الموظفين
        if (!Schema::hasTable('employee_requests')) {
            Schema::create('employee_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('type');
                $table->text('description');
                $table->string('status')->default('pending');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                
                $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // إنشاء جدول التعليقات
        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('commentable_type');
                $table->unsignedBigInteger('commentable_id');
                $table->text('content');
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['commentable_type', 'commentable_id']);
            });
        }

        // إنشاء جدول جهات الاتصال
        if (!Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('company')->nullable();
                $table->string('job_title')->nullable();
                $table->string('department')->nullable();
                $table->text('notes')->nullable();
                $table->string('profile_photo')->nullable();
                $table->timestamps();
            });
        }

        // إنشاء جدول تفاعلات جهات الاتصال
        if (!Schema::hasTable('contact_interactions')) {
            Schema::create('contact_interactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contact_id');
                $table->unsignedBigInteger('user_id');
                $table->string('type');
                $table->text('description');
                $table->timestamp('interaction_date');
                $table->timestamps();
                
                $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول فئات جهات الاتصال
        if (!Schema::hasTable('contact_categories')) {
            Schema::create('contact_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // إنشاء جدول الفروع
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->text('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->timestamps();
            });
        }

        // إنشاء جدول أنواع الهواتف
        if (!Schema::hasTable('phone_types')) {
            Schema::create('phone_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->timestamps();
            });
        }

        // إنشاء جدول فئات الأصول
        if (!Schema::hasTable('asset_categories')) {
            Schema::create('asset_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->text('description')->nullable();
                $table->text('description_ar')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->timestamps();
            });
        }

        // إنشاء جدول مواقع الأصول
        if (!Schema::hasTable('asset_locations')) {
            Schema::create('asset_locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->text('description')->nullable();
                $table->text('description_ar')->nullable();
                $table->timestamps();
            });
        }

        // إنشاء جدول الأصول
        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->string('serial_number')->unique();
                $table->string('model')->nullable();
                $table->string('brand')->nullable();
                $table->text('description')->nullable();
                $table->text('description_ar')->nullable();
                $table->decimal('purchase_price', 10, 2)->nullable();
                $table->date('purchase_date')->nullable();
                $table->date('warranty_expiry')->nullable();
                $table->string('status')->default('active');
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('location_id')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->timestamps();
                
                $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('set null');
                $table->foreign('location_id')->references('id')->on('asset_locations')->onDelete('set null');
                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            });
        }

        // إنشاء جدول تخصيص الأصول
        if (!Schema::hasTable('asset_assignments')) {
            Schema::create('asset_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('asset_id');
                $table->unsignedBigInteger('assigned_to');
                $table->unsignedBigInteger('assigned_by');
                $table->date('assigned_date');
                $table->date('return_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول سجلات الأصول
        if (!Schema::hasTable('asset_logs')) {
            Schema::create('asset_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('asset_id');
                $table->unsignedBigInteger('user_id');
                $table->string('action');
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول خصائص فئات الأصول
        if (!Schema::hasTable('asset_category_properties')) {
            Schema::create('asset_category_properties', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->string('type');
                $table->boolean('is_required')->default(false);
                $table->timestamps();
                
                $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('cascade');
            });
        }

        // إنشاء جدول قيم خصائص الأصول
        if (!Schema::hasTable('asset_property_values')) {
            Schema::create('asset_property_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('asset_id');
                $table->unsignedBigInteger('property_id');
                $table->text('value');
                $table->timestamps();
                
                $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
                $table->foreign('property_id')->references('id')->on('asset_category_properties')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف الجداول بالترتيب العكسي
        Schema::dropIfExists('asset_property_values');
        Schema::dropIfExists('asset_category_properties');
        Schema::dropIfExists('asset_logs');
        Schema::dropIfExists('asset_assignments');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_locations');
        Schema::dropIfExists('asset_categories');
        Schema::dropIfExists('phone_types');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('contact_categories');
        Schema::dropIfExists('contact_interactions');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('employee_requests');
        Schema::dropIfExists('tasks');
    }
};