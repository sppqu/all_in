<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Jalankan command expire pembayaran pending setiap menit
        $schedule->command('payments:expire-pending')
                ->everyMinute()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/payment-expire.log'));
        
        // Cek status berlangganan setiap hari pukul 09:00 pagi
        $schedule->command('subscriptions:check-status')
                ->dailyAt('09:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/subscription-check.log'));
        
        // Cek status berlangganan setiap 6 jam untuk notifikasi kritis
        $schedule->command('subscriptions:check-status')
                ->everySixHours()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/subscription-check.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 