<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rename tabel jika belum
        if (Schema::hasTable('school_profiles') && !Schema::hasTable('schools')) {
            Schema::rename('school_profiles', 'schools');
        }
        
        // Tambah kolom baru
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (!Schema::hasColumn('schools', 'foundation_id')) {
                    $table->foreignId('foundation_id')->nullable()->after('id')->constrained('foundations')->onDelete('cascade');
                }
                if (!Schema::hasColumn('schools', 'status')) {
                    $table->enum('status', ['active', 'inactive'])->default('active')->after('logo_sekolah');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (Schema::hasColumn('schools', 'foundation_id')) {
                    $table->dropForeign(['foundation_id']);
                    $table->dropColumn('foundation_id');
                }
                if (Schema::hasColumn('schools', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};





