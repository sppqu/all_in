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

            Log::info('Processing Tripay Callback', [
                'reference' => $reference,
                'merchant_ref' => $merchantRef,
                'status' => $status,
                'amount' => $amount
            ]);

            // Determine if this is subscription or addon based on merchant_ref
            if (str_starts_with($merchantRef, 'SUB-')) {
                $this->handleSubscriptionCallback($merchantRef, $status, $reference, $amount);
            } elseif (str_starts_with($merchantRef, 'ADDON-')) {
                $this->handleAddonCallback($merchantRef, $status, $reference, $amount);
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

        // Find subscription by transaction_id (merchant_ref)
        $subscription = DB::table('subscriptions')
            ->where('transaction_id', $merchantRef)
            ->first();

        Log::info('Subscription Query Result', [
            'found' => $subscription ? 'YES' : 'NO',
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
            } else {
                Log::warning('Subscription not found for merchant_ref', [
                    'merchant_ref' => $merchantRef,
                    'all_subscriptions' => DB::table('subscriptions')->select('id', 'transaction_id', 'status')->get()
                ]);
            }

        } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
            if ($subscription) {
                $updated = DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update([
                        'status' => 'cancelled',
                        'payment_reference' => $reference,
                        'updated_at' => now()
                    ]);

                Log::info('Subscription marked as failed/expired', [
                    'subscription_id' => $subscription->id,
                    'status' => $status,
                    'rows_affected' => $updated
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

        // Find user addon by transaction_id
        $userAddon = DB::table('user_addons')
            ->where('transaction_id', $merchantRef)
            ->first();

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
                    'updated_at' => now()
                ];

                // Set expires_at based on addon type
                if ($addon && $addon->type === 'one_time') {
                    $updateData['expires_at'] = null; // Lifetime
                } elseif ($addon && $addon->type === 'recurring') {
                    // Set expiry based on duration (default 30 days)
                    $updateData['expires_at'] = now()->addDays(30);
                }

                $updated = DB::table('user_addons')
                    ->where('id', $userAddon->id)
                    ->update($updateData);

                Log::info('Addon Activated via Tripay', [
                    'user_addon_id' => $userAddon->id,
                    'user_id' => $userId,
                    'addon_id' => $addonId,
                    'addon_type' => $addon->type ?? 'unknown',
                    'expires_at' => $updateData['expires_at'],
                    'merchant_ref' => $merchantRef,
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
}

