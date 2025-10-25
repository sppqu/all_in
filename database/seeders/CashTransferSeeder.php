<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data kas yang tersedia
        $kasList = DB::table('kas')->pluck('id')->toArray();
        
        if (count($kasList) < 2) {
            $this->command->info('Minimal harus ada 2 kas untuk membuat sample transfer!');
            return;
        }
        
        // Sample data transfer
        $transfers = [
            [
                'tanggal_transfer' => '2025-08-20',
                'no_transaksi' => 'TRF-250820-001',
                'kas_asal_id' => $kasList[0],
                'kas_tujuan_id' => $kasList[1],
                'jumlah_transfer' => 5000000,
                'keterangan' => 'Transfer dana operasional bulanan',
                'nama_penyetor' => 'Bendahara',
                'nama_penerima' => 'Kasir',
                'petugas_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tanggal_transfer' => '2025-08-18',
                'no_transaksi' => 'TRF-250818-001',
                'kas_asal_id' => $kasList[1],
                'kas_tujuan_id' => $kasList[0],
                'jumlah_transfer' => 2000000,
                'keterangan' => 'Pengembalian dana operasional',
                'nama_penyetor' => 'Kasir',
                'nama_penerima' => 'Bendahara',
                'petugas_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tanggal_transfer' => '2025-08-15',
                'no_transaksi' => 'TRF-250815-001',
                'kas_asal_id' => $kasList[0],
                'kas_tujuan_id' => $kasList[1],
                'jumlah_transfer' => 3000000,
                'keterangan' => 'Transfer dana untuk pembelian ATK',
                'nama_penyetor' => 'Bendahara',
                'nama_penerima' => 'Kasir',
                'petugas_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        foreach ($transfers as $transfer) {
            DB::table('cash_transfers')->insert($transfer);
        }
        
        $this->command->info('Sample data cash transfers berhasil ditambahkan!');
    }
}
