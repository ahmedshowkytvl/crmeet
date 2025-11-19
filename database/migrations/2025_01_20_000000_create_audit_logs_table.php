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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('action_type'); // create, update, delete, login, logout, view, etc.
            $table->string('module'); // employees, events, announcements, tasks, etc.
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('record_name')->nullable();
            $table->json('details')->nullable(); // store old/new values or description
            $table->string('ip_address')->nullable();
            $table->string('device_info')->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->timestamp('created_at');
            
            // Indexes for better performance
            $table->index(['user_id', 'created_at']);
            $table->index(['action_type', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('created_at');
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};


