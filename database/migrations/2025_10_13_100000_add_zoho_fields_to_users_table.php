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
            $table->string('zoho_agent_name')->nullable()->index()
                  ->comment('اسم الموظف كما يظهر في cf_closed_by')
                  ->after('is_archived');
            
            $table->string('zoho_agent_id')->nullable()
                  ->comment('معرف Agent في Zoho Desk')
                  ->after('zoho_agent_name');
            
            $table->string('zoho_email')->nullable()
                  ->comment('البريد الإلكتروني في Zoho')
                  ->after('zoho_agent_id');
            
            $table->boolean('is_zoho_enabled')->default(false)->index()
                  ->comment('هل الموظف مفعّل على نظام Zoho؟')
                  ->after('zoho_email');
            
            $table->timestamp('zoho_linked_at')->nullable()
                  ->comment('تاريخ الربط مع Zoho')
                  ->after('is_zoho_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'zoho_agent_name',
                'zoho_agent_id',
                'zoho_email',
                'is_zoho_enabled',
                'zoho_linked_at',
            ]);
        });
    }
};

