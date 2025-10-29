<?php

/**
 * Toggle iPaymu Sandbox Mode
 * 
 * This script switches between sandbox and production mode
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          Toggle iPaymu Sandbox Mode                         ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    echo "❌ ERROR: .env file not found!\n";
    exit(1);
}

// Read current .env
$envContent = file_get_contents($envFile);

// Check current mode
$currentMode = null;
if (preg_match('/IPAYMU_SANDBOX\s*=\s*(.+)/i', $envContent, $matches)) {
    $currentMode = trim($matches[1]);
}

echo "Current Configuration:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "Mode: " . ($currentMode === 'true' ? "🟡 SANDBOX (Testing)" : "🔴 PRODUCTION (Live)") . "\n";
echo "\n";

// Ask what to do
echo "What would you like to do?\n";
echo "  1. Switch to SANDBOX (Testing mode - safe for testing)\n";
echo "  2. Switch to PRODUCTION (Live mode - real transactions)\n";
echo "  3. Cancel\n";
echo "\n";
echo "Enter choice (1/2/3): ";

$choice = trim(fgets(STDIN));

if ($choice === '3') {
    echo "Cancelled.\n";
    exit(0);
}

$newMode = ($choice === '1') ? 'true' : 'false';
$modeName = ($newMode === 'true') ? 'SANDBOX' : 'PRODUCTION';

echo "\n";
echo "⚠️  You are about to switch to: " . $modeName . "\n";
echo "\n";

if ($newMode === 'false') {
    echo "⚠️⚠️⚠️ WARNING ⚠️⚠️⚠️\n";
    echo "PRODUCTION mode will:\n";
    echo "  - Create REAL transactions\n";
    echo "  - Charge REAL money\n";
    echo "  - Require REAL customer data\n";
    echo "  - Strict validation (may reject test data)\n";
    echo "\n";
    echo "Are you ABSOLUTELY sure? (type 'YES' to confirm): ";
    $confirm = trim(fgets(STDIN));
    
    if ($confirm !== 'YES') {
        echo "Cancelled.\n";
        exit(0);
    }
}

// Update .env
if (preg_match('/IPAYMU_SANDBOX\s*=\s*.+/i', $envContent)) {
    // Update existing
    $envContent = preg_replace(
        '/IPAYMU_SANDBOX\s*=\s*.+/i',
        'IPAYMU_SANDBOX=' . $newMode,
        $envContent
    );
} else {
    // Add new
    $envContent .= "\nIPAYMU_SANDBOX=" . $newMode . "\n";
}

// Write back
file_put_contents($envFile, $envContent);

echo "\n";
echo "✅ SUCCESS! Switched to " . $modeName . " mode!\n";
echo "\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "Next Steps:\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "1. Clear config cache:\n";
echo "   php artisan config:clear\n";
echo "\n";
echo "2. Restart PHP-FPM:\n";
echo "   systemctl restart php8.2-fpm\n";
echo "\n";
echo "3. Test payment creation:\n";
echo "   php test_spmb_payment.php\n";
echo "\n";

if ($newMode === 'true') {
    echo "ℹ️  SANDBOX Mode Benefits:\n";
    echo "  ✅ No real money charged\n";
    echo "  ✅ Can use test/dummy data\n";
    echo "  ✅ Less strict validation\n";
    echo "  ✅ Safe for development\n";
} else {
    echo "⚠️  PRODUCTION Mode Requirements:\n";
    echo "  ❗ Real customer data only\n";
    echo "  ❗ No dummy phone/email\n";
    echo "  ❗ Strict 'Suspicious buyer' detection\n";
    echo "  ❗ Real money transactions\n";
}

echo "\n";

