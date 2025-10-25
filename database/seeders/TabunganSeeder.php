<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TabunganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active students
        $students = DB::table('students')->where('student_status', 1)->get();
        
        foreach ($students as $student) {
            // Create tabungan record for each student
            $tabunganId = DB::table('tabungan')->insertGetId([
                'student_student_id' => $student->student_id,
                'user_user_id' => 1, // Default admin user
                'saldo' => rand(0, 1000000), // Random saldo between 0 and 1M
                'tabungan_input_date' => now(),
                'tabungan_last_update' => now(),
            ]);
            
            // Create some sample log tabungan records
            $sampleTransactions = [
                [
                    'kredit' => 50000,
                    'debit' => 0,
                    'keterangan' => 'Setoran awal',
                    'log_tabungan_input_date' => now()->subDays(30)
                ],
                [
                    'kredit' => 75000,
                    'debit' => 0,
                    'keterangan' => 'Setoran untuk keperluan sekolah',
                    'log_tabungan_input_date' => now()->subDays(20)
                ],
                [
                    'kredit' => 0,
                    'debit' => 25000,
                    'keterangan' => 'Penarikan untuk uang jajan',
                    'log_tabungan_input_date' => now()->subDays(10)
                ],
                [
                    'kredit' => 100000,
                    'debit' => 0,
                    'keterangan' => 'Setoran dari orang tua',
                    'log_tabungan_input_date' => now()->subDays(5)
                ]
            ];
            
            foreach ($sampleTransactions as $transaction) {
                DB::table('log_tabungan')->insert([
                    'student_student_id' => $student->student_id,
                    'tabungan_tabungan_id' => $tabunganId,
                    'kredit' => 0, // kredit field removed
                    'debit' => $transaction['debit'],
                    'keterangan' => $transaction['keterangan'],
                    'log_tabungan_input_date' => $transaction['log_tabungan_input_date'],
                    'log_tabungan_last_update' => $transaction['log_tabungan_input_date']
                ]);
            }
        }
        
        $this->command->info('Tabungan data seeded successfully!');
    }
} 