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
        Schema::create('zoho_department_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('zoho_department_id')->unique();
            $table->string('zoho_department_name');
            $table->unsignedBigInteger('local_department_id');
            $table->string('local_department_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('local_department_id')->references('id')->on('departments')->onDelete('cascade');
            
            // Indexes
            $table->index('zoho_department_id');
            $table->index('local_department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_department_mappings');
    }
};
