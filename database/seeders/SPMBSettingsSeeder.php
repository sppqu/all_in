<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SPMBSettings;
use App\Models\SPMBKejuruan;

class SPMBSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create SPMB Settings for current year
        SPMBSettings::create([
            'tahun_pelajaran' => '2024/2025',
            'pendaftaran_dibuka' => true,
            'tanggal_buka' => now()->subDays(30),
            'tanggal_tutup' => now()->addDays(60),
            'biaya_pendaftaran' => 50000,
            'biaya_spmb' => 100000,
            'deskripsi' => 'Pendaftaran SPMB Tahun Pelajaran 2024/2025',
        ]);

        // Create Kejuruan data
        $kejuruan = [
            [
                'nama_kejuruan' => 'Teknik Informatika',
                'kode_kejuruan' => 'TI',
                'deskripsi' => 'Program studi Teknik Informatika',
                'aktif' => true,
                'kuota' => 50
            ],
            [
                'nama_kejuruan' => 'Teknik Komputer',
                'kode_kejuruan' => 'TK',
                'deskripsi' => 'Program studi Teknik Komputer',
                'aktif' => true,
                'kuota' => 30
            ],
            [
                'nama_kejuruan' => 'Sistem Informasi',
                'kode_kejuruan' => 'SI',
                'deskripsi' => 'Program studi Sistem Informasi',
                'aktif' => true,
                'kuota' => 40
            ],
            [
                'nama_kejuruan' => 'Manajemen Informatika',
                'kode_kejuruan' => 'MI',
                'deskripsi' => 'Program studi Manajemen Informatika',
                'aktif' => true,
                'kuota' => 35
            ]
        ];

        foreach ($kejuruan as $data) {
            SPMBKejuruan::create($data);
        }

        echo "✅ SPMB Settings dan Kejuruan berhasil dibuat!\n";
        echo "📅 Tahun Pelajaran: 2024/2025\n";
        echo "🎓 Kejuruan: " . count($kejuruan) . " program studi\n";
    }
}
