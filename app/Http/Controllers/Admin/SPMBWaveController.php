<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SPMBWave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SPMBWaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $waves = SPMBWave::withCount('registrations')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.spmb.waves.index', compact('waves'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.spmb.waves.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'registration_fee' => 'required|numeric|min:0',
            'spmb_fee' => 'required|numeric|min:0',
            'quota' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Nama gelombang wajib diisi',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini',
            'end_date.required' => 'Tanggal berakhir wajib diisi',
            'end_date.after' => 'Tanggal berakhir harus setelah tanggal mulai',
            'registration_fee.required' => 'Biaya pendaftaran wajib diisi',
            'registration_fee.min' => 'Biaya pendaftaran tidak boleh kurang dari 0',
            'spmb_fee.required' => 'Biaya SPMB wajib diisi',
            'spmb_fee.min' => 'Biaya SPMB tidak boleh kurang dari 0',
            'quota.min' => 'Kuota minimal 1'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $wave = SPMBWave::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'registration_fee' => $request->registration_fee,
            'spmb_fee' => $request->spmb_fee,
            'quota' => $request->quota,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('manage.spmb.waves.index')
            ->with('success', 'Gelombang pendaftaran berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $wave = SPMBWave::with(['registrations' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        $stats = [
            'total_registrations' => $wave->registrations->count(),
            'pending' => $wave->registrations->where('status_pendaftaran', 'pending')->count(),
            'diterima' => $wave->registrations->where('status_pendaftaran', 'diterima')->count(),
            'ditolak' => $wave->registrations->where('status_pendaftaran', 'ditolak')->count(),
            'paid_registration' => $wave->registrations->where('registration_fee_paid', true)->count(),
            'paid_spmb' => $wave->registrations->where('spmb_fee_paid', true)->count(),
        ];

        return view('admin.spmb.waves.show', compact('wave', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $wave = SPMBWave::findOrFail($id);
        return view('admin.spmb.waves.edit', compact('wave'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $wave = SPMBWave::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'registration_fee' => 'required|numeric|min:0',
            'spmb_fee' => 'required|numeric|min:0',
            'quota' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Nama gelombang wajib diisi',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'end_date.required' => 'Tanggal berakhir wajib diisi',
            'end_date.after' => 'Tanggal berakhir harus setelah tanggal mulai',
            'registration_fee.required' => 'Biaya pendaftaran wajib diisi',
            'registration_fee.min' => 'Biaya pendaftaran tidak boleh kurang dari 0',
            'spmb_fee.required' => 'Biaya SPMB wajib diisi',
            'spmb_fee.min' => 'Biaya SPMB tidak boleh kurang dari 0',
            'quota.min' => 'Kuota minimal 1'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $wave->update([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'registration_fee' => $request->registration_fee,
            'spmb_fee' => $request->spmb_fee,
            'quota' => $request->quota,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('manage.spmb.waves.index')
            ->with('success', 'Gelombang pendaftaran berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $wave = SPMBWave::findOrFail($id);

        // Check if wave has registrations
        if ($wave->registrations()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus gelombang yang sudah memiliki pendaftaran');
        }

        $wave->delete();

        return redirect()->route('manage.spmb.waves.index')
            ->with('success', 'Gelombang pendaftaran berhasil dihapus');
    }

    /**
     * Toggle wave active status
     */
    public function toggleStatus(string $id)
    {
        $wave = SPMBWave::findOrFail($id);
        $wave->update(['is_active' => !$wave->is_active]);

        $status = $wave->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()
            ->with('success', "Gelombang {$wave->name} berhasil {$status}");
    }
}
