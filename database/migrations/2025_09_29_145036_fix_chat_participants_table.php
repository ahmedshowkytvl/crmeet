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
        Schema::table('chat_participants', function (Blueprint $table) {
            // إعادة تسمية العمود من room_id إلى chat_room_id
            $table->renameColumn('room_id', 'chat_room_id');
            
            // إضافة الأعمدة المفقودة
            $table->string('role')->default('member')->after('user_id');
            $table->timestamp('last_read_at')->nullable()->after('joined_at');
            $table->boolean('is_muted')->default(false)->after('last_read_at');
            $table->boolean('is_archived')->default(false)->after('is_muted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_participants', function (Blueprint $table) {
            // إعادة تسمية العمود من chat_room_id إلى room_id
            $table->renameColumn('chat_room_id', 'room_id');
            
            // حذف الأعمدة المضافة
            $table->dropColumn(['role', 'last_read_at', 'is_muted', 'is_archived']);
        });
    }
};
