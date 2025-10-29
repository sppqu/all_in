<?php

/**
 * Add Phone Column to Users Table
 * 
 * This script adds phone column to users table if it doesn't exist
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║           Add Phone Column to Users Table                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Check if any phone column exists
$hasNomorWa = Schema::hasColumn('users', 'nomor_wa');
$hasPhone = Schema::hasColumn('users', 'phone');
$hasNoHp = Schema::hasColumn('users', 'no_hp');

if ($hasNomorWa || $hasPhone || $hasNoHp) {
    echo "✅ Phone column already exists!\n";
    if ($hasNomorWa) echo "   Column: nomor_wa\n";
    if ($hasPhone) echo "   Column: phone\n";
    if ($hasNoHp) echo "   Column: no_hp\n";
    echo "\n";
    echo "Run this to update phone:\n";
    echo "   php update_admin_phone.php\n";
    echo "\n";
    exit(0);
}

echo "⚠️  Phone column not found in users table.\n";
echo "\n";
echo "This will add 'nomor_wa' column (VARCHAR 20, nullable)\n";
echo "\n";
echo "Continue? (y/n): ";

$confirm = trim(fgets(STDIN));

if (strtolower($confirm) !== 'y') {
    echo "Cancelled.\n";
    exit(0);
}

try {
    echo "\n";
    echo "Adding nomor_wa column...\n";
    
    Schema::table('users', function (Blueprint $table) {
        $table->string('nomor_wa', 20)->nullable()->after('email');
    });
    
    echo "✅ SUCCESS! nomor_wa column added!\n";
    echo "\n";
    
    // Verify
    $hasNomorWa = Schema::hasColumn('users', 'nomor_wa');
    echo "Verification: " . ($hasNomorWa ? '✅ Column exists' : '❌ Failed') . "\n";
    echo "\n";
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Next steps:\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "1. Update phone number:\n";
    echo "   php update_admin_phone.php\n";
    echo "\n";
    echo "2. Restart PHP-FPM:\n";
    echo "   systemctl restart php8.2-fpm\n";
    echo "\n";
    
} catch (\Exception $e) {
    echo "\n";
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\n";
    exit(1);
}

