<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateExistingDataSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('foundations') || !Schema::hasTable('schools')) {
            $this->command->error('Tabel foundations atau schools belum ada! Jalankan migration terlebih dahulu.');
            return;
        }

        // 1. Buat Foundation Default
        $foundation = DB::table('foundations')->first();
        if (!$foundation) {
            $foundationId = DB::table('foundations')->insertGetId([
                'nama_yayasan' => 'Yayasan Default',
                'alamat_yayasan' => 'Alamat Yayasan',
                'no_telp_yayasan' => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("Foundation default dibuat dengan ID: {$foundationId}");
        } else {
            $foundationId = $foundation->id;
            $this->command->info("Menggunakan foundation existing dengan ID: {$foundationId}");
        }

        // 2. Update schools dengan foundation_id jika belum
        $schools = DB::table('schools')->whereNull('foundation_id')->get();
        if ($schools->count() > 0) {
            DB::table('schools')
                ->whereNull('foundation_id')
                ->update(['foundation_id' => $foundationId]);
            $this->command->info("Updated {$schools->count()} schools dengan foundation_id");
        }

        // 3. Pastikan ada School Default
        $school = DB::table('schools')->where('foundation_id', $foundationId)->first();
        if (!$school) {
            // Buat school default baru
            $schoolId = DB::table('schools')->insertGetId([
                'foundation_id' => $foundationId,
                'jenjang' => 'SMA',
                'nama_sekolah' => 'Sekolah Default',
                'alamat' => '',
                'no_telp' => '',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("School default dibuat dengan ID: {$schoolId}");
        } else {
            $schoolId = $school->id;
            $this->command->info("Menggunakan school existing dengan ID: {$schoolId}");
        }

        // 4. Update user_schools untuk semua user existing
        if (Schema::hasTable('users') && Schema::hasTable('user_schools')) {
            $users = DB::table('users')->get();
            foreach ($users as $user) {
                $exists = DB::table('user_schools')
                    ->where('user_id', $user->id)
                    ->where('school_id', $schoolId)
                    ->exists();
                
                if (!$exists) {
                    DB::table('user_schools')->insert([
                        'user_id' => $user->id,
                        'school_id' => $schoolId,
                        'role' => $user->role ?? 'staff',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $this->command->info("User-school relationships created");
        }

        $this->command->info('✅ Migrasi data existing selesai!');
        $this->command->info("📊 Foundation ID: {$foundationId}");
        $this->command->info("📚 School ID: {$schoolId}");
    }
}

