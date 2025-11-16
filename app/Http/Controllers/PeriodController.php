<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        // Ambil periode sesuai school_id yang sedang aktif
        $periods = Period::where('school_id', $currentSchoolId)
            ->orderBy('period_start', 'desc')
            ->get();
            
        return view('periods.index', compact('periods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('periods.create');
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
        
        $request->validate([
            'period_start' => 'required|integer|min:2000|max:2100',
            'period_end' => 'required|integer|min:2000|max:2100|gt:period_start',
            'period_status' => 'boolean'
        ]);

        // Allow multiple active periods - removed logic to deactivate others

        $data = $request->all();
        $data['school_id'] = $currentSchoolId;
        Period::create($data);

        return redirect()->route('periods.index')
            ->with('success', 'Tahun pelajaran berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Period $period)
    {
        // Pastikan periode sesuai dengan school_id yang sedang aktif
        $currentSchoolId = currentSchoolId();
        if ($period->school_id != $currentSchoolId) {
            abort(403, 'Akses ditolak: Tahun pelajaran tidak sesuai dengan sekolah yang dipilih.');
        }
        
        return view('periods.show', compact('period'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Period $period)
    {
        // Pastikan periode sesuai dengan school_id yang sedang aktif
        $currentSchoolId = currentSchoolId();
        if ($period->school_id != $currentSchoolId) {
            abort(403, 'Akses ditolak: Tahun pelajaran tidak sesuai dengan sekolah yang dipilih.');
        }
        
        return view('periods.edit', compact('period'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Period $period)
    {
        // Pastikan periode sesuai dengan school_id yang sedang aktif
        $currentSchoolId = currentSchoolId();
        if ($period->school_id != $currentSchoolId) {
            abort(403, 'Akses ditolak: Tahun pelajaran tidak sesuai dengan sekolah yang dipilih.');
        }
        
        $request->validate([
            'period_start' => 'required|integer|min:2000|max:2100',
            'period_end' => 'required|integer|min:2000|max:2100|gt:period_start',
            'period_status' => 'boolean'
        ]);

        // Allow multiple active periods - removed logic to deactivate others

        $period->update($request->all());

        return redirect()->route('periods.index')
            ->with('success', 'Tahun pelajaran berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Period $period)
    {
        // Pastikan periode sesuai dengan school_id yang sedang aktif
        $currentSchoolId = currentSchoolId();
        if ($period->school_id != $currentSchoolId) {
            abort(403, 'Akses ditolak: Tahun pelajaran tidak sesuai dengan sekolah yang dipilih.');
        }
        
        $period->delete();

        return redirect()->route('periods.index')
            ->with('success', 'Tahun pelajaran berhasil dihapus!');
    }

    /**
     * Set period as active
     */
    public function setActive(Period $period)
    {
        // Pastikan periode sesuai dengan school_id yang sedang aktif
        $currentSchoolId = currentSchoolId();
        if ($period->school_id != $currentSchoolId) {
            abort(403, 'Akses ditolak: Tahun pelajaran tidak sesuai dengan sekolah yang dipilih.');
        }
        
        // Nonaktifkan semua periode
        Period::where('period_status', 1)->update(['period_status' => 0]);
        
        // Aktifkan periode yang dipilih
        $period->update(['period_status' => 1]);

        return redirect()->route('periods.index')
            ->with('success', 'Tahun pelajaran ' . $period->period_name . ' berhasil diaktifkan!');
    }

    /**
     * Toggle period status
     */
    public function toggleStatus(Request $request, Period $period)
    {
        try {
            // Pastikan periode sesuai dengan school_id yang sedang aktif
            $currentSchoolId = currentSchoolId();
            if ($period->school_id != $currentSchoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Tahun pelajaran tidak sesuai dengan sekolah yang dipilih.'
                ], 403);
            }
            
            // Allow multiple active periods - no need to deactivate others
            $period->update(['period_status' => $request->status]);
            
            $statusText = $request->status == 1 ? 'diaktifkan' : 'dinonaktifkan';
            
            return response()->json([
                'success' => true,
                'message' => 'Status tahun pelajaran berhasil ' . $statusText,
                'status' => $period->period_status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }
}
