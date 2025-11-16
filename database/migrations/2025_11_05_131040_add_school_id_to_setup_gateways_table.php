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
        // Step 1: Add school_id column first (without foreign key constraint)
        if (!Schema::hasColumn('setup_gateways', 'school_id')) {
            Schema::table('setup_gateways', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('setup_id');
            });
        }

        // Step 2: Migrate existing data to first school (if schools table exists)
        if (Schema::hasTable('schools')) {
            try {
                $firstSchool = DB::table('schools')->orderBy('id')->first();
                if ($firstSchool) {
                    DB::table('setup_gateways')
                        ->whereNull('school_id')
                        ->update(['school_id' => $firstSchool->id]);
                }
            } catch (\Exception $e) {
                // Continue if migration fails
            }
        }

        // Step 3: Add foreign key constraint only if schools table exists and column is ready
        if (Schema::hasTable('schools') && Schema::hasColumn('setup_gateways', 'school_id')) {
            try {
                // Ensure both tables use InnoDB engine for foreign key support
                try {
                    DB::statement("ALTER TABLE setup_gateways ENGINE=InnoDB");
                } catch (\Exception $e) {
                    // Continue if engine change fails
                }
                
                try {
                    DB::statement("ALTER TABLE schools ENGINE=InnoDB");
                } catch (\Exception $e) {
                    // Continue if engine change fails
                }
                
                // Try to add foreign key constraint
                // First check if it already exists by trying to drop it (will fail if doesn't exist)
                try {
                    Schema::table('setup_gateways', function (Blueprint $table) {
                        $table->dropForeign(['school_id']);
                    });
                    // If drop succeeds, foreign key existed, so we'll add it back
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, which is fine
                }
                
                // Add foreign key constraint
                Schema::table('setup_gateways', function (Blueprint $table) {
                    $table->foreign('school_id')
                        ->references('id')
                        ->on('schools')
                        ->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // If foreign key creation fails, continue without it
                // The column will be added but without constraint
                // This can happen if schools table structure doesn't match or engine is MyISAM
            }
        }

        // Step 4: Make school_id NOT NULL after migration (if there are schools and no null values)
        if (Schema::hasTable('schools') && Schema::hasColumn('setup_gateways', 'school_id')) {
            try {
                $nullCount = DB::table('setup_gateways')->whereNull('school_id')->count();
                if ($nullCount === 0) {
                    Schema::table('setup_gateways', function (Blueprint $table) {
                        $table->unsignedBigInteger('school_id')->nullable(false)->change();
                    });
                }
            } catch (\Exception $e) {
                // If change fails, continue with nullable
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setup_gateways', function (Blueprint $table) {
            if (Schema::hasColumn('setup_gateways', 'school_id')) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            }
        });
    }
};
