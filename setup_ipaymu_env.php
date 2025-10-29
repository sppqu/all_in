<?php

/**
 * Setup iPaymu ENV Configuration
 * 
 * This script copies iPaymu credentials from database to .env file
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Setup iPaymu ENV Configuration                        ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Get credentials from database
echo "📋 Getting iPaymu credentials from database...\n";
echo "─────────────────────────────────────────────────────────────────\n";

$gateway = DB::table('setup_gateways')->first();

if (!$gateway) {
    echo "❌ ERROR: No gateway configuration found in database!\n";
    echo "Please configure iPaymu in Admin Panel → Settings → Payment Gateway first.\n";
    exit(1);
}

if (!$gateway->ipaymu_is_active) {
    echo "⚠️  WARNING: iPaymu is not active in database!\n";
    echo "Do you still want to copy credentials to ENV? (y/n): ";
    $answer = trim(fgets(STDIN));
    if (strtolower($answer) !== 'y') {
        echo "Cancelled.\n";
        exit(0);
    }
}

$va = $gateway->ipaymu_va ?? '';
$apiKey = $gateway->ipaymu_api_key ?? '';
$mode = $gateway->ipaymu_mode ?? 'sandbox';
$sandbox = $mode === 'sandbox' ? 'true' : 'false';

echo "✅ Found credentials in database:\n";
echo "   VA: " . (!empty($va) ? substr($va, 0, 10) . "..." . substr($va, -5) : "EMPTY") . "\n";
echo "   API Key: " . (!empty($apiKey) ? substr($apiKey, 0, 15) . "..." . substr($apiKey, -10) : "EMPTY") . "\n";
echo "   Mode: " . strtoupper($mode) . "\n";
echo "\n";

if (empty($va) || empty($apiKey)) {
    echo "❌ ERROR: VA or API Key is empty in database!\n";
    echo "Please configure iPaymu in Admin Panel first.\n";
    exit(1);
}

// Check if .env file exists
$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    echo "❌ ERROR: .env file not found!\n";
    echo "Please create .env file from .env.example first.\n";
    exit(1);
}

echo "📝 Reading .env file...\n";
$envContent = file_get_contents($envFile);

// Check if ENV variables already exist
$hasVa = preg_match('/^IPAYMU_VA=/m', $envContent);
$hasApiKey = preg_match('/^IPAYMU_API_KEY=/m', $envContent);
$hasSandbox = preg_match('/^IPAYMU_SANDBOX=/m', $envContent);

echo "\n";
echo "Current .env status:\n";
echo "   IPAYMU_VA: " . ($hasVa ? "EXISTS" : "NOT FOUND") . "\n";
echo "   IPAYMU_API_KEY: " . ($hasApiKey ? "EXISTS" : "NOT FOUND") . "\n";
echo "   IPAYMU_SANDBOX: " . ($hasSandbox ? "EXISTS" : "NOT FOUND") . "\n";
echo "\n";

// Update or add ENV variables
if ($hasVa) {
    $envContent = preg_replace('/^IPAYMU_VA=.*/m', 'IPAYMU_VA=' . $va, $envContent);
    echo "✏️  Updated IPAYMU_VA\n";
} else {
    $envContent .= "\n# iPaymu Configuration\nIPAYMU_VA=" . $va . "\n";
    echo "➕ Added IPAYMU_VA\n";
}

if ($hasApiKey) {
    $envContent = preg_replace('/^IPAYMU_API_KEY=.*/m', 'IPAYMU_API_KEY=' . $apiKey, $envContent);
    echo "✏️  Updated IPAYMU_API_KEY\n";
} else {
    $envContent .= "IPAYMU_API_KEY=" . $apiKey . "\n";
    echo "➕ Added IPAYMU_API_KEY\n";
}

if ($hasSandbox) {
    $envContent = preg_replace('/^IPAYMU_SANDBOX=.*/m', 'IPAYMU_SANDBOX=' . $sandbox, $envContent);
    echo "✏️  Updated IPAYMU_SANDBOX\n";
} else {
    $envContent .= "IPAYMU_SANDBOX=" . $sandbox . "\n";
    echo "➕ Added IPAYMU_SANDBOX\n";
}

echo "\n";
echo "💾 Writing to .env file...\n";

if (file_put_contents($envFile, $envContent)) {
    echo "✅ Successfully updated .env file!\n";
} else {
    echo "❌ ERROR: Failed to write to .env file!\n";
    echo "Please check file permissions.\n";
    exit(1);
}

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                     Setup Complete!                           ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "✅ iPaymu ENV configuration has been set up successfully!\n";
echo "\n";
echo "ENV Variables:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "IPAYMU_VA=" . $va . "\n";
echo "IPAYMU_API_KEY=" . $apiKey . "\n";
echo "IPAYMU_SANDBOX=" . $sandbox . "\n";
echo "\n";

echo "📋 Next steps:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "1. Clear config cache:\n";
echo "   php artisan config:clear\n";
echo "\n";
echo "2. Verify ENV config:\n";
echo "   ./check_env_ipaymu.sh\n";
echo "   OR\n";
echo "   php vps_check_ipaymu_config.php\n";
echo "\n";
echo "3. Test iPaymu API:\n";
echo "   php test_ipaymu_api.php\n";
echo "\n";
echo "4. Restart PHP-FPM:\n";
echo "   systemctl restart php8.2-fpm\n";
echo "\n";
echo "5. Test addon purchase in browser\n";
echo "\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "Configuration Type:\n";
echo "  - Addon Purchase: ENV ✅\n";
echo "  - Subscription: ENV ✅\n";
echo "  - SPMB Step 2: ENV ✅\n";
echo "  - Student Cart: Database ✅\n";
echo "  - SPMB Step 5: Database ✅\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

