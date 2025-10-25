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
        Schema::table('spmb_registrations', function (Blueprint $table) {
            $table->string('nomor_pendaftaran')->unique()->nullable();
            $table->foreignId('kejuruan_id')->nullable()->constrained('s_p_m_b_kejuruans')->onDelete('set null');
            $table->enum('status_pendaftaran', ['pending', 'diterima', 'ditolak'])->default('pending');
            $table->text('catatan_admin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_registrations', function (Blueprint $table) {
            // Drop foreign key first before dropping the column
            $table->dropForeign(['kejuruan_id']);
            $table->dropColumn(['nomor_pendaftaran', 'kejuruan_id', 'status_pendaftaran', 'catatan_admin']);
        });
    }
};
