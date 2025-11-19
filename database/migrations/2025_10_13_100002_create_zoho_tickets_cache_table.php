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
        Schema::create('zoho_tickets_cache', function (Blueprint $table) {
            $table->id();
            $table->string('zoho_ticket_id')->unique();
            $table->string('ticket_number')->index();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('closed_by_name')->nullable()->index();
            $table->text('subject')->nullable();
            $table->string('status', 50);
            $table->string('department_id')->nullable();
            $table->timestamp('created_at_zoho');
            $table->timestamp('closed_at_zoho')->nullable();
            $table->integer('response_time_minutes')->nullable();
            $table->integer('thread_count')->default(0);
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at_zoho');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_tickets_cache');
    }
};

