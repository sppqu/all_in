<?php
/**
 * VPS Web Fix - Clear Cache and Regenerate Autoload
 * Access via browser: https://srx.sppqu.my.id/vps_web_fix.php
 */

// Security check
$secret = $_GET['secret'] ?? '';
if ($secret !== 'sppqu2024fix') {
    die('Access Denied. Use: ?secret=sppqu2024fix');
}

// Set time limit
set_time_limit(300);

echo "<pre>";
echo "================================================\n";
echo "🔧 VPS Web Fix - Cleaning Routes Cache\n";
echo "================================================\n\n";

// Step 1: Clear Laravel caches
echo "🗑️  Step 1: Clearing Laravel caches...\n";
$commands = [
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan route:clear',
    'php artisan view:clear',
    'php artisan optimize:clear'
];

foreach ($commands as $cmd) {
    echo "  Running: $cmd\n";
    $output = shell_exec($cmd . ' 2>&1');
    echo "  " . trim($output) . "\n";
}
echo "✅ Laravel caches cleared\n\n";

// Step 2: Clear bootstrap cache
echo "🗑️  Step 2: Clearing bootstrap cache...\n";
$files = [
    'bootstrap/cache/packages.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "  Deleted: $file\n";
    }
}
echo "✅ Bootstrap cache cleared\n\n";

// Step 3: Regenerate composer autoload
echo "🔄 Step 3: Regenerating composer autoload...\n";
$output = shell_exec('composer dump-autoload -o --no-interaction 2>&1');
echo "  " . trim($output) . "\n";
echo "✅ Composer autoload regenerated\n\n";

// Step 4: Cache for production
echo "⚡ Step 4: Caching for production...\n";
$cacheCommands = [
    'php artisan config:cache',
    'php artisan route:cache',
    'php artisan view:cache'
];

foreach ($cacheCommands as $cmd) {
    echo "  Running: $cmd\n";
    $output = shell_exec($cmd . ' 2>&1');
    echo "  " . trim($output) . "\n";
}
echo "✅ Production caches created\n\n";

// Step 5: Test route
echo "🧪 Step 5: Testing cart payment route...\n";
$output = shell_exec('php artisan route:list --name=cart.payment.ipaymu 2>&1');
echo "  " . trim($output) . "\n\n";

echo "================================================\n";
echo "✅ VPS Web Fix Completed!\n";
echo "================================================\n\n";

echo "📝 Next steps:\n";
echo "1. Test cart payment: https://srx.sppqu.my.id/student/cart\n";
echo "2. Delete this file after fix: vps_web_fix.php\n\n";

echo "</pre>";

