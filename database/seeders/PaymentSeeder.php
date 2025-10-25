<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if data already exists
        if (DB::table('payment')->count() > 0) {
            $this->command->info('Payment data already exists!');
            return;
        }
        
        // Get all periods and pos pembayaran
        $periods = DB::table('periods')->get();
        $posPembayaran = DB::table('pos_pembayaran')->get();
        
        if ($periods->count() == 0) {
            $this->command->error('No periods found. Please run PeriodSeeder first.');
            return;
        }
        
        if ($posPembayaran->count() == 0) {
            $this->command->error('No pos pembayaran found. Please run PosSeeder first.');
            return;
        }
        
        $period = $periods->first();
        
        // Create payment records for each pos
        foreach ($posPembayaran as $pos) {
            // Create BULAN payment record
            DB::table('payment')->insert([
                'payment_type' => 'BULAN',
                'pos_pos_id' => $pos->pos_id,
                'period_period_id' => $period->period_id,
                'payment_input_date' => now(),
                'payment_last_update' => now()
            ]);
            
            // Create BEBAS payment record
            DB::table('payment')->insert([
                'payment_type' => 'BEBAS',
                'pos_pos_id' => $pos->pos_id,
                'period_period_id' => $period->period_id,
                'payment_input_date' => now(),
                'payment_last_update' => now()
            ]);
        }
        
        $this->command->info('Payment records created successfully for all pos pembayaran!');
    }
}
