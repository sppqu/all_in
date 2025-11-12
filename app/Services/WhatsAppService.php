<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WhatsAppService
{
    protected $apiKey;
    protected $url;
    protected $sender;

    public function __construct($schoolId = null)
    {
        // Get school_id from parameter or from currentSchoolId() helper
        $currentSchoolId = $schoolId ?? currentSchoolId();
        
        // Ambil konfigurasi dari setup_gateways berdasarkan school_id
        if ($currentSchoolId) {
            $gateway = DB::table('setup_gateways')
                ->where('school_id', $currentSchoolId)
                ->first();
        } else {
            // Fallback: ambil dari sekolah pertama jika school_id tidak tersedia (untuk backward compatibility)
            $gateway = DB::table('setup_gateways')->first();
            Log::warning('WhatsApp gateway: school_id tidak tersedia, menggunakan gateway pertama');
        }
        
        if ($gateway) {
            $this->apiKey = $gateway->apikey_wagateway ?? '';
            $this->url = $gateway->url_wagateway ?? '';
            $this->sender = $gateway->sender_wagateway ?? '';
            
            Log::info('WhatsApp gateway configuration loaded:', [
                'school_id' => $currentSchoolId ?? 'NULL',
                'has_api_key' => !empty($this->apiKey),
                'has_url' => !empty($this->url),
                'has_sender' => !empty($this->sender),
                'enable_wa_notification' => $gateway->enable_wa_notification ?? false,
                'url' => $this->url
            ]);
        } else {
            $this->apiKey = '';
            $this->url = '';
            $this->sender = '';
            Log::warning('WhatsApp gateway configuration not found in setup_gateways table', [
                'school_id' => $currentSchoolId ?? 'NULL'
            ]);
        }
    }

    /**
     * Kirim notifikasi pembayaran sukses untuk pembayaran non-tunai (dengan transfer record)
     */
    public function sendPaymentSuccessNotification($transferId)
    {
        try {
            Log::info("Starting WhatsApp notification for transfer_id: {$transferId}");
            
            // Ambil data transfer dan siswa
            $transfer = DB::table('transfer as t')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->where('t.transfer_id', $transferId)
                ->select(
                    't.*',
                    's.student_full_name as student_name',
                    's.student_parent_phone',
                    's.student_nis as nis',
                    't.payment_number as reference',
                    't.confirm_pay as amount',
                    't.created_at'
                )
                ->first();

            if (!$transfer) {
                Log::warning("WhatsApp notification failed: No transfer data found for transfer_id: {$transferId}");
                return false;
            }

            if (!$transfer->student_parent_phone) {
                Log::warning("WhatsApp notification failed: No parent phone for transfer_id: {$transferId}");
                return false;
            }

            // Ambil semua transfer detail untuk multi POS
            $transferDetails = DB::table('transfer_detail as td')
                ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->leftJoin('pos_pembayaran as pos_bulan', 'p_bulan.pos_pos_id', '=', 'pos_bulan.pos_id')
                ->leftJoin('pos_pembayaran as pos_bebas', 'p_bebas.pos_pos_id', '=', 'pos_bebas.pos_id')
                ->leftJoin('periods as per_bulan', 'p_bulan.period_period_id', '=', 'per_bulan.period_id')
                ->leftJoin('periods as per_bebas', 'p_bebas.period_period_id', '=', 'per_bebas.period_id')
                ->where('td.transfer_id', $transferId)
                ->select(
                    'td.payment_type',
                    'td.subtotal',
                    DB::raw('CASE 
                        WHEN td.payment_type = 3 THEN "Setoran Tabungan"
                        WHEN b.bulan_id IS NOT NULL THEN CONCAT(pos_bulan.pos_name, "-", 
                            CASE b.month_month_id
                                WHEN 1 THEN "Juli"
                                WHEN 2 THEN "Agustus"
                                WHEN 3 THEN "September"
                                WHEN 4 THEN "Oktober"
                                WHEN 5 THEN "November"
                                WHEN 6 THEN "Desember"
                                WHEN 7 THEN "Januari"
                                WHEN 8 THEN "Februari"
                                WHEN 9 THEN "Maret"
                                WHEN 10 THEN "April"
                                WHEN 11 THEN "Mei"
                                WHEN 12 THEN "Juni"
                                ELSE "Unknown"
                            END, " (", COALESCE(CONCAT(per_bulan.period_start, "/", per_bulan.period_end), "2025/2026"), ")"
                        )
                        WHEN be.bebas_id IS NOT NULL THEN CONCAT(pos_bebas.pos_name, " - ", COALESCE(CONCAT(per_bebas.period_start, "/", per_bebas.period_end), "2025/2026"))
                        ELSE "Pembayaran Online"
                    END as pos_name')
                )
                ->get();

            // Gabungkan semua POS names
            $posNames = [];
            foreach ($transferDetails as $detail) {
                if ($detail->pos_name && !in_array($detail->pos_name, $posNames)) {
                    $posNames[] = $detail->pos_name;
                }
            }

            // Buat pos_name yang digabungkan
            $combinedPosName = count($posNames) > 1 ? implode(", ", $posNames) : ($posNames[0] ?? 'Pembayaran Online');

            // Gabungkan data untuk message
            $payment = (object) [
                'student_name' => $transfer->student_name,
                'student_parent_phone' => $transfer->student_parent_phone,
                'nis' => $transfer->nis,
                'reference' => $transfer->reference,
                'amount' => $transfer->amount,
                'created_at' => $transfer->created_at,
                'pos_name' => $combinedPosName
            ];

            Log::info("Payment data retrieved:", [
                'transfer_id' => $transferId,
                'payment_found' => $payment ? 'YES' : 'NO',
                'student_name' => $payment->student_name ?? 'NOT_FOUND',
                'parent_phone' => $payment->student_parent_phone ?? 'NOT_FOUND',
                'amount' => $payment->amount ?? 'NOT_FOUND',
                'pos_name' => $payment->pos_name ?? 'NOT_FOUND',
                'transfer_details_count' => $transferDetails->count(),
                'pos_names' => $posNames
            ]);

            if (!$payment) {
                Log::warning("WhatsApp notification failed: No payment data found for transfer_id: {$transferId}");
                return false;
            }

            if (!$payment->student_parent_phone) {
                Log::warning("WhatsApp notification failed: No parent phone for transfer_id: {$transferId}");
                return false;
            }

            // Format nomor telepon
            $phone = $this->formatPhoneNumber($payment->student_parent_phone);
            Log::info("Formatted phone number:", ['original' => $payment->student_parent_phone, 'formatted' => $phone]);
            
            // Buat pesan
            $message = $this->createPaymentSuccessMessage($payment);
            Log::info("Message created:", ['message_length' => strlen($message)]);
            
            // Kirim pesan
            $result = $this->sendMessage($phone, $message);
            Log::info("Message send result:", ['success' => $result ? 'YES' : 'NO']);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("WhatsApp notification error: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Kirim notifikasi pembayaran sukses untuk pembayaran tunai (tanpa transfer record)
     */
    public function sendPaymentSuccessNotificationWithoutTransfer($studentId, $paymentNumber, $amount, $billType, $billId)
    {
        try {
            Log::info("Starting WhatsApp notification for cash payment - student_id: {$studentId}, bill_type: {$billType}, bill_id: {$billId}");
            
            // Ambil data siswa terlebih dahulu
            $student = DB::table('students')
                ->where('student_id', $studentId)
                ->select('student_full_name', 'student_parent_phone', 'student_nis')
                ->first();
            
            if (!$student) {
                Log::warning("WhatsApp notification failed: No student data found for student_id: {$studentId}");
                return false;
            }
            
            if (!$student->student_parent_phone) {
                Log::warning("WhatsApp notification failed: No parent phone for student_id: {$studentId}");
                return false;
            }
            
            // Ambil data POS dan periode berdasarkan bill type
            $posName = 'Pembayaran Tunai';
            $periodInfo = '';
            
            if ($billType === 'bulanan') {
                $bulanData = DB::table('bulan as b')
                    ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                    ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                    ->where('b.bulan_id', $billId)
                    ->select('pos.pos_name', 'b.month_month_id', 'per.period_start', 'per.period_end')
                    ->first();
                
                if ($bulanData) {
                    $monthNames = [
                        1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
                        5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
                        9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                    ];
                    
                    $monthName = $monthNames[$bulanData->month_month_id] ?? 'Unknown';
                    $periodInfo = $bulanData->period_start && $bulanData->period_end 
                        ? " ({$bulanData->period_start}/{$bulanData->period_end})" 
                        : " (2025/2026)";
                    
                    $posName = "{$bulanData->pos_name}-{$monthName}{$periodInfo}";
                }
            } elseif ($billType === 'bebas') {
                // Untuk bebas, $billId adalah bebas_pay_id, jadi kita perlu ambil bebas_id dulu
                Log::info("Processing bebas payment notification", [
                    'bill_id' => $billId,
                    'bill_type' => $billType
                ]);
                
                $bebasPayData = DB::table('bebas_pay')
                    ->where('bebas_pay_id', $billId)
                    ->select('bebas_bebas_id')
                    ->first();
                
                Log::info("Bebas pay data retrieved", [
                    'bebas_pay_id' => $billId,
                    'bebas_bebas_id' => $bebasPayData ? $bebasPayData->bebas_bebas_id : 'NOT_FOUND'
                ]);
                
                if ($bebasPayData) {
                $bebasData = DB::table('bebas as be')
                    ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                    ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                        ->where('be.bebas_id', $bebasPayData->bebas_bebas_id)
                    ->select('pos.pos_name', 'per.period_start', 'per.period_end')
                    ->first();
                
                    Log::info("Bebas data retrieved", [
                        'bebas_id' => $bebasPayData->bebas_bebas_id,
                        'pos_name' => $bebasData ? $bebasData->pos_name : 'NOT_FOUND',
                        'period_start' => $bebasData ? $bebasData->period_start : 'NOT_FOUND',
                        'period_end' => $bebasData ? $bebasData->period_end : 'NOT_FOUND'
                    ]);
                    
                if ($bebasData) {
                    $periodInfo = $bebasData->period_start && $bebasData->period_end 
                        ? " ({$bebasData->period_start}/{$bebasData->period_end})" 
                        : " (2025/2026)";
                    
                    $posName = "{$bebasData->pos_name}{$periodInfo}";
                        
                        Log::info("Final pos name constructed", [
                            'pos_name' => $posName,
                            'period_info' => $periodInfo
                        ]);
                    }
                }
            }
            
            Log::info("Cash payment data retrieved:", [
                'student_id' => $studentId,
                'student_name' => $student->student_full_name,
                'parent_phone' => $student->student_parent_phone,
                'amount' => $amount,
                'pos_name' => $posName,
                'bill_type' => $billType,
                'bill_id' => $billId
            ]);
            
            // Format nomor telepon
            $phone = $this->formatPhoneNumber($student->student_parent_phone);
            Log::info("Formatted phone number:", ['original' => $student->student_parent_phone, 'formatted' => $phone]);
            
            // Buat pesan notifikasi
            $message = $this->createCashPaymentSuccessMessage(
                $student->student_full_name,
                $student->student_nis,
                $posName,
                $amount,
                $paymentNumber
            );

            Log::info("Cash payment notification message created:", ['message_length' => strlen($message)]);
            
            return $this->sendMessage($phone, $message);
        } catch (\Exception $e) {
            Log::error("Error in sendPaymentSuccessNotificationWithoutTransfer: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Kirim notifikasi pembayaran pending
     */
    public function sendPaymentPendingNotification($transferId)
    {
        try {
            // Ambil data transfer dan siswa
            $transfer = DB::table('transfer as t')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->where('t.transfer_id', $transferId)
                ->select(
                    't.*',
                    's.student_full_name as student_name',
                    's.student_parent_phone',
                    's.student_nis as nis',
                    't.payment_number as reference',
                    't.confirm_pay as amount',
                    't.created_at'
                )
                ->first();

            if (!$transfer || !$transfer->student_parent_phone) {
                Log::warning("WhatsApp notification failed: No transfer data or parent phone for transfer_id: {$transferId}");
                return false;
            }

            // Ambil semua transfer detail untuk multi POS
            $transferDetails = DB::table('transfer_detail as td')
                ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->leftJoin('pos_pembayaran as pos_bulan', 'p_bulan.pos_pos_id', '=', 'pos_bulan.pos_id')
                ->leftJoin('pos_pembayaran as pos_bebas', 'p_bebas.pos_pos_id', '=', 'pos_bebas.pos_id')
                ->leftJoin('periods as per_bulan', 'p_bulan.period_period_id', '=', 'per_bulan.period_id')
                ->leftJoin('periods as per_bebas', 'p_bebas.period_period_id', '=', 'per_bebas.period_id')
                ->where('td.transfer_id', $transferId)
                ->select(
                    'td.payment_type',
                    'td.subtotal',
                    DB::raw('CASE 
                        WHEN td.payment_type = 3 THEN "Setoran Tabungan"
                        WHEN b.bulan_id IS NOT NULL THEN CONCAT(pos_bulan.pos_name, "-", 
                            CASE b.month_month_id
                                WHEN 1 THEN "Juli"
                                WHEN 2 THEN "Agustus"
                                WHEN 3 THEN "September"
                                WHEN 4 THEN "Oktober"
                                WHEN 5 THEN "November"
                                WHEN 6 THEN "Desember"
                                WHEN 7 THEN "Januari"
                                WHEN 8 THEN "Februari"
                                WHEN 9 THEN "Maret"
                                WHEN 10 THEN "April"
                                WHEN 11 THEN "Mei"
                                WHEN 12 THEN "Juni"
                                ELSE "Unknown"
                            END, " (", COALESCE(CONCAT(per_bulan.period_start, "/", per_bulan.period_end), "2025/2026"), ")"
                        )
                        WHEN be.bebas_id IS NOT NULL THEN CONCAT(pos_bebas.pos_name, " - ", COALESCE(CONCAT(per_bebas.period_start, "/", per_bebas.period_end), "2025/2026"))
                        ELSE "Pembayaran Online"
                    END as pos_name')
                )
                ->get();

            // Gabungkan semua POS names
            $posNames = [];
            foreach ($transferDetails as $detail) {
                if ($detail->pos_name && !in_array($detail->pos_name, $posNames)) {
                    $posNames[] = $detail->pos_name;
                }
            }

            // Buat pos_name yang digabungkan
            $combinedPosName = count($posNames) > 1 ? implode(", ", $posNames) : ($posNames[0] ?? 'Pembayaran Online');

            // Gabungkan data untuk message
            $payment = (object) [
                'student_name' => $transfer->student_name,
                'student_parent_phone' => $transfer->student_parent_phone,
                'nis' => $transfer->nis,
                'reference' => $transfer->reference,
                'amount' => $transfer->amount,
                'created_at' => $transfer->created_at,
                'pos_name' => $combinedPosName
            ];

            Log::info("Payment pending data retrieved:", [
                'transfer_id' => $transferId,
                'payment_found' => $payment ? 'YES' : 'NO',
                'student_name' => $payment->student_name ?? 'NOT_FOUND',
                'parent_phone' => $payment->student_parent_phone ?? 'NOT_FOUND',
                'amount' => $payment->amount ?? 'NOT_FOUND',
                'pos_name' => $payment->pos_name ?? 'NOT_FOUND',
                'transfer_details_count' => $transferDetails->count(),
                'pos_names' => $posNames
            ]);

            // Format nomor telepon
            $phone = $this->formatPhoneNumber($payment->student_parent_phone);
            
            // Buat pesan
            $message = $this->createPaymentPendingMessage($payment);
            
            // Kirim pesan
            return $this->sendMessage($phone, $message);
            
        } catch (\Exception $e) {
            Log::error("WhatsApp notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim pesan custom untuk testing
     */
    public function sendCustomMessage($phoneNumber, $message)
    {
        try {
            // Format nomor telepon
            $phone = $this->formatPhoneNumber($phoneNumber);
            
            // Kirim pesan custom
            return $this->sendMessage($phone, $message);
            
        } catch (\Exception $e) {
            Log::error("WhatsApp custom message error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi penghapusan transaksi online
     */
    public function sendOnlineTransactionDeletedNotification($transaction, $paymentType)
    {
        try {
            if (!$transaction || !$transaction->student_parent_phone) {
                Log::warning("WhatsApp online deletion notification failed: No transaction data or parent phone");
                return false;
            }

            // Format nomor telepon
            $phone = $this->formatPhoneNumber($transaction->student_parent_phone);
            
            // Buat pesan
            $message = $this->createOnlineTransactionDeletedMessage($transaction, $paymentType);
            
            // Kirim pesan
            return $this->sendMessage($phone, $message);
            
        } catch (\Exception $e) {
            Log::error("WhatsApp online deletion notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi penghapusan transaksi dengan data langsung
     */
    public function sendTransactionDeletedNotificationDirect($transactionData, $paymentType)
    {
        try {
            Log::info('Starting WhatsApp deletion notification with data:', [
                'transaction_data' => $transactionData,
                'payment_type' => $paymentType
            ]);

            if (!$transactionData || !$transactionData->student_parent_phone) {
                Log::warning("WhatsApp deletion notification failed: No transaction data or parent phone");
                return false;
            }

            // Format nomor telepon
            $phone = $this->formatPhoneNumber($transactionData->student_parent_phone);
            Log::info('Formatted phone number:', ['phone' => $phone]);
            
            // Buat pesan
            $message = $this->createTransactionDeletedMessageDirect($transactionData, $paymentType);
            Log::info('Created message:', ['message' => $message]);
            
            // Kirim pesan
            $result = $this->sendMessage($phone, $message);
            Log::info('Message send result:', ['result' => $result]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("WhatsApp deletion notification error: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Kirim notifikasi penghapusan transaksi
     */
    public function sendTransactionDeletedNotification($transactionId, $studentId, $paymentType)
    {
        try {
            // Ambil data transaksi yang dihapus
            $transaction = DB::table('log_trx as lt')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->leftJoin('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->leftJoin('pos_pembayaran as pos_bulan', 'p_bulan.pos_pos_id', '=', 'pos_bulan.pos_id')
                ->leftJoin('pos_pembayaran as pos_bebas', 'p_bebas.pos_pos_id', '=', 'pos_bebas.pos_id')
                ->leftJoin('periods as per_bulan', 'p_bulan.period_period_id', '=', 'per_bulan.period_id')
                ->leftJoin('periods as per_bebas', 'p_bebas.period_period_id', '=', 'per_bebas.period_id')
                ->where('lt.log_trx_id', $transactionId)
                ->where('lt.student_student_id', $studentId)
                ->select(
                    's.student_full_name as student_name',
                    's.student_parent_phone',
                    's.student_nis as nis',
                    'lt.log_trx_input_date as payment_date',
                    DB::raw('COALESCE(b.bulan_bill, bp.bebas_pay_bill) as amount'),
                    DB::raw('COALESCE(b.bulan_number_pay, bp.bebas_pay_number) as payment_number'),
                    DB::raw('CASE 
                        WHEN b.bulan_id IS NOT NULL THEN CONCAT(pos_bulan.pos_name, "-", 
                            CASE b.month_month_id
                                WHEN 1 THEN "Januari"
                                WHEN 2 THEN "Februari"
                                WHEN 3 THEN "Maret"
                                WHEN 4 THEN "April"
                                WHEN 5 THEN "Mei"
                                WHEN 6 THEN "Juni"
                                WHEN 7 THEN "Juli"
                                WHEN 8 THEN "Agustus"
                                WHEN 9 THEN "September"
                                WHEN 10 THEN "Oktober"
                                WHEN 11 THEN "November"
                                WHEN 12 THEN "Desember"
                                ELSE "Unknown"
                            END, " (", COALESCE(CONCAT(per_bulan.period_start, "/", per_bulan.period_end), "2025/2026"), ")"
                        )
                        WHEN bp.bebas_pay_id IS NOT NULL THEN CONCAT(pos_bebas.pos_name, " - ", COALESCE(CONCAT(per_bebas.period_start, "/", per_bebas.period_end), "2025/2026"))
                        ELSE "Pembayaran"
                    END as pos_name'),
                    DB::raw("'{$paymentType}' as payment_type")
                )
                ->first();

            if (!$transaction || !$transaction->student_parent_phone) {
                Log::warning("WhatsApp deletion notification failed: No transaction data or parent phone for transaction_id: {$transactionId}");
                return false;
            }

            // Format nomor telepon
            $phone = $this->formatPhoneNumber($transaction->student_parent_phone);
            
            // Buat pesan
            $message = $this->createTransactionDeletedMessage($transaction);
            
            // Kirim pesan
            return $this->sendMessage($phone, $message);
            
        } catch (\Exception $e) {
            Log::error("WhatsApp deletion notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi setoran tabungan sukses
     */
    public function sendTabunganSuccessNotification($transferId)
    {
        try {
            Log::info("Starting WhatsApp tabungan notification for transfer_id: {$transferId}");
            
            // Ambil data setoran tabungan
            $payment = DB::table('transfer as t')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->leftJoin('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
                ->where('t.transfer_id', $transferId)
                ->where('td.payment_type', 3) // Tabungan type
                ->select(
                    't.*',
                    's.student_full_name as student_name',
                    's.student_parent_phone',
                    's.student_nis as nis',
                    't.payment_number as reference',
                    't.confirm_pay as amount',
                    't.created_at',
                    'td.desc as description'
                )
                ->first();

            Log::info("Tabungan payment data retrieved:", [
                'transfer_id' => $transferId,
                'payment_found' => $payment ? 'YES' : 'NO',
                'student_name' => $payment->student_name ?? 'NOT_FOUND',
                'parent_phone' => $payment->student_parent_phone ?? 'NOT_FOUND',
                'amount' => $payment->amount ?? 'NOT_FOUND'
            ]);

            if (!$payment) {
                Log::warning("WhatsApp tabungan notification failed: No payment data found for transfer_id: {$transferId}");
                return false;
            }

            if (!$payment->student_parent_phone) {
                Log::warning("WhatsApp tabungan notification failed: No parent phone for transfer_id: {$transferId}");
                return false;
            }

            // Format nomor telepon
            $phone = $this->formatPhoneNumber($payment->student_parent_phone);
            Log::info("Formatted phone number for tabungan:", ['original' => $payment->student_parent_phone, 'formatted' => $phone]);
            
            // Buat pesan
            $message = $this->createTabunganSuccessMessage($payment);
            
            // Kirim pesan
            $result = $this->sendMessage($phone, $message);
            
            Log::info("WhatsApp tabungan notification result:", [
                'transfer_id' => $transferId,
                'phone' => $phone,
                'success' => $result
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("WhatsApp tabungan notification error: " . $e->getMessage(), [
                'transfer_id' => $transferId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Kirim notifikasi setoran tabungan tunai
     */
    public function sendTabunganCashDepositNotification($studentId, $amount, $keterangan = null)
    {
        try {
            Log::info("Starting WhatsApp tabungan cash deposit notification for student_id: {$studentId}");
            
            // Ambil data siswa
            $student = DB::table('students')
                ->where('student_id', $studentId)
                ->select('student_full_name', 'student_parent_phone', 'student_nis')
                ->first();
            
            if (!$student) {
                Log::warning("WhatsApp tabungan cash deposit notification failed: No student data found for student_id: {$studentId}");
                return false;
            }
            
            if (!$student->student_parent_phone) {
                Log::warning("WhatsApp tabungan cash deposit notification failed: No parent phone for student_id: {$studentId}");
                return false;
            }

            // Format nomor telepon
            $phone = $this->formatPhoneNumber($student->student_parent_phone);
            
            // Buat pesan
            $message = $this->createTabunganCashDepositMessage($student, $amount, $keterangan);
            
            // Kirim pesan
            $result = $this->sendMessage($phone, $message);
            
            Log::info("WhatsApp tabungan cash deposit notification result:", [
                'student_id' => $studentId,
                'phone' => $phone,
                'success' => $result
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("WhatsApp tabungan cash deposit notification error: " . $e->getMessage(), [
                'student_id' => $studentId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Kirim notifikasi penarikan tabungan tunai
     */
    public function sendTabunganCashWithdrawalNotification($studentId, $amount, $keterangan = null)
    {
        try {
            Log::info("Starting WhatsApp tabungan cash withdrawal notification for student_id: {$studentId}");
            
            // Ambil data siswa
            $student = DB::table('students')
                ->where('student_id', $studentId)
                ->select('student_full_name', 'student_parent_phone', 'student_nis')
                ->first();
            
            if (!$student) {
                Log::warning("WhatsApp tabungan cash withdrawal notification failed: No student data found for student_id: {$studentId}");
                return false;
            }
            
            if (!$student->student_parent_phone) {
                Log::warning("WhatsApp tabungan cash withdrawal notification failed: No parent phone for student_id: {$studentId}");
                return false;
            }

            // Format nomor telepon
            $phone = $this->formatPhoneNumber($student->student_parent_phone);
            
            // Buat pesan
            $message = $this->createTabunganCashWithdrawalMessage($student, $amount, $keterangan);
            
            // Kirim pesan
            $result = $this->sendMessage($phone, $message);
            
            Log::info("WhatsApp tabungan cash withdrawal notification result:", [
                'student_id' => $studentId,
                'phone' => $phone,
                'success' => $result
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("WhatsApp tabungan cash withdrawal notification error: " . $e->getMessage(), [
                'student_id' => $studentId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Kirim notifikasi pembayaran gagal
     */
    public function sendPaymentFailedNotification($transferId)
    {
        try {
            // Ambil data transfer dan siswa
            $transfer = DB::table('transfer as t')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->where('t.transfer_id', $transferId)
                ->select(
                    't.*',
                    's.student_full_name as student_name',
                    's.student_parent_phone',
                    's.student_nis as nis',
                    't.payment_number as reference',
                    't.confirm_pay as amount',
                    't.created_at'
                )
                ->first();

            if (!$transfer || !$transfer->student_parent_phone) {
                Log::warning("WhatsApp notification failed: No transfer data or parent phone for transfer_id: {$transferId}");
                return false;
            }

            // Ambil semua transfer detail untuk multi POS
            $transferDetails = DB::table('transfer_detail as td')
                ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->leftJoin('pos_pembayaran as pos_bulan', 'p_bulan.pos_pos_id', '=', 'pos_bulan.pos_id')
                ->leftJoin('pos_pembayaran as pos_bebas', 'p_bebas.pos_pos_id', '=', 'pos_bebas.pos_id')
                ->leftJoin('periods as per_bulan', 'p_bulan.period_period_id', '=', 'per_bulan.period_id')
                ->leftJoin('periods as per_bebas', 'p_bebas.period_period_id', '=', 'per_bebas.period_id')
                ->where('td.transfer_id', $transferId)
                ->select(
                    'td.payment_type',
                    'td.subtotal',
                    DB::raw('CASE 
                        WHEN td.payment_type = 3 THEN "Setoran Tabungan"
                        WHEN b.bulan_id IS NOT NULL THEN CONCAT(pos_bulan.pos_name, "-", 
                            CASE b.month_month_id
                                WHEN 1 THEN "Juli"
                                WHEN 2 THEN "Agustus"
                                WHEN 3 THEN "September"
                                WHEN 4 THEN "Oktober"
                                WHEN 5 THEN "November"
                                WHEN 6 THEN "Desember"
                                WHEN 7 THEN "Januari"
                                WHEN 8 THEN "Februari"
                                WHEN 9 THEN "Maret"
                                WHEN 10 THEN "April"
                                WHEN 11 THEN "Mei"
                                WHEN 12 THEN "Juni"
                                ELSE "Unknown"
                            END, " (", COALESCE(CONCAT(per_bulan.period_start, "/", per_bulan.period_end), "2025/2026"), ")"
                        )
                        WHEN be.bebas_id IS NOT NULL THEN CONCAT(pos_bebas.pos_name, " - ", COALESCE(CONCAT(per_bebas.period_start, "/", per_bebas.period_end), "2025/2026"))
                        ELSE "Pembayaran Online"
                    END as pos_name')
                )
                ->get();

            // Gabungkan semua POS names
            $posNames = [];
            foreach ($transferDetails as $detail) {
                if ($detail->pos_name && !in_array($detail->pos_name, $posNames)) {
                    $posNames[] = $detail->pos_name;
                }
            }

            // Buat pos_name yang digabungkan
            $combinedPosName = count($posNames) > 1 ? implode(", ", $posNames) : ($posNames[0] ?? 'Pembayaran Online');

            // Gabungkan data untuk message
            $payment = (object) [
                'student_name' => $transfer->student_name,
                'student_parent_phone' => $transfer->student_parent_phone,
                'nis' => $transfer->nis,
                'reference' => $transfer->reference,
                'amount' => $transfer->amount,
                'created_at' => $transfer->created_at,
                'pos_name' => $combinedPosName
            ];

            // Format nomor telepon
            $phone = $this->formatPhoneNumber($payment->student_parent_phone);
            
            // Buat pesan
            $message = $this->createPaymentFailedMessage($payment);
            
            // Kirim pesan
            return $this->sendMessage($phone, $message);
            
        } catch (\Exception $e) {
            Log::error("WhatsApp notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim pesan WhatsApp menggunakan Fonnte
     */
    public function sendMessage($phone, $message)
    {
        try {
            Log::info('Starting WhatsApp message send:', [
                'phone' => $phone,
                'message_length' => strlen($message),
                'api_key_length' => strlen($this->apiKey),
                'url' => $this->url,
                'api_key_preview' => substr($this->apiKey, 0, 10) . '...'
            ]);

            if (empty($this->apiKey) || empty($this->url)) {
                Log::warning("WhatsApp gateway not configured", [
                    'api_key_empty' => empty($this->apiKey),
                    'url_empty' => empty($this->url),
                    'api_key' => $this->apiKey,
                    'url' => $this->url
                ]);
                return false;
            }

            $payload = [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62'
            ];

            Log::info('WhatsApp API payload:', $payload);

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->url, $payload);

            Log::info('WhatsApp API response:', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp message sent successfully to: {$phone}", [
                    'response_body' => $response->body()
                ]);
                return true;
            } else {
                Log::error("WhatsApp message failed:", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'phone' => $phone
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp message send exception: " . $e->getMessage(), [
                'phone' => $phone,
                'error_trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Format nomor telepon
     */
    public function formatPhoneNumber($phone)
    {
        try {
            if (empty($phone)) {
                Log::warning("Empty phone number provided for formatting");
                return '';
            }

            // Hapus karakter non-digit
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            if (empty($phone)) {
                Log::warning("Phone number is empty after cleaning");
                return '';
            }
            
            // Jika dimulai dengan 0, ganti dengan 62
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }
            
            // Jika belum ada 62, tambahkan
            if (substr($phone, 0, 2) !== '62') {
                $phone = '62' . $phone;
            }
            
            return $phone;
        } catch (\Exception $e) {
            Log::error("Error formatting phone number: " . $e->getMessage(), [
                'original_phone' => $phone,
                'error_trace' => $e->getTraceAsString()
            ]);
            return '';
        }
    }

    /**
     * Buat pesan pembayaran sukses
     */
    protected function createPaymentSuccessMessage($payment)
    {
        $amount = number_format($payment->amount ?? 0, 0, ',', '.');
        $date = date('d/m/Y H:i', strtotime($payment->created_at));
        $posName = $payment->pos_name ?? 'Pembayaran Tunai';
        
        // Jika berupa tabungan, ubah POS menjadi "Setoran Tabungan"
        if (strpos(strtolower($posName), 'setoran tabungan') !== false) {
            $posName = 'Setoran Tabungan';
        }
        
        Log::info("Creating payment success message:", [
            'amount' => $payment->amount,
            'formatted_amount' => $amount,
            'date' => $payment->created_at,
            'formatted_date' => $date,
            'pos_name' => $posName,
            'original_pos_name' => $payment->pos_name ?? 'Pembayaran Tunai',
            'student_name' => $payment->student_name,
            'nis' => $payment->nis,
            'reference' => $payment->reference
        ]);
        
        return "🎉 *PEMBAYARAN BERHASIL*

*Detail Pembayaran:*
📋 Nama: {$payment->student_name}
📚 NIS: {$payment->nis}
💰 Nominal: Rp {$amount}
📝 POS: {$posName}
📅 Tanggal: {$date}
🔢 No. Referensi: {$payment->reference}

✅ Pembayaran telah berhasil diproses dan diverifikasi.

Terima kasih telah melakukan pembayaran tepat waktu.

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Buat pesan pembayaran pending
     */
    protected function createPaymentPendingMessage($payment)
    {
        $amount = number_format($payment->confirm_pay ?? $payment->amount ?? 0, 0, ',', '.');
        $date = date('d/m/Y H:i', strtotime($payment->created_at));
        $posName = $payment->pos_name ?? 'Pembayaran Tunai';
        
        // Jika berupa tabungan, ubah POS menjadi "Setoran Tabungan"
        if (strpos(strtolower($posName), 'setoran tabungan') !== false) {
            $posName = 'Setoran Tabungan';
        }
        
        return "⏳ *PEMBAYARAN MENUNGGU VERIFIKASI*

*Detail Pembayaran:*
📋 Nama: {$payment->student_name}
📚 NIS: {$payment->nis}
💰 Nominal: Rp {$amount}
📝 POS: {$posName}
📅 Tanggal: {$date}
🔢 No. Referensi: {$payment->reference}

⏳ Status: Menunggu verifikasi admin

Mohon tunggu, pembayaran Anda sedang diverifikasi oleh admin.

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Buat pesan setoran tabungan sukses
     */
    protected function createTabunganSuccessMessage($payment)
    {
        $amount = number_format($payment->amount ?? 0, 0, ',', '.');
        $date = date('d/m/Y H:i', strtotime($payment->created_at));
        $description = $payment->description ?? 'Setor Tabungan';
        
        return "✅ *SETORAN TABUNGAN BERHASIL*

*Detail Setoran:*
📋 Nama: {$payment->student_name}
📚 NIS: {$payment->nis}
💰 Nominal: Rp {$amount}
📝 POS: Setoran
📅 Tanggal: {$date}
🔢 No. Referensi: {$payment->reference}

✅ Status: Setoran tabungan berhasil diproses

*Informasi:*
• Saldo tabungan telah diperbarui
• Setoran dapat digunakan untuk keperluan mendatang
• Terima kasih telah mempercayai sistem tabungan kami

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Buat pesan setoran tabungan tunai
     */
    protected function createTabunganCashDepositMessage($student, $amount, $keterangan = null)
    {
        $formattedAmount = number_format($amount, 0, ',', '.');
        $date = date('d/m/Y H:i');
        $description = $keterangan ?: 'Setoran Tabungan Tunai';
        
        return "💰 *SETORAN TABUNGAN TUNAI BERHASIL*

*Detail Setoran:*
📋 Nama: {$student->student_full_name}
📚 NIS: {$student->student_nis}
💰 Nominal: Rp {$formattedAmount}
📝 POS: Setoran
📅 Tanggal: {$date}
📝 Keterangan: {$description}

✅ Status: Setoran tabungan tunai berhasil diproses

*Informasi:*
• Saldo tabungan telah diperbarui
• Setoran dapat digunakan untuk keperluan mendatang
• Terima kasih telah mempercayai sistem tabungan kami

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Buat pesan penarikan tabungan tunai
     */
    protected function createTabunganCashWithdrawalMessage($student, $amount, $keterangan = null)
    {
        $formattedAmount = number_format($amount, 0, ',', '.');
        $date = date('d/m/Y H:i');
        $description = $keterangan ?: 'Penarikan Tabungan Tunai';
        
        return "💸 *PENARIKAN TABUNGAN TUNAI BERHASIL*

*Detail Penarikan:*
📋 Nama: {$student->student_full_name}
📚 NIS: {$student->student_nis}
💰 Nominal: Rp {$formattedAmount}
📝 POS: Penarikan
📅 Tanggal: {$date}
📝 Keterangan: {$description}

✅ Status: Penarikan tabungan tunai berhasil diproses

*Informasi:*
• Saldo tabungan telah diperbarui
• Penarikan telah disetujui dan diproses
• Terima kasih telah menggunakan sistem tabungan kami

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Buat pesan pembayaran gagal
     */
    protected function createPaymentFailedMessage($payment)
    {
        $amount = number_format($payment->confirm_pay ?? $payment->amount ?? 0, 0, ',', '.');
        $date = date('d/m/Y H:i', strtotime($payment->created_at));
        $posName = $payment->pos_name ?? 'Pembayaran Tunai';
        
        // Jika berupa tabungan, ubah POS menjadi "Setoran Tabungan"
        if (strpos(strtolower($posName), 'setoran tabungan') !== false) {
            $posName = 'Setoran Tabungan';
        }
        
        return "❌ *PEMBAYARAN GAGAL*

*Detail Pembayaran:*
📋 Nama: {$payment->student_name}
📚 NIS: {$payment->nis}
💰 Nominal: Rp {$amount}
📝 POS: {$posName}
📅 Tanggal: {$date}
🔢 No. Referensi: {$payment->reference}

❌ Status: Pembayaran gagal

Mohon coba lagi atau hubungi admin untuk bantuan.

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Buat pesan penghapusan transaksi
     */
    protected function createTransactionDeletedMessage($transaction)
    {
        $amount = number_format($transaction->amount ?? 0, 0, ',', '.');
        $date = date('d/m/Y H:i', strtotime($transaction->payment_date));
        $paymentTypeText = $transaction->payment_type === 'bulanan' ? 'Bulanan' : 'Bebas';
        
        return "🗑️ *TRANSAKSI DIHAPUS*

*Detail Transaksi yang Dihapus:*
📋 Nama: {$transaction->student_name}
📚 NIS: {$transaction->nis}
💰 Nominal: Rp {$amount}
📝 POS: {$transaction->pos_name}
📅 Tanggal: {$date}
🔢 No. Pembayaran: {$transaction->payment_number}
📊 Jenis: {$paymentTypeText}

🗑️ Status: Transaksi telah dihapus oleh admin

*Catatan:*
• Pembayaran telah dibatalkan
• Tagihan kembali aktif
• Silakan lakukan pembayaran ulang jika diperlukan

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Buat pesan penghapusan transaksi dengan data langsung
     */
    protected function createTransactionDeletedMessageDirect($transactionData, $paymentType)
    {
        $amount = number_format($transactionData->amount ?? 0, 0, ',', '.');
        $date = date('d/m/Y H:i', strtotime($transactionData->payment_date));
        $paymentTypeText = $paymentType === 'bulanan' ? 'Bulanan' : 'Bebas';
        
        return "🗑️ *TRANSAKSI DIHAPUS*

*Detail Transaksi yang Dihapus:*
📋 Nama: {$transactionData->student_name}
📚 NIS: {$transactionData->nis}
💰 Nominal: Rp {$amount}
📝 POS: {$transactionData->pos_name}
📅 Tanggal: {$date}
🔢 No. Pembayaran: {$transactionData->payment_number}
📊 Jenis: {$paymentTypeText}

🗑️ Status: Transaksi telah dihapus oleh admin

*Catatan:*
• Pembayaran telah dibatalkan
• Tagihan kembali aktif
• Silakan lakukan pembayaran ulang jika diperlukan

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Buat pesan penghapusan transaksi online
     */
    protected function createOnlineTransactionDeletedMessage($transaction, $paymentType)
    {
        $amount = number_format($transaction->confirm_pay ?? $transaction->payment_amount ?? 0, 0, ',', '.');
        $date = date('d/m/Y H:i', strtotime($transaction->created_at ?? $transaction->payment_date));
        $paymentTypeText = $paymentType === 'transfer' ? 'Transfer Bank' : 'Payment Gateway';
        $reference = $transaction->reference ?? $transaction->payment_number ?? 'N/A';
        
        return "🗑️ *TRANSAKSI ONLINE DIHAPUS*

*Detail Transaksi yang Dihapus:*
📋 Nama: {$transaction->student_full_name}
📚 NIS: {$transaction->student_nis}
💰 Nominal: Rp {$amount}
📊 Jenis: {$paymentTypeText}
📅 Tanggal: {$date}
🔢 No. Referensi: {$reference}

🗑️ Status: Transaksi online telah dihapus oleh admin

*Catatan:*
• Pembayaran online telah dibatalkan
• Tagihan kembali aktif
• Silakan lakukan pembayaran ulang jika diperlukan

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Buat pesan pembayaran tunai sukses
     */
    protected function createCashPaymentSuccessMessage($studentName, $nis, $posName, $amount, $paymentNumber)
    {
        $formattedAmount = number_format($amount, 0, ',', '.');
        $date = date('d/m/Y H:i');
        
        return "✅ *PEMBAYARAN TUNAI BERHASIL*

*Detail Pembayaran:*
📋 Nama: {$studentName}
📚 NIS: {$nis}
💰 Nominal: Rp {$formattedAmount}
📝 POS: {$posName}
📅 Tanggal: {$date}
🔢 No. Pembayaran: {$paymentNumber}
💳 Metode: Tunai

✅ Status: Pembayaran berhasil diproses

*Informasi:*
• Pembayaran telah diterima dan diproses
• Tagihan telah dilunasi
• Simpan bukti pembayaran ini

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Kirim notifikasi pembayaran disetujui (approved)
     */
    public function sendPaymentApprovedNotification($transferId)
    {
        try {
            Log::info("Starting WhatsApp approval notification for transfer_id: {$transferId}");
            
            // Ambil data transfer dan siswa
            $transfer = DB::table('transfer as t')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->where('t.transfer_id', $transferId)
                ->select(
                    't.*',
                    's.student_full_name as student_name',
                    's.student_parent_phone',
                    's.student_nis as nis',
                    't.payment_number as reference',
                    't.confirm_pay as amount',
                    't.created_at'
                )
                ->first();

            if (!$transfer) {
                Log::warning("WhatsApp approval notification failed: No transfer data found for transfer_id: {$transferId}");
                return false;
            }

            if (!$transfer->student_parent_phone) {
                Log::warning("WhatsApp approval notification failed: No parent phone for transfer_id: {$transferId}");
                return false;
            }

            // Ambil semua transfer detail untuk multi POS
            $transferDetails = DB::table('transfer_detail as td')
                ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->leftJoin('pos_pembayaran as pos_bulan', 'p_bulan.pos_pos_id', '=', 'pos_bulan.pos_id')
                ->leftJoin('pos_pembayaran as pos_bebas', 'p_bebas.pos_pos_id', '=', 'pos_bebas.pos_id')
                ->leftJoin('periods as per_bulan', 'p_bulan.period_period_id', '=', 'per_bulan.period_id')
                ->leftJoin('periods as per_bebas', 'p_bebas.period_period_id', '=', 'per_bebas.period_id')
                ->where('td.transfer_id', $transferId)
                ->select(
                    'td.payment_type',
                    'td.subtotal',
                    DB::raw('CASE 
                        WHEN td.payment_type = 3 THEN "Setoran Tabungan"
                        WHEN b.bulan_id IS NOT NULL THEN CONCAT(pos_bulan.pos_name, "-", 
                            CASE b.month_month_id
                                WHEN 1 THEN "Juli"
                                WHEN 2 THEN "Agustus"
                                WHEN 3 THEN "September"
                                WHEN 4 THEN "Oktober"
                                WHEN 5 THEN "November"
                                WHEN 6 THEN "Desember"
                                WHEN 7 THEN "Januari"
                                WHEN 8 THEN "Februari"
                                WHEN 9 THEN "Maret"
                                WHEN 10 THEN "April"
                                WHEN 11 THEN "Mei"
                                WHEN 12 THEN "Juni"
                                ELSE "Unknown"
                            END, " (", COALESCE(CONCAT(per_bulan.period_start, "/", per_bulan.period_end), "2025/2026"), ")"
                        )
                        WHEN be.bebas_id IS NOT NULL THEN CONCAT(pos_bebas.pos_name, " - ", COALESCE(CONCAT(per_bebas.period_start, "/", per_bebas.period_end), "2025/2026"))
                        ELSE "Pembayaran Online"
                    END as pos_name')
                )
                ->get();

            // Gabungkan semua POS names
            $posNames = [];
            foreach ($transferDetails as $detail) {
                if ($detail->pos_name && !in_array($detail->pos_name, $posNames)) {
                    $posNames[] = $detail->pos_name;
                }
            }

            // Buat pos_name yang digabungkan
            $combinedPosName = count($posNames) > 1 ? implode(", ", $posNames) : ($posNames[0] ?? 'Pembayaran Online');

            // Gabungkan data untuk message
            $payment = (object) [
                'student_name' => $transfer->student_name,
                'student_parent_phone' => $transfer->student_parent_phone,
                'nis' => $transfer->nis,
                'reference' => $transfer->reference,
                'amount' => $transfer->amount,
                'created_at' => $transfer->created_at,
                'pos_name' => $combinedPosName
            ];

            $phone = $this->formatPhoneNumber($payment->student_parent_phone);
            $message = $this->createPaymentApprovedMessage($payment);
            
            return $this->sendMessage($phone, $message);
            
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp approval notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi pembayaran ditolak (rejected)
     */
    public function sendPaymentRejectedNotification($transferId, $rejectionReason)
    {
        try {
            Log::info("Starting WhatsApp rejection notification for transfer_id: {$transferId}");
            
            // Ambil data transfer dan siswa
            $transfer = DB::table('transfer as t')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->where('t.transfer_id', $transferId)
                ->select(
                    't.*',
                    's.student_full_name as student_name',
                    's.student_parent_phone',
                    's.student_nis as nis',
                    't.payment_number as reference',
                    't.confirm_pay as amount',
                    't.created_at'
                )
                ->first();

            if (!$transfer) {
                Log::warning("WhatsApp rejection notification failed: No transfer data found for transfer_id: {$transferId}");
                return false;
            }

            if (!$transfer->student_parent_phone) {
                Log::warning("WhatsApp rejection notification failed: No parent phone for transfer_id: {$transferId}");
                return false;
            }

            // Ambil semua transfer detail untuk multi POS
            $transferDetails = DB::table('transfer_detail as td')
                ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->leftJoin('pos_pembayaran as pos_bulan', 'p_bulan.pos_pos_id', '=', 'pos_bulan.pos_id')
                ->leftJoin('pos_pembayaran as pos_bebas', 'p_bebas.pos_pos_id', '=', 'pos_bebas.pos_id')
                ->leftJoin('periods as per_bulan', 'p_bulan.period_period_id', '=', 'per_bulan.period_id')
                ->leftJoin('periods as per_bebas', 'p_bebas.period_period_id', '=', 'per_bebas.period_id')
                ->where('td.transfer_id', $transferId)
                ->select(
                    'td.payment_type',
                    'td.subtotal',
                    DB::raw('CASE 
                        WHEN td.payment_type = 3 THEN "Setoran Tabungan"
                        WHEN b.bulan_id IS NOT NULL THEN CONCAT(pos_bulan.pos_name, "-", 
                            CASE b.month_month_id
                                WHEN 1 THEN "Juli"
                                WHEN 2 THEN "Agustus"
                                WHEN 3 THEN "September"
                                WHEN 4 THEN "Oktober"
                                WHEN 5 THEN "November"
                                WHEN 6 THEN "Desember"
                                WHEN 7 THEN "Januari"
                                WHEN 8 THEN "Februari"
                                WHEN 9 THEN "Maret"
                                WHEN 10 THEN "April"
                                WHEN 11 THEN "Mei"
                                WHEN 12 THEN "Juni"
                                ELSE "Unknown"
                            END, " (", COALESCE(CONCAT(per_bulan.period_start, "/", per_bulan.period_end), "2025/2026"), ")"
                        )
                        WHEN be.bebas_id IS NOT NULL THEN CONCAT(pos_bebas.pos_name, " - ", COALESCE(CONCAT(per_bebas.period_start, "/", per_bebas.period_end), "2025/2026"))
                        ELSE "Pembayaran Online"
                    END as pos_name')
                )
                ->get();

            // Gabungkan semua POS names
            $posNames = [];
            foreach ($transferDetails as $detail) {
                if ($detail->pos_name && !in_array($detail->pos_name, $posNames)) {
                    $posNames[] = $detail->pos_name;
                }
            }

            // Buat pos_name yang digabungkan
            $combinedPosName = count($posNames) > 1 ? implode(", ", $posNames) : ($posNames[0] ?? 'Pembayaran Online');

            // Gabungkan data untuk message
            $payment = (object) [
                'student_name' => $transfer->student_name,
                'student_parent_phone' => $transfer->student_parent_phone,
                'nis' => $transfer->nis,
                'reference' => $transfer->reference,
                'amount' => $transfer->amount,
                'created_at' => $transfer->created_at,
                'pos_name' => $combinedPosName
            ];

            $phone = $this->formatPhoneNumber($payment->student_parent_phone);
            $message = $this->createPaymentRejectedMessage($payment, $rejectionReason);
            
            return $this->sendMessage($phone, $message);
            
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp rejection notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buat pesan pembayaran disetujui
     */
    protected function createPaymentApprovedMessage($payment)
    {
        $amount = number_format($payment->amount ?? 0, 0, ',', '.');
        $date = date('d/m/Y H:i', strtotime($payment->created_at));
        $posName = $payment->pos_name ?? 'Pembayaran Tunai';
        
        // Jika berupa tabungan, ubah POS menjadi "Setoran Tabungan"
        if (strpos(strtolower($posName), 'setoran tabungan') !== false) {
            $posName = 'Setoran Tabungan';
        }
        
        return "✅ *PEMBAYARAN DISETUJUI*

*Detail Pembayaran:*
📋 Nama: {$payment->student_name}
📚 NIS: {$payment->nis}
💰 Nominal: Rp {$amount}
📝 POS: {$posName}
📅 Tanggal: {$date}
🔢 No. Referensi: {$payment->reference}

✅ Status: Pembayaran telah diverifikasi dan disetujui

*Informasi:*
• Pembayaran online telah dikonfirmasi
• Tagihan telah dilunasi
• Terima kasih telah melakukan pembayaran

*Sistem Pembayaran SPPQU*";
    }

    /**
     * Buat pesan pembayaran ditolak
     */
    protected function createPaymentRejectedMessage($payment, $rejectionReason)
    {
        $amount = number_format($payment->amount ?? 0, 0, ',', '.');
        $date = date('d/m/Y H:i', strtotime($payment->created_at));
        $reason = $rejectionReason ?: 'Tidak ada alasan yang diberikan';
        $posName = $payment->pos_name ?? 'Pembayaran Tunai';
        
        // Jika berupa tabungan, ubah POS menjadi "Setoran Tabungan"
        if (strpos(strtolower($posName), 'setoran tabungan') !== false) {
            $posName = 'Setoran Tabungan';
        }
        
        return "❌ *PEMBAYARAN DITOLAK*

*Detail Pembayaran:*
📋 Nama: {$payment->student_name}
📚 NIS: {$payment->nis}
💰 Nominal: Rp {$amount}
📝 POS: {$posName}
📅 Tanggal: {$date}
🔢 No. Referensi: {$payment->reference}

❌ Status: Pembayaran ditolak oleh admin

*Alasan Penolakan:*
{$reason}

*Tindakan yang Diperlukan:*
• Periksa kembali bukti pembayaran
• Pastikan data pembayaran sudah benar
• Silakan upload ulang bukti pembayaran
• Atau hubungi admin untuk informasi lebih lanjut

*Sistem Pembayaran SPPQU*";
    }
} 