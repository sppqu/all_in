<?php

/**
 * Script untuk seed kategori jurnal harian
 * Jalankan: php seed_jurnal_kategori.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "  SEED KATEGORI JURNAL HARIAN\n";
echo "========================================\n\n";

// Cek apakah tabel ada
if (!DB::getSchemaBuilder()->hasTable('jurnal_kategori')) {
    echo "❌ ERROR: Tabel 'jurnal_kategori' tidak ditemukan!\n";
    echo "   Silakan jalankan migration dulu:\n";
    echo "   php artisan migrate\n\n";
    exit(1);
}

// Cek existing data
$existing = DB::table('jurnal_kategori')->count();
echo "📊 Total kategori saat ini: $existing\n\n";

if ($existing > 0) {
    echo "⚠️  WARNING: Tabel sudah berisi $existing kategori.\n";
    echo "   Apakah ingin menghapus dan create ulang? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $answer = trim($line);
    
    if (strtolower($answer) !== 'y') {
        echo "\n❌ Dibatalkan. Tidak ada perubahan.\n\n";
        exit(0);
    }
    
    echo "\n🗑️  Menghapus data lama...\n";
    DB::table('jurnal_entries')->delete();
    DB::table('jurnal_harian')->delete();
    DB::table('jurnal_kategori')->delete();
    echo "✅ Data lama berhasil dihapus\n\n";
}

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

echo "📝 Menambahkan 7 kategori jurnal harian...\n\n";

$success = 0;
foreach ($kategori as $item) {
    try {
        DB::table('jurnal_kategori')->insert(array_merge($item, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $success++;
        echo "  ✅ {$item['nama_kategori']} ({$item['kode']})\n";
    } catch (Exception $e) {
        echo "  ❌ ERROR: {$item['nama_kategori']} - {$e->getMessage()}\n";
    }
}

echo "\n========================================\n";
echo "  SELESAI!\n";
echo "========================================\n";
echo "✅ Berhasil: $success kategori\n";
echo "📊 Total: " . DB::table('jurnal_kategori')->count() . " kategori di database\n\n";

echo "Daftar Kategori Jurnal:\n";
echo "------------------------\n";
$allKategori = DB::table('jurnal_kategori')->orderBy('urutan')->get();
foreach ($allKategori as $k) {
    echo "  {$k->urutan}. {$k->nama_kategori}\n";
    echo "     Kode: {$k->kode} | Icon: {$k->icon} | Warna: {$k->warna}\n";
    echo "     {$k->deskripsi}\n\n";
}

echo "========================================\n";
echo "🚀 Form jurnal siswa sekarang sudah bisa digunakan!\n";
echo "   Akses: /student/jurnal/create\n";
echo "========================================\n\n";

