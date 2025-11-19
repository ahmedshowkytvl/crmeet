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
        Schema::table('departments', function (Blueprint $table) {
            // Drop the existing zoho_id column
            $table->dropColumn('zoho_id');
        });

        Schema::table('departments', function (Blueprint $table) {
            // Add the new zoho_id column as JSON
            $table->json('zoho_id')
                  ->nullable()
                  ->after('extension')
                  ->comment('Zoho Department IDs - تُملأ بواسطة Web Developer أو Senior في Internet Team');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Drop the JSON zoho_id column
            $table->dropColumn('zoho_id');
        });

        Schema::table('departments', function (Blueprint $table) {
            // Re-add the zoho_id column as string (original type)
            $table->string('zoho_id')
                  ->nullable()
                  ->after('extension')
                  ->comment('Zoho Department ID - يُملأ بواسطة Web Developer أو Senior في Internet Team');
        });
    }
};
