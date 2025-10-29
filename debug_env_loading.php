<?php

/**
 * Debug ENV Loading
 * 
 * This script debugs why ENV variables are not being loaded
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║              Debug ENV Loading Issue                          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// 1. Check if .env file exists
$envPath = __DIR__ . '/.env';
echo "1️⃣  Checking .env file existence:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "Path: {$envPath}\n";
echo "Exists: " . (file_exists($envPath) ? "✅ YES" : "❌ NO") . "\n";
echo "Readable: " . (is_readable($envPath) ? "✅ YES" : "❌ NO") . "\n";
echo "\n";

if (!file_exists($envPath)) {
    echo "❌ CRITICAL: .env file not found!\n";
    echo "Please create .env file or run: php setup_ipaymu_env.php\n";
    exit(1);
}

// 2. Read .env content
echo "2️⃣  Reading .env file content:\n";
echo "─────────────────────────────────────────────────────────────────\n";
$envContent = file_get_contents($envPath);
$lines = explode("\n", $envContent);
echo "Total lines: " . count($lines) . "\n";

// Find iPaymu lines
$ipaymuLines = array_filter($lines, function($line) {
    return stripos($line, 'IPAYMU') !== false;
});

if (empty($ipaymuLines)) {
    echo "❌ NO iPaymu config found in .env!\n";
    echo "\n";
    echo "Run this to setup: php setup_ipaymu_env.php\n";
} else {
    echo "✅ Found " . count($ipaymuLines) . " iPaymu lines:\n";
    foreach ($ipaymuLines as $lineNum => $line) {
        $line = trim($line);
        if (empty($line) || substr($line, 0, 1) === '#') continue;
        
        // Mask sensitive data
        if (preg_match('/^(IPAYMU_[A-Z_]+)=(.+)$/', $line, $matches)) {
            $key = $matches[1];
            $value = $matches[2];
            
            // Show preview
            if ($key === 'IPAYMU_VA') {
                echo "   {$key} = " . substr($value, 0, 10) . "..." . substr($value, -5) . " (len:" . strlen($value) . ")\n";
            } elseif ($key === 'IPAYMU_API_KEY') {
                echo "   {$key} = " . substr($value, 0, 15) . "... (len:" . strlen($value) . ")\n";
            } else {
                echo "   {$key} = {$value}\n";
            }
        }
    }
}
echo "\n";

// 3. Test parsing method (same as IpaymuService)
echo "3️⃣  Testing ENV parsing (same method as IpaymuService):\n";
echo "─────────────────────────────────────────────────────────────────\n";

function getEnvValue($key, $envContent) {
    $pattern = '/^' . preg_quote($key, '/') . '=(.*)$/m';
    
    if (preg_match($pattern, $envContent, $matches)) {
        $value = trim($matches[1]);
        
        // Remove quotes if present
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        
        return $value;
    }
    
    return null;
}

$keys = ['IPAYMU_VA', 'IPAYMU_API_KEY', 'IPAYMU_SANDBOX'];

foreach ($keys as $key) {
    $value = getEnvValue($key, $envContent);
    
    if ($value !== null) {
        echo "✅ {$key}: ";
        if ($key === 'IPAYMU_VA') {
            echo substr($value, 0, 10) . "..." . substr($value, -5) . " (len:" . strlen($value) . ")\n";
        } elseif ($key === 'IPAYMU_API_KEY') {
            echo substr($value, 0, 15) . "... (len:" . strlen($value) . ")\n";
        } else {
            echo "{$value}\n";
        }
    } else {
        echo "❌ {$key}: NOT FOUND\n";
    }
}
echo "\n";

// 4. Test using env() function
echo "4️⃣  Testing env() function (Laravel):\n";
echo "─────────────────────────────────────────────────────────────────\n";

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach ($keys as $key) {
    $value = env($key);
    
    echo "{$key}: ";
    if ($value !== null && $value !== '') {
        if ($key === 'IPAYMU_VA') {
            echo substr($value, 0, 10) . "..." . substr($value, -5) . " (len:" . strlen($value) . ") ✅\n";
        } elseif ($key === 'IPAYMU_API_KEY') {
            echo substr($value, 0, 15) . "... (len:" . strlen($value) . ") ✅\n";
        } else {
            echo "{$value} ✅\n";
        }
    } else {
        echo "NULL/EMPTY ❌\n";
    }
}
echo "\n";

// 5. Check config cache
echo "5️⃣  Checking config cache:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$configCachePath = __DIR__ . '/bootstrap/cache/config.php';
echo "Config cache path: {$configCachePath}\n";
echo "Config cached: " . (file_exists($configCachePath) ? "⚠️  YES (this causes env() to return NULL)" : "✅ NO") . "\n";

if (file_exists($configCachePath)) {
    echo "\n";
    echo "⚠️  CONFIG IS CACHED!\n";
    echo "This is why env() returns NULL.\n";
    echo "Solution: Our getEnvValue() method bypasses this.\n";
}
echo "\n";

// 6. Test IpaymuService initialization
echo "6️⃣  Testing IpaymuService initialization:\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    $ipaymu = new \App\Services\IpaymuService(true);
    echo "✅ IpaymuService initialized successfully\n";
    echo "\n";
    echo "Check storage/logs/laravel.log for detailed logs:\n";
    echo "  - Look for: 'Found IPAYMU_VA in .env file'\n";
    echo "  - Look for: 'FORCE Loading ENV config'\n";
    echo "  - Look for: 'iPaymu Service initialized'\n";
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Summary:\n";
echo "═══════════════════════════════════════════════════════════════\n";

$envExists = file_exists($envPath);
$hasIpaymuVars = !empty($ipaymuLines);
$envVaValue = getEnvValue('IPAYMU_VA', $envContent);
$envApiKeyValue = getEnvValue('IPAYMU_API_KEY', $envContent);

if ($envExists && $hasIpaymuVars && $envVaValue && $envApiKeyValue) {
    echo "✅ .env file exists and has valid iPaymu config\n";
    echo "✅ Parsing method works correctly\n";
    echo "\n";
    echo "If addon purchase still uses database config, check:\n";
    echo "1. Restart PHP-FPM: systemctl restart php8.2-fpm\n";
    echo "2. Check logs: tail -f storage/logs/laravel.log | grep IPAYMU\n";
    echo "3. Clear all caches: php artisan optimize:clear\n";
} else {
    echo "❌ Issues found:\n";
    if (!$envExists) echo "  - .env file not found\n";
    if (!$hasIpaymuVars) echo "  - No IPAYMU_ variables in .env\n";
    if (!$envVaValue) echo "  - IPAYMU_VA not found or empty\n";
    if (!$envApiKeyValue) echo "  - IPAYMU_API_KEY not found or empty\n";
    echo "\n";
    echo "Run: php setup_ipaymu_env.php\n";
}

echo "\n";

