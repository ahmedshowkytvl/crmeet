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
        Schema::create('meeting_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->integer('capacity');
            $table->string('location');
            $table->string('location_ar')->nullable();
            $table->json('amenities')->nullable(); // مثل: projector, whiteboard, video_conference
            $table->boolean('is_available')->default(true);
            $table->json('availability_schedule')->nullable(); // جدول الأوقات المتاحة (أيام وأوقات)
            $table->string('image')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_rooms');
    }
};
