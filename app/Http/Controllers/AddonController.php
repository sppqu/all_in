<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Addon;
use App\Models\UserAddon;
use Carbon\Carbon;

class AddonController extends Controller
{
    private $midtransServerKey = 'SB-Mid-server-kPdWjzufT77jNCgM7EQTYIz5';
    private $midtransClientKey = 'SB-Mid-client-lJRoDoWDFqA6NzlJ';
    private $isProduction = false;

    public function __construct()
    {
        \Midtrans\Config::$serverKey = $this->midtransServerKey;
        \Midtrans\Config::$clientKey = $this->midtransClientKey;
        \Midtrans\Config::$isProduction = $this->isProduction;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    public function index()
    {
        $addons = Addon::active()->get();
        $user = auth()->user();
        
        // Get user's active addons with valid addon relationships
        $userAddons = UserAddon::where('user_id', $user->id)
            ->where('status', 'active')
            ->withValidAddon() // Only get records that have a valid addon relationship
            ->with('addon')
            ->get();

        return view('addons.index', compact('addons', 'userAddons'));
    }

    public function show($slug)
    {
        $addon = Addon::where('slug', $slug)->active()->firstOrFail();
        $user = auth()->user();
        
        // Check addon ownership
        // Non-superadmin inherit addons from superadmin
        if ($user->role === 'superadmin') {
            // Superadmin: check their own addons
            $userAddon = UserAddon::where('user_id', $user->id)
                ->where('addon_id', $addon->id)
                ->where('status', 'active')
                ->first();
        } else {
            // Non-superadmin: check superadmin's addons
            $superadminId = getSuperadminId();
            $userAddon = $superadminId ? UserAddon::where('user_id', $superadminId)
                ->where('addon_id', $addon->id)
                ->where('status', 'active')
                ->first() : null;
        }

        // Only superadmin can purchase
        $canPurchase = ($user->role === 'superadmin');

        return view('addons.show', compact('addon', 'userAddon', 'canPurchase'));
    }

    public function purchase(Request $request, $slug)
    {
        $user = auth()->user();

        // Only superadmin can purchase addons
        if ($user->role !== 'superadmin') {
            return back()->with('error', 'Hanya superadmin yang dapat membeli add-on. User lain akan otomatis mendapatkan akses dari add-on yang dibeli superadmin.');
        }

        $request->validate([
            'payment_method' => 'required|string'
        ]);

        $addon = Addon::where('slug', $slug)->active()->firstOrFail();

        // Check if user already has this addon
        $existingAddon = UserAddon::where('user_id', $user->id)
            ->where('addon_id', $addon->id)
            ->where('status', 'active')
            ->first();

        if ($existingAddon) {
            return back()->with('error', 'Anda sudah memiliki add-on ini.');
        }

        // Delete old pending addon purchases untuk user + addon ini (cleanup duplikat)
        DB::table('user_addons')
            ->where('user_id', $user->id)
            ->where('addon_id', $addon->id)
            ->where('status', 'pending')
            ->whereNull('payment_reference')
            ->delete();

        // Create user addon record
        $userAddonId = DB::table('user_addons')->insertGetId([
            'user_id' => $user->id,
            'addon_id' => $addon->id,
            'status' => 'pending',
            'purchased_at' => null, // Will be filled when paid
            'amount_paid' => $addon->price,
            'payment_method' => $request->payment_method,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        try {
            // Use Tripay payment gateway
            $tripay = new \App\Services\SubscriptionTripayService();
            $merchantRef = 'ADDON-' . $user->id . '-' . $addon->id . '-' . time();
            
            $result = $tripay->createSubscriptionPayment([
                'user_id' => $user->id,
                'method' => $request->payment_method, // QRIS, BRIVA, BCAVA, dll
                'amount' => $addon->price,
                'plan_name' => 'SPPQU Addon - ' . $addon->name,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone ?? '08123456789',
                'return_url' => route('manage.addons.index'),
                'callback_url' => url('/api/manage/tripay/callback')
            ]);

            if (!$result['success']) {
                // Delete user addon if payment creation failed
                DB::table('user_addons')->where('id', $userAddonId)->delete();
                
                return back()->with('error', 'Gagal membuat transaksi pembayaran: ' . ($result['message'] ?? 'Unknown error'));
            }
            
            // Update user addon with transaction info
            DB::table('user_addons')
                ->where('id', $userAddonId)
                ->update([
                    'transaction_id' => $result['merchant_ref'],
                    'payment_reference' => $result['reference'] ?? null,
                    'updated_at' => now()
                ]);

            Log::info('Addon payment created via Tripay', [
                'user_id' => $user->id,
                'addon_id' => $addon->id,
                'user_addon_id' => $userAddonId,
                'merchant_ref' => $result['merchant_ref'],
                'reference' => $result['reference']
            ]);

            // Return Tripay payment page
            return view('addons.payment-tripay', compact('result', 'userAddonId', 'addon'));

        } catch (\Exception $e) {
            // Delete user addon if error occurred
            DB::table('user_addons')->where('id', $userAddonId)->delete();
            
            Log::error('Tripay Payment Error for Addon: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'addon_slug' => $slug,
                'payment_method' => $request->payment_method
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        Log::info('Addon Midtrans Callback received:', $request->all());
        
        try {
            // Handle both JSON and form data
            $orderId = $request->input('order_id') ?? $request->order_id;
            $status = $request->input('transaction_status') ?? $request->transaction_status;
            $fraudStatus = $request->input('fraud_status') ?? $request->fraud_status ?? null;
            
            Log::info("Processing addon callback - Order: {$orderId}, Status: {$status}, Fraud: {$fraudStatus}");

            // Extract user addon ID from order ID (format: ADDON-{id}-{timestamp})
            $parts = explode('-', $orderId);
            $userAddonId = $parts[1] ?? null;

            if (!$userAddonId) {
                Log::error("Invalid order ID format: {$orderId}");
                return response()->json(['error' => 'Invalid order ID'], 400);
            }

            $userAddon = UserAddon::find($userAddonId);
            
            if (!$userAddon) {
                Log::error("User addon not found: {$userAddonId}");
                return response()->json(['error' => 'User addon not found'], 404);
            }

            Log::info("Found user addon: {$userAddonId}, current status: {$userAddon->status}");

            // Update user addon based on transaction status
            switch ($status) {
                case 'capture':
                    if ($fraudStatus == 'challenge') {
                        $this->updateUserAddonStatus($userAddonId, 'challenge');
                        Log::info("User addon {$userAddonId} status updated to challenge");
                    } else if ($fraudStatus == 'accept') {
                        $this->activateUserAddon($userAddonId);
                        Log::info("User addon {$userAddonId} activated (capture accept)");
                    }
                    break;
                    
                case 'settlement':
                    $this->activateUserAddon($userAddonId);
                    Log::info("User addon {$userAddonId} activated (settlement)");
                    break;
                    
                case 'pending':
                    $this->updateUserAddonStatus($userAddonId, 'pending');
                    Log::info("User addon {$userAddonId} status updated to pending");
                    break;
                    
                case 'deny':
                    $this->updateUserAddonStatus($userAddonId, 'denied');
                    Log::info("User addon {$userAddonId} status updated to denied");
                    break;
                    
                case 'expire':
                    $this->updateUserAddonStatus($userAddonId, 'expired');
                    Log::info("User addon {$userAddonId} status updated to expired");
                    break;
                    
                case 'cancel':
                    $this->updateUserAddonStatus($userAddonId, 'cancelled');
                    Log::info("User addon {$userAddonId} status updated to cancelled");
                    break;
                    
                default:
                    Log::warning("Unknown transaction status: {$status}");
                    break;
            }

            // Return appropriate response based on request type
            if ($request->isMethod('post')) {
                // Notification from Midtrans
                return response()->json(['status' => 'OK']);
            } else {
                // Redirect from user
                $message = 'Status pembayaran add-on telah diperbarui';
                if ($status == 'settlement' || ($status == 'capture' && $fraudStatus == 'accept')) {
                    $message = 'Pembayaran berhasil! Add-on Anda telah aktif.';
                }
                return redirect()->route('manage.addons.index')->with('success', $message);
            }

        } catch (\Exception $e) {
            Log::error('Addon callback error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function activateUserAddon($userAddonId)
    {
        $userAddon = UserAddon::find($userAddonId);
        
        if ($userAddon) {
            UserAddon::where('id', $userAddonId)
                ->update([
                    'status' => 'active',
                    'updated_at' => now()
                ]);

            Log::info("User addon activated: {$userAddonId}");
        }
    }

    private function updateUserAddonStatus($userAddonId, $status)
    {
        UserAddon::where('id', $userAddonId)
            ->update([
                'status' => $status,
                'updated_at' => now()
            ]);

        Log::info("User addon status updated: {$userAddonId} -> {$status}");
    }

    /**
     * Manually activate addon for a user (admin only)
     */
    public function manualActivate($userId, $slug)
    {
        try {
            // Find addon by slug
            $addon = Addon::where('slug', $slug)->first();
            
            if (!$addon) {
                return response()->json([
                    'success' => false,
                    'error' => 'Addon tidak ditemukan'
                ], 404);
            }
            
            // Check if user exists
            $user = \DB::table('users')->find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User tidak ditemukan'
                ], 404);
            }
            
            // Check if already active
            $existingAddon = UserAddon::where('user_id', $userId)
                ->where('addon_id', $addon->id)
                ->where('status', 'active')
                ->first();
            
            if ($existingAddon) {
                return response()->json([
                    'success' => false,
                    'error' => 'Addon sudah aktif untuk user ini'
                ]);
            }
            
            // Find existing pending/inactive addon or create new
            $userAddon = UserAddon::where('user_id', $userId)
                ->where('addon_id', $addon->id)
                ->first();
            
            if ($userAddon) {
                // Update existing
                $userAddon->update([
                    'status' => 'active',
                    'purchased_at' => now(),
                    'amount_paid' => 0, // Manual activation - no payment
                    'payment_method' => 'manual',
                    'expires_at' => $addon->type === 'one_time' ? null : now()->addYear(),
                    'updated_at' => now()
                ]);
            } else {
                // Create new
                UserAddon::create([
                    'user_id' => $userId,
                    'addon_id' => $addon->id,
                    'status' => 'active',
                    'purchased_at' => now(),
                    'amount_paid' => 0,
                    'payment_method' => 'manual',
                    'expires_at' => $addon->type === 'one_time' ? null : now()->addYear(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            Log::info("Addon manually activated", [
                'user_id' => $userId,
                'addon_slug' => $slug,
                'addon_id' => $addon->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Addon {$addon->name} berhasil diaktifkan untuk User ID: {$userId}"
            ]);
            
        } catch (\Exception $e) {
            Log::error("Manual addon activation error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Manually deactivate addon for a user (admin only)
     */
    public function manualDeactivate($userId, $slug)
    {
        try {
            // Find addon by slug
            $addon = Addon::where('slug', $slug)->first();
            
            if (!$addon) {
                return response()->json([
                    'success' => false,
                    'error' => 'Addon tidak ditemukan'
                ], 404);
            }
            
            // Find user addon
            $userAddon = UserAddon::where('user_id', $userId)
                ->where('addon_id', $addon->id)
                ->where('status', 'active')
                ->first();
            
            if (!$userAddon) {
                return response()->json([
                    'success' => false,
                    'error' => 'Addon tidak aktif untuk user ini'
                ]);
            }
            
            // Deactivate
            $userAddon->update([
                'status' => 'inactive',
                'updated_at' => now()
            ]);
            
            Log::info("Addon manually deactivated", [
                'user_id' => $userId,
                'addon_slug' => $slug,
                'addon_id' => $addon->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Addon {$addon->name} berhasil dinonaktifkan untuk User ID: {$userId}"
            ]);
            
        } catch (\Exception $e) {
            Log::error("Manual addon deactivation error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkUserAddon($slug)
    {
        $user = auth()->user();
        $addon = Addon::where('slug', $slug)->first();
        
        if (!$addon) {
            return response()->json(['has_addon' => false]);
        }

        $userAddon = UserAddon::where('user_id', $user->id)
            ->where('addon_id', $addon->id)
            ->where('status', 'active')
            ->first();

        return response()->json([
            'has_addon' => $userAddon ? true : false,
            'addon_name' => $addon->name
        ]);
    }

    public function refreshAddonStatus(Request $request)
    {
        $user = auth()->user();
        $addonSlug = $request->input('addon_slug');
        
        if (!$addonSlug) {
            return response()->json(['error' => 'Addon slug required'], 400);
        }

        $addon = Addon::where('slug', $addonSlug)->first();
        
        if (!$addon) {
            return response()->json(['error' => 'Addon not found'], 404);
        }

        $userAddon = UserAddon::where('user_id', $user->id)
            ->where('addon_id', $addon->id)
            ->where('status', 'active')
            ->first();

        return response()->json([
            'has_addon' => $userAddon ? true : false,
            'addon_name' => $addon->name,
            'status' => $userAddon ? $userAddon->status : 'not_owned'
        ]);
    }

    /**
     * Check addon payment status from Tripay API
     */
    public function checkPaymentStatus(Request $request)
    {
        try {
            $reference = $request->input('reference');
            $addonId = $request->input('addon_id');
            
            if (!$reference || !$addonId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reference ID and Addon ID are required'
                ], 400);
            }

            Log::info('Checking addon payment status from Tripay', [
                'reference' => $reference,
                'addon_id' => $addonId
            ]);

            // Get transaction detail from Tripay API
            $tripay = new \App\Services\SubscriptionTripayService();
            $transaction = $tripay->getTransactionDetail($reference);

            if (!$transaction || !isset($transaction['data'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            $data = $transaction['data'];
            $status = $data['status'] ?? 'UNPAID'; // UNPAID, PAID, EXPIRED, FAILED
            $merchantRef = $data['merchant_ref'] ?? null;

            Log::info('Tripay addon transaction status', [
                'reference' => $reference,
                'merchant_ref' => $merchantRef,
                'status' => $status
            ]);

            // If status is PAID, update user_addon in database
            if ($status === 'PAID' && $merchantRef) {
                $userAddon = DB::table('user_addons')
                    ->where('transaction_id', $merchantRef)
                    ->orWhere('payment_reference', $reference)
                    ->first();

                if ($userAddon) {
                    // Check if already active
                    if ($userAddon->status === 'active') {
                        return response()->json([
                            'success' => true,
                            'status' => 'paid',
                            'message' => 'Addon already active',
                            'user_addon' => $userAddon
                        ]);
                    }

                    // Get addon info
                    $addon = DB::table('addons')->where('id', $addonId)->first();

                    // Update to active
                    DB::table('user_addons')
                        ->where('id', $userAddon->id)
                        ->update([
                            'status' => 'active',
                            'purchased_at' => now(),
                            'payment_reference' => $reference,
                            'expires_at' => $addon && $addon->type === 'one_time' ? null : now()->addDays(30),
                            'updated_at' => now()
                        ]);

                    Log::info('Addon activated from check status', [
                        'user_addon_id' => $userAddon->id,
                        'reference' => $reference
                    ]);

                    return response()->json([
                        'success' => true,
                        'status' => 'paid',
                        'message' => 'Pembayaran berhasil! Addon telah aktif.',
                        'redirect_url' => route('manage.addons.index')
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'status' => strtolower($status),
                'message' => $this->getStatusMessage($status),
                'data' => [
                    'status' => $status,
                    'reference' => $reference,
                    'merchant_ref' => $merchantRef
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Check addon payment status error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error checking payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user-friendly status message
     */
    private function getStatusMessage($status)
    {
        $messages = [
            'UNPAID' => 'Menunggu pembayaran',
            'PAID' => 'Pembayaran berhasil',
            'EXPIRED' => 'Pembayaran kadaluarsa',
            'FAILED' => 'Pembayaran gagal'
        ];

        return $messages[$status] ?? 'Status tidak diketahui';
    }
}
