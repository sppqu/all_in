<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        // Jika tidak ada school_id, redirect ke foundation dashboard untuk superadmin/admin_yayasan
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $classes = ClassModel::where('school_id', $currentSchoolId)
            ->withCount('students')
            ->orderBy('class_name')
            ->get();
        
        return view('classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
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
        
        return view('classes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
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
        
        // Validasi: class_name harus unique per sekolah
        $request->validate([
            'class_name' => [
                'required',
                'string',
                'max:45',
                function ($attribute, $value, $fail) use ($currentSchoolId) {
                    $exists = ClassModel::where('school_id', $currentSchoolId)
                        ->where('class_name', $value)
                        ->exists();
                    if ($exists) {
                        $fail('Nama kelas sudah digunakan di sekolah ini.');
                    }
                }
            ]
        ]);

        ClassModel::create([
            'class_name' => $request->class_name,
            'school_id' => $currentSchoolId,
        ]);

        return redirect()->route('classes.index')
            ->with('success', 'Kelas berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassModel $class)
    {
        // Pastikan kelas ini milik sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        // Cek apakah kelas ini milik sekolah yang sedang aktif
        if ($class->school_id != $currentSchoolId) {
            abort(403, 'Akses ditolak: Anda tidak memiliki akses ke kelas ini.');
        }
        
        // Filter siswa berdasarkan sekolah yang sedang aktif
        $class->load(['students' => function($query) use ($currentSchoolId) {
            $query->where('school_id', $currentSchoolId)
                  ->orderBy('student_full_name');
        }]);
        
        return view('classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassModel $class)
    {
        // Pastikan kelas ini milik sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        // Cek apakah kelas ini milik sekolah yang sedang aktif
        if ($class->school_id != $currentSchoolId) {
            abort(403, 'Akses ditolak: Anda tidak memiliki akses ke kelas ini.');
        }
        
        return view('classes.edit', compact('class'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassModel $class)
    {
        // Pastikan kelas ini milik sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        // Cek apakah kelas ini milik sekolah yang sedang aktif
        if ($class->school_id != $currentSchoolId) {
            abort(403, 'Akses ditolak: Anda tidak memiliki akses ke kelas ini.');
        }
        
        // Validasi: class_name harus unique per sekolah
        $request->validate([
            'class_name' => [
                'required',
                'string',
                'max:45',
                function ($attribute, $value, $fail) use ($currentSchoolId, $class) {
                    $exists = ClassModel::where('school_id', $currentSchoolId)
                        ->where('class_name', $value)
                        ->where('class_id', '!=', $class->class_id)
                        ->exists();
                    if ($exists) {
                        $fail('Nama kelas sudah digunakan di sekolah ini.');
                    }
                }
            ]
        ]);

        $class->update([
            'class_name' => $request->class_name,
        ]);

        return redirect()->route('classes.index')
            ->with('success', 'Kelas berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassModel $class)
    {
        // Pastikan kelas ini milik sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        // Cek apakah kelas ini milik sekolah yang sedang aktif
        if ($class->school_id != $currentSchoolId) {
            abort(403, 'Akses ditolak: Anda tidak memiliki akses ke kelas ini.');
        }
        
        // Cek apakah ada siswa di kelas ini
        if ($class->students()->where('school_id', $currentSchoolId)->count() > 0) {
            return redirect()->route('classes.index')
                ->with('error', 'Tidak dapat menghapus kelas yang masih memiliki siswa!');
        }

        $class->delete();

        return redirect()->route('classes.index')
            ->with('success', 'Kelas berhasil dihapus!');
    }
}
