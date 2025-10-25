<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SPMBAdditionalFee;

class SPMBAdditionalFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $additionalFees = [
            // Seragam
            [
                'name' => 'Seragam Putra',
                'code' => 'SERAGAM_PUTRA',
                'description' => 'Seragam lengkap untuk siswa putra (baju, celana, topi, dasi)',
                'type' => 'mandatory',
                'category' => 'seragam',
                'amount' => 350000,
                'conditions' => ['gender' => 'L'],
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Seragam Putri',
                'code' => 'SERAGAM_PUTRI',
                'description' => 'Seragam lengkap untuk siswa putri (baju, rok, kerudung, dasi)',
                'type' => 'mandatory',
                'category' => 'seragam',
                'amount' => 380000,
                'conditions' => ['gender' => 'P'],
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Seragam Olahraga',
                'code' => 'SERAGAM_OLAHRAGA',
                'description' => 'Seragam olahraga (kaos dan celana pendek)',
                'type' => 'mandatory',
                'category' => 'seragam',
                'amount' => 120000,
                'conditions' => null,
                'is_active' => true,
                'sort_order' => 3
            ],
            
            // Buku & Modul
            [
                'name' => 'Buku Paket Semester 1',
                'code' => 'BUKU_SEMESTER_1',
                'description' => 'Buku paket untuk semester 1 (semua mata pelajaran)',
                'type' => 'mandatory',
                'category' => 'buku',
                'amount' => 450000,
                'conditions' => null,
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Buku Paket Semester 2',
                'code' => 'BUKU_SEMESTER_2',
                'description' => 'Buku paket untuk semester 2 (semua mata pelajaran)',
                'type' => 'optional',
                'category' => 'buku',
                'amount' => 450000,
                'conditions' => null,
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'Modul Praktikum',
                'code' => 'MODUL_PRAKTIKUM',
                'description' => 'Modul praktikum untuk mata pelajaran kejuruan',
                'type' => 'mandatory',
                'category' => 'buku',
                'amount' => 150000,
                'conditions' => null,
                'is_active' => true,
                'sort_order' => 6
            ],
            
            // Alat Tulis
            [
                'name' => 'Alat Tulis Lengkap',
                'code' => 'ALAT_TULIS',
                'description' => 'Paket alat tulis lengkap (pensil, pulpen, penghapus, penggaris, dll)',
                'type' => 'mandatory',
                'category' => 'alat_tulis',
                'amount' => 75000,
                'conditions' => null,
                'is_active' => true,
                'sort_order' => 7
            ],
            [
                'name' => 'Buku Catatan',
                'code' => 'BUKU_CATATAN',
                'description' => 'Buku catatan untuk semua mata pelajaran',
                'type' => 'optional',
                'category' => 'alat_tulis',
                'amount' => 50000,
                'conditions' => null,
                'is_active' => true,
                'sort_order' => 8
            ],
            
            // Kegiatan
            [
                'name' => 'Study Tour',
                'code' => 'STUDY_TOUR',
                'description' => 'Biaya study tour semester 1',
                'type' => 'optional',
                'category' => 'kegiatan',
                'amount' => 200000,
                'conditions' => null,
                'is_active' => true,
                'sort_order' => 9
            ],
            [
                'name' => 'Praktik Kerja Industri',
                'code' => 'PRAKERIN',
                'description' => 'Biaya praktik kerja industri',
                'type' => 'mandatory',
                'category' => 'kegiatan',
                'amount' => 300000,
                'conditions' => null,
                'is_active' => true,
                'sort_order' => 10
            ],
            
            // Lainnya
            [
                'name' => 'Asuransi Kecelakaan',
                'code' => 'ASURANSI',
                'description' => 'Asuransi kecelakaan selama tahun ajaran',
                'type' => 'mandatory',
                'category' => 'lainnya',
                'amount' => 25000,
                'conditions' => null,
                'is_active' => true,
                'sort_order' => 11
            ],
            [
                'name' => 'Kartu Pelajar',
                'code' => 'KARTU_PELAJAR',
                'description' => 'Kartu pelajar dengan foto dan laminasi',
                'type' => 'mandatory',
                'category' => 'lainnya',
                'amount' => 15000,
                'conditions' => null,
                'is_active' => true,
                'sort_order' => 12
            ]
        ];

        foreach ($additionalFees as $feeData) {
            SPMBAdditionalFee::create($feeData);
        }
    }
}