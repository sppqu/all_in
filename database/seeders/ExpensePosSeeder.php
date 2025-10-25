<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpensePosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data default untuk pos pengeluaran
        $expensePositions = [
            [
                'pos_name' => 'Gaji Guru & Staff',
                'pos_code' => 'GAJI',
                'pos_type' => 'operasional',
                'pos_description' => 'Gaji bulanan guru, staff, dan karyawan sekolah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pos_name' => 'Biaya Operasional Sekolah',
                'pos_code' => 'OPS',
                'pos_type' => 'operasional',
                'pos_description' => 'Biaya listrik, air, internet, dan operasional harian',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pos_name' => 'Pemeliharaan Gedung',
                'pos_code' => 'MAINT',
                'pos_type' => 'fasilitas',
                'pos_description' => 'Biaya perbaikan dan pemeliharaan gedung sekolah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pos_name' => 'Pembelian Alat Tulis & ATK',
                'pos_code' => 'ATK',
                'pos_type' => 'akademik',
                'pos_description' => 'Pembelian alat tulis kantor dan kebutuhan administrasi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pos_name' => 'Kegiatan Akademik',
                'pos_code' => 'AKAD',
                'pos_type' => 'akademik',
                'pos_description' => 'Biaya kegiatan belajar mengajar dan akademik',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pos_name' => 'Biaya Administrasi',
                'pos_code' => 'ADMIN',
                'pos_type' => 'administrasi',
                'pos_description' => 'Biaya administrasi dan keuangan sekolah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insert data
        foreach ($expensePositions as $pos) {
            DB::table('expense_pos')->insert($pos);
        }
    }
}
