<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;
use App\Models\Student;
use Carbon\Carbon;

class MidtransController extends Controller
{
    /**
     * Create payment transaction
     */
    public function createPayment(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'student_id' => 'required|exists:students,student_id',
                'amount' => 'required|numeric|min:1000',
                'payment_type' => 'required|in:spp,bebas,other',
                'period' => 'required_if:payment_type,spp',
                'description' => 'required|string|max:255',
            ]);

            // Ambil data siswa
            $student = Student::findOrFail($request->student_id);
            
            // Generate order ID
            $orderId = 'PG-' . str_pad($student->student_id, 3, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            
            // Buat parameter untuk Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $request->amount,
                ],
                'customer_details' => [
                    'first_name' => $student->student_full_name,
                    'email' => 'student@sppqu.com',
                    'phone' => '08123456789',
                    'billing_address' => [
                        'first_name' => $student->student_full_name,
                        'address' => 'Alamat Siswa',
                        'city' => 'Jakarta',
                        'postal_code' => '12345',
                        'country_code' => 'IDN',
                    ],
                ],
                'item_details' => [
                    [
                        'id' => $request->payment_type . '-' . ($request->period ?? 'general'),
                        'price' => $request->amount,
                        'quantity' => 1,
                        'name' => $request->description,
                    ],
                ],
                'callbacks' => [
                    'finish' => url('/midtrans/finish'),
                    'error' => url('/midtrans/error'),
                    'pending' => url('/midtrans/pending'),
                    'unfinish' => url('/midtrans/unfinish'),
                ],
                'enabled_payments' => [
                    'credit_card', 'bca_va', 'bni_va', 'bri_va', 'mandiri_va', 'permata_va', 'other_va',
                    'gopay', 'indomaret', 'danamon_online', 'akulaku', 'shopeepay', 'ovo', 'dana', 'linkaja', 'qris',
                ],
                'credit_card' => [
                    'secure' => true,
                ],
            ];

            // Buat snap token
            $snapToken = app('midtrans')->createSnapToken($params);
            
            // Simpan data payment ke transfer table
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $student->student_id,
                'detail' => $request->description,
                'status' => 0, // Pending
                'confirm_pay' => $request->amount,
                'reference' => $orderId,
                'merchantRef' => $orderId,
                'checkout_url' => null, // Midtrans uses snap token
                'payment_method' => 'midtrans',
                'gateway_transaction_id' => $orderId,
                'payment_details' => json_encode($params),
                'snap_token' => $snapToken, // Simpan snap token
                'bill_type' => 'bulanan', // Default value
                'bill_id' => 0, // Default value
                'payment_number' => $orderId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Buat transfer_detail record
            $paymentType = 1; // Default: Bulanan
            $bulanId = null;
            $bebasId = null;
            
            if (strpos(strtolower($request->description), 'bebas') !== false || 
                strpos(strtolower($request->description), 'sumbangan') !== false ||
                strpos(strtolower($request->description), 'lainnya') !== false) {
                $paymentType = 2; // Bebas
                
                // Cari atau buat bebas record
                $bebas = DB::table('bebas')
                    ->where('student_student_id', $student->student_id)
                    ->where('bebas_name', 'like', '%' . $request->description . '%')
                    ->first();
                
                if (!$bebas) {
                    // Buat bebas record baru
                    $bebasId = DB::table('bebas')->insertGetId([
                        'student_student_id' => $student->student_id,
                        'bebas_name' => $request->description,
                        'bebas_bill' => $request->amount,
                        'bebas_total_pay' => 0,
                        'bebas_date_pay' => null,
                        'bebas_number_pay' => null,
                        'bebas_last_update' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $bebasId = $bebas->bebas_id;
                }
                
                // Update transfer dengan bill_type dan bill_id yang sesuai
                DB::table('transfer')
                    ->where('transfer_id', $transferId)
                    ->update([
                        'bill_type' => 'bebas',
                        'bill_id' => $bebasId
                    ]);
            } else {
                // Bulanan payment - cari atau buat bulan record
                $currentMonth = date('n');
                $currentYear = date('Y');
                
                $bulan = DB::table('bulan')
                    ->where('student_student_id', $student->student_id)
                    ->where('bulan_month', $currentMonth)
                    ->where('bulan_year', $currentYear)
                    ->first();
                
                if (!$bulan) {
                    // Buat bulan record baru
                    $bulanId = DB::table('bulan')->insertGetId([
                        'student_student_id' => $student->student_id,
                        'bulan_month' => $currentMonth,
                        'bulan_year' => $currentYear,
                        'bulan_bill' => $request->amount,
                        'bulan_date_pay' => null,
                        'bulan_number_pay' => null,
                        'bulan_last_update' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $bulanId = $bulan->bulan_id;
                }
                
                // Update transfer dengan bill_type dan bill_id yang sesuai
                DB::table('transfer')
                    ->where('transfer_id', $transferId)
                    ->update([
                        'bill_type' => 'bulanan',
                        'bill_id' => $bulanId
                    ]);
            }
            
            // Buat transfer_detail record
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => $paymentType,
                'bulan_id' => $bulanId,
                'bebas_id' => $bebasId,
                'subtotal' => $request->amount,
                'status' => 0, // Pending
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'snap_token' => $snapToken,
                'order_id' => $orderId,
                'payment_method' => 'midtrans'
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans create payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment success callback
     */
    public function finish(Request $request)
    {
        try {
            $orderId = $request->order_id;
            $status = $request->transaction_status;
            $fraudStatus = $request->fraud_status;
            
            Log::info('Midtrans finish callback received', [
                'order_id' => $orderId,
                'status' => $status,
                'fraud_status' => $fraudStatus,
                'all_data' => $request->all()
            ]);
            
            // Update payment status in transfer table
            $transfer = DB::table('transfer')
                ->where('gateway_transaction_id', $orderId)
                ->orWhere('reference', $orderId)
                ->first();
            
            if ($transfer) {
                DB::table('transfer')
                    ->where('transfer_id', $transfer->transfer_id)
                    ->update([
                        'status' => 1, // Success
                        'payment_details' => json_encode($request->all()),
                        'paid_at' => now(),
                        'updated_at' => now()
                    ]);

                // Check if this is a tabungan payment
                $transferDetail = DB::table('transfer_detail')
                    ->where('transfer_id', $transfer->transfer_id)
                    ->where('payment_type', 3) // Tabungan type
                    ->first();

                if ($transferDetail) {
                    // This is a tabungan payment - process it
                    $this->processTabunganSuccess($transfer, $transferDetail);
                    return redirect()->route('student.dashboard')->with('success', 'Setoran tabungan berhasil! Terima kasih telah melakukan setoran.');
                } else {
                    // Regular payment - log success for admin notification
                    Log::info('Payment success - Admin notification needed', [
                        'transfer_id' => $transfer->transfer_id,
                        'student_id' => $transfer->student_id,
                        'amount' => $transfer->confirm_pay,
                        'order_id' => $orderId
                    ]);

                    // Kirim notifikasi WhatsApp jika diaktifkan
                    try {
                        $gateway = DB::table('setup_gateways')->first();
                        if ($gateway && $gateway->enable_wa_notification) {
                            $whatsappService = new WhatsAppService();
                            $whatsappService->sendPaymentSuccessNotification($transfer->transfer_id);
                            Log::info("WhatsApp success notification sent for Midtrans transfer_id: {$transfer->transfer_id}");
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to send WhatsApp notification for Midtrans success: " . $e->getMessage());
                        // Jangan gagalkan proses pembayaran jika notifikasi gagal
                    }

                    // TODO: Send notification to admin
                    // $this->notifyAdminPaymentSuccess($transfer);
                }
            }

            return redirect()->route('student.payment.history')->with('success', 'Pembayaran berhasil! Terima kasih telah melakukan pembayaran.');
            
        } catch (\Exception $e) {
            Log::error('Midtrans finish callback error: ' . $e->getMessage());
            return redirect()->route('student.payment.history')->with('error', 'Terjadi kesalahan saat memproses pembayaran');
        }
    }

    /**
     * Handle payment error callback
     */
    public function error(Request $request)
    {
        try {
            $orderId = $request->order_id ?? 'unknown';
            
            Log::error('Midtrans payment error callback', [
                'order_id' => $orderId,
                'all_data' => $request->all()
            ]);

            // Update payment status to failed
            $transfer = DB::table('transfer')
                ->where('gateway_transaction_id', $orderId)
                ->orWhere('reference', $orderId)
                ->first();
            
            if ($transfer) {
                DB::table('transfer')
                    ->where('transfer_id', $transfer->transfer_id)
                    ->update([
                        'status' => 2, // Failed
                        'payment_details' => json_encode($request->all()),
                        'updated_at' => now()
                    ]);

                // Kirim notifikasi WhatsApp jika diaktifkan
                try {
                    $gateway = DB::table('setup_gateways')->first();
                    if ($gateway && $gateway->enable_wa_notification) {
                        $whatsappService = new WhatsAppService();
                        $whatsappService->sendPaymentFailedNotification($transfer->transfer_id);
                        Log::info("WhatsApp failed notification sent for Midtrans transfer_id: {$transfer->transfer_id}");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send WhatsApp notification for Midtrans failure: " . $e->getMessage());
                    // Jangan gagalkan proses pembayaran jika notifikasi gagal
                }
            }

            return redirect()->route('student.payment.history')->with('error', 'Pembayaran gagal. Silakan coba lagi.');
            
        } catch (\Exception $e) {
            Log::error('Midtrans error callback error: ' . $e->getMessage());
            return redirect()->route('student.payment.history')->with('error', 'Terjadi kesalahan saat memproses pembayaran');
        }
    }

    /**
     * Handle payment pending callback
     */
    public function pending(Request $request)
    {
        try {
            $orderId = $request->order_id;
            
            Log::info('Midtrans pending callback received', [
                'order_id' => $orderId,
                'all_data' => $request->all()
            ]);
            
            $transfer = DB::table('transfer')
                ->where('gateway_transaction_id', $orderId)
                ->orWhere('reference', $orderId)
                ->first();
            
            if ($transfer) {
                DB::table('transfer')
                    ->where('transfer_id', $transfer->transfer_id)
                    ->update([
                        'status' => 0, // Pending
                        'payment_details' => json_encode($request->all()),
                        'updated_at' => now()
                    ]);
            }

            return redirect()->route('student.payment.history')->with('warning', 'Pembayaran sedang diproses. Silakan cek status pembayaran Anda.');
            
        } catch (\Exception $e) {
            Log::error('Midtrans pending callback error: ' . $e->getMessage());
            return redirect()->route('student.payment.history')->with('error', 'Terjadi kesalahan saat memproses pembayaran');
        }
    }

    /**
     * Handle payment unfinish callback
     */
    public function unfinish(Request $request)
    {
        try {
            $orderId = $request->order_id;
            
            Log::info('Midtrans unfinish callback received', [
                'order_id' => $orderId,
                'all_data' => $request->all()
            ]);
            
            $transfer = DB::table('transfer')
                ->where('gateway_transaction_id', $orderId)
                ->orWhere('reference', $orderId)
                ->first();
            
            if ($transfer) {
                DB::table('transfer')
                    ->where('transfer_id', $transfer->transfer_id)
                    ->update([
                        'status' => 0, // Pending (user can continue)
                        'payment_details' => json_encode($request->all()),
                        'updated_at' => now()
                    ]);
            }

            return redirect()->route('student.payment.history')->with('warning', 'Pembayaran belum selesai. Anda dapat melanjutkan pembayaran dari riwayat pembayaran.');
            
        } catch (\Exception $e) {
            Log::error('Midtrans unfinish callback error: ' . $e->getMessage());
            return redirect()->route('student.payment.history')->with('error', 'Terjadi kesalahan saat memproses pembayaran');
        }
    }

    /**
     * Handle webhook notification (Server-to-Server)
     */
    public function webhook(Request $request)
    {
        try {
            $orderId = $request->order_id;
            $status = $request->transaction_status;
            $fraudStatus = $request->fraud_status;
            
            Log::info('Midtrans webhook received', [
                'order_id' => $orderId,
                'status' => $status,
                'fraud_status' => $fraudStatus,
                'all_data' => $request->all()
            ]);
            
            // Update payment status in transfer table
            $transfer = DB::table('transfer')
                ->where('gateway_transaction_id', $orderId)
                ->orWhere('reference', $orderId)
                ->first();
            
            if ($transfer) {
                $newStatus = $this->mapStatus($status);
                
                DB::table('transfer')
                    ->where('transfer_id', $transfer->transfer_id)
                    ->update([
                        'status' => $newStatus,
                        'payment_details' => json_encode($request->all()),
                        'paid_at' => $status === 'settlement' ? now() : null,
                        'updated_at' => now()
                    ]);

                // Log for admin notification
                if ($newStatus == 1) { // Success
                    Log::info('Payment success via webhook - Admin notification needed', [
                        'transfer_id' => $transfer->transfer_id,
                        'student_id' => $transfer->student_id,
                        'amount' => $transfer->confirm_pay,
                        'order_id' => $orderId
                    ]);
                    
                    // TODO: Send notification to admin
                    // $this->notifyAdminPaymentSuccess($transfer);
                }
            }

            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus($orderId)
    {
        try {
            $status = app('midtrans')->getStatus($orderId);
            
            return response()->json([
                'success' => true,
                'status' => $status,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Midtrans check status error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status pembayaran',
            ], 500);
        }
    }

    /**
     * Map Midtrans status to internal status
     */
    private function mapStatus($status)
    {
        $statusMap = [
            'capture' => 1, // Success
            'settlement' => 1, // Success
            'pending' => 0, // Pending
            'deny' => 2, // Failed
            'cancel' => 2, // Failed
            'expire' => 2, // Failed
            'failure' => 2, // Failed
        ];

        return $statusMap[$status] ?? 0; // Default to pending
    }

    /**
     * Process successful tabungan payment
     */
    private function processTabunganSuccess($transfer, $transferDetail)
    {
        try {
            DB::beginTransaction();

            // Get student data
            $student = DB::table('students')->where('student_id', $transfer->student_id)->first();
            
            if (!$student) {
                throw new \Exception('Student not found');
            }

            // Get or create tabungan record
            $tabungan = DB::table('tabungan')->where('student_student_id', $transfer->student_id)->first();
            
            if (!$tabungan) {
                // Create new tabungan record
                $tabunganId = DB::table('tabungan')->insertGetId([
                    'student_student_id' => $transfer->student_id,
                    'user_user_id' => 1, // Default admin user
                    'saldo' => $transferDetail->subtotal,
                    'tabungan_input_date' => now(),
                    'tabungan_last_update' => now()
                ]);
            } else {
                // Update existing tabungan balance
                DB::table('tabungan')
                    ->where('tabungan_id', $tabungan->tabungan_id)
                    ->update([
                        'saldo' => $tabungan->saldo + $transferDetail->subtotal,
                        'tabungan_last_update' => now()
                    ]);
                $tabunganId = $tabungan->tabungan_id;
            }

            // Insert log_tabungan record
            DB::table('log_tabungan')->insert([
                'student_student_id' => $transfer->student_id,
                'log_tabungan_input_date' => now(),
                'kredit' => $transferDetail->subtotal, // Setoran
                'debit' => 0, // Penarikan
                'keterangan' => $transferDetail->desc ?? 'Setoran via Payment Gateway',
                'tabungan_tabungan_id' => $tabunganId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Log success
            Log::info('Tabungan payment processed successfully', [
                'transfer_id' => $transfer->transfer_id,
                'student_id' => $transfer->student_id,
                'amount' => $transferDetail->subtotal,
                'tabungan_id' => $tabunganId
            ]);

            // Kirim notifikasi WhatsApp jika diaktifkan
            try {
                $gateway = DB::table('setup_gateways')->first();
                if ($gateway && $gateway->enable_wa_notification) {
                    $whatsappService = new WhatsAppService();
                    $whatsappService->sendTabunganSuccessNotification($transfer->transfer_id);
                    Log::info("WhatsApp tabungan success notification sent for transfer_id: {$transfer->transfer_id}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp notification for tabungan success: " . $e->getMessage());
                // Jangan gagalkan proses pembayaran jika notifikasi gagal
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error processing tabungan success: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * TODO: Notify admin about payment success
     */
    private function notifyAdminPaymentSuccess($transfer)
    {
        // TODO: Implement admin notification
        // This could be:
        // 1. Email notification
        // 2. Database notification
        // 3. Real-time notification (WebSocket/Pusher)
        // 4. SMS notification
        
        Log::info('Admin notification should be sent for payment success', [
            'transfer_id' => $transfer->transfer_id,
            'student_id' => $transfer->student_id,
            'amount' => $transfer->confirm_pay
        ]);
    }
} 