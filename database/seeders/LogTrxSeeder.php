<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogTrxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama
        DB::table('log_trx')->truncate();
        
        $today = now()->toDateString();
        $now = now();
        
        // Data sample untuk hari ini
        $sampleData = [
            // Pembayaran (type: in)
            [
                'transaction_date' => $today,
                'type' => 'in',
                'category' => 'bulanan',
                'description' => 'Pembayaran SPP Bulanan - Siswa A',
                'amount' => 500000,
                'payment_method' => 'cash',
                'status' => 'success',
                'student_id' => 1,
                'user_id' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'transaction_date' => $today,
                'type' => 'in',
                'category' => 'bebas',
                'description' => 'Pembayaran Uang Gedung - Siswa B',
                'amount' => 1000000,
                'payment_method' => 'midtrans',
                'status' => 'success',
                'student_id' => 2,
                'user_id' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'transaction_date' => $today,
                'type' => 'in',
                'category' => 'bulanan',
                'description' => 'Pembayaran SPP Bulanan - Siswa C',
                'amount' => 750000,
                'payment_method' => 'transfer',
                'status' => 'success',
                'student_id' => 3,
                'user_id' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // Pengeluaran (type: out)
            [
                'transaction_date' => $today,
                'type' => 'out',
                'category' => 'operasional',
                'description' => 'Pengeluaran Operasional Sekolah',
                'amount' => 500000,
                'payment_method' => 'cash',
                'status' => 'success',
                'student_id' => null,
                'user_id' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'transaction_date' => $today,
                'type' => 'out',
                'category' => 'utilitas',
                'description' => 'Pembayaran Listrik dan Air',
                'amount' => 300000,
                'payment_method' => 'transfer',
                'status' => 'success',
                'student_id' => null,
                'user_id' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // Tabungan (type: saving)
            [
                'transaction_date' => $today,
                'type' => 'saving',
                'category' => 'tabungan',
                'description' => 'Setoran Tabungan Siswa A',
                'amount' => 200000,
                'payment_method' => 'cash',
                'status' => 'success',
                'student_id' => 1,
                'user_id' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'transaction_date' => $today,
                'type' => 'saving',
                'category' => 'tabungan',
                'description' => 'Setoran Tabungan Siswa B',
                'amount' => 150000,
                'payment_method' => 'transfer',
                'status' => 'success',
                'student_id' => 2,
                'user_id' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];
        
        // Insert data sample
        foreach ($sampleData as $data) {
            DB::table('log_trx')->insert($data);
        }
        
        $this->command->info('✅ Data sample log_trx berhasil ditambahkan!');
        $this->command->info('📊 Total: ' . count($sampleData) . ' transaksi');
    }
}
