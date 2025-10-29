<?php

/**
 * VPS Check iPaymu Configuration
 * 
 * This script checks and displays iPaymu configuration from both ENV and Database
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         iPaymu Configuration Checker & Debugger               ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// 1. Check ENV Configuration
echo "🔍 Checking ENV Configuration (.env file):\n";
echo "─────────────────────────────────────────────────────────────────\n";

$envVa = env('IPAYMU_VA', '');
$envApiKey = env('IPAYMU_API_KEY', '');
$envSandbox = env('IPAYMU_SANDBOX', true);

echo "IPAYMU_VA: " . (!empty($envVa) ? "✅ SET (length: ".strlen($envVa).")" : "❌ EMPTY") . "\n";
echo "IPAYMU_API_KEY: " . (!empty($envApiKey) ? "✅ SET (length: ".strlen($envApiKey).")" : "❌ EMPTY") . "\n";
echo "IPAYMU_SANDBOX: " . ($envSandbox ? "🧪 SANDBOX (testing)" : "🚀 PRODUCTION") . "\n";

if (!empty($envVa)) {
    echo "VA Value: " . substr($envVa, 0, 10) . "..." . substr($envVa, -5) . "\n";
}
if (!empty($envApiKey)) {
    echo "API Key Value: " . substr($envApiKey, 0, 15) . "..." . substr($envApiKey, -10) . "\n";
}

echo "\n";

// 2. Check Database Configuration
echo "🔍 Checking Database Configuration (setup_gateways table):\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    $gateway = DB::table('setup_gateways')->first();
    
    if ($gateway) {
        echo "✅ Gateway record found\n";
        echo "iPaymu Active: " . ($gateway->ipaymu_is_active ? "✅ YES" : "❌ NO") . "\n";
        echo "iPaymu VA: " . (!empty($gateway->ipaymu_va) ? "✅ SET (length: ".strlen($gateway->ipaymu_va).")" : "❌ EMPTY") . "\n";
        echo "iPaymu API Key: " . (!empty($gateway->ipaymu_api_key) ? "✅ SET (length: ".strlen($gateway->ipaymu_api_key).")" : "❌ EMPTY") . "\n";
        echo "iPaymu Mode: " . ($gateway->ipaymu_mode ?? 'sandbox') . "\n";
        
        if (!empty($gateway->ipaymu_va)) {
            echo "DB VA Value: " . substr($gateway->ipaymu_va, 0, 10) . "..." . substr($gateway->ipaymu_va, -5) . "\n";
        }
        if (!empty($gateway->ipaymu_api_key)) {
            echo "DB API Key Value: " . substr($gateway->ipaymu_api_key, 0, 15) . "..." . substr($gateway->ipaymu_api_key, -10) . "\n";
        }
    } else {
        echo "❌ No gateway record found in database\n";
    }
} catch (\Exception $e) {
    echo "❌ Error reading database: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Configuration Comparison
echo "🔍 Configuration Status:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$envConfigValid = !empty($envVa) && !empty($envApiKey);
$dbConfigValid = isset($gateway) && $gateway && !empty($gateway->ipaymu_va) && !empty($gateway->ipaymu_api_key);

echo "ENV Config Valid: " . ($envConfigValid ? "✅ YES" : "❌ NO") . "\n";
echo "DB Config Valid: " . ($dbConfigValid ? "✅ YES" : "❌ NO") . "\n";

echo "\n";

// 4. Usage Recommendations
echo "💡 Configuration Usage:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "Step 2 SPMB (QRIS):  Uses ENV config (fallback to DB if empty)\n";
echo "Student Cart:        Uses DATABASE config\n";
echo "Addon Purchase:      Uses ENV config (fallback to DB if empty)\n";
echo "Subscription:        Uses ENV config (fallback to DB if empty)\n";

echo "\n";

// 5. Recommendations
echo "📋 Recommendations:\n";
echo "─────────────────────────────────────────────────────────────────\n";

if (!$envConfigValid && !$dbConfigValid) {
    echo "❌ CRITICAL: No valid iPaymu configuration found!\n";
    echo "   Action required:\n";
    echo "   1. Set ENV variables in .env file, OR\n";
    echo "   2. Configure in Admin Panel > Settings > Payment Gateway\n";
} elseif (!$envConfigValid && $dbConfigValid) {
    echo "⚠️  WARNING: ENV config empty, using DATABASE config as fallback\n";
    echo "   This is OK, but recommended to set ENV for better separation\n";
    echo "   Add to .env file:\n";
    echo "   IPAYMU_VA=" . ($gateway->ipaymu_va ?? 'your_va_here') . "\n";
    echo "   IPAYMU_API_KEY=" . ($gateway->ipaymu_api_key ?? 'your_api_key_here') . "\n";
    echo "   IPAYMU_SANDBOX=" . ($gateway->ipaymu_mode === 'sandbox' ? 'true' : 'false') . "\n";
} elseif ($envConfigValid && !$dbConfigValid) {
    echo "⚠️  WARNING: Database config empty\n";
    echo "   Student cart payments will not work!\n";
    echo "   Action: Configure in Admin Panel > Settings > Payment Gateway\n";
} else {
    echo "✅ GOOD: Both ENV and Database configurations are valid\n";
    echo "   - Step 2 SPMB uses ENV config\n";
    echo "   - Student cart uses DATABASE config\n";
    
    // Check if they're the same
    if ($envVa === $gateway->ipaymu_va && $envApiKey === $gateway->ipaymu_api_key) {
        echo "   ℹ️  Note: ENV and DB have the same credentials (same account)\n";
    } else {
        echo "   ℹ️  Note: ENV and DB have different credentials (different accounts)\n";
    }
}

echo "\n";

// 6. Test Signature Generation
echo "🧪 Testing Signature Generation:\n";
echo "─────────────────────────────────────────────────────────────────\n";

if ($envConfigValid) {
    $testBody = json_encode(['test' => 'data', 'amount' => 3000]);
    $testSignature = hash_hmac('sha256', $envVa . $envApiKey . $testBody, $envApiKey);
    echo "ENV Signature Test: ✅ Generated\n";
    echo "Sample: " . substr($testSignature, 0, 20) . "...\n";
} else {
    echo "ENV Signature Test: ❌ Cannot test (config empty)\n";
}

if ($dbConfigValid) {
    $testBody = json_encode(['test' => 'data', 'amount' => 3000]);
    $testSignature = hash_hmac('sha256', $gateway->ipaymu_va . $gateway->ipaymu_api_key . $testBody, $gateway->ipaymu_api_key);
    echo "DB Signature Test: ✅ Generated\n";
    echo "Sample: " . substr($testSignature, 0, 20) . "...\n";
} else {
    echo "DB Signature Test: ❌ Cannot test (config empty)\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Check complete!\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

