<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        // DISABLED: Allow all access without subscription check
        return $next($request);
        
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Check if user has active subscription (for admin/superadmin only)
        if (in_array($user->role, ['admin', 'superadmin', 'operator', 'admin_jurnal'])) {
            // Use inheritance - check superadmin's subscription for non-superadmin users
            $checkUserId = getCheckUserId($user->id);
            
            if (!$checkUserId) {
                return redirect()->route('manage.subscription.index')
                    ->with('error', 'Tidak dapat menemukan superadmin untuk memeriksa berlangganan.');
            }
            
            // Check for active subscription in new subscriptions table (Tripay)
            $activeSubscription = DB::table('subscriptions')
                ->where('user_id', $checkUserId)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->first();
            
            // If no active subscription in subscriptions table, check user_addons (legacy)
            if (!$activeSubscription) {
                // Check for recurring subscriptions
                $activeSubscription = DB::table('user_addons')
                    ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
                    ->where('user_addons.user_id', $checkUserId)
                    ->where('user_addons.status', 'active')
                    ->where('addons.type', 'recurring')
                    ->where('user_addons.expires_at', '>', now())
                    ->first();
                
                // If no recurring subscription, check for one_time addons (permanent)
                if (!$activeSubscription) {
                    $activeSubscription = DB::table('user_addons')
                        ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
                        ->where('user_addons.user_id', $checkUserId)
                        ->where('user_addons.status', 'active')
                        ->where('addons.type', 'one_time')
                        ->first();
                }
            }

            if (!$activeSubscription) {
                // No active subscription - BLOCK ACCESS except subscription routes
                // Allow access to subscription and addons pages only
                $allowedRoutes = [
                    'manage/subscription*',
                    'manage/addons*',
                    'subscription/*',
                    'addons/*',
                    'logout'
                ];
                
                $isAllowed = false;
                foreach ($allowedRoutes as $pattern) {
                    if ($request->is($pattern)) {
                        $isAllowed = true;
                        break;
                    }
                }
                
                if (!$isAllowed) {
                    // Check if user has never subscribed
                    $hasSubscriptionHistory = DB::table('subscriptions')
                        ->where('user_id', $checkUserId)
                        ->exists();
                    
                    $message = $hasSubscriptionHistory 
                        ? 'Berlangganan Anda telah berakhir. Silakan perpanjang berlangganan untuk mengakses fitur aplikasi.'
                        : 'Anda belum memiliki berlangganan aktif. Silakan berlangganan terlebih dahulu untuk mengakses fitur aplikasi.';
                    
                    return redirect()->route('manage.subscription.index')
                        ->with('error', $message);
                }
                
                // Set session for UI (disable menus)
                session(['subscription_expired' => true]);
            } else {
                // Clear subscription expired flag
                session()->forget('subscription_expired');
                
                // Check if subscription is expiring soon (within 30 days)
                // First check subscriptions table
                $expiringSubscription = DB::table('subscriptions')
                    ->where('user_id', $checkUserId)
                    ->where('status', 'active')
                    ->where('expires_at', '<=', now()->addDays(30))
                    ->where('expires_at', '>', now())
                    ->first();
                
                // If not found, check user_addons (legacy)
                if (!$expiringSubscription) {
                    $expiringSubscription = DB::table('user_addons')
                        ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
                        ->where('user_addons.user_id', $checkUserId)
                        ->where('user_addons.status', 'active')
                        ->where('addons.type', 'recurring')
                        ->where('user_addons.expires_at', '<=', now()->addDays(30))
                        ->where('user_addons.expires_at', '>', now())
                        ->first();
                }

                if ($expiringSubscription) {
                    $daysUntilExpiry = now()->diffInDays($expiringSubscription->expires_at, false);
                    $roundedDays = round($daysUntilExpiry);
                    
                    if ($roundedDays <= 1) {
                        session()->flash('subscription_warning', [
                            'type' => 'critical',
                            'message' => 'Berlangganan Anda akan berakhir dalam 1 hari! Perpanjang sekarang untuk menghindari gangguan layanan.',
                            'show_popup' => true
                        ]);
                    } elseif ($roundedDays <= 3) {
                        session()->flash('subscription_warning', [
                            'type' => 'warning',
                            'message' => "Berlangganan Anda akan berakhir dalam {$roundedDays} hari. Perpanjang sekarang untuk menghindari gangguan layanan.",
                            'show_popup' => true
                        ]);
                    } elseif ($roundedDays <= 7) {
                        session()->flash('subscription_warning', [
                            'type' => 'warning',
                            'message' => "Berlangganan Anda akan berakhir dalam {$roundedDays} hari. Segera perpanjang berlangganan untuk menghindari gangguan layanan.",
                            'show_popup' => true
                        ]);
                    } elseif ($roundedDays <= 14) {
                        session()->flash('subscription_warning', [
                            'type' => 'info',
                            'message' => "Berlangganan Anda akan berakhir dalam {$roundedDays} hari. Pertimbangkan untuk memperpanjang berlangganan.",
                            'show_popup' => true
                        ]);
                    } else {
                        // H-30 reminder
                        session()->flash('subscription_warning', [
                            'type' => 'info',
                            'message' => "Berlangganan Anda akan berakhir dalam {$roundedDays} hari. Ini adalah pengingat awal untuk memperpanjang berlangganan.",
                            'show_popup' => true
                        ]);
                    }
                }
            }
        }

        return $next($request);
    }
}
