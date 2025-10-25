<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassModel;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            ['class_name' => 'X IPA'],
            ['class_name' => 'X IPS'],
            ['class_name' => 'X IPA 1'],
            ['class_name' => 'X IPA 2'],
            ['class_name' => 'XI IPS 1'],
            ['class_name' => 'XI IPS 2'],
            ['class_name' => 'XII MIPA 1'],
        ];

        foreach ($classes as $class) {
            ClassModel::create($class);
        }
    }
} 