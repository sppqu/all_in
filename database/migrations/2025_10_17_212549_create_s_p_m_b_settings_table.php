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
        Schema::create('s_p_m_b_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_pelajaran')->unique();
            $table->boolean('pendaftaran_dibuka')->default(false);
            $table->date('tanggal_buka')->nullable();
            $table->date('tanggal_tutup')->nullable();
            $table->decimal('biaya_pendaftaran', 15, 2)->default(50000);
            $table->decimal('biaya_spmb', 15, 2)->default(100000);
            $table->text('deskripsi')->nullable();
            $table->json('pengaturan_tambahan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_p_m_b_settings');
    }
};
