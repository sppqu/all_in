<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pos;
use App\Models\Payment;
use App\Models\Period;
use App\Models\UserAddon;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RealisasiPosController extends Controller
{
    public function index(Request $request)
    {
        // Check if user has Analisis Target add-on
        $hasAnalisisTarget = UserAddon::where('user_id', auth()->id())
            ->whereHas('addon', function($query) {
                $query->where('slug', 'analisis-target');
            })
            ->where('status', 'active')
            ->exists();
        
        if (!$hasAnalisisTarget) {
            return redirect()->route('manage.addons.show', 'analisis-target')
                ->with('error', 'Anda tidak memiliki akses ke Analisis Target. Silakan beli add-on Analisis Target terlebih dahulu.');
        }
        
        try {
            // Log untuk debugging
            \Log::info('RealisasiPosController@index called');
            \Log::info('Request parameters:', $request->all());
            
            // Ambil tahun pelajaran yang aktif
            $activePeriod = Period::where('period_status', 1)->first();
            \Log::info('Active period:', ['activePeriod' => $activePeriod ? $activePeriod->toArray() : null]);
            
            // Filter berdasarkan request atau gunakan default
            $selectedPeriod = $request->get('period_id', $activePeriod->period_id ?? null);
            // Default range tanggal: awal bulan sampai hari ini
            $startDate = $request->get('start_date', date('Y-m-01'));
            $endDate = $request->get('end_date', date('Y-m-d'));
            // Filter kelas
            $selectedClass = $request->get('class_id', null);
            
            // Log filter yang digunakan
            \Log::info('Filter applied:', [
                'selectedPeriod' => $selectedPeriod,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'selectedClass' => $selectedClass,
                'request_period' => $request->get('period_id'),
                'request_start_date' => $request->get('start_date'),
                'request_end_date' => $request->get('end_date')
            ]);
            
            // Ambil semua tahun pelajaran untuk dropdown
            $periods = Period::orderBy('period_start', 'desc')->get();
            \Log::info('Periods loaded:', ['count' => $periods->count(), 'periods' => $periods->toArray()]);
            
            // Ambil data kelas untuk dropdown
            $classes = DB::table('class_models')
                ->orderBy('class_name')
                ->get();
            
            // Ambil data realisasi POS dengan filter kelas
            $realisasiData = $this->getRealisasiData($selectedPeriod, $startDate, $endDate, $selectedClass);
            
            \Log::info('View data prepared:', [
                'selectedPeriod' => $selectedPeriod,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'selectedClass' => $selectedClass,
                'periodsCount' => $periods->count(),
                'classesCount' => $classes->count(),
                'realisasiDataCount' => count($realisasiData)
            ]);
            
            return view('laporan.realisasi-pos', compact(
                'realisasiData', 
                'periods', 
                'classes',
                'selectedPeriod', 
                'startDate', 
                'endDate',
                'selectedClass'
            ));
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Error in RealisasiPosController@index: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return view dengan error message
            return view('laporan.realisasi-pos', [
                'realisasiData' => [],
                'periods' => Period::orderBy('period_start', 'desc')->get(),
                'classes' => DB::table('class_models')->orderBy('class_name')->get(),
                'selectedPeriod' => null,
                'startDate' => date('Y-m-01'),
                'endDate' => date('Y-m-d'),
                'selectedClass' => null,
                'error' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage()
            ]);
        }
    }
    
    private function getRealisasiData($periodId, $startDate, $endDate, $selectedClass = null)
    {
        try {
            // Log untuk debugging
            \Log::info('getRealisasiData called with:', ['periodId' => $periodId, 'startDate' => $startDate, 'endDate' => $endDate]);
            
            // Ambil semua POS (tidak ada filter is_active, ambil semua)
            $posList = Pos::all();
            \Log::info('POS list loaded:', ['count' => $posList->count()]);
            
            $realisasiData = [];
            $totalTagihan = 0;
            $totalTerbayar = 0;
            $totalBelumTerbayar = 0;
            
            foreach ($posList as $pos) {
                \Log::info('Processing POS:', ['pos_id' => $pos->pos_id, 'pos_name' => $pos->pos_name]);
                
                // Hitung target, tagihan, dan terbayar
                $target = $this->calculateTarget($pos->pos_id, $periodId, $startDate, $endDate, $selectedClass);
                $tagihan = $this->calculateTagihan($pos->pos_id, $periodId, $startDate, $endDate, $selectedClass);
                $terbayar = $this->calculateTerbayar($pos->pos_id, $periodId, $startDate, $endDate, $selectedClass);
                $belumTerbayar = $target - $terbayar; // Belum terbayar = target - terbayar
                $pencapaian = $target > 0 ? ($terbayar / $target) * 100 : 0; // Pencapaian = terbayar / target
                
                \Log::info('POS calculation result:', [
                    'pos_id' => $pos->pos_id,
                    'target' => $target,
                    'tagihan' => $tagihan,
                    'terbayar' => $terbayar,
                    'belum_terbayar' => $belumTerbayar,
                    'pencapaian' => $pencapaian
                ]);
                
                $realisasiData[] = [
                    'pos_name' => $pos->pos_name,
                    'target' => $target,
                    'tagihan' => $tagihan,
                    'terbayar' => $terbayar,
                    'belum_terbayar' => $belumTerbayar,
                    'pencapaian' => round($pencapaian, 1)
                ];
                
                $totalTagihan += $target; // Total target
                $totalTerbayar += $terbayar;
                $totalBelumTerbayar += $belumTerbayar;
            }
            
            // Tambahkan total
            $totalPencapaian = $totalTagihan > 0 ? ($totalTerbayar / $totalTagihan) * 100 : 0;
            
            $realisasiData[] = [
                'pos_name' => 'TOTAL',
                'target' => $totalTagihan, // Total target
                'tagihan' => $totalTagihan, // Untuk kompatibilitas dengan view yang ada
                'terbayar' => $totalTerbayar,
                'belum_terbayar' => $totalBelumTerbayar,
                'pencapaian' => round($totalPencapaian, 1),
                'is_total' => true
            ];
            
            \Log::info('getRealisasiData completed successfully', ['total_records' => count($realisasiData)]);
            return $realisasiData;
            
        } catch (\Exception $e) {
            \Log::error('Error in getRealisasiData:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    private function calculateTarget($posId, $periodId, $startDate, $endDate, $selectedClass = null)
    {
        try {
            // Debug: Log parameter yang diterima
            \Log::info('calculateTarget called with:', [
                'posId' => $posId,
                'periodId' => $periodId,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'selectedClass' => $selectedClass
            ]);
            
            // Ambil informasi POS untuk mendapatkan tarif
            $posInfo = DB::table('pos_pembayaran')->where('pos_id', $posId)->first();
            
                         // Hitung target berdasarkan jenis pembayaran bulanan
             // Target Bulanan = TOTAL KESELURUHAN tagihan bulanan dalam periode tertentu
             $targetBulananQuery = DB::table('payment')
                 ->join('bulan', 'payment.payment_id', '=', 'bulan.payment_payment_id')
                 ->join('students', 'bulan.student_student_id', '=', 'students.student_id')
                 ->where('payment.pos_pos_id', $posId)
                 ->where('payment.period_period_id', $periodId)
                 // Filter by class if selected
                 ->when($selectedClass, function($query) use ($selectedClass) {
                     $query->where('students.class_class_id', $selectedClass);
                 })
                 // TIDAK ADA FILTER TANGGAL - ambil semua tagihan dalam periode
                 ->selectRaw('
                     COUNT(DISTINCT students.student_id) as jumlah_siswa,
                     SUM(bulan.bulan_bill) as total_tagihan
                 ')
                 ->first();
                 
             // Target Bulanan = SUM(bulan.bulan_bill) - Total tagihan
             $jumlahSiswaBulanan = $targetBulananQuery ? $targetBulananQuery->jumlah_siswa : 0;
             $targetBulananValue = $targetBulananQuery ? $targetBulananQuery->total_tagihan : 0;
                
                         // Debug: Log query bulanan
             \Log::info('Target Bulanan Query for POS ' . $posId . ' (Total Keseluruhan):', [
                 'pos_id' => $posId,
                 'period_id' => $periodId,
                 'start_date' => $startDate,
                 'end_date' => $endDate,
                 'jumlah_siswa' => $jumlahSiswaBulanan,
                 'total_tagihan' => $targetBulananValue,
                 'note' => 'Menghitung TOTAL KESELURUHAN tagihan bulanan dalam periode ' . $periodId . ' (tanpa filter tanggal)'
             ]);
                
                         // Hitung target berdasarkan jenis pembayaran bebas
             // Target Bebas = TOTAL KESELURUHAN tagihan bebas dalam periode tertentu
             $targetBebasQuery = DB::table('payment')
                 ->join('bebas', 'payment.payment_id', '=', 'bebas.payment_payment_id')
                 ->join('students', 'bebas.student_student_id', '=', 'students.student_id')
                 ->where('payment.pos_pos_id', $posId)
                 ->where('payment.period_period_id', $periodId)
                 // Filter by class if selected
                 ->when($selectedClass, function($query) use ($selectedClass) {
                     $query->where('students.class_class_id', $selectedClass);
                 })
                 // TIDAK ADA FILTER TANGGAL - ambil semua tagihan dalam periode
                 ->selectRaw('
                     COUNT(DISTINCT students.student_id) as jumlah_siswa,
                     SUM(bebas.bebas_bill) as total_tagihan
                 ')
                 ->first();
                 
             // Target Bebas = SUM(bebas.bebas_bill) - Total tagihan
             $jumlahSiswaBebas = $targetBebasQuery ? $targetBebasQuery->jumlah_siswa : 0;
             $targetBebasValue = $targetBebasQuery ? $targetBebasQuery->total_tagihan : 0;
            
                         // Debug: Log hasil query
             \Log::info('Target calculation for POS ' . $posId . ':', [
                 'target_bulanan' => $targetBulananValue,
                 'target_bebas' => $targetBebasValue,
                 'total_target' => $targetBulananValue + $targetBebasValue,
                 'start_date' => $startDate,
                 'end_date' => $endDate,
                 'period_filter' => $periodId,
                 'note' => 'Target = TOTAL KESELURUHAN tagihan dalam periode tertentu (semua tagihan bulanan + bebas)',
                 'detail_bulanan' => [
                     'siswa' => $jumlahSiswaBulanan,
                     'total_tagihan' => $targetBulananValue
                 ],
                 'detail_bebas' => [
                     'siswa' => $jumlahSiswaBebas,
                     'total_tagihan' => $targetBebasValue
                 ]
             ]);
            
            // Jika target 0, coba cek data yang tersedia
            if (($targetBulananValue + $targetBebasValue) == 0) {
                // Debug: Cek data payment yang tersedia
                $availablePayments = DB::table('payment')
                    ->where('pos_pos_id', $posId)
                    ->where('period_period_id', $periodId)
                    ->get();
                    
                \Log::info('Available payments for debugging:', [
                    'pos_id' => $posId,
                    'period_id' => $periodId,
                    'payments_count' => $availablePayments->count(),
                    'payments' => $availablePayments->toArray()
                ]);
                
                                 // Debug: Cek data bulan yang tersedia
                 $availableBulan = DB::table('bulan')
                     ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                     ->where('payment.pos_pos_id', $posId)
                     ->where('payment.period_period_id', $periodId)
                     ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
                     ->get();
                     
                 \Log::info('Available bulan for debugging:', [
                     'pos_id' => $posId,
                     'period_id' => $periodId,
                     'start_date' => $startDate,
                     'end_date' => $endDate,
                     'bulan_count' => $availableBulan->count(),
                     'bulan_data' => $availableBulan->toArray()
                 ]);
            }
            
            return $targetBulananValue + $targetBebasValue;
            
                 } catch (\Exception $e) {
             \Log::error('Error in calculateTarget:', [
                 'message' => $e->getMessage(),
                 'posId' => $posId,
                 'periodId' => $periodId,
                 'startDate' => $startDate,
                 'endDate' => $endDate
             ]);
             return 0;
         }
    }
    
         private function calculateTagihan($posId, $periodId, $startDate, $endDate, $selectedClass = null)
     {
         // Hitung tagihan berdasarkan jenis pembayaran bulanan
         $tagihanBulanan = DB::table('payment')
             ->join('bulan', 'payment.payment_id', '=', 'bulan.payment_payment_id')
             ->join('students', 'bulan.student_student_id', '=', 'students.student_id')
             ->where('payment.pos_pos_id', $posId)
             ->where('payment.period_period_id', $periodId)
             // Filter by class if selected
             ->when($selectedClass, function($query) use ($selectedClass) {
                 $query->where('students.class_class_id', $selectedClass);
             })
             // TIDAK ADA FILTER TANGGAL - ambil semua tagihan dalam periode
             ->sum('bulan.bulan_bill');
             
         // Hitung tagihan berdasarkan jenis pembayaran bebas
         $tagihanBebas = DB::table('payment')
             ->join('bebas', 'payment.payment_id', '=', 'bebas.payment_payment_id')
             ->join('students', 'bebas.student_student_id', '=', 'students.student_id')
             ->where('payment.pos_pos_id', $posId)
             ->where('payment.period_period_id', $periodId)
             // Filter by class if selected
             ->when($selectedClass, function($query) use ($selectedClass) {
                 $query->where('students.class_class_id', $selectedClass);
             })
             // TIDAK ADA FILTER TANGGAL - ambil semua tagihan dalam periode
             ->sum('bebas.bebas_bill');
             
         return $tagihanBulanan + $tagihanBebas;
     }
    
        private function calculateTerbayar($posId, $periodId, $startDate, $endDate, $selectedClass = null)
    {
        try {
            // Delegasikan ke Rekapitulasi agar hasil TERBAYAR identik
            \Log::info('calculateTerbayar delegated to Rekapitulasi:', [
                'posId' => $posId,
                'periodId' => $periodId,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'selectedClass' => $selectedClass
            ]);
            
            $rekapData = app(\App\Http\Controllers\PaymentController::class)
                ->getRekapitulasiData(null, $startDate, $endDate, null, $posId, $selectedClass);

            $totalTerbayar = (float) ($rekapData->sum('cash_amount')
                + $rekapData->sum('transfer_amount')
                + $rekapData->sum('gateway_amount'));

            return $totalTerbayar;
        } catch (\Exception $e) {
            \Log::error('Error in calculateTerbayar (delegated):', [
                'message' => $e->getMessage(),
                'posId' => $posId,
                'periodId' => $periodId,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
            return 0;
        }
    }
    
    /**
     * Hitung terbayar menggunakan tabel log_trx (lebih akurat)
     */
    private function calculateTerbayarFromLogTrx($posId, $periodId, $startDate, $endDate)
    {
        try {
            // ✅ SOURCE 1: Pembayaran Bulanan dari log_trx
            $terbayarBulananFromLog = DB::table('log_trx as lt')
                ->join('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->where('p.pos_pos_id', $posId)
                ->where('p.period_period_id', $periodId)
                ->whereNotNull('lt.bulan_bulan_id')
                ->whereRaw('DATE(lt.log_trx_input_date) >= ? AND DATE(lt.log_trx_input_date) <= ?', [$startDate, $endDate])
                ->sum('b.bulan_bill');

            // ✅ SOURCE 2: Pembayaran Bebas dari log_trx
            $terbayarBebasFromLog = DB::table('log_trx as lt')
                ->join('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->where('p.pos_pos_id', $posId)
                ->where('p.period_period_id', $periodId)
                ->whereNotNull('lt.bebas_pay_bebas_pay_id')
                ->whereRaw('DATE(lt.log_trx_input_date) >= ? AND DATE(lt.log_trx_input_date) <= ?', [$startDate, $endDate])
                ->sum('bp.bebas_pay_bill');

            $totalFromLogTrx = $terbayarBulananFromLog + $terbayarBebasFromLog;

            // Debug: Log hasil perhitungan dari log_trx
            \Log::info('Terbayar calculation from log_trx for POS ' . $posId . ':', [
                'pos_id' => $posId,
                'period_id' => $periodId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'terbayar_bulanan_from_log' => $terbayarBulananFromLog,
                'terbayar_bebas_from_log' => $terbayarBebasFromLog,
                'total_from_log_trx' => $totalFromLogTrx,
                'note' => 'Menggunakan tabel log_trx untuk perhitungan yang lebih akurat'
            ]);

            return $totalFromLogTrx;

        } catch (\Exception $e) {
            \Log::error('Error in calculateTerbayarFromLogTrx:', [
                'message' => $e->getMessage(),
                'posId' => $posId,
                'periodId' => $periodId,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
            return 0;
        }
    }
    
    public function exportExcel(Request $request)
    {
        // Check if user has Analisis Target add-on
        $hasAnalisisTarget = UserAddon::where('user_id', auth()->id())
            ->whereHas('addon', function($query) {
                $query->where('slug', 'analisis-target');
            })
            ->where('status', 'active')
            ->exists();
        
        if (!$hasAnalisisTarget) {
            return redirect()->route('manage.addons.show', 'analisis-target')
                ->with('error', 'Anda tidak memiliki akses ke Analisis Target. Silakan beli add-on Analisis Target terlebih dahulu.');
        }
        
        $periodId = $request->get('period_id');
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $selectedClass = $request->get('class_id', null);
        
        // Ambil data untuk export
        $realisasiData = $this->getRealisasiData($periodId, $startDate, $endDate, $selectedClass);
        $period = Period::find($periodId);
        
        // Buat nama file
        $filename = "Realisasi_POS_{$period->period_start}_{$period->period_end}_{$startDate}_to_{$endDate}_" . date('Y-m-d_H-i-s') . ".xlsx";
        
        // Return view Excel dengan headers untuk download
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
        
        return response()
            ->view('laporan.realisasi-pos-excel', compact('realisasiData', 'period', 'startDate', 'endDate'))
            ->withHeaders($headers);
    }
    
    public function exportPdf(Request $request)
    {
        // Check if user has Analisis Target add-on
        $hasAnalisisTarget = UserAddon::where('user_id', auth()->id())
            ->whereHas('addon', function($query) {
                $query->where('slug', 'analisis-target');
            })
            ->where('status', 'active')
            ->exists();
        
        if (!$hasAnalisisTarget) {
            return redirect()->route('manage.addons.show', 'analisis-target')
                ->with('error', 'Anda tidak memiliki akses ke Analisis Target. Silakan beli add-on Analisis Target terlebih dahulu.');
        }
        
        $periodId = $request->get('period_id');
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $selectedClass = $request->get('class_id', null);
        
        // Ambil data untuk export
        $realisasiData = $this->getRealisasiData($periodId, $startDate, $endDate, $selectedClass);
        $period = Period::find($periodId);
        
        // Buat nama file
        $filename = "Realisasi_POS_{$period->period_start}_{$period->period_end}_{$startDate}_to_{$endDate}_" . date('Y-m-d_H-i-s') . ".pdf";
        
        // Jika DomPDF tersedia, gunakan PDF
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.realisasi-pos-pdf', compact('realisasiData', 'period', 'startDate', 'endDate'));
            return $pdf->download($filename);
        } else {
            // Fallback: Return view HTML
            return response()
                ->view('laporan.realisasi-pos-pdf', compact('realisasiData', 'period', 'startDate', 'endDate'))
                ->withHeaders([
                    'Content-Type' => 'text/html',
                ]);
        }
    }
    
    public function test()
    {
        try {
            // Test basic database connection
            $dbName = DB::connection()->getDatabaseName();
            
            // Test if models can be loaded
            $posCount = Pos::count();
            $periodCount = Period::count();
            
            // Test basic queries
            $posList = Pos::select('pos_id', 'pos_name')->limit(3)->get();
            $periodList = Period::select('period_id', 'period_start', 'period_end', 'period_status')->limit(3)->get();
            
            // Test active period
            $activePeriod = Period::where('period_status', 1)->first();
            
            // Test data availability for specific period and month
            $testPeriodId = $activePeriod ? $activePeriod->period_id : 1;
            $testMonth = '09'; // September
            
            // Test payment data
            $paymentData = DB::table('payment')
                ->where('period_period_id', $testPeriodId)
                ->get();
                
            // Test bulan data
            $bulanData = DB::table('bulan')
                ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                ->where('payment.period_period_id', $testPeriodId)
                ->where('bulan.month_month_id', $testMonth)
                ->get();
                
            // Test bebas data
            $bebasData = DB::table('bebas')
                ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
                ->where('payment.period_period_id', $testPeriodId)
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'RealisasiPosController berfungsi dengan baik!',
                'timestamp' => now(),
                'database' => $dbName,
                'pos_count' => $posCount,
                'period_count' => $periodCount,
                'pos_sample' => $posList->toArray(),
                'period_sample' => $periodList->toArray(),
                'active_period' => $activePeriod ? [
                    'period_id' => $activePeriod->period_id,
                    'period_start' => $activePeriod->period_start,
                    'period_end' => $activePeriod->period_end,
                    'period_status' => $activePeriod->period_status
                ] : null,
                'test_data' => [
                    'test_period_id' => $testPeriodId,
                    'test_month' => $testMonth,
                    'payment_count' => $paymentData->count(),
                    'bulan_count' => $bulanData->count(),
                    'bebas_count' => $bebasData->count(),
                    'payment_sample' => $paymentData->take(3)->toArray(),
                    'bulan_sample' => $bulanData->take(3)->toArray(),
                    'bebas_sample' => $bebasData->take(3)->toArray()
                ],
                'database_structure' => [
                    'payment_columns' => DB::getSchemaBuilder()->getColumnListing('payment'),
                    'bulan_columns' => DB::getSchemaBuilder()->getColumnListing('bulan'),
                    'bebas_columns' => DB::getSchemaBuilder()->getColumnListing('bebas'),
                    'students_columns' => DB::getSchemaBuilder()->getColumnListing('students')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'timestamp' => now(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
         // Method test untuk debugging target calculation
     public function testTargetCalculation($posId = null, $periodId = null, $startDate = null, $endDate = null)
    {
        try {
            // Gunakan parameter default jika tidak ada
            if (!$posId) {
                $pos = Pos::first();
                $posId = $pos ? $pos->pos_id : 1;
            }
            
            if (!$periodId) {
                $period = Period::where('period_status', 1)->first();
                $periodId = $period ? $period->period_id : 1;
            }
            
                         if (!$startDate) {
                 $startDate = date('Y-m-01'); // Awal bulan
             }
             
             if (!$endDate) {
                 $endDate = date('Y-m-d'); // Hari ini
             }
            
            // Test calculateTarget
            $target = $this->calculateTarget($posId, $periodId, $startDate, $endDate);
            
            // Test calculateTagihan
            $tagihan = $this->calculateTagihan($posId, $periodId, $startDate, $endDate);
            
            // Test calculateTerbayar
            $terbayar = $this->calculateTerbayar($posId, $periodId, $startDate, $endDate);
            
            // Test data availability untuk range tanggal
            $bulanData = DB::table('bulan')
                ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                ->where('payment.pos_pos_id', $posId)
                ->where('payment.period_period_id', $periodId)
                ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
                ->get();
                
            // Test data transfer untuk range tanggal
            $transferData = DB::table('transfer')
                ->join('transfer_detail', 'transfer.transfer_id', '=', 'transfer_detail.transfer_id')
                ->join('bulan', 'transfer_detail.bulan_id', '=', 'bulan.bulan_id')
                ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                ->where('payment.pos_pos_id', $posId)
                ->where('payment.period_period_id', $periodId)
                ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
                ->where('transfer.status', 1)
                ->get();
            
            return response()->json([
                'success' => true,
                'test_parameters' => [
                    'pos_id' => $posId,
                    'period_id' => $periodId,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'results' => [
                    'target' => $target,
                    'tagihan' => $tagihan,
                    'terbayar' => $terbayar,
                    'belum_terbayar' => $target - $terbayar,
                    'pencapaian' => $target > 0 ? ($terbayar / $target) * 100 : 0
                ],
                'data_availability' => [
                    'bulan_count' => $bulanData->count(),
                    'bulan_sample' => $bulanData->take(3)->toArray(),
                    'transfer_count' => $transferData->count(),
                    'transfer_sample' => $transferData->take(3)->toArray()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    // Method test untuk melihat data bulan yang tersedia
    public function testMonthData($posId = null, $periodId = null)
    {
        try {
            // Gunakan parameter default jika tidak ada
            if (!$posId) {
                $pos = Pos::first();
                $posId = $pos ? $pos->pos_id : 1;
            }
            
            if (!$periodId) {
                $period = Period::where('period_status', 1)->first();
                $periodId = $period ? $period->period_id : 1;
            }
            
            $monthData = [];
            
            // Test data untuk setiap bulan
            for ($month = 1; $month <= 12; $month++) {
                $monthStr = sprintf('%02d', $month);
                
                // Data bulan
                $bulanCount = DB::table('bulan')
                    ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                    ->where('payment.pos_pos_id', $posId)
                    ->where('payment.period_period_id', $periodId)
                    ->where('bulan.month_month_id', $monthStr)
                    ->count();
                    
                // Data transfer
                $transferCount = DB::table('transfer')
                    ->join('transfer_detail', 'transfer.transfer_id', '=', 'transfer_detail.transfer_id')
                    ->join('bulan', 'transfer_detail.bulan_id', '=', 'bulan.bulan_id')
                    ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                    ->where('payment.pos_pos_id', $posId)
                    ->where('payment.period_period_id', $periodId)
                    ->where('bulan.month_month_id', $monthStr)
                    ->where('transfer.status', 1)
                    ->count();
                    
                $monthData[] = [
                    'month' => $monthStr,
                    'month_name' => $this->getMonthName($monthStr),
                    'bulan_count' => $bulanCount,
                    'transfer_count' => $transferCount
                ];
            }
            
            return response()->json([
                'success' => true,
                'test_parameters' => [
                    'pos_id' => $posId,
                    'period_id' => $periodId
                ],
                'month_data' => $monthData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    // Method test khusus untuk debugging data terbayar
    public function testTerbayarData($posId = null, $periodId = null, $month = null)
    {
        try {
            // Gunakan parameter default jika tidak ada
            if (!$posId) {
                $pos = Pos::first();
                $posId = $pos ? $pos->pos_id : 1;
            }
            
            if (!$periodId) {
                $period = Period::where('period_status', 1)->first();
                $periodId = $period ? $period->period_id : 1;
            }
            
            if (!$month) {
                $month = '3'; // September (dalam sistem akademik: 1=Juli, 2=Agustus, 3=September)
            }
            
            // Test 1: Cek struktur tabel
            $transferColumns = DB::getSchemaBuilder()->getColumnListing('transfer');
            $transferDetailColumns = DB::getSchemaBuilder()->getColumnListing('transfer_detail');
            $bulanColumns = DB::getSchemaBuilder()->getColumnListing('bulan');
            $bebasColumns = DB::getSchemaBuilder()->getColumnListing('bebas');
            
            // Test 2: Cek data transfer yang tersedia
            $allTransfers = DB::table('transfer')->get();
            $paidTransfers = DB::table('transfer')->where('status', 1)->get();
            $unpaidTransfers = DB::table('transfer')->where('status', 0)->get();
            
            // Test 3: Cek data transfer_detail
            $allTransferDetails = DB::table('transfer_detail')->get();
            $transferDetailsWithTransfer = DB::table('transfer_detail')
                ->join('transfer', 'transfer_detail.transfer_id', '=', 'transfer.transfer_id')
                ->where('transfer.status', 1)
                ->get();
            
            // Test 4: Cek data bulan, bebas, dan bebas_pay
            $allBulan = DB::table('bulan')->get();
            $allBebas = DB::table('bebas')->get();
            $allBebasPay = DB::table('bebas_pay')->get();
            
            // Test 4b: Cek data dengan status bayar
            $paidBulan = DB::table('bulan')->whereNotNull('bulan_date_pay')->get();
            $paidBebas = DB::table('bebas')->where('bebas_total_pay', '>', 0)->get();
            $paidBebasPay = DB::table('bebas_pay')->where('bebas_pay_bill', '>', 0)->get();
            
            // Test 5: Cek data payment
            $allPayments = DB::table('payment')
                ->where('pos_pos_id', $posId)
                ->where('period_period_id', $periodId)
                ->get();
            
            // Test 6: Cek join antara transfer dan bulan/bebas
            $transferBulanJoin = DB::table('transfer')
                ->join('transfer_detail', 'transfer.transfer_id', '=', 'transfer_detail.transfer_id')
                ->join('bulan', 'transfer_detail.bulan_id', '=', 'bulan.bulan_id')
                ->where('transfer.status', 1)
                ->limit(5)
                ->get();
                
            $transferBebasJoin = DB::table('transfer')
                ->join('transfer_detail', 'transfer.transfer_id', '=', 'transfer_detail.transfer_id')
                ->join('bebas', 'transfer_detail.bebas_id', '=', 'bebas.bebas_id')
                ->where('transfer.status', 1)
                ->limit(5)
                ->get();
            
            // Test 7: Cek data dengan filter yang sama seperti di controller
            $filteredTransferBulan = DB::table('transfer')
                ->join('transfer_detail', 'transfer.transfer_id', '=', 'transfer_detail.transfer_id')
                ->join('bulan', 'transfer_detail.bulan_id', '=', 'bulan.bulan_id')
                ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                ->where('payment.pos_pos_id', $posId)
                ->where('payment.period_period_id', $periodId)
                ->where('bulan.month_month_id', $month)
                ->where('transfer.status', 1)
                ->get();
                
            $filteredTransferBebas = DB::table('transfer')
                ->join('transfer_detail', 'transfer.transfer_id', '=', 'transfer_detail.transfer_id')
                ->join('bebas', 'transfer_detail.bebas_id', '=', 'bebas.bebas_id')
                ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
                ->where('payment.pos_pos_id', $posId)
                ->where('payment.period_period_id', $periodId)
                ->where('transfer.status', 1)
                ->get();
            
            return response()->json([
                'success' => true,
                'test_parameters' => [
                    'pos_id' => $posId,
                    'period_id' => $periodId,
                    'month' => $month
                ],
                'database_structure' => [
                    'transfer_columns' => $transferColumns,
                    'transfer_detail_columns' => $transferDetailColumns,
                    'bulan_columns' => $bulanColumns,
                    'bebas_columns' => $bebasColumns
                ],
                'data_counts' => [
                    'all_transfers' => $allTransfers->count(),
                    'paid_transfers' => $paidTransfers->count(),
                    'unpaid_transfers' => $unpaidTransfers->count(),
                    'all_transfer_details' => $allTransferDetails->count(),
                    'transfer_details_with_paid_transfer' => $transferDetailsWithTransfer->count(),
                    'all_bulan' => $allBulan->count(),
                    'all_bebas' => $allBebas->count(),
                    'all_bebas_pay' => $allBebasPay->count(),
                    'paid_bulan' => $paidBulan->count(),
                    'paid_bebas' => $paidBebas->count(),
                    'paid_bebas_pay' => $paidBebasPay->count(),
                    'filtered_payments' => $allPayments->count()
                ],
                'sample_data' => [
                    'paid_transfers_sample' => $paidTransfers->take(3)->toArray(),
                    'transfer_details_sample' => $allTransferDetails->take(3)->toArray(),
                    'transfer_bulan_join_sample' => $transferBulanJoin->toArray(),
                    'transfer_bebas_join_sample' => $transferBebasJoin->toArray(),
                    'filtered_transfer_bulan' => $filteredTransferBulan->toArray(),
                    'filtered_transfer_bebas' => $filteredTransferBebas->toArray(),
                    'paid_bulan_sample' => $paidBulan->take(3)->toArray(),
                    'paid_bebas_sample' => $paidBebas->take(3)->toArray(),
                    'paid_bebas_pay_sample' => $paidBebasPay->take(3)->toArray()
                ],
                'debug_info' => [
                    'message' => 'Data terbayar debugging completed. Check sample data to see what\'s available.'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    public function testBulanData()
    {
        try {
            // 1. Cek struktur tabel bulan
            $bulanColumns = DB::getSchemaBuilder()->getColumnListing('bulan');
            
            // 2. Cek semua data bulan
            $allBulan = DB::table('bulan')->get();
            
            // 3. Cek bulan dengan date_pay (yang sudah bayar)
            $bulanWithDatePay = DB::table('bulan')->whereNotNull('bulan_date_pay')->get();
            
            // 4. Cek bulan tanpa date_pay (yang belum bayar)
            $bulanWithoutDatePay = DB::table('bulan')->whereNull('bulan_date_pay')->get();
            
            // 5. Cek bulan berdasarkan status
            $bulanStatus0 = DB::table('bulan')->where('bulan_status', 0)->get();
            $bulanStatus1 = DB::table('bulan')->where('bulan_status', 1)->get();
            
            // 6. Cek bulan dengan join payment dan pos
            $bulanWithPayment = DB::table('bulan')
                ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                ->join('pos_pembayaran', 'payment.pos_pos_id', '=', 'pos_pembayaran.pos_id')
                ->select(
                    'bulan.*',
                    'payment.pos_pos_id',
                    'payment.period_period_id',
                    'pos_pembayaran.pos_name'
                )
                ->limit(10)
                ->get();
            
            // 7. Sample data untuk analisis
            $sampleAll = $allBulan->take(5);
            $samplePaid = $bulanWithDatePay->take(5);
            $sampleUnpaid = $bulanWithoutDatePay->take(5);
            
            return response()->json([
                'success' => true,
                'bulan_structure' => $bulanColumns,
                'data_counts' => [
                    'total_bulan' => $allBulan->count(),
                    'bulan_with_date_pay' => $bulanWithDatePay->count(),
                    'bulan_without_date_pay' => $bulanWithoutDatePay->count(),
                    'bulan_status_0' => $bulanStatus0->count(),
                    'bulan_status_1' => $bulanStatus1->count()
                ],
                'sample_data' => [
                    'all_bulan_sample' => $sampleAll->toArray(),
                    'paid_bulan_sample' => $samplePaid->toArray(),
                    'unpaid_bulan_sample' => $sampleUnpaid->toArray(),
                    'bulan_with_payment' => $bulanWithPayment->toArray()
                ],
                'analysis' => [
                    'note' => 'bulan_status=1 adalah default, bukan indikasi sudah bayar',
                    'paid_indicator' => 'bulan_date_pay IS NOT NULL menunjukkan sudah bayar',
                    'recommendation' => 'Gunakan whereNotNull(bulan_date_pay) untuk filter pembayaran'
                ],
                'debug_info' => [
                    'payment_id_3_data' => DB::table('bulan')
                        ->where('payment_payment_id', 3)
                        ->whereNotNull('bulan_date_pay')
                        ->get()
                        ->toArray(),
                    'month_1_data' => DB::table('bulan')
                        ->where('month_month_id', '1')
                        ->whereNotNull('bulan_date_pay')
                        ->get()
                        ->toArray()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function testRealisasiCalculation()
    {
        try {
            // Test dengan parameter yang sama seperti di halaman utama
            $posId = 1;
            $periodId = 1;
            $month = '3'; // September (3=September dalam sistem akademik)
            
            // Test calculateTarget
            $target = $this->calculateTarget($posId, $periodId, $month);
            
            // Test calculateTagihan
            $tagihan = $this->calculateTagihan($posId, $periodId, $month);
            
            // Test calculateTerbayar
            $terbayar = $this->calculateTerbayar($posId, $periodId, $month);
            
            // Test data bulan yang tersedia untuk bulan 09
            $bulanData = DB::table('bulan')
                ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                ->where('payment.pos_pos_id', $posId)
                ->where('payment.period_period_id', $periodId)
                ->where('bulan.month_month_id', $month)
                ->get();
                
            // Test data bulan yang sudah bayar untuk bulan 09
            $bulanPaid = DB::table('bulan')
                ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                ->where('payment.pos_pos_id', $posId)
                ->where('payment.period_period_id', $periodId)
                ->where('bulan.month_month_id', $month)
                ->whereNotNull('bulan.bulan_date_pay')
                ->get();
                
            // Test data bulan yang sudah bayar untuk SEMUA bulan
            $bulanAllPaid = DB::table('bulan')
                ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                ->where('payment.pos_pos_id', $posId)
                ->where('payment.period_period_id', $periodId)
                ->whereNotNull('bulan.bulan_date_pay')
                ->get();
            
            return response()->json([
                'success' => true,
                'test_parameters' => [
                    'pos_id' => $posId,
                    'period_id' => $periodId,
                    'month' => $month
                ],
                'calculation_results' => [
                    'target' => $target,
                    'tagihan' => $tagihan,
                    'terbayar' => $terbayar,
                    'belum_terbayar' => $target - $terbayar,
                    'pencapaian' => $target > 0 ? ($terbayar / $target) * 100 : 0
                ],
                'data_analysis' => [
                    'bulan_total_for_month' => $bulanData->count(),
                    'bulan_paid_for_month' => $bulanPaid->count(),
                    'bulan_all_paid_total' => $bulanAllPaid->count(),
                    'bulan_data_sample' => $bulanData->take(3)->toArray(),
                    'bulan_paid_sample' => $bulanPaid->toArray(),
                    'bulan_all_paid_sample' => $bulanAllPaid->take(5)->toArray()
                ],
                'debug_info' => [
                    'note' => 'Test ini menggunakan parameter yang sama seperti di halaman utama',
                    'month_filter' => 'Filter bulan: ' . $month,
                    'expected_result' => 'Seharusnya ada data terbayar dari bulan yang sudah bayar'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    // Method test untuk membandingkan target dengan rekapitulasi
    public function testComparisonWithRekapitulasi($posId = null, $periodId = null, $startDate = null, $endDate = null)
    {
        try {
            // Gunakan parameter default jika tidak ada
            if (!$posId) {
                $pos = Pos::first();
                $posId = $pos ? $pos->pos_id : 1;
            }
            
            if (!$periodId) {
                $period = Period::where('period_status', 1)->first();
                $periodId = $period ? $period->period_id : 1;
            }
            
            if (!$startDate) {
                $startDate = date('Y-m-01'); // Awal bulan
            }
            
            if (!$endDate) {
                $endDate = date('Y-m-d'); // Hari ini
            }
            
            // 1. Hitung Target (dari RealisasiPosController)
            $target = $this->calculateTarget($posId, $periodId, $startDate, $endDate);
            $terbayar = $this->calculateTerbayar($posId, $periodId, $startDate, $endDate);
            
            // 2. Hitung Rekapitulasi (dari PaymentController)
            $rekapitulasiData = $this->getRekapitulasiDataForComparison($posId, $startDate, $endDate);
            
            // 3. Analisis perbedaan
            $analysis = $this->analyzeDifferences($posId, $periodId, $startDate, $endDate, $target, $terbayar, $rekapitulasiData);
            
            return response()->json([
                'success' => true,
                'test_parameters' => [
                    'pos_id' => $posId,
                    'period_id' => $periodId,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'target_calculation' => [
                    'target' => $target,
                    'terbayar' => $terbayar,
                    'belum_terbayar' => $target - $terbayar,
                    'pencapaian' => $target > 0 ? ($terbayar / $target) * 100 : 0
                ],
                'rekapitulasi_data' => $rekapitulasiData,
                'analysis' => $analysis
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    private function getRekapitulasiDataForComparison($posId, $startDate, $endDate)
    {
        $result = collect();
        
        // 1. Ambil data tunai dari bulan
        $tunaiBulan = DB::table('bulan as b')
            ->join('students as s', 'b.student_student_id', '=', 's.student_id')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->where('p.pos_pos_id', $posId)
            ->whereNotNull('b.bulan_date_pay')
            ->whereRaw('DATE(b.bulan_date_pay) >= ? AND DATE(b.bulan_date_pay) <= ?', [$startDate, $endDate])
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                      ->whereRaw('td.bulan_id = b.bulan_id')
                      ->where('td.bulan_id', '>', 0)
                      ->whereIn('t.status', [1, 2]);
            })
            ->sum('b.bulan_bill');
            
        // 2. Ambil data tunai dari bebas
        $tunaiBebas = DB::table('bebas_pay as bp')
            ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
            ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->where('p.pos_pos_id', $posId)
            ->whereNotNull('bp.bebas_pay_input_date')
            ->whereRaw('DATE(bp.bebas_pay_input_date) >= ? AND DATE(bp.bebas_pay_input_date) <= ?', [$startDate, $endDate])
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                      ->whereRaw('td.bebas_id = bp.bebas_bebas_id')
                      ->where('td.bebas_id', '>', 0)
                      ->whereIn('t.status', [1, 2]);
            })
            ->sum('bp.bebas_pay_bill');
            
        // 3. Ambil data transfer
        $transferAmount = DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->join('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->where('p.pos_pos_id', $posId)
            ->whereNotNull('t.confirm_pay')
            ->where('t.status', 1)
            ->where('td.payment_type', '!=', 3)
            ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate])
            ->sum('td.subtotal');
            
        // 4. Ambil data transfer bebas
        $transferBebasAmount = DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->join('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
            ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
            ->where('p.pos_pos_id', $posId)
            ->whereNotNull('t.confirm_pay')
            ->where('t.status', 1)
            ->where('td.payment_type', '!=', 3)
            ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate])
            ->sum('td.subtotal');
            
                // 5. Ambil data payment gateway (online payment) untuk bulanan
        $paymentGatewayAmount = DB::table('online_payments as op')
            ->join('bulan as b', 'op.bill_id', '=', 'b.bulan_id')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->where('p.pos_pos_id', $posId)
            ->where('op.bill_type', 'bulanan')
            ->where('op.status', 'success')
            ->whereRaw('DATE(op.created_at) >= ? AND DATE(op.created_at) <= ?', [$startDate, $endDate])
            ->sum('op.amount');
        
        // 6. Ambil data payment gateway untuk bebas
        $paymentGatewayBebasAmount = DB::table('online_payments as op')
            ->join('bebas as be', 'op.bill_id', '=', 'be.bebas_id')
            ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
            ->where('p.pos_pos_id', $posId)
            ->where('op.bill_type', 'bebas')
            ->where('op.status', 'success')
            ->whereRaw('DATE(op.created_at) >= ? AND DATE(op.created_at) <= ?', [$startDate, $endDate])
            ->sum('op.amount');
            
        return [
            'tunai_bulan' => $tunaiBulan,
            'tunai_bebas' => $tunaiBebas,
            'transfer_bulan' => $transferAmount,
            'transfer_bebas' => $transferBebasAmount,
            'payment_gateway_bulan' => $paymentGatewayAmount,
            'payment_gateway_bebas' => $paymentGatewayBebasAmount,
            'total_tunai' => $tunaiBulan + $tunaiBebas,
            'total_transfer' => $transferAmount + $transferBebasAmount,
            'total_payment_gateway' => $paymentGatewayAmount + $paymentGatewayBebasAmount,
            'total_rekapitulasi' => $tunaiBulan + $tunaiBebas + $transferAmount + $transferBebasAmount + $paymentGatewayAmount + $paymentGatewayBebasAmount
        ];
    }
    
    private function analyzeDifferences($posId, $periodId, $startDate, $endDate, $target, $terbayar, $rekapitulasiData)
    {
        $analysis = [];
        
        // 1. Perbandingan Target vs Rekapitulasi
        $analysis['target_vs_rekapitulasi'] = [
            'target' => $target,
            'rekapitulasi_total' => $rekapitulasiData['total_rekapitulasi'],
            'difference' => $target - $rekapitulasiData['total_rekapitulasi'],
            'percentage_diff' => $target > 0 ? (($target - $rekapitulasiData['total_rekapitulasi']) / $target) * 100 : 0
        ];
        
        // 2. Perbandingan Terbayar vs Rekapitulasi
        $analysis['terbayar_vs_rekapitulasi'] = [
            'terbayar' => $terbayar,
            'rekapitulasi_total' => $rekapitulasiData['total_rekapitulasi'],
            'difference' => $terbayar - $rekapitulasiData['total_rekapitulasi'],
            'percentage_diff' => $terbayar > 0 ? (($terbayar - $rekapitulasiData['total_rekapitulasi']) / $terbayar) * 100 : 0
        ];
        
        // 3. Detail breakdown
        $analysis['breakdown'] = [
            'target_breakdown' => [
                'bulanan' => $this->getTargetBulanan($posId, $periodId),
                'bebas' => $this->getTargetBebas($posId, $periodId)
            ],
            'terbayar_breakdown' => [
                'bulanan_cash' => $this->getTerbayarBulananCash($posId, $periodId, $startDate, $endDate),
                'bebas_pay' => $this->getTerbayarBebasPay($posId, $periodId, $startDate, $endDate)
            ],
            'rekapitulasi_breakdown' => $rekapitulasiData
        ];
        
        // 4. Analisis kemungkinan penyebab perbedaan
        $analysis['possible_causes'] = $this->identifyPossibleCauses($posId, $periodId, $startDate, $endDate, $target, $terbayar, $rekapitulasiData);
        
        return $analysis;
    }
    
    private function getTargetBulanan($posId, $periodId)
    {
        return DB::table('payment')
            ->join('bulan', 'payment.payment_id', '=', 'bulan.payment_payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->sum('bulan.bulan_bill');
    }
    
    private function getTargetBebas($posId, $periodId)
    {
        return DB::table('payment')
            ->join('bebas', 'payment.payment_id', '=', 'bebas.payment_payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->sum('bebas.bebas_bill');
    }
    
    private function getTerbayarBulananCash($posId, $periodId, $startDate, $endDate)
    {
        return DB::table('bulan')
            ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
            ->whereNotNull('bulan.bulan_date_pay')
            ->sum('bulan.bulan_bill');
    }
    
    private function getTerbayarBebasPay($posId, $periodId, $startDate, $endDate)
    {
        return DB::table('bebas_pay')
            ->join('bebas', 'bebas_pay.bebas_bebas_id', '=', 'bebas.bebas_id')
            ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bebas_pay.bebas_pay_input_date', [$startDate, $endDate])
            ->where('bebas_pay.bebas_pay_bill', '>', 0)
            ->sum('bebas_pay.bebas_pay_bill');
    }
    
    private function identifyPossibleCauses($posId, $periodId, $startDate, $endDate, $target, $terbayar, $rekapitulasiData)
    {
        $causes = [];
        
        // 1. Cek apakah ada data yang tidak masuk dalam rekapitulasi
        $missingInRekapitulasi = $target - $rekapitulasiData['total_rekapitulasi'];
        if ($missingInRekapitulasi > 0) {
            $causes[] = "Data sebesar Rp " . number_format($missingInRekapitulasi) . " tidak masuk dalam rekapitulasi";
        }
        
        // 2. Cek apakah ada data yang tidak masuk dalam terbayar
        $missingInTerbayar = $rekapitulasiData['total_rekapitulasi'] - $terbayar;
        if ($missingInTerbayar > 0) {
            $causes[] = "Data sebesar Rp " . number_format($missingInTerbayar) . " ada di rekapitulasi tapi tidak di terbayar";
        }
        
        // 3. Cek apakah ada data yang double counting
        $doubleCounting = $terbayar - $rekapitulasiData['total_rekapitulasi'];
        if ($doubleCounting > 0) {
            $causes[] = "Kemungkinan double counting sebesar Rp " . number_format($doubleCounting);
        }
        
        // 4. Cek data yang tidak ada di periode yang dipilih
        $dataOutsidePeriod = $this->checkDataOutsidePeriod($posId, $periodId, $startDate, $endDate);
        if ($dataOutsidePeriod > 0) {
            $causes[] = "Ada data sebesar Rp " . number_format($dataOutsidePeriod) . " di luar periode yang dipilih";
        }
        
        return $causes;
    }
    
    private function checkDataOutsidePeriod($posId, $periodId, $startDate, $endDate)
    {
        // Cek data bulanan di luar periode
        $bulanOutsidePeriod = DB::table('bulan')
            ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereNotNull('bulan.bulan_date_pay')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where('bulan.bulan_date_pay', '<', $startDate)
                      ->orWhere('bulan.bulan_date_pay', '>', $endDate);
            })
            ->sum('bulan.bulan_bill');
            
        // Cek data bebas di luar periode
        $bebasOutsidePeriod = DB::table('bebas_pay')
            ->join('bebas', 'bebas_pay.bebas_bebas_id', '=', 'bebas.bebas_id')
            ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->where('bebas_pay.bebas_pay_bill', '>', 0)
            ->where(function($query) use ($startDate, $endDate) {
                $query->where('bebas_pay.bebas_pay_input_date', '<', $startDate)
                      ->orWhere('bebas_pay.bebas_pay_input_date', '>', $endDate);
            })
            ->sum('bebas_pay.bebas_pay_bill');
            
        return $bulanOutsidePeriod + $bebasOutsidePeriod;
    }

    private function getMonthName($month)
    {
        // Urutan bulan akademik: 1=Juli, 2=Agustus, 3=September, dst
        $months = [
            '1' => 'Juli', '2' => 'Agustus', '3' => 'September',
            '4' => 'Oktober', '5' => 'November', '6' => 'Desember',
            '7' => 'Januari', '8' => 'Februari', '9' => 'Maret',
            '10' => 'April', '11' => 'Mei', '12' => 'Juni'
        ];
        
        return $months[$month] ?? 'Unknown';
    }

    // Method test khusus untuk menganalisis perbedaan terbayar
    public function testTerbayarComparison($posId = null, $periodId = null, $startDate = null, $endDate = null)
    {
        try {
            // Gunakan parameter default jika tidak ada
            if (!$posId) {
                $pos = Pos::first();
                $posId = $pos ? $pos->pos_id : 1;
            }
            
            if (!$periodId) {
                $period = Period::where('period_status', 1)->first();
                $periodId = $period ? $period->period_id : 1;
            }
            
            if (!$startDate) {
                $startDate = '2025-09-01'; // Sesuai dengan data yang Anda tunjukkan
            }
            
            if (!$endDate) {
                $endDate = '2025-09-04'; // Sesuai dengan data yang Anda tunjukkan
            }
            
            // 1. Hitung Terbayar dari Target Analisis (RealisasiPosController)
            $terbayarTargetAnalisis = $this->calculateTerbayar($posId, $periodId, $startDate, $endDate);
            
            // 2. Hitung Terbayar dari Rekapitulasi (PaymentController)
            $terbayarRekapitulasi = $this->getRekapitulasiDataForComparison($posId, $startDate, $endDate);
            
            // 3. Detail breakdown untuk analisis
            $detailAnalysis = $this->getTerbayarDetailAnalysis($posId, $periodId, $startDate, $endDate);
            
            return response()->json([
                'success' => true,
                'test_parameters' => [
                    'pos_id' => $posId,
                    'period_id' => $periodId,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'terbayar_comparison' => [
                    'target_analisis' => $terbayarTargetAnalisis,
                    'rekapitulasi_total' => $terbayarRekapitulasi['total_rekapitulasi'],
                    'difference' => $terbayarTargetAnalisis - $terbayarRekapitulasi['total_rekapitulasi'],
                    'percentage_diff' => $terbayarTargetAnalisis > 0 ? (($terbayarTargetAnalisis - $terbayarRekapitulasi['total_rekapitulasi']) / $terbayarTargetAnalisis) * 100 : 0
                ],
                'rekapitulasi_breakdown' => $terbayarRekapitulasi,
                'target_analisis_breakdown' => $detailAnalysis,
                'possible_causes' => $this->identifyTerbayarDifferences($posId, $periodId, $startDate, $endDate, $terbayarTargetAnalisis, $terbayarRekapitulasi)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    private function getTerbayarDetailAnalysis($posId, $periodId, $startDate, $endDate)
    {
        // 1. Terbayar Bulanan dari Target Analisis
        $terbayarBulananTarget = DB::table('bulan')
            ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
            ->whereNotNull('bulan.bulan_date_pay')
            ->sum('bulan.bulan_bill');
            
        // 2. Terbayar Bebas dari Target Analisis
        $terbayarBebasTarget = DB::table('bebas_pay')
            ->join('bebas', 'bebas_pay.bebas_bebas_id', '=', 'bebas.bebas_id')
            ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bebas_pay.bebas_pay_input_date', [$startDate, $endDate])
            ->where('bebas_pay.bebas_pay_bill', '>', 0)
            ->sum('bebas_pay.bebas_pay_bill');
            
        // 3. Cek data yang mungkin double counting
        $doubleCountingData = $this->checkDoubleCountingData($posId, $periodId, $startDate, $endDate);
        
        // 4. Cek data yang tidak masuk rekapitulasi
        $missingInRekapitulasi = $this->checkMissingInRekapitulasi($posId, $periodId, $startDate, $endDate);
        
        return [
            'terbayar_bulanan_target' => $terbayarBulananTarget,
            'terbayar_bebas_target' => $terbayarBebasTarget,
            'total_target_analisis' => $terbayarBulananTarget + $terbayarBebasTarget,
            'double_counting_data' => $doubleCountingData,
            'missing_in_rekapitulasi' => $missingInRekapitulasi,
            'sample_data_bulanan' => $this->getSampleBulananData($posId, $periodId, $startDate, $endDate),
            'sample_data_bebas' => $this->getSampleBebasData($posId, $periodId, $startDate, $endDate)
        ];
    }
    
    private function checkDoubleCountingData($posId, $periodId, $startDate, $endDate)
    {
        // Cek apakah ada data yang masuk di bulan dan bebas sekaligus
        $doubleData = DB::table('bulan')
            ->join('payment as p1', 'bulan.payment_payment_id', '=', 'p1.payment_id')
            ->join('bebas_pay', function($join) {
                $join->on('bebas_pay.bebas_bebas_id', '=', DB::raw('bulan.bulan_id'))
                     ->orOn('bebas_pay.bebas_bebas_id', '=', DB::raw('p1.payment_id'));
            })
            ->join('payment as p2', 'bebas_pay.bebas_bebas_id', '=', 'p2.payment_id')
            ->where('p1.pos_pos_id', $posId)
            ->where('p1.period_period_id', $periodId)
            ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
            ->whereBetween('bebas_pay.bebas_pay_input_date', [$startDate, $endDate])
            ->whereNotNull('bulan.bulan_date_pay')
            ->where('bebas_pay.bebas_pay_bill', '>', 0)
            ->get();
            
        return [
            'count' => $doubleData->count(),
            'data' => $doubleData->toArray()
        ];
    }
    
    private function checkMissingInRekapitulasi($posId, $periodId, $startDate, $endDate)
    {
        // Cek data yang ada di target analisis tapi tidak ada di rekapitulasi
        $missingData = [];
        
        // 1. Data bulanan yang tidak ada di rekapitulasi
        $bulananMissing = DB::table('bulan')
            ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
            ->whereNotNull('bulan.bulan_date_pay')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                      ->whereRaw('td.bulan_id = bulan.bulan_id')
                      ->where('td.bulan_id', '>', 0)
                      ->whereIn('t.status', [1, 2]);
            })
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer as t')
                      ->whereRaw('t.bill_id = bulan.bulan_id')
                      ->where('t.bill_type', 'bulanan')
                      ->whereIn('t.status', [1, 2]);
            })
            ->get();
            
        // 2. Data bebas yang tidak ada di rekapitulasi
        $bebasMissing = DB::table('bebas_pay')
            ->join('bebas', 'bebas_pay.bebas_bebas_id', '=', 'bebas.bebas_id')
            ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bebas_pay.bebas_pay_input_date', [$startDate, $endDate])
            ->where('bebas_pay.bebas_pay_bill', '>', 0)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                      ->whereRaw('td.bebas_id = bebas_pay.bebas_bebas_id')
                      ->where('td.bebas_id', '>', 0)
                      ->whereIn('t.status', [1, 2]);
            })
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer as t')
                      ->whereRaw('t.bill_id = bebas_pay.bebas_bebas_id')
                      ->where('t.bill_type', 'bebas')
                      ->whereIn('t.status', [1, 2]);
            })
            ->get();
            
        return [
            'bulanan_missing' => [
                'count' => $bulananMissing->count(),
                'total_amount' => $bulananMissing->sum('bulan_bill'),
                'sample_data' => $bulananMissing->take(5)->toArray()
            ],
            'bebas_missing' => [
                'count' => $bebasMissing->count(),
                'total_amount' => $bebasMissing->sum('bebas_pay_bill'),
                'sample_data' => $bebasMissing->take(5)->toArray()
            ]
        ];
    }
    
    private function getSampleBulananData($posId, $periodId, $startDate, $endDate)
    {
        return DB::table('bulan')
            ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
            ->join('students', 'bulan.student_student_id', '=', 'students.student_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
            ->whereNotNull('bulan.bulan_date_pay')
            ->select(
                'students.student_full_name',
                'bulan.bulan_date_pay',
                'bulan.bulan_bill',
                'bulan.bulan_id'
            )
            ->limit(10)
            ->get()
            ->toArray();
    }
    
    private function getSampleBebasData($posId, $periodId, $startDate, $endDate)
    {
        return DB::table('bebas_pay')
            ->join('bebas', 'bebas_pay.bebas_bebas_id', '=', 'bebas.bebas_id')
            ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
            ->join('students', 'bebas.student_student_id', '=', 'students.student_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bebas_pay.bebas_pay_input_date', [$startDate, $endDate])
            ->where('bebas_pay.bebas_pay_bill', '>', 0)
            ->select(
                'students.student_full_name',
                'bebas_pay.bebas_pay_input_date',
                'bebas_pay.bebas_pay_bill',
                'bebas_pay.bebas_bebas_id'
            )
            ->limit(10)
            ->get()
            ->toArray();
    }
    
    private function identifyTerbayarDifferences($posId, $periodId, $startDate, $endDate, $terbayarTarget, $rekapitulasiData)
    {
        $causes = [];
        
        // 1. Perbedaan utama
        $difference = $terbayarTarget - $rekapitulasiData['total_rekapitulasi'];
        
        if ($difference > 0) {
            $causes[] = "Target Analisis lebih besar Rp " . number_format($difference) . " dari Rekapitulasi";
            
            // 2. Analisis kemungkinan penyebab
            if ($difference > 1000000) { // Jika perbedaan > 1 juta
                $causes[] = "Kemungkinan ada data yang tidak masuk dalam rekapitulasi";
            }
            
            // 3. Cek apakah ada data yang double counting di target analisis
            $doubleCountCheck = $this->checkDoubleCountingData($posId, $periodId, $startDate, $endDate);
            if ($doubleCountCheck['count'] > 0) {
                $causes[] = "Ada " . $doubleCountCheck['count'] . " data yang mungkin double counting";
            }
            
            // 4. Cek apakah ada data yang tidak masuk rekapitulasi
            $missingCheck = $this->checkMissingInRekapitulasi($posId, $periodId, $startDate, $endDate);
            $totalMissing = $missingCheck['bulanan_missing']['total_amount'] + $missingCheck['bebas_missing']['total_amount'];
            if ($totalMissing > 0) {
                $causes[] = "Ada data sebesar Rp " . number_format($totalMissing) . " yang tidak masuk rekapitulasi";
            }
        } else if ($difference < 0) {
            $causes[] = "Rekapitulasi lebih besar Rp " . number_format(abs($difference)) . " dari Target Analisis";
            $causes[] = "Kemungkinan ada data yang tidak masuk dalam target analisis";
        } else {
            $causes[] = "Target Analisis dan Rekapitulasi sama";
        }
        
                return $causes;
    }
    
    // Method test untuk menganalisis perbedaan dengan data yang sama
    public function testSameDataComparison($posName = null, $startDate = null, $endDate = null)
    {
        try {
            // Gunakan parameter default jika tidak ada
            if (!$posName) {
                $posName = 'TAB WAJIB'; // Sesuai dengan data yang Anda tunjukkan
            }
            
            if (!$startDate) {
                $startDate = '2025-09-01'; // Sesuai dengan data yang Anda tunjukkan
            }
            
            if (!$endDate) {
                $endDate = '2025-09-04'; // Sesuai dengan data yang Anda tunjukkan
            }
            
            // Ambil POS ID berdasarkan nama
            $pos = DB::table('pos_pembayaran')->where('pos_name', $posName)->first();
            if (!$pos) {
                return response()->json([
                    'success' => false,
                    'message' => 'POS dengan nama "' . $posName . '" tidak ditemukan'
                ]);
            }
            
            $posId = $pos->pos_id;
            $periodId = 1; // Default period
            
            // 1. Hitung Target Analisis
            $targetAnalisis = $this->calculateTarget($posId, $periodId, $startDate, $endDate);
            $terbayarAnalisis = $this->calculateTerbayar($posId, $periodId, $startDate, $endDate);
            
            // 2. Hitung Rekapitulasi
            $rekapitulasiData = $this->getRekapitulasiDataForComparison($posId, $startDate, $endDate);
            
            // 3. Detail breakdown untuk analisis
            $detailAnalysis = $this->getDetailedComparison($posId, $periodId, $startDate, $endDate, $posName);
            
            return response()->json([
                'success' => true,
                'test_parameters' => [
                    'pos_name' => $posName,
                    'pos_id' => $posId,
                    'period_id' => $periodId,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'comparison_results' => [
                    'target_analisis' => [
                        'target' => $targetAnalisis,
                        'terbayar' => $terbayarAnalisis,
                        'belum_terbayar' => $targetAnalisis - $terbayarAnalisis,
                        'pencapaian' => $targetAnalisis > 0 ? ($terbayarAnalisis / $targetAnalisis) * 100 : 0
                    ],
                    'rekapitulasi' => [
                        'tunai' => $rekapitulasiData['total_tunai'],
                        'transfer' => $rekapitulasiData['total_transfer'],
                        'payment_gateway' => $rekapitulasiData['total_payment_gateway'],
                        'total' => $rekapitulasiData['total_rekapitulasi'],
                        'breakdown' => [
                            'tunai_bulan' => $rekapitulasiData['tunai_bulan'],
                            'tunai_bebas' => $rekapitulasiData['tunai_bebas'],
                            'transfer_bulan' => $rekapitulasiData['transfer_bulan'],
                            'transfer_bebas' => $rekapitulasiData['transfer_bebas'],
                            'payment_gateway_bulan' => $rekapitulasiData['payment_gateway_bulan'],
                            'payment_gateway_bebas' => $rekapitulasiData['payment_gateway_bebas']
                        ]
                    ],
                    'differences' => [
                        'terbayar_vs_rekapitulasi' => $terbayarAnalisis - $rekapitulasiData['total_rekapitulasi'],
                        'percentage_diff' => $terbayarAnalisis > 0 ? (($terbayarAnalisis - $rekapitulasiData['total_rekapitulasi']) / $terbayarAnalisis) * 100 : 0
                    ]
                ],
                'detail_analysis' => $detailAnalysis,
                'possible_causes' => $this->identifySpecificCauses($posId, $periodId, $startDate, $endDate, $terbayarAnalisis, $rekapitulasiData)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    private function getDetailedComparison($posId, $periodId, $startDate, $endDate, $posName)
    {
        $analysis = [];
        
        // 1. Data dari Target Analisis
        $analysis['target_analisis_detail'] = [
            'bulanan' => $this->getTargetAnalisisBulanan($posId, $periodId, $startDate, $endDate),
            'bebas' => $this->getTargetAnalisisBebas($posId, $periodId, $startDate, $endDate)
        ];
        
        // 2. Data dari Rekapitulasi
        $analysis['rekapitulasi_detail'] = [
            'tunai_bulan' => $this->getRekapitulasiTunaiBulan($posId, $startDate, $endDate),
            'tunai_bebas' => $this->getRekapitulasiTunaiBebas($posId, $startDate, $endDate),
            'transfer_bulan' => $this->getRekapitulasiTransferBulan($posId, $startDate, $endDate),
            'transfer_bebas' => $this->getRekapitulasiTransferBebas($posId, $startDate, $endDate)
        ];
        
        // 3. Sample data untuk analisis
        $analysis['sample_data'] = [
            'target_analisis_bulanan' => $this->getSampleTargetAnalisisBulanan($posId, $periodId, $startDate, $endDate),
            'target_analisis_bebas' => $this->getSampleTargetAnalisisBebas($posId, $periodId, $startDate, $endDate),
            'rekapitulasi_tunai' => $this->getSampleRekapitulasiTunai($posId, $startDate, $endDate),
            'rekapitulasi_transfer' => $this->getSampleRekapitulasiTransfer($posId, $startDate, $endDate)
        ];
        
        return $analysis;
    }
    
    private function getTargetAnalisisBulanan($posId, $periodId, $startDate, $endDate)
    {
        return DB::table('bulan')
            ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
            ->join('students', 'bulan.student_student_id', '=', 'students.student_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
            ->whereNotNull('bulan.bulan_date_pay')
            ->select(
                'students.student_full_name',
                'bulan.bulan_date_pay',
                'bulan.bulan_bill',
                'bulan.bulan_id'
            )
            ->get()
            ->toArray();
    }
    
    private function getTargetAnalisisBebas($posId, $periodId, $startDate, $endDate)
    {
        return DB::table('bebas_pay')
            ->join('bebas', 'bebas_pay.bebas_bebas_id', '=', 'bebas.bebas_id')
            ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
            ->join('students', 'bebas.student_student_id', '=', 'students.student_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bebas_pay.bebas_pay_input_date', [$startDate, $endDate])
            ->where('bebas_pay.bebas_pay_bill', '>', 0)
            ->select(
                'students.student_full_name',
                'bebas_pay.bebas_pay_input_date',
                'bebas_pay.bebas_pay_bill',
                'bebas_pay.bebas_bebas_id'
            )
            ->get()
            ->toArray();
    }
    
    private function getRekapitulasiTunaiBulan($posId, $startDate, $endDate)
    {
        return DB::table('bulan as b')
            ->join('students as s', 'b.student_student_id', '=', 's.student_id')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->where('p.pos_pos_id', $posId)
            ->whereNotNull('b.bulan_date_pay')
            ->whereRaw('DATE(b.bulan_date_pay) >= ? AND DATE(b.bulan_date_pay) <= ?', [$startDate, $endDate])
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                      ->whereRaw('td.bulan_id = b.bulan_id')
                      ->where('td.bulan_id', '>', 0)
                      ->whereIn('t.status', [1, 2]);
            })
            ->select(
                's.student_full_name',
                'b.bulan_date_pay',
                'b.bulan_bill',
                'b.bulan_id'
            )
            ->get()
            ->toArray();
    }
    
    private function getRekapitulasiTunaiBebas($posId, $startDate, $endDate)
    {
        return DB::table('bebas_pay as bp')
            ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
            ->join('students as s', 'be.student_student_id', '=', 's.student_id')
            ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
            ->where('p.pos_pos_id', $posId)
            ->whereNotNull('bp.bebas_pay_input_date')
            ->whereRaw('DATE(bp.bebas_pay_input_date) >= ? AND DATE(bp.bebas_pay_input_date) <= ?', [$startDate, $endDate])
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                      ->whereRaw('td.bebas_id = bp.bebas_bebas_id')
                      ->where('td.bebas_id', '>', 0)
                      ->whereIn('t.status', [1, 2]);
            })
            ->select(
                's.student_full_name',
                'bp.bebas_pay_input_date',
                'bp.bebas_pay_bill',
                'bp.bebas_bebas_id'
            )
            ->get()
            ->toArray();
    }
    
    private function getRekapitulasiTransferBulan($posId, $startDate, $endDate)
    {
        return DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->join('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('p.pos_pos_id', $posId)
            ->whereNotNull('t.confirm_pay')
            ->where('t.status', 1)
            ->where('td.payment_type', '!=', 3)
            ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate])
            ->select(
                's.student_full_name',
                't.updated_at as payment_date',
                'td.subtotal as payment_amount',
                't.transfer_id'
            )
            ->get()
            ->toArray();
    }
    
    private function getRekapitulasiTransferBebas($posId, $startDate, $endDate)
    {
        return DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->join('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
            ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('p.pos_pos_id', $posId)
            ->whereNotNull('t.confirm_pay')
            ->where('t.status', 1)
            ->where('td.payment_type', '!=', 3)
            ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate])
            ->select(
                's.student_full_name',
                't.updated_at as payment_date',
                'td.subtotal as payment_amount',
                't.transfer_id'
            )
            ->get()
            ->toArray();
    }
    
    private function getSampleTargetAnalisisBulanan($posId, $periodId, $startDate, $endDate)
    {
        return DB::table('bulan')
            ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
            ->join('students', 'bulan.student_student_id', '=', 'students.student_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
            ->whereNotNull('bulan.bulan_date_pay')
            ->select(
                'students.student_full_name',
                'bulan.bulan_date_pay',
                'bulan.bulan_bill',
                'bulan.bulan_id'
            )
            ->limit(5)
            ->get()
            ->toArray();
    }
    
    private function getSampleTargetAnalisisBebas($posId, $periodId, $startDate, $endDate)
    {
        return DB::table('bebas_pay')
            ->join('bebas', 'bebas_pay.bebas_bebas_id', '=', 'bebas.bebas_id')
            ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
            ->join('students', 'bebas.student_student_id', '=', 'students.student_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bebas_pay.bebas_pay_input_date', [$startDate, $endDate])
            ->where('bebas_pay.bebas_pay_bill', '>', 0)
            ->select(
                'students.student_full_name',
                'bebas_pay.bebas_pay_input_date',
                'bebas_pay.bebas_pay_bill',
                'bebas_pay.bebas_bebas_id'
            )
            ->limit(5)
            ->get()
            ->toArray();
    }
    
    private function getSampleRekapitulasiTunai($posId, $startDate, $endDate)
    {
        return DB::table('bulan as b')
            ->join('students as s', 'b.student_student_id', '=', 's.student_id')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->where('p.pos_pos_id', $posId)
            ->whereNotNull('b.bulan_date_pay')
            ->whereRaw('DATE(b.bulan_date_pay) >= ? AND DATE(b.bulan_date_pay) <= ?', [$startDate, $endDate])
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                      ->whereRaw('td.bulan_id = b.bulan_id')
                      ->where('td.bulan_id', '>', 0)
                      ->whereIn('t.status', [1, 2]);
            })
            ->select(
                's.student_full_name',
                'b.bulan_date_pay',
                'b.bulan_bill',
                'b.bulan_id'
            )
            ->limit(5)
            ->get()
            ->toArray();
    }
    
    private function getSampleRekapitulasiTransfer($posId, $startDate, $endDate)
    {
        return DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->join('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('p.pos_pos_id', $posId)
            ->whereNotNull('t.confirm_pay')
            ->where('t.status', 1)
            ->where('td.payment_type', '!=', 3)
            ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate])
            ->select(
                's.student_full_name',
                't.updated_at as payment_date',
                'td.subtotal as payment_amount',
                't.transfer_id'
            )
            ->limit(5)
            ->get()
            ->toArray();
    }
    
    private function identifySpecificCauses($posId, $periodId, $startDate, $endDate, $terbayarAnalisis, $rekapitulasiData)
    {
        $causes = [];
        
        // 1. Perbedaan utama
        $difference = $terbayarAnalisis - $rekapitulasiData['total_rekapitulasi'];
        
        if ($difference != 0) {
            $causes[] = "Perbedaan sebesar Rp " . number_format($difference) . " antara Target Analisis dan Rekapitulasi";
            
            // 2. Analisis detail
            if ($difference > 0) {
                $causes[] = "Target Analisis lebih besar - kemungkinan ada data yang tidak masuk rekapitulasi";
                
                // Cek data yang tidak masuk rekapitulasi
                $missingData = $this->checkMissingDataInRekapitulasi($posId, $periodId, $startDate, $endDate);
                if ($missingData['total_amount'] > 0) {
                    $causes[] = "Data sebesar Rp " . number_format($missingData['total_amount']) . " tidak masuk rekapitulasi";
                    $causes[] = "Detail: " . $missingData['bulanan_count'] . " transaksi bulanan, " . $missingData['bebas_count'] . " transaksi bebas";
                }
            } else {
                $causes[] = "Rekapitulasi lebih besar - kemungkinan ada data yang tidak masuk target analisis";
            }
            
            // 3. Cek perbedaan tanggal
            $dateDifference = $this->checkDateDifference($posId, $periodId, $startDate, $endDate);
            if ($dateDifference['count'] > 0) {
                $causes[] = "Ada " . $dateDifference['count'] . " transaksi dengan tanggal pembayaran berbeda";
            }
            
            // 4. Cek perbedaan periode
            $periodDifference = $this->checkPeriodDifference($posId, $periodId, $startDate, $endDate);
            if ($periodDifference['count'] > 0) {
                $causes[] = "Ada " . $periodDifference['count'] . " transaksi dari periode yang berbeda";
            }
        } else {
            $causes[] = "Target Analisis dan Rekapitulasi sama";
        }
        
        return $causes;
    }
    
    private function checkMissingDataInRekapitulasi($posId, $periodId, $startDate, $endDate)
    {
        // Data bulanan yang tidak masuk rekapitulasi
        $bulananMissing = DB::table('bulan')
            ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
            ->whereNotNull('bulan.bulan_date_pay')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                      ->whereRaw('td.bulan_id = bulan.bulan_id')
                      ->where('td.bulan_id', '>', 0)
                      ->whereIn('t.status', [1, 2]);
            })
            ->get();
            
        // Data bebas yang tidak masuk rekapitulasi
        $bebasMissing = DB::table('bebas_pay')
            ->join('bebas', 'bebas_pay.bebas_bebas_id', '=', 'bebas.bebas_id')
            ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereBetween('bebas_pay.bebas_pay_input_date', [$startDate, $endDate])
            ->where('bebas_pay.bebas_pay_bill', '>', 0)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                      ->whereRaw('td.bebas_id = bebas_pay.bebas_bebas_id')
                      ->where('td.bebas_id', '>', 0)
                      ->whereIn('t.status', [1, 2]);
            })
            ->get();
            
        return [
            'bulanan_count' => $bulananMissing->count(),
            'bulanan_amount' => $bulananMissing->sum('bulan_bill'),
            'bebas_count' => $bebasMissing->count(),
            'bebas_amount' => $bebasMissing->sum('bebas_pay_bill'),
            'total_amount' => $bulananMissing->sum('bulan_bill') + $bebasMissing->sum('bebas_pay_bill')
        ];
    }
    
    private function checkDateDifference($posId, $periodId, $startDate, $endDate)
    {
        // Cek data yang ada di target analisis tapi tanggalnya berbeda dengan rekapitulasi
        $dateDiff = DB::table('bulan')
            ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', $periodId)
            ->whereNotNull('bulan.bulan_date_pay')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where('bulan.bulan_date_pay', '<', $startDate)
                      ->orWhere('bulan.bulan_date_pay', '>', $endDate);
            })
            ->count();
            
        return [
            'count' => $dateDiff
        ];
    }
    
    private function checkPeriodDifference($posId, $periodId, $startDate, $endDate)
    {
        // Cek data yang ada di target analisis tapi dari periode yang berbeda
        $periodDiff = DB::table('bulan')
            ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
            ->where('payment.pos_pos_id', $posId)
            ->where('payment.period_period_id', '!=', $periodId)
            ->whereBetween('bulan.bulan_date_pay', [$startDate, $endDate])
            ->whereNotNull('bulan.bulan_date_pay')
            ->count();
            
        return [
            'count' => $periodDiff
        ];
    }

    /**
     * Test method untuk menampilkan hasil perbandingan yang jelas
     */
    public function testFinalComparison($posName = null, $startDate = null, $endDate = null)
    {
        try {
            // Set default values if not provided
            $posName = $posName ?: 'TAB WAJIB';
            $startDate = $startDate ?: '2025-09-01';
            $endDate = $endDate ?: '2025-09-04';
            
            // Get POS ID
            $pos = DB::table('pos_pembayaran')->where('pos_name', 'LIKE', '%' . $posName . '%')->first();
            if (!$pos) {
                return response()->json(['success' => false, 'message' => 'POS tidak ditemukan: ' . $posName]);
            }
            
            $posId = $pos->pos_id;
            $periodId = 1; // Default period
            
            // Get data from both methods
            $targetAnalisis = $this->calculateTarget($posId, $periodId, $startDate, $endDate);
            $terbayarAnalisis = $this->calculateTerbayar($posId, $periodId, $startDate, $endDate);
            $rekapitulasiData = $this->getRekapitulasiDataForComparison($posId, $startDate, $endDate);
            $rekapitulasiTotal = $rekapitulasiData['total_rekapitulasi'];
            
            // Calculate differences
            $differenceTerbayar = $terbayarAnalisis - $rekapitulasiTotal;
            $differenceTarget = $targetAnalisis - $rekapitulasiTotal;
            
            return response()->json([
                'success' => true,
                'test_parameters' => [
                    'pos_name' => $posName,
                    'pos_id' => $posId,
                    'period_id' => $periodId,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'results' => [
                    'target_analisis' => [
                        'target' => $targetAnalisis,
                        'terbayar' => $terbayarAnalisis,
                        'belum_terbayar' => $targetAnalisis - $terbayarAnalisis,
                        'pencapaian' => $targetAnalisis > 0 ? round(($terbayarAnalisis / $targetAnalisis) * 100, 2) : 0
                    ],
                    'rekapitulasi' => [
                        'tunai' => $rekapitulasiData['total_tunai'],
                        'transfer' => $rekapitulasiData['total_transfer'],
                        'payment_gateway' => $rekapitulasiData['total_payment_gateway'],
                        'total' => $rekapitulasiTotal
                    ],
                    'comparison' => [
                        'terbayar_vs_rekapitulasi' => [
                            'difference' => $differenceTerbayar,
                            'is_match' => $differenceTerbayar == 0,
                            'percentage_diff' => $terbayarAnalisis > 0 ? round(($differenceTerbayar / $terbayarAnalisis) * 100, 2) : 0
                        ],
                        'target_vs_rekapitulasi' => [
                            'difference' => $differenceTarget,
                            'is_match' => $differenceTarget == 0,
                            'percentage_diff' => $targetAnalisis > 0 ? round(($differenceTarget / $targetAnalisis) * 100, 2) : 0
                        ]
                    ]
                ],
                'conclusion' => [
                    'status' => $differenceTerbayar == 0 ? 'SUCCESS' : 'DIFFERENCE_FOUND',
                    'message' => $differenceTerbayar == 0 ? 
                        'Target Analisis dan Rekapitulasi sudah sama!' : 
                        'Masih ada perbedaan sebesar Rp ' . number_format($differenceTerbayar),
                    'recommendation' => $differenceTerbayar == 0 ? 
                        'Perhitungan sudah akurat dan konsisten' : 
                        'Perlu analisis lebih lanjut untuk menemukan penyebab perbedaan'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * Ambil data realisasi berdasarkan kelas
     */
    private function getRealisasiDataByClass($periodId, $startDate, $endDate, $selectedClass = null)
    {
        try {
            // Log untuk debugging
            \Log::info('getRealisasiDataByClass called with:', [
                'periodId' => $periodId, 
                'startDate' => $startDate, 
                'endDate' => $endDate,
                'selectedClass' => $selectedClass
            ]);
            
            // Query untuk mendapatkan data kelas dengan realisasi
            $query = DB::table('class_models as c')
                ->select([
                    'c.class_id',
                    'c.class_name',
                    DB::raw('COUNT(DISTINCT s.student_id) as jumlah_siswa'),
                    DB::raw('SUM(CASE WHEN b.bulan_id IS NOT NULL THEN b.bulan_bill ELSE 0 END) as target_bulanan'),
                    DB::raw('SUM(CASE WHEN be.bebas_id IS NOT NULL THEN be.bebas_bill ELSE 0 END) as target_bebas'),
                    DB::raw('SUM(CASE WHEN b.bulan_id IS NOT NULL THEN b.bulan_bill ELSE 0 END) + SUM(CASE WHEN be.bebas_id IS NOT NULL THEN be.bebas_bill ELSE 0 END) as total_target')
                ])
                ->leftJoin('students as s', 'c.class_id', '=', 's.class_class_id')
                ->leftJoin('bulan as b', function($join) use ($periodId) {
                    $join->on('s.student_id', '=', 'b.student_student_id')
                         ->join('payment as pb', 'b.payment_payment_id', '=', 'pb.payment_id')
                         ->where('pb.period_period_id', $periodId);
                })
                ->leftJoin('bebas as be', function($join) use ($periodId) {
                    $join->on('s.student_id', '=', 'be.student_student_id')
                         ->join('payment as pe', 'be.payment_payment_id', '=', 'pe.payment_id')
                         ->where('pe.period_period_id', $periodId);
                })
                ->groupBy('c.class_id', 'c.class_name');
            
            // Filter kelas tertentu jika dipilih
            if ($selectedClass) {
                $query->where('c.class_id', $selectedClass);
            }
            
            $classData = $query->get();
            
            $realisasiData = [];
            $totalTagihan = 0;
            $totalTerbayar = 0;
            $totalBelumTerbayar = 0;
            
            foreach ($classData as $class) {
                \Log::info('Processing Class:', [
                    'class_id' => $class->class_id, 
                    'class_name' => $class->class_name,
                    'jumlah_siswa' => $class->jumlah_siswa,
                    'total_target' => $class->total_target
                ]);
                
                // Hitung terbayar untuk kelas ini
                $terbayar = $this->calculateTerbayarByClass($class->class_id, $periodId, $startDate, $endDate);
                $target = $class->total_target;
                $belumTerbayar = $target - $terbayar;
                $pencapaian = $target > 0 ? ($terbayar / $target) * 100 : 0;
                
                \Log::info('Class calculation result:', [
                    'class_id' => $class->class_id,
                    'target' => $target,
                    'terbayar' => $terbayar,
                    'belum_terbayar' => $belumTerbayar,
                    'pencapaian' => $pencapaian
                ]);
                
                $realisasiData[] = [
                    'class_id' => $class->class_id,
                    'class_name' => $class->class_name,
                    'jumlah_siswa' => $class->jumlah_siswa,
                    'target' => $target,
                    'terbayar' => $terbayar,
                    'belum_terbayar' => $belumTerbayar,
                    'pencapaian' => round($pencapaian, 1)
                ];
                
                $totalTagihan += $target;
                $totalTerbayar += $terbayar;
                $totalBelumTerbayar += $belumTerbayar;
            }
            
            // Urutkan berdasarkan prosentase pencapaian (descending)
            usort($realisasiData, function($a, $b) {
                return $b['pencapaian'] <=> $a['pencapaian'];
            });
            
            // Tambahkan total
            $totalPencapaian = $totalTagihan > 0 ? ($totalTerbayar / $totalTagihan) * 100 : 0;
            
            $realisasiData[] = [
                'class_id' => null,
                'class_name' => 'TOTAL',
                'jumlah_siswa' => array_sum(array_column($realisasiData, 'jumlah_siswa')),
                'target' => $totalTagihan,
                'terbayar' => $totalTerbayar,
                'belum_terbayar' => $totalBelumTerbayar,
                'pencapaian' => round($totalPencapaian, 1),
                'is_total' => true
            ];
            
            \Log::info('getRealisasiDataByClass completed successfully', ['total_records' => count($realisasiData)]);
            return $realisasiData;
            
        } catch (\Exception $e) {
            \Log::error('Error in getRealisasiDataByClass:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Hitung terbayar berdasarkan kelas
     */
    private function calculateTerbayarByClass($classId, $periodId, $startDate, $endDate)
    {
        try {
            // ✅ SOURCE 1: Pembayaran Tunai Bulanan per Kelas
            $terbayarTunaiBulanan = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                ->where('s.class_class_id', $classId)
                ->where('p.period_period_id', $periodId)
                ->whereNotNull('b.bulan_date_pay')
                ->whereRaw('DATE(b.bulan_date_pay) >= ? AND DATE(b.bulan_date_pay) <= ?', [$startDate, $endDate])
                // Exclude transfer payments
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('transfer_detail as td')
                          ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                          ->whereRaw('td.bulan_id = b.bulan_id')
                          ->where('td.bulan_id', '>', 0)
                          ->whereIn('t.status', [1, 2]);
                })
                // Exclude payment gateway
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('online_payments as op')
                          ->whereRaw('op.bill_id = b.bulan_id')
                          ->where('op.bill_type', 'bulanan')
                          ->where('op.status', 'success');
                })
                ->sum('b.bulan_bill');

            // ✅ SOURCE 2: Pembayaran Tunai Bebas per Kelas
            $terbayarTunaiBebas = DB::table('bebas_pay as bp')
                ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                ->where('s.class_class_id', $classId)
                ->where('p.period_period_id', $periodId)
                ->whereNotNull('bp.bebas_pay_input_date')
                ->whereRaw('DATE(bp.bebas_pay_input_date) >= ? AND DATE(bp.bebas_pay_input_date) <= ?', [$startDate, $endDate])
                ->where('bp.bebas_pay_bill', '>', 0)
                // Exclude transfer payments
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('transfer_detail as td')
                          ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                          ->whereRaw('td.bebas_id = bp.bebas_bebas_id')
                          ->where('td.bebas_id', '>', 0)
                          ->whereIn('t.status', [1, 2]);
                })
                // Exclude payment gateway
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('online_payments as op')
                          ->whereRaw('op.bill_id = bp.bebas_bebas_id')
                          ->where('op.bill_type', 'bebas')
                          ->where('op.status', 'success');
                })
                ->sum('bp.bebas_pay_bill');

            // ✅ SOURCE 3: Pembayaran Transfer Bulanan per Kelas
            $terbayarTransferBulanan = DB::table('transfer as t')
                ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
                ->join('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                ->where('s.class_class_id', $classId)
                ->where('p.period_period_id', $periodId)
                ->whereNotNull('t.confirm_pay')
                ->where('t.status', 1)
                ->where('td.payment_type', '!=', 3)
                ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate])
                ->sum('td.subtotal');

            // ✅ SOURCE 4: Pembayaran Transfer Bebas per Kelas
            $terbayarTransferBebas = DB::table('transfer as t')
                ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
                ->join('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                ->where('s.class_class_id', $classId)
                ->where('p.period_period_id', $periodId)
                ->whereNotNull('t.confirm_pay')
                ->where('t.status', 1)
                ->where('td.payment_type', '!=', 3)
                ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate])
                ->sum('td.subtotal');

            // ✅ SOURCE 5: Pembayaran Payment Gateway Bulanan per Kelas
            $terbayarPaymentGatewayBulanan = DB::table('online_payments as op')
                ->join('bulan as b', 'op.bill_id', '=', 'b.bulan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                ->where('s.class_class_id', $classId)
                ->where('p.period_period_id', $periodId)
                ->where('op.bill_type', 'bulanan')
                ->where('op.status', 'success')
                ->whereRaw('DATE(op.created_at) >= ? AND DATE(op.created_at) <= ?', [$startDate, $endDate])
                ->sum('op.amount');

            // ✅ SOURCE 6: Pembayaran Payment Gateway Bebas per Kelas
            $terbayarPaymentGatewayBebas = DB::table('online_payments as op')
                ->join('bebas as be', 'op.bill_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                ->where('s.class_class_id', $classId)
                ->where('p.period_period_id', $periodId)
                ->where('op.bill_type', 'bebas')
                ->where('op.status', 'success')
                ->whereRaw('DATE(op.created_at) >= ? AND DATE(op.created_at) <= ?', [$startDate, $endDate])
                ->sum('op.amount');

            $totalTerbayar = $terbayarTunaiBulanan + $terbayarTunaiBebas + 
                           $terbayarTransferBulanan + $terbayarTransferBebas + 
                           $terbayarPaymentGatewayBulanan + $terbayarPaymentGatewayBebas;

            // Debug: Log hasil perhitungan
            \Log::info('Terbayar calculation for Class ' . $classId . ':', [
                'class_id' => $classId,
                'period_id' => $periodId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'terbayar_tunai_bulanan' => $terbayarTunaiBulanan,
                'terbayar_tunai_bebas' => $terbayarTunaiBebas,
                'terbayar_transfer_bulanan' => $terbayarTransferBulanan,
                'terbayar_transfer_bebas' => $terbayarTransferBebas,
                'terbayar_payment_gateway_bulanan' => $terbayarPaymentGatewayBulanan,
                'terbayar_payment_gateway_bebas' => $terbayarPaymentGatewayBebas,
                'total_terbayar' => $totalTerbayar,
                'note' => 'Menggunakan pendekatan yang membedakan metode pembayaran per kelas'
            ]);

            return $totalTerbayar;

        } catch (\Exception $e) {
            \Log::error('Error in calculateTerbayarByClass:', [
                'message' => $e->getMessage(),
                'classId' => $classId,
                'periodId' => $periodId,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Test method untuk menampilkan hasil realisasi berdasarkan kelas
     */
    public function testRealisasiByClass($periodId = 1, $startDate = '2025-09-01', $endDate = '2025-09-04', $selectedClass = null)
    {
        try {
            $realisasiData = $this->getRealisasiDataByClass($periodId, $startDate, $endDate, $selectedClass);
            
            return response()->json([
                'success' => true,
                'data' => $realisasiData,
                'summary' => [
                    'total_classes' => count($realisasiData) - 1, // Exclude total row
                    'period_id' => $periodId,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'selected_class' => $selectedClass
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Test method untuk menampilkan hasil realisasi POS dengan filter kelas
     */
    public function testRealisasiPosWithClass($periodId = 1, $startDate = '2025-09-01', $endDate = '2025-09-04', $selectedClass = null)
    {
        try {
            $realisasiData = $this->getRealisasiData($periodId, $startDate, $endDate, $selectedClass);
            
            return response()->json([
                'success' => true,
                'data' => $realisasiData,
                'summary' => [
                    'total_pos' => count($realisasiData) - 1, // Exclude total row
                    'period_id' => $periodId,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'selected_class' => $selectedClass,
                    'view_type' => 'pos_with_class_filter'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
