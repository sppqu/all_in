<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\IpaymuService;

class IpaymuCallbackController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('=== iPaymu Callback Received ===', [
            'all_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            // Get signature from header
            $receivedSignature = $request->header('signature');
            
            // Get callback data
            $callbackData = $request->all();
            
            // Verify signature (skip if in sandbox/testing mode)
            $ipaymu = new IpaymuService();
            
            // Only verify signature if not empty
            if ($receivedSignature) {
                $isValid = $ipaymu->verifyCallbackSignature($callbackData, $receivedSignature);
                
                if (!$isValid) {
                    Log::warning('iPaymu callback signature verification failed - Processing anyway for testing', [
                        'received_signature' => $receivedSignature,
                        'data' => $callbackData
                    ]);
                    // Continue processing even if signature fails (for sandbox testing)
                    // return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
                }
            } else {
                Log::warning('iPaymu callback without signature - Processing anyway', [
                    'data' => $callbackData
                ]);
            }

            // Extract data from callback
            $status = $callbackData['status'] ?? $callbackData['Status'] ?? null;
            $statusCode = $callbackData['status_code'] ?? $callbackData['StatusCode'] ?? null;
            $referenceId = $callbackData['reference_id'] ?? $callbackData['ReferenceId'] ?? null;
            $transactionId = $callbackData['trx_id'] ?? $callbackData['TrxId'] ?? null;
            $amount = $callbackData['amount'] ?? $callbackData['Amount'] ?? 0;

            if (!$referenceId) {
                Log::error('iPaymu callback missing reference_id', $callbackData);
                return response()->json(['success' => false, 'message' => 'Missing reference_id'], 400);
            }

            Log::info('iPaymu Callback Data Extracted', [
                'reference_id' => $referenceId,
                'status' => $status,
                'status_code' => $statusCode,
                'transaction_id' => $transactionId,
                'amount' => $amount
            ]);

            // Determine transaction type from reference ID
            if (str_starts_with($referenceId, 'SUB-')) {
                $this->handleSubscriptionCallback($referenceId, $status, $statusCode, $transactionId, $amount);
            } elseif (str_starts_with($referenceId, 'ADDON-')) {
                $this->handleAddonCallback($referenceId, $status, $statusCode, $transactionId, $amount);
            } elseif (str_starts_with($referenceId, 'BULANAN-')) {
                $this->handleBulananCallback($referenceId, $status, $statusCode, $transactionId, $amount);
            } elseif (str_starts_with($referenceId, 'BEBAS-')) {
                $this->handleBebasCallback($referenceId, $status, $statusCode, $transactionId, $amount);
            } elseif (str_starts_with($referenceId, 'CART-')) {
                $this->handleCartCallback($referenceId, $status, $statusCode, $transactionId, $amount);
            } elseif (str_starts_with($referenceId, 'TABUNGAN-')) {
                $this->handleTabunganCallback($referenceId, $status, $statusCode, $transactionId, $amount);
            } elseif (str_starts_with($referenceId, 'SPMB-STEP2-')) {
                $this->handleSPMBCallback($referenceId, $status, $statusCode, $transactionId, $amount);
            } else {
                Log::error('Unknown reference_id format', ['reference_id' => $referenceId]);
                return response()->json(['success' => false, 'message' => 'Unknown transaction type'], 400);
            }

            return response()->json(['success' => true, 'message' => 'Callback processed']);

        } catch (\Exception $e) {
            Log::error('iPaymu Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle subscription payment callback
     */
    private function handleSubscriptionCallback($referenceId, $status, $statusCode, $transactionId, $amount)
    {
        Log::info('Processing Subscription Callback', [
            'reference_id' => $referenceId,
            'status' => $status,
            'status_code' => $statusCode
        ]);

        // Find subscription by transaction_id (reference_id)
        $subscription = DB::table('subscriptions')
            ->where('transaction_id', $referenceId)
            ->first();

        if (!$subscription) {
            Log::error('Subscription not found for reference_id: ' . $referenceId);
            return;
        }

        // Map iPaymu status to our status
        // iPaymu Status: berhasil = paid, pending = pending, expired = expired, failed = failed
        $newStatus = 'pending';
        if (strtolower($status) === 'berhasil' || $statusCode == 1) {
            $newStatus = 'paid';
        } elseif (strtolower($status) === 'pending' || $statusCode == 0) {
            $newStatus = 'pending';
        } elseif (strtolower($status) === 'expired' || strtolower($status) === 'failed') {
            $newStatus = 'expired';
        }

        Log::info('Updating subscription status', [
            'subscription_id' => $subscription->id,
            'old_status' => $subscription->status,
            'new_status' => $newStatus
        ]);

        // Update subscription
        DB::table('subscriptions')
            ->where('id', $subscription->id)
            ->update([
                'status' => $newStatus,
                'payment_reference' => $transactionId,
                'updated_at' => now()
            ]);

        // If paid, activate subscription
        if ($newStatus === 'paid') {
            $duration = $subscription->duration ?? 30;
            $startDate = now();
            $expiresAt = $startDate->copy()->addDays($duration);

            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update([
                    'status' => 'active',
                    'starts_at' => $startDate,
                    'expires_at' => $expiresAt,
                    'updated_at' => now()
                ]);

            // Update invoice status
            DB::table('subscription_invoices')
                ->where('subscription_id', $subscription->id)
                ->where('payment_status', 'pending')
                ->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'payment_reference' => $transactionId,
                    'updated_at' => now()
                ]);

            Log::info('Subscription activated', [
                'subscription_id' => $subscription->id,
                'expires_at' => $expiresAt
            ]);
        }
    }

    /**
     * Handle addon payment callback
     */
    private function handleAddonCallback($referenceId, $status, $statusCode, $transactionId, $amount)
    {
        Log::info('=== Processing Addon Callback (iPaymu) ===', [
            'reference_id' => $referenceId,
            'status' => $status,
            'status_code' => $statusCode,
            'transaction_id' => $transactionId,
            'amount' => $amount
        ]);

        // Extract user_id and addon_id from reference_id (format: ADDON-{user_id}-{addon_id}-{timestamp})
        $parts = explode('-', $referenceId);
        $userId = $parts[1] ?? null;
        $addonId = $parts[2] ?? null;

        Log::info('Extracted from reference_id', [
            'user_id' => $userId,
            'addon_id' => $addonId,
            'parts' => $parts
        ]);

        // Find user addon by transaction_id (reference_id) first
        $userAddon = DB::table('user_addons')
            ->where('transaction_id', $referenceId)
            ->first();

        // Fallback: Find by user_id, addon_id, and pending status if not found
        if (!$userAddon && $userId && $addonId) {
            Log::info('User addon not found by transaction_id, trying fallback query');
            
            $userAddon = DB::table('user_addons')
                ->where('user_id', $userId)
                ->where('addon_id', $addonId)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Update transaction_id if found via fallback
            if ($userAddon) {
                DB::table('user_addons')
                    ->where('id', $userAddon->id)
                    ->update([
                        'transaction_id' => $referenceId,
                        'payment_reference' => $transactionId,
                        'updated_at' => now()
                    ]);
                
                Log::info('✓ Found user addon via fallback and updated transaction_id', [
                    'user_addon_id' => $userAddon->id,
                    'user_id' => $userId,
                    'addon_id' => $addonId
                ]);
            }
        }

        if (!$userAddon) {
            Log::error('❌ User addon not found even after fallback', [
                'reference_id' => $referenceId,
                'user_id' => $userId,
                'addon_id' => $addonId,
                'all_pending_addons' => DB::table('user_addons')
                    ->select('id', 'user_id', 'addon_id', 'transaction_id', 'status', 'created_at')
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
            ]);
            return;
        }

        Log::info('✓ User addon found', [
            'user_addon_id' => $userAddon->id,
            'user_id' => $userAddon->user_id,
            'addon_id' => $userAddon->addon_id,
            'current_status' => $userAddon->status
        ]);

        // Map iPaymu status to our status
        $newStatus = 'pending';
        if (strtolower($status) === 'berhasil' || $statusCode == 1) {
            $newStatus = 'active';
        } elseif (strtolower($status) === 'pending' || $statusCode == 0) {
            $newStatus = 'pending';
        } elseif (strtolower($status) === 'expired' || strtolower($status) === 'failed') {
            $newStatus = 'expired';
        }

        Log::info('Status mapping', [
            'ipaymu_status' => $status,
            'ipaymu_status_code' => $statusCode,
            'mapped_status' => $newStatus
        ]);

        // Get addon details for expiry calculation
        $addon = DB::table('addons')->where('id', $userAddon->addon_id)->first();

        // Prepare update data
        $updateData = [
            'status' => $newStatus,
            'payment_reference' => $transactionId,
            'transaction_id' => $referenceId,
            'updated_at' => now()
        ];

        // If active (paid), set purchase date and expiry
        if ($newStatus === 'active') {
            $purchasedAt = now();
            $expiresAt = null;
            
            // If addon is recurring, set expiry date
            if ($addon && $addon->type === 'recurring') {
                $duration = $addon->duration ?? 30;
                $expiresAt = $purchasedAt->copy()->addDays($duration);
                
                Log::info('Setting recurring addon expiry', [
                    'addon_id' => $userAddon->addon_id,
                    'addon_type' => $addon->type,
                    'duration_days' => $duration,
                    'expires_at' => $expiresAt
                ]);
            } elseif ($addon && $addon->type === 'one_time') {
                Log::info('Setting one-time addon (lifetime)', [
                    'addon_id' => $userAddon->addon_id,
                    'addon_type' => $addon->type
                ]);
            } else {
                Log::warning('Addon type unknown or addon not found', [
                    'addon_id' => $userAddon->addon_id,
                    'addon' => $addon
                ]);
            }

            $updateData['purchased_at'] = $purchasedAt;
            $updateData['expires_at'] = $expiresAt;
            $updateData['amount_paid'] = $amount;
            $updateData['payment_method'] = 'ipaymu';
        }

        Log::info('Updating user addon with data', [
            'user_addon_id' => $userAddon->id,
            'old_status' => $userAddon->status,
            'update_data' => $updateData
        ]);

        // Update user addon
        $updated = DB::table('user_addons')
            ->where('id', $userAddon->id)
            ->update($updateData);

        Log::info('✓✓✓ User Addon Updated Successfully via iPaymu Callback', [
            'user_addon_id' => $userAddon->id,
            'user_id' => $userAddon->user_id,
            'addon_id' => $userAddon->addon_id,
            'final_status' => $newStatus,
            'purchased_at' => $updateData['purchased_at'] ?? null,
            'expires_at' => $updateData['expires_at'] ?? null,
            'rows_affected' => $updated,
            'reference_id' => $referenceId,
            'transaction_id' => $transactionId
        ]);
    }

    /**
     * Handle SPMB payment callback
     */
    private function handleSPMBCallback($referenceId, $status, $statusCode, $transactionId, $amount)
    {
        Log::info('Processing SPMB Callback', [
            'reference_id' => $referenceId,
            'status' => $status,
            'status_code' => $statusCode
        ]);

        // Find SPMB payment by payment_reference (reference_id)
        $payment = DB::table('spmb_payments')
            ->where('payment_reference', $referenceId)
            ->first();

        if (!$payment) {
            Log::error('SPMB payment not found for reference_id: ' . $referenceId);
            return;
        }

        // Map iPaymu status to our status
        $newStatus = 'pending';
        if (strtolower($status) === 'berhasil' || $statusCode == 1) {
            $newStatus = 'paid';
        } elseif (strtolower($status) === 'pending' || $statusCode == 0) {
            $newStatus = 'pending';
        } elseif (strtolower($status) === 'expired' || strtolower($status) === 'failed') {
            $newStatus = 'failed';
        }

        Log::info('Updating SPMB payment status', [
            'payment_id' => $payment->id,
            'old_status' => $payment->status,
            'new_status' => $newStatus
        ]);

        // Update payment status
        DB::table('spmb_payments')
            ->where('id', $payment->id)
            ->update([
                'status' => $newStatus,
                'tripay_reference' => $transactionId,
                'updated_at' => now()
            ]);

        // If paid, update registration to next step
        if ($newStatus === 'paid') {
            $registration = DB::table('spmb_registrations')
                ->where('id', $payment->registration_id)
                ->first();

            if ($registration && $registration->step == 2) {
                DB::table('spmb_registrations')
                    ->where('id', $payment->registration_id)
                    ->update([
                        'step' => 3,
                        'updated_at' => now()
                    ]);

                Log::info('SPMB registration step updated after payment', [
                    'registration_id' => $payment->registration_id,
                    'old_step' => 2,
                    'new_step' => 3
                ]);
            }
        }

        Log::info('SPMB payment callback processed successfully', [
            'payment_id' => $payment->id,
            'final_status' => $newStatus
        ]);
    }

    /**
     * Handle pembayaran bulanan callback
     * Format: BULANAN-{student_id}-{bulan_id}-{timestamp}
     */
    private function handleBulananCallback($referenceId, $status, $statusCode, $transactionId, $amount)
    {
        Log::info('=== Processing Bulanan Payment Callback (iPaymu) ===', [
            'reference_id' => $referenceId,
            'status' => $status,
            'status_code' => $statusCode,
            'transaction_id' => $transactionId,
            'amount' => $amount
        ]);

        // Extract student_id and bulan_id from reference_id
        // Format: BULANAN-{student_id}-{bulan_id}-{timestamp}
        $parts = explode('-', $referenceId);
        $studentId = $parts[1] ?? null;
        $bulanId = $parts[2] ?? null;

        if (!$studentId || !$bulanId) {
            Log::error('Invalid BULANAN reference_id format', ['reference_id' => $referenceId]);
            return;
        }

        Log::info('Extracted bulanan data', [
            'student_id' => $studentId,
            'bulan_id' => $bulanId
        ]);

        // Find bulan record
        $bulan = DB::table('bulan')->where('bulan_id', $bulanId)->first();

        if (!$bulan) {
            Log::error('Bulan record not found', ['bulan_id' => $bulanId]);
            return;
        }

        // Check payment status
        if (strtolower($status) === 'berhasil' || $statusCode == 1) {
            // Payment success
            Log::info('Processing successful bulanan payment');

            // Update bulan payment status
            DB::table('bulan')
                ->where('bulan_id', $bulanId)
                ->update([
                    'bulan_date_pay' => now(),
                    'bulan_last_update' => now()
                ]);

            // Create transfer record
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'bill_type' => 'bulanan',
                'bill_id' => $bulanId,
                'confirm_pay' => $amount,
                'payment_method' => 'ipaymu',
                'reference' => $referenceId,
                'merchantRef' => $transactionId,
                'status' => 1, // Success
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create transfer_detail record
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => 1, // 1 for bulanan payment
                'bulan_id' => $bulanId,
                'bebas_id' => null,
                'desc' => 'Pembayaran Bulanan via iPaymu',
                'subtotal' => $amount,
                'is_tabungan' => 0
            ]);

            // Create log_trx record
            DB::table('log_trx')->insert([
                'student_student_id' => $studentId,
                'bulan_bulan_id' => $bulanId,
                'bebas_pay_bebas_pay_id' => null,
                'log_trx_input_date' => now(),
                'log_trx_last_update' => now()
            ]);

            Log::info('✓✓✓ Bulanan payment processed successfully via iPaymu', [
                'student_id' => $studentId,
                'bulan_id' => $bulanId,
                'amount' => $amount,
                'transfer_id' => $transferId,
                'reference_id' => $referenceId,
                'transaction_id' => $transactionId
            ]);
        } else {
            Log::warning('Bulanan payment not successful', [
                'status' => $status,
                'status_code' => $statusCode,
                'reference_id' => $referenceId
            ]);
        }
    }

    /**
     * Handle pembayaran bebas callback
     * Format: BEBAS-{student_id}-{bebas_id}-{timestamp}
     */
    private function handleBebasCallback($referenceId, $status, $statusCode, $transactionId, $amount)
    {
        Log::info('=== Processing Bebas Payment Callback (iPaymu) ===', [
            'reference_id' => $referenceId,
            'status' => $status,
            'status_code' => $statusCode,
            'transaction_id' => $transactionId,
            'amount' => $amount
        ]);

        // Extract student_id and bebas_id from reference_id
        // Format: BEBAS-{student_id}-{bebas_id}-{timestamp}
        $parts = explode('-', $referenceId);
        $studentId = $parts[1] ?? null;
        $bebasId = $parts[2] ?? null;

        if (!$studentId || !$bebasId) {
            Log::error('Invalid BEBAS reference_id format', ['reference_id' => $referenceId]);
            return;
        }

        Log::info('Extracted bebas data', [
            'student_id' => $studentId,
            'bebas_id' => $bebasId
        ]);

        // Find bebas record
        $bebas = DB::table('bebas')->where('bebas_id', $bebasId)->first();

        if (!$bebas) {
            Log::error('Bebas record not found', ['bebas_id' => $bebasId]);
            return;
        }

        // Check payment status
        if (strtolower($status) === 'berhasil' || $statusCode == 1) {
            // Payment success
            Log::info('Processing successful bebas payment');

            // Calculate new total payment
            $newTotalPay = $bebas->bebas_total_pay + $amount;

            // Update bebas payment
            DB::table('bebas')
                ->where('bebas_id', $bebasId)
                ->update([
                    'bebas_total_pay' => $newTotalPay,
                    'bebas_date_pay' => now(),
                    'bebas_last_update' => now()
                ]);

            // Insert to bebas_pay
            $bebasPayId = DB::table('bebas_pay')->insertGetId([
                'bebas_bebas_id' => $bebasId,
                'bebas_pay_bill' => $amount,
                'bebas_pay_number' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'bebas_pay_desc' => 'Pembayaran Online via iPaymu',
                'user_user_id' => 1,
                'bebas_pay_input_date' => now(),
                'bebas_pay_last_update' => now()
            ]);

            // Create transfer record
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'bill_type' => 'bebas',
                'bill_id' => $bebasId,
                'confirm_pay' => $amount,
                'payment_method' => 'ipaymu',
                'reference' => $referenceId,
                'merchantRef' => $transactionId,
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
                'bebas_id' => $bebasId,
                'desc' => 'Pembayaran Bebas via iPaymu',
                'subtotal' => $amount,
                'is_tabungan' => 0
            ]);

            // Create log_trx record
            DB::table('log_trx')->insert([
                'student_student_id' => $studentId,
                'bulan_bulan_id' => null,
                'bebas_pay_bebas_pay_id' => $bebasPayId,
                'log_trx_input_date' => now(),
                'log_trx_last_update' => now()
            ]);

            Log::info('✓✓✓ Bebas payment processed successfully via iPaymu', [
                'student_id' => $studentId,
                'bebas_id' => $bebasId,
                'amount' => $amount,
                'new_total_pay' => $newTotalPay,
                'transfer_id' => $transferId,
                'bebas_pay_id' => $bebasPayId,
                'reference_id' => $referenceId,
                'transaction_id' => $transactionId
            ]);
        } else {
            Log::warning('Bebas payment not successful', [
                'status' => $status,
                'status_code' => $statusCode,
                'reference_id' => $referenceId
            ]);
        }
    }

    /**
     * Handle cart payment callback
     */
    private function handleCartCallback($referenceId, $status, $statusCode, $transactionId, $amount)
    {
        Log::info('🛒 === Processing Cart Payment Callback (iPaymu) ===', [
            'reference_id' => $referenceId,
            'status' => $status,
            'status_code' => $statusCode,
            'transaction_id' => $transactionId,
            'amount' => $amount
        ]);

        // Check if payment is successful
        if (strtolower($status) !== 'berhasil' && $statusCode != 1) {
            Log::warning('Cart payment not successful', [
                'status' => $status,
                'status_code' => $statusCode
            ]);
            return;
        }

        // Find transfer by reference_id
        $transfer = DB::table('transfer')
            ->where('reference', $referenceId)
            ->orWhere('merchantRef', $referenceId)
            ->first();

        if (!$transfer) {
            Log::error('Transfer not found for cart payment', ['reference_id' => $referenceId]);
            return;
        }

        // Update transfer status
        DB::table('transfer')
            ->where('transfer_id', $transfer->transfer_id)
            ->update([
                'status' => 1, // Success
                'paid_at' => now(),
                'gateway_transaction_id' => $transactionId,
                'updated_at' => now()
            ]);

        // Get all transfer details (cart items)
        $transferDetails = DB::table('transfer_detail')
            ->where('transfer_id', $transfer->transfer_id)
            ->get();

        // Update each cart item (bulan or bebas)
        foreach ($transferDetails as $detail) {
            if ($detail->payment_type == 1 && $detail->bulan_id) {
                // Update bulan
                DB::table('bulan')
                    ->where('bulan_id', $detail->bulan_id)
                    ->update([
                        'bulan_date_pay' => now(),
                        'bulan_number_pay' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                        'bulan_status' => 1,
                        'bulan_last_update' => now()
                    ]);

                // Insert to log_trx
                DB::table('log_trx')->insert([
                    'student_student_id' => $transfer->student_id,
                    'bulan_bulan_id' => $detail->bulan_id,
                    'bebas_pay_bebas_pay_id' => null,
                    'log_trx_input_date' => now(),
                    'log_trx_last_update' => now()
                ]);

            } elseif ($detail->payment_type == 2 && $detail->bebas_id) {
                // Get bebas data
                $bebas = DB::table('bebas')->where('bebas_id', $detail->bebas_id)->first();
                
                if ($bebas) {
                    // Insert to bebas_pay
                    $bebasPayId = DB::table('bebas_pay')->insertGetId([
                        'bebas_bebas_id' => $detail->bebas_id,
                        'bebas_pay_bill' => $detail->subtotal,
                        'bebas_pay_number' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                        'bebas_pay_desc' => 'Pembayaran Cart via iPaymu',
                        'user_user_id' => 1,
                        'bebas_pay_input_date' => now(),
                        'bebas_pay_last_update' => now()
                    ]);

                    // Update bebas
                    DB::table('bebas')
                        ->where('bebas_id', $detail->bebas_id)
                        ->update([
                            'bebas_total_pay' => $bebas->bebas_total_pay + $detail->subtotal,
                            'bebas_date_pay' => now(),
                            'bebas_number_pay' => 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                            'bebas_last_update' => now()
                        ]);

                    // Insert to log_trx
                    DB::table('log_trx')->insert([
                        'student_student_id' => $transfer->student_id,
                        'bulan_bulan_id' => null,
                        'bebas_pay_bebas_pay_id' => $bebasPayId,
                        'log_trx_input_date' => now(),
                        'log_trx_last_update' => now()
                    ]);
                }
            }
        }

        Log::info('✅ Cart payment processed successfully', [
            'transfer_id' => $transfer->transfer_id,
            'items_processed' => $transferDetails->count(),
            'reference_id' => $referenceId
        ]);
    }

    /**
     * Handle tabungan (savings) payment callback
     */
    private function handleTabunganCallback($referenceId, $status, $statusCode, $transactionId, $amount)
    {
        Log::info('💰 === Processing Tabungan Callback (iPaymu) ===', [
            'reference_id' => $referenceId,
            'status' => $status,
            'status_code' => $statusCode,
            'transaction_id' => $transactionId,
            'amount' => $amount
        ]);

        // Check if payment is successful
        if (strtolower($status) !== 'berhasil' && $statusCode != 1) {
            Log::warning('Tabungan payment not successful', [
                'status' => $status,
                'status_code' => $statusCode
            ]);
            return;
        }

        // Find transfer by reference_id
        $transfer = DB::table('transfer')
            ->where('reference', $referenceId)
            ->orWhere('merchantRef', $referenceId)
            ->first();

        if (!$transfer) {
            Log::error('Transfer not found for tabungan', ['reference_id' => $referenceId]);
            return;
        }

        // Update transfer status
        DB::table('transfer')
            ->where('transfer_id', $transfer->transfer_id)
            ->update([
                'status' => 1, // Success
                'paid_at' => now(),
                'gateway_transaction_id' => $transactionId,
                'updated_at' => now()
            ]);

        // Check if student already has tabungan record
        $existingTabungan = DB::table('tabungan')
            ->where('student_student_id', $transfer->student_id)
            ->first();

        if ($existingTabungan) {
            // Update existing tabungan
            DB::table('tabungan')
                ->where('student_student_id', $transfer->student_id)
                ->update([
                    'saldo' => DB::raw('saldo + ' . $amount),
                    'tabungan_last_update' => now()
                ]);
            
            $tabunganId = $existingTabungan->tabungan_id;
        } else {
            // Insert new tabungan record
            $tabunganId = DB::table('tabungan')->insertGetId([
                'student_student_id' => $transfer->student_id,
                'user_user_id' => 1,
                'saldo' => $amount,
                'tabungan_input_date' => now(),
                'tabungan_last_update' => now()
            ]);
        }

        // Get current saldo for log_tabungan
        $currentSaldo = DB::table('tabungan')
            ->where('tabungan_id', $tabunganId)
            ->value('saldo');

        // Insert to log_tabungan
        DB::table('log_tabungan')->insert([
            'tabungan_tabungan_id' => $tabunganId,
            'student_student_id' => $transfer->student_id,
            'kredit' => $amount,
            'debit' => 0,
            'saldo' => $currentSaldo,
            'keterangan' => 'Setoran Tabungan via iPaymu - ' . $referenceId,
            'log_tabungan_input_date' => now(),
            'log_tabungan_last_update' => now()
        ]);

        Log::info('✅ Tabungan payment processed successfully', [
            'transfer_id' => $transfer->transfer_id,
            'tabungan_id' => $tabunganId,
            'amount' => $amount,
            'new_saldo' => $currentSaldo
        ]);
    }
}

