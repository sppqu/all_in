<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Addon;
use App\Models\UserAddon;

echo "=== HAPUS ADDON ===\n\n";

if (!isset($argv[1])) {
    echo "Usage: php remove_addon.php <user_id> <addon_slug>\n";
    echo "       php remove_addon.php <email> <addon_slug>\n";
    echo "       php remove_addon.php list (untuk melihat daftar user)\n";
    echo "       php remove_addon.php addons (untuk melihat daftar addon)\n\n";
    exit(1);
}

$input = $argv[1];

// Jika input adalah "list", tampilkan daftar user
if ($input === 'list') {
    echo "📋 DAFTAR USER:\n";
    echo str_repeat("-", 80) . "\n";
    echo sprintf("%-5s %-30s %-20s %-15s\n", "ID", "Name", "Email", "Role");
    echo str_repeat("-", 80) . "\n";
    
    $users = User::orderBy('id')->get();
    foreach ($users as $user) {
        echo sprintf("%-5s %-30s %-20s %-15s\n", 
            $user->id, 
            substr($user->name, 0, 30), 
            substr($user->email, 0, 20), 
            $user->role
        );
    }
    echo str_repeat("-", 80) . "\n";
    echo "Total: " . $users->count() . " users\n\n";
    exit(0);
}

// Jika input adalah "addons", tampilkan daftar addon
if ($input === 'addons') {
    echo "📋 DAFTAR ADDON:\n";
    echo str_repeat("-", 80) . "\n";
    echo sprintf("%-5s %-30s %-20s %-15s\n", "ID", "Name", "Slug", "Price");
    echo str_repeat("-", 80) . "\n";
    
    $addons = Addon::orderBy('id')->get();
    foreach ($addons as $addon) {
        echo sprintf("%-5s %-30s %-20s %-15s\n", 
            $addon->id, 
            substr($addon->name, 0, 30), 
            $addon->slug, 
            'Rp ' . number_format($addon->price, 0, ',', '.')
        );
    }
    echo str_repeat("-", 80) . "\n";
    echo "Total: " . $addons->count() . " addons\n\n";
    exit(0);
}

if (!isset($argv[2])) {
    echo "❌ Error: Addon slug tidak diberikan!\n";
    echo "💡 Gunakan 'php remove_addon.php addons' untuk melihat daftar addon\n";
    exit(1);
}

$addonSlug = $argv[2];

// Cari user berdasarkan ID atau email
$user = null;
if (is_numeric($input)) {
    $user = User::find($input);
} else {
    $user = User::where('email', $input)->first();
}

if (!$user) {
    echo "❌ Error: User tidak ditemukan!\n";
    echo "💡 Gunakan 'php remove_addon.php list' untuk melihat daftar user\n";
    exit(1);
}

echo "👤 User ditemukan: {$user->name} ({$user->email}) - Role: {$user->role}\n";

// Cari addon
$addon = Addon::where('slug', $addonSlug)->first();

if (!$addon) {
    echo "❌ Error: Addon '{$addonSlug}' tidak ditemukan!\n";
    echo "💡 Gunakan 'php remove_addon.php addons' untuk melihat daftar addon\n";
    exit(1);
}

echo "📦 Addon: {$addon->name} (Slug: {$addon->slug})\n\n";

// Cari user addon
$userAddon = UserAddon::where('user_id', $user->id)
    ->where('addon_id', $addon->id)
    ->first();

if (!$userAddon) {
    echo "ℹ️ User tidak memiliki addon '{$addon->name}'\n";
    echo "🎯 Tidak ada yang perlu dihapus\n";
    exit(0);
}

echo "🔍 User addon ditemukan:\n";
echo "   - Status: {$userAddon->status}\n";
echo "   - Tanggal pembelian: {$userAddon->purchased_at}\n";
echo "   - Metode pembayaran: {$userAddon->payment_method}\n\n";

echo "🗑️ Menghapus addon '{$addon->name}' dari user...\n";
$userAddon->delete();

echo "✅ Addon '{$addon->name}' berhasil dihapus dari user!\n";
echo "🎯 User tidak akan bisa akses fitur addon ini lagi\n";

// Cek apakah ada menu yang terpengaruh
if ($addonSlug === 'spmb') {
    echo "\n⚠️ PERHATIAN: Menu SPMB akan hilang dari sidebar user!\n";
    echo "   - User tidak bisa akses fitur SPMB\n";
    echo "   - Menu SPMB tidak akan muncul di sidebar\n";
}

echo "\n=== SELESAI ===\n";
