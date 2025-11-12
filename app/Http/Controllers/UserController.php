<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function roleMenu()
    {
        // Hanya tampilkan role yang ada di tabel users (bukan admin/superadmin)
        $roles = ['kasir','bendahara','spmb_admin','admin_perpustakaan'];
        
        // Filter hanya menu yang valid (exclude array items)
        $allMenus = config('menus');
        $menuKeys = array_filter(array_keys($allMenus), function($key) use ($allMenus) {
            return is_string($allMenus[$key]); // Hanya ambil yang string, bukan array
        });
        
        $permissions = \App\Models\MenuPermission::whereIn('role', $roles)->get()->groupBy('role');
        return view('users.role-menu', compact('roles','menuKeys','permissions'));
    }

    public function saveRoleMenu(\Illuminate\Http\Request $request)
    {
        $data = $request->get('perm', []);
        
        // Filter hanya menu yang valid (exclude array items)
        $allMenus = config('menus');
        $validMenuKeys = array_filter(array_keys($allMenus), function($key) use ($allMenus) {
            return is_string($allMenus[$key]); // Hanya ambil yang string, bukan array
        });
        
        foreach ($data as $role => $menus) {
            foreach ($validMenuKeys as $key) {
                $allowed = isset($menus[$key]) ? 1 : 0;
                
                \App\Models\MenuPermission::updateOrCreate(
                    ['role' => $role, 'menu_key' => $key],
                    ['allowed' => $allowed]
                );
            }
        }
        
        return redirect()->back()->with('success', 'Hak akses menu berhasil disimpan');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $foundationId = session('foundation_id');
        $foundation = $foundationId ? \App\Models\Foundation::find($foundationId) : null;
        
        // Filter users berdasarkan role pengguna yang sedang login
        $query = User::query();
        
        // Jika user adalah admin_yayasan atau superadmin
        if (in_array($currentUser->role, ['superadmin', 'admin_yayasan'])) {
            // Tampilkan dropdown sekolah untuk filter
            $schools = $foundation ? $foundation->schools()->orderBy('nama_sekolah')->get() : collect();
            $selectedSchoolId = $request->get('school_id') ?: null;
            
            if ($selectedSchoolId) {
                // Jika sekolah dipilih, ambil user yang ter-assign ke sekolah tersebut
                $query->whereHas('schools', function($q) use ($selectedSchoolId) {
                    $q->where('schools.id', $selectedSchoolId);
                });
            } else {
                // Jika tidak ada filter sekolah, untuk admin_yayasan hanya tampilkan user dari foundation mereka
                if ($currentUser->role === 'admin_yayasan' && $foundationId) {
                    // Admin yayasan hanya lihat user yang ter-assign ke sekolah di foundation mereka
                    // (tidak termasuk superadmin dan admin_yayasan lain)
                    $query->whereHas('schools', function($sq) use ($foundationId) {
                        $sq->where('foundation_id', $foundationId);
                    });
                }
                // Superadmin bisa lihat semua user (termasuk superadmin dan admin_yayasan lain)
            }
        } else {
            // Jika user adalah admin sekolah atau role lain (kasir, bendahara, dll)
            // Hanya tampilkan user yang ter-assign ke sekolah yang sama dengan user yang sedang login
            $currentSchoolId = currentSchoolId();
            
            if (!$currentSchoolId) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            
            // Ambil semua sekolah yang di-assign ke user yang sedang login
            $userSchoolIds = $currentUser->schools()->pluck('schools.id')->toArray();
            
            // Hanya tampilkan user yang ter-assign ke sekolah yang sama
            $query->whereHas('schools', function($q) use ($userSchoolIds) {
                $q->whereIn('schools.id', $userSchoolIds);
            });
            
            // Jangan tampilkan dropdown sekolah untuk user sekolah
            $schools = collect();
            $selectedSchoolId = null;
        }
        
        $users = $query->orderBy('name')->get();
        
        return view('users.index', compact('users', 'schools', 'selectedSchoolId', 'foundation'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $foundationId = session('foundation_id');
        $foundation = $foundationId ? \App\Models\Foundation::find($foundationId) : null;
        $schools = $foundation ? $foundation->schools()->orderBy('nama_sekolah')->get() : collect();
        
        return view('users.create', compact('schools', 'foundation'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Jika user adalah superadmin atau admin_yayasan, default role jadi 'admin' (Admin Sekolah)
        $isFoundationLevel = auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan';
        
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'nomor_wa' => 'nullable|string|max:20',
            'school_ids' => $isFoundationLevel ? 'required|array|min:1' : 'nullable|array',
            'school_ids.*' => 'exists:schools,id',
            'is_bk' => 'nullable|boolean',
            'spmb_admin_access' => 'nullable|boolean',
        ];
        
        // Jika bukan foundation level, otomatis set school_id dari admin yang membuat
        if (!$isFoundationLevel) {
            $currentSchoolId = currentSchoolId();
            if ($currentSchoolId) {
                $request->merge(['school_ids' => [$currentSchoolId]]);
            }
        }
        
        // Hanya validasi role jika bukan foundation level
        if (!$isFoundationLevel) {
            $validationRules['role'] = 'required|string|in:admin,admin_bk,admin_jurnal,admin_perpustakaan,spmb_admin,kasir,bendahara';
        }
        
        $request->validate($validationRules);
        
        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        
        // Set default role untuk foundation level
        if ($isFoundationLevel) {
            $data['role'] = 'admin'; // Default jadi Admin Sekolah
        }
        
        // Set akses BK: otomatis true jika role adalah admin_bk, otherwise false
        // Admin dengan addon BK aktif sudah bisa akses melalui logika addon, tidak perlu flag is_bk
        if ($data['role'] === 'admin_bk') {
            $data['is_bk'] = true; // Role admin_bk otomatis punya akses BK
        } else {
            $data['is_bk'] = false; // Admin dengan addon aktif bisa akses melalui logika addon
        }
        
        // Set akses SPMB: otomatis true jika role adalah spmb_admin, otherwise false
        // Admin dengan addon SPMB aktif sudah bisa akses melalui logika addon, tidak perlu flag spmb_admin_access
        if ($data['role'] === 'spmb_admin') {
            $data['spmb_admin_access'] = true; // Role spmb_admin otomatis punya akses SPMB
        } else {
            $data['spmb_admin_access'] = false; // Admin dengan addon aktif bisa akses melalui logika addon
        }
        
        $user = User::create($data);
        
        // Assign sekolah jika role bukan superadmin atau admin_yayasan
        if (!in_array($user->role, ['superadmin', 'admin_yayasan'])) {
            $schoolIds = $request->has('school_ids') ? $request->school_ids : [];
            
            // Jika tidak ada school_ids dari request, gunakan school_id dari admin yang membuat
            if (empty($schoolIds)) {
                $currentSchoolId = currentSchoolId();
                if ($currentSchoolId) {
                    $schoolIds = [$currentSchoolId];
                }
            }
            
            if (!empty($schoolIds)) {
                foreach ($schoolIds as $schoolId) {
                    // Cek apakah sudah ada relasi, jika belum baru attach
                    if (!$user->schools()->where('schools.id', $schoolId)->exists()) {
                        $user->schools()->attach($schoolId, [
                            'role' => $user->role,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } elseif ($request->has('school_ids') && !empty($request->school_ids)) {
            // Untuk superadmin/admin_yayasan, tetap bisa assign sekolah manual
            $schoolIds = $request->school_ids;
            foreach ($schoolIds as $schoolId) {
                if (!$user->schools()->where('schools.id', $schoolId)->exists()) {
                    $user->schools()->attach($schoolId, [
                        'role' => $user->role,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        return redirect()->route('manage.users.index')->with('success', 'Admin Sekolah berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $currentUser = auth()->user();
        $foundationId = session('foundation_id');
        $foundation = $foundationId ? \App\Models\Foundation::find($foundationId) : null;
        
        // Validasi akses berdasarkan role
        if (in_array($currentUser->role, ['superadmin', 'admin_yayasan'])) {
            // Admin yayasan hanya bisa edit user yang ter-assign ke sekolah di foundation mereka
            if ($currentUser->role === 'admin_yayasan' && $foundationId) {
                $hasAccess = $user->schools()->whereHas('foundation', function($q) use ($foundationId) {
                    $q->where('id', $foundationId);
                })->exists();
                
                if (!$hasAccess && !in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                    return redirect()->route('manage.users.index')
                        ->with('error', 'Anda tidak memiliki akses untuk mengedit pengguna ini.');
                }
            }
            // Superadmin bisa edit semua user
            $schools = $foundation ? $foundation->schools()->orderBy('nama_sekolah')->get() : collect();
        } else {
            // User sekolah hanya bisa edit user yang ter-assign ke sekolah yang sama
            $currentSchoolId = currentSchoolId();
            
            if (!$currentSchoolId) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            
            $userSchoolIds = $currentUser->schools()->pluck('schools.id')->toArray();
            $targetUserSchoolIds = $user->schools()->pluck('schools.id')->toArray();
            
            // Cek apakah ada sekolah yang sama
            $hasCommonSchool = !empty(array_intersect($userSchoolIds, $targetUserSchoolIds));
            
            if (!$hasCommonSchool) {
                return redirect()->route('manage.users.index')
                    ->with('error', 'Anda tidak memiliki akses untuk mengedit pengguna ini.');
            }
            
            // User sekolah tidak bisa mengubah role atau assign sekolah lain
            $schools = collect();
        }
        
        $userSchoolIds = $user->schools()->pluck('schools.id')->toArray();
        
        return view('users.edit', compact('user', 'schools', 'foundation', 'userSchoolIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6',
            'nomor_wa' => 'nullable|string|max:20',
            'role' => 'required|string|in:superadmin,admin,admin_bk,admin_jurnal,admin_perpustakaan,spmb_admin,kasir,bendahara',
            'is_bk' => 'nullable|boolean',
            'spmb_admin_access' => 'nullable|boolean',
        ]);
        $data = $request->all();
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        
        // Set akses BK: otomatis true jika role adalah admin_bk, otherwise false
        // Admin dengan addon BK aktif sudah bisa akses melalui logika addon, tidak perlu flag is_bk
        if ($data['role'] === 'admin_bk') {
            $data['is_bk'] = true; // Role admin_bk otomatis punya akses BK
        } else {
            $data['is_bk'] = false; // Admin dengan addon aktif bisa akses melalui logika addon
        }
        
        // Set akses SPMB: otomatis true jika role adalah spmb_admin, otherwise false
        // Admin dengan addon SPMB aktif sudah bisa akses melalui logika addon, tidak perlu flag spmb_admin_access
        if ($data['role'] === 'spmb_admin') {
            $data['spmb_admin_access'] = true; // Role spmb_admin otomatis punya akses SPMB
        } else {
            $data['spmb_admin_access'] = false; // Admin dengan addon aktif bisa akses melalui logika addon
        }
        $user->update($data);
        
        // Update assignment sekolah
        $currentUser = auth()->user();
        $isFoundationLevel = in_array($currentUser->role, ['superadmin', 'admin_yayasan']);
        
        if ($isFoundationLevel && $request->has('school_ids') && !empty($request->school_ids)) {
            // Untuk superadmin/admin_yayasan, bisa update assignment sekolah
            $schoolIds = $request->school_ids;
            // Sync sekolah (hapus yang tidak ada, tambah yang baru)
            $user->schools()->sync($schoolIds);
        } elseif (!$isFoundationLevel) {
            // Untuk admin sekolah, pastikan user tetap ter-assign ke sekolah yang sama dengan admin
            $currentSchoolId = currentSchoolId();
            if ($currentSchoolId) {
                // Pastikan user ter-assign ke sekolah admin yang sedang login
                if (!$user->schools()->where('schools.id', $currentSchoolId)->exists()) {
                    $user->schools()->attach($currentSchoolId, [
                        'role' => $user->role,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        return redirect()->route('manage.users.index')->with('success', 'Pengguna berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        $foundationId = session('foundation_id');
        
        // Validasi akses berdasarkan role
        if (in_array($currentUser->role, ['superadmin', 'admin_yayasan'])) {
            // Admin yayasan hanya bisa delete user yang ter-assign ke sekolah di foundation mereka
            if ($currentUser->role === 'admin_yayasan' && $foundationId) {
                $hasAccess = $user->schools()->whereHas('foundation', function($q) use ($foundationId) {
                    $q->where('id', $foundationId);
                })->exists();
                
                if (!$hasAccess && !in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                    return redirect()->route('manage.users.index')
                        ->with('error', 'Anda tidak memiliki akses untuk menghapus pengguna ini.');
                }
            }
            // Superadmin bisa delete semua user
        } else {
            // User sekolah hanya bisa delete user yang ter-assign ke sekolah yang sama
            $currentSchoolId = currentSchoolId();
            
            if (!$currentSchoolId) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            
            $userSchoolIds = $currentUser->schools()->pluck('schools.id')->toArray();
            $targetUserSchoolIds = $user->schools()->pluck('schools.id')->toArray();
            
            // Cek apakah ada sekolah yang sama
            $hasCommonSchool = !empty(array_intersect($userSchoolIds, $targetUserSchoolIds));
            
            if (!$hasCommonSchool) {
                return redirect()->route('manage.users.index')
                    ->with('error', 'Anda tidak memiliki akses untuk menghapus pengguna ini.');
            }
        }
        
        // Jangan izinkan menghapus diri sendiri
        if ($user->id === $currentUser->id) {
            return redirect()->route('manage.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        
        // Detach semua relasi sekolah
        $user->schools()->detach();
        
        // Hapus user
        $user->delete();
        
        return redirect()->route('manage.users.index')
            ->with('success', 'Pengguna berhasil dihapus!');
    }
}
