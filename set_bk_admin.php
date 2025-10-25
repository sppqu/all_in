<?php

/**
 * Script untuk membuat/update user sebagai Admin BK
 * 
 * Usage:
 * php set_bk_admin.php [email|username|id]
 * 
 * Contoh:
 * php set_bk_admin.php admin@sppqu.com
 * php set_bk_admin.php 1
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "\n";
echo "===========================================\n";
echo " SET USER SEBAGAI ADMIN BK\n";
echo "===========================================\n\n";

// Get parameter
$identifier = $argv[1] ?? null;

if (!$identifier) {
    echo "❌ Error: Parameter tidak ditemukan!\n\n";
    echo "Usage: php set_bk_admin.php [email|username|id]\n\n";
    echo "Contoh:\n";
    echo "  php set_bk_admin.php admin@sppqu.com\n";
    echo "  php set_bk_admin.php admin\n";
    echo "  php set_bk_admin.php 1\n\n";
    exit(1);
}

try {
    // Find user
    $user = User::where('email', $identifier)
        ->orWhere('username', $identifier)
        ->orWhere('id', $identifier)
        ->first();
    
    if (!$user) {
        echo "❌ Error: User tidak ditemukan dengan identifier: {$identifier}\n\n";
        
        // Show available users
        echo "User yang tersedia:\n";
        $users = User::orderBy('id')->get();
        foreach ($users as $u) {
            echo "  - ID: {$u->id} | Username: {$u->username} | Email: {$u->email} | Role: {$u->role}\n";
        }
        echo "\n";
        exit(1);
    }
    
    echo "User ditemukan:\n";
    echo "  ID       : {$user->id}\n";
    echo "  Username : {$user->username}\n";
    echo "  Email    : {$user->email}\n";
    echo "  Role     : {$user->role}\n";
    echo "  is_bk    : " . ($user->is_bk ? 'Yes' : 'No') . "\n\n";
    
    // Update is_bk
    $user->is_bk = true;
    $user->save();
    
    echo "✅ User berhasil diset sebagai Admin BK!\n\n";
    
    echo "Informasi Login:\n";
    echo "  URL      : " . url('/login') . "\n";
    echo "  Username : {$user->username}\n";
    echo "  Email    : {$user->email}\n\n";
    
    echo "Setelah login, user akan diarahkan ke:\n";
    echo "  " . route('manage.bk.dashboard') . "\n\n";
    
    echo "Menu yang dapat diakses:\n";
    echo "  - Dashboard BK\n";
    echo "  - Data Pelanggaran\n";
    echo "  - Bimbingan Konseling\n";
    echo "  - Laporan Rekap\n";
    echo "  - Master Pelanggaran\n\n";
    
    echo "Menu lain (SPP, SPMB, dll) TIDAK DAPAT diakses.\n\n";
    
    echo "===========================================\n";
    echo " SELESAI!\n";
    echo "===========================================\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

