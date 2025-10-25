<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Addon;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Payment Gateway Add-on (jika belum ada)
        Addon::firstOrCreate(
            ['slug' => 'payment-gateway'],
            [
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
            ]
        );

        // WhatsApp Gateway Add-on
        Addon::firstOrCreate(
            ['slug' => 'whatsapp-gateway'],
            [
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
            ]
        );

        // Menu Analisis Target Add-on
        Addon::firstOrCreate(
            ['slug' => 'analisis-target'],
            [
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
            ]
        );

        // SPMB Add-on
        Addon::firstOrCreate(
            ['slug' => 'spmb'],
            [
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
            ]
        );

        // Bimbingan Konseling (BK) Add-on
        Addon::firstOrCreate(
            ['slug' => 'bk'],
            [
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
            ]
        );

        // E-Jurnal Harian 7KAIH Add-on
        Addon::firstOrCreate(
            ['slug' => 'ejurnal-7kaih'],
            [
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
            ]
        );

        // Inventaris Add-on
        Addon::firstOrCreate(
            ['slug' => 'inventaris'],
            [
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
        );
    }
}
