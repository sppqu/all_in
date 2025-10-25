<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if data already exists
        if (DB::table('periods')->count() > 0) {
            $this->command->info('Period data already exists!');
            return;
        }

        // Get current year
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;

        // Create current academic year period (active)
        DB::table('periods')->insert([
            'period_start' => $currentYear,
            'period_end' => $nextYear,
            'period_status' => 1, // Active
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create previous academic year period (inactive)
        DB::table('periods')->insert([
            'period_start' => $currentYear - 1,
            'period_end' => $currentYear,
            'period_status' => 0, // Inactive
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create next academic year period (inactive)
        DB::table('periods')->insert([
            'period_start' => $nextYear,
            'period_end' => $nextYear + 1,
            'period_status' => 0, // Inactive
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->command->info('Period data seeded successfully!');
    }
}



