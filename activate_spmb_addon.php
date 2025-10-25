<?php
/**
 * Script untuk mengaktifkan addon SPMB setelah pembayaran berhasil
 * Jalankan: php activate_spmb_addon.php [user_id]
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\UserAddon;
use App\Models\Addon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

function activateSPMBAddon($userId) {
    try {
        // Cari addon SPMB
        $addon = Addon::where('slug', 'spmb')->first();
        if (!$addon) {
            echo "❌ Addon SPMB tidak ditemukan!\n";
            return false;
        }

        // Cari user addon
        $userAddon = UserAddon::where('user_id', $userId)
            ->where('addon_id', $addon->id)
            ->first();

        if (!$userAddon) {
            echo "❌ User addon tidak ditemukan untuk user ID: $userId\n";
            return false;
        }

        // Update status
        $userAddon->update([
            'status' => 'active',
            'updated_at' => now()
        ]);

        echo "✅ Addon SPMB berhasil diaktifkan untuk user ID: $userId\n";
        echo "📅 Tanggal aktivasi: " . now()->format('Y-m-d H:i:s') . "\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Main execution
if ($argc < 2) {
    echo "Usage: php activate_spmb_addon.php [user_id]\n";
    echo "Example: php activate_spmb_addon.php 1\n";
    exit(1);
}

$userId = $argv[1];
activateSPMBAddon($userId);
?>
