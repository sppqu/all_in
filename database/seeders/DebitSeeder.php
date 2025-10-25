<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Debit;
use App\Models\User;
use Carbon\Carbon;

class DebitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user as default
        $user = User::first();
        
        if (!$user) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        $sampleData = [
            [
                'debit_date' => Carbon::now()->subDays(5),
                'debit_desc' => 'Penerimaan SPP Bulanan Januari 2025',
                'debit_value' => 500000,
                'user_user_id' => $user->id,
                'no_ref' => 'SPP-001-2025',
                'kode_akun' => '4.1000',
                'pajak' => 0,
                'total' => 500000,
            ],
            [
                'debit_date' => Carbon::now()->subDays(4),
                'debit_desc' => 'Penerimaan Uang Gedung',
                'debit_value' => 1000000,
                'user_user_id' => $user->id,
                'no_ref' => 'UG-001-2025',
                'kode_akun' => '4.2000',
                'pajak' => 0,
                'total' => 1000000,
            ],
            [
                'debit_date' => Carbon::now()->subDays(3),
                'debit_desc' => 'Penerimaan Uang Praktikum Komputer',
                'debit_value' => 250000,
                'user_user_id' => $user->id,
                'no_ref' => 'PRK-001-2025',
                'kode_akun' => '4.3000',
                'pajak' => 0,
                'total' => 250000,
            ],
            [
                'debit_date' => Carbon::now()->subDays(2),
                'debit_desc' => 'Penerimaan Donasi Sponsor',
                'debit_value' => 2000000,
                'user_user_id' => $user->id,
                'no_ref' => 'DON-001-2025',
                'kode_akun' => '4.4000',
                'pajak' => 0,
                'total' => 2000000,
            ],
            [
                'debit_date' => Carbon::now()->subDays(1),
                'debit_desc' => 'Penerimaan Bunga Bank',
                'debit_value' => 50000,
                'user_user_id' => $user->id,
                'no_ref' => 'BB-001-2025',
                'kode_akun' => '4.5000',
                'pajak' => 0,
                'total' => 50000,
            ],
            [
                'debit_date' => Carbon::now(),
                'debit_desc' => 'Penerimaan Sewa Aula',
                'debit_value' => 750000,
                'user_user_id' => $user->id,
                'no_ref' => 'SEWA-001-2025',
                'kode_akun' => '4.4000',
                'pajak' => 11,
                'total' => 832500,
            ],
        ];

        foreach ($sampleData as $data) {
            Debit::create($data);
        }

        $this->command->info('Debit seeder completed successfully!');
    }
}
