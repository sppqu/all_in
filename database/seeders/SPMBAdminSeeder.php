<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SPMBAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create SPMB Admin user
        User::create([
            'name' => 'SPMB Administrator',
            'email' => 'spmb@sppqu.com',
            'password' => Hash::make('spmb123'),
            'role' => 'spmb_admin',
            'nomor_wa' => '081234567899',
        ]);

        // Create SPMB Staff user
        User::create([
            'name' => 'SPMB Staff',
            'email' => 'spmbstaff@sppqu.com',
            'password' => Hash::make('spmbstaff123'),
            'role' => 'spmb_admin',
            'nomor_wa' => '081234567900',
        ]);

        echo "✅ SPMB Admin users created successfully!\n";
        echo "📧 SPMB Admin: spmb@sppqu.com / spmb123\n";
        echo "📧 SPMB Staff: spmbstaff@sppqu.com / spmbstaff123\n";
        echo "🔐 Both users have SPMB admin access\n";
    }
}






