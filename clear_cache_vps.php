<?php
/**
 * Clear Cache VPS - Simple version
 * Upload file ini ke root folder VPS dan akses via browser
 */

echo "<pre>";
echo "================================================\n";
echo "🔧 Clear Cache & Fix Routes\n";
echo "================================================\n\n";

// Clear Laravel caches
echo "🗑️  Clearing caches...\n";

$commands = [
    'config:clear' => 'Config cache',
    'cache:clear' => 'Application cache',
    'route:clear' => 'Route cache',
    'view:clear' => 'View cache',
    'optimize:clear' => 'Optimized files'
];

foreach ($commands as $cmd => $desc) {
    echo "  - Clearing $desc...\n";
    exec("php artisan $cmd 2>&1", $output, $return);
    if ($return === 0) {
        echo "    ✅ Done\n";
    } else {
        echo "    ⚠️  " . implode("\n", $output) . "\n";
    }
    $output = [];
}

echo "\n";

// Clear bootstrap cache files
echo "🗑️  Clearing bootstrap cache files...\n";
$cacheFiles = [
    'bootstrap/cache/packages.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "  ✅ Deleted: $file\n";
    }
}

echo "\n";

// Regenerate composer autoload
echo "🔄 Regenerating composer autoload...\n";
exec("composer dump-autoload -o 2>&1", $output, $return);
if ($return === 0) {
    echo "  ✅ Composer autoload regenerated\n";
} else {
    echo "  ⚠️  " . implode("\n", $output) . "\n";
}

echo "\n";

// Cache for production
echo "⚡ Caching for production...\n";
$cacheCommands = [
    'config:cache' => 'Config',
    'route:cache' => 'Routes',
    'view:cache' => 'Views'
];

foreach ($cacheCommands as $cmd => $desc) {
    echo "  - Caching $desc...\n";
    exec("php artisan $cmd 2>&1", $output, $return);
    if ($return === 0) {
        echo "    ✅ Done\n";
    } else {
        echo "    ⚠️  " . implode("\n", $output) . "\n";
    }
    $output = [];
}

echo "\n";

// Test route
echo "🧪 Testing cart payment route...\n";
exec("php artisan route:list --name=cart.payment.ipaymu 2>&1", $output);
echo "  " . implode("\n  ", $output) . "\n";

echo "\n";
echo "================================================\n";
echo "✅ Clear Cache Completed!\n";
echo "================================================\n\n";

echo "📝 Next steps:\n";
echo "1. Delete this file: clear_cache_vps.php\n";
echo "2. Test cart payment: <a href='/student/cart'>/student/cart</a>\n";
echo "3. Check latest error: <a href='/check_error.php'>check_error.php</a>\n\n";

echo "</pre>";

