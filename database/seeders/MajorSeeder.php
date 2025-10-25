<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Major;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $majors = [
            ['majors_name' => 'IPA (Ilmu Pengetahuan Alam)'],
            ['majors_name' => 'IPS (Ilmu Pengetahuan Sosial)'],
            ['majors_name' => 'MIPA (Matematika dan Ilmu Pengetahuan Alam)'],
            ['majors_name' => 'Bahasa'],
            ['majors_name' => 'Agama'],
        ];

        foreach ($majors as $major) {
            Major::create($major);
        }
    }
}
