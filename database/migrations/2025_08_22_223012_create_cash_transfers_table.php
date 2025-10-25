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
        Schema::create('cash_transfers', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_transfer');
            $table->string('no_transaksi', 50)->unique();
            $table->unsignedBigInteger('kas_asal_id')->nullable();
            $table->unsignedBigInteger('kas_tujuan_id')->nullable();
            $table->decimal('jumlah_transfer', 15, 2);
            $table->text('keterangan')->nullable();
            $table->string('nama_penyetor', 100)->nullable();
            $table->string('nama_penerima', 100)->nullable();
            $table->foreignId('petugas_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            // Indexes
            $table->index('tanggal_transfer');
            $table->index('kas_asal_id');
            $table->index('kas_tujuan_id');
            $table->index('petugas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transfers');
    }
};
