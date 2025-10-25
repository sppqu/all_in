c<?php

/**
 * Script untuk seed data addon ke database
 * Jalankan: php seed_addons.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Addon;

echo "========================================\n";
echo "  SEED ADDON DATA TO DATABASE\n";
echo "========================================\n\n";

$addons = [
    [
        'slug' => 'payment-gateway',
        'name' => 'Payment Gateway',
        'description' => 'Integrasi payment gateway untuk pembayaran online dengan berbagai metode pembayaran seperti kartu kredit, e-wallet, dan transfer bank.',
        'price' => 149000,
        'type' => 'one_time',
        'is_active' => true,
        'features' => [
            'Integrasi Midtrans Payment Gateway',
            'Pembayaran dengan Kartu Kredit/Debit',
            'E-Wallet (GoPay, OVO, DANA, LinkAja)',
            'Transfer Bank (BCA, BNI, BRI, Mandiri)',
            'Convenience Store (Indomaret, Alfamart)',
            'PayLater (Akulaku, Kredivo)',
            'Dashboard monitoring pembayaran',
            'Notifikasi pembayaran real-time',
            'Laporan transaksi pembayaran',
            'Support teknis 24/7'
        ]
    ],
    [
        'slug' => 'whatsapp-gateway',
        'name' => 'WhatsApp Gateway',
        'description' => 'Integrasi WhatsApp Gateway untuk notifikasi otomatis dan komunikasi dengan siswa dan orang tua melalui WhatsApp.',
        'price' => 99000,
        'type' => 'one_time',
        'is_active' => true,
        'features' => [
            'Integrasi WhatsApp Gateway API',
            'Notifikasi pembayaran otomatis',
            'Notifikasi tagihan dan tunggakan',
            'Notifikasi jadwal pembayaran',
            'Broadcast pesan ke siswa/orang tua',
            'Template pesan yang dapat dikustomisasi',
            'Laporan pengiriman pesan',
            'Dashboard monitoring WhatsApp',
            'Support multiple nomor WhatsApp',
            'API untuk integrasi custom'
        ]
    ],
    [
        'slug' => 'analisis-target',
        'name' => 'Menu Analisis Target',
        'description' => 'Fitur analisis target capaian untuk monitoring dan evaluasi target pembayaran SPP sekolah.',
        'price' => 99000,
        'type' => 'one_time',
        'is_active' => true,
        'features' => [
            'Dashboard analisis target capaian',
            'Monitoring target pembayaran per periode',
            'Grafik dan chart visualisasi data',
            'Laporan capaian target real-time',
            'Analisis tren pembayaran',
            'Perbandingan target vs realisasi',
            'Export laporan dalam format Excel/PDF',
            'Notifikasi target tidak tercapai',
            'Prediksi capaian target',
            'Dashboard executive summary'
        ]
    ],
    [
        'slug' => 'spmb',
        'name' => 'SPMB (Sistem Penerimaan Mahasiswa Baru)',
        'description' => 'Sistem lengkap untuk mengelola penerimaan siswa/mahasiswa baru dengan pendaftaran online, seleksi, dan administrasi.',
        'price' => 199000,
        'type' => 'one_time',
        'is_active' => true,
        'features' => [
            'Pendaftaran online siswa baru',
            'Form pendaftaran yang dapat dikustomisasi',
            'Sistem seleksi dan screening',
            'Dashboard monitoring pendaftar',
            'Laporan statistik pendaftaran',
            'Notifikasi status pendaftaran',
            'Integrasi dengan sistem akademik',
            'Export data pendaftar',
            'Manajemen kuota penerimaan',
            'Sistem pembayaran pendaftaran'
        ]
    ],
    [
        'slug' => 'bk',
        'name' => 'Bimbingan Konseling (BK)',
        'description' => 'Sistem pencatatan pelanggaran siswa dan konseling dengan skor point otomatis.',
        'price' => 99000,
        'type' => 'one_time',
        'is_active' => true,
        'features' => [
            'Pencatatan pelanggaran siswa dengan point otomatis',
            'Master 30+ jenis pelanggaran (Ringan, Sedang, Berat)',
            'Laporan rekap pelanggaran per siswa',
            'Ranking siswa berdasarkan total point',
            'History pelanggaran lengkap',
            'Export ke Excel & Print',
            'Filter berdasarkan kelas, tanggal, status',
            'Approval workflow (Pending/Approved/Rejected)',
            'Role khusus untuk Guru BK',
            'Dashboard monitoring real-time'
        ]
    ],
    [
        'slug' => 'ejurnal-7kaih',
        'name' => 'E-Jurnal Harian 7KAIH',
        'description' => 'Sistem pencatatan jurnal harian siswa berbasis 7 Kebiasaan Anak Indonesia Hebat (Belajar, Ibadah, Disiplin, Kebersihan, Kejujuran, Kerja Sama, Tanggung Jawab).',
        'price' => 249000,
        'type' => 'one_time',
        'is_active' => true,
        'features' => [
            'Jurnal harian berbasis 7 kategori kebiasaan positif',
            'Input kegiatan harian dengan penilaian mandiri (1-10)',
            'Upload foto dokumentasi kegiatan',
            'Rekap bulanan dengan grafik perkembangan',
            'Dashboard monitoring untuk guru',
            'Verifikasi jurnal oleh guru & feedback',
            'Laporan PDF jurnal mingguan/bulanan',
            'Notifikasi WhatsApp reminder pengisian',
            'Riwayat kegiatan siswa lengkap',
            'Analisis tren kebiasaan per siswa/kelas'
        ]
    ],
    [
        'slug' => 'e-perpustakaan',
        'name' => 'E-Perpustakaan Digital',
        'description' => 'Sistem perpustakaan digital lengkap untuk manajemen koleksi buku, peminjaman, dan pembacaan online.',
        'price' => 179000,
        'type' => 'one_time',
        'is_active' => true,
        'features' => [
            'Katalog buku digital',
            'Sistem peminjaman online',
            'Pembaca buku digital (e-reader)',
            'Manajemen koleksi perpustakaan',
            'Tracking peminjaman dan pengembalian',
            'Notifikasi jatuh tempo',
            'Laporan statistik perpustakaan',
            'Search dan filter buku',
            'Dashboard monitoring',
            'Integrasi barcode scanner'
        ]
    ],
    [
        'slug' => 'inventaris',
        'name' => 'Sistem Inventaris',
        'description' => 'Sistem manajemen inventaris sekolah yang lengkap untuk tracking aset, pemeliharaan, dan pengadaan.',
        'price' => 129000,
        'type' => 'one_time',
        'is_active' => true,
        'features' => [
            'Pencatatan aset inventaris sekolah',
            'Kategori dan klasifikasi barang',
            'Tracking lokasi dan kondisi aset',
            'Sistem pemeliharaan dan perbaikan',
            'Laporan inventaris real-time',
            'Alert barang rusak/expired',
            'Sistem pengadaan barang',
            'Export laporan inventaris',
            'Dashboard monitoring aset',
            'Integrasi dengan sistem keuangan'
        ]
    ]
];

echo "Sedang memproses " . count($addons) . " addon...\n\n";

$created = 0;
$updated = 0;

foreach ($addons as $addonData) {
    try {
        $addon = Addon::firstOrCreate(
            ['slug' => $addonData['slug']],
            $addonData
        );
        
        if ($addon->wasRecentlyCreated) {
            $created++;
            echo "✅ [CREATED] {$addon->name} (slug: {$addon->slug})\n";
        } else {
            // Update existing addon
            $addon->update($addonData);
            $updated++;
            echo "🔄 [UPDATED] {$addon->name} (slug: {$addon->slug})\n";
        }
    } catch (Exception $e) {
        echo "❌ [ERROR] Failed to create/update addon '{$addonData['slug']}': {$e->getMessage()}\n";
    }
}

echo "\n========================================\n";
echo "  SELESAI!\n";
echo "========================================\n";
echo "✅ Created: $created addon(s)\n";
echo "🔄 Updated: $updated addon(s)\n";
echo "📊 Total: " . Addon::count() . " addon(s) di database\n\n";

echo "Daftar addon yang tersedia:\n";
echo "----------------------------\n";
$allAddons = Addon::orderBy('name')->get();
foreach ($allAddons as $addon) {
    echo "  • {$addon->name}\n";
    echo "    Slug: {$addon->slug}\n";
    echo "    Harga: Rp " . number_format($addon->price, 0, ',', '.') . "\n";
    echo "    Status: " . ($addon->is_active ? 'Active' : 'Inactive') . "\n\n";
}

echo "========================================\n";
echo "Anda sekarang bisa aktivasi addon melalui:\n";
echo "  • Web: /activate-addon-page\n";
echo "  • URL: /activate/{userId}/{addonSlug}\n";
echo "========================================\n";

