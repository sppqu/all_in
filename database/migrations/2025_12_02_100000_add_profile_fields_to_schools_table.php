<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (!Schema::hasColumn('schools', 'kepala_sekolah')) {
                    $table->string('kepala_sekolah', 255)->nullable()->after('nama_sekolah');
                }
                if (!Schema::hasColumn('schools', 'npsn')) {
                    $table->string('npsn', 20)->nullable()->after('kepala_sekolah');
                }
                if (!Schema::hasColumn('schools', 'email')) {
                    $table->string('email', 255)->nullable()->after('npsn');
                }
                if (!Schema::hasColumn('schools', 'alamat_baris_1')) {
                    $table->text('alamat_baris_1')->nullable()->after('alamat');
                }
                if (!Schema::hasColumn('schools', 'alamat_baris_2')) {
                    $table->string('alamat_baris_2', 255)->nullable()->after('alamat_baris_1');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (Schema::hasColumn('schools', 'kepala_sekolah')) {
                    $table->dropColumn('kepala_sekolah');
                }
                if (Schema::hasColumn('schools', 'npsn')) {
                    $table->dropColumn('npsn');
                }
                if (Schema::hasColumn('schools', 'email')) {
                    $table->dropColumn('email');
                }
                if (Schema::hasColumn('schools', 'alamat_baris_1')) {
                    $table->dropColumn('alamat_baris_1');
                }
                if (Schema::hasColumn('schools', 'alamat_baris_2')) {
                    $table->dropColumn('alamat_baris_2');
                }
            });
        }
    }
};





