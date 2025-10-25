<?php

if (!function_exists('getSuperadminId')) {
    /**
     * Get the ID of the superadmin user
     * This is used for inheritance - all users inherit subscription/addons from superadmin
     */
    function getSuperadminId() {
        static $superadminId = null;
        
        if ($superadminId === null) {
            $superadmin = \DB::table('users')
                ->where('role', 'superadmin')
                ->first();
            
            $superadminId = $superadmin ? $superadmin->id : null;
        }
        
        return $superadminId;
    }
}

if (!function_exists('getCheckUserId')) {
    /**
     * Get the user ID to check for subscription/addons
     * If user is superadmin, return their ID
     * Otherwise, return superadmin ID (inheritance)
     */
    function getCheckUserId($userId = null) {
        if (!$userId) {
            $userId = auth()->id();
        }
        
        if (!$userId) {
            return null;
        }
        
        $user = \DB::table('users')->find($userId);
        
        if (!$user) {
            return null;
        }
        
        // If user is superadmin, check their own subscriptions/addons
        // Otherwise, inherit from superadmin
        return ($user->role === 'superadmin') ? $userId : getSuperadminId();
    }
}

if (!function_exists('hasActiveSubscription')) {
    /**
     * Check if user has active RECURRING subscription
     * Non-superadmin users inherit subscription from superadmin
     * 
     * NOTE: One-time addons are NOT subscriptions!
     * Subscription recurring is REQUIRED for app access.
     * One-time addons are additional features that require active subscription.
     */
    function hasActiveSubscription($userId = null) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return false;
        }
        
        // ONLY check for recurring subscriptions
        // One-time addons do NOT count as subscriptions
        return \DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.user_id', $checkUserId)
            ->where('user_addons.status', 'active')
            ->where('addons.type', 'recurring')
            ->where('user_addons.expires_at', '>', now())
            ->exists();
    }
}

if (!function_exists('isSubscriptionExpiring')) {
    /**
     * Check if subscription is expiring soon
     * Non-superadmin users inherit subscription from superadmin
     */
    function isSubscriptionExpiring($userId = null, $days = 7) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return false;
        }
        
        return \DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.user_id', $checkUserId)
            ->where('user_addons.status', 'active')
            ->where('addons.type', 'recurring')
            ->where('user_addons.expires_at', '<=', now()->addDays($days))
            ->where('user_addons.expires_at', '>', now())
            ->exists();
    }
}

if (!function_exists('getSubscriptionDaysLeft')) {
    /**
     * Get days left until subscription expires
     * Non-superadmin users inherit subscription from superadmin
     */
    function getSubscriptionDaysLeft($userId = null) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return 0;
        }
        
        $subscription = \DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.user_id', $checkUserId)
            ->where('user_addons.status', 'active')
            ->where('addons.type', 'recurring')
            ->where('user_addons.expires_at', '>', now())
            ->select('user_addons.expires_at')
                ->first();
            
        if (!$subscription) {
            return 0;
        }
        
        return now()->diffInDays($subscription->expires_at, false);
    }
}

if (!function_exists('menuCan')) {
    /**
     * Check if user can access menu based on subscription
     */
    function menuCan($menuKey) {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Superadmin always has access
        if ($user->role === 'superadmin') {
            return true;
        }
        
        // For admin users, check subscription
        if (in_array($user->role, ['admin', 'superadmin'])) {
            return hasActiveSubscription($user->id);
        }
        
        // For other users, check based on role permissions
        $menuPermissions = [
            'menu.data_master' => ['admin', 'superadmin'],
            'menu.setting_tarif' => ['admin', 'superadmin'],
            'menu.pembayaran' => ['admin', 'superadmin'],
            'menu.tabungan' => ['admin', 'superadmin'],
            'menu.akuntansi' => ['admin', 'superadmin'],
            'menu.laporan' => ['admin', 'superadmin'],
            'menu.billing' => ['admin', 'superadmin'],
            'menu.kirim_tagihan' => ['admin', 'superadmin'],
            'menu.users' => ['admin', 'superadmin'],
            'menu.general_setting' => ['admin', 'superadmin'],
            'menu.spmb.waves' => ['admin', 'superadmin'],
        ];
        
        if (isset($menuPermissions[$menuKey])) {
            return in_array($user->role, $menuPermissions[$menuKey]);
        }
        
        return false;
    }
}

if (!function_exists('canAccessPremium')) {
    /**
     * Check if user can access premium features
     */
    function canAccessPremium($feature) {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Superadmin always has access
        if ($user->role === 'superadmin') {
            return true;
        }
        
        // For admin users, check subscription
        if (in_array($user->role, ['admin', 'superadmin'])) {
            return hasActiveSubscription($user->id);
        }
        
        return false;
    }
}

if (!function_exists('hasSPMBAddon')) {
    /**
     * Check if user has SPMB addon
     * Non-superadmin users inherit addon from superadmin
     */
    function hasSPMBAddon($userId = null) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return false;
        }
        
        // Check if user has SPMB addon
        return \DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.user_id', $checkUserId)
            ->where('user_addons.status', 'active')
            ->where('addons.slug', 'spmb')
            ->where(function($query) {
                $query->whereNull('user_addons.expires_at')
                      ->orWhere('user_addons.expires_at', '>', now());
            })
            ->exists();
    }
}

if (!function_exists('hasLibraryAddon')) {
    /**
     * Check if user has Library addon
     * Non-superadmin users inherit addon from superadmin
     */
    function hasLibraryAddon($userId = null) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return false;
        }
        
        // Check if user has Library addon
        return \DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.user_id', $checkUserId)
            ->where('user_addons.status', 'active')
            ->where('addons.slug', 'library')
            ->where(function($query) {
                $query->whereNull('user_addons.expires_at')
                      ->orWhere('user_addons.expires_at', '>', now());
            })
            ->exists();
    }
}

if (!function_exists('hasBKAddon')) {
    /**
     * Check if user has BK (Bimbingan Konseling) addon
     * Non-superadmin users inherit addon from superadmin
     */
    function hasBKAddon($userId = null) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return false;
        }
        
        // Check if user has BK addon
        return \DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.user_id', $checkUserId)
            ->where('user_addons.status', 'active')
            ->where('addons.slug', 'bk')
            ->where(function($query) {
                $query->whereNull('user_addons.expires_at')
                      ->orWhere('user_addons.expires_at', '>', now());
            })
            ->exists();
    }
}

if (!function_exists('hasEJurnalAddon')) {
    /**
     * Check if user has E-Jurnal addon
     * Non-superadmin users inherit addon from superadmin
     */
    function hasEJurnalAddon($userId = null) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return false;
        }
        
        // Check if user has E-Jurnal addon
        return \DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.user_id', $checkUserId)
            ->where('user_addons.status', 'active')
            ->where('addons.slug', 'e-jurnal')
            ->where(function($query) {
                $query->whereNull('user_addons.expires_at')
                      ->orWhere('user_addons.expires_at', '>', now());
            })
            ->exists();
    }
}