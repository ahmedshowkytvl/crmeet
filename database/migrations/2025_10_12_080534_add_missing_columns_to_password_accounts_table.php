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
        Schema::table('password_accounts', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->text('notes_ar')->nullable()->after('notes');
            $table->boolean('requires_2fa')->default(false)->after('notes_ar');
            $table->timestamp('expires_at')->nullable()->after('requires_2fa');
            $table->boolean('is_shared')->default(false)->after('expires_at');
            $table->string('category')->nullable()->after('is_shared');
            $table->string('category_ar')->nullable()->after('category');
            $table->string('icon')->nullable()->after('category_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'name_ar',
                'notes_ar',
                'requires_2fa',
                'expires_at',
                'is_shared',
                'category',
                'category_ar',
                'icon'
            ]);
        });
    }
};
