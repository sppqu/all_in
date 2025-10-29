<?php

/**
 * Check Users Table Structure
 * 
 * This script checks the structure of users table
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║              Check Users Table Structure                     ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Get table columns
echo "Users Table Columns:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$columns = DB::select("SHOW COLUMNS FROM users");

foreach ($columns as $column) {
    echo sprintf(
        "%-20s %-20s %s\n",
        $column->Field,
        $column->Type,
        $column->Null === 'YES' ? 'NULL' : 'NOT NULL'
    );
}

echo "\n";

// Check if phone column exists
$hasNomorWa = Schema::hasColumn('users', 'nomor_wa');
$hasPhone = Schema::hasColumn('users', 'phone');
$hasNoHp = Schema::hasColumn('users', 'no_hp');
$hasPhoneNumber = Schema::hasColumn('users', 'phone_number');

echo "Phone Column Check:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "nomor_wa: " . ($hasNomorWa ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
echo "phone: " . ($hasPhone ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
echo "no_hp: " . ($hasNoHp ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
echo "phone_number: " . ($hasPhoneNumber ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
echo "\n";

// Get superadmin info
echo "Superadmin Info:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$admin = DB::table('users')->where('role', 'superadmin')->first();

if (!$admin) {
    echo "❌ Superadmin not found!\n";
} else {
    echo "ID: " . $admin->id . "\n";
    echo "Name: " . $admin->name . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Role: " . $admin->role . "\n";
    
    // Try to get phone from different possible columns
    if (isset($admin->nomor_wa)) {
        echo "nomor_wa: " . $admin->nomor_wa . "\n";
    }
    if (isset($admin->phone)) {
        echo "phone: " . $admin->phone . "\n";
    }
    if (isset($admin->no_hp)) {
        echo "no_hp: " . $admin->no_hp . "\n";
    }
    if (isset($admin->phone_number)) {
        echo "phone_number: " . $admin->phone_number . "\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Recommendation:\n";
echo "═══════════════════════════════════════════════════════════════\n";

if (!$hasNomorWa && !$hasPhone && !$hasNoHp && !$hasPhoneNumber) {
    echo "⚠️  No phone column found! Run this to add it:\n";
    echo "   php vps_add_phone_column.php\n";
} elseif ($hasNomorWa) {
    echo "✅ Phone column exists as 'nomor_wa'\n";
    echo "   Run: php update_admin_phone.php\n";
} elseif ($hasNoHp) {
    echo "ℹ️  Phone column exists as 'no_hp'\n";
    echo "   Run: php update_admin_phone.php\n";
} elseif ($hasPhoneNumber) {
    echo "ℹ️  Phone column exists as 'phone_number'\n";
    echo "   Run: php update_admin_phone.php\n";
}

echo "\n";

