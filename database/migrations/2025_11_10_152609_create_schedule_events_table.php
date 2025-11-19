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
        Schema::create('schedule_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('timezone')->default('UTC');
            $table->string('location')->nullable();
            $table->enum('event_type', ['meeting', 'event', 'reminder', 'task'])->default('event');
            $table->enum('status', ['scheduled', 'confirmed', 'cancelled', 'completed'])->default('scheduled');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            
            // Recurring Events
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_pattern', ['daily', 'weekly', 'monthly', 'yearly', 'custom'])->nullable();
            $table->json('recurring_rules')->nullable(); // مثل: interval, days_of_week, end_date
            $table->dateTime('recurring_end_date')->nullable();
            $table->unsignedBigInteger('parent_event_id')->nullable();
            $table->foreign('parent_event_id')->references('id')->on('schedule_events')->onDelete('cascade');
            
            // Meeting Room
            $table->unsignedBigInteger('meeting_room_id')->nullable();
            $table->foreign('meeting_room_id')->references('id')->on('meeting_rooms')->onDelete('set null');
            
            // Event Creator/Owner
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Color for calendar display
            $table->string('color', 7)->default('#3788d8');
            
            // Reminders
            $table->json('reminders')->nullable(); // مثل: [{'type': 'email', 'minutes': 15}]
            
            // Google Calendar / Outlook sync
            $table->string('external_calendar_id')->nullable();
            $table->string('external_calendar_type')->nullable(); // google, outlook
            $table->timestamp('last_synced_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['user_id', 'start_time']);
            $table->index(['meeting_room_id', 'start_time', 'end_time']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_events');
    }
};
