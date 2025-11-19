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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('mobile')->nullable()->after('phone');
            $table->string('website')->nullable()->after('email');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->string('contact_person')->nullable()->after('country');
            $table->string('contact_phone')->nullable()->after('contact_person');
            $table->string('contact_email')->nullable()->after('contact_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'mobile',
                'website',
                'city',
                'country',
                'contact_person',
                'contact_phone',
                'contact_email'
            ]);
        });
    }
};