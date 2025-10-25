<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BimbinganKonseling;
use App\Models\Student;
use Illuminate\Http\Request;

class BimbinganKonselingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BimbinganKonseling::with(['siswa.class', 'guruBK']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by jenis
        if ($request->filled('jenis')) {
            $query->where('jenis_bimbingan', $request->jenis);
        }

        // Search by siswa name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('student_full_name', 'like', '%' . $search . '%')
                  ->orWhere('student_nis', 'like', '%' . $search . '%');
            });
        }

        $bimbingan = $query->orderBy('tanggal_bimbingan', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->paginate(15);

        return view('manage.bk.bimbingan.index', compact('bimbingan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::with('class')
                          ->orderBy('student_full_name')
                          ->get();

        return view('manage.bk.bimbingan.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:students,student_id',
            'jenis_bimbingan' => 'required|in:akademik,pribadi,sosial,karir',
            'kategori' => 'required|in:ringan,sedang,berat',
            'permasalahan' => 'required|string',
            'analisis' => 'nullable|string',
            'tindakan' => 'nullable|string',
            'hasil' => 'nullable|string',
            'tanggal_bimbingan' => 'required|date',
            'sesi_ke' => 'required|integer|min:1',
            'status' => 'required|in:dijadwalkan,berlangsung,selesai,ditunda',
            'catatan' => 'nullable|string',
        ]);

        $validated['guru_bk_id'] = auth()->id();

        $bimbingan = BimbinganKonseling::create($validated);

        return redirect()->route('manage.bk.bimbingan.index')
                        ->with('success', 'Data bimbingan konseling berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BimbinganKonseling $bimbingan)
    {
        $bimbingan->load(['siswa.class', 'guruBk']);
        
        // Get sesi history for this student
        $riwayat = BimbinganKonseling::where('siswa_id', $bimbingan->siswa_id)
                                    ->where('bimbingan_id', '!=', $bimbingan->bimbingan_id)
                                    ->orderBy('tanggal_bimbingan', 'desc')
                                    ->get();

        return view('manage.bk.bimbingan.show', compact('bimbingan', 'riwayat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BimbinganKonseling $bimbingan)
    {
        $students = Student::with('class')
                          ->orderBy('student_full_name')
                          ->get();

        return view('manage.bk.bimbingan.edit', compact('bimbingan', 'students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BimbinganKonseling $bimbingan)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:students,student_id',
            'jenis_bimbingan' => 'required|in:akademik,pribadi,sosial,karir',
            'kategori' => 'required|in:ringan,sedang,berat',
            'permasalahan' => 'required|string',
            'analisis' => 'nullable|string',
            'tindakan' => 'nullable|string',
            'hasil' => 'nullable|string',
            'tanggal_bimbingan' => 'required|date',
            'sesi_ke' => 'required|integer|min:1',
            'status' => 'required|in:dijadwalkan,berlangsung,selesai,ditunda',
            'catatan' => 'nullable|string',
        ]);

        $bimbingan->update($validated);

        return redirect()->route('manage.bk.bimbingan.index')
                        ->with('success', 'Data bimbingan konseling berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BimbinganKonseling $bimbingan)
    {
        $bimbingan->delete();

        return redirect()->route('manage.bk.bimbingan.index')
                        ->with('success', 'Data bimbingan konseling berhasil dihapus.');
    }
}

