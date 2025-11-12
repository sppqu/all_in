<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;

class BulkWhatsAppController extends Controller
{
    /**
     * Tampilkan halaman kirim tagihan masal
     */
    public function index()
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }

        // Ambil data periode berdasarkan school_id
        $periods = DB::table('periods')
            ->where('school_id', $currentSchoolId)
            ->orderBy('period_id', 'desc')
            ->get();

        // Ambil data pos pembayaran berdasarkan school_id
        $posPembayaran = DB::table('pos_pembayaran')
            ->where('school_id', $currentSchoolId)
            ->orderBy('pos_name', 'asc')
            ->get();

        // Ambil data kelas berdasarkan school_id
        $classes = DB::table('class_models')
            ->where('school_id', $currentSchoolId)
            ->orderBy('class_name', 'asc')
            ->get();

        return view('bulk-whatsapp.index', compact('periods', 'posPembayaran', 'classes'));
    }

    /**
     * Ambil data tagihan berdasarkan filter
     */
    public function getBills(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            // Filter berdasarkan sekolah yang sedang aktif
            $currentSchoolId = currentSchoolId();
            
            if (!$currentSchoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.'
                ], 403);
            }
            
            // Log incoming request
            Log::info('Bulk WhatsApp getBills called with:', $request->all());
            
            $request->validate([
                'period_id' => 'required',
                'pos_id' => 'required',
                'class_id' => 'nullable',
                'month_id' => 'nullable|integer|min:1|max:12',
                'student_status' => 'nullable|in:aktif,tidak aktif',
                'bill_type' => 'nullable|in:bulanan,bebas,all'
            ]);

            // Handle empty string values
            if ($request->month_id === '') {
                $request->merge(['month_id' => null]);
            }
            if ($request->class_id === '') {
                $request->merge(['class_id' => null]);
            }
            if ($request->student_status === '') {
                $request->merge(['student_status' => null]);
            }

            $periodId = $request->period_id;
            $posId = $request->pos_id;
            $classId = $request->class_id;
            $monthId = $request->month_id ? (int)$request->month_id : null;
            $studentStatus = $request->student_status;
            $billType = $request->bill_type ?? 'bulanan'; // Default to bulanan

            // Query untuk tagihan berdasarkan jenis yang dipilih
            if ($billType === 'bebas') {
                // Query untuk tagihan bebas
                $query = DB::table('students as s')
                    ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                    ->leftJoin('bebas as be', 's.student_id', '=', 'be.student_student_id')
                    ->leftJoin('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                    ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                    ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                    ->whereNotNull('be.bebas_id') // Pastikan ada data tagihan bebas
                    ->whereNotNull('p.payment_id') // Pastikan ada data payment
                    ->whereRaw('(be.bebas_bill - COALESCE(be.bebas_total_pay, 0)) > 0') // Hanya yang belum lunas
                    ->select(
                        's.student_id',
                        's.student_nis',
                        's.student_full_name',
                        's.student_parent_phone',
                        'c.class_name',
                        'be.bebas_id as bill_id',
                        DB::raw("NULL as month_month_id"),
                        DB::raw('(be.bebas_bill - COALESCE(be.bebas_total_pay, 0)) as bill_amount'),
                        DB::raw("NULL as bulan_date_pay"),
                        DB::raw("NULL as bulan_status"),
                        'pos.pos_name',
                        'per.period_start',
                        'per.period_end',
                        DB::raw("'bebas' as bill_type"),
                        'be.bebas_desc',
                        DB::raw("NULL as month_name")
                    );
            } elseif ($billType === 'all') {
                // Query untuk semua jenis tagihan menggunakan UNION (sudah terbukti berfungsi)
                $bulananQuery = DB::table('students as s')
                    ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                    ->leftJoin('bulan as b', 's.student_id', '=', 'b.student_student_id')
                    ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                    ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                    ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                    ->whereNotNull('b.bulan_id')
                    ->whereNull('b.bulan_date_pay')
                    ->select(
                        's.student_id',
                        's.student_nis',
                        's.student_full_name',
                        's.student_parent_phone',
                        'c.class_name',
                        'b.bulan_id as bill_id',
                        'b.month_month_id',
                        'b.bulan_bill as bill_amount',
                        'b.bulan_date_pay',
                        'b.bulan_status',
                        'pos.pos_name',
                        'per.period_start',
                        'per.period_end',
                        DB::raw("'bulanan' as bill_type"),
                        DB::raw("NULL as bebas_desc"),
                        DB::raw("NULL as month_name")
                    );

                $bebasQuery = DB::table('students as s')
                    ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                    ->leftJoin('bebas as be', 's.student_id', '=', 'be.student_student_id')
                    ->leftJoin('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                    ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                    ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                    ->whereNotNull('be.bebas_id')
                    ->whereRaw('(be.bebas_bill - COALESCE(be.bebas_total_pay, 0)) > 0')
                    ->select(
                        's.student_id',
                        's.student_nis',
                        's.student_full_name',
                        's.student_parent_phone',
                        'c.class_name',
                        'be.bebas_id as bill_id',
                        DB::raw("NULL as month_month_id"),
                        DB::raw('(be.bebas_bill - COALESCE(be.bebas_total_pay, 0)) as bill_amount'),
                        DB::raw("NULL as bulan_date_pay"),
                        DB::raw("NULL as bulan_status"),
                        'pos.pos_name',
                        'per.period_start',
                        'per.period_end',
                        DB::raw("'bebas' as bill_type"),
                        'be.bebas_desc',
                        DB::raw("NULL as month_name")
                    );

                $query = $bulananQuery->union($bebasQuery);
            } else {
                // Query untuk tagihan bulanan (default)
                $query = DB::table('students as s')
                    ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                    ->leftJoin('bulan as b', 's.student_id', '=', 'b.student_student_id')
                    ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                    ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                    ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                    ->whereNotNull('b.bulan_id') // Pastikan ada data tagihan
                    ->whereNotNull('p.payment_id') // Pastikan ada data payment
                    ->whereNull('b.bulan_date_pay') // Hanya tampilkan yang belum lunas
                    ->select(
                        's.student_id',
                        's.student_nis',
                        's.student_full_name',
                        's.student_parent_phone',
                        'c.class_name',
                        'b.bulan_id as bill_id',
                        'b.month_month_id',
                        'b.bulan_bill as bill_amount',
                        'b.bulan_date_pay',
                        'b.bulan_status',
                        'pos.pos_name',
                        'per.period_start',
                        'per.period_end',
                        DB::raw("'bulanan' as bill_type"),
                        DB::raw("NULL as bebas_desc"),
                        DB::raw("CASE 
                            WHEN b.month_month_id = 1 THEN 'Juli'
                            WHEN b.month_month_id = 2 THEN 'Agustus'
                            WHEN b.month_month_id = 3 THEN 'September'
                            WHEN b.month_month_id = 4 THEN 'Oktober'
                            WHEN b.month_month_id = 5 THEN 'November'
                            WHEN b.month_month_id = 6 THEN 'Desember'
                            WHEN b.month_month_id = 7 THEN 'Januari'
                            WHEN b.month_month_id = 8 THEN 'Februari'
                            WHEN b.month_month_id = 9 THEN 'Maret'
                            WHEN b.month_month_id = 10 THEN 'April'
                            WHEN b.month_month_id = 11 THEN 'Mei'
                            WHEN b.month_month_id = 12 THEN 'Juni'
                            ELSE 'Unknown'
                        END as month_name")
                    );
            }

            // Log initial query state
            Log::info('Initial query before filters:', [
                'base_query_sql' => $query->toSql(),
                'base_query_bindings' => $query->getBindings()
            ]);
            
            // Filter berdasarkan status siswa
            if ($studentStatus) {
                if ($studentStatus === 'aktif') {
                    $query->where('s.student_status', 1);
                } elseif ($studentStatus === 'tidak aktif') {
                    $query->where('s.student_status', 0);
                } else {
                    $query->where('s.student_status', $studentStatus);
                }
                Log::info('Applied student_status filter:', ['value' => $studentStatus, 'converted' => $studentStatus === 'aktif' ? 1 : ($studentStatus === 'tidak aktif' ? 0 : $studentStatus)]);
            } else {
                $query->where('s.student_status', 1); // Default hanya siswa aktif (status = 1)
                Log::info('Applied default student_status filter: 1 (aktif)');
            }
            
            // Filter berdasarkan periode jika dipilih
            if ($periodId !== 'all') {
                $query->where('p.period_period_id', $periodId);
                Log::info('Applied period_id filter:', ['value' => $periodId]);
            }
            
            // Filter berdasarkan POS jika dipilih
            if ($posId !== 'all') {
                $query->where('p.pos_pos_id', $posId);
                Log::info('Applied pos_id filter:', ['value' => $posId]);
            }
            
            // Filter berdasarkan bulan jika dipilih (hanya untuk tagihan bulanan)
            if ($monthId && ($billType === 'bulanan' || $billType === 'all')) {
                if ($billType === 'bulanan') {
                    // Filter bulanan: tampilkan tagihan dari bulan 1 sampai bulan yang dipilih
                    $query->where('b.month_month_id', '<=', $monthId);
                }
                // Untuk 'all', filter bulan akan diterapkan setelah query
                Log::info('Applied month_id filter (sampai bulan):', ['value' => $monthId, 'bill_type' => $billType]);
            }

            if ($classId) {
                $query->where('s.class_class_id', $classId);
                Log::info('Applied class_id filter:', ['value' => $classId]);
            }

            // Log query untuk debug
            Log::info('Bulk WhatsApp query parameters:', [
                'period_id' => $periodId,
                'pos_id' => $posId,
                'class_id' => $classId,
                'month_id' => $monthId,
                'student_status' => $studentStatus,
                'bill_type' => $billType
            ]);
            
            // Log raw SQL query untuk debugging
            $sql = $query->toSql();
            $bindings = $query->getBindings();
            Log::info('Bulk WhatsApp raw SQL after filters:', [
                'sql' => $sql,
                'bindings' => $bindings
            ]);
            
            // Query sudah memiliki select statement, tinggal tambahkan order by
            if ($billType === 'all') {
                // Untuk UNION query, order by harus dilakukan setelah query
                $bills = $query->get();
                // Sort secara manual setelah UNION
                $bills = $bills->sortBy([
                    ['class_name', 'asc'],
                    ['student_full_name', 'asc']
                ])->values();
            } else {
                // Untuk query biasa, bisa langsung order by
                $bills = $query->orderBy('c.class_name', 'asc')
                    ->orderBy('s.student_full_name', 'asc')
                    ->get();
            }
            
            // Filter bulan untuk kasus 'all' setelah query
            if ($billType === 'all' && $monthId) {
                $bills = $bills->filter(function($bill) use ($monthId) {
                    return $bill->bill_type === 'bebas' || $bill->month_month_id <= $monthId;
                });
                Log::info('Applied month filter after query for bill_type=all (sampai bulan):', ['month_id' => $monthId, 'filtered_count' => $bills->count()]);
            }
            
            // Optimasi: Jika 'all' + bulan spesifik, gunakan query yang lebih efisien
            if ($billType === 'all' && $monthId) {
                Log::info('Using optimized query for all + specific month');
                
                // Query yang dioptimalkan untuk kasus ini - menggunakan approach yang benar
                // Karena payment table tidak punya student_student_id, kita gunakan separate queries
                
                // Query bulanan dengan filter bulan langsung
                $bulananOptimized = DB::table('students as s')
                    ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                    ->leftJoin('bulan as b', 's.student_id', '=', 'b.student_student_id')
                    ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                    ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                    ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                    ->where('s.student_status', 1)
                    ->whereNotNull('b.bulan_id')
                    ->whereNull('b.bulan_date_pay')
                    ->where('b.month_month_id', '<=', $monthId)
                    ->select(
                        's.student_id',
                        's.student_nis',
                        's.student_full_name',
                        's.student_parent_phone',
                        'c.class_name',
                        'b.bulan_id as bill_id',
                        'b.month_month_id',
                        'b.bulan_bill as bill_amount',
                        'b.bulan_date_pay',
                        'b.bulan_status',
                        'pos.pos_name',
                        'per.period_start',
                        'per.period_end',
                        DB::raw("'bulanan' as bill_type"),
                        DB::raw("NULL as bebas_desc"),
                        DB::raw('CASE 
                            WHEN b.month_month_id = 1 THEN "Juli"
                            WHEN b.month_month_id = 2 THEN "Agustus"
                            WHEN b.month_month_id = 3 THEN "September"
                            WHEN b.month_month_id = 4 THEN "Oktober"
                            WHEN b.month_month_id = 5 THEN "November"
                            WHEN b.month_month_id = 6 THEN "Desember"
                            WHEN b.month_month_id = 7 THEN "Januari"
                            WHEN b.month_month_id = 8 THEN "Februari"
                            WHEN b.month_month_id = 9 THEN "Maret"
                            WHEN b.month_month_id = 10 THEN "April"
                            WHEN b.month_month_id = 11 THEN "Mei"
                            WHEN b.month_month_id = 12 THEN "Juni"
                            ELSE NULL
                        END as month_name')
                    );
                
                // Query bebas (tidak ada filter bulan)
                $bebasOptimized = DB::table('students as s')
                    ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                    ->leftJoin('bebas as be', 's.student_id', '=', 'be.student_student_id')
                    ->leftJoin('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                    ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                    ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                    ->where('s.student_status', 1)
                    ->whereNotNull('be.bebas_id')
                    ->whereRaw('(be.bebas_bill - COALESCE(be.bebas_total_pay, 0)) > 0')
                    ->select(
                        's.student_id',
                        's.student_nis',
                        's.student_full_name',
                        's.student_parent_phone',
                        'c.class_name',
                        'be.bebas_id as bill_id',
                        DB::raw("NULL as month_month_id"),
                        DB::raw('(be.bebas_bill - COALESCE(be.bebas_total_pay, 0)) as bill_amount'),
                        DB::raw("NULL as bulan_date_pay"),
                        DB::raw("NULL as bulan_status"),
                        'pos.pos_name',
                        'per.period_start',
                        'per.period_end',
                        DB::raw("'bebas' as bill_type"),
                        'be.bebas_desc',
                        DB::raw("NULL as month_name")
                    );
                
                // Apply other filters
                if ($periodId !== 'all') {
                    $bulananOptimized->where('p.period_period_id', $periodId);
                    $bebasOptimized->where('p.period_period_id', $periodId);
                }
                if ($posId !== 'all') {
                    $bulananOptimized->where('p.pos_pos_id', $posId);
                    $bebasOptimized->where('p.pos_pos_id', $posId);
                }
                if ($classId) {
                    $bulananOptimized->where('s.class_class_id', $classId);
                    $bebasOptimized->where('s.class_class_id', $classId);
                }
                if ($studentStatus) {
                    if ($studentStatus === 'aktif') {
                        $bulananOptimized->where('s.student_status', 1);
                        $bebasOptimized->where('s.student_status', 1);
                    } elseif ($studentStatus === 'tidak aktif') {
                        $bulananOptimized->where('s.student_status', 0);
                        $bebasOptimized->where('s.student_status', 0);
                    } else {
                        $bulananOptimized->where('s.student_status', $studentStatus);
                        $bebasOptimized->where('s.student_status', $studentStatus);
                    }
                }
                
                // Get optimized results and combine
                $bulananResult = $bulananOptimized->orderBy('c.class_name', 'asc')
                    ->orderBy('s.student_full_name', 'asc')
                    ->get();
                    
                $bebasResult = $bebasOptimized->orderBy('c.class_name', 'asc')
                    ->orderBy('s.student_full_name', 'asc')
                    ->get();
                
                $optimizedBills = $bulananResult->concat($bebasResult);
                
                Log::info('Optimized query result:', [
                    'total_bills' => $optimizedBills->count(),
                    'bulanan_count' => $bulananResult->count(),
                    'bebas_count' => $bebasResult->count(),
                    'month_filter' => $monthId
                ]);
                
                // Replace original results with optimized ones
                $bills = $optimizedBills;
            }
            
            // Log breakdown untuk kasus 'all'
            if ($billType === 'all') {
                $bulananCount = $bills->where('bill_type', 'bulanan')->count();
                $bebasCount = $bills->where('bill_type', 'bebas')->count();
                Log::info('Bill type breakdown for all:', [
                    'total' => $bills->count(),
                    'bulanan' => $bulananCount,
                    'bebas' => $bebasCount
                ]);
            }
            
            // Log hasil query
            Log::info('Bulk WhatsApp query result:', [
                'total_bills' => $bills->count(),
                'sample_bills' => $bills->take(3)->toArray()
            ]);
            
            // Log execution time
            $executionTime = microtime(true) - $startTime;
            Log::info('Bulk WhatsApp execution time:', [
                'execution_time_seconds' => round($executionTime, 3),
                'bill_type' => $billType,
                'month_filter' => $monthId,
                'total_bills' => $bills->count()
            ]);

            // Log sample data untuk debugging
            if ($bills->count() > 0) {
                $sampleBill = $bills->first();
                Log::info('Sample bill data:', [
                    'student_id' => $sampleBill->student_id,
                    'student_nis' => $sampleBill->student_nis,
                    'student_full_name' => $sampleBill->student_full_name,
                    'class_name' => $sampleBill->class_name,
                    'bill_id' => $sampleBill->bill_id,
                    'bill_type' => $sampleBill->bill_type,
                    'pos_name' => $sampleBill->pos_name
                ]);
            }

            // Group data per siswa
            $groupedBills = $bills->groupBy('student_id')->map(function($studentBills, $studentId) {
                $firstBill = $studentBills->first();
                $totalAmount = $studentBills->sum('bill_amount');
                $unpaidBills = $studentBills->filter(function($bill) {
                    if ($bill->bill_type === 'bebas') {
                        return $bill->bill_amount > 0; // Bebas: belum lunas jika sisa > 0
                    } else {
                        return is_null($bill->bulan_date_pay); // Bulanan: belum lunas jika tidak ada tanggal pembayaran
                    }
                });
                
                // Ambil daftar POS yang unik
                $posList = $studentBills->pluck('pos_name')->unique()->implode(', ');
                
                // Detail tagihan untuk setiap siswa
                $billDetails = $studentBills->map(function($bill) {
                    $monthNames = [
                        1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
                        5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
                        9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                    ];
                    
                    if ($bill->bill_type === 'bebas') {
                        $detail = $bill->bebas_desc ?? 'Tagihan Bebas';
                    } else {
                        $detail = $monthNames[$bill->month_month_id] ?? 'Unknown';
                    }
                    
                    $isPaid = $bill->bill_type === 'bebas' ? 
                        ($bill->bill_amount <= 0) : 
                        !is_null($bill->bulan_date_pay);
                    
                    return [
                        'bill_id' => $bill->bill_id,
                        'bill_type' => $bill->bill_type,
                        'pos_name' => $bill->pos_name,
                        'detail' => $detail,
                        'amount' => $bill->bill_amount,
                        'is_paid' => $isPaid,
                        'period' => $bill->period_start . '/' . $bill->period_end
                    ];
                });
                
                return [
                    'student_id' => $studentId,
                    'nis' => $firstBill->student_nis,
                    'nama' => $firstBill->student_full_name,
                    'parent_phone' => $firstBill->student_parent_phone,
                    'kelas' => $firstBill->class_name,
                    'pos_list' => $posList,
                    'total_amount' => $totalAmount,
                    'unpaid_amount' => $unpaidBills->sum('bill_amount'),
                    'total_bills' => $studentBills->count(),
                    'unpaid_bills' => $unpaidBills->count(),
                    'has_phone' => !empty($firstBill->student_parent_phone),
                    'bill_details' => $billDetails
                ];
            })->values();
            
            // Format data untuk response (grouped)
            $formattedBills = $groupedBills;

            // Hitung statistik berdasarkan data yang di-grouping
            $stats = [
                'total_students' => $formattedBills->count(),
                'total_bills' => $formattedBills->sum('total_bills'),
                'unpaid_bills' => $formattedBills->sum('unpaid_bills'),
                'with_phone' => $formattedBills->where('has_phone', true)->count(),
                'without_phone' => $formattedBills->where('has_phone', false)->count(),
                'total_amount' => $formattedBills->sum('total_amount'),
                'unpaid_amount' => $formattedBills->sum('unpaid_amount')
            ];

            // Log response yang akan dikirim
            Log::info('Bulk WhatsApp response:', [
                'success' => true,
                'total_bills' => $formattedBills->count(),
                'stats' => $stats,
                'sample_formatted_bill' => $formattedBills->first()
            ]);

            Log::info('Bulk WhatsApp getBills completed successfully', [
                'total_students' => count($formattedBills),
                'total_bills' => $stats['total_bills'] ?? 0,
                'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);

            return response()->json([
                'success' => true,
                'bills' => $formattedBills,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting bills: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data tagihan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim tagihan masal via WhatsApp
     */
    public function sendBulkBills(Request $request)
    {
        try {
            // Filter berdasarkan sekolah yang sedang aktif
            $currentSchoolId = currentSchoolId();
            
            if (!$currentSchoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.'
                ], 403);
            }
            
            $request->validate([
                'bills' => 'required|array',
                'bills.*.student_id' => 'required|exists:students,student_id',
                'bills.*.bill_id' => 'required',
                'bills.*.bill_type' => 'required|in:bulanan,bebas',
                'message_template' => 'nullable|string|max:1000'
            ]);

            $bills = $request->bills;
            $messageTemplate = $request->message_template ?? '';
            $whatsappService = new WhatsAppService();
            
            $results = [];
            $successCount = 0;
            $failedCount = 0;

            foreach ($bills as $bill) {
                try {
                    // Ambil detail tagihan berdasarkan jenis
                    if ($bill['bill_type'] === 'bebas') {
                        // Query untuk tagihan bebas
                        $billDetail = DB::table('bebas as be')
                            ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                            ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                            ->join('periods as per', 'p.period_period_id', '=', 'per.period_id')
                            ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                            ->where('be.bebas_id', $bill['bill_id'])
                            ->select(
                                's.student_full_name',
                                's.student_parent_phone',
                                DB::raw('(be.bebas_bill - COALESCE(be.bebas_total_pay, 0)) as bill_amount'),
                                DB::raw("NULL as month_month_id"),
                                'pos.pos_name',
                                'per.period_start',
                                'per.period_end',
                                DB::raw("'bebas' as bill_type"),
                                'be.bebas_desc'
                            )
                            ->first();
                    } else {
                        // Query untuk tagihan bulanan
                        $billDetail = DB::table('bulan as b')
                            ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                            ->join('periods as per', 'p.period_period_id', '=', 'per.period_id')
                            ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                            ->where('b.bulan_id', $bill['bill_id'])
                            ->select(
                                's.student_full_name',
                                's.student_parent_phone',
                                'b.bulan_bill as bill_amount',
                                'b.month_month_id',
                                'pos.pos_name',
                                'per.period_start',
                                'per.period_end',
                                DB::raw("'bulanan' as bill_type"),
                                DB::raw("NULL as bebas_desc")
                            )
                            ->first();
                    }

                    if (!$billDetail || !$billDetail->student_parent_phone) {
                        $results[] = [
                            'student_id' => $bill['student_id'],
                            'bill_id' => $bill['bill_id'],
                            'bill_type' => $bill['bill_type'],
                            'status' => 'failed',
                            'message' => 'Data tagihan tidak ditemukan atau nomor telepon kosong'
                        ];
                        $failedCount++;
                        continue;
                    }

                    // Buat pesan tagihan
                    $monthNames = [
                        1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
                        5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari', 
                        9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                    ];

                    if ($billDetail->bill_type === 'bebas') {
                        $monthName = $billDetail->bebas_desc ?? 'Tagihan Bebas';
                    } else {
                        $monthName = $monthNames[$billDetail->month_month_id] ?? 'Unknown';
                    }
                    
                    $period = $billDetail->period_start . '/' . $billDetail->period_end;
                    $amount = number_format($billDetail->bill_amount, 0, ',', '.');

                    $message = $this->createBillMessage(
                        $billDetail->student_full_name,
                        $billDetail->pos_name,
                        $monthName,
                        $period,
                        $amount,
                        $messageTemplate
                    );

                    // Kirim pesan WhatsApp
                    $result = $whatsappService->sendCustomMessage(
                        $billDetail->student_parent_phone,
                        $message
                    );

                    if ($result) {
                        $results[] = [
                            'student_id' => $bill['student_id'],
                            'bill_id' => $bill['bill_id'],
                            'bill_type' => $bill['bill_type'],
                            'status' => 'success',
                            'message' => 'Tagihan berhasil dikirim'
                        ];
                        $successCount++;

                        // Log pengiriman
                        Log::info("Bulk WhatsApp bill sent successfully", [
                            'student_id' => $bill['student_id'],
                            'bill_id' => $bill['bill_id'],
                            'bill_type' => $bill['bill_type'],
                            'phone' => $billDetail->student_parent_phone,
                            'amount' => $amount
                        ]);
                    } else {
                        $results[] = [
                            'student_id' => $bill['student_id'],
                            'bill_id' => $bill['bill_id'],
                            'bill_type' => $bill['bill_type'],
                            'status' => 'failed',
                            'message' => 'Gagal mengirim pesan WhatsApp'
                        ];
                        $failedCount++;
                    }

                } catch (\Exception $e) {
                    Log::error("Error sending bill to student {$bill['student_id']}: " . $e->getMessage());
                    
                    $results[] = [
                        'student_id' => $bill['student_id'],
                        'bill_id' => $bill['bill_id'],
                        'bill_type' => $bill['bill_type'],
                        'status' => 'failed',
                        'message' => 'Error: ' . $e->getMessage()
                    ];
                    $failedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Pengiriman tagihan selesai. Berhasil: {$successCount}, Gagal: {$failedCount}",
                'results' => $results,
                'summary' => [
                    'total' => count($bills),
                    'success' => $successCount,
                    'failed' => $failedCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending bulk bills: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim tagihan masal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim pesan konsolidasi tagihan per siswa (1 nomor = 1 pesan dengan semua tagihan)
     */
    public function sendConsolidatedBills(Request $request)
    {
        try {
            // Filter berdasarkan sekolah yang sedang aktif
            $currentSchoolId = currentSchoolId();
            
            if (!$currentSchoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.'
                ], 403);
            }
            
            $request->validate([
                'bills' => 'required|array',
                'bills.*.student_id' => 'required|exists:students,student_id',
                'bills.*.bill_id' => 'required',
                'bills.*.bill_type' => 'required|in:bulanan,bebas',
                'message_template' => 'nullable|string|max:1000'
            ]);

            $bills = $request->bills;
            $messageTemplate = $request->message_template ?? '';
            $whatsappService = new WhatsAppService();
            
            // Group bills by student
            $studentBills = collect($bills)->groupBy('student_id');
            $results = [];
            $successCount = 0;
            $failedCount = 0;

            foreach ($studentBills as $studentId => $studentBillList) {
                try {
                    // Get student info
                    $student = DB::table('students as s')
                        ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                        ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                        ->where('s.student_id', $studentId)
                        ->select('s.student_full_name', 's.student_parent_phone', 'c.class_name')
                        ->first();

                    if (!$student || !$student->student_parent_phone) {
                        $results[] = [
                            'student_id' => $studentId,
                            'status' => 'failed',
                            'message' => 'Data siswa tidak ditemukan atau nomor telepon kosong'
                        ];
                        $failedCount++;
                        continue;
                    }

                    // Get all bills for this student
                    $studentBillDetails = [];
                    $totalAmount = 0;

                    foreach ($studentBillList as $bill) {
                        if ($bill['bill_type'] === 'bebas') {
                            $billDetail = DB::table('bebas as be')
                                ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                                ->join('periods as per', 'p.period_period_id', '=', 'per.period_id')
                                ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                                ->where('be.bebas_id', $bill['bill_id'])
                                ->select(
                                    'be.bebas_desc',
                                    DB::raw('(be.bebas_bill - COALESCE(be.bebas_total_pay, 0)) as bill_amount'),
                                    'pos.pos_name',
                                    'per.period_start',
                                    'per.period_end'
                                )
                                ->first();

                            if ($billDetail) {
                                $amount = $billDetail->bill_amount;
                                $totalAmount += $amount;
                                $studentBillDetails[] = [
                                    'type' => 'bebas',
                                    'description' => $billDetail->bebas_desc,
                                    'amount' => $amount,
                                    'pos_name' => $billDetail->pos_name,
                                    'period' => $billDetail->period_start . '/' . $billDetail->period_end
                                ];
                            }
                        } else {
                            $billDetail = DB::table('bulan as b')
                                ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                                ->join('periods as per', 'p.period_period_id', '=', 'per.period_id')
                                ->where('s.school_id', $currentSchoolId) // Filter berdasarkan school_id
                                ->where('b.bulan_id', $bill['bill_id'])
                                ->select(
                                    'b.month_month_id',
                                    'b.bulan_bill as bill_amount',
                                    'pos.pos_name',
                                    'per.period_start',
                                    'per.period_end'
                                )
                                ->first();

                            if ($billDetail) {
                                $monthNames = [
                                    1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
                                    5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari', 
                                    9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                                ];
                                $monthName = $monthNames[$billDetail->month_month_id] ?? 'Unknown';
                                $amount = $billDetail->bill_amount;
                                $totalAmount += $amount;
                                $studentBillDetails[] = [
                                    'type' => 'bulanan',
                                    'description' => $monthName,
                                    'amount' => $amount,
                                    'pos_name' => $billDetail->pos_name,
                                    'period' => $billDetail->period_start . '/' . $billDetail->period_end
                                ];
                            }
                        }
                    }

                    if (empty($studentBillDetails)) {
                        $results[] = [
                            'student_id' => $studentId,
                            'status' => 'failed',
                            'message' => 'Tidak ada detail tagihan yang valid'
                        ];
                        $failedCount++;
                        continue;
                    }

                    // Create consolidated message
                    $message = $this->createConsolidatedMessage(
                        $student->student_full_name,
                        $student->class_name,
                        $studentBillDetails,
                        $totalAmount,
                        $messageTemplate
                    );

                    // Send WhatsApp message
                    $result = $whatsappService->sendCustomMessage(
                        $student->student_parent_phone,
                        $message
                    );

                    if ($result) {
                        $results[] = [
                            'student_id' => $studentId,
                            'status' => 'success',
                            'message' => 'Pesan konsolidasi berhasil dikirim',
                            'total_bills' => count($studentBillDetails),
                            'total_amount' => $totalAmount
                        ];
                        $successCount++;

                        // Log pengiriman
                        Log::info("Consolidated WhatsApp message sent successfully", [
                            'student_id' => $studentId,
                            'phone' => $student->student_parent_phone,
                            'total_bills' => count($studentBillDetails),
                            'total_amount' => $totalAmount
                        ]);
                    } else {
                        $results[] = [
                            'student_id' => $studentId,
                            'status' => 'failed',
                            'message' => 'Gagal mengirim pesan WhatsApp'
                        ];
                        $failedCount++;
                    }

                } catch (\Exception $e) {
                    Log::error("Error sending consolidated message to student {$studentId}: " . $e->getMessage());
                    
                    $results[] = [
                        'student_id' => $studentId,
                        'status' => 'failed',
                        'message' => 'Error: ' . $e->getMessage()
                    ];
                    $failedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Pengiriman pesan konsolidasi selesai. Berhasil: {$successCount}, Gagal: {$failedCount}",
                'results' => $results,
                'summary' => [
                    'total_students' => $studentBills->count(),
                    'success' => $successCount,
                    'failed' => $failedCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending consolidated bills: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan konsolidasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim pesan massal via WhatsApp
     */
    public function sendMassMessage(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|integer',
                'message' => 'required|string|max:1000'
            ]);

            $studentId = $request->student_id;
            $message = $request->message;

            // Ambil data siswa
            $studentData = DB::table('students as s')
                ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                ->select(
                    's.student_id',
                    's.student_full_name',
                    's.student_phone',
                    'c.class_name'
                )
                ->where('s.student_id', $studentId)
                ->first();

            if (!$studentData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan'
                ], 404);
            }

            // Cek apakah siswa memiliki nomor HP
            if (empty($studentData->student_phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak memiliki nomor HP'
                ], 400);
            }

            // Kirim pesan via WhatsApp
            $whatsappService = new WhatsAppService();
            $response = $whatsappService->sendCustomMessage($studentData->student_phone, $message);

            if ($response) {
                // Log pengiriman
                Log::info('WhatsApp pesan massal berhasil dikirim', [
                    'student_id' => $studentId,
                    'phone' => $studentData->student_phone,
                    'message' => $message
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pesan berhasil dikirim',
                    'student_name' => $studentData->student_full_name
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim WhatsApp'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error in sendMassMessage: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buat pesan tagihan
     */
    private function createBillMessage($studentName, $posName, $monthName, $period, $amount, $customMessage = '')
    {
        $message = "📋 *TAGIHAN SPP*\n\n";
        $message .= "Halo Bapak/Ibu orang tua dari *{$studentName}*\n\n";
        $message .= "📚 *Detail Tagihan:*\n";
        $message .= "• POS: {$posName}\n";
        $message .= "• Bulan: {$monthName}\n";
        $message .= "• Tahun Ajaran: {$period}\n";
        $message .= "• Nominal: Rp {$amount}\n\n";

        if (!empty($customMessage)) {
            $message .= "💬 *Pesan Tambahan:*\n{$customMessage}\n\n";
        }

        $message .= "💰 *Cara Pembayaran:*\n";
        $message .= "1. Pembayaran Tunai di Sekolah\n";
        $message .= "2. Pembayaran via Tabungan\n";
        $message .= "3. Transfer Bank (sesuai rekening sekolah)\n\n";
        
        $message .= "⏰ *Batas Waktu:*\n";
        $message .= "Harap lunasi tagihan sebelum akhir bulan\n\n";
        
        $message .= "📞 *Hubungi:*\n";
        $message .= "Admin Keuangan untuk informasi lebih lanjut\n\n";
        
        $message .= "Terima kasih atas perhatiannya 🙏";

        return $message;
    }

    /**
     * Buat pesan konsolidasi tagihan
     */
    private function createConsolidatedMessage($studentName, $className, $billDetails, $totalAmount, $customMessage = '')
    {
        $monthNames = [
            1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
            5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari', 
            9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
        ];

        $message = "🏫 *INFORMASI TAGIHAN SEKOLAH*\n\n";
        $message .= "👤 *Nama Siswa:* {$studentName}\n";
        $message .= "🏫 *Kelas:* {$className}\n";
        $message .= "📅 *Tanggal:* " . date('d/m/Y') . "\n\n";
        $message .= "📋 *DETAIL TAGIHAN:*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        // Group bills by POS
        $billsByPos = collect($billDetails)->groupBy('pos_name');
        
        foreach ($billsByPos as $posName => $posBills) {
            $message .= "📍 *{$posName}*\n";
            foreach ($posBills as $bill) {
                $amount = number_format($bill['amount'], 0, ',', '.');
                if ($bill['type'] === 'bulanan') {
                    $message .= "   • {$bill['description']} ({$bill['period']}): Rp {$amount}\n";
                } else {
                    $message .= "   • {$bill['description']} ({$bill['period']}): Rp {$amount}\n";
                }
            }
            $message .= "\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "💰 *TOTAL TAGIHAN: Rp " . number_format($totalAmount, 0, ',', '.') . "*\n\n";

        if (!empty($customMessage)) {
            $message .= "💬 *Pesan Tambahan:*\n";
            $message .= "{$customMessage}\n\n";
        }

        $message .= "📞 *Untuk informasi lebih lanjut, silakan hubungi admin sekolah*\n";
        $message .= "🙏 *Terima kasih atas perhatiannya*";

        return $message;
    }
}
