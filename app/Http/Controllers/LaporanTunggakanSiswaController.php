<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanTunggakanSiswaController extends Controller
{
    /**
     * Menampilkan halaman laporan tunggakan siswa
     */
    public function index(Request $request)
    {
        // Ambil data untuk filter
        $classes = DB::table('class_models')
            ->select('class_id', 'class_name')
            ->orderBy('class_name')
            ->get();

        $posList = DB::table('pos_pembayaran')
            ->select('pos_id', 'pos_name')
            ->orderBy('pos_name')
            ->get();

        $students = DB::table('students')
            ->select('student_id', 'student_full_name', 'student_nis')
            ->orderBy('student_full_name')
            ->get();

        // Ambil data tahun pelajaran
        $periods = DB::table('periods')
            ->select('period_id', DB::raw('CONCAT(period_start, "/", period_end) as period_name'))
            ->orderBy('period_start', 'desc')
            ->get();

        // Data bulan untuk dropdown
        $months = [
            ['id' => 1, 'name' => 'Juli'],
            ['id' => 2, 'name' => 'Agustus'],
            ['id' => 3, 'name' => 'September'],
            ['id' => 4, 'name' => 'Oktober'],
            ['id' => 5, 'name' => 'November'],
            ['id' => 6, 'name' => 'Desember'],
            ['id' => 7, 'name' => 'Januari'],
            ['id' => 8, 'name' => 'Februari'],
            ['id' => 9, 'name' => 'Maret'],
            ['id' => 10, 'name' => 'April'],
            ['id' => 11, 'name' => 'Mei'],
            ['id' => 12, 'name' => 'Juni']
        ];

        // Filter parameters - tahun pelajaran dan bulan
        $periodId = $request->filled('period_id') ? $request->period_id : null;
        $monthId = $request->filled('month_id') ? $request->month_id : null;
        $studentId = $request->filled('student_id') ? $request->student_id : null;
        $posId = $request->filled('pos_id') ? $request->pos_id : null;
        $classId = $request->filled('class_id') ? $request->class_id : null;
        $studentStatus = $request->filled('student_status') ? $request->student_status : null;
        
        // Debug: Log filter parameters
        \Log::info('Filter Parameters:', [
            'periodId' => $periodId,
            'monthId' => $monthId,
            'studentId' => $studentId,
            'posId' => $posId,
            'classId' => $classId,
            'studentStatus' => $studentStatus
        ]);

        // Query untuk data tunggakan
        $tunggakanData = $this->getTunggakanData($periodId, $monthId, $studentId, $posId, $classId, $studentStatus);

        // Hitung total tunggakan
        $totalTunggakan = $tunggakanData->sum('total_tunggakan');
        $totalSiswa = $tunggakanData->count();
        $rataRataTunggakan = $totalSiswa > 0 ? $totalTunggakan / $totalSiswa : 0;

        $school_profile = \App\Models\SchoolProfile::first();

        return view('admin.laporan.tunggakan-siswa.index', compact(
            'tunggakanData',
            'classes',
            'posList',
            'students',
            'periods',
            'months',
            'periodId',
            'monthId',
            'studentId',
            'posId',
            'classId',
            'studentStatus',
            'totalTunggakan',
            'totalSiswa',
            'rataRataTunggakan',
            'school_profile'
        ));
    }

    /**
     * Ambil data tunggakan siswa
     */
    private function getTunggakanData($periodId, $monthId, $studentId, $posId, $classId, $studentStatus)
    {
        try {
            $result = collect();

            // Query untuk bulanan - menggunakan LEFT JOIN untuk menangani foreign key yang tidak valid
            $bulananQuery = DB::table('bulan as b')
                ->leftJoin('students as s', 'b.student_student_id', '=', 's.student_id')
                ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                ->whereRaw('CAST(b.bulan_bill AS DECIMAL(10,2)) > 0')
                ->whereNull('b.bulan_date_pay'); // bulan_date_pay NULL berarti belum dibayar

            // Query untuk bebas - menggunakan LEFT JOIN untuk menangani foreign key yang tidak valid
            $bebasQuery = DB::table('bebas as be')
                ->leftJoin('students as s', 'be.student_student_id', '=', 's.student_id')
                ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                ->leftJoin('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                ->where('be.bebas_bill', '>', 0)
                ->whereRaw('CAST(be.bebas_total_pay AS DECIMAL(10,2)) < CAST(be.bebas_bill AS DECIMAL(10,2))'); // Total pay < bill berarti belum lunas

            // Apply period and month filter
            if ($periodId) {
                $bulananQuery->where('per.period_id', $periodId);
                $bebasQuery->where('per.period_id', $periodId);
            }
            
            if ($monthId) {
                // Filter bulanan: tampilkan tagihan dari bulan 1 sampai bulan yang dipilih
                $bulananQuery->where('b.month_month_id', '<=', $monthId);
                // Untuk bebas, tidak ada filter bulan
            }

            // Apply filters
            if ($studentId) {
                // Untuk student filter, gunakan student_student_id dari tabel bulan/bebas
                $bulananQuery->where('b.student_student_id', $studentId);
                $bebasQuery->where('be.student_student_id', $studentId);
            }

            if ($posId) {
                $bulananQuery->where('pos.pos_id', $posId);
                $bebasQuery->where('pos.pos_id', $posId);
            }

            if ($classId) {
                $bulananQuery->where('c.class_id', $classId);
                $bebasQuery->where('c.class_id', $classId);
            }

            if ($studentStatus !== null) {
                $bulananQuery->where('s.student_status', $studentStatus);
                $bebasQuery->where('s.student_status', $studentStatus);
            }

            // Debug: Log query untuk bulanan
            \Log::info('Bulanan Query SQL:', [
                'sql' => $bulananQuery->toSql(),
                'bindings' => $bulananQuery->getBindings()
            ]);

            // Get bulanan data dengan fallback untuk data yang tidak memiliki relasi
            $bulananData = $bulananQuery->select(
                DB::raw('COALESCE(s.student_id, b.student_student_id) as student_id'), // Gunakan student_student_id jika tidak ada student
                DB::raw('COALESCE(s.student_full_name, CONCAT("Student ID: ", b.student_student_id, " (Data Tidak Valid)")) as student_full_name'),
                DB::raw('COALESCE(s.student_nis, CONCAT("NIS-", b.student_student_id)) as student_nis'),
                DB::raw('COALESCE(s.student_status, 1) as student_status'),
                DB::raw('COALESCE(c.class_name, "Kelas Tidak Diketahui") as class_name'),
                DB::raw('COALESCE(pos.pos_name, "POS Tidak Diketahui") as pos_name'),
                'b.bulan_bill',
                DB::raw('CASE WHEN bulan_status = 1 THEN bulan_bill ELSE 0 END as bulan_pay'), // Untuk bulanan, jika status = 1 berarti lunas
                DB::raw('b.bulan_bill as tunggakan'), // Tunggakan = bill karena belum dibayar
                DB::raw('b.bulan_input_date as tanggal'),
                DB::raw("'bulanan' as jenis"),
                DB::raw('b.bulan_id as bill_id'),
                DB::raw('CONCAT(per.period_start, "/", per.period_end) as period_name'),
                'b.month_month_id as bulan',
                DB::raw('YEAR(b.bulan_input_date) as tahun')
            )->get();

            // Get bebas data dengan fallback untuk data yang tidak memiliki relasi
            $bebasData = $bebasQuery->select(
                DB::raw('COALESCE(s.student_id, be.student_student_id) as student_id'), // Gunakan student_student_id jika tidak ada student
                DB::raw('COALESCE(s.student_full_name, CONCAT("Student ID: ", be.student_student_id, " (Data Tidak Valid)")) as student_full_name'),
                DB::raw('COALESCE(s.student_nis, CONCAT("NIS-", be.student_student_id)) as student_nis'),
                DB::raw('COALESCE(s.student_status, 1) as student_status'),
                DB::raw('COALESCE(c.class_name, "Kelas Tidak Diketahui") as class_name'),
                DB::raw('COALESCE(pos.pos_name, "POS Tidak Diketahui") as pos_name'),
                'be.bebas_bill',
                'be.bebas_total_pay',
                DB::raw('(be.bebas_bill - be.bebas_total_pay) as tunggakan'),
                DB::raw('be.bebas_input_date as tanggal'),
                DB::raw("'bebas' as jenis"),
                DB::raw('be.bebas_id as bill_id'),
                DB::raw('CONCAT(per.period_start, "/", per.period_end) as period_name'),
                DB::raw('YEAR(be.bebas_input_date) as tahun')
            )->get();

            // Debug: Log jumlah data yang ditemukan
            \Log::info('Tunggakan Data Debug:', [
                'bulanan_count' => $bulananData->count(),
                'bebas_count' => $bebasData->count(),
                'period_id' => $periodId,
                'month_id' => $monthId,
                'bulanan_sample' => $bulananData->take(3)->toArray(),
                'bebas_sample' => $bebasData->take(3)->toArray()
            ]);

            // Combine and group data
            $allData = $bulananData->concat($bebasData);

            // Group by student
            $groupedData = $allData->groupBy('student_id');

            foreach ($groupedData as $studentId => $items) {
                $firstItem = $items->first();
                $totalTunggakan = $items->sum('tunggakan');
                
                // Ambil daftar POS yang unik
                $posList = $items->pluck('pos_name')->unique()->implode(', ');
                
                $detailTunggakan = $items->map(function ($item) {
                    // Format nama bulan
                    $namaBulan = '';
                    if ($item->jenis === 'bulanan' && isset($item->bulan)) {
                        $namaBulan = $this->getNamaBulan($item->bulan);
                    }
                    
                    // Format pos name berdasarkan jenis
                    $posNameFormatted = '';
                    if ($item->jenis === 'bulanan') {
                        // Format: nama_pos (nama_bulan-tahun_pelajaran)
                        $posNameFormatted = $item->pos_name . ' (' . $namaBulan . '-' . ($item->period_name ?? $item->tahun) . ')';
                    } else {
                        // Format: nama_pos-tahun_pelajaran
                        $posNameFormatted = $item->pos_name . '-' . ($item->period_name ?? $item->tahun);
                    }
                    
                    return [
                        'pos_name' => $posNameFormatted,
                        'jenis' => $item->jenis,
                        'bill' => $item->bulan_bill ?? $item->bebas_bill,
                        'pay' => $item->bulan_pay ?? $item->bebas_total_pay ?? 0,
                        'tunggakan' => $item->tunggakan,
                        'tanggal' => $item->tanggal,
                        'bill_id' => $item->bill_id
                    ];
                });

                $result->push([
                    'student_id' => $studentId,
                    'student_name' => $firstItem->student_full_name,
                    'student_nis' => $firstItem->student_nis,
                    'student_status' => $firstItem->student_status,
                    'class_name' => $firstItem->class_name,
                    'pos_list' => $posList,
                    'total_tunggakan' => $totalTunggakan,
                    'jumlah_item' => $items->count(),
                    'detail_tunggakan' => $detailTunggakan
                ]);
            }

            // Sort by total tunggakan descending
            $finalResult = $result->sortByDesc('total_tunggakan')->values();
            
            // Debug: Log final result
            \Log::info('Final Tunggakan Result:', [
                'total_students' => $finalResult->count(),
                'sample_result' => $finalResult->take(3)->toArray()
            ]);
            
            return $finalResult;

        } catch (\Exception $e) {
            \Log::error('Error getting tunggakan data: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Export laporan tunggakan ke PDF
     */
    public function exportPdf(Request $request)
    {
        $periodId = $request->filled('period_id') ? $request->period_id : null;
        $monthId = $request->filled('month_id') ? $request->month_id : null;
        $studentId = $request->filled('student_id') ? $request->student_id : null;
        $posId = $request->filled('pos_id') ? $request->pos_id : null;
        $classId = $request->filled('class_id') ? $request->class_id : null;
        $studentStatus = $request->filled('student_status') ? $request->student_status : null;

        $tunggakanData = $this->getTunggakanData($periodId, $monthId, $studentId, $posId, $classId, $studentStatus);
        
        // Ambil identitas sekolah
        $school = DB::table('school_profiles')->first();

        // Get filter info
        $filterInfo = $this->getFilterInfo($periodId, $monthId, $studentId, $posId, $classId, $studentStatus);

        // Generate PDF
        $pdf = Pdf::loadView('admin.laporan.tunggakan-siswa.pdf', compact(
            'tunggakanData',
            'periodId',
            'monthId',
            'school',
            'filterInfo'
        ));
        
        $filename = 'Laporan_Tunggakan_Siswa';
        if ($periodId) {
            $period = DB::table('periods')->where('period_id', $periodId)->first();
            $periodName = $period ? $period->period_start . '/' . $period->period_end : '';
            $filename .= '_' . $periodName;
        }
        if ($monthId) {
            $monthNames = [1=>'Juli',2=>'Agustus',3=>'September',4=>'Oktober',5=>'November',6=>'Desember',7=>'Januari',8=>'Februari',9=>'Maret',10=>'April',11=>'Mei',12=>'Juni'];
            $monthName = $monthNames[$monthId] ?? '';
            $filename .= '_' . $monthName;
        }
        if (!$periodId && !$monthId) {
            $filename .= '_Semua_Data';
        }
        $filename .= '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export tunggakan per siswa ke PDF
     */
    public function exportPdfStudent(Request $request)
    {
        $periodId = $request->filled('period_id') ? $request->period_id : null;
        $monthId = $request->filled('month_id') ? $request->month_id : null;
        $studentId = $request->filled('student_id') ? $request->student_id : null;
        $posId = $request->filled('pos_id') ? $request->pos_id : null;
        $classId = $request->filled('class_id') ? $request->class_id : null;
        $studentStatus = $request->filled('student_status') ? $request->student_status : null;

        // Ambil hanya data untuk siswa ini
        $data = $this->getTunggakanData($periodId, $monthId, $studentId, $posId, $classId, $studentStatus);
        $studentData = $data->first();
        // Normalisasi struktur agar mudah dipakai di view
        if (is_array($studentData)) {
            $studentData = (object) $studentData;
        }
        // Pastikan detail_tunggakan berupa array sederhana
        if ($studentData && isset($studentData->detail_tunggakan) && $studentData->detail_tunggakan instanceof \Illuminate\Support\Collection) {
            $studentData->detail_tunggakan = $studentData->detail_tunggakan->values()->toArray();
        }

        // Tambahkan nama periode jika tersedia dari parameter
        if ($studentData && !$periodId && empty($studentData->period_name)) {
            // Ambil dari salah satu item detail jika ada
            if (!empty($studentData->detail_tunggakan)) {
                $firstDetail = $studentData->detail_tunggakan[0];
                if (!empty($firstDetail['period_name'])) {
                    $studentData->period_name = $firstDetail['period_name'];
                }
            }
        } elseif ($studentData && $periodId) {
            $period = DB::table('periods')->where('period_id', $periodId)->first();
            if ($period) {
                $studentData->period_name = $period->period_start . '/' . $period->period_end;
            }
        }

        // Identitas sekolah
        $school = DB::table('school_profiles')->first();

        // Jika tidak ada data, buat PDF kosong dengan info siswa
        $pdf = Pdf::loadView('admin.laporan.tunggakan-siswa.pdf-student', [
            'studentData' => $studentData,
            'school' => $school,
            'periodId' => $periodId,
            'monthId' => $monthId
        ]);

        // Nama file
        $filename = 'Tunggakan_' . ($studentData->student_nis ?? 'Siswa') . '.pdf';
        return $pdf->download($filename);
    }


    /**
     * Get filter information for display
     */
    private function getFilterInfo($periodId, $monthId, $studentId, $posId, $classId, $studentStatus)
    {
        $info = [];

        if ($periodId) {
            $period = DB::table('periods')->where('period_id', $periodId)->first();
            $info['period'] = $period ? $period->period_start . '/' . $period->period_end : 'Tidak ditemukan';
        }

        if ($monthId) {
            $monthNames = [1=>'Juli',2=>'Agustus',3=>'September',4=>'Oktober',5=>'November',6=>'Desember',7=>'Januari',8=>'Februari',9=>'Maret',10=>'April',11=>'Mei',12=>'Juni'];
            $info['month'] = $monthNames[$monthId] ?? 'Tidak ditemukan';
        }

        if ($studentId) {
            $student = DB::table('students')->where('student_id', $studentId)->first();
            $info['student'] = $student ? $student->student_full_name : 'Tidak ditemukan';
        }

        if ($posId) {
            $pos = DB::table('pos_pembayaran')->where('pos_id', $posId)->first();
            $info['pos'] = $pos ? $pos->pos_name : 'Tidak ditemukan';
        }

        if ($classId) {
            $class = DB::table('class_models')->where('class_id', $classId)->first();
            $info['class'] = $class ? $class->class_name : 'Tidak ditemukan';
        }

        if ($studentStatus !== null) {
            $info['status'] = $studentStatus == 1 ? 'Aktif' : 'Tidak Aktif';
        }

        return $info;
    }

    /**
     * Helper method untuk mendapatkan nama bulan berdasarkan month_month_id
     * month_month_id: 1=Juli, 2=Agustus, 3=September, 4=Oktober, 5=November, 6=Desember,
     *                 7=Januari, 8=Februari, 9=Maret, 10=April, 11=Mei, 12=Juni
     */
    private function getNamaBulan($bulan)
    {
        $namaBulan = [
            1 => 'Juli',
            2 => 'Agustus',
            3 => 'September',
            4 => 'Oktober',
            5 => 'November',
            6 => 'Desember',
            7 => 'Januari',
            8 => 'Februari',
            9 => 'Maret',
            10 => 'April',
            11 => 'Mei',
            12 => 'Juni'
        ];
        
        return $namaBulan[$bulan] ?? 'Tidak Diketahui';
    }
}
