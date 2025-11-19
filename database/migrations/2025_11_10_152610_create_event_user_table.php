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
        Schema::create('event_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['attendee', 'organizer', 'optional'])->default('attendee');
            $table->enum('rsvp_status', ['pending', 'accepted', 'declined', 'tentative'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->text('response_note')->nullable();
            $table->timestamps();
            
            $table->foreign('event_id')->references('id')->on('schedule_events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Ensure a user can only be invited once per event
            $table->unique(['event_id', 'user_id']);
            
            // Indexes
            $table->index('user_id');
            $table->index('rsvp_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_user');
    }
};
