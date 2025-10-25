<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin user
        $superadminId = DB::table('users')->insertGetId([
            'name' => 'Super Administrator',
            'email' => 'superadmin@sppqu.com',
            'password' => Hash::make('superadmin123'),
            'role' => 'superadmin',
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addYears(10), // 10 tahun subscription
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get superadmin role ID
        $superadminRoleId = DB::table('roles')->where('name', 'superadmin')->value('id');

        if ($superadminRoleId) {
            // Assign superadmin role to user
            DB::table('role_user')->insert([
                'role_id' => $superadminRoleId,
                'user_id' => $superadminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create admin user
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Administrator',
            'email' => 'admin@sppqu.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addYears(5), // 5 tahun subscription
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get admin role ID
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

        if ($adminRoleId) {
            // Assign admin role to user
            DB::table('role_user')->insert([
                'role_id' => $adminRoleId,
                'user_id' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create operator user
        $operatorId = DB::table('users')->insertGetId([
            'name' => 'Operator',
            'email' => 'operator@sppqu.com',
            'password' => Hash::make('operator123'),
            'role' => 'operator',
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addYears(2), // 2 tahun subscription
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get operator role ID
        $operatorRoleId = DB::table('roles')->where('name', 'operator')->value('id');

        if ($operatorRoleId) {
            // Assign operator role to user
            DB::table('role_user')->insert([
                'role_id' => $operatorRoleId,
                'user_id' => $operatorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "✅ SuperAdmin users created successfully!\n";
        echo "📧 SuperAdmin: superadmin@sppqu.com / superadmin123\n";
        echo "📧 Admin: admin@sppqu.com / admin123\n";
        echo "📧 Operator: operator@sppqu.com / operator123\n";
        echo "🔐 All users have active subscriptions\n";
    }
}
