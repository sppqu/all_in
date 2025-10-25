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
        
        // Check if user already has this addon
        $userAddon = UserAddon::where('user_id', $user->id)
            ->where('addon_id', $addon->id)
            ->where('status', 'active')
            ->first();

        return view('addons.show', compact('addon', 'userAddon'));
    }

    public function purchase(Request $request, $slug)
    {
        $request->validate([
            'payment_method' => 'required|string'
        ]);

        $addon = Addon::where('slug', $slug)->active()->firstOrFail();
        $user = auth()->user();

        // Check if user already has this addon
        $existingAddon = UserAddon::where('user_id', $user->id)
            ->where('addon_id', $addon->id)
            ->where('status', 'active')
            ->first();

        if ($existingAddon) {
            return back()->with('error', 'Anda sudah memiliki add-on ini.');
        }

        // Create user addon record
        $userAddonId = UserAddon::insertGetId([
            'user_id' => $user->id,
            'addon_id' => $addon->id,
            'status' => 'pending',
            'purchased_at' => now(),
            'amount_paid' => $addon->price,
            'payment_method' => $request->payment_method,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Prepare Midtrans parameters
        $params = [
            'transaction_details' => [
                'order_id' => 'ADDON-' . $userAddonId . '-' . time(),
                'gross_amount' => $addon->price
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? ''
            ],
            'item_details' => [
                [
                    'id' => $addon->slug,
                    'price' => $addon->price,
                    'quantity' => 1,
                    'name' => $addon->name . ' - SPPQU Add-on'
                ]
            ],
            'enabled_payments' => [
                'credit_card', 'bca_va', 'bni_va', 'bri_va', 'mandiri_clickpay',
                'gopay', 'indomaret', 'danamon_online', 'akulaku'
            ],
            'callbacks' => [
                'finish' => url('/addons/callback'),
                'error' => url('/addons/callback'),
                'pending' => url('/addons/callback')
            ]
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            // Update user addon with snap token
            UserAddon::where('id', $userAddonId)->update([
                'transaction_id' => $params['transaction_details']['order_id'],
                'updated_at' => now()
            ]);

            return view('addons.payment', compact('snapToken', 'addon', 'userAddonId'));

        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memproses pembayaran');
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
}
