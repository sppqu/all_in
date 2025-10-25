<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SPMBRegistration;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║          RESET SEMUA PASSWORD SPMB KE DEFAULT             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Ambil semua user SPMB
$users = SPMBRegistration::all();

if ($users->count() === 0) {
    echo "❌ Tidak ada user SPMB ditemukan!\n";
    exit;
}

echo "📊 Total user SPMB: " . $users->count() . "\n\n";
echo str_repeat("=", 60) . "\n\n";

foreach ($users as $user) {
    echo "User #{$user->id}\n";
    echo "├─ Nama    : {$user->name}\n";
    echo "├─ HP      : {$user->phone}\n";
    echo "├─ Status  : {$user->status}\n";
    
    // Password default adalah 6 digit terakhir HP
    $defaultPassword = substr($user->phone, -6);
    
    // Cek password saat ini
    $currentValid = $user->checkPassword($defaultPassword);
    
    if ($currentValid) {
        echo "├─ Password: ✅ Sudah menggunakan default ($defaultPassword)\n";
    } else {
        echo "├─ Password: ❌ TIDAK menggunakan default\n";
        echo "│\n";
        echo "├─ 🔄 Mereset password ke default...\n";
        
        // Reset ke password default
        $user->update(['password' => $defaultPassword]);
        $user->refresh();
        
        // Test ulang
        $testAfterReset = $user->checkPassword($defaultPassword);
        
        if ($testAfterReset) {
            echo "└─ ✅ Password berhasil direset ke: $defaultPassword\n";
        } else {
            echo "└─ ❌ GAGAL reset password!\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    RINGKASAN LOGIN                        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

foreach ($users as $user) {
    $defaultPassword = substr($user->phone, -6);
    echo "👤 {$user->name}\n";
    echo "   HP       : {$user->phone}\n";
    echo "   Password : {$defaultPassword}\n";
    echo "   ---\n";
}

echo "\n✅ Semua password telah direset ke 6 digit terakhir nomor HP!\n";
echo "Silakan coba login sekarang.\n\n";

