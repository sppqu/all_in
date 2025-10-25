<?php

use Illuminate\Support\Facades\Route;

// Route publik untuk aktivasi cepat (tanpa login)
Route::get('/activate/{userId}/{addonSlug}', function($userId, $addonSlug) {
    try {
        $addon = \App\Models\Addon::where('slug', $addonSlug)->first();
        if (!$addon) {
            return response()->json(['error' => "Addon '$addonSlug' tidak ditemukan"], 404);
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['error' => "User ID $userId tidak ditemukan"], 404);
        }

        $userAddon = \App\Models\UserAddon::where('user_id', $userId)
            ->where('addon_id', $addon->id)
            ->first();

        if (!$userAddon) {
            // Create new user addon
            \App\Models\UserAddon::create([
                'user_id' => $userId,
                'addon_id' => $addon->id,
                'status' => 'active',
                'purchased_at' => now(),
                'amount_paid' => $addon->price,
                'payment_method' => 'web_activation',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $message = "Addon '{$addon->name}' berhasil dibuat dan diaktifkan untuk user '{$user->name}'";
        } else {
            // Update existing user addon
            $userAddon->update([
                'status' => 'active',
                'updated_at' => now()
            ]);
            
            $message = "Addon '{$addon->name}' berhasil diaktifkan untuk user '{$user->name}'";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'user' => $user->name,
            'addon' => $addon->name,
            'activation_date' => now()->format('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->name('activate.addon.public');

// Route publik untuk deaktivasi (tanpa login)
Route::get('/deactivate/{userId}/{addonSlug}', function($userId, $addonSlug) {
    try {
        $addon = \App\Models\Addon::where('slug', $addonSlug)->first();
        if (!$addon) {
            return response()->json(['error' => "Addon '$addonSlug' tidak ditemukan"], 404);
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['error' => "User ID $userId tidak ditemukan"], 404);
        }

        $userAddon = \App\Models\UserAddon::where('user_id', $userId)
            ->where('addon_id', $addon->id)
            ->first();

        if (!$userAddon) {
            return response()->json(['error' => "User '{$user->name}' tidak memiliki addon '{$addon->name}'"], 404);
        }

        $userAddon->update([
            'status' => 'inactive',
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Addon '{$addon->name}' berhasil dinonaktifkan untuk user '{$user->name}'",
            'user' => $user->name,
            'addon' => $addon->name,
            'deactivation_date' => now()->format('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->name('deactivate.addon.public');

// Halaman web untuk aktivasi addon (tanpa login)
Route::get('/activate-addon-page', function() {
    return view('activate-addon');
})->name('activate.addon.page');

// Temporary route untuk aktivasi SPMB addon (backward compatibility)
Route::get('/activate-spmb/{userId}', function($userId) {
    return redirect()->route('activate.addon.public', ['userId' => $userId, 'addonSlug' => 'spmb']);
})->name('activate.spmb');

// Route untuk superadmin mengelola addon user
Route::middleware(['auth'])->group(function () {
    // Grant addon to user (superadmin only)
    Route::post('/grant-addon/{userId}/{addonSlug}', function($userId, $addonSlug) {
        if (!canManageAddons()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $result = grantAddonToUser($userId, $addonSlug);
        
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => "Addon $addonSlug berhasil diberikan ke user ID: $userId"
            ]);
        } else {
            return response()->json(['error' => 'Gagal memberikan addon'], 500);
        }
    })->name('grant.addon');
    
    // Revoke addon from user (superadmin only)
    Route::post('/revoke-addon/{userId}/{addonSlug}', function($userId, $addonSlug) {
        if (!canManageAddons()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        try {
            $addon = \App\Models\Addon::where('slug', $addonSlug)->first();
            if (!$addon) {
                return response()->json(['error' => 'Addon tidak ditemukan'], 404);
            }

            $userAddon = \App\Models\UserAddon::where('user_id', $userId)
                ->where('addon_id', $addon->id)
                ->first();

            if ($userAddon) {
                $userAddon->update(['status' => 'inactive']);
                return response()->json([
                    'success' => true,
                    'message' => "Addon $addonSlug berhasil dicabut dari user ID: $userId"
                ]);
            } else {
                return response()->json(['error' => 'User addon tidak ditemukan'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('revoke.addon');
    
    // Web interface untuk aktivasi addon (superadmin only)
    Route::get('/activate-addon', function() {
        if (!canManageAddons()) {
            abort(403, 'Unauthorized');
        }
        
        $users = \App\Models\User::orderBy('name')->get();
        $addons = \App\Models\Addon::orderBy('name')->get();
        
        return view('admin.activate-addon', compact('users', 'addons'));
    })->name('activate.addon');
    
    // Process activation via web
    Route::post('/activate-addon', function(\Illuminate\Http\Request $request) {
        if (!canManageAddons()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'addon_slug' => 'required|string|exists:addons,slug'
        ]);
        
        $result = grantAddonToUser($request->user_id, $request->addon_slug);
        
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => "Addon berhasil diaktifkan untuk user!"
            ]);
        } else {
            return response()->json(['error' => 'Gagal mengaktifkan addon'], 500);
        }
    })->name('activate.addon.process');
    
    // Deactivate addon via web
    Route::post('/deactivate-addon', function(\Illuminate\Http\Request $request) {
        if (!canManageAddons()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'addon_slug' => 'required|string|exists:addons,slug'
        ]);
        
        try {
            $addon = \App\Models\Addon::where('slug', $request->addon_slug)->first();
            $userAddon = \App\Models\UserAddon::where('user_id', $request->user_id)
                ->where('addon_id', $addon->id)
                ->first();

            if ($userAddon) {
                $userAddon->update(['status' => 'inactive']);
                return response()->json([
                    'success' => true,
                    'message' => "Addon berhasil dinonaktifkan untuk user!"
                ]);
            } else {
                return response()->json(['error' => 'User addon tidak ditemukan'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('deactivate.addon.process');
});
