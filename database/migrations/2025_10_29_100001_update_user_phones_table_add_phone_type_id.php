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
        if (Schema::hasTable('user_phones')) {
            // Add phone_type_id column if it doesn't exist
            if (!Schema::hasColumn('user_phones', 'phone_type_id')) {
                Schema::table('user_phones', function (Blueprint $table) {
                    $table->unsignedBigInteger('phone_type_id')->nullable()->after('user_id');
                    
                    // Add foreign key constraint if phone_types table exists
                    if (Schema::hasTable('phone_types')) {
                        $table->foreign('phone_type_id')->references('id')->on('phone_types')->onDelete('set null');
                    }
                });
                
                // Migrate existing data: convert 'type' string to phone_type_id
                if (Schema::hasColumn('user_phones', 'type')) {
                    // Get or create phone types
                    $workType = DB::table('phone_types')->where('slug', 'work')->first();
                    if (!$workType) {
                        $workTypeId = DB::table('phone_types')->insertGetId([
                            'name' => 'Work',
                            'name_ar' => 'عمل',
                            'slug' => 'work',
                            'is_active' => true,
                            'sort_order' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $workType = (object)['id' => $workTypeId];
                    }
                    
                    // Update existing records
                    DB::table('user_phones')
                        ->where('type', 'work')
                        ->orWhereNull('type')
                        ->update(['phone_type_id' => $workType->id]);
                    
                    // Create other phone types and migrate
                    $personalType = DB::table('phone_types')->where('slug', 'personal')->first();
                    if (!$personalType && DB::table('user_phones')->where('type', 'personal')->exists()) {
                        $personalTypeId = DB::table('phone_types')->insertGetId([
                            'name' => 'Personal',
                            'name_ar' => 'شخصي',
                            'slug' => 'personal',
                            'is_active' => true,
                            'sort_order' => 2,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        DB::table('user_phones')
                            ->where('type', 'personal')
                            ->update(['phone_type_id' => $personalTypeId]);
                    }
                    
                    $mobileType = DB::table('phone_types')->where('slug', 'mobile')->first();
                    if (!$mobileType && DB::table('user_phones')->where('type', 'mobile')->exists()) {
                        $mobileTypeId = DB::table('phone_types')->insertGetId([
                            'name' => 'Mobile',
                            'name_ar' => 'محمول',
                            'slug' => 'mobile',
                            'is_active' => true,
                            'sort_order' => 3,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        DB::table('user_phones')
                            ->where('type', 'mobile')
                            ->update(['phone_type_id' => $mobileTypeId]);
                    }
                }
            }
            
            // Add other missing columns that might be needed
            if (!Schema::hasColumn('user_phones', 'country_code')) {
                Schema::table('user_phones', function (Blueprint $table) {
                    $table->string('country_code')->nullable()->after('phone_type_id');
                });
            }
            
            if (!Schema::hasColumn('user_phones', 'is_verified')) {
                Schema::table('user_phones', function (Blueprint $table) {
                    $table->boolean('is_verified')->default(false)->after('is_primary');
                });
            }
            
            if (!Schema::hasColumn('user_phones', 'notes')) {
                Schema::table('user_phones', function (Blueprint $table) {
                    $table->text('notes')->nullable()->after('is_verified');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_phones')) {
            Schema::table('user_phones', function (Blueprint $table) {
                if (Schema::hasColumn('user_phones', 'notes')) {
                    $table->dropColumn('notes');
                }
                if (Schema::hasColumn('user_phones', 'is_verified')) {
                    $table->dropColumn('is_verified');
                }
                if (Schema::hasColumn('user_phones', 'country_code')) {
                    $table->dropColumn('country_code');
                }
                if (Schema::hasColumn('user_phones', 'phone_type_id')) {
                    $table->dropForeign(['phone_type_id']);
                    $table->dropColumn('phone_type_id');
                }
            });
        }
    }
};

