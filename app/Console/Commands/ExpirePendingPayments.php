<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpirePendingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:expire-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengubah status pembayaran Midtrans pending menjadi gagal setelah 23:59 menit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses expire pembayaran Midtrans pending...');

        // Mengambil pembayaran Midtrans pending yang sudah lebih dari 23:59 menit
        $expiredPayments = DB::table('transfer')
            ->where('status', 'pending') // Status pending untuk Midtrans
            ->where('payment_type', 'online') // Payment gateway
            ->where('payment_method', 'midtrans') // Khusus Midtrans
            ->where('created_at', '<', Carbon::now()->subMinutes(1439)) // 23:59 menit = 1439 menit
            ->get();

        $count = 0;

        foreach ($expiredPayments as $payment) {
            // Update status menjadi failed
            DB::table('transfer')
                ->where('transfer_id', $payment->transfer_id)
                ->update([
                    'status' => 'failed',
                    'updated_at' => Carbon::now()
                ]);

            $count++;
            
            $this->info("Pembayaran Midtrans ID: {$payment->transfer_id} - Status diubah menjadi failed");
        }

        $this->info("Selesai! {$count} pembayaran Midtrans pending telah diubah menjadi failed.");
        
        return 0;
    }
} 