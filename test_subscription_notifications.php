<?php
/**
 * Script untuk menguji notifikasi berlangganan
 * 
 * Script ini akan menjalankan command untuk memeriksa status berlangganan
 * dan membuat notifikasi untuk berlangganan yang akan habis atau sudah habis.
 * 
 * Cara menjalankan:
 * php test_subscription_notifications.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "==============================================\n";
echo "Testing Subscription Notification System\n";
echo "==============================================\n\n";

// Jalankan command untuk check subscription status
echo "Running subscriptions:check-status command...\n\n";
$exitCode = $kernel->call('subscriptions:check-status');

echo "\n==============================================\n";
echo "Command completed with exit code: {$exitCode}\n";
echo "==============================================\n\n";

// Tampilkan notifikasi yang dibuat
echo "Checking notifications in database...\n\n";

$notifications = \DB::table('notifications')
    ->whereIn('type', ['subscription_expiring', 'subscription_expired'])
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();

if ($notifications->isEmpty()) {
    echo "No subscription notifications found in database.\n";
} else {
    echo "Recent Subscription Notifications:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($notifications as $notification) {
        echo "ID: {$notification->id}\n";
        echo "Type: {$notification->type}\n";
        echo "Title: {$notification->title}\n";
        echo "Message: {$notification->message}\n";
        echo "Color: {$notification->color}\n";
        echo "Read: " . ($notification->is_read ? 'Yes' : 'No') . "\n";
        echo "Created: {$notification->created_at}\n";
        echo str_repeat("-", 80) . "\n";
    }
}

// Tampilkan statistik berlangganan
echo "\nSubscription Statistics:\n";
echo str_repeat("-", 80) . "\n";

$activeSubscriptions = \DB::table('subscriptions')
    ->where('status', 'active')
    ->where('expires_at', '>', now())
    ->count();

$expiringSubscriptions = \DB::table('subscriptions')
    ->where('status', 'active')
    ->where('expires_at', '<=', now()->addDays(30))
    ->where('expires_at', '>', now())
    ->count();

$expiredSubscriptions = \DB::table('subscriptions')
    ->where('expires_at', '<=', now())
    ->count();

echo "Active subscriptions: {$activeSubscriptions}\n";
echo "Expiring within 30 days: {$expiringSubscriptions}\n";
echo "Expired subscriptions: {$expiredSubscriptions}\n";

echo "\n==============================================\n";
echo "Test completed!\n";
echo "==============================================\n";

