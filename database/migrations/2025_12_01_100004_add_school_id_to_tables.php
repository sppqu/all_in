<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix invalid date values in students table before adding foreign key
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'student_born_date')) {
            try {
                // Fix '0000-00-00' dates to NULL
                DB::statement("UPDATE `students` SET `student_born_date` = NULL WHERE `student_born_date` = '0000-00-00' OR `student_born_date` = '0000-00-00 00:00:00'");
            } catch (\Exception $e) {
                // Continue if error (column might not exist or already fixed)
            }
        }

        $tables = [
            'students' => 'student_id',
            'users' => 'id',
            'class_models' => 'class_id',
            'periods' => 'period_id',
        ];

        // Step 1: Add school_id column without foreign key first
        foreach ($tables as $table => $primaryKey) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'school_id')) {
                try {
                    Schema::table($table, function (Blueprint $t) use ($table) {
                        $columnPosition = $table === 'users' ? 'id' : Schema::getColumnListing($table)[0];
                        $t->unsignedBigInteger('school_id')->nullable()->after($columnPosition);
                    });
                } catch (\Exception $e) {
                    // Skip if error
                    continue;
                }
            }
        }

        // Step 2: Migrate existing data
        $defaultSchool = DB::table('schools')->orderBy('id')->first();
        if ($defaultSchool) {
            foreach ($tables as $table => $primaryKey) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')) {
                    try {
                        DB::table($table)
                            ->whereNull('school_id')
                            ->update(['school_id' => $defaultSchool->id]);
                    } catch (\Exception $e) {
                        // Skip if error
                        continue;
                    }
                }
            }
        }

        // Step 3: Add foreign key constraints (only if schools table exists)
        if (Schema::hasTable('schools')) {
            foreach ($tables as $table => $primaryKey) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')) {
                    try {
                        // Check if foreign key already exists
                        $foreignKeys = DB::select("
                            SELECT CONSTRAINT_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_SCHEMA = DATABASE() 
                            AND TABLE_NAME = '{$table}' 
                            AND COLUMN_NAME = 'school_id' 
                            AND REFERENCED_TABLE_NAME IS NOT NULL
                        ");
                        
                        if (empty($foreignKeys)) {
                            // Try to convert table to InnoDB if needed
                            try {
                                DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
                            } catch (\Exception $e) {
                                // Continue if engine conversion fails
                            }

                            // Add foreign key constraint
                            DB::statement("
                                ALTER TABLE `{$table}` 
                                ADD CONSTRAINT `{$table}_school_id_foreign` 
                                FOREIGN KEY (`school_id`) 
                                REFERENCES `schools` (`id`) 
                                ON DELETE CASCADE
                            ");
                        }
                    } catch (\Exception $e) {
                        // Skip if foreign key creation fails
                        continue;
                    }
                }
            }
        }

        // Step 4: Set NOT NULL after data is updated (optional, only if all records have school_id)
        foreach ($tables as $table => $primaryKey) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')) {
                try {
                    // Check if all records have school_id
                    $nullCount = DB::table($table)->whereNull('school_id')->count();
                    if ($nullCount === 0) {
                        Schema::table($table, function (Blueprint $t) {
                            $t->unsignedBigInteger('school_id')->nullable(false)->change();
                        });
                    }
                } catch (\Exception $e) {
                    // Skip if error
                    continue;
                }
            }
        }
    }

    public function down(): void
    {
        $tables = ['students', 'users', 'class_models', 'periods'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropForeign(['school_id']);
                    $t->dropColumn('school_id');
                });
            }
        }
    }
};





