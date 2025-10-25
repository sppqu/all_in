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
        Schema::create('transaksi_pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi', 50)->unique();
            $table->date('tanggal_pengeluaran');
            $table->string('tahun_ajaran', 20);
            $table->string('dibayar_ke', 100);
            $table->unsignedBigInteger('metode_pembayaran_id');
            $table->text('keterangan_transaksi')->nullable();
            $table->string('operator', 100);
            $table->decimal('total_pengeluaran', 15, 2);
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('metode_pembayaran_id')->references('id')->on('payment_methods')->onDelete('restrict');
            
            // Indexes
            $table->index('no_transaksi');
            $table->index('tanggal_pengeluaran');
            $table->index('tahun_ajaran');
            $table->index('status');
        });
        
        // Tabel untuk detail item transaksi
        Schema::create('transaksi_pengeluaran_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksi_id');
            $table->unsignedBigInteger('pos_sumber_dana_id'); // Dari pos penerimaan
            $table->unsignedBigInteger('pos_pengeluaran_id'); // Ke pos pengeluaran
            $table->text('keterangan_item');
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('transaksi_id')->references('id')->on('transaksi_pengeluaran')->onDelete('cascade');
            $table->foreign('pos_sumber_dana_id')->references('pos_id')->on('pos_pembayaran')->onDelete('restrict');
            $table->foreign('pos_pengeluaran_id')->references('pos_id')->on('pos_pengeluaran')->onDelete('restrict');
            
            // Indexes
            $table->index('transaksi_id');
            $table->index('pos_sumber_dana_id');
            $table->index('pos_pengeluaran_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_pengeluaran_detail');
        Schema::dropIfExists('transaksi_pengeluaran');
    }
};
