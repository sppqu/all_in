<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first kas ID for sample data
        $kasId = \DB::table('kas')->value('id');
        
        if ($kasId) {
            \DB::table('payment_methods')->insert([
                [
                    'nama_metode' => 'TUNAI',
                    'kas_id' => $kasId,
                    'keterangan' => 'KAS DI TANGAN',
                    'status' => 'ON',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'nama_metode' => 'TRANSFER BANK',
                    'kas_id' => $kasId,
                    'keterangan' => 'Pembayaran via transfer bank',
                    'status' => 'ON',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'nama_metode' => 'E-WALLET',
                    'kas_id' => $kasId,
                    'keterangan' => 'Pembayaran via e-wallet (OVO, DANA, dll)',
                    'status' => 'ON',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
    }
}
