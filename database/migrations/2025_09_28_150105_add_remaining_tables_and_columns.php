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
        // إضافة الأعمدة المفقودة لجدول roles
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (!Schema::hasColumn('roles', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }
            });
        }

        // إنشاء جدول user_phones
        if (!Schema::hasTable('user_phones')) {
            Schema::create('user_phones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('phone_number');
                $table->string('type')->default('work'); // work, personal, mobile, emergency
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول password_accounts
        if (!Schema::hasTable('password_accounts')) {
            Schema::create('password_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('username');
                $table->text('password');
                $table->string('url')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول password_assignments
        if (!Schema::hasTable('password_assignments')) {
            Schema::create('password_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('account_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
                
                $table->foreign('account_id')->references('id')->on('password_accounts')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول password_audit_logs
        if (!Schema::hasTable('password_audit_logs')) {
            Schema::create('password_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('account_id');
                $table->unsignedBigInteger('user_id');
                $table->string('action');
                $table->text('details')->nullable();
                $table->timestamps();
                
                $table->foreign('account_id')->references('id')->on('password_accounts')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول password_history
        if (!Schema::hasTable('password_history')) {
            Schema::create('password_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('account_id');
                $table->text('password');
                $table->timestamp('changed_at');
                $table->unsignedBigInteger('changed_by');
                $table->timestamps();
                
                $table->foreign('account_id')->references('id')->on('password_accounts')->onDelete('cascade');
                $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول suppliers
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_archived')->default(false);
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
            });
        }

        // إنشاء جدول employee_emails
        if (!Schema::hasTable('employee_emails')) {
            Schema::create('employee_emails', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('email');
                $table->string('type')->default('work');
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
                
                $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول warehouses
        if (!Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->text('description')->nullable();
                $table->text('description_ar')->nullable();
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }

        // إنشاء جدول warehouse_cabinets
        if (!Schema::hasTable('warehouse_cabinets')) {
            Schema::create('warehouse_cabinets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warehouse_id');
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->foreign('warehouse_id')->references('id')->on('warehouses');
            });
        }

        // إنشاء جدول warehouse_shelves
        if (!Schema::hasTable('warehouse_shelves')) {
            Schema::create('warehouse_shelves', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cabinet_id');
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->foreign('cabinet_id')->references('id')->on('warehouse_cabinets');
            });
        }

        // إنشاء جدول notifications
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('type');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول chat_rooms
        if (!Schema::hasTable('chat_rooms')) {
            Schema::create('chat_rooms', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('type')->default('group');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول chat_messages
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('room_id');
                $table->unsignedBigInteger('user_id');
                $table->text('message');
                $table->string('type')->default('text');
                $table->timestamps();
                
                $table->foreign('room_id')->references('id')->on('chat_rooms')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // إنشاء جدول chat_participants
        if (!Schema::hasTable('chat_participants')) {
            Schema::create('chat_participants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('room_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamp('joined_at');
                $table->timestamps();
                
                $table->foreign('room_id')->references('id')->on('chat_rooms')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف الجداول بالترتيب العكسي
        Schema::dropIfExists('chat_participants');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_rooms');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('warehouse_shelves');
        Schema::dropIfExists('warehouse_cabinets');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('employee_emails');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('password_history');
        Schema::dropIfExists('password_audit_logs');
        Schema::dropIfExists('password_assignments');
        Schema::dropIfExists('password_accounts');
        Schema::dropIfExists('user_phones');
        
        // إزالة الأعمدة المضافة
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('roles', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
            });
        }
    }
};