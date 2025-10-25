<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolProfile;

class SchoolProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SchoolProfile::create([
            'jenjang' => 'SMK',
            'nama_sekolah' => 'SMK SPPQU DIGITAL PAYMENT',
            'alamat' => 'Jl. Bledak Anggur IV, No.22, Tlogosari Kulon, Kota Semarang',
            'no_telp' => '082188497818',
            'logo_sekolah' => 'logos/wygvrxDY3Pl1yCnS2knXOg8OG8PCCV7Mpf5WuiCV.jpg',
        ]);
    }
} 