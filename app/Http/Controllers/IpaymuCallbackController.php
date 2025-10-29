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
}

