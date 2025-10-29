<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use Carbon\Carbon;

class CheckSubscriptionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check subscription status and create notifications for expiring and expired subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking subscription status...');

        $notificationsCreated = 0;
        $now = Carbon::now();

        // Get all active subscriptions from subscriptions table (Tripay)
        $subscriptions = DB::table('subscriptions')
            ->where('status', 'active')
            ->get();

        foreach ($subscriptions as $subscription) {
            $userId = $subscription->user_id;
            $expiresAt = Carbon::parse($subscription->expires_at);
            $daysUntilExpiry = $now->diffInDays($expiresAt, false);

            // Check if already expired
            if ($expiresAt <= $now) {
                $this->createExpiredNotification($userId, $subscription);
                $notificationsCreated++;
                
                // Update subscription status to expired
                DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update(['status' => 'expired']);
                
                $this->warn("Subscription expired for user ID: {$userId}");
                continue;
            }

            // Check for expiring subscriptions at specific intervals
            if ($daysUntilExpiry <= 30 && $daysUntilExpiry > 0) {
                // Create notification based on days left
                if ($this->shouldCreateNotification($userId, $daysUntilExpiry, 'subscription_expiring')) {
                    $this->createExpiringNotification($userId, $daysUntilExpiry, $subscription);
                    $notificationsCreated++;
                    $this->info("Created expiring notification for user ID: {$userId} ({$daysUntilExpiry} days left)");
                }
            }
        }

        // Check legacy user_addons table for recurring subscriptions
        $legacySubscriptions = DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.status', 'active')
            ->where('addons.type', 'recurring')
            ->select('user_addons.*', 'addons.name as addon_name')
            ->get();

        foreach ($legacySubscriptions as $subscription) {
            $userId = $subscription->user_id;
            $expiresAt = Carbon::parse($subscription->expires_at);
            $daysUntilExpiry = $now->diffInDays($expiresAt, false);

            // Check if already expired
            if ($expiresAt <= $now) {
                $this->createExpiredNotification($userId, $subscription);
                $notificationsCreated++;
                
                // Update subscription status to expired
                DB::table('user_addons')
                    ->where('id', $subscription->id)
                    ->update(['status' => 'expired']);
                
                $this->warn("Legacy subscription expired for user ID: {$userId}");
                continue;
            }

            // Check for expiring subscriptions
            if ($daysUntilExpiry <= 30 && $daysUntilExpiry > 0) {
                if ($this->shouldCreateNotification($userId, $daysUntilExpiry, 'subscription_expiring')) {
                    $this->createExpiringNotification($userId, $daysUntilExpiry, $subscription);
                    $notificationsCreated++;
                    $this->info("Created expiring notification (legacy) for user ID: {$userId} ({$daysUntilExpiry} days left)");
                }
            }
        }

        $this->info("Subscription check completed. Created {$notificationsCreated} notifications.");
        return 0;
    }

    /**
     * Check if notification should be created (avoid duplicates within 24 hours)
     */
    private function shouldCreateNotification($userId, $daysLeft, $type)
    {
        // Check if similar notification was created in last 24 hours
        $existingNotification = Notification::where('data->user_id', $userId)
            ->where('type', $type)
            ->where('data->days_left', $daysLeft)
            ->where('created_at', '>', Carbon::now()->subDay())
            ->exists();

        return !$existingNotification;
    }

    /**
     * Create notification for expiring subscription
     */
    private function createExpiringNotification($userId, $daysLeft, $subscription)
    {
        $roundedDays = round($daysLeft);
        
        // Determine severity based on days left
        if ($roundedDays <= 1) {
            $color = 'danger';
            $icon = 'fa-exclamation-triangle';
            $title = 'Berlangganan Akan Berakhir Besok!';
            $message = 'Berlangganan Anda akan berakhir dalam 1 hari! Perpanjang sekarang untuk menghindari gangguan layanan.';
        } elseif ($roundedDays <= 3) {
            $color = 'danger';
            $icon = 'fa-exclamation-circle';
            $title = 'Berlangganan Segera Berakhir';
            $message = "Berlangganan Anda akan berakhir dalam {$roundedDays} hari. Perpanjang sekarang untuk menghindari gangguan layanan.";
        } elseif ($roundedDays <= 7) {
            $color = 'warning';
            $icon = 'fa-clock';
            $title = 'Berlangganan Akan Berakhir';
            $message = "Berlangganan Anda akan berakhir dalam {$roundedDays} hari. Segera perpanjang berlangganan untuk menghindari gangguan layanan.";
        } elseif ($roundedDays <= 14) {
            $color = 'warning';
            $icon = 'fa-calendar-alt';
            $title = 'Pengingat Berlangganan';
            $message = "Berlangganan Anda akan berakhir dalam {$roundedDays} hari. Pertimbangkan untuk memperpanjang berlangganan.";
        } else {
            $color = 'info';
            $icon = 'fa-bell';
            $title = 'Pengingat Berlangganan (H-30)';
            $message = "Berlangganan Anda akan berakhir dalam {$roundedDays} hari. Ini adalah pengingat awal untuk memperpanjang berlangganan.";
        }

        Notification::create([
            'type' => 'subscription_expiring',
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'data' => [
                'user_id' => $userId,
                'days_left' => $roundedDays,
                'expires_at' => isset($subscription->expires_at) ? $subscription->expires_at : null,
                'subscription_id' => isset($subscription->id) ? $subscription->id : null,
            ]
        ]);
    }

    /**
     * Create notification for expired subscription
     */
    private function createExpiredNotification($userId, $subscription)
    {
        // Check if expired notification already exists (avoid duplicates)
        $existingNotification = Notification::where('data->user_id', $userId)
            ->where('type', 'subscription_expired')
            ->where('created_at', '>', Carbon::now()->subDay())
            ->exists();

        if ($existingNotification) {
            return;
        }

        Notification::create([
            'type' => 'subscription_expired',
            'title' => 'Berlangganan Telah Berakhir',
            'message' => 'Berlangganan Anda telah berakhir. Silakan perpanjang berlangganan untuk mengakses fitur aplikasi.',
            'icon' => 'fa-times-circle',
            'color' => 'danger',
            'data' => [
                'user_id' => $userId,
                'expired_at' => isset($subscription->expires_at) ? $subscription->expires_at : null,
                'subscription_id' => isset($subscription->id) ? $subscription->id : null,
            ]
        ]);
    }
}

