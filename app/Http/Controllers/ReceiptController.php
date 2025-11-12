<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceiptController extends Controller
{
    public function show($paymentNumber)
    {
        try {
            // Cari data pembayaran berdasarkan nomor pembayaran
            $paymentData = $this->getPaymentData($paymentNumber);
            
            if (!$paymentData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan'
                ], 404);
            }
            
            return view('payment.receipt', $paymentData);
            
        } catch (\Exception $e) {
            \Log::error('Error generating receipt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat kuitansi'
            ], 500);
        }
    }
    
    private function getPaymentData($paymentNumber)
    {
        // Coba cari di log_trx untuk pembayaran bulanan
        $bulananPayment = DB::table('log_trx as lt')
            ->join('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
            ->where('b.bulan_number_pay', $paymentNumber)
            ->where('b.bulan_status', 1)
            ->select(
                's.student_nis as nis',
                's.student_full_name as nama',
                's.class_class_id as kelas',
                's.student_status as status_siswa',
                'b.bulan_date_pay as payment_date',
                'b.bulan_number_pay as payment_number',
                'b.bulan_bill as amount',
                'pos.pos_name',
                'b.month_month_id as month',
                DB::raw("'2025/2026' as payment_period")
            )
            ->first();
            
        if ($bulananPayment) {
            return [
                'student' => [
                    'nis' => $bulananPayment->nis,
                    'nama' => $bulananPayment->nama,
                    'kelas' => $bulananPayment->kelas,
                    'status' => $bulananPayment->status_siswa
                ],
                'payment_date' => Carbon::parse($bulananPayment->payment_date)->format('d F Y'),
                'payment_number' => $bulananPayment->payment_number,
                'payment_method' => 'Tunai', // Default
                'officer' => 'Administrator2', // Default
                'payment_details' => [
                    [
                        'description' => $bulananPayment->pos_name . ' - T.A ' . ($bulananPayment->payment_period ?? '2024/2025') . ' - (' . $this->getMonthName($bulananPayment->month) . ' ' . ($bulananPayment->payment_period ?? '2024') . ')',
                        'amount' => $bulananPayment->amount
                    ]
                ],
                'total_amount' => $bulananPayment->amount,
                'current_date' => Carbon::now()->format('d F Y')
            ];
        }
        
        // Coba cari di log_trx untuk pembayaran bebas
        $bebasPayment = DB::table('log_trx as lt')
            ->join('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
            ->join('bebas as b', 'bp.bebas_bebas_id', '=', 'b.bebas_id')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
            ->where('bp.bebas_pay_number', $paymentNumber)
            ->select(
                's.student_nis as nis',
                's.student_full_name as nama',
                's.class_class_id as kelas',
                's.student_status as status_siswa',
                'bp.bebas_pay_input_date as payment_date',
                'bp.bebas_pay_number as payment_number',
                'bp.bebas_pay_bill as amount',
                'bp.bebas_pay_desc as description',
                'pos.pos_name',
                DB::raw("'2025/2026' as payment_period")
            )
            ->first();
            
        if ($bebasPayment) {
            return [
                'student' => [
                    'nis' => $bebasPayment->nis,
                    'nama' => $bebasPayment->nama,
                    'kelas' => $bebasPayment->kelas,
                    'status' => $bebasPayment->status_siswa
                ],
                'payment_date' => Carbon::parse($bebasPayment->payment_date)->format('d F Y'),
                'payment_number' => $bebasPayment->payment_number,
                'payment_method' => 'Tunai', // Default
                'officer' => 'Administrator2', // Default
                'payment_details' => [
                    [
                        'description' => $bebasPayment->pos_name . ' - T.A ' . ($bebasPayment->payment_period ?? '2024/2025') . ($bebasPayment->description ? ' - ' . $bebasPayment->description : ''),
                        'amount' => $bebasPayment->amount
                    ]
                ],
                'total_amount' => $bebasPayment->amount,
                'current_date' => Carbon::now()->format('d F Y')
            ];
        }
        
        return null;
    }
    
    private function getMonthName($month)
    {
        $months = [
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
        
        return $months[$month] ?? 'Unknown';
    }
    
    public function generateReceipt(Request $request)
    {
        try {
            \Log::info('Generate receipt request received:', $request->all());
            
            $request->validate([
                'payment_number' => 'required|string',
                'student_id' => 'required|exists:students,student_id',
                'payment_type' => 'required|in:bulanan,bebas',
                'amount' => 'required|numeric',
                'description' => 'nullable|string',
                'payment_date' => 'required|date'
            ]);
            
            // Ambil data siswa dengan join ke tabel kelas
            try {
                            // Debug: Log request data
            \Log::info('Receipt request data:', [
                'student_id' => $request->student_id,
                'all_params' => $request->all(),
                'query_params' => $request->query(),
                'post_data' => $request->post()
            ]);
                
                // Ambil data siswa dengan join ke tabel kelas
                $student = DB::table('students as s')
                    ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
                    ->select('s.*', 'c.class_name')
                    ->where('s.student_id', $request->student_id)
                    ->first();
                    
                \Log::info('Raw student data:', (array) $student);
                
                if (!$student) {
                    \Log::warning('Student not found with ID: ' . $request->student_id);
                    
                    // Debug: Cek semua siswa yang ada
                    $allStudents = DB::table('students')->select('student_id', 'student_nis', 'student_full_name', 'class_class_id')->get();
                    \Log::info('All students in database:', $allStudents->toArray());
                } else {
                    // Debug: Log data siswa yang ditemukan
                    \Log::info('Student found with ID ' . $request->student_id . ':', [
                        'student_id' => $student->student_id,
                        'student_nis' => $student->student_nis,
                        'student_full_name' => $student->student_full_name,
                        'class_class_id' => $student->class_class_id,
                        'class_name' => $student->class_name ?? 'No class name'
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching student data: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching student data: ' . $e->getMessage()
                ], 500);
            }
                
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan'
                ], 404);
            }
            
            \Log::info('Student data for receipt:', [
                'student_id' => $request->student_id,
                'student_nis' => $student->student_nis,
                'student_name' => $student->student_full_name,
                'class_name' => $student->class_name,
                'class_id' => $student->class_class_id,
                'status' => $student->student_status
            ]);
            
            $currentUser = auth()->user();
            $officerName = $currentUser ? $currentUser->name : 'Administrator';
            
            // Ambil data transaksi berdasarkan tanggal yang dipilih
            $selectedDate = $request->payment_date;
            $paymentDetails = [];
            $totalAmount = 0;
            
            try {
                            // Cek apakah ada data transaksi yang dikirim dari frontend
            \Log::info('Request data:', $request->all());
            
            if ($request->has('transaction_data') && $request->transaction_data) {
                $transactionData = json_decode($request->transaction_data, true);
                \Log::info('Transaction data from frontend:', $transactionData);
                
                if (is_array($transactionData) && count($transactionData) > 0) {
                    foreach ($transactionData as $transaction) {
                        \Log::info('Processing transaction:', $transaction);
                        
                        // Jika data dari frontend hanya berisi description dan amount, bersihkan format
                        if (isset($transaction['description']) && !isset($transaction['pos_name'])) {
                            $description = $transaction['description'];
                            
                            // Hilangkan kode angka di akhir (format: angka 8-12 digit)
                            $description = preg_replace('/\d{8,12}$/', '', $description);
                            
                            // Perbaiki format jika masih ada masalah
                            if (strpos($description, 'SPP-') !== false) {
                                // Format bulanan: SPP-Agustus (2025/2026) -> SPP- T.A 2025/2026 ( Agustus )
                                if (preg_match('/SPP-([^(]+)\s*\(([^)]+)\)/', $description, $matches)) {
                                    $month = trim($matches[1]);
                                    $period = trim($matches[2]);
                                    $description = 'SPP- T.A ' . $period . ' ( ' . $month . ' )';
                                }
                            } elseif (strpos($description, 'U. TES') !== false) {
                                // Format bebas: U. TES - 2025/2026 -> U. TES - T.A 2025/2026
                                \Log::info('Processing U. TES format:', ['original' => $description]);
                                
                                if (preg_match('/U\. TES\s*-\s*(\d{4}\/\d{4})/', $description, $matches)) {
                                    $period = trim($matches[1]);
                                    $description = 'U. TES - T.A ' . $period;
                                    \Log::info('U. TES with period found:', ['period' => $period, 'result' => $description]);
                                } else {
                                    // Jika tidak ada periode, gunakan default
                                    $description = 'U. TES - T.A 2025/2026';
                                    \Log::info('U. TES using default period:', ['result' => $description]);
                                }
                            }
                            
                            $paymentDetails[] = [
                                'description' => $description,
                                'amount' => $transaction['amount'] ?? 20000
                            ];
                            $totalAmount += $transaction['amount'] ?? 20000;
                        } else {
                            // Format pos name based on payment type
                            $posName = $transaction['pos_name'] ?? 'SPP';
                            $period = $transaction['period'] ?? '2025/2026';
                            $month = $transaction['month'] ?? '';
                            $paymentType = $transaction['payment_type'] ?? 1;
                            
                            if ($paymentType == 1) {
                                // Bulanan format: SPP- T.A 2025/2026 ( September )
                                $description = $posName . '- T.A ' . $period . ' ( ' . $month . ' )';
                            } else {
                                // Bebas format: U. TES - T.A 2025/2026
                                $description = $posName . ' - T.A ' . $period;
                            }
                            
                            // Hilangkan kode angka di akhir jika ada
                            $description = preg_replace('/\d{8,12}$/', '', $description);
                            
                            $paymentDetails[] = [
                                'description' => $description,
                                'amount' => $transaction['amount'] ?? 20000
                            ];
                            $totalAmount += $transaction['amount'] ?? 20000;
                        }
                    }
                } else {
                    \Log::warning('Transaction data is empty or invalid');
                }
            } else {
                \Log::warning('No transaction_data in request');
                    // Fallback ke query database jika tidak ada data dari frontend
                    // Coba query yang lebih sederhana dulu
                    $transactions = DB::table('log_trx as lt')
                        ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                        ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                        ->leftJoin('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                        ->leftJoin('payment as p', function($join) {
                            $join->on('b.payment_payment_id', '=', 'p.payment_id')
                                 ->orOn('be.payment_payment_id', '=', 'p.payment_id');
                        })
                        ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                        ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                        ->leftJoin('month as m', 'b.month_month_id', '=', 'm.month_id')
                        ->where('lt.student_student_id', $request->student_id)
                        ->whereDate('lt.log_trx_input_date', $selectedDate)
                        ->select(
                            'lt.*',
                            'pos.pos_name',
                            'per.period_start',
                            'per.period_end',
                            'm.month_name',
                            'b.bulan_bill',
                            'bp.bebas_pay_bill',
                            'p.payment_type'
                        )
                        ->get();
                        
                    // Jika query kompleks tidak menghasilkan data, coba query sederhana
                    if ($transactions->count() == 0) {
                        \Log::info('Complex query returned no results, trying simple query');
                        $transactions = DB::table('log_trx as lt')
                            ->where('lt.student_student_id', $request->student_id)
                            ->whereDate('lt.log_trx_input_date', $selectedDate)
                            ->get();
                            
                        \Log::info('Simple query results:', $transactions->toArray());
                        
                        // Jika ada data dari query sederhana, buat format default
                        if ($transactions->count() > 0) {
                            foreach ($transactions as $transaction) {
                                $paymentDetails[] = [
                                    'description' => 'SPP- T.A 2025/2026 ( September )',
                                    'amount' => 20000
                                ];
                                $totalAmount += 20000;
                            }
                        }
                    }
                        
                    \Log::info('Log transactions found for student ' . $request->student_id . ' on date ' . $selectedDate . ':', $transactions->toArray());
                    
                    if ($transactions->count() > 0) {
                        foreach ($transactions as $transaction) {
                            \Log::info('Processing database transaction:', [
                                'pos_name' => $transaction->pos_name,
                                'period_start' => $transaction->period_start,
                                'period_end' => $transaction->period_end,
                                'month_name' => $transaction->month_name,
                                'payment_type' => $transaction->payment_type,
                                'bulan_bill' => $transaction->bulan_bill,
                                'bebas_pay_bill' => $transaction->bebas_pay_bill
                            ]);
                            
                            $posName = $transaction->pos_name ?? 'SPP';
                            $period = ($transaction->period_start && $transaction->period_end) ? 
                                     $transaction->period_start . '/' . $transaction->period_end : '2025/2026';
                            $month = $transaction->month_name ?? '';
                            $paymentType = $transaction->payment_type ?? 1;
                            $amount = $transaction->bulan_bill ?? $transaction->bebas_pay_bill ?? 20000;
                            
                            if ($paymentType == 1) {
                                // Bulanan format: SPP- T.A 2025/2026 ( September )
                                $description = $posName . '- T.A ' . $period . ' ( ' . $month . ' )';
                                \Log::info('Generated bulanan description:', $description);
                            } else {
                                // Bebas format: U. TES - T.A 2025/2026
                                $description = $posName . ' - T.A ' . $period;
                                \Log::info('Generated bebas description:', ['posName' => $posName, 'period' => $period, 'result' => $description]);
                            }
                            
                            // Hilangkan kode angka di akhir jika ada
                            $description = preg_replace('/\d{8,12}$/', '', $description);
                            
                            \Log::info('Final description after cleanup:', $description);
                            
                            $paymentDetails[] = [
                                'description' => $description,
                                'amount' => $amount
                            ];
                            $totalAmount += $amount;
                        }
                    } else {
                        \Log::warning('No transactions found in database for student ' . $request->student_id . ' on date ' . $selectedDate);
                    }
                }
                
            } catch (\Exception $e) {
                \Log::error('Error fetching transactions: ' . $e->getMessage());
            }
            
            // Jika tidak ada transaksi pada tanggal tersebut, JANGAN tampilkan data dummy
            if (empty($paymentDetails)) {
                \Log::warning('No payment details found. Leaving receipt empty as requested.');
                $paymentDetails = [];
                $totalAmount = 0;
            }
            
            $paymentData = [
                'student' => (object) [
                    'nis' => $student->student_nis,
                    'nama' => $student->student_full_name,
                    'kelas' => $student->class_name ?? 'Kelas Tidak Ditemukan',
                    'status' => $student->student_status ? 'Aktif' : 'Tidak Aktif'
                ],
                'officer' => $officerName,
                'payment_date' => Carbon::parse($request->payment_date)->format('d F Y'),
                'payment_number' => $request->payment_number,
                'payment_method' => $request->payment_method ?? 'Tunai',
                'payment_details' => $paymentDetails,
                'total_amount' => $totalAmount,
                'current_date' => Carbon::now()->format('d F Y')
            ];
            
            // Ambil data profil sekolah
            $schoolProfile = currentSchool() ?? \App\Models\School::first();
            
            // Jika tidak ada data profil sekolah, gunakan data default
            if (!$schoolProfile) {
                $schoolProfile = (object) [
                    'jenjang' => 'SMK',
                    'nama_sekolah' => 'SMK SPPQU DIGITAL PAYMENT',
                    'alamat' => 'Jl. Bledak Anggur IV, No.22, Tlogosari Kulon, Kota Semarang',
                    'no_telp' => '082188497818',
                    'logo_sekolah' => null,
                ];
            }
            
            $paymentData = array_merge($paymentData, [
                'school_profile' => $schoolProfile
            ]);
            
            \Log::info('Payment data for receipt:', $paymentData);
            \Log::info('Payment details being sent to template:', $paymentDetails);
            \Log::info('Student data being sent to template:', [
                'student_id' => $request->student_id,
                'student_nis' => $student->student_nis,
                'student_name' => $student->student_full_name,
                'student_class' => $student->class_name,
                'student_status' => $student->student_status
            ]);
            
            return view('payment.receipt', $paymentData);
            
        } catch (\Exception $e) {
            \Log::error('Error generating receipt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat kuitansi'
            ], 500);
        }
    }
} 