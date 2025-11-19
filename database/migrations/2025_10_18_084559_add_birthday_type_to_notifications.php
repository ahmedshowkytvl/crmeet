<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'birthday' to the enum type in notifications table
        DB::statement("ALTER TABLE notifications ALTER COLUMN type TYPE VARCHAR(50)");
        DB::statement("ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check");
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check CHECK (type IN ('message', 'task', 'asset', 'birthday'))");
        
        // Also update notification_preferences table
        DB::statement("ALTER TABLE notification_preferences ALTER COLUMN type TYPE VARCHAR(50)");
        DB::statement("ALTER TABLE notification_preferences DROP CONSTRAINT IF EXISTS notification_preferences_type_check");
        DB::statement("ALTER TABLE notification_preferences ADD CONSTRAINT notification_preferences_type_check CHECK (type IN ('message', 'task', 'asset', 'birthday'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'birthday' from the enum type
        DB::statement("ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check");
        DB::statement("ALTER TABLE notifications ALTER COLUMN type TYPE VARCHAR(50)");
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check CHECK (type IN ('message', 'task', 'asset'))");
        
        DB::statement("ALTER TABLE notification_preferences DROP CONSTRAINT IF EXISTS notification_preferences_type_check");
        DB::statement("ALTER TABLE notification_preferences ALTER COLUMN type TYPE VARCHAR(50)");
        DB::statement("ALTER TABLE notification_preferences ADD CONSTRAINT notification_preferences_type_check CHECK (type IN ('message', 'task', 'asset'))");
    }
};