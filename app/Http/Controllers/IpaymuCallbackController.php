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
            
            // Verify signature
            $ipaymu = new IpaymuService();
            if (!$ipaymu->verifyCallbackSignature($callbackData, $receivedSignature)) {
                Log::error('iPaymu callback signature verification failed', [
                    'received_signature' => $receivedSignature,
                    'data' => $callbackData
                ]);
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
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
        Log::info('Processing Addon Callback', [
            'reference_id' => $referenceId,
            'status' => $status,
            'status_code' => $statusCode
        ]);

        // Find user addon by transaction_id (reference_id)
        $userAddon = DB::table('user_addons')
            ->where('transaction_id', $referenceId)
            ->first();

        if (!$userAddon) {
            Log::error('User addon not found for reference_id: ' . $referenceId);
            return;
        }

        // Map iPaymu status to our status
        $newStatus = 'pending';
        if (strtolower($status) === 'berhasil' || $statusCode == 1) {
            $newStatus = 'active';
        } elseif (strtolower($status) === 'pending' || $statusCode == 0) {
            $newStatus = 'pending';
        } elseif (strtolower($status) === 'expired' || strtolower($status) === 'failed') {
            $newStatus = 'expired';
        }

        Log::info('Updating user addon status', [
            'user_addon_id' => $userAddon->id,
            'old_status' => $userAddon->status,
            'new_status' => $newStatus
        ]);

        // Update user addon
        DB::table('user_addons')
            ->where('id', $userAddon->id)
            ->update([
                'status' => $newStatus,
                'payment_reference' => $transactionId,
                'updated_at' => now()
            ]);

        // If active (paid), set purchase date and expiry
        if ($newStatus === 'active') {
            // Get addon details
            $addon = DB::table('addons')->where('id', $userAddon->addon_id)->first();
            
            $purchasedAt = now();
            $expiresAt = null;
            
            // If addon is recurring, set expiry date
            if ($addon && $addon->type === 'recurring') {
                $duration = $addon->duration ?? 30;
                $expiresAt = $purchasedAt->copy()->addDays($duration);
            }

            DB::table('user_addons')
                ->where('id', $userAddon->id)
                ->update([
                    'purchased_at' => $purchasedAt,
                    'expires_at' => $expiresAt,
                    'updated_at' => now()
                ]);

            Log::info('User addon activated', [
                'user_addon_id' => $userAddon->id,
                'purchased_at' => $purchasedAt,
                'expires_at' => $expiresAt
            ]);
        }
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

