<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurnalKategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing entries first to avoid FK constraint error
        DB::table('jurnal_entries')->delete();
        DB::table('jurnal_harian')->delete();
        DB::table('jurnal_kategori')->delete();
        
        $kategori = [
            [
                'nama_kategori' => 'Bangun Pagi',
                'kode' => 'BANGUN',
                'deskripsi' => 'Jam berapa bangun pagi hari ini',
                'icon' => 'fas fa-sun',
                'warna' => '#ff9800',
                'urutan' => 1,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Beribadah',
                'kode' => 'IBADAH',
                'deskripsi' => 'Checklist sholat 5 waktu (Subuh, Dzuhur, Asar, Magrib, Isya)',
                'icon' => 'fas fa-mosque',
                'warna' => '#28a745',
                'urutan' => 2,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Berolahraga',
                'kode' => 'OLAHRAGA',
                'deskripsi' => 'Apakah berolahraga hari ini dan jam berapa',
                'icon' => 'fas fa-running',
                'warna' => '#007bff',
                'urutan' => 3,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Makan Sehat & Bergizi',
                'kode' => 'MAKAN',
                'deskripsi' => 'Checklist makan (pagi, siang, malam) dan keterangan menu',
                'icon' => 'fas fa-utensils',
                'warna' => '#17a2b8',
                'urutan' => 4,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Gemar Membaca',
                'kode' => 'MEMBACA',
                'deskripsi' => 'Apakah belajar/membaca hari ini dan apa yang dipelajari',
                'icon' => 'fas fa-book-reader',
                'warna' => '#dc3545',
                'urutan' => 5,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Bermasyarakat',
                'kode' => 'SOSIAL',
                'deskripsi' => 'Kegiatan dengan keluarga atau teman',
                'icon' => 'fas fa-users',
                'warna' => '#fd7e14',
                'urutan' => 6,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Tidur Cepat',
                'kode' => 'TIDUR',
                'deskripsi' => 'Jam berapa tidur malam dan keterangan',
                'icon' => 'fas fa-bed',
                'warna' => '#6f42c1',
                'urutan' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($kategori as $item) {
            DB::table('jurnal_kategori')->insert(array_merge($item, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

