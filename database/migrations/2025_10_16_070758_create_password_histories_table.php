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
        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('password_accounts')->onDelete('cascade');
            $table->text('old_password');
            $table->text('new_password');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->string('change_reason')->nullable();
            $table->string('change_reason_ar')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['account_id', 'changed_at']);
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_histories');
    }
};
