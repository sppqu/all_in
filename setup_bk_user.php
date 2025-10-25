<?php

/**
 * Script untuk setup user BK dan activate addon BK
 * 
 * Usage: php setup_bk_user.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Addon;
use App\Models\UserAddon;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "   SETUP USER BK & ACTIVATE ADDON BK   \n";
echo "========================================\n\n";

try {
    // 1. Cari atau buat addon BK
    echo "1. Checking BK Addon...\n";
    $bkAddon = Addon::where('slug', 'bk')->first();
    
    if (!$bkAddon) {
        echo "   ❌ BK Addon tidak ditemukan!\n";
        echo "   Running seeder...\n";
        
        Artisan::call('db:seed', [
            '--class' => 'AddonSeeder',
            '--force' => true
        ]);
        
        $bkAddon = Addon::where('slug', 'bk')->first();
    }
    
    if ($bkAddon) {
        echo "   ✅ BK Addon found: {$bkAddon->name}\n";
        echo "      ID: {$bkAddon->id}\n";
        echo "      Price: Rp " . number_format($bkAddon->price, 0, ',', '.') . "\n\n";
    } else {
        die("   ❌ Failed to create BK Addon!\n\n");
    }
    
    // 2. Cari atau buat user admin
    echo "2. Setup User BK...\n";
    echo "   Enter WhatsApp number (e.g., 628123456789): ";
    $phone = trim(fgets(STDIN));
    
    $user = User::where('nomor_wa', $phone)->first();
    
    if (!$user) {
        echo "   User not found. Creating new user...\n";
        echo "   Enter name: ";
        $name = trim(fgets(STDIN));
        
        $user = User::create([
            'name' => $name,
            'nomor_wa' => $phone,
            'role' => 'admin',
            'is_bk' => true,
            'email' => $phone . '@example.com' // Dummy email
        ]);
        
        echo "   ✅ User created: {$user->name}\n";
    } else {
        echo "   ✅ User found: {$user->name}\n";
        
        // Update is_bk flag
        if (!$user->is_bk) {
            $user->update(['is_bk' => true]);
            echo "   ✅ User updated to BK role\n";
        } else {
            echo "   ✅ User already has BK role\n";
        }
    }
    
    echo "      ID: {$user->id}\n";
    echo "      Phone: {$user->nomor_wa}\n";
    echo "      Role: {$user->role}\n";
    echo "      Is BK: " . ($user->is_bk ? 'Yes' : 'No') . "\n\n";
    
    // 3. Activate addon untuk user
    echo "3. Activating BK Addon for user...\n";
    
    $userAddon = UserAddon::where('user_id', $user->id)
        ->where('addon_id', $bkAddon->id)
        ->first();
    
    if ($userAddon) {
        if ($userAddon->status !== 'active') {
            $userAddon->update(['status' => 'active']);
            echo "   ✅ Addon activated (was inactive)\n";
        } else {
            echo "   ✅ Addon already active\n";
        }
    } else {
        UserAddon::create([
            'user_id' => $user->id,
            'addon_id' => $bkAddon->id,
            'status' => 'active',
            'purchased_at' => now(),
            'amount_paid' => $bkAddon->price
        ]);
        echo "   ✅ Addon activated (newly added)\n";
    }
    
    echo "\n";
    echo "========================================\n";
    echo "              ✅ SUCCESS!               \n";
    echo "========================================\n\n";
    
    echo "User Setup:\n";
    echo "  Name: {$user->name}\n";
    echo "  Phone: {$user->nomor_wa}\n";
    echo "  Role: BK Admin\n\n";
    
    echo "Next Steps:\n";
    echo "  1. Login via OTP: {$user->nomor_wa}\n";
    echo "  2. You will be redirected to BK Dashboard\n";
    echo "  3. Access: /manage/bk\n\n";
    
    echo "BK Dashboard Features:\n";
    echo "  ✅ Modern & Interactive UI\n";
    echo "  ✅ Real-time Statistics\n";
    echo "  ✅ Charts & Graphs\n";
    echo "  ✅ Top 5 Problem Students\n";
    echo "  ✅ Recent Activities\n";
    echo "  ✅ Quick Actions\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    exit(1);
}

