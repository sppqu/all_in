<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Student;
use App\Models\Payment;
use App\Models\Pos;
use App\Models\Period;
use App\Models\SetupGateway;
use App\Services\WhatsAppService;
use App\Services\NotificationService;
use App\Services\IpaymuService;

class OnlinePaymentController extends Controller
{
    /**
     * Menampilkan halaman utama pembayaran online
     */
    public function index()
    {
        // Get current school_id from session
        $currentSchoolId = session('current_school_id');
        
        if (!$currentSchoolId) {
            return redirect()->route('manage.general.setting')
                ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
        }

        // Filter active period by school_id
        $activePeriod = Period::where('period_status', 1)
            ->where('school_id', $currentSchoolId)
            ->first();
        
        // Filter payments by school_id
        $payments = Payment::with(['pos', 'period'])
            ->where('school_id', $currentSchoolId)
            ->get();
        
        // Statistik pembayaran online - filter by school_id
        $totalPayments = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->count();
        
        $successPayments = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('t.status', 1)
            ->count();
        
        $pendingPayments = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('t.status', 0)
            ->count();
        
        $failedPayments = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('t.status', 2)
            ->count();
        
        return view('online-payment.index', compact(
            'activePeriod', 
            'payments', 
            'totalPayments', 
            'successPayments', 
            'pendingPayments', 
            'failedPayments'
        ));
    }

    /**
     * Menampilkan form pencarian siswa untuk pembayaran online
     */
    public function searchStudent()
    {
        return view('online-payment.search-student');
    }

