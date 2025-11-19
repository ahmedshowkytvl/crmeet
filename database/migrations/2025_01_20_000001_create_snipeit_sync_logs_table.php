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
        Schema::create('snipeit_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // assets, users, categories, etc.
            $table->string('sync_type'); // full, incremental
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('synced_count')->default(0);
            $table->integer('created_count')->default(0);
            $table->integer('updated_count')->default(0);
            $table->json('errors')->nullable(); // Array of errors
            $table->integer('duration')->nullable(); // Duration in seconds
            $table->timestamps();

            // Add indexes
            $table->index(['type', 'status']);
            $table->index('started_at');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snipeit_sync_logs');
    }
};
