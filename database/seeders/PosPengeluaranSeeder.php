<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosPengeluaranSeeder extends Seeder
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
                'pos_description' => 'Gaji bulanan guru, staff, dan karyawan sekolah',
                'pos_type' => 'operasional',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pos_name' => 'Biaya Operasional Sekolah',
                'pos_description' => 'Biaya listrik, air, internet, dan operasional harian',
                'pos_type' => 'operasional',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pos_name' => 'Pemeliharaan Gedung',
                'pos_description' => 'Biaya perbaikan dan pemeliharaan gedung sekolah',
                'pos_type' => 'fasilitas',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pos_name' => 'Pembelian Alat Tulis & ATK',
                'pos_description' => 'Pembelian alat tulis kantor dan kebutuhan administrasi',
                'pos_type' => 'akademik',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pos_name' => 'Kegiatan Akademik',
                'pos_description' => 'Biaya kegiatan belajar mengajar dan akademik',
                'pos_type' => 'akademik',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pos_name' => 'Biaya Administrasi',
                'pos_description' => 'Biaya administrasi dan keuangan sekolah',
                'pos_type' => 'administrasi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insert data
        foreach ($expensePositions as $pos) {
            DB::table('pos_pengeluaran')->insert($pos);
        }
    }
}
