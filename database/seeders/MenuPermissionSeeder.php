<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuPermission;

class MenuPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin - Akses penuh ke semua menu
        $superadminMenus = [
            'menu.dashboard' => true,
            'menu.data_master' => true,
            'menu.students' => true,
            'menu.classes' => true,
            'menu.majors' => true,
            'menu.periods' => true,
            'menu.pos' => true,
            'menu.account_codes' => true,
            'menu.users' => true,
            'menu.payment' => true,
            'menu.online_payment' => true,
            'menu.setting_tarif' => true,
            'menu.pembayaran' => true,
            'menu.tabungan' => true,
            'menu.akuntansi' => true,
            'menu.laporan' => true,
            'menu.billing' => true,
            'menu.kirim_tagihan' => true,
            'menu.general_setting' => true,
            'menu.spmb' => true,
            'menu.spmb.waves' => true,
            'menu.spmb.additional-fees' => true,
        ];

        foreach ($superadminMenus as $menu => $allowed) {
            MenuPermission::updateOrCreate(
                ['role' => 'superadmin', 'menu_key' => $menu],
                ['allowed' => $allowed]
            );
        }

        // Admin - Akses ke manajemen data dan laporan
        $adminMenus = [
            'menu.dashboard' => true,
            'menu.data_master' => true,
            'menu.students' => true,
            'menu.classes' => true,
            'menu.majors' => true,
            'menu.periods' => true,
            'menu.pos' => true,
            'menu.account_codes' => true,
            'menu.users' => true,
            'menu.payment' => true,
            'menu.online_payment' => true,
            'menu.setting_tarif' => true,
            'menu.pembayaran' => true,
            'menu.tabungan' => true,
            'menu.akuntansi' => true,
            'menu.laporan' => true,
            'menu.billing' => true,
            'menu.kirim_tagihan' => true,
            'menu.general_setting' => false, // Tidak bisa akses settings
            'menu.spmb' => true,
            'menu.spmb.waves' => true,
            'menu.spmb.additional-fees' => true,
        ];

        foreach ($adminMenus as $menu => $allowed) {
            MenuPermission::updateOrCreate(
                ['role' => 'admin', 'menu_key' => $menu],
                ['allowed' => $allowed]
            );
        }

        // Operator - Akses terbatas ke transaksi
        $operatorMenus = [
            'menu.dashboard' => true,
            'menu.data_master' => false,
            'menu.students' => true,
            'menu.classes' => false,
            'menu.majors' => false,
            'menu.periods' => false,
            'menu.pos' => true,
            'menu.account_codes' => false,
            'menu.users' => false,
            'menu.payment' => true,
            'menu.online_payment' => true,
            'menu.setting_tarif' => false,
            'menu.pembayaran' => true,
            'menu.tabungan' => true,
            'menu.akuntansi' => true,
            'menu.laporan' => true,
            'menu.billing' => false,
            'menu.kirim_tagihan' => false,
            'menu.general_setting' => false,
        ];

        foreach ($operatorMenus as $menu => $allowed) {
            MenuPermission::updateOrCreate(
                ['role' => 'operator', 'menu_key' => $menu],
                ['allowed' => $allowed]
            );
        }

        // Guest - Tidak ada akses
        $guestMenus = [
            'menu.dashboard' => false,
            'menu.data_master' => false,
            'menu.students' => false,
            'menu.classes' => false,
            'menu.majors' => false,
            'menu.periods' => false,
            'menu.pos' => false,
            'menu.account_codes' => false,
            'menu.users' => false,
            'menu.payment' => false,
            'menu.online_payment' => false,
            'menu.setting_tarif' => false,
            'menu.pembayaran' => false,
            'menu.tabungan' => false,
            'menu.akuntansi' => false,
            'menu.laporan' => false,
            'menu.billing' => false,
            'menu.kirim_tagihan' => false,
            'menu.general_setting' => false,
        ];

        foreach ($guestMenus as $menu => $allowed) {
            MenuPermission::updateOrCreate(
                ['role' => 'guest', 'menu_key' => $menu],
                ['allowed' => $allowed]
            );
        }

        // SPMB Admin - Hanya akses SPMB
        $spmbAdminMenus = [
            'menu.dashboard' => true,
            'menu.data_master' => false,
            'menu.students' => false,
            'menu.classes' => false,
            'menu.majors' => false,
            'menu.periods' => false,
            'menu.pos' => false,
            'menu.account_codes' => false,
            'menu.users' => false,
            'menu.payment' => false,
            'menu.online_payment' => false,
            'menu.setting_tarif' => false,
            'menu.pembayaran' => false,
            'menu.tabungan' => false,
            'menu.akuntansi' => false,
            'menu.laporan' => false,
            'menu.billing' => false,
            'menu.kirim_tagihan' => false,
            'menu.general_setting' => false,
            'menu.spmb' => true, // Hanya akses SPMB
            'menu.spmb.waves' => true,
        ];

        foreach ($spmbAdminMenus as $menu => $allowed) {
            MenuPermission::updateOrCreate(
                ['role' => 'spmb_admin', 'menu_key' => $menu],
                ['allowed' => $allowed]
            );
        }
    }
}
