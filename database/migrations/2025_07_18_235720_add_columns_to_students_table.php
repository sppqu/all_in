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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'nis')) {
                $table->string('nis', 20)->unique();
            }
            if (!Schema::hasColumn('students', 'name')) {
                $table->string('name', 100);
            }
            if (!Schema::hasColumn('students', 'gender')) {
                $table->enum('gender', ['L', 'P']);
            }
            if (!Schema::hasColumn('students', 'class_id')) {
                $table->foreignId('class_id')->constrained('class_models', 'class_id')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'class_id')) {
                $table->dropForeign(['class_id']);
            }
            $table->dropColumn(['nis', 'name', 'gender', 'class_id']);
        });
    }
};
