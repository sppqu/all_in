<?php

namespace App\Http\Controllers;

use App\Models\Pos;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        // Filter POS berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $posList = Pos::where('school_id', $currentSchoolId)
            ->orderBy('pos_name')
            ->get();
        
        return view('pos.index', compact('posList'));
    }

    public function create()
    {
        // Pastikan ada school_id
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        return view('pos.create');
    }

    public function store(Request $request)
    {
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $request->validate([
            'pos_name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($currentSchoolId) {
                    $exists = Pos::where('school_id', $currentSchoolId)
                        ->where('pos_name', $value)
                        ->exists();
                    if ($exists) {
                        $fail('Nama POS sudah digunakan di sekolah ini.');
                    }
                }
            ],
            'pos_description' => 'nullable|string|max:100',
        ]);
        
        Pos::create([
            'pos_name' => $request->pos_name,
            'pos_description' => $request->pos_description,
            'school_id' => $currentSchoolId,
        ]);
        
        return redirect()->route('pos.index')->with('success', 'Pos Pembayaran berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $pos = Pos::where('school_id', $currentSchoolId)->findOrFail($id);
        return view('pos.edit', compact('pos'));
    }

    public function update(Request $request, $id)
    {
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $pos = Pos::where('school_id', $currentSchoolId)->findOrFail($id);
        
        $request->validate([
            'pos_name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($currentSchoolId, $id) {
                    $exists = Pos::where('school_id', $currentSchoolId)
                        ->where('pos_name', $value)
                        ->where('pos_id', '!=', $id)
                        ->exists();
                    if ($exists) {
                        $fail('Nama POS sudah digunakan di sekolah ini.');
                    }
                }
            ],
            'pos_description' => 'nullable|string|max:100',
        ]);
        
        $pos->update([
            'pos_name' => $request->pos_name,
            'pos_description' => $request->pos_description,
        ]);
        
        return redirect()->route('pos.index')->with('success', 'Pos Pembayaran berhasil diupdate!');
    }

    public function destroy($id)
    {
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $pos = Pos::where('school_id', $currentSchoolId)->findOrFail($id);
        
        // Cek apakah POS digunakan di Payment
        $hasPayment = \DB::table('payment')
            ->where('pos_pos_id', $pos->pos_id)
            ->exists();
        
        if ($hasPayment) {
            return redirect()->route('pos.index')
                ->with('error', 'Tidak dapat menghapus POS karena masih digunakan dalam jenis pembayaran!');
        }
        
        $pos->delete();
        return redirect()->route('pos.index')->with('success', 'Pos Pembayaran berhasil dihapus!');
    }
} 