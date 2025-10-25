<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiPenerimaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID metode pembayaran pertama
        $metodePembayaran = DB::table('payment_methods')->first();
        $metodeId = $metodePembayaran ? $metodePembayaran->id : 1;
        
        // Ambil ID pos penerimaan pertama
        $posPenerimaan = DB::table('pos_pembayaran')->first();
        $posId = $posPenerimaan ? $posPenerimaan->pos_id : 1;
        
        // Insert transaksi penerimaan
        $transaksiId = DB::table('transaksi_penerimaan')->insertGetId([
            'no_transaksi' => 'TRM202508000001',
            'tanggal_penerimaan' => '2025-08-22',
            'tahun_ajaran' => '2025/2026',
            'diterima_dari' => 'fdfhdh',
            'metode_pembayaran_id' => $metodeId,
            'keterangan_transaksi' => 'dhdhdh',
            'operator' => 'ADMIN - Administrator',
            'total_penerimaan' => 450000,
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Insert detail transaksi
        DB::table('transaksi_penerimaan_detail')->insert([
            [
                'transaksi_id' => $transaksiId,
                'pos_penerimaan_id' => $posId,
                'keterangan_item' => 'jjjklll',
                'jumlah' => 50000,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'transaksi_id' => $transaksiId,
                'pos_penerimaan_id' => $posId,
                'keterangan_item' => 'sdsadasd',
                'jumlah' => 400000,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        $this->command->info('Transaksi Penerimaan sample data berhasil ditambahkan!');
    }
}
