<?php

/**
 * Debug Subscription Payment Issue
 * 
 * This script helps debug subscription payment errors
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          Debug Subscription Payment Issue                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// 1. Check superadmin info
echo "1. Superadmin Info:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$admin = DB::table('users')->where('role', 'superadmin')->first();

if (!$admin) {
    echo "❌ ERROR: Superadmin not found!\n";
    exit(1);
}

echo "ID: " . $admin->id . "\n";
echo "Name: " . $admin->name . "\n";
echo "Email: " . $admin->email . "\n";

// Check phone columns
$phoneValue = null;
$phoneColumn = null;

if (isset($admin->nomor_wa)) {
    $phoneValue = $admin->nomor_wa;
    $phoneColumn = 'nomor_wa';
} elseif (isset($admin->phone)) {
    $phoneValue = $admin->phone;
    $phoneColumn = 'phone';
} elseif (isset($admin->no_hp)) {
    $phoneValue = $admin->no_hp;
    $phoneColumn = 'no_hp';
}

echo "Phone Column: " . ($phoneColumn ?? 'NONE') . "\n";
echo "Phone Value: " . ($phoneValue ?? 'NULL') . "\n";
echo "\n";

// 2. Test iPaymu config
echo "2. iPaymu Configuration:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$ipaymuVa = env('IPAYMU_VA');
$ipaymuKey = env('IPAYMU_API_KEY');
$ipaymuSandbox = env('IPAYMU_SANDBOX');
$ipaymuProduction = env('IPAYMU_PRODUCTION');

echo "IPAYMU_VA: " . ($ipaymuVa ? substr($ipaymuVa, 0, 10) . '...' : 'NOT SET') . "\n";
echo "IPAYMU_API_KEY: " . ($ipaymuKey ? substr($ipaymuKey, 0, 20) . '...' : 'NOT SET') . "\n";
echo "IPAYMU_SANDBOX: " . ($ipaymuSandbox ?? 'NOT SET') . "\n";
echo "IPAYMU_PRODUCTION: " . ($ipaymuProduction ?? 'NOT SET') . "\n";
echo "\n";

// 3. Test phone validation
echo "3. Phone Validation Test:\n";
echo "─────────────────────────────────────────────────────────────────\n";

if (!$phoneValue) {
    echo "❌ PROBLEM: Phone value is NULL or empty!\n";
    echo "   This will cause 406 error in production mode.\n";
    echo "\n";
    echo "   SOLUTION: Run this command:\n";
    echo "   php update_admin_phone.php\n";
    echo "\n";
} else {
    $cleanPhone = preg_replace('/[^0-9]/', '', $phoneValue);
    
    echo "Original: " . $phoneValue . "\n";
    echo "Cleaned: " . $cleanPhone . "\n";
    echo "Length: " . strlen($cleanPhone) . "\n";
    
    // Check if dummy
    if ($cleanPhone === '08123456789') {
        echo "⚠️  WARNING: This is a DUMMY phone number!\n";
        echo "   In production mode, this will cause 406 error.\n";
        echo "\n";
        echo "   SOLUTION: Update to real phone:\n";
        echo "   php update_admin_phone.php\n";
        echo "\n";
    } elseif (strlen($cleanPhone) < 10) {
        echo "❌ ERROR: Phone too short (minimum 10 digits)!\n";
        echo "\n";
    } else {
        echo "✅ Phone validation PASSED!\n";
        echo "\n";
    }
}

// 4. Check recent subscription errors in logs
echo "4. Recent Subscription Logs:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$logFile = storage_path('logs/laravel.log');

if (file_exists($logFile)) {
    // Get last 50 lines that contain "subscription" or "PRODUCTION ERROR"
    $lines = file($logFile);
    $relevantLines = [];
    
    foreach (array_reverse($lines) as $line) {
        if (stripos($line, 'subscription') !== false || 
            stripos($line, 'PRODUCTION ERROR') !== false ||
            stripos($line, 'Cannot use dummy phone') !== false ||
            stripos($line, '406') !== false) {
            $relevantLines[] = $line;
            if (count($relevantLines) >= 10) break;
        }
    }
    
    if (count($relevantLines) > 0) {
        echo "Recent relevant logs:\n";
        foreach (array_reverse($relevantLines) as $line) {
            echo "  " . trim($line) . "\n";
        }
    } else {
        echo "No recent subscription errors found in logs.\n";
    }
} else {
    echo "Log file not found: " . $logFile . "\n";
}

echo "\n";

// 5. Test createSubscriptionPayment method
echo "5. Test Subscription Payment Data:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$testData = [
    'user_id' => $admin->id,
    'method' => 'QRIS',
    'amount' => 189000,
    'plan_name' => 'SPPQU Subscription - 1 Bulan',
    'customer_name' => $admin->name,
    'customer_email' => $admin->email,
    'customer_phone' => $phoneValue ?? '08123456789',
    'return_url' => 'http://test.com/return',
    'callback_url' => 'http://test.com/callback'
];

echo "Test data that will be sent:\n";
foreach ($testData as $key => $value) {
    echo "  " . str_pad($key, 20) . ": " . $value . "\n";
}

echo "\n";

// Check if phone will pass validation
$isSandbox = env('IPAYMU_SANDBOX', 'true') === 'true';
$testPhone = $testData['customer_phone'];
$cleanTestPhone = preg_replace('/[^0-9]/', '', $testPhone);

echo "Validation Check:\n";
echo "  Sandbox Mode: " . ($isSandbox ? 'YES' : 'NO (PRODUCTION)') . "\n";
echo "  Phone: " . $cleanTestPhone . "\n";

if (!$isSandbox && ($cleanTestPhone === '08123456789' || strlen($cleanTestPhone) < 10)) {
    echo "  Result: ❌ WILL FAIL (406 error)\n";
    echo "\n";
    echo "  Reason: " . ($cleanTestPhone === '08123456789' ? 'Dummy phone not allowed in production' : 'Phone too short') . "\n";
} else {
    echo "  Result: ✅ WILL PASS\n";
}

echo "\n";

// Summary
echo "═══════════════════════════════════════════════════════════════\n";
echo "Summary:\n";
echo "═══════════════════════════════════════════════════════════════\n";

if (!$phoneValue) {
    echo "🔴 CRITICAL: No phone number found!\n";
    echo "   Action: php update_admin_phone.php\n";
} elseif (!$isSandbox && ($cleanPhone === '08123456789' || strlen($cleanPhone) < 10)) {
    echo "🔴 CRITICAL: Phone invalid for production!\n";
    echo "   Current: " . $phoneValue . "\n";
    echo "   Action: php update_admin_phone.php\n";
} else {
    echo "✅ Phone validation should pass!\n";
    echo "   If still getting 406, check:\n";
    echo "   1. Clear cache: php artisan config:clear\n";
    echo "   2. Restart PHP: systemctl restart php8.2-fpm\n";
    echo "   3. Check browser console for errors\n";
    echo "   4. Check: tail -f storage/logs/laravel.log\n";
}

echo "\n";

