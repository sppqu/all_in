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
            // Drop foreign key constraint terlebih dahulu
            if (Schema::hasColumn('students', 'class_id')) {
                $table->dropForeign(['class_id']);
            }
            
            // Drop kolom lama jika ada
            $columnsToDrop = ['nis', 'name', 'gender'];
            if (Schema::hasColumn('students', 'class_id')) {
                $columnsToDrop[] = 'class_id';
            }
            $table->dropColumn($columnsToDrop);
            
            // Tambah kolom baru sesuai schema
            $table->string('student_nis', 45)->after('student_id');
            $table->unique('student_nis', 'students_nis_unique');
            $table->string('student_nisn', 45)->nullable()->after('student_nis');
            $table->string('student_password', 100)->nullable()->after('student_nisn');
            $table->string('student_full_name', 255)->after('student_password');
            $table->enum('student_gender', ['L', 'P'])->after('student_full_name');
            $table->string('student_born_place', 45)->nullable()->after('student_gender');
            $table->date('student_born_date')->nullable()->after('student_born_place');
            $table->string('student_img', 255)->nullable()->after('student_born_date');
            $table->string('student_phone', 45)->nullable()->after('student_img');
            $table->string('student_hobby', 100)->nullable()->after('student_phone');
            $table->text('student_address')->nullable()->after('student_hobby');
            $table->string('student_name_of_mother', 255)->nullable()->after('student_address');
            $table->string('student_name_of_father', 255)->nullable()->after('student_name_of_mother');
            $table->string('student_parent_phone', 45)->nullable()->after('student_name_of_father');
            $table->foreignId('class_class_id')->constrained('class_models', 'class_id')->onDelete('cascade')->after('student_parent_phone');
            $table->foreignId('majors_majors_id')->constrained('majors', 'majors_id')->onDelete('cascade')->after('class_class_id');
            $table->tinyInteger('student_status')->default(1)->after('majors_majors_id');
            $table->timestamp('student_input_date')->useCurrent()->after('student_status');
            $table->timestamp('student_last_update')->useCurrent()->useCurrentOnUpdate()->after('student_input_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['class_class_id', 'majors_majors_id']);
            $table->dropColumn([
                'student_nis', 'student_nisn', 'student_password', 'student_full_name',
                'student_gender', 'student_born_place', 'student_born_date', 'student_img',
                'student_phone', 'student_hobby', 'student_address', 'student_name_of_mother',
                'student_name_of_father', 'student_parent_phone', 'class_class_id',
                'majors_majors_id', 'student_status', 'student_input_date', 'student_last_update'
            ]);
            
            // Restore kolom lama
            $table->string('nis', 20)->unique();
            $table->string('name', 100);
            $table->enum('gender', ['L', 'P']);
            $table->foreignId('class_id')->constrained('class_models', 'class_id')->onDelete('cascade');
        });
    }
};
