<?php
/**
 * Script untuk cek dan fix masalah dropdown kas di VPS
 * Upload file ini ke root folder VPS, lalu jalankan via browser atau terminal
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<pre>";
echo "=======================================================\n";
echo "   CHECK & FIX DROPDOWN KAS DI VPS\n";
echo "=======================================================\n\n";

// ============================================
// 1. CEK TABEL KAS ADA ATAU TIDAK
// ============================================
echo "1. CEK TABEL 'kas' ...\n";
if (!Schema::hasTable('kas')) {
    echo "   ❌ TABEL 'kas' TIDAK DITEMUKAN!\n";
    echo "   SOLUSI: Jalankan migration\n";
    echo "   Command: php artisan migrate\n\n";
    exit;
} else {
    echo "   ✅ Tabel 'kas' ditemukan\n\n";
}

// ============================================
// 2. CEK DATA KAS
// ============================================
echo "2. CEK DATA KAS ...\n";
$allKas = DB::table('kas')->get();
echo "   Total data kas: " . $allKas->count() . "\n";

if ($allKas->count() == 0) {
    echo "   ❌ TIDAK ADA DATA KAS! (Ini penyebab dropdown kosong)\n\n";
    
    echo "3. AUTO-INSERT SAMPLE DATA KAS ...\n";
    
    $sampleKas = [
        [
            'nama_kas' => 'Kas Tunai',
            'jenis_kas' => 'cash',
            'deskripsi' => 'Kas tunai sekolah',
            'nomor_rekening' => null,
            'nama_bank' => null,
            'saldo' => 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'nama_kas' => 'Bank BRI',
            'jenis_kas' => 'bank',
            'deskripsi' => 'Rekening sekolah di Bank BRI',
            'nomor_rekening' => '1234567890',
            'nama_bank' => 'Bank BRI',
            'saldo' => 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'nama_kas' => 'Bank Mandiri',
            'jenis_kas' => 'bank',
            'deskripsi' => 'Rekening sekolah di Bank Mandiri',
            'nomor_rekening' => '9876543210',
            'nama_bank' => 'Bank Mandiri',
            'saldo' => 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'nama_kas' => 'E-Wallet (OVO/GoPay)',
            'jenis_kas' => 'e_wallet',
            'deskripsi' => 'E-Wallet untuk transaksi online',
            'nomor_rekening' => '081234567890',
            'nama_bank' => null,
            'saldo' => 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    try {
        foreach ($sampleKas as $kas) {
            DB::table('kas')->insert($kas);
            echo "   ✓ Insert: {$kas['nama_kas']}\n";
        }
        echo "   ✅ Berhasil insert " . count($sampleKas) . " data kas!\n\n";
        
        $allKas = DB::table('kas')->get(); // Refresh data
    } catch (\Exception $e) {
        echo "   ❌ Error insert: " . $e->getMessage() . "\n\n";
        exit;
    }
}

// ============================================
// 3. TAMPILKAN SEMUA KAS
// ============================================
echo "3. DAFTAR KAS YANG TERSEDIA:\n";
if ($allKas->count() > 0) {
    foreach ($allKas as $kas) {
        $status = $kas->is_active ? '✅ Aktif' : '❌ Nonaktif';
        echo sprintf(
            "   ID: %s | Nama: %-25s | Jenis: %-10s | %s\n",
            $kas->id,
            $kas->nama_kas,
            $kas->jenis_kas,
            $status
        );
    }
    echo "\n";
}

// ============================================
// 4. CEK KAS AKTIF
// ============================================
echo "4. CEK KAS YANG AKTIF (is_active = 1) ...\n";
$activeKas = DB::table('kas')->where('is_active', 1)->get();
echo "   Total kas aktif: " . $activeKas->count() . "\n";

if ($activeKas->count() == 0) {
    echo "   ❌ WARNING: Tidak ada kas yang aktif!\n";
    echo "   SOLUSI: Aktifkan minimal 1 kas\n\n";
} else {
    echo "   ✅ OK - Ada " . $activeKas->count() . " kas aktif\n\n";
}

// ============================================
// 5. TEST QUERY CONTROLLER
// ============================================
echo "5. TEST QUERY YANG DIGUNAKAN CONTROLLER ...\n";
$kasList = DB::table('kas')
    ->where('is_active', 1)
    ->orderBy('nama_kas')
    ->get();
    
echo "   Query: DB::table('kas')->where('is_active', 1)->get()\n";
echo "   Result: " . $kasList->count() . " data\n";

if ($kasList->count() > 0) {
    echo "   ✅ Query berhasil! Dropdown seharusnya tampil\n\n";
} else {
    echo "   ❌ Query tidak return data! Dropdown akan kosong\n\n";
}

// ============================================
// 6. INFORMASI CACHE
// ============================================
echo "6. INFORMASI CACHE:\n";
echo "   Jika masih tidak muncul, jalankan command berikut di VPS:\n";
echo "   \n";
echo "   cd /path/to/project\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan view:clear\n";
echo "   php artisan route:clear\n";
echo "   \n";

// ============================================
// 7. SUMMARY
// ============================================
echo "\n=======================================================\n";
echo "   RINGKASAN STATUS\n";
echo "=======================================================\n";
echo "Tabel 'kas':      " . (Schema::hasTable('kas') ? '✅ Ada' : '❌ Tidak ada') . "\n";
echo "Total data kas:   " . $allKas->count() . " data\n";
echo "Kas aktif:        " . $activeKas->count() . " data\n";
echo "Query controller: " . ($kasList->count() > 0 ? '✅ Berhasil (' . $kasList->count() . ' data)' : '❌ Kosong') . "\n";
echo "\n";

if ($kasList->count() > 0) {
    echo "✅ SEMUA OK! Dropdown kas seharusnya sudah tampil.\n";
    echo "   Jika masih belum tampil:\n";
    echo "   1. Clear cache Laravel (lihat perintah di atas)\n";
    echo "   2. Pastikan file controller sudah ter-update di VPS\n";
    echo "   3. Hard refresh browser (Ctrl+Shift+R)\n";
} else {
    echo "❌ MASIH ADA MASALAH! Periksa:\n";
    echo "   1. Data kas harus ada dan aktif\n";
    echo "   2. Migration sudah dijalankan\n";
    echo "   3. Database connection benar\n";
}

echo "\n=======================================================\n";
echo "SELESAI - " . date('Y-m-d H:i:s') . "\n";
echo "=======================================================\n";
echo "</pre>";

