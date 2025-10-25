<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            AccountCodeSeeder::class,
            ClassSeeder::class,
            MajorSeeder::class,
            PeriodSeeder::class,
            SchoolProfileSeeder::class,
            SetupGatewaySeeder::class,
            UserSeeder::class,
            StudentSeeder::class,
            PosSeeder::class,
            PaymentSeeder::class,
            BillSeeder::class,
        ]);
    }
}
