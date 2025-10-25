<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Addon;
use Carbon\Carbon;

class LibraryAddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert Library addon
        Addon::firstOrCreate(
            ['slug' => 'e-perpustakaan'],
            [
                'name' => 'E-Perpustakaan',
                'description' => 'Sistem Perpustakaan Digital dengan fitur koleksi buku, baca online, peminjaman, dan manajemen lengkap.',
                'price' => 299000,
                'type' => 'one_time',
                'is_active' => true,
                'features' => [
                    'Koleksi Buku Digital (PDF)',
                    'Baca Online dengan PDF Viewer',
                    'Download Buku (Terbatas)',
                    'Sistem Peminjaman E-Book',
                    'Kategori & Pencarian Buku',
                    'Riwayat Aktivitas Pembacaan',
                    'Dashboard Statistik Lengkap',
                    'Buku Unggulan & Populer',
                    'Manajemen User & Akses',
                    'Filter & Sort Buku Advanced',
                ]
            ]
        );

        // Insert Book Categories
        $categories = [
            [
                'nama_kategori' => 'Teknologi & Komputer',
                'kode' => 'TECH',
                'deskripsi' => 'Buku tentang teknologi, pemrograman, dan komputer',
                'icon' => 'fas fa-laptop-code',
                'warna' => '#3498db',
                'urutan' => 1,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Bahasa & Sastra',
                'kode' => 'LANG',
                'deskripsi' => 'Buku bahasa Indonesia, Inggris, dan sastra',
                'icon' => 'fas fa-language',
                'warna' => '#9b59b6',
                'urutan' => 2,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Fiksi & Novel',
                'kode' => 'NOVEL',
                'deskripsi' => 'Novel, cerita fiksi, dan karya sastra',
                'icon' => 'fas fa-book-open',
                'warna' => '#e74c3c',
                'urutan' => 3,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Sains & Matematika',
                'kode' => 'SCIENCE',
                'deskripsi' => 'Buku sains, matematika, dan ilmu pengetahuan',
                'icon' => 'fas fa-flask',
                'warna' => '#27ae60',
                'urutan' => 4,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Sejarah & Sosial',
                'kode' => 'HISTORY',
                'deskripsi' => 'Buku sejarah, sosiologi, dan ilmu sosial',
                'icon' => 'fas fa-landmark',
                'warna' => '#f39c12',
                'urutan' => 5,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Agama & Akhlak',
                'kode' => 'RELIGION',
                'deskripsi' => 'Buku agama Islam, akhlak, dan pendidikan karakter',
                'icon' => 'fas fa-mosque',
                'warna' => '#16a085',
                'urutan' => 6,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Ekonomi & Bisnis',
                'kode' => 'BUSINESS',
                'deskripsi' => 'Buku ekonomi, bisnis, dan kewirausahaan',
                'icon' => 'fas fa-chart-line',
                'warna' => '#d35400',
                'urutan' => 7,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Seni & Budaya',
                'kode' => 'ART',
                'deskripsi' => 'Buku seni, musik, dan budaya',
                'icon' => 'fas fa-palette',
                'warna' => '#e91e63',
                'urutan' => 8,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Olahraga & Kesehatan',
                'kode' => 'HEALTH',
                'deskripsi' => 'Buku olahraga, kesehatan, dan kebugaran',
                'icon' => 'fas fa-heartbeat',
                'warna' => '#c0392b',
                'urutan' => 9,
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Referensi & Ensiklopedia',
                'kode' => 'REF',
                'deskripsi' => 'Kamus, ensiklopedia, dan buku referensi',
                'icon' => 'fas fa-book-medical',
                'warna' => '#34495e',
                'urutan' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('book_categories')->updateOrInsert(
                ['kode' => $category['kode']],
                array_merge($category, [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );
        }
    }
}
