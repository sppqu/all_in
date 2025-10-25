<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function roleMenu()
    {
        // Hanya tampilkan role yang ada di tabel users (bukan admin/superadmin)
        $roles = ['kasir','bendahara','spmb_admin'];
        
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
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'nomor_wa' => 'nullable|string|max:20',
            'role' => 'required|string|in:superadmin,admin,admin_bk,admin_jurnal,spmb_admin,kasir,bendahara',
            'is_bk' => 'nullable|boolean',
            'spmb_admin_access' => 'nullable|boolean',
        ]);
        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        $data['is_bk'] = $request->has('is_bk') ? (bool)$request->is_bk : false;
        $data['spmb_admin_access'] = $request->has('spmb_admin_access') ? (bool)$request->spmb_admin_access : false;
        User::create($data);
        return redirect()->route('manage.users.index')->with('success', 'Pengguna berhasil ditambahkan!');
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
        return view('users.edit', compact('user'));
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
            'role' => 'required|string|in:superadmin,admin,admin_bk,admin_jurnal,spmb_admin,kasir,bendahara',
            'is_bk' => 'nullable|boolean',
            'spmb_admin_access' => 'nullable|boolean',
        ]);
        $data = $request->all();
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_bk'] = $request->has('is_bk') ? (bool)$request->is_bk : false;
        $data['spmb_admin_access'] = $request->has('spmb_admin_access') ? (bool)$request->spmb_admin_access : false;
        $user->update($data);
        return redirect()->route('manage.users.index')->with('success', 'Pengguna berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('manage.users.index')->with('success', 'Pengguna berhasil dihapus!');
    }
}
