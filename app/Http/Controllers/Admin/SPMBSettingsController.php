<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SPMBSettings;
use App\Models\SPMBKejuruan;
use App\Models\SPMBRegistration;

class SPMBSettingsController extends Controller
{
    /**
     * Display SPMB settings dashboard
     */
    public function index()
    {
        $settings = SPMBSettings::orderBy('tahun_pelajaran', 'desc')->get();
        $kejuruan = SPMBKejuruan::orderBy('nama_kejuruan')->get();
        
        $stats = [
            'total_pendaftar' => SPMBRegistration::count(),
            'pending' => SPMBRegistration::where('status_pendaftaran', 'pending')->count(),
            'diterima' => SPMBRegistration::where('status_pendaftaran', 'diterima')->count(),
            'ditolak' => SPMBRegistration::where('status_pendaftaran', 'ditolak')->count(),
        ];

        return view('admin.spmb.settings', compact('settings', 'kejuruan', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.spmb.settings-create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun_pelajaran' => 'required|string|unique:s_p_m_b_settings,tahun_pelajaran',
            'pendaftaran_dibuka' => 'boolean',
            'tanggal_buka' => 'nullable|date',
            'tanggal_tutup' => 'nullable|date|after_or_equal:tanggal_buka',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'biaya_spmb' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string'
        ]);

        SPMBSettings::create($request->all());

        return redirect()->route('manage.spmb.settings')
            ->with('success', 'Pengaturan SPMB berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $settings = SPMBSettings::findOrFail($id);
        return view('admin.spmb.settings-show', compact('settings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $settings = SPMBSettings::findOrFail($id);
        return view('admin.spmb.settings-edit', compact('settings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $settings = SPMBSettings::findOrFail($id);
        
        $request->validate([
            'tahun_pelajaran' => 'required|string|unique:s_p_m_b_settings,tahun_pelajaran,' . $id,
            'pendaftaran_dibuka' => 'boolean',
            'tanggal_buka' => 'nullable|date',
            'tanggal_tutup' => 'nullable|date|after_or_equal:tanggal_buka',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'biaya_spmb' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string'
        ]);

        $settings->update($request->all());

        return redirect()->route('manage.spmb.settings')
            ->with('success', 'Pengaturan SPMB berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $settings = SPMBSettings::findOrFail($id);
        $settings->delete();

        return redirect()->route('manage.spmb.settings')
            ->with('success', 'Pengaturan SPMB berhasil dihapus.');
    }

    /**
     * Toggle registration status
     */
    public function toggleRegistration(Request $request, $id)
    {
        $settings = SPMBSettings::findOrFail($id);
        $settings->update(['pendaftaran_dibuka' => !$settings->pendaftaran_dibuka]);

        $status = $settings->pendaftaran_dibuka ? 'dibuka' : 'ditutup';
        
        return back()->with('success', "Pendaftaran SPMB {$status}.");
    }

    /**
     * Update registration status
     */
    public function updateRegistrationStatus(Request $request, $id)
    {
        $request->validate([
            'status_pendaftaran' => 'required|in:pending,diterima,ditolak',
            'catatan_admin' => 'nullable|string'
        ]);

        $registration = SPMBRegistration::findOrFail($id);
        $registration->update([
            'status_pendaftaran' => $request->status_pendaftaran,
            'catatan_admin' => $request->catatan_admin
        ]);

        // Generate nomor pendaftaran jika diterima
        if ($request->status_pendaftaran === 'diterima' && !$registration->nomor_pendaftaran) {
            $registration->generateNomorPendaftaran();
        }

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }
}
