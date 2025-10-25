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
        Schema::create('spmb_additional_fees', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama biaya (contoh: Seragam Putra, Seragam Putri, Buku Paket)
            $table->string('code')->unique(); // Kode unik (contoh: SERAGAM_PUTRA, SERAGAM_PUTRI)
            $table->text('description')->nullable(); // Deskripsi biaya
            $table->enum('type', ['mandatory', 'optional', 'conditional'])->default('optional'); // Jenis biaya
            $table->enum('category', ['seragam', 'buku', 'alat_tulis', 'kegiatan', 'lainnya'])->default('lainnya'); // Kategori
            $table->decimal('amount', 15, 2)->default(0); // Jumlah biaya
            $table->json('conditions')->nullable(); // Kondisi khusus (contoh: gender, kelas, dll)
            $table->boolean('is_active')->default(true); // Status aktif
            $table->integer('sort_order')->default(0); // Urutan tampilan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_additional_fees');
    }
};
