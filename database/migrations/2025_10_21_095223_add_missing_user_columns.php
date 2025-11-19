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
        Schema::table('users', function (Blueprint $table) {
            // إضافة الأعمدة المفقودة
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type')->default('employee')->after('last_name');
            }
            
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('last_name');
            }
            
            if (!Schema::hasColumn('users', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('user_type');
            }
            
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('department_id');
            }
            
            if (!Schema::hasColumn('users', 'manager_id')) {
                $table->unsignedBigInteger('manager_id')->nullable()->after('role_id');
            }
            
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()->after('manager_id');
            }
            
            if (!Schema::hasColumn('users', 'work_email')) {
                $table->string('work_email')->nullable()->after('job_title');
            }
            
            if (!Schema::hasColumn('users', 'phone_work')) {
                $table->string('phone_work')->nullable()->after('work_email');
            }
            
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone_work');
            }
            
            if (!Schema::hasColumn('users', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('address');
            }
            
            if (!Schema::hasColumn('users', 'birthday')) {
                $table->date('birthday')->nullable()->after('hire_date');
            }
            
            if (!Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('birthday');
            }
            
            if (!Schema::hasColumn('users', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('birth_date');
            }
            
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_archived');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [
                'is_active', 'is_archived', 'birth_date', 'birthday', 'hire_date',
                'address', 'phone_work', 'work_email', 'job_title', 'manager_id',
                'role_id', 'department_id', 'name_ar', 'name', 'user_type'
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};