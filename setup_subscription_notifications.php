<?php
/**
 * Setup Script untuk Aktivasi Sistem Notifikasi Berlangganan
 * 
 * Script ini akan:
 * 1. Verifikasi semua file diperlukan ada
 * 2. Test database connection
 * 3. Test command execution
 * 4. Membuat notifikasi test
 * 5. Memberikan instruksi setup cron job
 * 
 * Cara menjalankan:
 * php setup_subscription_notifications.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Warna untuk output
function colorize($text, $color = 'green') {
    $colors = [
        'green' => "\033[32m",
        'red' => "\033[31m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m"
    ];
    
    return $colors[$color] . $text . $colors['reset'];
}

function success($message) {
    echo colorize("✓ ", 'green') . $message . "\n";
}

function error($message) {
    echo colorize("✗ ", 'red') . $message . "\n";
}

function info($message) {
    echo colorize("ℹ ", 'blue') . $message . "\n";
}

function warning($message) {
    echo colorize("⚠ ", 'yellow') . $message . "\n";
}

function title($message) {
    echo "\n" . colorize(str_repeat("=", 60), 'blue') . "\n";
    echo colorize($message, 'blue') . "\n";
    echo colorize(str_repeat("=", 60), 'blue') . "\n\n";
}

// ============================================================================
// START SETUP
// ============================================================================

title("SETUP SISTEM NOTIFIKASI BERLANGGANAN");

// ============================================================================
// 1. Verifikasi File
// ============================================================================
title("1. VERIFIKASI FILE");

$requiredFiles = [
    'app/Console/Commands/CheckSubscriptionStatus.php',
    'app/Http/Middleware/CheckSubscription.php',
    'app/Providers/NotificationServiceProvider.php',
    'app/Models/Notification.php',
    'app/Console/Kernel.php',
];

$allFilesExist = true;
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        success("File ditemukan: $file");
    } else {
        error("File tidak ditemukan: $file");
        $allFilesExist = false;
    }
}

if (!$allFilesExist) {
    error("Beberapa file diperlukan tidak ditemukan!");
    exit(1);
}

// ============================================================================
// 2. Test Database Connection
// ============================================================================
title("2. TEST DATABASE CONNECTION");

try {
    \DB::connection()->getPdo();
    success("Database connection berhasil");
    
    // Check if notifications table exists
    if (\Schema::hasTable('notifications')) {
        success("Table 'notifications' ditemukan");
    } else {
        error("Table 'notifications' tidak ditemukan!");
        warning("Jalankan: php artisan migrate");
        exit(1);
    }
    
    // Check if subscriptions table exists
    if (\Schema::hasTable('subscriptions')) {
        success("Table 'subscriptions' ditemukan");
    } else {
        warning("Table 'subscriptions' tidak ditemukan (opsional)");
    }
    
    // Check if user_addons table exists
    if (\Schema::hasTable('user_addons')) {
        success("Table 'user_addons' ditemukan");
    } else {
        warning("Table 'user_addons' tidak ditemukan (opsional)");
    }
    
} catch (\Exception $e) {
    error("Database connection gagal: " . $e->getMessage());
    exit(1);
}

// ============================================================================
// 3. Test Command
// ============================================================================
title("3. TEST COMMAND EXECUTION");

try {
    info("Menjalankan command: subscriptions:check-status");
    $exitCode = $kernel->call('subscriptions:check-status');
    
    if ($exitCode === 0) {
        success("Command berhasil dijalankan (Exit Code: 0)");
    } else {
        warning("Command selesai dengan exit code: $exitCode");
    }
} catch (\Exception $e) {
    error("Error menjalankan command: " . $e->getMessage());
}

// ============================================================================
// 4. Check Notifications
// ============================================================================
title("4. CHECK NOTIFIKASI");

$notificationCount = \DB::table('notifications')
    ->whereIn('type', ['subscription_expiring', 'subscription_expired'])
    ->count();

if ($notificationCount > 0) {
    success("Ditemukan $notificationCount notifikasi berlangganan di database");
    
    $unreadCount = \DB::table('notifications')
        ->whereIn('type', ['subscription_expiring', 'subscription_expired'])
        ->where('is_read', false)
        ->count();
    
    info("Notifikasi belum dibaca: $unreadCount");
} else {
    warning("Tidak ada notifikasi berlangganan di database");
    info("Notifikasi akan dibuat otomatis saat command berjalan dan ada berlangganan yang akan expired");
}

// ============================================================================
// 5. Check Subscriptions
// ============================================================================
title("5. CHECK BERLANGGANAN");

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

info("Total berlangganan aktif: $activeSubscriptions");

if ($expiringSubscriptions > 0) {
    warning("Berlangganan akan habis dalam 30 hari: $expiringSubscriptions");
    info("Notifikasi akan dibuat untuk berlangganan ini");
} else {
    info("Tidak ada berlangganan yang akan habis dalam 30 hari: $expiringSubscriptions");
}

if ($expiredSubscriptions > 0) {
    warning("Berlangganan yang sudah habis: $expiredSubscriptions");
} else {
    info("Tidak ada berlangganan yang sudah habis: $expiredSubscriptions");
}

// ============================================================================
// 6. Check Middleware
// ============================================================================
title("6. CHECK MIDDLEWARE");

$middlewareFile = file_get_contents('app/Http/Middleware/CheckSubscription.php');

if (strpos($middlewareFile, '// DISABLED: Allow all access without subscription check') !== false) {
    error("Middleware masih dalam mode DISABLED!");
    warning("Edit file: app/Http/Middleware/CheckSubscription.php");
} else {
    success("Middleware CheckSubscription sudah aktif");
}

// ============================================================================
// 7. Check Scheduler
// ============================================================================
title("7. CHECK SCHEDULER");

$kernelFile = file_get_contents('app/Console/Kernel.php');

if (strpos($kernelFile, "subscriptions:check-status") !== false) {
    success("Command sudah terdaftar di scheduler");
    info("Command akan berjalan:");
    info("  - Setiap hari jam 09:00");
    info("  - Setiap 6 jam");
} else {
    error("Command belum terdaftar di scheduler");
    warning("Edit file: app/Console/Kernel.php");
}

// ============================================================================
// 8. Instruksi Cron Job
// ============================================================================
title("8. SETUP CRON JOB (PENTING!)");

warning("Agar scheduler berjalan otomatis, setup cron job berikut:");
echo "\n";
echo colorize("# Edit crontab", 'yellow') . "\n";
echo "crontab -e\n\n";
echo colorize("# Tambahkan baris ini:", 'yellow') . "\n";
echo "* * * * * cd " . base_path() . " && php artisan schedule:run >> /dev/null 2>&1\n\n";

// ============================================================================
// 9. Test Routes
// ============================================================================
title("9. CHECK ROUTES");

info("Pastikan routes berikut tersedia:");
echo "  - GET  /manage/notifications\n";
echo "  - PATCH /manage/notifications/{id}/read\n";
echo "  - GET  /manage/subscription/plans\n\n";

info("Test dengan command:");
echo "  php artisan route:list | grep notifications\n\n";

// ============================================================================
// SUMMARY
// ============================================================================
title("SUMMARY DAN LANGKAH SELANJUTNYA");

success("Setup selesai! Sistem notifikasi berlangganan sudah aktif.");
echo "\n";

info("Langkah selanjutnya:");
echo "1. Setup cron job di server (lihat instruksi di atas)\n";
echo "2. Test di browser dengan login sebagai user yang akan expired\n";
echo "3. Jalankan manual test: php test_subscription_notifications.php\n";
echo "4. Monitor log: tail -f storage/logs/subscription-check.log\n";
echo "\n";

info("Dokumentasi lengkap:");
echo "Baca file: SUBSCRIPTION_NOTIFICATIONS.md\n";
echo "\n";

info("Command berguna:");
echo "  - Test command: php artisan subscriptions:check-status\n";
echo "  - List schedule: php artisan schedule:list\n";
echo "  - Run schedule: php artisan schedule:run\n";
echo "  - Check routes: php artisan route:list | grep notifications\n";
echo "\n";

title("SETUP COMPLETED!");

