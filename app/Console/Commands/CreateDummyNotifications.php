<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;

class CreateDummyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:create-dummy {--count=5 : Number of dummy notifications to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create dummy notifications for testing the notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->option('count');
        $this->info("Creating {$count} dummy notifications...");

        $dummyData = [
            [
                'type' => 'payment_online_new',
                'title' => 'Pembayaran Online Baru',
                'message' => 'Pembayaran Bulanan sebesar Rp 500.000 dari Ahmad Fadillah via Payment Gateway menunggu verifikasi.',
                'icon' => 'fa-credit-card',
                'color' => 'warning'
            ],
            [
                'type' => 'payment_online_success',
                'title' => 'Pembayaran Online Berhasil',
                'message' => 'Pembayaran Bebas sebesar Rp 300.000 dari Siti Nurhaliza via Transfer Bank telah berhasil.',
                'icon' => 'fa-check-circle',
                'color' => 'success'
            ],
            [
                'type' => 'payment_online_rejected',
                'title' => 'Pembayaran Online Ditolak',
                'message' => 'Pembayaran Tabungan sebesar Rp 100.000 dari Budi Santoso via Payment Gateway telah ditolak.',
                'icon' => 'fa-times-circle',
                'color' => 'danger'
            ],
            [
                'type' => 'payment_online_new',
                'title' => 'Pembayaran Online Baru',
                'message' => 'Pembayaran Bulanan sebesar Rp 450.000 dari Dewi Sartika via Transfer Bank menunggu verifikasi.',
                'icon' => 'fa-credit-card',
                'color' => 'warning'
            ],
            [
                'type' => 'payment_online_success',
                'title' => 'Pembayaran Online Berhasil',
                'message' => 'Pembayaran Tabungan sebesar Rp 75.000 dari Muhammad Rizki via Payment Gateway telah berhasil.',
                'icon' => 'fa-check-circle',
                'color' => 'success'
            ]
        ];

        $createdCount = 0;
        for ($i = 0; $i < min($count, count($dummyData)); $i++) {
            $data = $dummyData[$i];
            
            Notification::create([
                'type' => $data['type'],
                'title' => $data['title'],
                'message' => $data['message'],
                'icon' => $data['icon'],
                'color' => $data['color'],
                'data' => [
                    'transfer_id' => rand(1000, 9999),
                    'student_id' => rand(1, 100),
                    'student_name' => 'Siswa Test ' . ($i + 1),
                    'amount' => rand(50000, 500000),
                    'payment_type' => ['Bulanan', 'Bebas', 'Tabungan'][rand(0, 2)],
                    'payment_method' => ['Transfer Bank', 'Payment Gateway'][rand(0, 1)],
                    'status' => $data['type'] === 'payment_online_success' ? 1 : ($data['type'] === 'payment_online_rejected' ? 2 : 0)
                ]
            ]);

            $createdCount++;
            $this->line("Created notification: {$data['title']}");
        }

        $this->info("Successfully created {$createdCount} dummy notifications.");
        $this->info("You can now test the notification system in the admin dashboard!");
    }
}
