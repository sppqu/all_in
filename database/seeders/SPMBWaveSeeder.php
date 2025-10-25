<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SPMBWave;
use Carbon\Carbon;

class SPMBWaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample waves
        $waves = [
            [
                'name' => 'Gelombang 1',
                'description' => 'Gelombang pendaftaran pertama untuk tahun akademik 2024/2025',
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(30),
                'registration_fee' => 150000,
                'spmb_fee' => 500000,
                'quota' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Gelombang 2',
                'description' => 'Gelombang pendaftaran kedua untuk tahun akademik 2024/2025',
                'start_date' => Carbon::now()->addDays(35),
                'end_date' => Carbon::now()->addDays(60),
                'registration_fee' => 200000,
                'spmb_fee' => 600000,
                'quota' => 80,
                'is_active' => true,
            ],
            [
                'name' => 'Gelombang 3',
                'description' => 'Gelombang pendaftaran ketiga untuk tahun akademik 2024/2025',
                'start_date' => Carbon::now()->addDays(65),
                'end_date' => Carbon::now()->addDays(90),
                'registration_fee' => 250000,
                'spmb_fee' => 700000,
                'quota' => 50,
                'is_active' => true,
            ]
        ];

        foreach ($waves as $waveData) {
            SPMBWave::create($waveData);
        }
    }
}
