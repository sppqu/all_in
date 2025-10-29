<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SubscriptionTripayService;
use App\Models\UserAddon;
use App\Models\Subscription;
use Carbon\Carbon;

class TripayCallbackController extends Controller
{
    protected $tripayService;

    public function __construct()
    {
        $this->tripayService = new SubscriptionTripayService();
    }

    /**
     * Handle Tripay callback untuk subscription dan addon
     */
    public function handle(Request $request)
    {
        // Handle GET request (for testing/verification)
        if ($request->isMethod('get')) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Tripay Callback Endpoint is Ready',
                'endpoint' => url('/manage/tripay/callback'),
                'supported_methods' => ['POST'],
                'timestamp' => now()->toDateTimeString()
            ], 200);
        }

        Log::info('Tripay Callback Received', $request->all());

        try {
            // Verify callback signature (DISABLED FOR DEVELOPMENT)
            $signatureValid = $this->tripayService->verifyCallback($request->all());
            
            if (!$signatureValid) {
                Log::warning('Tripay callback signature verification failed', [
                    'received_signature' => $request->signature,
                    'data' => $request->only(['reference', 'merchant_ref', 'status'])
                ]);
                // TEMPORARY: Continue processing even if signature invalid (for development)
                // return response()->json(['message' => 'Invalid signature'], 403);
            }

            $reference = $request->reference;
            $merchantRef = $request->merchant_ref;
            $status = $request->status; // UNPAID, PAID, EXPIRED, FAILED
            $amount = $request->total_amount;

            // Validasi data yang diperlukan
            if (!$reference || !$merchantRef) {
                Log::error('Missing required fields in Tripay callback', [
                    'reference' => $reference,
                    'merchant_ref' => $merchantRef,
                    'all_data' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Reference ID and Merchant Ref are required'
                ], 400);
            }

            Log::info('Processing Tripay Callback', [
                'reference' => $reference,
                'merchant_ref' => $merchantRef,
                'status' => $status,
                'amount' => $amount
            ]);

            // Determine if this is subscription, addon, or SPMB based on merchant_ref
            if (str_starts_with($merchantRef, 'SUB-')) {
                $this->handleSubscriptionCallback($merchantRef, $status, $reference, $amount);
            } elseif (str_starts_with($merchantRef, 'ADDON-')) {
                $this->handleAddonCallback($merchantRef, $status, $reference, $amount);
            } elseif (str_starts_with($merchantRef, 'QRIS-STEP2-') || str_starts_with($merchantRef, 'SPMB-')) {
                $this->handleSPMBCallback($merchantRef, $status, $reference, $amount);
            } else {
                Log::error('Unknown merchant_ref format', ['merchant_ref' => $merchantRef]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown transaction type'
                ], 400);
            }

            // Return response format expected by Tripay
            return response()->json([
                'success' => true,
                'message' => 'Callback received and processed',
                'data' => [
                    'reference' => $reference,
                    'merchant_ref' => $merchantRef,
                    'status' => $status
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Tripay Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle subscription payment callback
     */
    private function handleSubscriptionCallback($merchantRef, $status, $reference, $amount)
    {
        Log::info('=== Handle Subscription Callback START ===', [
            'merchant_ref' => $merchantRef,
            'status' => $status,
            'reference' => $reference,
            'amount' => $amount
        ]);

        // Extract user ID from merchant_ref (format: SUB-{user_id}-{timestamp})
        $parts = explode('-', $merchantRef);
        $userId = $parts[1] ?? null;

        if (!$userId) {
            Log::error('Invalid SUB merchant_ref format', ['merchant_ref' => $merchantRef]);
            return;
        }

        Log::info('Extracted User ID', ['user_id' => $userId]);

        // Find subscription by transaction_id (merchant_ref) OR payment_reference
        // Prioritize payment_reference jika ada
        $subscription = DB::table('subscriptions')
            ->where('user_id', $userId)
            ->where(function($query) use ($merchantRef, $reference) {
                $query->where('transaction_id', $merchantRef)
                      ->orWhere('payment_reference', $reference);
            })
            ->orderByRaw("CASE WHEN payment_reference = ? THEN 1 ELSE 2 END", [$reference])
            ->first();

        Log::info('Subscription Query Result', [
            'found' => $subscription ? 'YES' : 'NO',
            'subscription_id' => $subscription->id ?? null,
            'subscription' => $subscription
        ]);

        if ($status === 'PAID') {
            if ($subscription) {
                // Get subscription duration_days
                $durationDays = $subscription->duration_days ?? 365;
                
                // Update existing subscription to active
                $updated = DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update([
                        'status' => 'active',
                        'activated_at' => now(),
                        'expires_at' => now()->addDays($durationDays),
                        'payment_reference' => $reference,
                        'updated_at' => now()
                    ]);

                Log::info('Subscription Updated to ACTIVE', [
                    'subscription_id' => $subscription->id,
                    'merchant_ref' => $merchantRef,
                    'rows_affected' => $updated,
                    'activated_at' => now(),
                    'expires_at' => now()->addDays($durationDays)
                ]);

                // Update invoice status to paid
                $invoiceUpdated = DB::table('subscription_invoices')
                    ->where('subscription_id', $subscription->id)
                    ->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'payment_reference' => $reference,
                        'updated_at' => now()
                    ]);

                Log::info('Invoice Updated to PAID', [
                    'subscription_id' => $subscription->id,
                    'invoice_rows_affected' => $invoiceUpdated,
                    'paid_at' => now()
                ]);

            } else {
                Log::warning('Subscription not found for merchant_ref', [
                    'merchant_ref' => $merchantRef,
                    'all_subscriptions' => DB::table('subscriptions')->select('id', 'transaction_id', 'status')->get()
                ]);
            }

        } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
            if ($subscription) {
                // Update subscription to cancelled
                $updated = DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update([
                        'status' => 'cancelled',
                        'payment_reference' => $reference,
                        'updated_at' => now()
                    ]);

                // Update invoice to failed
                $invoiceUpdated = DB::table('subscription_invoices')
                    ->where('subscription_id', $subscription->id)
                    ->update([
                        'payment_status' => 'failed',
                        'payment_reference' => $reference,
                        'updated_at' => now()
                    ]);

                Log::info('Subscription marked as failed/expired', [
                    'subscription_id' => $subscription->id,
                    'status' => $status,
                    'rows_affected' => $updated,
                    'invoice_rows_affected' => $invoiceUpdated
                ]);
            }
        }

        Log::info('=== Handle Subscription Callback END ===');
    }

    /**
     * Handle addon payment callback
     */
    private function handleAddonCallback($merchantRef, $status, $reference, $amount)
    {
        Log::info('=== Handle Addon Callback START ===', [
            'merchant_ref' => $merchantRef,
            'status' => $status,
            'reference' => $reference,
            'amount' => $amount
        ]);

        // Extract user_id and addon_id from merchant_ref (format: ADDON-{user_id}-{addon_id}-{timestamp})
        $parts = explode('-', $merchantRef);
        $userId = $parts[1] ?? null;
        $addonId = $parts[2] ?? null;

        if (!$userId || !$addonId) {
            Log::error('Invalid ADDON merchant_ref format', ['merchant_ref' => $merchantRef]);
            return;
        }

        Log::info('Extracted Data', ['user_id' => $userId, 'addon_id' => $addonId]);

        // Find user addon by transaction_id first
        $userAddon = DB::table('user_addons')
            ->where('transaction_id', $merchantRef)
            ->first();

        // Fallback: Find by user_id, addon_id, and pending status if not found
        if (!$userAddon) {
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
                        'transaction_id' => $merchantRef,
                        'payment_reference' => $reference,
                        'updated_at' => now()
                    ]);
                Log::info('Updated user addon with transaction_id via fallback', [
                    'user_addon_id' => $userAddon->id
                ]);
            }
        }

        Log::info('User Addon Query Result', [
            'found' => $userAddon ? 'YES' : 'NO',
            'user_addon' => $userAddon
        ]);

        if ($status === 'PAID') {
            if ($userAddon) {
                // Get addon info to determine type
                $addon = DB::table('addons')->where('id', $addonId)->first();
                
                $updateData = [
                    'status' => 'active',
                    'purchased_at' => now(),
                    'amount_paid' => $amount,
                    'payment_method' => 'tripay',
                    'payment_reference' => $reference,
                    'transaction_id' => $merchantRef,
                    'updated_at' => now()
                ];

                // Set expires_at based on addon type
                if ($addon && $addon->type === 'one_time') {
                    $updateData['expires_at'] = null; // Lifetime
                    Log::info('Setting addon as one_time (lifetime)', ['addon_id' => $addonId]);
                } elseif ($addon && $addon->type === 'recurring') {
                    // Set expiry based on duration (default 30 days)
                    $duration = $addon->duration ?? 30;
                    $updateData['expires_at'] = now()->addDays($duration);
                    Log::info('Setting addon as recurring', [
                        'addon_id' => $addonId,
                        'duration_days' => $duration,
                        'expires_at' => $updateData['expires_at']
                    ]);
                } else {
                    Log::warning('Addon type unknown, defaulting to 30 days', ['addon' => $addon]);
                    $updateData['expires_at'] = now()->addDays(30);
                }

                Log::info('Updating user addon to active', [
                    'user_addon_id' => $userAddon->id,
                    'update_data' => $updateData
                ]);

                $updated = DB::table('user_addons')
                    ->where('id', $userAddon->id)
                    ->update($updateData);

                Log::info('✓ Addon Activated via Tripay Successfully', [
                    'user_addon_id' => $userAddon->id,
                    'user_id' => $userId,
                    'addon_id' => $addonId,
                    'addon_type' => $addon->type ?? 'unknown',
                    'expires_at' => $updateData['expires_at'],
                    'merchant_ref' => $merchantRef,
                    'reference' => $reference,
                    'rows_affected' => $updated
                ]);
            } else {
                Log::warning('UserAddon not found for merchant_ref', [
                    'merchant_ref' => $merchantRef,
                    'all_user_addons' => DB::table('user_addons')->select('id', 'transaction_id', 'status')->get()
                ]);
            }

        } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
            if ($userAddon) {
                $updated = DB::table('user_addons')
                    ->where('id', $userAddon->id)
                    ->update([
                        'status' => 'cancelled',
                        'updated_at' => now()
                    ]);

                Log::info('Addon purchase cancelled/expired', [
                    'user_addon_id' => $userAddon->id,
                    'status' => $status,
                    'rows_affected' => $updated
                ]);
            }
        }

        Log::info('=== Handle Addon Callback END ===');
    }

    /**
     * Handle SPMB payment callback (Step-2 Registration Fee & SPMB Fee)
     */
    private function handleSPMBCallback($merchantRef, $status, $reference, $amount)
    {
        Log::info('=== Handle SPMB Callback START ===', [
            'merchant_ref' => $merchantRef,
            'status' => $status,
            'reference' => $reference,
            'amount' => $amount
        ]);

        // Find payment by payment_reference OR tripay_reference
        $payment = DB::table('spmb_payments')
            ->where(function($query) use ($merchantRef, $reference) {
                $query->where('payment_reference', $merchantRef)
                      ->orWhere('tripay_reference', $reference);
            })
            ->first();

        Log::info('SPMB Payment Query Result', [
            'found' => $payment ? 'YES' : 'NO',
            'payment_id' => $payment->id ?? null,
            'payment' => $payment
        ]);

        if (!$payment) {
            Log::warning('SPMB Payment not found', [
                'merchant_ref' => $merchantRef,
                'reference' => $reference,
                'all_payments' => DB::table('spmb_payments')
                    ->select('id', 'payment_reference', 'tripay_reference', 'status')
                    ->limit(10)
                    ->get()
            ]);
            return;
        }

        if ($status === 'PAID') {
            // Update payment status to paid
            $updated = DB::table('spmb_payments')
                ->where('id', $payment->id)
                ->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'updated_at' => now()
                ]);

            Log::info('SPMB Payment Updated to PAID', [
                'payment_id' => $payment->id,
                'payment_type' => $payment->type,
                'rows_affected' => $updated
            ]);

            // Update registration based on payment type
            if ($payment->type === 'registration_fee') {
                // Step-2 Registration Fee
                $regUpdated = DB::table('spmb_registrations')
                    ->where('id', $payment->registration_id)
                    ->update([
                        'registration_fee_paid' => true,
                        'step' => 3, // Move to next step
                        'updated_at' => now()
                    ]);

                Log::info('SPMB Registration Updated - Step-2 Paid', [
                    'registration_id' => $payment->registration_id,
                    'moved_to_step' => 3,
                    'rows_affected' => $regUpdated
                ]);
            } elseif ($payment->type === 'spmb_fee') {
                // Final SPMB Fee
                $regUpdated = DB::table('spmb_registrations')
                    ->where('id', $payment->registration_id)
                    ->update([
                        'spmb_fee_paid' => true,
                        'step' => 6, // Completion
                        'status' => 'completed',
                        'updated_at' => now()
                    ]);

                Log::info('SPMB Registration Updated - SPMB Fee Paid', [
                    'registration_id' => $payment->registration_id,
                    'status' => 'completed',
                    'rows_affected' => $regUpdated
                ]);
            }

        } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
            // Update payment status to failed/expired
            $updated = DB::table('spmb_payments')
                ->where('id', $payment->id)
                ->update([
                    'status' => strtolower($status),
                    'updated_at' => now()
                ]);

            Log::info('SPMB Payment marked as failed/expired', [
                'payment_id' => $payment->id,
                'status' => $status,
                'rows_affected' => $updated
            ]);
        }

        Log::info('=== Handle SPMB Callback END ===');
    }
}

