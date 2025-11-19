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
        Schema::create('user_zoho_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('period_type', ['daily', 'weekly', 'monthly']);
            $table->date('period_date');
            $table->integer('tickets_closed_count')->default(0);
            $table->decimal('avg_response_time_minutes', 8, 2)->nullable();
            $table->decimal('tickets_per_hour', 8, 2)->nullable();
            $table->integer('total_threads_count')->default(0);
            $table->decimal('performance_score', 5, 2)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period_type', 'period_date']);
            $table->index(['period_type', 'period_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_zoho_stats');
    }
};

