<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranSiswa;
use App\Models\Pelanggaran;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BKDashboardController extends Controller
{
    /**
     * Display BK Dashboard
     */
    public function index()
    {
        // 1. Statistics
        $totalPelanggaran = PelanggaranSiswa::where('status', 'approved')->count();
        $pelanggaranPending = PelanggaranSiswa::where('status', 'pending')->count();
        $pelanggaranBulanIni = PelanggaranSiswa::where('status', 'approved')
            ->whereMonth('tanggal_pelanggaran', date('m'))
            ->whereYear('tanggal_pelanggaran', date('Y'))
            ->count();
        
        $totalSiswaBermasalah = Student::whereHas('pelanggaranSiswa', function($q) {
            $q->where('status', 'approved');
        })->count();

        // 2. Top 5 Siswa Bermasalah
        $topSiswaBermasalah = Student::withCount([
            'pelanggaranSiswa as total_pelanggaran' => function($q) {
                $q->where('status', 'approved');
            }
        ])->having('total_pelanggaran', '>', 0)
          ->orderBy('total_pelanggaran', 'desc')
          ->limit(5)
          ->get();

        // Calculate total points for each
        foreach ($topSiswaBermasalah as $student) {
            $student->total_point = PelanggaranSiswa::getTotalPointSiswa($student->student_id);
        }

        // 3. Recent Pelanggaran (7 hari terakhir)
        $recentPelanggaran = PelanggaranSiswa::with(['siswa', 'pelanggaran.kategori'])
            ->where('tanggal_pelanggaran', '>=', now()->subDays(7))
            ->latest('tanggal_pelanggaran')
            ->limit(10)
            ->get();

        // 4. Statistik per Kategori
        $statsByCategory = DB::table('pelanggaran_siswa')
            ->join('pelanggaran', 'pelanggaran_siswa.pelanggaran_id', '=', 'pelanggaran.id')
            ->join('pelanggaran_kategori', 'pelanggaran.kategori_id', '=', 'pelanggaran_kategori.id')
            ->where('pelanggaran_siswa.status', 'approved')
            ->select('pelanggaran_kategori.nama', DB::raw('COUNT(*) as total'))
            ->groupBy('pelanggaran_kategori.nama')
            ->get();

        // 5. Chart Data - Pelanggaran per Bulan (6 bulan terakhir)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = PelanggaranSiswa::where('status', 'approved')
                ->whereMonth('tanggal_pelanggaran', $month->month)
                ->whereYear('tanggal_pelanggaran', $month->year)
                ->count();
            
            $chartData['labels'][] = $month->format('M Y');
            $chartData['data'][] = $count;
        }

        // 6. Pelanggaran Terbanyak
        $topPelanggaran = Pelanggaran::withCount([
            'pelanggaranSiswa as total' => function($q) {
                $q->where('status', 'approved');
            }
        ])->having('total', '>', 0)
          ->orderBy('total', 'desc')
          ->limit(5)
          ->get();

        return view('manage.bk.dashboard', compact(
            'totalPelanggaran',
            'pelanggaranPending',
            'pelanggaranBulanIni',
            'totalSiswaBermasalah',
            'topSiswaBermasalah',
            'recentPelanggaran',
            'statsByCategory',
            'chartData',
            'topPelanggaran'
        ));
    }

    /**
     * Halaman Bimbingan Konseling
     * Menampilkan siswa yang memerlukan bimbingan
     */
    public function bimbinganKonseling()
    {
        // Siswa dengan point tinggi (perlu bimbingan)
        $siswaPerluBimbingan = Student::withCount([
            'pelanggaranSiswa as total_pelanggaran' => function($q) {
                $q->where('status', 'approved');
            }
        ])->having('total_pelanggaran', '>', 0)
          ->orderBy('total_pelanggaran', 'desc')
          ->get();

        // Calculate total points for each
        foreach ($siswaPerluBimbingan as $student) {
            $student->total_point = PelanggaranSiswa::getTotalPointSiswa($student->student_id);
        }

        // Filter siswa dengan point >= 25 (perlu perhatian khusus)
        $siswaPerluBimbingan = $siswaPerluBimbingan->filter(function($student) {
            return $student->total_point >= 25;
        });

        return view('manage.bk.bimbingan-konseling', compact('siswaPerluBimbingan'));
    }
}

