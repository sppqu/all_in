<?php

/**
 * Update Admin Phone Number
 * 
 * This script updates superadmin phone number in database
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║            Update Superadmin Phone Number                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Get current superadmin
$admin = DB::table('users')->where('role', 'superadmin')->first();

if (!$admin) {
    echo "❌ ERROR: Superadmin not found!\n";
    exit(1);
}

echo "Current Admin Info:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "Name: " . $admin->name . "\n";
echo "Email: " . $admin->email . "\n";
echo "Current Phone: " . ($admin->phone ?? 'NULL') . "\n";
echo "\n";

// Get new phone from stdin
echo "Enter new phone number (example: 082225855859): ";
$newPhone = trim(fgets(STDIN));

// Validate phone
$newPhone = preg_replace('/[^0-9]/', '', $newPhone);

if (strlen($newPhone) < 10) {
    echo "❌ ERROR: Phone number too short! Must be at least 10 digits.\n";
    exit(1);
}

if ($newPhone === '08123456789') {
    echo "⚠️  WARNING: You are setting a dummy/test number!\n";
    echo "This will NOT work in iPaymu production mode.\n";
    echo "Continue anyway? (y/n): ";
    $confirm = trim(fgets(STDIN));
    if (strtolower($confirm) !== 'y') {
        echo "Cancelled.\n";
        exit(0);
    }
}

// Ensure starts with 08 or 62
if (!str_starts_with($newPhone, '08') && !str_starts_with($newPhone, '62')) {
    $newPhone = '08' . ltrim($newPhone, '0');
    echo "ℹ️  Auto-corrected to: " . $newPhone . "\n";
}

echo "\n";
echo "You are about to update:\n";
echo "  From: " . ($admin->phone ?? 'NULL') . "\n";
echo "  To:   " . $newPhone . "\n";
echo "\n";
echo "Continue? (y/n): ";
$confirm = trim(fgets(STDIN));

if (strtolower($confirm) !== 'y') {
    echo "Cancelled.\n";
    exit(0);
}

// Update phone
try {
    DB::table('users')
        ->where('id', $admin->id)
        ->update([
            'phone' => $newPhone,
            'updated_at' => now()
        ]);
    
    echo "\n";
    echo "✅ SUCCESS! Phone number updated!\n";
    echo "\n";
    echo "New phone: " . $newPhone . "\n";
    echo "\n";
    
    // Verify update
    $updated = DB::table('users')->where('id', $admin->id)->first();
    echo "Verified from database: " . $updated->phone . "\n";
    echo "\n";
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Next steps:\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "1. Restart PHP-FPM:\n";
    echo "   systemctl restart php8.2-fpm\n";
    echo "\n";
    echo "2. Test addon purchase in browser\n";
    echo "   - Login as superadmin\n";
    echo "   - Go to Addons\n";
    echo "   - Purchase an addon\n";
    echo "   - Should work now!\n";
    echo "\n";
    echo "3. If using production mode (IPAYMU_SANDBOX=false):\n";
    echo "   - Make sure this is a REAL phone number\n";
    echo "   - NOT a dummy/test number\n";
    echo "\n";
    
} catch (\Exception $e) {
    echo "\n";
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

