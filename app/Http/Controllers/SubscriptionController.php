<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\SubscriptionInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class SubscriptionController extends BaseController
{
    public function __construct()
    {
        // Hanya admin/superadmin yang bisa mengakses subscription
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'superadmin'])) {
                return redirect()->route('manage.dashboard')->with('error', 'Hanya admin yang dapat mengakses halaman berlangganan.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $user = auth()->user();

        // Cleanup: Delete old pending subscriptions tanpa payment_reference (duplikat)
        DB::table('subscriptions')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereNull('payment_reference')
            ->where('created_at', '<', now()->subHours(24)) // Older than 24 hours
            ->delete();

        // Ambil semua subscription dari admin/superadmin (exclude pending yang tidak ada payment_reference)
        $subscriptions = \App\Models\Subscription::with(['invoice', 'user'])
            ->join('users', 'subscriptions.user_id', '=', 'users.id')
            ->whereIn('users.role', ['admin', 'superadmin'])
            ->where(function($query) {
                $query->where('subscriptions.status', '!=', 'pending')
                      ->orWhereNotNull('subscriptions.payment_reference');
            })
            ->select('subscriptions.*')
            ->orderBy('subscriptions.created_at', 'desc')
            ->get();

        $activeSubscription = \App\Models\Subscription::with(['invoice', 'user'])
            ->join('users', 'subscriptions.user_id', '=', 'users.id')
            ->whereIn('users.role', ['admin', 'superadmin'])
            ->where('subscriptions.status', 'active')
            ->where('subscriptions.expires_at', '>', now())
            ->select('subscriptions.*')
            ->orderBy('subscriptions.created_at', 'desc')
            ->first();

        // Ambil riwayat pembelian addon dari superadmin
        $addonPurchases = DB::table('user_addons')
            ->join('users', 'user_addons.user_id', '=', 'users.id')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->whereIn('users.role', ['admin', 'superadmin'])
            ->where(function($query) {
                $query->where('user_addons.status', '!=', 'pending')
                      ->orWhereNotNull('user_addons.payment_reference');
            })
            ->select(
                'user_addons.*',
                'users.name as user_name',
                'users.email as user_email',
                'addons.name as addon_name',
                'addons.slug as addon_slug',
                'addons.type as addon_type'
            )
            ->orderBy('user_addons.created_at', 'desc')
            ->get();

        return view('subscription.index', compact('subscriptions', 'activeSubscription', 'addonPurchases'));
    }

    public function showPlans()
    {
        $plans = [
            [
                'id' => '1_bulan',
                'name' => '1 Bulan',
                'price' => 189000,
                'duration' => 30,
                'original_price' => 189000,
                'discount' => 0,
                'features' => [
                    'Akses penuh ke semua fitur SPPQU',
                    'Laporan unlimited',
                    'Support email & WhatsApp',
                    'Update otomatis',
                    'Backup data harian',
                    'API access'
                ]
            ],
            [
                'id' => '6_bulan',
                'name' => '6 Bulan',
                'price' => 1020000, // 170000 x 6
                'duration' => 180,
                'original_price' => 1134000, // 189000 x 6
                'discount' => 10, // 10% discount
                'monthly_price' => 170000,
                'features' => [
                    'Semua fitur 1 Bulan',
                    'Diskon 10% dari harga normal',
                    'Support prioritas',
                    'Backup data harian',
                    'API access unlimited',
                    'Custom branding'
                ]
            ],
            [
                'id' => '12_bulan',
                'name' => '12 Bulan',
                'price' => 1800000, // 150000 x 12
                'duration' => 365,
                'original_price' => 2268000, // 189000 x 12
                'discount' => 21, // 21% discount
                'monthly_price' => 150000,
                'features' => [
                    'Semua fitur 6 Bulan',
                    'Diskon 21% dari harga normal',
                    'Support 24/7',
                    'Backup data harian',
                    'API access unlimited',
                    'Custom branding',
                    'Priority updates'
                ]
            ],
            [
                'id' => '24_bulan',
                'name' => '24 Bulan',
                'price' => 3000000, // 125000 x 24
                'duration' => 730,
                'original_price' => 4536000, // 189000 x 24
                'discount' => 34, // 34% discount
                'monthly_price' => 125000,
                'features' => [
                    'Semua fitur 12 Bulan',
                    'Diskon 34% dari harga normal',
                    'Support 24/7 prioritas',
                    'Backup data real-time',
                    'API access unlimited',
                    'Custom branding',
                    'Priority updates',
                    'Dedicated support'
                ]
            ]
        ];

        return view('subscription.plans', compact('plans'));
    }

    // DEPRECATED: Method payment() lama dengan Midtrans sudah tidak digunakan
    // Sekarang semua pembayaran menggunakan Tripay melalui createSubscription()
    public function payment($subscription_id)
    {
        Log::info('Deprecated payment() method called', ['subscription_id' => $subscription_id]);
        
        // Redirect ke halaman subscription
        return redirect()->route('manage.subscription.index')
            ->with('info', 'Silakan gunakan fitur perpanjangan berlangganan yang baru dengan Tripay.');
    }

    public function createSubscription(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|string',
            'payment_method' => 'required|string'
        ]);

        $plans = [
            '1_bulan' => ['price' => 189000, 'duration' => 30, 'name' => '1 Bulan'],
            '6_bulan' => ['price' => 1020000, 'duration' => 180, 'name' => '6 Bulan'],
            '12_bulan' => ['price' => 1800000, 'duration' => 365, 'name' => '12 Bulan'],
            '24_bulan' => ['price' => 3000000, 'duration' => 730, 'name' => '24 Bulan']
        ];

        if (!isset($plans[$request->plan_id])) {
            return back()->with('error', 'Plan tidak valid');
        }

        $plan = $plans[$request->plan_id];
        $user = auth()->user();

        // Delete old pending subscriptions untuk user ini (cleanup duplikat)
        DB::table('subscriptions')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereNull('payment_reference') // Belum pernah dapat payment reference
            ->delete();

        Log::info('Creating new subscription', [
            'user_id' => $user->id,
            'plan_id' => $request->plan_id,
            'plan_name' => $plan['name'],
            'amount' => $plan['price']
        ]);

        // Create subscription record
        $subscriptionId = DB::table('subscriptions')->insertGetId([
            'user_id' => $user->id,
            'plan_id' => $request->plan_id,
            'plan_name' => $plan['name'],
            'amount' => $plan['price'],
            'duration_days' => $plan['duration'],
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Get phone number - check multiple possible column names
        $customerPhone = $user->nomor_wa ?? $user->phone ?? $user->no_hp ?? '08123456789';
        
        // Create invoice
        $invoice = SubscriptionInvoice::create([
            'subscription_id' => $subscriptionId,
            'invoice_number' => SubscriptionInvoice::generateInvoiceNumber(),
            'plan_name' => $plan['name'],
            'amount' => $plan['price'],
            'currency' => 'IDR',
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'due_date' => now()->addDays(7), // Due in 7 days
            'billing_details' => [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $customerPhone,
                'plan_duration' => $plan['duration'] . ' hari'
            ]
        ]);

        // Use iPaymu for payment gateway with ENV config (for internal system/subscription)
        try {
            $ipaymu = new \App\Services\IpaymuService(true); // true = use ENV config
            
            $result = $ipaymu->createSubscriptionPayment([
                'user_id' => $user->id,
                'method' => $request->payment_method, // QRIS, VA, dll
                'amount' => $plan['price'],
                'plan_name' => 'SPPQU Subscription - ' . $plan['name'],
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $customerPhone,
                'return_url' => route('manage.subscription.index'),
                'callback_url' => url('/api/manage/ipaymu/callback')
            ]);

            if (!$result['success']) {
                // Delete subscription and invoice if payment creation failed
                DB::table('subscriptions')->where('id', $subscriptionId)->delete();
                $invoice->delete();
                
                return back()->with('error', 'Gagal membuat transaksi pembayaran: ' . ($result['message'] ?? 'Unknown error'));
            }
            
            // Update subscription with transaction info
            DB::table('subscriptions')
                ->where('id', $subscriptionId)
                ->update([
                    'transaction_id' => $result['reference_id'],
                    'payment_reference' => $result['transaction_id'] ?? null,
                    'payment_url' => $result['payment_url'] ?? null,
                    'updated_at' => now()
                ]);

            // Update invoice with order ID
            $invoice->update([
                'midtrans_order_id' => $result['reference_id'],
                'payment_reference' => $result['transaction_id'] ?? null
            ]);

            // Return iPaymu payment page
            return view('subscription.payment-ipaymu', compact('result', 'subscriptionId', 'plan', 'invoice'));

        } catch (\Exception $e) {
            // Delete subscription and invoice if error occurred
            DB::table('subscriptions')->where('id', $subscriptionId)->delete();
            $invoice->delete();
            
            Log::error('Tripay Payment Error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'plan_id' => $request->plan_id,
                'payment_method' => $request->payment_method
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        Log::info('Midtrans Callback received:', $request->all());
        
        try {
            // Handle both notification and redirect
            $orderId = $request->order_id;
            $status = $request->transaction_status;
            $fraudStatus = $request->fraud_status ?? null;
            $paymentType = $request->payment_type ?? null;
            
            Log::info("Processing callback - Order: {$orderId}, Status: {$status}, Fraud: {$fraudStatus}");

            // Extract subscription ID from order ID (format: SUB-{id}-{timestamp})
            $parts = explode('-', $orderId);
            $subscriptionId = $parts[1] ?? null;

            if (!$subscriptionId) {
                Log::error("Invalid order ID format: {$orderId}");
                return response()->json(['error' => 'Invalid order ID'], 400);
            }

            $subscription = DB::table('subscriptions')->find($subscriptionId);
            
            if (!$subscription) {
                Log::error("Subscription not found: {$subscriptionId}");
                return response()->json(['error' => 'Subscription not found'], 404);
            }

            Log::info("Found subscription: {$subscriptionId}, current status: {$subscription->status}");

            // Update subscription and invoice based on transaction status
            switch ($status) {
                case 'capture':
                    if ($fraudStatus == 'challenge') {
                        $this->updateSubscriptionStatus($subscriptionId, 'challenge');
                        $this->updateInvoiceStatus($subscriptionId, 'pending');
                        Log::info("Subscription {$subscriptionId} status updated to challenge");
                    } else if ($fraudStatus == 'accept') {
                        $this->activateSubscription($subscriptionId);
                        $this->updateInvoiceStatus($subscriptionId, 'paid', $orderId);
                        Log::info("Subscription {$subscriptionId} activated (capture accept)");
                    }
                    break;
                    
                case 'settlement':
                    $this->activateSubscription($subscriptionId);
                    $this->updateInvoiceStatus($subscriptionId, 'paid', $orderId);
                    Log::info("Subscription {$subscriptionId} activated (settlement)");
                    break;
                    
                case 'pending':
                    $this->updateSubscriptionStatus($subscriptionId, 'pending');
                    $this->updateInvoiceStatus($subscriptionId, 'pending');
                    Log::info("Subscription {$subscriptionId} status updated to pending");
                    break;
                    
                case 'deny':
                    $this->updateSubscriptionStatus($subscriptionId, 'denied');
                    $this->updateInvoiceStatus($subscriptionId, 'failed');
                    Log::info("Subscription {$subscriptionId} status updated to denied");
                    break;
                    
                case 'expire':
                    $this->updateSubscriptionStatus($subscriptionId, 'expired');
                    $this->updateInvoiceStatus($subscriptionId, 'cancelled');
                    Log::info("Subscription {$subscriptionId} status updated to expired");
                    break;
                    
                case 'cancel':
                    $this->updateSubscriptionStatus($subscriptionId, 'cancelled');
                    $this->updateInvoiceStatus($subscriptionId, 'cancelled');
                    Log::info("Subscription {$subscriptionId} status updated to cancelled");
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
                $message = 'Status pembayaran telah diperbarui';
                if ($status == 'settlement' || ($status == 'capture' && $fraudStatus == 'accept')) {
                    $message = 'Pembayaran berhasil! Berlangganan Anda telah aktif.';
                }
                return redirect()->route('manage.subscription.index')->with('success', $message);
            }

        } catch (\Exception $e) {
            Log::error('Callback error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function activateSubscription($subscriptionId)
    {
        $subscription = DB::table('subscriptions')->find($subscriptionId);
        
        if ($subscription) {
            $expiresAt = Carbon::now()->addDays($subscription->duration_days);
            
            DB::table('subscriptions')
                ->where('id', $subscriptionId)
                ->update([
                    'status' => 'active',
                    'activated_at' => now(),
                    'expires_at' => $expiresAt,
                    'updated_at' => now()
                ]);

            // Update user subscription status
            DB::table('users')
                ->where('id', $subscription->user_id)
                ->update([
                    'subscription_status' => 'active',
                    'subscription_expires_at' => $expiresAt,
                    'updated_at' => now()
                ]);

            Log::info("Subscription activated: {$subscriptionId}");
        }
    }

    private function updateSubscriptionStatus($subscriptionId, $status)
    {
        DB::table('subscriptions')
            ->where('id', $subscriptionId)
            ->update([
                'status' => $status,
                'updated_at' => now()
            ]);

        Log::info("Subscription status updated: {$subscriptionId} -> {$status}");
    }

    private function updateInvoiceStatus($subscriptionId, $status, $transactionId = null)
    {
        $updateData = [
            'payment_status' => $status,
            'updated_at' => now()
        ];

        if ($status === 'paid') {
            $updateData['paid_at'] = now();
            if ($transactionId) {
                $updateData['midtrans_transaction_id'] = $transactionId;
            }
        }

        SubscriptionInvoice::where('subscription_id', $subscriptionId)
            ->update($updateData);

        Log::info("Invoice status updated: {$subscriptionId} -> {$status}");
    }

    public function downloadInvoice($invoiceId)
    {
        $invoice = SubscriptionInvoice::with('subscription.user')->findOrFail($invoiceId);
        
        // Check if user owns this invoice
        if ($invoice->subscription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to invoice');
        }

        // Get school profile
        $schoolProfile = DB::table('schools')->first();

        $data = [
            'invoice' => $invoice,
            'user' => $invoice->subscription->user,
            'company' => [
                'name' => 'SPPQU',
                'address' => 'Jl. Bledak Anggur IV, Pedurungan, Semarang',
                'phone' => '+62 82188497818',
                'email' => 'info@sppqu.com'
            ],
            'school' => $schoolProfile ? [
                'name' => $schoolProfile->nama_sekolah ?? 'Nama Sekolah',
                'address' => $schoolProfile->alamat ?? 'Alamat Sekolah',
                'phone' => $schoolProfile->no_telp ?? 'No. Telepon'
            ] : [
                'name' => 'Nama Sekolah',
                'address' => 'Alamat Sekolah',
                'phone' => 'No. Telepon'
            ]
        ];

        $pdf = Pdf::loadView('subscription.invoice-pdf', $data);
        
        // Clean filename - replace invalid characters
        $cleanInvoiceNumber = str_replace(['/', '\\'], '-', $invoice->invoice_number);
        $filename = "Invoice-{$cleanInvoiceNumber}.pdf";
        
        return $pdf->download($filename);
    }

    public function cancelSubscription(Request $request)
    {
        $subscriptionId = $request->subscription_id;
        $subscription = DB::table('subscriptions')
            ->where('id', $subscriptionId)
            ->where('user_id', auth()->id())
            ->first();

        if ($subscription && $subscription->status == 'active') {
            // Update subscription status and set expires_at to now (immediately expire)
            DB::table('subscriptions')
                ->where('id', $subscriptionId)
                ->update([
                    'status' => 'cancelled',
                    'expires_at' => now(), // Set ke sekarang agar langsung tidak aktif
                    'cancelled_at' => now(),
                    'updated_at' => now()
                ]);

            // Update user subscription status
            DB::table('users')
                ->where('id', auth()->id())
                ->update([
                    'subscription_status' => 'cancelled',
                    'updated_at' => now()
                ]);

            // IMPORTANT: Update user_addons yang recurring (subscription addon)
            // Set expires_at ke now agar langsung tidak aktif
            DB::table('user_addons')
                ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
                ->where('user_addons.user_id', auth()->id())
                ->where('addons.type', 'recurring') // Only recurring (subscription)
                ->where('user_addons.status', 'active')
                ->update([
                    'user_addons.expires_at' => now(), // Set ke sekarang
                    'user_addons.status' => 'cancelled',
                    'user_addons.updated_at' => now()
                ]);

            Log::info('Subscription cancelled', [
                'subscription_id' => $subscriptionId,
                'user_id' => auth()->id(),
                'expires_at_set_to' => now()
            ]);

            return back()->with('success', 'Berlangganan berhasil dibatalkan. Akses akan berakhir sekarang.');
        }

        return back()->with('error', 'Berlangganan tidak ditemukan atau tidak aktif');
    }

    public function checkSubscriptionStatus()
    {
        $user = auth()->user();
        $activeSubscription = DB::table('subscriptions')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (!$activeSubscription) {
            // Update user subscription status to expired
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'subscription_status' => 'expired',
                    'updated_at' => now()
                ]);

            return response()->json(['status' => 'expired']);
        }

        // Check if subscription expires soon (within 7 days)
        $daysUntilExpiry = now()->diffInDays($activeSubscription->expires_at, false);
        $roundedDays = round($daysUntilExpiry); // Bulatkan ke angka bulat
        $isExpiringSoon = $roundedDays <= 7 && $roundedDays > 0;

        return response()->json([
            'status' => 'active',
            'expires_at' => $activeSubscription->expires_at,
            'plan_name' => $activeSubscription->plan_name,
            'days_until_expiry' => $roundedDays,
            'is_expiring_soon' => $isExpiringSoon
        ]);
    }

    public function getSubscriptionNotifications()
    {
        $user = auth()->user();
        $notifications = [];

        // Check active subscription
        $activeSubscription = DB::table('subscriptions')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if ($activeSubscription) {
            $daysUntilExpiry = now()->diffInDays($activeSubscription->expires_at, false);
            $roundedDays = round($daysUntilExpiry); // Bulatkan ke angka bulat
            
            if ($roundedDays <= 1) {
                $notifications[] = [
                    'type' => 'critical',
                    'title' => 'Berlangganan Berakhir Besok!',
                    'message' => 'Berlangganan Anda akan berakhir dalam 1 hari. Perpanjang sekarang untuk tetap mengakses semua fitur.',
                    'action' => 'Perpanjang Sekarang',
                    'action_url' => route('manage.subscription.plans')
                ];
            } elseif ($roundedDays <= 3) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'Berlangganan Akan Berakhir',
                    'message' => "Berlangganan Anda akan berakhir dalam {$roundedDays} hari. Perpanjang sekarang untuk menghindari gangguan layanan.",
                    'action' => 'Perpanjang Sekarang',
                    'action_url' => route('manage.subscription.plans')
                ];
            } elseif ($roundedDays <= 7) {
                $notifications[] = [
                    'type' => 'info',
                    'title' => 'Perpanjang Berlangganan',
                    'message' => "Berlangganan Anda akan berakhir dalam {$roundedDays} hari. Pertimbangkan untuk memperpanjang berlangganan.",
                    'action' => 'Lihat Paket',
                    'action_url' => route('manage.subscription.plans')
                ];
            }
        } else {
            // No active subscription
            $notifications[] = [
                'type' => 'error',
                'title' => 'Berlangganan Tidak Aktif',
                'message' => 'Anda tidak memiliki berlangganan aktif. Berlangganan sekarang untuk mengakses semua fitur.',
                'action' => 'Berlangganan Sekarang',
                'action_url' => route('manage.subscription.plans')
            ];
        }

        return response()->json($notifications);
    }

    public function premiumFeatures()
    {
        // Pastikan helper functions tersedia
        // hasActiveSubscription function is now defined in helpers.php

        // canAccessPremium function is now defined in helpers.php

        return view('subscription.premium-features');
    }

    /**
     * Check payment status from iPaymu (check database only, callback will update)
     */
    public function checkPaymentStatus(Request $request)
    {
        try {
            $reference = $request->input('reference');
            
            if (!$reference) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reference ID is required'
                ], 400);
            }

            Log::info('Checking subscription payment status', ['reference' => $reference]);

            // Check subscription status in database (iPaymu callback will update it)
            $subscription = DB::table('subscriptions')
                ->where('transaction_id', $reference)
                ->orWhere('payment_reference', $reference)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }

            Log::info('Subscription status from database', [
                'subscription_id' => $subscription->id,
                'status' => $subscription->status
            ]);

            // Return status based on subscription
            if ($subscription->status === 'active') {
                return response()->json([
                    'success' => true,
                    'status' => 'paid',
                    'message' => 'Pembayaran berhasil! Subscription aktif.',
                    'subscription' => $subscription
                ]);
            } elseif ($subscription->status === 'paid') {
                return response()->json([
                    'success' => true,
                    'status' => 'paid',
                    'message' => 'Pembayaran berhasil! Subscription akan segera diaktifkan.',
                    'subscription' => $subscription
                ]);
            } elseif ($subscription->status === 'expired') {
                return response()->json([
                    'success' => false,
                    'status' => 'expired',
                    'message' => 'Pembayaran kadaluarsa'
                ]);
            } elseif ($subscription->status === 'failed' || $subscription->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'message' => 'Pembayaran gagal'
                ]);
            } else {
                // Status pending - still waiting
                return response()->json([
                    'success' => true,
                    'status' => 'pending',
                    'message' => 'Menunggu pembayaran...'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Check payment status error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error checking payment status: ' . $e->getMessage()
            ], 500);
        }
    }
}
