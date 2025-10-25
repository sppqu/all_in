<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Addon;
use App\Models\UserAddon;
use App\Models\User;

echo "=== GRANT ADDON SPMB KE USER ===\n\n";

if (!isset($argv[1])) {
    echo "Usage: php grant_spmb_to_user.php <user_id>\n";
    echo "       php grant_spmb_to_user.php <email>\n";
    echo "       php grant_spmb_to_user.php list (untuk melihat daftar user)\n\n";
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

// Cari user berdasarkan ID atau email
$user = null;
if (is_numeric($input)) {
    $user = User::find($input);
} else {
    $user = User::where('email', $input)->first();
}

if (!$user) {
    echo "❌ Error: User tidak ditemukan!\n";
    echo "💡 Gunakan 'php grant_spmb_to_user.php list' untuk melihat daftar user\n";
    exit(1);
}

echo "👤 User ditemukan: {$user->name} ({$user->email}) - Role: {$user->role}\n";

// Cari addon SPMB
$spmbAddon = Addon::where('slug', 'spmb')->first();

if (!$spmbAddon) {
    echo "❌ Error: Addon SPMB tidak ditemukan!\n";
    exit(1);
}

echo "📦 Addon SPMB: {$spmbAddon->name} (Harga: Rp " . number_format($spmbAddon->price, 0, ',', '.') . ")\n\n";

// Cek apakah user sudah memiliki addon
$existingAddon = UserAddon::where('user_id', $user->id)
    ->where('addon_id', $spmbAddon->id)
    ->first();

if ($existingAddon) {
    if ($existingAddon->status === 'active') {
        echo "✅ User sudah memiliki addon SPMB aktif!\n";
        echo "📅 Tanggal pembelian: " . $existingAddon->purchased_at->format('Y-m-d H:i:s') . "\n";
        echo "💰 Harga yang dibayar: Rp " . number_format($existingAddon->amount_paid, 0, ',', '.') . "\n";
        echo "💳 Metode pembayaran: {$existingAddon->payment_method}\n";
    } else {
        echo "🔄 Mengaktifkan addon SPMB yang sudah ada...\n";
        $existingAddon->update([
            'status' => 'active',
            'updated_at' => now()
        ]);
        echo "✅ Addon SPMB berhasil diaktifkan!\n";
    }
} else {
    echo "🎁 Memberikan addon SPMB ke user...\n";
    
    UserAddon::create([
        'user_id' => $user->id,
        'addon_id' => $spmbAddon->id,
        'status' => 'active',
        'purchased_at' => now(),
        'amount_paid' => $spmbAddon->price,
        'payment_method' => 'admin_grant',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Addon SPMB berhasil diberikan!\n";
    echo "📅 Tanggal pemberian: " . now()->format('Y-m-d H:i:s') . "\n";
    echo "💰 Harga: Rp " . number_format($spmbAddon->price, 0, ',', '.') . "\n";
    echo "💳 Metode pemberian: admin_grant\n";
}

echo "\n🎯 Sekarang user dapat:\n";
echo "   1. Melihat menu SPMB di sidebar\n";
echo "   2. Mengakses semua fitur SPMB\n";
echo "   3. Mengelola pendaftaran SPMB\n\n";

echo "=== SELESAI ===\n";
