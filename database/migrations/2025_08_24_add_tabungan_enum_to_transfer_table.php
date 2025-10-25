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
        Schema::table('transfer', function (Blueprint $table) {
            // Tambahkan enum untuk jenis pembayaran tabungan
            $table->enum('jenis_pembayaran', ['bulanan', 'bebas', 'tabungan'])->nullable()->after('bill_type');
            
            // Tambahkan field untuk tracking metode pembayaran tabungan
            $table->enum('metode_pembayaran_tabungan', ['tunai', 'transfer_bank', 'payment_gateway'])->nullable()->after('jenis_pembayaran');
            
            // Tambahkan field untuk referensi transaksi tabungan
            $table->string('referensi_tabungan', 255)->nullable()->after('metode_pembayaran_tabungan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_pembayaran',
                'metode_pembayaran_tabungan',
                'referensi_tabungan'
            ]);
        });
    }
};
