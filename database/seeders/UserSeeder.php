<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin - Akses penuh ke semua fitur
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@sppqu.com',
            'password' => Hash::make('superadmin123'),
            'role' => 'superadmin',
            'nomor_wa' => '081234567890',
        ]);

        // Admin - Akses ke manajemen data dan laporan
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@sppqu.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'nomor_wa' => '081234567891',
        ]);

        // Kepala Sekolah - Akses ke laporan dan monitoring
        User::create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepsek@sppqu.com',
            'password' => Hash::make('kepsek123'),
            'role' => 'admin',
            'nomor_wa' => '081234567892',
        ]);

        // Bendahara - Akses ke keuangan dan pembayaran
        User::create([
            'name' => 'Bendahara',
            'email' => 'bendahara@sppqu.com',
            'password' => Hash::make('bendahara123'),
            'role' => 'admin',
            'nomor_wa' => '081234567893',
        ]);

        // Kasir/Operator - Akses ke transaksi pembayaran
        User::create([
            'name' => 'Kasir Utama',
            'email' => 'kasir@sppqu.com',
            'password' => Hash::make('kasir123'),
            'role' => 'operator',
            'nomor_wa' => '081234567894',
        ]);

        // Operator Pembayaran
        User::create([
            'name' => 'Operator Pembayaran',
            'email' => 'operator@sppqu.com',
            'password' => Hash::make('operator123'),
            'role' => 'operator',
            'nomor_wa' => '081234567895',
        ]);

        // Staff TU - Akses terbatas
        User::create([
            'name' => 'Staff TU',
            'email' => 'staff@sppqu.com',
            'password' => Hash::make('staff123'),
            'role' => 'operator',
            'nomor_wa' => '081234567896',
        ]);

        // User untuk testing dan development
        User::create([
            'name' => 'Test User',
            'email' => 'test@sppqu.com',
            'password' => Hash::make('test123'),
            'role' => 'operator',
            'nomor_wa' => '081234567897',
        ]);

        // Demo user untuk presentasi
        User::create([
            'name' => 'Demo User',
            'email' => 'demo@sppqu.com',
            'password' => Hash::make('demo123'),
            'role' => 'operator',
            'nomor_wa' => '081234567898',
        ]);
    }
}
