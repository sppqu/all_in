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
        $periods = Period::orderBy('period_start', 'desc')->get();
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

        // Jika status aktif, nonaktifkan periode lain di sekolah yang sama
        if ($request->period_status) {
            Period::where('school_id', $currentSchoolId)
                ->where('period_status', 1)
                ->update(['period_status' => 0]);
        }

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
        return view('periods.show', compact('period'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Period $period)
    {
        return view('periods.edit', compact('period'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Period $period)
    {
        $request->validate([
            'period_start' => 'required|integer|min:2000|max:2100',
            'period_end' => 'required|integer|min:2000|max:2100|gt:period_start',
            'period_status' => 'boolean'
        ]);

        // Jika status aktif, nonaktifkan periode lain
        if ($request->period_status) {
            Period::where('period_id', '!=', $period->period_id)
                  ->where('period_status', 1)
                  ->update(['period_status' => 0]);
        }

        $period->update($request->all());

        return redirect()->route('periods.index')
            ->with('success', 'Tahun pelajaran berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Period $period)
    {
        $period->delete();

        return redirect()->route('periods.index')
            ->with('success', 'Tahun pelajaran berhasil dihapus!');
    }

    /**
     * Set period as active
     */
    public function setActive(Period $period)
    {
        // Nonaktifkan semua periode
        Period::where('period_status', 1)->update(['period_status' => 0]);
        
        // Aktifkan periode yang dipilih
        $period->update(['period_status' => 1]);

        return redirect()->route('periods.index')
            ->with('success', 'Tahun pelajaran ' . $period->period_name . ' berhasil diaktifkan!');
    }
}
