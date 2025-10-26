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
                return response()->json(['message' => 'Unknown transaction type'], 400);
            }

            return response()->json(['message' => 'Callback processed successfully']);

        } catch (\Exception $e) {
            Log::error('Tripay Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle subscription payment callback
     */
    private function handleSubscriptionCallback($merchantRef, $status, $reference, $amount)
    {
        // Extract user ID from merchant_ref (format: SUB-{user_id}-{timestamp})
        $parts = explode('-', $merchantRef);
        $userId = $parts[1] ?? null;

        if (!$userId) {
            Log::error('Invalid SUB merchant_ref format', ['merchant_ref' => $merchantRef]);
            return;
        }

        // Find subscription by merchant_ref or create new if PAID
        $subscription = Subscription::where('transaction_id', $merchantRef)->first();

        if ($status === 'PAID') {
            if ($subscription) {
                // Update existing subscription
                $subscription->update([
                    'status' => 'active',
                    'payment_status' => 'paid',
                    'payment_reference' => $reference,
                    'updated_at' => now()
                ]);

                Log::info('Subscription updated to PAID', [
                    'subscription_id' => $subscription->id,
                    'merchant_ref' => $merchantRef
                ]);
            } else {
                Log::warning('Subscription not found for merchant_ref', [
                    'merchant_ref' => $merchantRef
                ]);
            }

        } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
            if ($subscription) {
                $subscription->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                    'payment_reference' => $reference,
                    'updated_at' => now()
                ]);

                Log::info('Subscription marked as failed/expired', [
                    'subscription_id' => $subscription->id,
                    'status' => $status
                ]);
            }
        }
    }

    /**
     * Handle addon payment callback
     */
    private function handleAddonCallback($merchantRef, $status, $reference, $amount)
    {
        // Extract user_id and addon_id from merchant_ref (format: ADDON-{user_id}-{addon_id}-{timestamp})
        $parts = explode('-', $merchantRef);
        $userId = $parts[1] ?? null;
        $addonId = $parts[2] ?? null;

        if (!$userId || !$addonId) {
            Log::error('Invalid ADDON merchant_ref format', ['merchant_ref' => $merchantRef]);
            return;
        }

        // Find user addon by merchant_ref
        $userAddon = UserAddon::where('transaction_id', $merchantRef)->first();

        if ($status === 'PAID') {
            if ($userAddon) {
                // Update to active
                $addon = $userAddon->addon;
                
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

                $userAddon->update($updateData);

                Log::info('Addon activated via Tripay', [
                    'user_addon_id' => $userAddon->id,
                    'user_id' => $userId,
                    'addon_id' => $addonId,
                    'merchant_ref' => $merchantRef
                ]);
            } else {
                Log::warning('UserAddon not found for merchant_ref', [
                    'merchant_ref' => $merchantRef
                ]);
            }

        } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
            if ($userAddon) {
                $userAddon->update([
                    'status' => 'cancelled',
                    'updated_at' => now()
                ]);

                Log::info('Addon purchase cancelled/expired', [
                    'user_addon_id' => $userAddon->id,
                    'status' => $status
                ]);
            }
        }
    }
}

