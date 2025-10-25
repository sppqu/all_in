<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada data pos pengeluaran terlebih dahulu
        $expensePos = DB::table('expense_pos')->first();
        if (!$expensePos) {
            $this->command->info('Tidak ada data pos pengeluaran. Jalankan ExpensePosSeeder terlebih dahulu.');
            return;
        }

        // Data sample transaksi pengeluaran
        $transactions = [
            [
                'tanggal' => '2025-08-21',
                'no_transaksi' => 'EXP-2025-08-21-001',
                'pos_pengeluaran_id' => $expensePos->id,
                'jumlah_pengeluaran' => 2500000,
                'keterangan' => 'Pembelian ATK untuk semester baru',
                'operator' => 'Admin',
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tanggal' => '2025-08-20',
                'no_transaksi' => 'EXP-2025-08-20-001',
                'pos_pengeluaran_id' => $expensePos->id,
                'jumlah_pengeluaran' => 1500000,
                'keterangan' => 'Biaya maintenance komputer lab',
                'operator' => 'Admin',
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tanggal' => '2025-08-19',
                'no_transaksi' => 'EXP-2025-08-19-001',
                'pos_pengeluaran_id' => $expensePos->id,
                'jumlah_pengeluaran' => 800000,
                'keterangan' => 'Biaya kebersihan bulanan',
                'operator' => 'Admin',
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($transactions as $transaction) {
            DB::table('expense_transactions')->insert($transaction);
        }

        $this->command->info('Data sample transaksi pengeluaran berhasil ditambahkan!');
    }
}
