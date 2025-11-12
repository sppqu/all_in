<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Foundation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    /**
     * Display a listing of schools
     */
    public function index()
    {
        $foundationId = session('foundation_id') ?? Foundation::first()?->id;
        
        if (!$foundationId) {
            return redirect()->route('manage.general.setting')
                ->with('error', 'Belum ada yayasan. Silakan setup yayasan terlebih dahulu.');
        }

        $foundation = Foundation::findOrFail($foundationId);
        $schools = $foundation->schools()->orderBy('nama_sekolah')->get();

        return view('foundation.schools.index', compact('foundation', 'schools'));
    }

    /**
     * Show the form for creating a new school
     */
    public function create()
    {
        $foundationId = session('foundation_id') ?? Foundation::first()?->id;
        $foundation = Foundation::findOrFail($foundationId);

        return view('foundation.schools.create', compact('foundation'));
    }

    /**
     * Store a newly created school
     */
    public function store(Request $request)
    {
        $request->validate([
            'foundation_id' => 'required|exists:foundations,id',
            'jenjang' => 'required|string|max:50',
            'nama_sekolah' => 'required|string|max:255',
            'kepala_sekolah' => 'nullable|string|max:255',
            'npsn' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'required|string',
            'alamat_baris_1' => 'nullable|string',
            'alamat_baris_2' => 'nullable|string|max:255',
            'no_telp' => 'required|string|max:50',
            'logo_sekolah' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $school = new School();
        $school->foundation_id = $request->foundation_id;
        $school->jenjang = $request->jenjang;
        $school->nama_sekolah = $request->nama_sekolah;
        $school->kepala_sekolah = $request->kepala_sekolah;
        $school->npsn = $request->npsn;
        $school->email = $request->email;
        $school->alamat = $request->alamat;
        $school->alamat_baris_1 = $request->alamat_baris_1;
        $school->alamat_baris_2 = $request->alamat_baris_2;
        $school->no_telp = $request->no_telp;
        $school->status = 'active'; // Default selalu aktif

        if ($request->hasFile('logo_sekolah')) {
            $logoPath = $request->file('logo_sekolah')->store('schools/logos', 'public');
            $school->logo_sekolah = $logoPath;
        }

        $school->save();

        return redirect()->route('manage.foundation.schools.index')
            ->with('success', 'Sekolah berhasil ditambahkan.');
    }

    /**
     * Display the specified school
     */
    public function show(School $school)
    {
        $school->load('students', 'classes', 'periods');
        
        $stats = [
            'total_students' => $school->students()->where('student_status', 1)->count(),
            'total_classes' => $school->classes()->count(),
            'total_periods' => $school->periods()->count(),
        ];

        return view('foundation.schools.show', compact('school', 'stats'));
    }

    /**
     * Show the form for editing the specified school
     */
    public function edit(School $school)
    {
        $user = auth()->user();
        
        // Check access: Foundation level bisa edit semua, admin sekolah hanya bisa edit sekolahnya sendiri
        if (!in_array($user->role, ['superadmin', 'admin_yayasan'])) {
            $userSchoolIds = DB::table('user_schools')
                ->where('user_id', $user->id)
                ->pluck('school_id')
                ->toArray();
            
            if (!in_array($school->id, $userSchoolIds)) {
                return redirect()->route('manage.foundation.schools.index')
                    ->with('error', 'Anda tidak memiliki akses untuk mengedit sekolah ini.');
            }
        }
        
        $foundation = $school->foundation;

        return view('foundation.schools.edit', compact('school', 'foundation'));
    }

    /**
     * Update the specified school
     */
    public function update(Request $request, School $school)
    {
        $user = auth()->user();
        $isFoundationLevel = in_array($user->role, ['superadmin', 'admin_yayasan']);
        
        // Check access: Foundation level bisa edit semua, admin sekolah hanya bisa edit sekolahnya sendiri
        if (!$isFoundationLevel) {
            $userSchoolIds = DB::table('user_schools')
                ->where('user_id', $user->id)
                ->pluck('school_id')
                ->toArray();
            
            if (!in_array($school->id, $userSchoolIds)) {
                return redirect()->route('manage.foundation.schools.index')
                    ->with('error', 'Anda tidak memiliki akses untuk mengedit sekolah ini.');
            }
        }
        
        $validationRules = [
            'no_telp' => 'required|string|max:50',
            'npsn' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat_baris_1' => 'nullable|string',
            'alamat_baris_2' => 'nullable|string|max:255',
            'logo_sekolah' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
        
        // Hanya foundation level yang bisa edit nama_sekolah, jenjang, kepala_sekolah
        if ($isFoundationLevel) {
            $validationRules['jenjang'] = 'required|string|max:50';
            $validationRules['nama_sekolah'] = 'required|string|max:255';
            $validationRules['kepala_sekolah'] = 'nullable|string|max:255';
            $validationRules['alamat'] = 'required|string';
        }
        
        $request->validate($validationRules);

        // Update field sesuai level user
        if ($isFoundationLevel) {
            $school->jenjang = $request->jenjang;
            $school->nama_sekolah = $request->nama_sekolah;
            $school->kepala_sekolah = $request->kepala_sekolah;
            $school->alamat = $request->alamat;
        }
        
        // Field yang bisa di-edit oleh admin sekolah
        $school->no_telp = $request->no_telp;
        $school->npsn = $request->npsn;
        $school->email = $request->email;
        $school->alamat_baris_1 = $request->alamat_baris_1;
        $school->alamat_baris_2 = $request->alamat_baris_2;
        $school->status = 'active'; // Selalu aktif, tidak bisa diubah

        if ($request->hasFile('logo_sekolah')) {
            // Hapus logo lama
            if ($school->logo_sekolah && Storage::disk('public')->exists($school->logo_sekolah)) {
                Storage::disk('public')->delete($school->logo_sekolah);
            }
            
            $logoPath = $request->file('logo_sekolah')->store('schools/logos', 'public');
            $school->logo_sekolah = $logoPath;
        }

        $school->save();

        // Redirect berdasarkan role user
        $user = auth()->user();
        if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
            // Foundation level: redirect ke index sekolah
            return redirect()->route('manage.foundation.schools.index')
                ->with('success', 'Data sekolah berhasil diperbarui.');
        } else {
            // Admin sekolah: redirect ke dashboard sekolah
            return redirect()->route('manage.admin.dashboard')
                ->with('success', 'Profil sekolah berhasil diperbarui.');
        }
    }

    /**
     * Remove the specified school
     */
    public function destroy(School $school)
    {
        // Cek apakah ada data terkait
        $hasStudents = $school->students()->exists();
        $hasPayments = DB::table('transfer')
            ->whereIn('student_id', $school->students()->pluck('student_id'))
            ->exists();

        if ($hasStudents || $hasPayments) {
            return redirect()->route('manage.foundation.schools.index')
                ->with('error', 'Tidak dapat menghapus sekolah karena masih memiliki data terkait (siswa, pembayaran, dll).');
        }

        // Hapus logo
        if ($school->logo_sekolah && Storage::disk('public')->exists($school->logo_sekolah)) {
            Storage::disk('public')->delete($school->logo_sekolah);
        }

        $school->delete();

        return redirect()->route('manage.foundation.schools.index')
            ->with('success', 'Sekolah berhasil dihapus.');
    }

    /**
     * Switch school context
     */
    public function switchSchool(Request $request, $schoolId)
    {
        $school = School::findOrFail($schoolId);
        
        // Validate user access
        $user = auth()->user();
        if ($user->role !== 'superadmin' && $user->role !== 'admin_yayasan') {
            $hasAccess = DB::table('user_schools')
                ->where('user_id', $user->id)
                ->where('school_id', $schoolId)
                ->exists();

            if (!$hasAccess) {
                return redirect()->back()
                    ->with('error', 'Anda tidak memiliki akses ke sekolah ini.');
            }
        }

        session(['current_school_id' => $school->id]);
        session(['foundation_id' => $school->foundation_id]);

        return redirect()->back()
            ->with('success', 'Sekolah berhasil dipilih: ' . $school->nama_sekolah);
    }
}

