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
        if (!Schema::hasTable('employee_emails')) {
            Schema::create('employee_emails', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('email_address'); // Match the model fillable
                $table->string('email_type')->default('work'); // work, personal, other
                $table->boolean('is_primary')->default(false);
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('employee_id');
                $table->index(['employee_id', 'is_active']);
                $table->index(['employee_id', 'is_primary']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_emails');
    }
};


