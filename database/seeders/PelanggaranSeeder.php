<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PelanggaranKategori;
use App\Models\Pelanggaran;

class PelanggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Categories
        $ringan = PelanggaranKategori::create([
            'nama' => 'Pelanggaran Ringan',
            'kode' => 'R',
            'keterangan' => 'Pelanggaran dengan kategori ringan, sanksi berupa teguran lisan atau tertulis',
            'warna' => '#ffc107',
            'is_active' => true
        ]);

        $sedang = PelanggaranKategori::create([
            'nama' => 'Pelanggaran Sedang',
            'kode' => 'S',
            'keterangan' => 'Pelanggaran dengan kategori sedang, sanksi berupa skorsing atau panggilan orangtua',
            'warna' => '#ff9800',
            'is_active' => true
        ]);

        $berat = PelanggaranKategori::create([
            'nama' => 'Pelanggaran Berat',
            'kode' => 'B',
            'keterangan' => 'Pelanggaran dengan kategori berat, sanksi dapat berupa skorsing panjang hingga dikeluarkan',
            'warna' => '#f44336',
            'is_active' => true
        ]);

        // 2. Create Pelanggaran Ringan
        $pelanggaranRingan = [
            ['kode' => 'R001', 'nama' => 'Terlambat masuk sekolah', 'point' => 5],
            ['kode' => 'R002', 'nama' => 'Tidak mengerjakan PR', 'point' => 5],
            ['kode' => 'R003', 'nama' => 'Tidak memakai atribut sekolah lengkap', 'point' => 10],
            ['kode' => 'R004', 'nama' => 'Tidak membawa buku pelajaran', 'point' => 5],
            ['kode' => 'R005', 'nama' => 'Berbicara saat upacara', 'point' => 10],
            ['kode' => 'R006', 'nama' => 'Tidak mengikuti apel/upacara', 'point' => 15],
            ['kode' => 'R007', 'nama' => 'Membuang sampah sembarangan', 'point' => 10],
            ['kode' => 'R008', 'nama' => 'Makan/minum saat jam pelajaran', 'point' => 10],
            ['kode' => 'R009', 'nama' => 'Tidur saat jam pelajaran', 'point' => 10],
            ['kode' => 'R010', 'nama' => 'Berpakaian tidak rapi', 'point' => 10],
        ];

        foreach ($pelanggaranRingan as $p) {
            Pelanggaran::create([
                'kategori_id' => $ringan->id,
                'kode' => $p['kode'],
                'nama' => $p['nama'],
                'point' => $p['point'],
                'is_active' => true
            ]);
        }

        // 3. Create Pelanggaran Sedang
        $pelanggaranSedang = [
            ['kode' => 'S001', 'nama' => 'Tidak masuk sekolah tanpa keterangan', 'point' => 20],
            ['kode' => 'S002', 'nama' => 'Meninggalkan pelajaran tanpa izin', 'point' => 25],
            ['kode' => 'S003', 'nama' => 'Keluar kelas saat guru mengajar tanpa izin', 'point' => 20],
            ['kode' => 'S004', 'nama' => 'Membuat keributan di kelas', 'point' => 25],
            ['kode' => 'S005', 'nama' => 'Menggunakan HP saat jam pelajaran', 'point' => 30],
            ['kode' => 'S006', 'nama' => 'Berbohong kepada guru', 'point' => 30],
            ['kode' => 'S007', 'nama' => 'Mencontek saat ulangan', 'point' => 35],
            ['kode' => 'S008', 'nama' => 'Tidak mengikuti piket kelas', 'point' => 20],
            ['kode' => 'S009', 'nama' => 'Merokok di lingkungan sekolah', 'point' => 50],
            ['kode' => 'S010', 'nama' => 'Berpacaran di lingkungan sekolah', 'point' => 40],
        ];

        foreach ($pelanggaranSedang as $p) {
            Pelanggaran::create([
                'kategori_id' => $sedang->id,
                'kode' => $p['kode'],
                'nama' => $p['nama'],
                'point' => $p['point'],
                'is_active' => true
            ]);
        }

        // 4. Create Pelanggaran Berat
        $pelanggaranBerat = [
            ['kode' => 'B001', 'nama' => 'Berkelahi dengan teman', 'point' => 75],
            ['kode' => 'B002', 'nama' => 'Membawa senjata tajam', 'point' => 100],
            ['kode' => 'B003', 'nama' => 'Mengancam guru/teman', 'point' => 80],
            ['kode' => 'B004', 'nama' => 'Mencuri', 'point' => 100],
            ['kode' => 'B005', 'nama' => 'Merusak fasilitas sekolah dengan sengaja', 'point' => 75],
            ['kode' => 'B006', 'nama' => 'Membawa/mengonsumsi narkoba', 'point' => 150],
            ['kode' => 'B007', 'nama' => 'Membawa/mengonsumsi minuman keras', 'point' => 100],
            ['kode' => 'B008', 'nama' => 'Memalsukan tanda tangan/dokumen', 'point' => 80],
            ['kode' => 'B009', 'nama' => 'Melakukan tindakan asusila', 'point' => 150],
            ['kode' => 'B010', 'nama' => 'Terlibat tawuran', 'point' => 150],
        ];

        foreach ($pelanggaranBerat as $p) {
            Pelanggaran::create([
                'kategori_id' => $berat->id,
                'kode' => $p['kode'],
                'nama' => $p['nama'],
                'point' => $p['point'],
                'is_active' => true
            ]);
        }

        echo "✅ Seeder berhasil! Total pelanggaran: " . Pelanggaran::count() . "\n";
    }
}
