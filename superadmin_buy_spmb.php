<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Addon;
use App\Models\UserAddon;
use App\Models\User;

echo "=== SUPERADMIN MEMBELI ADDON SPMB ===\n\n";

// Cari superadmin user
$superadmin = User::where('role', 'superadmin')->first();

if (!$superadmin) {
    echo "❌ Error: Superadmin tidak ditemukan!\n";
    exit(1);
}

echo "👤 Superadmin ditemukan: {$superadmin->name} (ID: {$superadmin->id})\n";

// Cari addon SPMB
$spmbAddon = Addon::where('slug', 'spmb')->first();

if (!$spmbAddon) {
    echo "❌ Error: Addon SPMB tidak ditemukan!\n";
    exit(1);
}

echo "📦 Addon SPMB ditemukan: {$spmbAddon->name} (Harga: Rp " . number_format($spmbAddon->price, 0, ',', '.') . ")\n\n";

// Cek apakah superadmin sudah memiliki addon
$existingAddon = UserAddon::where('user_id', $superadmin->id)
    ->where('addon_id', $spmbAddon->id)
    ->first();

if ($existingAddon) {
    if ($existingAddon->status === 'active') {
        echo "✅ Superadmin sudah memiliki addon SPMB aktif!\n";
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
    echo "🛒 Membeli addon SPMB untuk superadmin...\n";
    
    UserAddon::create([
        'user_id' => $superadmin->id,
        'addon_id' => $spmbAddon->id,
        'status' => 'active',
        'purchased_at' => now(),
        'amount_paid' => $spmbAddon->price,
        'payment_method' => 'superadmin_purchase',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Addon SPMB berhasil dibeli dan diaktifkan!\n";
    echo "📅 Tanggal pembelian: " . now()->format('Y-m-d H:i:s') . "\n";
    echo "💰 Harga: Rp " . number_format($spmbAddon->price, 0, ',', '.') . "\n";
    echo "💳 Metode pembayaran: superadmin_purchase\n";
}

echo "\n🎯 Sekarang superadmin dapat:\n";
echo "   1. Mengakses menu SPMB\n";
echo "   2. Memberikan akses addon ke user lain\n";
echo "   3. Mengelola semua fitur SPMB\n\n";

echo "📋 Cara memberikan akses ke user lain:\n";
echo "   - Via Browser: /grant-addon/{userId}/spmb\n";
echo "   - Via Database: Update user_addons table\n";
echo "   - Via Tinker: grantAddonToUser(userId, 'spmb')\n\n";

echo "=== SELESAI ===\n";
