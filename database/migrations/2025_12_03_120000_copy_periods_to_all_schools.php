<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ambil semua sekolah
        $schools = DB::table('schools')->get();
        
        if ($schools->count() == 0) {
            return;
        }
        
        // Ambil periode yang sudah ada (untuk di-copy)
        $existingPeriods = DB::table('periods')
            ->whereNotNull('school_id')
            ->orderBy('period_id')
            ->get();
        
        // Jika tidak ada periode, buat periode default untuk setiap sekolah
        if ($existingPeriods->count() == 0) {
            $currentYear = date('Y');
            $nextYear = $currentYear + 1;
            
            foreach ($schools as $school) {
                // Cek apakah sekolah ini sudah punya periode
                $schoolPeriods = DB::table('periods')
                    ->where('school_id', $school->id)
                    ->count();
                
                if ($schoolPeriods == 0) {
                    // Buat periode default untuk sekolah ini
                    DB::table('periods')->insert([
                        'period_start' => $currentYear,
                        'period_end' => $nextYear,
                        'period_status' => 1, // Aktif
                        'school_id' => $school->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        } else {
            // Copy periode yang sudah ada ke semua sekolah yang belum punya periode
            foreach ($schools as $school) {
                // Cek apakah sekolah ini sudah punya periode
                $schoolPeriods = DB::table('periods')
                    ->where('school_id', $school->id)
                    ->count();
                
                if ($schoolPeriods == 0) {
                    // Copy semua periode yang sudah ada ke sekolah ini
                    foreach ($existingPeriods as $period) {
                        DB::table('periods')->insert([
                            'period_start' => $period->period_start,
                            'period_end' => $period->period_end,
                            'period_status' => $school->id == $period->school_id ? $period->period_status : 0, // Hanya aktif untuk sekolah asal, yang lain non-aktif
                            'school_id' => $school->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Tidak perlu rollback karena ini adalah data migration
    }
};