    /**
     * API untuk mencari siswa berdasarkan NIS atau nama
     */
    public function findStudent(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'filter' => 'nullable|string|in:all,active,inactive,graduated',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $search = $request->search ?? '';
        $filter = $request->filter ?? 'all';
        $perPage = $request->per_page ?? 10;
        
        $query = Student::with(['class']);

        // Apply search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('student_nis', 'LIKE', "%{$search}%")
                  ->orWhere('student_full_name', 'LIKE', "%{$search}%");
            });
        }

        // Apply transaction status filter
        switch ($filter) {
            case 'pending':
                $query->whereHas('transfers', function($q) {
                    $q->where('status', 0);
                });
                break;
            case 'success':
                $query->whereHas('transfers', function($q) {
                    $q->where('status', 1);
                });
                break;
            case 'failed':
                $query->whereHas('transfers', function($q) {
                    $q->where('status', 2);
                });
                break;
            default:
                // 'all' - no additional filter
                break;
        }

        $students = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'students' => $students->items(),
            'total' => $students->total(),
            'current_page' => $students->currentPage(),
            'per_page' => $students->perPage(),
            'last_page' => $students->lastPage()
        ]);
    }

    /**
     * Menampilkan detail tagihan siswa
     */
    public function studentBills($studentId)
    {
        $student = Student::with(['class'])->findOrFail($studentId);
        
        // Ambil tagihan bulanan - dengan DISTINCT untuk menghindari duplikasi
        $bulananBills = DB::table('bulan as b')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('b.student_student_id', $studentId)
            ->select(
                'b.bulan_id',
                'b.student_student_id',
                'b.payment_payment_id',
                'b.month_month_id',
                'b.bulan_bill',
                'b.bulan_status',
                'b.bulan_date_pay',
                'b.bulan_number_pay',
                'b.bulan_last_update',
                'pos.pos_name',
                'per.period_start',
                'per.period_end'
            )
            ->distinct()
            ->get();

        // Ambil tagihan bebas - dengan DISTINCT untuk menghindari duplikasi
        $bebasBills = DB::table('bebas as be')
            ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('be.student_student_id', $studentId)
            ->select(
                'be.bebas_id',
                'be.student_student_id',
                'be.payment_payment_id',
                'be.bebas_bill',
                'be.bebas_total_pay',
                'be.bebas_desc',
                'be.bebas_date_pay',
                'be.bebas_number_pay',
                'be.bebas_last_update',
                'pos.pos_name',
                'per.period_start',
                'per.period_end'
            )
            ->distinct()
            ->get();

        return view('online-payment.student-bills', compact('student', 'bulananBills', 'bebasBills'));
    }

    /**
     * Menampilkan form pembayaran
     */
    public function paymentForm($studentId, $billType, $billId)
    {
        $student = Student::with(['class'])->findOrFail($studentId);
        
        if ($billType === 'bulanan') {
            $bill = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('b.bulan_id', $billId)
                ->where('b.student_student_id', $studentId)
                ->first();
        } else {
            $bill = DB::table('bebas as be')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('be.bebas_id', $billId)
                ->where('be.student_student_id', $studentId)
                ->first();
        }

        if (!$bill) {
            return redirect()->back()->with('error', 'Tagihan tidak ditemukan');
        }

        return view('online-payment.payment-form', compact('student', 'bill', 'billType'));
    }

    /**
     * Memproses pembayaran online via Tripay
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'bill_type' => 'required|in:bulanan,bebas',
            'bill_id' => 'required|integer',
            'payment_type' => 'required|in:realtime,manual',
            'amount' => 'required|numeric|min:1',
            // Conditional validation
            'payment_method' => 'required_if:payment_type,realtime|string',
            'manual_bank_name' => 'required_if:payment_type,manual|string',
            'manual_account_number' => 'required_if:payment_type,manual|string',
            'manual_account_name' => 'required_if:payment_type,manual|string',
            'manual_proof_file' => 'required_if:payment_type,manual|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'manual_notes' => 'nullable|string',
            'bypass_period_validation' => 'nullable|boolean', // Opsi untuk melewati validasi periode
        ]);

        try {
            DB::beginTransaction();

            // Get student data
            $student = Student::findOrFail($request->student_id);

            // Validate payment amount against bill remaining amount
            $validationResult = $this->validatePaymentAmount($request->bill_type, $request->bill_id, $student->student_id, $request->amount);
            if (!$validationResult['valid']) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => $validationResult['message']
                ], 400);
            }

            // Validasi periode (opsional - bisa dilewati)
            if (!$request->bypass_period_validation) {
                $periodValidationResult = $this->validatePeriodSequence($request->bill_type, $request->bill_id, $student->student_id);
                if (!$periodValidationResult['valid']) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => $periodValidationResult['message'],
                        'can_bypass' => true, // Memberitahu frontend bahwa validasi bisa dilewati
                        'bypass_message' => 'Jika Anda yakin ingin melanjutkan pembayaran ini, silakan centang opsi "Lewati Validasi Periode" dan coba lagi.'
                    ], 400);
                }
            }

            if ($request->payment_type === 'realtime') {
                // Real-time payment via iPaymu
                return $this->processIpaymuPayment($request, $student);
            } else {
                // Manual payment processing
                return $this->processManualPayment($request, $student);
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment processing error', [
                'message' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process iPaymu payment
     */
    private function processIpaymuPayment(Request $request, $student)
    {
        try {
            // Initialize iPaymu service
            $ipaymuService = new IpaymuService();

            // Generate reference ID
            $referenceId = 'BULANAN-' . $student->student_id . '-' . $request->bill_id . '-' . time();
            
            if ($request->bill_type === 'bebas') {
                $referenceId = 'BEBAS-' . $student->student_id . '-' . $request->bill_id . '-' . time();
            }

            Log::info('🔵 Processing iPaymu payment', [
                'reference_id' => $referenceId,
                'student_id' => $student->student_id,
                'bill_type' => $request->bill_type,
                'bill_id' => $request->bill_id,
                'amount' => $request->amount
            ]);

            // Get bill details
            $billDetails = $this->getBillDetails($request->bill_type, $request->bill_id, $student->student_id);
            
            if (!$billDetails) {
                throw new \Exception('Tagihan tidak ditemukan');
            }

            // Prepare product data
            $product = [
                $billDetails['name']
            ];

            $qty = [1];
            $price = [(int) $request->amount];

            // Create iPaymu payment
            $ipaymuResponse = $ipaymuService->createPayment(
                $referenceId,
                $billDetails['name'],
                (int) $request->amount,
                $student->student_full_name,
                $student->student_parent_phone ?? '08123456789',
                'student@sppqu.com',
                $product,
                $qty,
                $price,
                route('ipaymu.callback'),
                route('student.payment.history')
            );

            // Handle error jika gateway tidak aktif
            if (!$ipaymuResponse['success']) {
                if (isset($ipaymuResponse['error_code']) && $ipaymuResponse['error_code'] === 'GATEWAY_INACTIVE') {
                    return response()->json([
                        'success' => false,
                        'message' => $ipaymuResponse['message'] ?? 'Metode pembayaran yang Anda pilih sedang offline atau tidak aktif. Silakan pilih metode pembayaran lain.'
                    ], 400);
                }
                throw new \Exception($ipaymuResponse['message'] ?? 'Gagal membuat transaksi pembayaran');
            }

            Log::info('🔵 iPaymu API Response', [
                'success' => $ipaymuResponse['success'] ?? false,
                'data' => $ipaymuResponse['data'] ?? null
            ]);

            if (!$ipaymuResponse['success']) {
                throw new \Exception($ipaymuResponse['message'] ?? 'Gagal membuat transaksi pembayaran');
            }

            // Insert to transfer table
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $student->student_id,
                'detail' => $billDetails['name'],
                'status' => 0, // Pending
                'confirm_pay' => $request->amount,
                'reference' => $referenceId,
                'merchantRef' => $referenceId,
                'gateway_transaction_id' => $ipaymuResponse['data']['transaction_id'] ?? null,
                'payment_number' => $referenceId,
                'payment_method' => 'ipaymu',
                'bill_type' => $request->bill_type,
                'bill_id' => $request->bill_id,
                'payment_details' => json_encode($ipaymuResponse['data'] ?? []),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert to transfer_detail table
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => $request->bill_type === 'bulanan' ? 1 : 2,
                'bulan_id' => $request->bill_type === 'bulanan' ? $request->bill_id : null,
                'bebas_id' => $request->bill_type === 'bebas' ? $request->bill_id : null,
                'desc' => $billDetails['name'],
                'subtotal' => $request->amount
            ]);

            DB::commit();

            Log::info('✅ Transfer record created successfully', [
                'transfer_id' => $transferId,
                'reference_id' => $referenceId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'payment_number' => $referenceId,
                'payment_url' => $ipaymuResponse['data']['payment_url'] ?? null,
                'redirect_url' => $ipaymuResponse['data']['payment_url'] ?? null
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('❌ iPaymu payment error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process manual payment
     */
    private function processManualPayment(Request $request, $student)
    {
        try {
            // Generate payment number for Transfer Bank
            $paymentNumber = 'TF-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Handle file upload
            $filePath = null;
            if ($request->hasFile('manual_proof_file')) {
                $file = $request->file('manual_proof_file');
                // Ensure we get the correct extension without duplication
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = 'payment_proof_' . $paymentNumber . '.' . $extension;
                $filePath = $file->storeAs('payment_proofs', $fileName, 'public');
            }

            // Insert to transfer table
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $student->student_id,
                'detail' => 'Pembayaran Manual',
                'status' => 0, // Pending
                'confirm_name' => $request->manual_account_name,
                'confirm_bank' => $request->manual_bank_name,
                'confirm_accnum' => $request->manual_account_number,
                'confirm_photo' => $filePath,
                'confirm_pay' => $request->amount,
                'reference' => $paymentNumber,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert to transfer_detail table
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => $request->bill_type === 'bulanan' ? 1 : 2,
                'bulan_id' => $request->bill_type === 'bulanan' ? $request->bill_id : null,
                'bebas_id' => $request->bill_type === 'bebas' ? $request->bill_id : null,
                'desc' => 'Pembayaran Manual',
                'subtotal' => $request->amount
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran manual berhasil diajukan',
                'payment_number' => $paymentNumber
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Validate period sequence for online payments
     */
    private function validatePeriodSequence($billType, $billId, $studentId)
    {
        try {
            if ($billType === 'bulanan') {
                // Get current payment period
                $currentBill = DB::table('bulan as b')
                    ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                    ->where('b.bulan_id', $billId)
                    ->where('b.student_student_id', $studentId)
                    ->select('p.period_period_id', 'b.month_month_id', 'p.payment_id')
                    ->first();

                if (!$currentBill) {
                    return [
                        'valid' => false,
                        'message' => 'Data tagihan tidak ditemukan'
                    ];
                }

                // Cek apakah ada period sebelumnya yang belum lunas
                $previousUnpaidPeriods = DB::table('bulan as b')
                    ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                    ->where('b.student_student_id', $studentId)
                    ->where('p.period_period_id', '<', $currentBill->period_period_id)
                    ->whereNull('b.bulan_date_pay')
                    ->where('b.bulan_bill', '>', 0)
                    ->orderBy('p.period_period_id', 'asc')
                    ->first();

                if ($previousUnpaidPeriods) {
                    $periodInfo = DB::table('periods')
                        ->where('period_id', $previousUnpaidPeriods->period_period_id)
                        ->first();

                    $periodName = $periodInfo ? ($periodInfo->period_start . '/' . $periodInfo->period_end) : 'Periode Sebelumnya';
                    
                    return [
                        'valid' => false,
                        'message' => "⚠️ Validasi Periode\n\nMasih ada pembayaran yang belum diselesaikan di tahun ajaran $periodName.\n\nSistem menganjurkan untuk menyelesaikan pembayaran di periode sebelumnya terlebih dahulu untuk menjaga urutan pembayaran yang benar."
                    ];
                }

                // Cek urutan bulan dalam periode yang sama
                $previousUnpaidMonths = DB::table('bulan')
                    ->where('student_student_id', $studentId)
                    ->where('payment_payment_id', $currentBill->payment_id)
                    ->where('month_month_id', '<', $currentBill->month_month_id)
                    ->whereNull('bulan_date_pay')
                    ->where('bulan_bill', '>', 0)
                    ->orderBy('month_month_id', 'asc')
                    ->get();

                if ($previousUnpaidMonths->count() > 0) {
                    $monthNames = [
                        1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
                        5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
                        9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                    ];
                    
                    $unpaidMonths = $previousUnpaidMonths->pluck('month_month_id')->map(function($monthId) use ($monthNames) {
                        return $monthNames[$monthId] ?? 'Bulan ' . $monthId;
                    })->join(', ');
                    
                    return [
                        'valid' => false,
                        'message' => "⚠️ Validasi Urutan Bulan\n\nMasih ada pembayaran yang belum diselesaikan untuk bulan: $unpaidMonths.\n\nSistem menganjurkan untuk menyelesaikan pembayaran bulan sebelumnya terlebih dahulu."
                    ];
                }

            } else {
                // Validasi untuk pembayaran bebas
                $currentBill = DB::table('bebas as be')
                    ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                    ->where('be.bebas_id', $billId)
                    ->where('be.student_student_id', $studentId)
                    ->select('p.period_period_id', 'p.payment_id')
                    ->first();

                if (!$currentBill) {
                    return [
                        'valid' => false,
                        'message' => 'Data tagihan tidak ditemukan'
                    ];
                }

                // Cek apakah ada period sebelumnya yang belum lunas untuk pembayaran bebas
                $previousUnpaidBebasPeriods = DB::table('bebas as b')
                    ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                    ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->where('b.student_student_id', $studentId)
                    ->where('p.period_period_id', '<', $currentBill->period_period_id)
                    ->whereRaw('(b.bebas_bill - b.bebas_total_pay) > 0')
                    ->orderBy('p.period_period_id', 'asc')
                    ->first();

                if ($previousUnpaidBebasPeriods) {
                    $periodInfo = DB::table('periods')
                        ->where('period_id', $previousUnpaidBebasPeriods->period_period_id)
                        ->first();

                    $periodName = $periodInfo ? ($periodInfo->period_start . '/' . $periodInfo->period_end) : 'Periode Sebelumnya';
                    
                    return [
                        'valid' => false,
                        'message' => "⚠️ Validasi Periode Pembayaran Bebas\n\nMasih ada pembayaran bebas yang belum diselesaikan di tahun ajaran $periodName.\n\nSistem menganjurkan untuk menyelesaikan pembayaran di periode sebelumnya terlebih dahulu."
                    ];
                }
            }

            return [
                'valid' => true,
                'message' => 'Validasi periode berhasil'
            ];

        } catch (\Exception $e) {
            Log::error('Error validating period sequence', [
                'bill_type' => $billType,
                'bill_id' => $billId,
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'message' => 'Terjadi kesalahan saat validasi periode'
            ];
        }
    }

    /**
     * Validate payment amount against bill remaining amount
     */
    private function validatePaymentAmount($billType, $billId, $studentId, $amount)
    {
        try {
            if ($billType === 'bulanan') {
                // Get bulan bill details
                $bill = DB::table('bulan as b')
                    ->where('b.bulan_id', $billId)
                    ->where('b.student_student_id', $studentId)
                    ->whereNull('b.bulan_date_pay') // Only unpaid bills
                    ->select('b.bulan_bill')
                    ->first();

                if (!$bill) {
                    return [
                        'valid' => false,
                        'message' => 'Tagihan bulanan tidak ditemukan atau sudah lunas'
                    ];
                }

                $totalBill = $bill->bulan_bill;
                $remainingAmount = $totalBill;

                if ($amount > $remainingAmount) {
                    return [
                        'valid' => false,
                        'message' => "Jumlah pembayaran (Rp " . number_format($amount, 0, ',', '.') . ") melebihi sisa tagihan (Rp " . number_format($remainingAmount, 0, ',', '.') . ")"
                    ];
                }

            } else {
                // Get bebas bill details
                $bill = DB::table('bebas as be')
                    ->where('be.bebas_id', $billId)
                    ->where('be.student_student_id', $studentId)
                    ->whereNull('be.bebas_date_pay') // Only unpaid bills
                    ->select('be.bebas_bill', 'be.bebas_total_pay')
                    ->first();

                if (!$bill) {
                    return [
                        'valid' => false,
                        'message' => 'Tagihan bebas tidak ditemukan atau sudah lunas'
                    ];
                }

                $totalBill = $bill->bebas_bill;
                $paidAmount = $bill->bebas_total_pay ?? 0;
                $remainingAmount = $totalBill - $paidAmount;

                if ($amount > $remainingAmount) {
                    return [
                        'valid' => false,
                        'message' => "Jumlah pembayaran (Rp " . number_format($amount, 0, ',', '.') . ") melebihi sisa tagihan (Rp " . number_format($remainingAmount, 0, ',', '.') . ")"
                    ];
                }
            }

            return [
                'valid' => true,
                'message' => 'Validasi berhasil'
            ];

        } catch (\Exception $e) {
            Log::error('Error validating payment amount', [
                'bill_type' => $billType,
                'bill_id' => $billId,
                'student_id' => $studentId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'message' => 'Terjadi kesalahan saat validasi pembayaran'
            ];
        }
    }

    /**
     * Get bill details
     */
    private function getBillDetails($billType, $billId, $studentId)
    {
        if ($billType === 'bulanan') {
            $bill = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('b.bulan_id', $billId)
                ->where('b.student_student_id', $studentId)
                ->select('pos.pos_name', 'b.month_month_id')
                ->first();

            if ($bill) {
                $monthNames = [
                    1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
                    5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari', 
                    9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                ];
                
                return [
                    'name' => $bill->pos_name . ' - ' . ($monthNames[$bill->month_month_id] ?? 'Unknown')
                ];
            }
        } else {
            $bill = DB::table('bebas as be')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('be.bebas_id', $billId)
                ->where('be.student_student_id', $studentId)
                ->select('pos.pos_name')
                ->first();

            if ($bill) {
                return [
                    'name' => $bill->pos_name
                ];
            }
        }

        return null;
    }

    /**
     * Get available payment methods (iPaymu)
     */
    public function getPaymentChannels()
    {
        try {
            // iPaymu doesn't require channel selection beforehand
            // Payment methods are shown directly on the payment page
            return response()->json([
                'success' => true,
                'message' => 'iPaymu akan menampilkan metode pembayaran langsung di halaman pembayaran',
                'data' => []
            ]);

        } catch (\Exception $e) {
            Log::error('Get payment channels error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle return from iPaymu
     */
    public function paymentReturn(Request $request)
    {
        $reference = $request->input('reference') ?? $request->input('trx_id');
        $status = $request->input('status');

        // Find transfer record
        $transfer = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('t.reference', $reference)
            ->orWhere('t.merchantRef', $reference)
            ->orWhere('t.gateway_transaction_id', $reference)
            ->select('t.*', 's.student_full_name')
            ->first();

        if (!$transfer) {
            return redirect()->route('student.dashboard')->with('error', 'Transaksi tidak ditemukan');
        }

        $message = '';
        $type = 'info';

        // Handle different status from iPaymu
        switch ($status) {
            case 'berhasil':
            case 'success':
            case 'paid':
                $message = 'Pembayaran berhasil! Terima kasih telah melakukan pembayaran.';
                $type = 'success';
                break;
            case 'pending':
                $message = 'Pembayaran sedang diproses. Silakan tunggu beberapa saat.';
                $type = 'info';
                break;
            case 'expired':
            case 'kadaluarsa':
                $message = 'Pembayaran telah kadaluarsa. Silakan coba lagi.';
                $type = 'warning';
                break;
            case 'failed':
            case 'gagal':
                $message = 'Pembayaran gagal. Silakan coba lagi.';
                $type = 'error';
                break;
            default:
                $message = 'Status pembayaran: ' . $status;
                break;
        }

        return redirect()->route('student.payment.history')->with($type, $message);
    }

    /**
     * Menampilkan riwayat pembayaran online
     */
    public function paymentHistory(Request $request)
    {
        // Get current school_id from session
        $currentSchoolId = session('current_school_id');
        
        if (!$currentSchoolId) {
            return redirect()->route('manage.general.setting')
                ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
        }

        // Query untuk transfer manual (existing)
        $transferQuery = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->where('s.school_id', $currentSchoolId) // Filter by school_id
            ->select(
                't.transfer_id',
                't.student_id',
                't.reference',
                't.confirm_pay as amount',
                't.confirm_bank as payment_method',
                't.status',
                't.created_at',
                't.updated_at',
                's.student_nis',
                's.student_full_name',
                'c.class_name',
                DB::raw("CASE WHEN t.payment_method = 'midtrans' THEN 'online' ELSE 'manual' END as payment_type"),
                DB::raw("'transfer' as source_table"),
                't.confirm_bank'
            );

        // Get data from transfer table only
        $transferData = $transferQuery->get();

        // Use transfer data as the main data source
        $allTransfers = $transferData;

        // Apply filters - Perbaikan logic filter
        if ($request->filled('status')) {
            $status = $request->status;
            $allTransfers = $allTransfers->filter(function($item) use ($status) {
                if ($status === '0') {
                    return $item->status == 0;
                } elseif ($status === '1') {
                    return $item->status == 1;
                } elseif ($status === '2') {
                    return $item->status == 2;
                }
                return true;
            });
        }

        if ($request->filled('payment_type')) {
            $paymentType = $request->payment_type;
            $allTransfers = $allTransfers->filter(function($item) use ($paymentType) {
                return $item->payment_type == $paymentType;
            });
        }

        if ($request->filled('date_from')) {
            $dateFrom = $request->date_from;
            $allTransfers = $allTransfers->filter(function($item) use ($dateFrom) {
                return date('Y-m-d', strtotime($item->created_at)) >= $dateFrom;
            });
        }

        if ($request->filled('date_to')) {
            $dateTo = $request->date_to;
            $allTransfers = $allTransfers->filter(function($item) use ($dateTo) {
                return date('Y-m-d', strtotime($item->created_at)) <= $dateTo;
            });
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $allTransfers = $allTransfers->filter(function($item) use ($search) {
                return stripos($item->student_nis, $search) !== false ||
                       stripos($item->student_full_name, $search) !== false ||
                       stripos($item->reference, $search) !== false;
            });
        }

        // Log filter results for debugging
        \Log::info('Filter applied', [
            'total_before_filter' => $transferData->count(),
            'total_after_filter' => $allTransfers->count(),
            'filters' => $request->only(['status', 'payment_type', 'date_from', 'date_to', 'search', 'per_page'])
        ]);

        // Sort: status menunggu (0) paling atas, kemudian yang lain diurutkan berdasarkan created_at desc
        $allTransfers = $allTransfers->sort(function($a, $b) {
            // Prioritas: status 0 (menunggu) di atas
            if ($a->status == 0 && $b->status != 0) {
                return -1; // a lebih dulu
            }
            if ($a->status != 0 && $b->status == 0) {
                return 1; // b lebih dulu
            }
            // Jika status sama, urutkan berdasarkan created_at desc (terbaru dulu)
            return strtotime($b->created_at) - strtotime($a->created_at);
        });
        
        // Manual pagination
        $perPage = $request->get('per_page', 20);
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedTransfers = $allTransfers->slice($offset, $perPage);
        
        $transfers = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedTransfers,
            $allTransfers->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(), 
                'pageName' => 'page',
                'query' => request()->query()
            ]
        );

        return view('online-payment.history', compact('transfers'));
    }

    /**
     * Callback dari iPaymu (redirected to IpaymuCallbackController)
     * This method is kept for backward compatibility
     */
    public function paymentCallback(Request $request)
    {
        Log::info('🔵 Payment callback received - redirecting to IpaymuCallbackController', [
                'method' => $request->method(),
            'data' => $request->all()
        ]);

        // Callback untuk iPaymu sudah dihandle di IpaymuCallbackController
        // Method ini hanya untuk backward compatibility
                return response()->json([
                    'success' => true,
            'message' => 'Callback should be handled by IpaymuCallbackController',
            'note' => 'Please ensure your payment gateway callback URL is set to /api/ipaymu/callback'
        ]);
    }

    /**
     * Update bill status after successful payment
     */
    private function updateBillStatus($transferId)
    {
        Log::info('Starting updateBillStatus', ['transfer_id' => $transferId]);
        
        // Get all transfer details for this transfer
        $transferDetails = DB::table('transfer_detail')
            ->where('transfer_id', $transferId)
            ->get();

        if ($transferDetails->isEmpty()) {
            Log::error('Transfer details not found', ['transfer_id' => $transferId]);
            return;
        }
        
        Log::info('Transfer details found', [
            'transfer_id' => $transferId,
            'details_count' => $transferDetails->count(),
            'details' => $transferDetails->toArray()
        ]);

        // Get student_id from transfer
        $studentId = DB::table('transfer')->where('transfer_id', $transferId)->value('student_id');

        foreach ($transferDetails as $transferDetail) {
            Log::info('Processing transfer detail', [
                'detail_id' => $transferDetail->id,
                'payment_type' => $transferDetail->payment_type,
                'bulan_id' => $transferDetail->bulan_id,
                'bebas_id' => $transferDetail->bebas_id,
                'subtotal' => $transferDetail->subtotal
            ]);

            if ($transferDetail->payment_type == 1) {
                // Bulanan payment
                Log::info('Processing bulanan payment', [
                    'bulan_id' => $transferDetail->bulan_id,
                    'subtotal' => $transferDetail->subtotal
                ]);
                
                // Get bulan data before update untuk mendapatkan bulan_bill
                $bulanData = DB::table('bulan')->where('bulan_id', $transferDetail->bulan_id)->first();
                
                if (!$bulanData) {
                    Log::error('Bulan data not found', ['bulan_id' => $transferDetail->bulan_id]);
                    continue;
                }
                
                Log::info('Bulan data before update', [
                    'bulan_id' => $transferDetail->bulan_id,
                    'bulan_bill' => $bulanData->bulan_bill,
                    'current_status' => $bulanData->bulan_status,
                    'current_date_pay' => $bulanData->bulan_date_pay
                ]);
                
                // Update semua field yang diperlukan untuk bulanan
                $updateResult = DB::table('bulan')
                    ->where('bulan_id', $transferDetail->bulan_id)
                    ->update([
                        'bulan_date_pay' => now(),
                        'bulan_number_pay' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                        'bulan_status' => 1, // Set status menjadi lunas
                        'bulan_last_update' => now()
                    ]);
                
                // Log detail update untuk debugging
                Log::info('Bulan update details', [
                    'bulan_id' => $transferDetail->bulan_id,
                    'update_result' => $updateResult,
                    'updated_fields' => [
                        'bulan_date_pay' => now(),
                        'bulan_status' => 1,
                        'bulan_number_pay' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)
                    ]
                ]);
                
                Log::info('Bulan update result', [
                    'bulan_id' => $transferDetail->bulan_id,
                    'update_result' => $updateResult
                ]);
                
                // Verifikasi update berhasil
                if ($updateResult > 0) {
                    $updatedBulan = DB::table('bulan')->where('bulan_id', $transferDetail->bulan_id)->first();
                    Log::info('Bulan verification after update', [
                        'bulan_id' => $transferDetail->bulan_id,
                        'bulan_status' => $updatedBulan->bulan_status,
                        'bulan_date_pay' => $updatedBulan->bulan_date_pay,
                        'bulan_number_pay' => $updatedBulan->bulan_number_pay
                    ]);
                    
                    // Double check dengan query langsung
                    $directCheck = DB::select("SELECT bulan_status, bulan_date_pay, bulan_number_pay FROM bulan WHERE bulan_id = ?", [$transferDetail->bulan_id]);
                    Log::info('Direct database check', [
                        'bulan_id' => $transferDetail->bulan_id,
                        'direct_result' => $directCheck
                    ]);
                    
                } else {
                    Log::error('Bulan update failed', [
                        'bulan_id' => $transferDetail->bulan_id,
                        'update_result' => $updateResult
                    ]);
                    
                    // Check apakah ada constraint atau error lain
                    try {
                        $bulanCheck = DB::table('bulan')->where('bulan_id', $transferDetail->bulan_id)->first();
                        Log::error('Bulan check after failed update', [
                            'bulan_id' => $transferDetail->bulan_id,
                            'current_data' => $bulanCheck
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error checking bulan after failed update', [
                            'bulan_id' => $transferDetail->bulan_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Insert to log_trx
                $logTrxId = DB::table('log_trx')->insertGetId([
                    'student_student_id' => $studentId,
                    'bulan_bulan_id' => $transferDetail->bulan_id,
                    'bebas_pay_bebas_pay_id' => null,
                    'log_trx_input_date' => now(),
                    'log_trx_last_update' => now()
                ]);
                
                Log::info('Log trx inserted for bulanan', ['log_trx_id' => $logTrxId]);

            } elseif ($transferDetail->payment_type == 2) {
                // Bebas payment
                Log::info('Processing bebas payment', [
                    'bebas_id' => $transferDetail->bebas_id,
                    'subtotal' => $transferDetail->subtotal
                ]);
                
                $bebas = DB::table('bebas')->where('bebas_id', $transferDetail->bebas_id)->first();
                
                if ($bebas) {
                    Log::info('Bebas record found', ['bebas' => $bebas]);
                    
                    // Insert to bebas_pay
                    $bebasPayId = DB::table('bebas_pay')->insertGetId([
                        'bebas_bebas_id' => $transferDetail->bebas_id,
                        'bebas_pay_bill' => $transferDetail->subtotal,
                        'bebas_pay_number' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                        'bebas_pay_desc' => 'Pembayaran Online via Midtrans',
                        'user_user_id' => 1,
                        'bebas_pay_input_date' => now(),
                        'bebas_pay_last_update' => now()
                    ]);
                    
                    Log::info('Bebas pay inserted', ['bebas_pay_id' => $bebasPayId]);

                    // Update bebas total_pay dan bebas_date_pay
                    $updateResult = DB::table('bebas')
                        ->where('bebas_id', $transferDetail->bebas_id)
                        ->update([
                            'bebas_total_pay' => $bebas->bebas_total_pay + $transferDetail->subtotal,
                            'bebas_date_pay' => now(),
                            'bebas_number_pay' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                            'bebas_last_update' => now()
                        ]);
                    
                    Log::info('Bebas update result', [
                        'bebas_id' => $transferDetail->bebas_id,
                        'old_total_pay' => $bebas->bebas_total_pay,
                        'new_total_pay' => $bebas->bebas_total_pay + $transferDetail->subtotal,
                        'update_result' => $updateResult
                    ]);

                    // Insert to log_trx
                    $logTrxId = DB::table('log_trx')->insertGetId([
                        'student_student_id' => $studentId,
                        'bulan_bulan_id' => null,
                        'bebas_pay_bebas_pay_id' => $bebasPayId,
                        'log_trx_input_date' => now(),
                        'log_trx_last_update' => now()
                    ]);
                    
                    Log::info('Log trx inserted for bebas', ['log_trx_id' => $logTrxId]);
                } else {
                    Log::error('Bebas record not found', ['bebas_id' => $transferDetail->bebas_id]);
                }
            }
        }
        
        Log::info('updateBillStatus completed', [
            'transfer_id' => $transferId,
            'processed_details' => $transferDetails->count()
        ]);
    }

    /**
     * Method untuk memproses ulang transfer yang sudah sukses tapi belum terupdate semua transfer_detail-nya
     * Hanya untuk development/testing
     */
    public function reprocessSuccessfulTransfer(Request $request, $transferId)
    {
        try {
            Log::info('Reprocessing successful transfer', ['transfer_id' => $transferId]);
            
            // Get transfer data
            $transfer = DB::table('transfer')->where('transfer_id', $transferId)->first();
            if (!$transfer) {
                return response()->json(['success' => false, 'message' => 'Transfer not found']);
            }
            
            if ($transfer->status != 1) {
                return response()->json(['success' => false, 'message' => 'Transfer is not successful (status != 1)']);
            }
            
            Log::info('Transfer found for reprocessing', [
                'transfer_id' => $transferId,
                'status' => $transfer->status,
                'paid_at' => $transfer->paid_at
            ]);
            
            // Process updateBillStatus
            $this->updateBillStatus($transferId);
            
            return response()->json([
                'success' => true,
                'message' => 'Transfer reprocessed successfully',
                'transfer_id' => $transferId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in reprocessSuccessfulTransfer', [
                'transfer_id' => $transferId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Method untuk testing manual update bulan_status
     * Hanya untuk development/testing
     */
    public function testUpdateBulanStatus(Request $request, $bulanId)
    {
        try {
            Log::info('Manual test update bulan status', ['bulan_id' => $bulanId]);
            
            // Get bulan data before update
            $bulanBefore = DB::table('bulan')->where('bulan_id', $bulanId)->first();
            if (!$bulanBefore) {
                return response()->json(['success' => false, 'message' => 'Bulan not found']);
            }
            
            Log::info('Bulan before update', [
                'bulan_id' => $bulanId,
                'bulan_status' => $bulanBefore->bulan_status,
                'bulan_date_pay' => $bulanBefore->bulan_date_pay,
                'bulan_bill' => $bulanBefore->bulan_bill
            ]);
            
            // Update bulan_status dengan query yang lebih spesifik
            $updateResult = DB::table('bulan')
                ->where('bulan_id', $bulanId)
                ->update([
                    'bulan_status' => 1,
                    'bulan_date_pay' => now(),
                    'bulan_number_pay' => 'TEST-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'bulan_last_update' => now()
                ]);
            
            Log::info('Update query result', [
                'bulan_id' => $bulanId,
                'update_result' => $updateResult,
                'affected_rows' => $updateResult
            ]);
            
            if ($updateResult > 0) {
                // Get updated data
                $bulanAfter = DB::table('bulan')->where('bulan_id', $bulanId)->first();
                
                Log::info('Bulan after manual update', [
                    'bulan_id' => $bulanId,
                    'bulan_status' => $bulanAfter->bulan_status,
                    'bulan_date_pay' => $bulanAfter->bulan_date_pay,
                    'bulan_number_pay' => $bulanAfter->bulan_number_pay
                ]);
                
                // Double check dengan query langsung
                $directCheck = DB::select("SELECT bulan_status, bulan_date_pay, bulan_number_pay FROM bulan WHERE bulan_id = ?", [$bulanId]);
                Log::info('Direct database check after update', [
                    'bulan_id' => $bulanId,
                    'direct_result' => $directCheck
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Bulan status updated successfully',
                    'before' => $bulanBefore,
                    'after' => $bulanAfter,
                    'direct_check' => $directCheck
                ]);
            } else {
                // Check apakah ada constraint atau error lain
                try {
                    $bulanCheck = DB::table('bulan')->where('bulan_id', $bulanId)->first();
                    Log::error('Bulan check after failed update', [
                        'bulan_id' => $bulanId,
                        'current_data' => $bulanCheck
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error checking bulan after failed update', [
                        'bulan_id' => $bulanId,
                        'error' => $e->getMessage()
                    ]);
                }
                
                return response()->json(['success' => false, 'message' => 'Update failed - no rows affected']);
            }
            
        } catch (\Exception $e) {
            Log::error('Error in testUpdateBulanStatus', [
                'bulan_id' => $bulanId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Verifikasi pembayaran manual
     */
    public function verifyPayment(Request $request, $id)
    {
        // Get current school_id from session
        $currentSchoolId = session('current_school_id');
        
        if (!$currentSchoolId) {
            return response()->json([
                'success' => false,
                'message' => 'Sekolah belum dipilih'
            ], 403);
        }

        $request->validate([
            'verification_status' => 'required|in:verified,rejected',
            'verification_notes' => 'nullable|string'
        ]);

        try {
            $transfer = DB::table('transfer as t')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->where('t.transfer_id', $id)
                ->where('s.school_id', $currentSchoolId) // Filter by school_id
                ->select('t.*')
                ->first();
            
            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran tidak ditemukan'
                ], 404);
            }

            DB::beginTransaction();

            // Update transfer status
            $newStatus = $request->verification_status === 'verified' ? 1 : 2; // 1 = verified, 2 = rejected
            DB::table('transfer')
                ->where('transfer_id', $id)
                ->update([
                    'status' => $newStatus,
                    'verif_user' => auth()->id(),
                    'verif_date' => now(),
                    'updated_at' => now()
                ]);

            // If verified, update bill status
            if ($request->verification_status === 'verified') {
                // Get transfer details
                $transferDetails = DB::table('transfer_detail')
                    ->where('transfer_id', $id)
                    ->get();
                


                foreach ($transferDetails as $detail) {
                    if ($detail->payment_type == 1) { // Bulanan
                        // Get bulan data untuk mendapatkan bulan_bill
                        $bulanData = DB::table('bulan')->where('bulan_id', $detail->bulan_id)->first();
                        
                        if ($bulanData) {
                            DB::table('bulan')
                                ->where('bulan_id', $detail->bulan_id)
                                ->where('student_student_id', $transfer->student_id)
                                ->update([
                                    'bulan_date_pay' => now()->format('Y-m-d'),
                                    'bulan_number_pay' => $transfer->reference,
                                    'bulan_status' => 1, // Set status menjadi lunas
                                    'bulan_last_update' => now()
                                ]);
                        }

                        // Insert ke log_trx
                        DB::table('log_trx')->insert([
                            'student_student_id' => $transfer->student_id,
                            'bulan_bulan_id' => $detail->bulan_id,
                            'bebas_pay_bebas_pay_id' => null,
                            'log_trx_input_date' => now(),
                            'log_trx_last_update' => now()
                        ]);
                    } elseif ($detail->payment_type == 2) { // Bebas
                        // Insert ke bebas_pay
                        $bebasPayId = DB::table('bebas_pay')->insertGetId([
                            'bebas_bebas_id' => $detail->bebas_id,
                            'bebas_pay_bill' => $detail->subtotal,
                            'bebas_pay_number' => $transfer->reference,
                            'bebas_pay_desc' => 'Pembayaran Transfer Bank',
                            'user_user_id' => auth()->id() ?? 1,
                            'bebas_pay_input_date' => $transfer->created_at,
                            'bebas_pay_last_update' => now()
                        ]);

                        // Update bebas
                        DB::table('bebas')
                            ->where('bebas_id', $detail->bebas_id)
                            ->where('student_student_id', $transfer->student_id)
                            ->update([
                                'bebas_date_pay' => now()->format('Y-m-d'),
                                'bebas_number_pay' => $transfer->reference,
                                'bebas_total_pay' => DB::raw('bebas_total_pay + ' . $detail->subtotal),
                                'bebas_last_update' => now()
                            ]);

                        // Insert ke log_trx
                        DB::table('log_trx')->insert([
                            'student_student_id' => $transfer->student_id,
                            'bulan_bulan_id' => null,
                            'bebas_pay_bebas_pay_id' => $bebasPayId,
                            'log_trx_input_date' => $transfer->created_at,
                            'log_trx_last_update' => now()
                        ]);
                    } elseif ($detail->payment_type == 3 && $detail->is_tabungan == 1) { // Tabungan
                        // Check if student already has tabungan record
                        $existingTabungan = DB::table('tabungan')
                            ->where('student_student_id', $transfer->student_id)
                            ->first();
                        
                        if ($existingTabungan) {
                            // Update existing tabungan
                            DB::table('tabungan')
                                ->where('student_student_id', $transfer->student_id)
                                ->update([
                                    'saldo' => DB::raw('saldo + ' . $detail->subtotal),
                                    'tabungan_last_update' => now()
                                ]);
                            
                            $tabunganId = $existingTabungan->tabungan_id;
                        } else {
                            // Insert new tabungan record
                            $tabunganId = DB::table('tabungan')->insertGetId([
                                'student_student_id' => $transfer->student_id,
                                'user_user_id' => auth()->id() ?? 1,
                                'saldo' => $detail->subtotal,
                                'tabungan_input_date' => now(),
                                'tabungan_last_update' => now()
                            ]);
                        }

                        // Get current saldo for log_tabungan
                        $currentSaldo = DB::table('tabungan')
                            ->where('tabungan_id', $tabunganId)
                            ->value('saldo');

                        // Insert ke log_tabungan
                        DB::table('log_tabungan')->insert([
                            'tabungan_tabungan_id' => $tabunganId,
                            'student_student_id' => $transfer->student_id,
                            'kredit' => $detail->subtotal, // Setoran masuk sebagai kredit
                            'debit' => 0, // Tidak ada penarikan
                            'saldo' => $currentSaldo, // Saldo setelah setoran
                            'keterangan' => 'Setoran Tabungan via Transfer Bank - ' . $transfer->reference,
                            'log_tabungan_input_date' => now(),
                            'log_tabungan_last_update' => now()
                        ]);
                    }
                }
            }

            DB::commit();

            // Kirim notifikasi WhatsApp jika diaktifkan
            try {
                $gateway = DB::table('setup_gateways')->first();
                if ($gateway && $gateway->enable_wa_notification) {
                    $whatsappService = new WhatsAppService();
                    
                    if ($request->verification_status === 'verified') {
                        $whatsappService->sendPaymentSuccessNotification($id);
                        Log::info("WhatsApp success notification sent for verified transfer_id: {$id}");
                    } else {
                        $whatsappService->sendPaymentFailedNotification($id);
                        Log::info("WhatsApp failed notification sent for rejected transfer_id: {$id}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp notification for verification: " . $e->getMessage());
                // Jangan gagalkan proses verifikasi jika notifikasi gagal
            }

            // Jika verified, return dengan redirect URL ke halaman cetak kuitansi
            if ($request->verification_status === 'verified') {
            return response()->json([
                'success' => true,
                    'message' => 'Pembayaran berhasil diverifikasi',
                    'redirect' => route('online-payment.receipt', $id)
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran ditolak'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail pembayaran
     */
    public function paymentDetail($id)
    {
        try {
            // Get current school_id from session
            $currentSchoolId = session('current_school_id');
            
            if (!$currentSchoolId) {
                return response()->json(['error' => 'Sekolah belum dipilih'], 403);
            }

            \Log::info('Payment detail requested for ID: ' . $id);
            \Log::info('User authenticated: ' . (auth()->check() ? 'Yes' : 'No'));
            \Log::info('Request method: ' . request()->method());
            \Log::info('Request URL: ' . request()->url());
            
            $transfer = DB::table('transfer as t')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                ->where('t.transfer_id', $id)
                ->where('s.school_id', $currentSchoolId) // Filter by school_id
                ->select(
                    't.*',
                    's.student_nis',
                    's.student_full_name',
                    'c.class_name'
                )
                ->first();

            if (!$transfer) {
                \Log::warning('Transfer not found for ID: ' . $id);
                return response()->json(['error' => 'Pembayaran tidak ditemukan'], 404);
            }
            
            \Log::info('Transfer found:', ['transfer_id' => $transfer->transfer_id, 'student_name' => $transfer->student_full_name]);

            // Get transfer details
            $transferDetails = DB::table('transfer_detail as td')
                ->leftJoin('bulan as b', function($join) {
                    $join->on('td.bulan_id', '=', 'b.bulan_id')
                         ->where('td.payment_type', '=', 1);
                })
                ->leftJoin('bebas as be', function($join) {
                    $join->on('td.bebas_id', '=', 'be.bebas_id')
                         ->where('td.payment_type', '=', 2);
                })
                ->leftJoin('payment as p', function($join) {
                    $join->on('b.payment_payment_id', '=', 'p.payment_id')
                         ->orOn('be.payment_payment_id', '=', 'p.payment_id');
                })
                ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('td.transfer_id', $id)
                ->select(
                    'td.*',
                    'pos.pos_name',
                    'b.bulan_bill',
                    'be.bebas_bill',
                    'b.bulan_date_pay',
                    'be.bebas_date_pay',
                    'b.month_month_id'
                )
                ->get();

            // Add month names mapping
            $monthNames = [
                1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
                5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
                9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
            ];

            // Add month names to transfer details
            foreach ($transferDetails as $detail) {
                if ($detail->payment_type == 1 && $detail->month_month_id) {
                    $detail->month_name = $monthNames[$detail->month_month_id] ?? 'Unknown';
                }
            }

            \Log::info('Transfer details loaded:', ['count' => $transferDetails->count()]);

            return view('online-payment.detail', compact('transfer', 'transferDetails'));
            
        } catch (\Exception $e) {
            \Log::error('Error in paymentDetail:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return HTML error instead of JSON for AJAX requests
            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat detail pembayaran',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download receipt pembayaran
     */
    public function downloadReceipt($id)
    {
        // Get current school_id from session
        $currentSchoolId = session('current_school_id');
        
        if (!$currentSchoolId) {
            return redirect()->route('manage.general.setting')
                ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
        }

        $payment = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->where('t.transfer_id', $id)
            ->where('s.school_id', $currentSchoolId) // Filter by school_id
            ->select('t.*', 's.student_nis', 's.student_full_name', 'c.class_name')
            ->first();

        if (!$payment) {
            return redirect()->back()->with('error', 'Pembayaran tidak ditemukan');
        }

        // Get transfer details
        $transferDetails = DB::table('transfer_detail as td')
            ->leftJoin('bulan as b', function($join) {
                $join->on('td.bulan_id', '=', 'b.bulan_id')
                     ->where('td.payment_type', '=', 1);
            })
            ->leftJoin('bebas as be', function($join) {
                $join->on('td.bebas_id', '=', 'be.bebas_id')
                     ->where('td.payment_type', '=', 2);
            })
            ->leftJoin('payment as p', function($join) {
                $join->on('b.payment_payment_id', '=', 'p.payment_id')
                     ->orOn('be.payment_payment_id', '=', 'p.payment_id');
            })
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('td.transfer_id', $id)
            ->select(
                'td.*',
                'pos.pos_name',
                'b.bulan_bill',
                'be.bebas_bill',
                'b.month_month_id',
                'per.period_start',
                'per.period_end'
            )
            ->get();

        // Add month names mapping
        $monthNames = [
            1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
            5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
            9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
        ];

        // Add month names and period information to transfer details
        foreach ($transferDetails as $detail) {
            if ($detail->payment_type == 1 && $detail->month_month_id) {
                $detail->month_name = $monthNames[$detail->month_month_id] ?? 'Unknown';
                
                // Add period information
                if ($detail->period_start && $detail->period_end) {
                    $detail->period_name = $detail->period_start . '/' . $detail->period_end;
                } else {
                    $detail->period_name = 'T.A 2025/2026'; // Default fallback
                }
            }
        }

        // Ambil data sekolah
        $schoolProfile = DB::table('schools')->first();

        // Ambil informasi petugas dari verif_user atau gunakan default
        $officerName = 'Sistem Pembayaran Online';
        if ($payment->verif_user) {
            $verifier = DB::table('users')->where('id', $payment->verif_user)->first();
            if ($verifier) {
                $officerName = $verifier->name ?? 'Petugas Verifikasi';
            }
        }

        return view('online-payment.receipt', compact('payment', 'transferDetails', 'schoolProfile', 'officerName'));
    }

    /**
     * Approve pembayaran
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transfer = DB::table('transfer')->where('transfer_id', $id)->first();
            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran tidak ditemukan'
                ], 404);
            }

            if ($transfer->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah diverifikasi sebelumnya'
                ], 400);
            }

            // Update status transfer menjadi approved
            DB::table('transfer')
                ->where('transfer_id', $id)
                ->update([
                    'status' => 1,
                    'updated_at' => now()
                ]);

            // Get transfer details
            $transferDetails = DB::table('transfer_detail')
                ->where('transfer_id', $id)
                ->get();

            // Process each payment detail
            foreach ($transferDetails as $detail) {
                if ($detail->payment_type == 1) { // Bulanan
                    // Get bulan data untuk mendapatkan bulan_bill
                    $bulanData = DB::table('bulan')->where('bulan_id', $detail->bulan_id)->first();
                    
                    if ($bulanData) {
                        DB::table('bulan')
                            ->where('bulan_id', $detail->bulan_id)
                            ->update([
                                'bulan_date_pay' => now(),
                                'bulan_number_pay' => $transfer->reference ?? 'APPROVED-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                                'bulan_status' => 1, // Set status menjadi lunas
                                'bulan_last_update' => now()
                            ]);
                    }
                } elseif ($detail->payment_type == 2) { // Bebas
                    DB::table('bebas')
                        ->where('bebas_id', $detail->bebas_id)
                        ->update([
                            'bebas_date_pay' => now(),
                            'bebas_status' => 1
                        ]);
                } elseif ($detail->payment_type == 3 && $detail->is_tabungan == 1) { // Tabungan
                    // Update tabungan saldo
                    $existingTabungan = DB::table('tabungan')
                        ->where('student_student_id', $transfer->student_id)
                        ->first();
                    
                    if ($existingTabungan) {
                        DB::table('tabungan')
                            ->where('student_student_id', $transfer->student_id)
                            ->update([
                                'saldo' => DB::raw('saldo + ' . $detail->subtotal),
                                'tabungan_last_update' => now()
                            ]);
                        
                        $tabunganId = $existingTabungan->tabungan_id;
                    } else {
                        $tabunganId = DB::table('tabungan')->insertGetId([
                            'student_student_id' => $transfer->student_id,
                            'user_user_id' => auth()->id() ?? 1,
                            'saldo' => $detail->subtotal,
                            'tabungan_input_date' => now(),
                            'tabungan_last_update' => now()
                        ]);
                    }

                    // Insert ke log_tabungan
                    $currentSaldo = DB::table('tabungan')
                        ->where('tabungan_id', $tabunganId)
                        ->value('saldo');

                    DB::table('log_tabungan')->insert([
                        'tabungan_tabungan_id' => $tabunganId,
                        'student_student_id' => $transfer->student_id,
                        'kredit' => $detail->subtotal,
                        'debit' => 0,
                        'saldo' => $currentSaldo,
                        'keterangan' => 'Setoran Tabungan via Transfer Bank - ' . $transfer->reference,
                        'log_tabungan_input_date' => now(),
                        'log_tabungan_last_update' => now()
                    ]);
                }
            }

            // Kirim notifikasi WhatsApp jika diaktifkan
            try {
                $gateway = DB::table('setup_gateways')->first();
                if ($gateway && $gateway->enable_wa_notification) {
                    $whatsappService = new WhatsAppService();
                    $whatsappService->sendPaymentApprovedNotification($id);
                    Log::info("WhatsApp notification sent for approved transfer_id: {$id}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp notification: " . $e->getMessage());
            }

            // Buat notifikasi sistem
            try {
                // Get student name
                $student = DB::table('student')->where('student_id', $transfer->student_id)->first();
                $studentName = $student ? $student->student_full_name : 'Siswa';
                
                // Calculate total amount
                $totalAmount = $transferDetails->sum('subtotal');
                
                NotificationService::createPaymentNotification([
                    'id' => $id,
                    'amount' => $totalAmount,
                    'student_name' => $studentName,
                    'payment_method' => 'Online Payment'
                ]);
                
                Log::info("System notification created for approved transfer_id: {$id}");
            } catch (\Exception $e) {
                Log::error("Failed to create system notification: " . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diverifikasi!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error approving payment:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memverifikasi pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject pembayaran
     */
    public function reject(Request $request, $id)
    {
        try {
            $request->validate([
                'rejection_reason' => 'required|string|max:500'
            ]);

            $transfer = DB::table('transfer')->where('transfer_id', $id)->first();
            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran tidak ditemukan'
                ], 404);
            }

            if ($transfer->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah diverifikasi sebelumnya'
                ], 400);
            }

            // Update status transfer menjadi rejected
            DB::table('transfer')
                ->where('transfer_id', $id)
                ->update([
                    'status' => 2,
                    'rejection_reason' => $request->rejection_reason,
                    'updated_at' => now()
                ]);

            // Kirim notifikasi WhatsApp jika diaktifkan
            try {
                $gateway = DB::table('setup_gateways')->first();
                if ($gateway && $gateway->enable_wa_notification) {
                    $whatsappService = new WhatsAppService();
                    $whatsappService->sendPaymentRejectedNotification($id, $request->rejection_reason);
                    Log::info("WhatsApp notification sent for rejected transfer_id: {$id}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp notification: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil ditolak!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error rejecting payment:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menolak pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }
}

