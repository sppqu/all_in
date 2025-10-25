<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kasData = [
            [
                'nama_kas' => 'Kas Utama',
                'deskripsi' => 'Kas utama sekolah untuk operasional',
                'saldo' => 0,
                'is_active' => true,
                'jenis_kas' => 'cash',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kas' => 'Bank BCA',
                'deskripsi' => 'Rekening Bank BCA sekolah',
                'saldo' => 0,
                'is_active' => true,
                'jenis_kas' => 'bank',
                'nomor_rekening' => '1234567890',
                'nama_bank' => 'BCA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kas' => 'Bank Mandiri',
                'deskripsi' => 'Rekening Bank Mandiri sekolah',
                'saldo' => 0,
                'is_active' => true,
                'jenis_kas' => 'bank',
                'nomor_rekening' => '0987654321',
                'nama_bank' => 'Mandiri',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kas' => 'Dana Darurat',
                'deskripsi' => 'Dana darurat sekolah',
                'saldo' => 0,
                'is_active' => true,
                'jenis_kas' => 'cash',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($kasData as $kas) {
            DB::table('kas')->updateOrInsert(
                ['nama_kas' => $kas['nama_kas']],
                $kas
            );
        }

        echo "✅ Kas data seeded successfully!\n";
        echo "💰 Created " . count($kasData) . " kas accounts\n";
    }
}
