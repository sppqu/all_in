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
        Schema::create('pelanggaran_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100); // Ringan, Sedang, Berat
            $table->string('kode', 10)->unique(); // R, S, B
            $table->text('keterangan')->nullable();
            $table->string('warna', 20)->default('#6c757d'); // untuk badge color
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_kategori');
    }
};
