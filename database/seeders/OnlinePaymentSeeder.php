<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OnlinePaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil beberapa siswa untuk testing
        $students = DB::table('students')->limit(5)->get();
        
        if ($students->count() > 0) {
            foreach ($students as $student) {
                // Buat beberapa pembayaran online untuk testing
                $paymentMethods = ['bank_transfer', 'credit_card', 'e_wallet'];
                $statuses = ['success', 'pending', 'failed'];
                $billTypes = ['bulanan', 'bebas'];
                
                for ($i = 0; $i < 3; $i++) {
                    $paymentNumber = 'ONLINE-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    $amount = rand(100000, 500000);
                    $method = $paymentMethods[array_rand($paymentMethods)];
                    $status = $statuses[array_rand($statuses)];
                    $billType = $billTypes[array_rand($billTypes)];
                    
                    DB::table('online_payments')->insert([
                        'payment_number' => $paymentNumber,
                        'student_id' => $student->student_id,
                        'bill_type' => $billType,
                        'bill_id' => rand(1, 10),
                        'amount' => $amount,
                        'payment_method' => $method,
                        'status' => $status,
                        'payment_details' => json_encode([
                            'gateway' => 'test_gateway',
                            'transaction_id' => 'TXN' . rand(100000, 999999),
                            'payment_channel' => $method
                        ]),
                        'gateway_transaction_id' => 'TXN' . rand(100000, 999999),
                        'paid_at' => $status === 'success' ? now() : null,
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now()->subDays(rand(1, 30))
                    ]);
                }
            }
        }
        
        $this->command->info('Online Payment test data seeded successfully!');
    }
}
