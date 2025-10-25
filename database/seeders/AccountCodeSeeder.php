<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccountCode;

class AccountCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountCodes = [
            // AKTIVA (1xxx)
            [
                'kode' => '1101',
                'nama' => 'Kas',
                'deskripsi' => 'Uang tunai yang tersedia di sekolah',
                'tipe' => 'aktiva',
                'kategori' => 'lancar',
                'is_active' => true
            ],
            [
                'kode' => '1102',
                'nama' => 'Bank',
                'deskripsi' => 'Saldo rekening bank sekolah',
                'tipe' => 'aktiva',
                'kategori' => 'lancar',
                'is_active' => true
            ],
            [
                'kode' => '1201',
                'nama' => 'Piutang SPP',
                'deskripsi' => 'Piutang dari siswa atas pembayaran SPP',
                'tipe' => 'aktiva',
                'kategori' => 'lancar',
                'is_active' => true
            ],
            [
                'kode' => '1202',
                'nama' => 'Piutang Lain-lain',
                'deskripsi' => 'Piutang lainnya yang dimiliki sekolah',
                'tipe' => 'aktiva',
                'kategori' => 'lancar',
                'is_active' => true
            ],
            [
                'kode' => '1301',
                'nama' => 'Persediaan ATK',
                'deskripsi' => 'Persediaan alat tulis kantor',
                'tipe' => 'aktiva',
                'kategori' => 'lancar',
                'is_active' => true
            ],
            [
                'kode' => '1401',
                'nama' => 'Gedung',
                'deskripsi' => 'Bangunan dan gedung sekolah',
                'tipe' => 'aktiva',
                'kategori' => 'tetap',
                'is_active' => true
            ],
            [
                'kode' => '1402',
                'nama' => 'Kendaraan',
                'deskripsi' => 'Kendaraan operasional sekolah',
                'tipe' => 'aktiva',
                'kategori' => 'tetap',
                'is_active' => true
            ],
            [
                'kode' => '1403',
                'nama' => 'Peralatan',
                'deskripsi' => 'Peralatan dan mesin sekolah',
                'tipe' => 'aktiva',
                'kategori' => 'tetap',
                'is_active' => true
            ],

            // PASIVA (2xxx)
            [
                'kode' => '2101',
                'nama' => 'Hutang Dagang',
                'deskripsi' => 'Hutang kepada supplier dan vendor',
                'tipe' => 'pasiva',
                'kategori' => 'lancar',
                'is_active' => true
            ],
            [
                'kode' => '2102',
                'nama' => 'Hutang Bank',
                'deskripsi' => 'Pinjaman bank untuk operasional',
                'tipe' => 'pasiva',
                'kategori' => 'lancar',
                'is_active' => true
            ],
            [
                'kode' => '2103',
                'nama' => 'Hutang Pajak',
                'deskripsi' => 'Hutang pajak yang belum dibayar',
                'tipe' => 'pasiva',
                'kategori' => 'lancar',
                'is_active' => true
            ],
            [
                'kode' => '2104',
                'nama' => 'Hutang Gaji',
                'deskripsi' => 'Hutang gaji karyawan dan guru',
                'tipe' => 'pasiva',
                'kategori' => 'lancar',
                'is_active' => true
            ],

            // MODAL (3xxx)
            [
                'kode' => '3101',
                'nama' => 'Modal Pemilik',
                'deskripsi' => 'Modal awal dari pemilik sekolah',
                'tipe' => 'modal',
                'kategori' => null,
                'is_active' => true
            ],
            [
                'kode' => '3201',
                'nama' => 'Laba Ditahan',
                'deskripsi' => 'Laba yang ditahan untuk pengembangan',
                'tipe' => 'modal',
                'kategori' => null,
                'is_active' => true
            ],

            // PENDAPATAN (4xxx)
            [
                'kode' => '4101',
                'nama' => 'Pendapatan SPP',
                'deskripsi' => 'Pendapatan dari pembayaran SPP siswa',
                'tipe' => 'pendapatan',
                'kategori' => 'pendapatan',
                'is_active' => true
            ],
            [
                'kode' => '4102',
                'nama' => 'Pendapatan Uang Gedung',
                'deskripsi' => 'Pendapatan dari uang gedung',
                'tipe' => 'pendapatan',
                'kategori' => 'pendapatan',
                'is_active' => true
            ],
            [
                'kode' => '4103',
                'nama' => 'Pendapatan Seragam',
                'deskripsi' => 'Pendapatan dari penjualan seragam',
                'tipe' => 'pendapatan',
                'kategori' => 'pendapatan',
                'is_active' => true
            ],
            [
                'kode' => '4104',
                'nama' => 'Pendapatan Buku',
                'deskripsi' => 'Pendapatan dari penjualan buku',
                'tipe' => 'pendapatan',
                'kategori' => 'pendapatan',
                'is_active' => true
            ],
            [
                'kode' => '4201',
                'nama' => 'Pendapatan Lain-lain',
                'deskripsi' => 'Pendapatan lain di luar kegiatan utama',
                'tipe' => 'pendapatan',
                'kategori' => 'pendapatan',
                'is_active' => true
            ],

            // BEBAN (5xxx)
            [
                'kode' => '5101',
                'nama' => 'Beban Gaji Guru',
                'deskripsi' => 'Beban gaji dan tunjangan guru',
                'tipe' => 'beban',
                'kategori' => 'beban_operasional',
                'is_active' => true
            ],
            [
                'kode' => '5102',
                'nama' => 'Beban Gaji Karyawan',
                'deskripsi' => 'Beban gaji dan tunjangan karyawan',
                'tipe' => 'beban',
                'kategori' => 'beban_operasional',
                'is_active' => true
            ],
            [
                'kode' => '5103',
                'nama' => 'Beban Listrik',
                'deskripsi' => 'Beban tagihan listrik sekolah',
                'tipe' => 'beban',
                'kategori' => 'beban_operasional',
                'is_active' => true
            ],
            [
                'kode' => '5104',
                'nama' => 'Beban Air',
                'deskripsi' => 'Beban tagihan air sekolah',
                'tipe' => 'beban',
                'kategori' => 'beban_operasional',
                'is_active' => true
            ],
            [
                'kode' => '5105',
                'nama' => 'Beban Internet',
                'deskripsi' => 'Beban tagihan internet sekolah',
                'tipe' => 'beban',
                'kategori' => 'beban_operasional',
                'is_active' => true
            ],
            [
                'kode' => '5106',
                'nama' => 'Beban ATK',
                'deskripsi' => 'Beban pembelian alat tulis kantor',
                'tipe' => 'beban',
                'kategori' => 'beban_operasional',
                'is_active' => true
            ],
            [
                'kode' => '5107',
                'nama' => 'Beban Makanan',
                'deskripsi' => 'Beban makanan untuk kegiatan sekolah',
                'tipe' => 'beban',
                'kategori' => 'beban_operasional',
                'is_active' => true
            ],
            [
                'kode' => '5108',
                'nama' => 'Beban Transportasi',
                'deskripsi' => 'Beban transportasi untuk kegiatan sekolah',
                'tipe' => 'beban',
                'kategori' => 'beban_operasional',
                'is_active' => true
            ],
            [
                'kode' => '5201',
                'nama' => 'Beban Pajak',
                'deskripsi' => 'Beban pajak yang dibayar sekolah',
                'tipe' => 'beban',
                'kategori' => 'beban_non_operasional',
                'is_active' => true
            ],
            [
                'kode' => '5202',
                'nama' => 'Beban Bunga Bank',
                'deskripsi' => 'Beban bunga pinjaman bank',
                'tipe' => 'beban',
                'kategori' => 'beban_non_operasional',
                'is_active' => true
            ],
            [
                'kode' => '5203',
                'nama' => 'Beban Kerugian',
                'deskripsi' => 'Kerugian yang dialami sekolah',
                'tipe' => 'beban',
                'kategori' => 'beban_non_operasional',
                'is_active' => true
            ]
        ];

        foreach ($accountCodes as $accountCode) {
            AccountCode::create($accountCode);
        }

        $this->command->info('Account codes seeded successfully!');
    }
} 