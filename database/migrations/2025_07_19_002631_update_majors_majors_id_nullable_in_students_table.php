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
        // Ubah kolom menjadi nullable menggunakan raw SQL
        DB::statement('ALTER TABLE students MODIFY majors_majors_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ubah kembali menjadi not nullable
        DB::statement('ALTER TABLE students MODIFY majors_majors_id BIGINT UNSIGNED NOT NULL');
    }
};
