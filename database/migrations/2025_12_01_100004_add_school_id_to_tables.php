<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'students' => 'student_id',
            'users' => 'id',
            'class_models' => 'class_id',
            'periods' => 'period_id',
        ];

        foreach ($tables as $table => $primaryKey) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'school_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->foreignId('school_id')->nullable()->after($table === 'users' ? 'id' : Schema::getColumnListing($table)[0])
                        ->constrained('schools')->onDelete('cascade');
                });
            }
        }

        // Migrasi data existing
        $defaultSchool = DB::table('schools')->orderBy('id')->first();
        if ($defaultSchool) {
            foreach ($tables as $table => $primaryKey) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')) {
                    DB::table($table)
                        ->whereNull('school_id')
                        ->update(['school_id' => $defaultSchool->id]);
                }
            }
            
            // Set NOT NULL setelah data di-update
            foreach ($tables as $table => $primaryKey) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')) {
                    try {
                        Schema::table($table, function (Blueprint $t) {
                            $t->foreignId('school_id')->nullable(false)->change();
                        });
                    } catch (\Exception $e) {
                        // Skip jika error
                    }
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





