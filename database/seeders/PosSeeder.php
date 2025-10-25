<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if data already exists
        if (DB::table('pos_pembayaran')->count() > 0) {
            $this->command->info('Pos Pembayaran data already exists!');
            return;
        }
        
        // Insert pos pembayaran data
        $posData = [
            [
                'pos_name' => 'SPP',
                'pos_description' => 'Sumbangan Pembinaan Pendidikan Bulanan'
            ],
            [
                'pos_name' => 'Uang Gedung',
                'pos_description' => 'Uang Gedung Sekolah'
            ],
            [
                'pos_name' => 'Uang Seragam',
                'pos_description' => 'Uang Seragam Sekolah'
            ],
            [
                'pos_name' => 'Uang Buku',
                'pos_description' => 'Uang Buku Pelajaran'
            ],
            [
                'pos_name' => 'Uang Kegiatan',
                'pos_description' => 'Uang Kegiatan Sekolah'
            ]
        ];
        
        foreach ($posData as $pos) {
            DB::table('pos_pembayaran')->insert($pos);
        }
        
        $this->command->info('Pos Pembayaran data seeded successfully!');
    }
} 