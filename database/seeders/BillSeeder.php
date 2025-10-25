<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get students, periods, and pos_pembayaran
        $students = DB::table('students')->get();
        $periods = DB::table('periods')->get();
        $posPembayaran = DB::table('pos_pembayaran')->get();
        
        if ($students->count() > 0 && $periods->count() > 0 && $posPembayaran->count() > 0) {
            $period = $periods->first();
            $pos = $posPembayaran->first();
            
            // Create payment records first
            $paymentId = DB::table('payment')->insertGetId([
                'payment_type' => 'BULAN',
                'pos_pos_id' => $pos->pos_id,
                'period_period_id' => $period->period_id,
                'payment_input_date' => now(),
                'payment_last_update' => now()
            ]);
            
            $paymentBebasId = DB::table('payment')->insertGetId([
                'payment_type' => 'BEBAS',
                'pos_pos_id' => $pos->pos_id,
                'period_period_id' => $period->period_id,
                'payment_input_date' => now(),
                'payment_last_update' => now()
            ]);
            
            // Create bulan (monthly) bills for each student
            foreach ($students as $student) {
                $months = [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];
                
                foreach ($months as $index => $month) {
                    // Some bills are paid, some are unpaid
                    $isPaid = rand(0, 1) === 1;
                    
                    DB::table('bulan')->insert([
                        'student_student_id' => $student->student_id,
                        'payment_payment_id' => $paymentId,
                        'month_month_id' => $index + 1, // Using index as month_id
                        'bulan_bill' => 200000,
                        'bulan_date_pay' => $isPaid ? now()->subDays(rand(1, 30)) : null,
                        'bulan_number_pay' => $isPaid ? 'BUL-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) : null,
                        'bulan_input_date' => now(),
                        'bulan_last_update' => now()
                    ]);
                }
                
                // Create bebas (non-monthly) bills for each student
                $bebasTypes = ['Uang Gedung', 'Uang Seragam', 'Uang Buku'];
                
                foreach ($bebasTypes as $bebasType) {
                    $isPaid = rand(0, 1) === 1;
                    
                    DB::table('bebas')->insert([
                        'student_student_id' => $student->student_id,
                        'payment_payment_id' => $paymentBebasId,
                        'bebas_bill' => rand(300000, 800000),
                        'bebas_desc' => $bebasType,
                        'bebas_input_date' => now(),
                        'bebas_last_update' => now()
                    ]);
                }
            }
        }
        
        $this->command->info('Bill data seeded successfully!');
    }
} 