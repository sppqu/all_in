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
        Schema::create('spmb_waves', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama gelombang (contoh: Gelombang 1, Gelombang 2)
            $table->text('description')->nullable(); // Deskripsi gelombang
            $table->date('start_date'); // Tanggal mulai pendaftaran
            $table->date('end_date'); // Tanggal berakhir pendaftaran
            $table->decimal('registration_fee', 15, 2)->default(0); // Biaya pendaftaran
            $table->decimal('spmb_fee', 15, 2)->default(0); // Biaya SPMB
            $table->boolean('is_active')->default(true); // Status aktif gelombang
            $table->integer('quota')->nullable(); // Kuota pendaftaran (optional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_waves');
    }
};
