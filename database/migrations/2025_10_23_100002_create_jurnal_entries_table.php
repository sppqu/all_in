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
        Schema::create('jurnal_entries', function (Blueprint $table) {
            $table->id('entry_id');
            $table->foreignId('jurnal_id')->constrained('jurnal_harian', 'jurnal_id')->onDelete('cascade');
            $table->foreignId('kategori_id')->constrained('jurnal_kategori', 'kategori_id')->onDelete('cascade');
            $table->text('kegiatan'); // Deskripsi kegiatan
            $table->integer('nilai')->default(0); // Skala 1-5 atau 1-10
            $table->text('keterangan')->nullable();
            $table->string('foto')->nullable(); // Optional foto dokumentasi
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->timestamps();

            // Index
            $table->index(['jurnal_id', 'kategori_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_entries');
    }
};

