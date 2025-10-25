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
        Schema::create('transaksi_penerimaan', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi', 50)->unique();
            $table->date('tanggal_penerimaan');
            $table->string('tahun_ajaran', 20);
            $table->string('diterima_dari', 100);
            $table->unsignedBigInteger('metode_pembayaran_id');
            $table->text('keterangan_transaksi')->nullable();
            $table->string('operator', 100);
            $table->decimal('total_penerimaan', 15, 2);
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('metode_pembayaran_id')->references('id')->on('payment_methods')->onDelete('restrict');
            
            // Indexes
            $table->index('no_transaksi');
            $table->index('tanggal_penerimaan');
            $table->index('tahun_ajaran');
            $table->index('status');
        });
        
        // Tabel untuk detail item transaksi
        Schema::create('transaksi_penerimaan_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksi_id');
            $table->unsignedBigInteger('pos_penerimaan_id');
            $table->text('keterangan_item');
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('transaksi_id')->references('id')->on('transaksi_penerimaan')->onDelete('cascade');
            $table->foreign('pos_penerimaan_id')->references('pos_id')->on('pos_pembayaran')->onDelete('restrict');
            
            // Indexes
            $table->index('transaksi_id');
            $table->index('pos_penerimaan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_penerimaan_detail');
        Schema::dropIfExists('transaksi_penerimaan');
    }
};
