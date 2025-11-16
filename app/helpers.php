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
     * For students, check based on their school's admin
     */
    function getCheckUserId($userId = null) {
        // Check if context is student (from session)
        if (session('is_student')) {
            $studentId = session('student_id');
            if ($studentId) {
                // Get student's school_id
                $student = \DB::table('students')->where('student_id', $studentId)->first();
                if ($student && $student->school_id) {
                    // Find admin/superadmin associated with this school
                    $adminUser = \DB::table('user_schools')
                        ->join('users', 'user_schools.user_id', '=', 'users.id')
                        ->where('user_schools.school_id', $student->school_id)
                        ->whereIn('users.role', ['admin', 'superadmin'])
                        ->select('users.id', 'users.role')
                        ->first();
                    
                    if ($adminUser) {
                        // If admin is superadmin, return their ID
                        // Otherwise, return superadmin ID for inheritance
                        return ($adminUser->role === 'superadmin') ? $adminUser->id : getSuperadminId();
                    }
                }
            }
            // Fallback to superadmin if no admin found
            return getSuperadminId();
        }
        
        // For regular users (non-student)
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
     * Check if user can access menu based on role permissions from database
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
        
        // Check menu_permissions table first for all roles
        $permission = \DB::table('menu_permissions')
            ->where('role', $user->role)
            ->where('menu_key', $menuKey)
            ->first();
        
        // If permission found in database, use it
        if ($permission !== null) {
            return (bool) $permission->allowed;
        }
        
        // Fallback: For admin users without specific permission in DB, check subscription
        if (in_array($user->role, ['admin'])) {
            return hasActiveSubscription($user->id);
        }
        
        // Fallback: Check hardcoded permissions for backward compatibility
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
     * Returns true if addon is purchased (not only active)
     */
    function hasSPMBAddon($userId = null) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return false;
        }
        
        // Check if user has SPMB addon (purchased, not only active)
        // Menu should appear if addon is purchased, even if not yet active
        return \DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.user_id', $checkUserId)
            ->where('addons.slug', 'spmb')
            ->whereIn('user_addons.status', ['active', 'pending', 'inactive'])
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
     * Returns true if addon is purchased (not only active)
     */
    function hasLibraryAddon($userId = null) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return false;
        }
        
        // Check if user has Library addon (purchased, not only active)
        // Menu should appear if addon is purchased, even if not yet active
        // Support both 'library' and 'e-perpustakaan' slugs
        return \DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.user_id', $checkUserId)
            ->whereIn('addons.slug', ['library', 'e-perpustakaan'])
            ->whereIn('user_addons.status', ['active', 'pending', 'inactive'])
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
     * Returns true if addon is purchased (not only active)
     */
    function hasBKAddon($userId = null) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return false;
        }
        
        // Check if user has BK addon (must be active for menu to appear)
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
     * Returns true if addon is purchased (not only active)
     */
    function hasEJurnalAddon($userId = null) {
        // Get the correct user ID to check (superadmin for inheritance)
        $checkUserId = getCheckUserId($userId);
        
        if (!$checkUserId) {
            return false;
        }
        
        // Check if user has E-Jurnal addon (must be active for menu to appear)
        // Support both 'e-jurnal' and 'ejurnal-7kaih' slugs for backward compatibility
        return \DB::table('user_addons')
            ->join('addons', 'user_addons.addon_id', '=', 'addons.id')
            ->where('user_addons.user_id', $checkUserId)
            ->where('user_addons.status', 'active')
            ->whereIn('addons.slug', ['e-jurnal', 'ejurnal-7kaih'])
            ->where(function($query) {
                $query->whereNull('user_addons.expires_at')
                      ->orWhere('user_addons.expires_at', '>', now());
            })
            ->exists();
    }
}

if (!function_exists('currentSchool')) {
    /**
     * Get current school from session
     */
    function currentSchool() {
        static $school = null;
        
        if ($school === null && session('current_school_id')) {
            $school = \App\Models\School::find(session('current_school_id'));
        }
        
        return $school;
    }
}

if (!function_exists('currentSchoolId')) {
    /**
     * Get current school ID from session
     */
    function currentSchoolId() {
        return session('current_school_id');
    }
}

if (!function_exists('currentFoundation')) {
    /**
     * Get current foundation from session
     */
    function currentFoundation() {
        static $foundation = null;
        
        if ($foundation === null && session('foundation_id')) {
            $foundation = \App\Models\Foundation::find(session('foundation_id'));
        }
        
        return $foundation;
    }
}

if (!function_exists('currentFoundationId')) {
    /**
     * Get current foundation ID from session
     */
    function currentFoundationId() {
        return session('foundation_id');
    }
}

if (!function_exists('canManageAddons')) {
    /**
     * Check if user can manage addons (superadmin only)
     */
    function canManageAddons() {
        $user = auth()->user();
        return $user && $user->role === 'superadmin';
    }
}

if (!function_exists('grantAddonToUser')) {
    /**
     * Grant addon to user
     * For non-superadmin users, grant to superadmin instead (inheritance)
     */
    function grantAddonToUser($userId, $addonSlug) {
        try {
            $addon = \App\Models\Addon::where('slug', $addonSlug)->first();
            if (!$addon) {
                return false;
            }

            $user = \App\Models\User::find($userId);
            if (!$user) {
                return false;
            }

            // For inheritance: if user is not superadmin, grant to superadmin instead
            $targetUserId = ($user->role === 'superadmin') ? $userId : getSuperadminId();
            
            if (!$targetUserId) {
                return false;
            }

            $userAddon = \App\Models\UserAddon::where('user_id', $targetUserId)
                ->where('addon_id', $addon->id)
                ->first();

            if ($userAddon) {
                // Update existing
                $userAddon->update([
                    'status' => 'active',
                    'purchased_at' => now(),
                    'amount_paid' => $addon->price,
                    'payment_method' => 'admin_grant',
                    'expires_at' => $addon->type === 'one_time' ? null : now()->addYear(),
                    'updated_at' => now()
                ]);
            } else {
                // Create new
                \App\Models\UserAddon::create([
                    'user_id' => $targetUserId,
                    'addon_id' => $addon->id,
                    'status' => 'active',
                    'purchased_at' => now(),
                    'amount_paid' => $addon->price,
                    'payment_method' => 'admin_grant',
                    'expires_at' => $addon->type === 'one_time' ? null : now()->addYear(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Error granting addon: ' . $e->getMessage());
            return false;
        }
    }
}