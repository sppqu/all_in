<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RekapitulasiTabunganController extends Controller
{
    /**
     * Menampilkan halaman rekapitulasi tabungan
     */
    public function index(Request $request)
    {
        // Cek apakah ada filter yang di-submit
        $hasFilters = $request->filled('start_date') || $request->filled('end_date') || 
                     $request->filled('payment_method') || $request->filled('class_id') ||
                     $request->has('filter_submitted');
        
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->endOfMonth()->format('Y-m-d');
        $paymentMethod = $request->filled('payment_method') ? $request->payment_method : null;
        $classId = $request->filled('class_id') ? $request->class_id : null;
        
        // Ambil data kelas untuk filter
        $classes = DB::table('class_models')->orderBy('class_name')->get();
        
        // Hanya ambil data rekapitulasi jika ada filter yang di-submit
        if ($hasFilters) {
            $rekapitulasiData = $this->getRekapitulasiData($startDate, $endDate, $paymentMethod, $classId);
            
            // Hitung total
            $totalSetoran = $rekapitulasiData->sum('total_setoran');
            $totalPenarikan = $rekapitulasiData->sum('jumlah_penarikan');
            $totalSaldo = $rekapitulasiData->sum('saldo_akhir');
        } else {
            // Jika tidak ada filter, tampilkan data kosong
            $rekapitulasiData = collect([]);
            $totalSetoran = 0;
            $totalPenarikan = 0;
            $totalSaldo = 0;
        }
        
        return view('admin.rekapitulasi-tabungan.index', compact(
            'rekapitulasiData',
            'classes',
            'startDate',
            'endDate',
            'paymentMethod',
            'classId',
            'totalSetoran',
            'totalPenarikan',
            'totalSaldo',
            'hasFilters'
        ));
    }

    /**
     * Ambil data rekapitulasi tabungan
     */
    private function getRekapitulasiData($startDate, $endDate, $paymentMethod = null, $classId = null)
    {
        try {
            $result = collect();
            
            Log::info('Starting getRekapitulasiData with params:', [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'paymentMethod' => $paymentMethod,
                'classId' => $classId
            ]);

            // 1. Data dari tabel tabungan (saldo akhir)
            $tabunganQuery = DB::table('tabungan as t')
                ->join('students as s', 't.student_student_id', '=', 's.student_id')
                ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
                ->select(
                    's.student_id',
                    's.student_nis',
                    's.student_full_name',
                    'c.class_name',
                    't.saldo as saldo_akhir',
                    't.tabungan_last_update'
                );

            if ($classId) {
                $tabunganQuery->where('s.class_class_id', $classId);
            }

            $tabunganData = $tabunganQuery->orderBy('c.class_name')
                ->orderBy('s.student_full_name')
                ->get();

            foreach ($tabunganData as $tabungan) {
                $studentId = $tabungan->student_id;
                
                // 2. Hitung total setoran dan penarikan dari log_tabungan
                $logQuery = DB::table('log_tabungan as lt')
                    ->where('lt.student_student_id', $studentId)
                    ->whereRaw('DATE(lt.log_tabungan_input_date) >= ? AND DATE(lt.log_tabungan_input_date) <= ?', [$startDate, $endDate]);

                // Filter berdasarkan metode pembayaran jika ada
                if ($paymentMethod) {
                    $logQuery = $this->filterLogByPaymentMethod($logQuery, $paymentMethod);
                }

                $logData = $logQuery->select(
                    DB::raw('SUM(lt.kredit) as total_setoran'),
                    DB::raw('SUM(lt.debit) as total_penarikan')
                )->first();

                // 3. Data dari tabel transfer untuk pembayaran online
                $transferQuery = DB::table('transfer as tr')
                    ->join('transfer_detail as td', 'tr.transfer_id', '=', 'td.transfer_id')
                    ->where('tr.student_id', $studentId)
                    ->where('td.payment_type', 3) // Tabungan type
                    ->whereRaw('DATE(tr.created_at) >= ? AND DATE(tr.created_at) <= ?', [$startDate, $endDate]);

                // Filter transfer berdasarkan metode pembayaran jika ada
                if ($paymentMethod) {
                    $transferQuery = $this->filterTransferByPaymentMethod($transferQuery, $paymentMethod);
                }

                $transferData = $transferQuery->select(
                    DB::raw('SUM(td.subtotal) as total_transfer_setoran'),
                    DB::raw('COUNT(*) as jumlah_transaksi_transfer')
                )->first();

                // Pisahkan setoran berdasarkan metode pembayaran
                $setoranTunai = $this->getSetoranTunai($studentId, $startDate, $endDate, $paymentMethod);
                $setoranTransferBank = $this->getSetoranTransferBank($studentId, $startDate, $endDate, $paymentMethod);
                $setoranPaymentGateway = $this->getSetoranPaymentGateway($studentId, $startDate, $endDate, $paymentMethod);
                
                // Jika ada filter metode pembayaran, hanya hitung yang sesuai
                if ($paymentMethod) {
                    switch ($paymentMethod) {
                        case 'tunai':
                            $totalSetoran = $setoranTunai;
                            break;
                        case 'transfer_bank':
                            $totalSetoran = $setoranTransferBank;
                            break;
                        case 'payment_gateway':
                            $totalSetoran = $setoranPaymentGateway;
                            break;
                        default:
                            $totalSetoran = $setoranTunai + $setoranTransferBank + $setoranPaymentGateway;
                    }
                } else {
                    $totalSetoran = $setoranTunai + $setoranTransferBank + $setoranPaymentGateway;
                }
                
                // Hitung total penarikan - hanya untuk tunai atau jika tidak ada filter
                if (!$paymentMethod || $paymentMethod === 'tunai') {
                    $totalPenarikan = $logData->total_penarikan ?? 0;
                } else {
                    $totalPenarikan = 0; // Penarikan hanya untuk tunai
                }
                
                // Hitung saldo awal (saldo sebelum periode yang dipilih)
                $saldoAwal = $this->getSaldoAwal($studentId, $startDate);
                
                $result->push([
                    'student_id' => $studentId,
                    'student_nis' => $tabungan->student_nis,
                    'student_name' => $tabungan->student_full_name,
                    'class_name' => $tabungan->class_name,
                    'saldo_akhir' => $tabungan->saldo_akhir,
                    'saldo_awal' => $saldoAwal,
                    'setoran_tunai' => $setoranTunai,
                    'setoran_transfer_bank' => $setoranTransferBank,
                    'setoran_payment_gateway' => $setoranPaymentGateway,
                    'total_setoran' => $totalSetoran,
                    'jumlah_penarikan' => $totalPenarikan,
                    'jumlah_transaksi' => $this->getJumlahTransaksiTotal($studentId, $startDate, $endDate, $paymentMethod, $transferData->jumlah_transaksi_transfer ?? 0),
                    'last_update' => $tabungan->tabungan_last_update,
                    'detail_transaksi' => $this->getDetailTransaksi($studentId, $startDate, $endDate, $paymentMethod)
                ]);
            }

            Log::info('Rekapitulasi data result:', [
                'count' => $result->count(),
                'sample_data' => $result->take(3)->toArray()
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error in getRekapitulasiData: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return collect([]);
        }
    }

    /**
     * Filter log berdasarkan metode pembayaran
     */
    private function filterLogByPaymentMethod($query, $paymentMethod)
    {
        switch ($paymentMethod) {
            case 'tunai':
                return $query->where(function($q) {
                    $q->where('lt.keterangan', 'not like', '%Transfer Bank%')
                      ->where('lt.keterangan', 'not like', '%Payment Gateway%')
                      ->where('lt.keterangan', 'not like', '%via transfer bank%')
                      ->where('lt.keterangan', 'not like', '%via payment gateway%')
                      ->where('lt.keterangan', 'not like', '%Midtrans%')
                      ->where('lt.keterangan', 'not like', '%Online%')
                      ->where('lt.keterangan', 'not like', '%Penarikan%')
                      ->where('lt.keterangan', 'not like', '%Withdrawal%');
                });
            case 'transfer_bank':
                return $query->where(function($q) {
                    $q->where('lt.keterangan', 'like', '%Transfer Bank%')
                      ->orWhere('lt.keterangan', 'like', '%via transfer bank%')
                      ->orWhere('lt.keterangan', 'like', '%Bank Transfer%');
                });
            case 'payment_gateway':
                return $query->where(function($q) {
                    $q->where('lt.keterangan', 'like', '%Payment Gateway%')
                      ->orWhere('lt.keterangan', 'like', '%via payment gateway%')
                      ->orWhere('lt.keterangan', 'like', '%Midtrans%')
                      ->orWhere('lt.keterangan', 'like', '%Online%');
                });
            default:
                return $query;
        }
    }

    /**
     * Filter transfer berdasarkan metode pembayaran
     */
    private function filterTransferByPaymentMethod($query, $paymentMethod)
    {
        switch ($paymentMethod) {
            case 'tunai':
                // Untuk tunai, tidak ada data di tabel transfer
                return $query->whereRaw('1 = 0'); // Return empty result
            case 'transfer_bank':
                return $query->where(function($q) {
                    $q->where('tr.metode_pembayaran_tabungan', 'transfer_bank')
                      ->orWhere('tr.payment_method', 'bank_transfer')
                      ->orWhere('tr.payment_method', 'manual_transfer')
                      ->orWhere('tr.payment_method', 'transfer');
                });
            case 'payment_gateway':
                return $query->where(function($q) {
                    $q->where('tr.metode_pembayaran_tabungan', 'payment_gateway')
                      ->orWhere('tr.payment_method', 'midtrans')
                      ->orWhere('tr.payment_method', 'credit_card')
                      ->orWhere('tr.payment_method', 'e_wallet')
                      ->orWhere('tr.payment_method', 'online_payment')
                      ->orWhere('tr.payment_method', 'online');
                });
            default:
                return $query;
        }
    }



    /**
     * Hitung saldo awal sebelum periode yang dipilih
     */
    private function getSaldoAwal($studentId, $startDate)
    {
        try {
            // Ambil saldo dari tabel tabungan (saldo terakhir)
            $saldoTerakhir = DB::table('tabungan')
                ->where('student_student_id', $studentId)
                ->value('saldo') ?? 0;
            
            // Hitung total setoran dan penarikan sebelum tanggal mulai
            $setoranSebelum = DB::table('log_tabungan')
                ->where('student_student_id', $studentId)
                ->where('kredit', '>', 0)
                ->whereRaw('DATE(log_tabungan_input_date) < ?', [$startDate])
                ->sum('kredit');
            
            $penarikanSebelum = DB::table('log_tabungan')
                ->where('student_student_id', $studentId)
                ->where('debit', '>', 0)
                ->whereRaw('DATE(log_tabungan_input_date) < ?', [$startDate])
                ->sum('debit');
            
            // Setoran dari transfer sebelum periode
            $setoranTransferSebelum = DB::table('transfer as tr')
                ->join('transfer_detail as td', 'tr.transfer_id', '=', 'td.transfer_id')
                ->where('tr.student_id', $studentId)
                ->where('td.payment_type', 3) // Tabungan
                ->whereRaw('DATE(tr.created_at) < ?', [$startDate])
                ->sum('td.subtotal');
            
            // Setoran tunai sebelum periode (jika tabel receipt_pos ada)
            $setoranTunaiSebelum = 0;
            try {
                $setoranTunaiSebelum = DB::table('receipt_pos')
                    ->where('student_id', $studentId)
                    ->where('payment_type', 3) // Tabungan
                    ->whereRaw('DATE(created_at) < ?', [$startDate])
                    ->sum('total_amount');
            } catch (\Exception $e) {
                // Tabel receipt_pos mungkin tidak ada, abaikan
                Log::info("Table receipt_pos not found or not accessible for student $studentId");
            }
            
            // Saldo awal = saldo terakhir - (setoran + penarikan) dalam periode yang dipilih
            $saldoAwal = $saldoTerakhir - ($setoranSebelum + $setoranTransferSebelum + $setoranTunaiSebelum) + $penarikanSebelum;
            
            return max(0, $saldoAwal); // Pastikan tidak negatif
            
        } catch (\Exception $e) {
            Log::warning('Error calculating saldo awal: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil setoran tunai dari log_tabungan
     */
    private function getSetoranTunai($studentId, $startDate, $endDate, $paymentMethod)
    {
        try {
            if ($paymentMethod && $paymentMethod !== 'tunai') {
                return 0;
            }

            // Cari setoran tunai di log_tabungan - semua kredit adalah setoran kecuali yang jelas bukan setoran tunai
            $setoranTunai = DB::table('log_tabungan')
                ->where('student_student_id', $studentId)
                ->where('kredit', '>', 0)
                ->whereRaw('DATE(log_tabungan_input_date) >= ? AND DATE(log_tabungan_input_date) <= ?', [$startDate, $endDate])
                ->where('keterangan', 'not like', '%Transfer Bank%')
                ->where('keterangan', 'not like', '%Payment Gateway%')
                ->where('keterangan', 'not like', '%via transfer bank%')
                ->where('keterangan', 'not like', '%via payment gateway%')
                ->where('keterangan', 'not like', '%Midtrans%')
                ->where('keterangan', 'not like', '%Online%')
                ->where('keterangan', 'not like', '%Penarikan%')
                ->where('keterangan', 'not like', '%Withdrawal%')
                ->sum('kredit');

            Log::info("Setoran Tunai for student $studentId: $setoranTunai");
            return $setoranTunai;

        } catch (\Exception $e) {
            Log::warning('Error getting setoran tunai: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil setoran transfer bank dari log_tabungan dan transfer
     */
    private function getSetoranTransferBank($studentId, $startDate, $endDate, $paymentMethod)
    {
        try {
            if ($paymentMethod && $paymentMethod !== 'transfer_bank') {
                return 0;
            }

            // Cari setoran transfer bank di log_tabungan
            $setoranLog = DB::table('log_tabungan')
                ->where('student_student_id', $studentId)
                ->where('kredit', '>', 0)
                ->whereRaw('DATE(log_tabungan_input_date) >= ? AND DATE(log_tabungan_input_date) <= ?', [$startDate, $endDate])
                ->where('keterangan', 'like', '%Transfer Bank%')
                ->sum('kredit');

            // Cari setoran transfer bank di tabel transfer
            $setoranTransfer = DB::table('transfer as tr')
                ->join('transfer_detail as td', 'tr.transfer_id', '=', 'td.transfer_id')
                ->where('tr.student_id', $studentId)
                ->where('td.payment_type', 3) // Tabungan
                ->whereRaw('DATE(tr.created_at) >= ? AND DATE(tr.created_at) <= ?', [$startDate, $endDate])
                ->where(function($query) {
                    $query->where('tr.metode_pembayaran_tabungan', 'transfer_bank')
                          ->orWhere('tr.payment_method', 'bank_transfer')
                          ->orWhere('tr.payment_method', 'manual_transfer');
                })
                ->sum('td.subtotal');

            $totalSetoran = $setoranLog + $setoranTransfer;
            Log::info("Setoran Transfer Bank for student $studentId: Log: $setoranLog, Transfer: $setoranTransfer, Total: $totalSetoran");
            return $totalSetoran;

        } catch (\Exception $e) {
            Log::warning('Error getting setoran transfer bank: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil setoran payment gateway dari log_tabungan dan transfer
     */
    private function getSetoranPaymentGateway($studentId, $startDate, $endDate, $paymentMethod)
    {
        try {
            if ($paymentMethod && $paymentMethod !== 'payment_gateway') {
                return 0;
            }

            // Cari setoran payment gateway di log_tabungan
            $setoranLog = DB::table('log_tabungan')
                ->where('student_student_id', $studentId)
                ->where('kredit', '>', 0)
                ->whereRaw('DATE(log_tabungan_input_date) >= ? AND DATE(log_tabungan_input_date) <= ?', [$startDate, $endDate])
                ->where('keterangan', 'like', '%Payment Gateway%')
                ->sum('kredit');

            // Cari setoran payment gateway di tabel transfer
            $setoranTransfer = DB::table('transfer as tr')
                ->join('transfer_detail as td', 'tr.transfer_id', '=', 'td.transfer_id')
                ->where('tr.student_id', $studentId)
                ->where('td.payment_type', 3) // Tabungan
                ->whereRaw('DATE(tr.created_at) >= ? AND DATE(tr.created_at) <= ?', [$startDate, $endDate])
                ->where(function($query) {
                    $query->where('tr.metode_pembayaran_tabungan', 'payment_gateway')
                          ->orWhere('tr.payment_method', 'midtrans')
                          ->orWhere('tr.payment_method', 'credit_card')
                          ->orWhere('tr.payment_method', 'e_wallet')
                          ->orWhere('tr.payment_method', 'online_payment');
                })
                ->sum('td.subtotal');

            $totalSetoran = $setoranLog + $setoranTransfer;
            Log::info("Setoran Payment Gateway for student $studentId: Log: $setoranLog, Transfer: $setoranTransfer, Total: $totalSetoran");
            return $totalSetoran;

        } catch (\Exception $e) {
            Log::warning('Error getting setoran payment gateway: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil jumlah transaksi tunai
     */
    private function getJumlahTransaksiTunai($studentId, $startDate, $endDate, $paymentMethod)
    {
        try {
            if ($paymentMethod && $paymentMethod !== 'tunai') {
                return 0;
            }

            // Hitung jumlah transaksi tunai di log_tabungan - semua kredit adalah setoran kecuali yang jelas bukan setoran tunai
            $jumlahTransaksi = DB::table('log_tabungan')
                ->where('student_student_id', $studentId)
                ->where('kredit', '>', 0)
                ->whereRaw('DATE(log_tabungan_input_date) >= ? AND DATE(log_tabungan_input_date) <= ?', [$startDate, $endDate])
                ->where('keterangan', 'not like', '%Transfer Bank%')
                ->where('keterangan', 'not like', '%Payment Gateway%')
                ->where('keterangan', 'not like', '%via transfer bank%')
                ->where('keterangan', 'not like', '%via payment gateway%')
                ->where('keterangan', 'not like', '%Midtrans%')
                ->where('keterangan', 'not like', '%Online%')
                ->where('keterangan', 'not like', '%Penarikan%')
                ->where('keterangan', 'not like', '%Withdrawal%')
                ->count();

            return $jumlahTransaksi;

        } catch (\Exception $e) {
            Log::warning('Error getting jumlah transaksi tunai: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil jumlah transaksi total berdasarkan filter metode pembayaran
     */
    private function getJumlahTransaksiTotal($studentId, $startDate, $endDate, $paymentMethod, $transferCount)
    {
        try {
            if ($paymentMethod) {
                switch ($paymentMethod) {
                    case 'tunai':
                        return $this->getJumlahTransaksiTunai($studentId, $startDate, $endDate, $paymentMethod);
                    case 'transfer_bank':
                    case 'payment_gateway':
                        return $transferCount;
                    default:
                        return $this->getJumlahTransaksiTunai($studentId, $startDate, $endDate, $paymentMethod) + $transferCount;
                }
            } else {
                return $this->getJumlahTransaksiTunai($studentId, $startDate, $endDate, $paymentMethod) + $transferCount;
            }
        } catch (\Exception $e) {
            Log::warning('Error getting jumlah transaksi total: ' . $e->getMessage());
            return 0;
        }
    }





    /**
     * Ambil detail transaksi untuk siswa tertentu (hanya dari log_tabungan)
     */
    private function getDetailTransaksi($studentId, $startDate, $endDate, $paymentMethod)
    {
        try {
            // Hanya ambil transaksi dari log_tabungan
            $logQuery = DB::table('log_tabungan as lt')
                ->where('lt.student_student_id', $studentId)
                ->whereRaw('DATE(lt.log_tabungan_input_date) >= ? AND DATE(lt.log_tabungan_input_date) <= ?', [$startDate, $endDate]);

            if ($paymentMethod) {
                $logQuery = $this->filterLogByPaymentMethod($logQuery, $paymentMethod);
            }

            $logTransaksi = $logQuery->get()->map(function ($item) {
                return [
                    'tanggal' => $item->log_tabungan_input_date,
                    'kredit' => $item->kredit,
                    'debit' => $item->debit,
                    'keterangan' => $item->keterangan,
                    'sumber' => 'log_tabungan',
                    'metode_pembayaran' => $this->getPaymentMethodFromKeterangan($item->keterangan)
                ];
            });

            // Urutkan berdasarkan tanggal
            return $logTransaksi->sortBy('tanggal')->values();

        } catch (\Exception $e) {
            Log::error('Error getting detail transaksi: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Export rekapitulasi ke PDF
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->endOfMonth()->format('Y-m-d');
        $paymentMethod = $request->filled('payment_method') ? $request->payment_method : null;
        $classId = $request->filled('class_id') ? $request->class_id : null;

        $rekapitulasiData = $this->getRekapitulasiData($startDate, $endDate, $paymentMethod, $classId);
        
        // Ambil identitas sekolah
        $school = DB::table('school_profiles')->first();

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.rekapitulasi-tabungan.pdf', compact(
            'rekapitulasiData',
            'startDate',
            'endDate',
            'paymentMethod',
            'school'
        ));
        
        $filename = 'Rekapitulasi_Tabungan_' . $startDate . '_' . $endDate . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Ambil detail transaksi untuk siswa tertentu (API endpoint)
     */
    public function getDetailTransaksiApi($studentId, Request $request)
    {
        try {
            $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->endOfMonth()->format('Y-m-d');
            $paymentMethod = $request->filled('payment_method') ? $request->payment_method : null;

            $transactions = $this->getDetailTransaksi($studentId, $startDate, $endDate, $paymentMethod);

            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getDetailTransaksiApi: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail transaksi'
            ], 500);
        }
    }

    /**
     * Tentukan metode pembayaran berdasarkan keterangan log tabungan
     */
    private function getPaymentMethodFromKeterangan($keterangan)
    {
        if (empty($keterangan)) {
            return 'Tunai';
        }

        $keterangan = strtolower($keterangan);
        
        // Cek untuk Transfer Bank
        if (strpos($keterangan, 'transfer bank') !== false || 
            strpos($keterangan, 'bank transfer') !== false ||
            strpos($keterangan, 'manual transfer') !== false ||
            strpos($keterangan, 'via transfer bank') !== false) {
            return 'Transfer Bank';
        }
        
        // Cek untuk Payment Gateway
        if (strpos($keterangan, 'payment gateway') !== false || 
            strpos($keterangan, 'midtrans') !== false ||
            strpos($keterangan, 'online') !== false ||
            strpos($keterangan, 'credit card') !== false ||
            strpos($keterangan, 'e-wallet') !== false ||
            strpos($keterangan, 'via payment gateway') !== false) {
            return 'Payment Gateway';
        }
        
        // Default untuk setoran manual/tunai
        return 'Tunai';
    }



    /**
     * Export rekapitulasi ke Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->endOfMonth()->format('Y-m-d');
            $paymentMethod = $request->filled('payment_method') ? $request->payment_method : null;
            $classId = $request->filled('class_id') ? $request->class_id : null;

            $rekapitulasiData = $this->getRekapitulasiData($startDate, $endDate, $paymentMethod, $classId);
            
            // Hitung total
            $totalSetoran = $rekapitulasiData->sum('total_setoran');
            $totalPenarikan = $rekapitulasiData->sum('jumlah_penarikan');
            $totalSaldo = $rekapitulasiData->sum('saldo_akhir');

            // Generate filename
            $filename = 'Rekapitulasi_Tabungan_' . $startDate . '_' . $endDate . '.xlsx';
            
            // Export menggunakan Laravel Excel
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\RekapitulasiTabunganExport(
                    $rekapitulasiData,
                    $startDate,
                    $endDate,
                    $paymentMethod,
                    $totalSetoran,
                    $totalPenarikan,
                    $totalSaldo
                ),
                $filename
            );

        } catch (\Exception $e) {
            Log::error('Error exporting Excel: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat export Excel: ' . $e->getMessage()
            ], 500);
        }
    }
}
