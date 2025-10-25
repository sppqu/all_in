<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SPMBKejuruan;
use App\Models\SPMBRegistration;

class SPMBKejuruanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kejuruan = SPMBKejuruan::withCount('registrations')->orderBy('nama_kejuruan')->get();
        
        $stats = [
            'total' => SPMBKejuruan::count(),
            'aktif' => SPMBKejuruan::where('aktif', true)->count(),
            'tidak_aktif' => SPMBKejuruan::where('aktif', false)->count(),
            'total_pendaftar' => SPMBRegistration::count(),
        ];

        return view('admin.spmb.kejuruan.index', compact('kejuruan', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.spmb.kejuruan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kejuruan' => 'required|string|max:255',
            'kode_kejuruan' => 'required|string|max:10|unique:s_p_m_b_kejuruans,kode_kejuruan',
            'deskripsi' => 'nullable|string',
            'aktif' => 'boolean',
            'kuota' => 'nullable|integer|min:0'
        ]);

        SPMBKejuruan::create($request->all());

        return redirect()->route('manage.spmb.kejuruan.index')
            ->with('success', 'Kejuruan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $kejuruan = SPMBKejuruan::with(['registrations' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
        $stats = [
            'total_pendaftar' => $kejuruan->registrations->count(),
            'diterima' => $kejuruan->registrations->where('status_pendaftaran', 'diterima')->count(),
            'pending' => $kejuruan->registrations->where('status_pendaftaran', 'pending')->count(),
            'ditolak' => $kejuruan->registrations->where('status_pendaftaran', 'ditolak')->count(),
        ];

        return view('admin.spmb.kejuruan.show', compact('kejuruan', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kejuruan = SPMBKejuruan::findOrFail($id);
        return view('admin.spmb.kejuruan.edit', compact('kejuruan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $kejuruan = SPMBKejuruan::findOrFail($id);
        
        $request->validate([
            'nama_kejuruan' => 'required|string|max:255',
            'kode_kejuruan' => 'required|string|max:10|unique:s_p_m_b_kejuruans,kode_kejuruan,' . $id,
            'deskripsi' => 'nullable|string',
            'aktif' => 'boolean',
            'kuota' => 'nullable|integer|min:0'
        ]);

        $kejuruan->update($request->all());

        return redirect()->route('manage.spmb.kejuruan.index')
            ->with('success', 'Kejuruan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kejuruan = SPMBKejuruan::findOrFail($id);
        
        // Check if kejuruan has registrations
        if ($kejuruan->registrations()->count() > 0) {
            return redirect()->route('manage.spmb.kejuruan.index')
                ->with('error', 'Tidak dapat menghapus kejuruan yang sudah memiliki pendaftar.');
        }

        $kejuruan->delete();

        return redirect()->route('manage.spmb.kejuruan.index')
            ->with('success', 'Kejuruan berhasil dihapus.');
    }

    /**
     * Toggle status kejuruan
     */
    public function toggleStatus(Request $request, $id)
    {
        $kejuruan = SPMBKejuruan::findOrFail($id);
        $kejuruan->update(['aktif' => !$kejuruan->aktif]);

        $status = $kejuruan->aktif ? 'diaktifkan' : 'dinonaktifkan';
        
        return back()->with('success', "Kejuruan {$status}.");
    }

    /**
     * Bulk action for kejuruan
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'kejuruan_ids' => 'required|array',
            'kejuruan_ids.*' => 'exists:s_p_m_b_kejuruans,id'
        ]);

        $kejuruanIds = $request->kejuruan_ids;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                SPMBKejuruan::whereIn('id', $kejuruanIds)->update(['aktif' => true]);
                $message = 'Kejuruan berhasil diaktifkan.';
                break;
            case 'deactivate':
                SPMBKejuruan::whereIn('id', $kejuruanIds)->update(['aktif' => false]);
                $message = 'Kejuruan berhasil dinonaktifkan.';
                break;
            case 'delete':
                // Check if any kejuruan has registrations
                $kejuruanWithRegistrations = SPMBKejuruan::whereIn('id', $kejuruanIds)
                    ->whereHas('registrations')
                    ->count();
                
                if ($kejuruanWithRegistrations > 0) {
                    return back()->with('error', 'Tidak dapat menghapus kejuruan yang sudah memiliki pendaftar.');
                }
                
                SPMBKejuruan::whereIn('id', $kejuruanIds)->delete();
                $message = 'Kejuruan berhasil dihapus.';
                break;
        }

        return back()->with('success', $message);
    }
}
