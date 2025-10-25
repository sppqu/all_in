<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;

class CreateNotificationsFromExistingTransfers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:create-from-transfers {--days=7 : Number of days to look back}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create notifications from existing online payment transfers for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Creating notifications from transfers in the last {$days} days...");

        // Ambil transaksi online yang sudah ada dalam X hari terakhir
        $transfers = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->whereNotNull('t.checkout_url')
            ->where('t.created_at', '>=', now()->subDays($days))
            ->select(
                't.transfer_id',
                't.student_id',
                't.confirm_pay as total_amount',
                't.status',
                't.payment_method',
                't.bill_type',
                't.created_at',
                's.student_full_name as student_name'
            )
            ->get();

        if ($transfers->isEmpty()) {
            $this->warn('No online transfers found in the specified period.');
            return;
        }

        $this->info("Found {$transfers->count()} online transfers.");

        $createdCount = 0;
        foreach ($transfers as $transfer) {
            // Cek apakah sudah ada notifikasi untuk transfer ini
            $existingNotification = Notification::where('type', 'payment_online_new')
                ->whereJsonContains('data->transfer_id', $transfer->transfer_id)
                ->first();

            if ($existingNotification) {
                $this->line("Notification already exists for transfer {$transfer->transfer_id}");
                continue;
            }

            // Tentukan jenis pembayaran
            $paymentType = $this->getPaymentTypeText($transfer->bill_type);
            
            // Tentukan metode pembayaran
            $paymentMethod = $this->getPaymentMethodText($transfer);
            
            // Tentukan status dan warna
            $status = $transfer->status;
            $icon = $status == 1 ? 'fa-check-circle' : ($status == 2 ? 'fa-times-circle' : 'fa-credit-card');
            $color = $status == 1 ? 'success' : ($status == 2 ? 'danger' : 'warning');
            $type = $status == 1 ? 'payment_online_success' : ($status == 2 ? 'payment_online_rejected' : 'payment_online_new');
            
            // Buat notifikasi
            Notification::create([
                'type' => $type,
                'title' => $status == 1 ? 'Pembayaran Online Berhasil' : ($status == 2 ? 'Pembayaran Online Ditolak' : 'Pembayaran Online Baru'),
                'message' => "Pembayaran {$paymentType} sebesar Rp " . number_format($transfer->total_amount) . " dari {$transfer->student_name} via {$paymentMethod} " . 
                            ($status == 1 ? 'telah berhasil.' : ($status == 2 ? 'telah ditolak.' : 'menunggu verifikasi.')),
                'icon' => $icon,
                'color' => $color,
                'data' => [
                    'transfer_id' => $transfer->transfer_id,
                    'student_id' => $transfer->student_id,
                    'student_name' => $transfer->student_name,
                    'amount' => $transfer->total_amount,
                    'payment_type' => $paymentType,
                    'payment_method' => $paymentMethod,
                    'status' => $status
                ]
            ]);

            $createdCount++;
            $this->line("Created notification for transfer {$transfer->transfer_id} - {$transfer->student_name}");
        }

        $this->info("Successfully created {$createdCount} notifications from existing transfers.");
    }

    /**
     * Tentukan jenis pembayaran berdasarkan bill_type
     */
    private function getPaymentTypeText($billType): string
    {
        switch ($billType) {
            case 1:
                return 'Bulanan';
            case 2:
                return 'Bebas';
            case 3:
                return 'Tabungan';
            default:
                return 'Tidak Diketahui';
        }
    }

    /**
     * Tentukan metode pembayaran
     */
    private function getPaymentMethodText($transfer): string
    {
        if (!empty($transfer->payment_method)) {
            switch ($transfer->payment_method) {
                case 'bank_transfer':
                case 'manual_transfer':
                    return 'Transfer Bank';
                case 'midtrans':
                case 'credit_card':
                case 'e_wallet':
                case 'online_payment':
                    return 'Payment Gateway';
                case 'cash':
                    return 'Tunai';
            }
        }
        
        return 'Online Payment';
    }
}
