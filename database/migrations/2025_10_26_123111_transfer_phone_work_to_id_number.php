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
        // Transfer phone_work to id_number for all users where id_number is empty
        DB::table('users')
            ->whereNull('id_number')
            ->orWhere('id_number', '')
            ->whereNotNull('phone_work')
            ->where('phone_work', '!=', '')
            ->update(['id_number' => DB::raw('phone_work')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration transfers data, so rollback is not needed
        // If you want to reverse, you would need to transfer back from id_number to phone_work
    }
};
