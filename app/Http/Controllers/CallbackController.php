<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    public function __construct()
    {
        // No middleware for this controller to avoid CSRF issues
    }

    public function tripayCallback(Request $request)
    {
        try {
            // Log all request information
            Log::info('Tripay callback received', [
                'method' => $request->method(),
                'url' => $request->url(),
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            $reference = $request->input('reference');
            $merchantRef = $request->input('merchant_ref');
            $paymentMethod = $request->input('payment_method');
            $totalAmount = $request->input('total_amount');

            // Log the data we received
            Log::info('Tripay callback data', [
                'reference' => $reference,
                'merchant_ref' => $merchantRef,
                'payment_method' => $paymentMethod,
                'total_amount' => $totalAmount
            ]);

            // Find transfer record
            $transfer = DB::table('transfer')
                ->where('reference', $reference)
                ->orWhere('merchantRef', $merchantRef)
                ->first();

            if (!$transfer) {
                Log::error('Transfer record not found', [
                    'reference' => $reference, 
                    'merchant_ref' => $merchantRef,
                    'all_data' => $request->all()
                ]);
                return response('Transfer not found', 404, [
                'Content-Type' => 'text/plain'
            ]);
            }

            DB::beginTransaction();

            // For now, set status to pending (0) since we don't have status in callback
            $newStatus = 0; // Pending - will be updated when payment is confirmed

            DB::table('transfer')
                ->where('transfer_id', $transfer->transfer_id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now()
                ]);

            DB::commit();

            Log::info('Tripay callback processed successfully', [
                'reference' => $reference,
                'merchant_ref' => $merchantRef,
                'transfer_id' => $transfer->transfer_id,
                'payment_method' => $paymentMethod,
                'total_amount' => $totalAmount
            ]);

            // Tripay mengharapkan response sederhana
            return response('OK', 200, [
                'Content-Type' => 'text/plain'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Tripay callback error', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('Internal server error', 500, [
                'Content-Type' => 'text/plain'
            ]);
        }
    }

    public function testCallback(Request $request)
    {
        Log::info('Test callback received', $request->all());
        return response()->json([
            'success' => true,
            'message' => 'Test callback received',
            'data' => $request->all()
        ]);
    }

    public function midtransCallback(Request $request)
    {
        try {
            // Log all request information
            Log::info('Midtrans callback received', [
                'method' => $request->method(),
                'url' => $request->url(),
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'raw_body' => $request->getContent()
            ]);

            $orderId = $request->input('order_id');
            $status = $request->input('transaction_status');
            $fraudStatus = $request->input('fraud_status');
            $paymentType = $request->input('payment_type');
            $grossAmount = $request->input('gross_amount');
            $signatureKey = $request->input('signature_key');

            Log::info('Midtrans callback data parsed', [
                'order_id' => $orderId,
                'transaction_status' => $status,
                'fraud_status' => $fraudStatus,
                'payment_type' => $paymentType,
                'gross_amount' => $grossAmount,
                'signature_key' => $signatureKey ? 'present' : 'missing'
            ]);

            // Validate required fields
            if (!$orderId || !$status) {
                Log::error('Midtrans callback missing required fields', [
                    'order_id' => $orderId,
                    'status' => $status,
                    'all_data' => $request->all()
                ]);
                return response()->json(['error' => 'Missing required fields'], 400);
            }

            // Check if this is a cart payment
            if (strpos($orderId, 'CART-') === 0) {
                Log::info('Processing cart payment callback', ['order_id' => $orderId]);
                return $this->processCartPaymentCallbackNoSession($request);
            }
            
            // Check if this is a tabungan payment
            if (strpos($orderId, 'TB-') === 0) {
                Log::info('Processing tabungan payment callback', ['order_id' => $orderId]);
                return $this->processTabunganPaymentCallback($request);
            }

            // For other payment types, log and return success
            Log::info('Midtrans callback processed for order: ' . $orderId, [
                'status' => $status,
                'fraud_status' => $fraudStatus,
                'payment_type' => $paymentType
            ]);

            // Return success response
            return response()->json(['status' => 'OK']);

        } catch (\Exception $e) {
            Log::error('Midtrans callback error', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function processCartPaymentCallbackNoSession(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $status = $request->input('transaction_status');
            $fraudStatus = $request->input('fraud_status');
            $grossAmount = $request->input('gross_amount');

            Log::info('Processing cart payment callback (NO SESSION)', [
                'order_id' => $orderId,
                'status' => $status,
                'fraud_status' => $fraudStatus,
                'gross_amount' => $grossAmount
            ]);

            // Extract student ID from order ID (format: CART-{studentId}-{timestamp})
            $parts = explode('-', $orderId);
            $studentId = $parts[1] ?? null;

            if (!$studentId) {
                Log::error("Invalid cart order ID format: {$orderId}");
                return response()->json(['error' => 'Invalid order ID'], 400);
            }

            // Get cart payment data from database (NO SESSION)
            Log::info("Getting cart payment data from database for order: {$orderId}");
            
            // Try to get data from cart_payment_temp table
            $tempPaymentData = DB::table('cart_payment_temp')
                ->where('order_id', $orderId)
                ->first();
            
            if ($tempPaymentData) {
                Log::info("Found cart payment data in database for order: {$orderId}", [
                    'temp_data' => $tempPaymentData
                ]);
                
                // Convert database data to cart payment format
                $cartPaymentData = [
                    'order_id' => $tempPaymentData->order_id,
                    'student_id' => $tempPaymentData->student_id,
                    'cart_items' => json_decode($tempPaymentData->cart_items, true),
                    'total_amount' => $tempPaymentData->total_amount
                ];
                
                Log::info("Converted database data to cart payment format", [
                    'cart_payment_data' => $cartPaymentData
                ]);
            } else {
                // Try to get data from transfer table as final fallback
                $transferRecord = DB::table('transfer')
                    ->where('reference', $orderId)
                    ->orWhere('merchantRef', $orderId)
                    ->first();
                
                if ($transferRecord) {
                    Log::info("Found transfer record in database for order: {$orderId}", [
                        'transfer_record' => $transferRecord
                    ]);
                    
                    // Update transfer status based on Midtrans status
                    $newStatus = $this->mapMidtransStatusToTransferStatus($status);
                    
                    DB::table('transfer')
                        ->where('transfer_id', $transferRecord->transfer_id)
                        ->update([
                            'status' => $newStatus,
                            'updated_at' => now()
                        ]);
                    
                    Log::info("Transfer status updated in database for order: {$orderId}", [
                        'old_status' => $transferRecord->status,
                        'new_status' => $newStatus,
                        'midtrans_status' => $status
                    ]);
                    
                    return response()->json(['status' => 'OK']);
                }
                
                Log::error("No payment data found for order: {$orderId}", [
                    'expected_order_id' => $orderId
                ]);
                
                return response()->json(['error' => 'Payment data not found'], 404);
            }

            DB::beginTransaction();

            // Process each cart item based on transaction status
            switch ($status) {
                case 'capture':
                    if ($fraudStatus == 'challenge') {
                        // Payment is being challenged
                        Log::info("Cart payment challenged for order: {$orderId}");
                    } else if ($fraudStatus == 'accept') {
                        // Payment is successful
                        $this->processCartPaymentSuccess($cartPaymentData, $orderId, $grossAmount);
                    }
                    break;
                    
                case 'settlement':
                    // Payment is successful
                    Log::info("Cart payment settled for order: {$orderId}");
                    $this->processCartPaymentSuccess($cartPaymentData, $orderId, $grossAmount);
                    break;
                    
                case 'pending':
                    Log::info("Cart payment pending for order: {$orderId}");
                    // Process pending payment - create transfer record with pending status
                    $this->processCartPaymentPending($cartPaymentData, $orderId, $grossAmount);
                    break;
                    
                case 'deny':
                    Log::info("Cart payment denied for order: {$orderId}");
                    break;
                    
                case 'expire':
                    Log::info("Cart payment expired for order: {$orderId}");
                    break;
                    
                case 'cancel':
                    Log::info("Cart payment cancelled for order: {$orderId}");
                    break;
                    
                default:
                    Log::warning("Unknown transaction status: {$status} for order: {$orderId}");
                    break;
            }

            DB::commit();

            // Clean up temporary payment data from database (NO SESSION)
            DB::table('cart_payment_temp')
                ->where('order_id', $orderId)
                ->delete();

            Log::info("Cart payment callback processed successfully for order: {$orderId}", [
                'status' => $status,
                'fraud_status' => $fraudStatus
            ]);

            return response()->json(['status' => 'OK']);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Cart payment callback error (NO SESSION)', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Map Midtrans status to transfer status
     */
    private function mapMidtransStatusToTransferStatus($midtransStatus)
    {
        switch ($midtransStatus) {
            case 'capture':
            case 'settlement':
                return 1; // Success/Paid
            case 'pending':
                return 0; // Pending
            case 'deny':
            case 'expire':
            case 'cancel':
                return 2; // Failed/Cancelled
            default:
                return 0; // Default to pending
        }
    }

    private function processCartPaymentSuccess($cartPaymentData, $orderId, $grossAmount)
    {
        $studentId = $cartPaymentData['student_id'];
        $cartItems = $cartPaymentData['cart_items'];

        Log::info("Processing successful cart payment", [
            'order_id' => $orderId,
            'student_id' => $studentId,
            'cart_items' => $cartItems,
            'gross_amount' => $grossAmount
        ]);

        // Process each cart item
        foreach ($cartItems as $item) {
            if ($item['bill_type'] === 'bulanan') {
                // Process bulanan payment
                $this->processBulananPayment($studentId, $item['bill_id'], $item['amount'], $orderId);
            } else if ($item['bill_type'] === 'bebas') {
                // Process bebas payment
                $this->processBebasPayment($studentId, $item['bill_id'], $item['amount'], $orderId);
            }
        }
    }

    private function processBulananPayment($studentId, $billId, $amount, $orderId)
    {
        // Get bulanan bill details
        $bulanan = DB::table('bulan')
            ->where('bulan_id', $billId)
            ->where('student_student_id', $studentId)
            ->first();

        if (!$bulanan) {
            Log::warning("Bulanan bill not found, creating transfer record only", [
                'bill_id' => $billId, 
                'student_id' => $studentId,
                'order_id' => $orderId
            ]);
            
            // Create transfer record even if bill not found
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'bill_type' => 'bulanan',
                'bill_id' => $billId,
                'confirm_pay' => $amount,
                'payment_method' => 'midtrans',
                'reference' => $orderId,
                'merchantRef' => $orderId,
                'status' => 1, // Success
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create transfer_detail record
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => 1, // 1 for bulanan payment
                'bulan_id' => $billId,
                'bebas_id' => null,
                'desc' => 'Pembayaran Bulanan via Midtrans (Bill Not Found)',
                'subtotal' => $amount,
                'is_tabungan' => 0
            ]);

            // Create log_trx record
            DB::table('log_trx')->insert([
                'student_student_id' => $studentId,
                'bulan_bulan_id' => $billId,
                'bebas_pay_bebas_pay_id' => null,
                'log_trx_input_date' => now(),
                'log_trx_last_update' => now()
            ]);

            Log::info("Transfer record created for missing bulanan bill", [
                'bill_id' => $billId,
                'student_id' => $studentId,
                'amount' => $amount,
                'order_id' => $orderId,
                'transfer_id' => $transferId
            ]);
            return;
        }

        // Calculate remaining amount
        $totalBill = $bulanan->bulan_bill;
        $paidAmount = $bulanan->bulan_total_pay ?? 0;
        $remainingAmount = $totalBill - $paidAmount;
        $newPaidAmount = min($amount, $remainingAmount);

        // Update bulanan payment
        DB::table('bulan')
            ->where('bulan_id', $billId)
            ->update([
                'bulan_total_pay' => $paidAmount + $newPaidAmount,
                'bulan_date_pay' => now(),
                'updated_at' => now()
            ]);

        // Create transfer record
        $transferId = DB::table('transfer')->insertGetId([
            'student_id' => $studentId,
            'bill_type' => 'bulanan',
            'bill_id' => $billId,
            'confirm_pay' => $newPaidAmount,
            'payment_method' => 'midtrans',
            'reference' => $orderId,
            'merchantRef' => $orderId,
            'status' => 1, // Success
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create transfer_detail record
        DB::table('transfer_detail')->insert([
            'transfer_id' => $transferId,
            'payment_type' => 1, // 1 for bulanan payment
            'bulan_id' => $billId,
            'bebas_id' => null,
            'desc' => 'Pembayaran Bulanan via Midtrans',
            'subtotal' => $newPaidAmount,
            'is_tabungan' => 0
        ]);

        // Create log_trx record
        DB::table('log_trx')->insert([
            'student_student_id' => $studentId,
            'bulan_bulan_id' => $billId,
            'bebas_pay_bebas_pay_id' => null,
            'log_trx_input_date' => now(),
            'log_trx_last_update' => now()
        ]);

        Log::info("Bulanan payment processed successfully", [
            'bill_id' => $billId,
            'student_id' => $studentId,
            'amount' => $newPaidAmount,
            'order_id' => $orderId,
            'transfer_id' => $transferId
        ]);
    }

    private function processBebasPayment($studentId, $billId, $amount, $orderId)
    {
        // Get bebas bill details
        $bebas = DB::table('bebas')
            ->where('bebas_id', $billId)
            ->where('student_student_id', $studentId)
            ->first();

        if (!$bebas) {
            Log::warning("Bebas bill not found, creating transfer record only", [
                'bill_id' => $billId, 
                'student_id' => $studentId,
                'order_id' => $orderId
            ]);
            
            // Create transfer record even if bill not found
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'bill_type' => 'bebas',
                'bill_id' => $billId,
                'confirm_pay' => $amount,
                'payment_method' => 'midtrans',
                'reference' => $orderId,
                'merchantRef' => $orderId,
                'status' => 1, // Success
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create transfer_detail record
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => 2, // 2 for bebas payment
                'bulan_id' => null,
                'bebas_id' => $billId,
                'desc' => 'Pembayaran Bebas via Midtrans (Bill Not Found)',
                'subtotal' => $amount,
                'is_tabungan' => 0
            ]);

            // Create log_trx record
            DB::table('log_trx')->insert([
                'student_student_id' => $studentId,
                'bulan_bulan_id' => null,
                'bebas_pay_bebas_pay_id' => $billId,
                'log_trx_input_date' => now(),
                'log_trx_last_update' => now()
            ]);

            Log::info("Transfer record created for missing bebas bill", [
                'bill_id' => $billId,
                'student_id' => $studentId,
                'amount' => $amount,
                'order_id' => $orderId,
                'transfer_id' => $transferId
            ]);
            return;
        }

        // Calculate remaining amount
        $totalBill = $bebas->bebas_bill;
        $paidAmount = $bebas->bebas_total_pay ?? 0;
        $remainingAmount = $totalBill - $paidAmount;
        $newPaidAmount = min($amount, $remainingAmount);

        // Update bebas payment
        DB::table('bebas')
            ->where('bebas_id', $billId)
            ->update([
                'bebas_total_pay' => $paidAmount + $newPaidAmount,
                'bebas_date_pay' => now(),
                'updated_at' => now()
            ]);

        // Create transfer record
        $transferId = DB::table('transfer')->insertGetId([
            'student_id' => $studentId,
            'bill_type' => 'bebas',
            'bill_id' => $billId,
            'confirm_pay' => $newPaidAmount,
            'payment_method' => 'midtrans',
            'reference' => $orderId,
            'merchantRef' => $orderId,
            'status' => 1, // Success
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create transfer_detail record
        DB::table('transfer_detail')->insert([
            'transfer_id' => $transferId,
            'payment_type' => 2, // 2 for bebas payment
            'bulan_id' => null,
            'bebas_id' => $billId,
            'desc' => 'Pembayaran Bebas via Midtrans',
            'subtotal' => $newPaidAmount,
            'is_tabungan' => 0
        ]);

        // Create log_trx record
        DB::table('log_trx')->insert([
            'student_student_id' => $studentId,
            'bulan_bulan_id' => null,
            'bebas_pay_bebas_pay_id' => $billId,
            'log_trx_input_date' => now(),
            'log_trx_last_update' => now()
        ]);

        Log::info("Bebas payment processed successfully", [
            'bill_id' => $billId,
            'student_id' => $studentId,
            'amount' => $newPaidAmount,
            'order_id' => $orderId,
            'transfer_id' => $transferId
        ]);
    }

    private function processCartPaymentPending($cartPaymentData, $orderId, $grossAmount)
    {
        $studentId = $cartPaymentData['student_id'];
        $cartItems = $cartPaymentData['cart_items'];

        Log::info("Processing pending cart payment", [
            'order_id' => $orderId,
            'student_id' => $studentId,
            'cart_items' => $cartItems,
            'gross_amount' => $grossAmount
        ]);

        // Process each cart item
        foreach ($cartItems as $item) {
            if ($item['bill_type'] === 'bulanan') {
                // Process bulanan payment with pending status
                $this->processBulananPaymentPending($studentId, $item['bill_id'], $item['amount'], $orderId);
            } else if ($item['bill_type'] === 'bebas') {
                // Process bebas payment with pending status
                $this->processBebasPaymentPending($studentId, $item['bill_id'], $item['amount'], $orderId);
            }
        }
    }

    private function processBulananPaymentPending($studentId, $billId, $amount, $orderId)
    {
        // Get bulanan bill details
        $bulanan = DB::table('bulan')
            ->where('bulan_id', $billId)
            ->where('student_student_id', $studentId)
            ->first();

        if (!$bulanan) {
            Log::warning("Bulanan bill not found, creating transfer record only", [
                'bill_id' => $billId, 
                'student_id' => $studentId,
                'order_id' => $orderId
            ]);
            
            // Create transfer record even if bill not found
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'bill_type' => 'bulanan',
                'bill_id' => $billId,
                'confirm_pay' => $amount,
                'payment_method' => 'midtrans',
                'reference' => $orderId,
                'merchantRef' => $orderId,
                'status' => 0, // Pending
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create transfer_detail record
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => 1, // 1 for bulanan payment
                'bulan_id' => $billId,
                'bebas_id' => null,
                'desc' => 'Pembayaran Bulanan via Midtrans (Pending - Bill Not Found)',
                'subtotal' => $amount,
                'is_tabungan' => 0
            ]);

            // Create log_trx record
            DB::table('log_trx')->insert([
                'student_student_id' => $studentId,
                'bulan_bulan_id' => $billId,
                'bebas_pay_bebas_pay_id' => null,
                'log_trx_input_date' => now(),
                'log_trx_last_update' => now()
            ]);

            Log::info("Transfer record created for missing bulanan bill (pending)", [
                'bill_id' => $billId,
                'student_id' => $studentId,
                'amount' => $amount,
                'order_id' => $orderId,
                'transfer_id' => $transferId
            ]);
            return;
        }

        // Create transfer record for pending payment
        $transferId = DB::table('transfer')->insertGetId([
            'student_id' => $studentId,
            'bill_type' => 'bulanan',
            'bill_id' => $billId,
            'confirm_pay' => $amount,
            'payment_method' => 'midtrans',
            'reference' => $orderId,
            'merchantRef' => $orderId,
            'status' => 0, // Pending
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create transfer_detail record
        DB::table('transfer_detail')->insert([
            'transfer_id' => $transferId,
            'payment_type' => 1, // 1 for bulanan payment
            'bulan_id' => $billId,
            'bebas_id' => null,
            'desc' => 'Pembayaran Bulanan via Midtrans (Pending)',
            'subtotal' => $amount,
            'is_tabungan' => 0
        ]);

        // Create log_trx record
        DB::table('log_trx')->insert([
            'student_student_id' => $studentId,
            'bulan_bulan_id' => $billId,
            'bebas_pay_bebas_pay_id' => null,
            'log_trx_input_date' => now(),
            'log_trx_last_update' => now()
        ]);

        Log::info("Transfer record created for bulanan payment (pending)", [
            'bill_id' => $billId,
            'student_id' => $studentId,
            'amount' => $amount,
            'order_id' => $orderId,
            'transfer_id' => $transferId
        ]);
    }

    private function processBebasPaymentPending($studentId, $billId, $amount, $orderId)
    {
        // Get bebas bill details
        $bebas = DB::table('bebas')
            ->where('bebas_id', $billId)
            ->where('student_student_id', $studentId)
            ->first();

        if (!$bebas) {
            Log::warning("Bebas bill not found, creating transfer record only", [
                'bill_id' => $billId, 
                'student_id' => $studentId,
                'order_id' => $orderId
            ]);
            
            // Create transfer record even if bill not found
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'bill_type' => 'bebas',
                'bill_id' => $billId,
                'confirm_pay' => $amount,
                'payment_method' => 'midtrans',
                'reference' => $orderId,
                'merchantRef' => $orderId,
                'status' => 0, // Pending
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create transfer_detail record
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => 2, // 2 for bebas payment
                'bulan_id' => null,
                'bebas_id' => $billId,
                'desc' => 'Pembayaran Bebas via Midtrans (Pending - Bill Not Found)',
                'subtotal' => $amount,
                'is_tabungan' => 0
            ]);

            // Create log_trx record
            DB::table('log_trx')->insert([
                'student_student_id' => $studentId,
                'bulan_bulan_id' => null,
                'bebas_pay_bebas_pay_id' => $billId,
                'log_trx_input_date' => now(),
                'log_trx_last_update' => now()
            ]);

            Log::info("Transfer record created for missing bebas bill (pending)", [
                'bill_id' => $billId,
                'student_id' => $studentId,
                'amount' => $amount,
                'order_id' => $orderId,
                'transfer_id' => $transferId
            ]);
            return;
        }

        // Create transfer record for pending payment
        $transferId = DB::table('transfer')->insertGetId([
            'student_id' => $studentId,
            'bill_type' => 'bebas',
            'bill_id' => $billId,
            'confirm_pay' => $amount,
            'payment_method' => 'midtrans',
            'reference' => $orderId,
            'merchantRef' => $orderId,
            'status' => 0, // Pending
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create transfer_detail record
        DB::table('transfer_detail')->insert([
            'transfer_id' => $transferId,
            'payment_type' => 2, // 2 for bebas payment
            'bulan_id' => null,
            'bebas_id' => $billId,
            'desc' => 'Pembayaran Bebas via Midtrans (Pending)',
            'subtotal' => $amount,
            'is_tabungan' => 0
        ]);

        // Create log_trx record
        DB::table('log_trx')->insert([
            'student_student_id' => $studentId,
            'bulan_bulan_id' => null,
            'bebas_pay_bebas_pay_id' => $billId,
            'log_trx_input_date' => now(),
            'log_trx_last_update' => now()
        ]);

        Log::info("Transfer record created for bebas payment (pending)", [
            'bill_id' => $billId,
            'student_id' => $studentId,
            'amount' => $amount,
            'order_id' => $orderId,
            'transfer_id' => $transferId
        ]);
    }

    private function processTabunganPaymentCallback(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $status = $request->input('transaction_status');
            $fraudStatus = $request->input('fraud_status');
            $grossAmount = $request->input('gross_amount');

            Log::info('Processing tabungan payment callback', [
                'order_id' => $orderId,
                'status' => $status,
                'fraud_status' => $fraudStatus,
                'gross_amount' => $grossAmount
            ]);

            // Extract student ID from order ID (format: TB-{studentId}-{random})
            $parts = explode('-', $orderId);
            $studentId = $parts[1] ?? null;

            if (!$studentId) {
                Log::error("Invalid tabungan order ID format: {$orderId}");
                return response()->json(['error' => 'Invalid order ID'], 400);
            }

            // Get existing transfer record
            $transferRecord = DB::table('transfer')
                ->where('reference', $orderId)
                ->first();

            if (!$transferRecord) {
                Log::error("Tabungan transfer record not found for order: {$orderId}");
                return response()->json(['error' => 'Transfer record not found'], 404);
            }

            DB::beginTransaction();

            // Update transfer status based on transaction status
            $newStatus = $this->mapMidtransStatusToTransferStatus($status);
            
            DB::table('transfer')
                ->where('transfer_id', $transferRecord->transfer_id)
                ->update([
                    'status' => $newStatus,
                    'paid_at' => $status === 'settlement' ? now() : null,
                    'updated_at' => now()
                ]);

            // Create transfer_detail record if not exists
            $existingDetail = DB::table('transfer_detail')
                ->where('transfer_id', $transferRecord->transfer_id)
                ->first();

            if (!$existingDetail) {
                DB::table('transfer_detail')->insert([
                    'transfer_id' => $transferRecord->transfer_id,
                    'payment_type' => 3, // Tabungan type
                    'bulan_id' => null,
                    'bebas_id' => null,
                    'desc' => 'Setor Tabungan via Midtrans',
                    'subtotal' => $grossAmount,
                    'is_tabungan' => 1
                ]);
            }

            // Create log_trx record
            DB::table('log_trx')->insert([
                'student_student_id' => $studentId,
                'bulan_bulan_id' => null,
                'bebas_pay_bebas_pay_id' => null,
                'log_trx_input_date' => now(),
                'log_trx_last_update' => now()
            ]);

            DB::commit();

            Log::info("Tabungan payment callback processed successfully for order: {$orderId}", [
                'status' => $status,
                'fraud_status' => $fraudStatus,
                'new_status' => $newStatus
            ]);

            return response()->json(['status' => 'OK']);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Tabungan payment callback error', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
} 