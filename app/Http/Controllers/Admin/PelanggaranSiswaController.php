<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranSiswa;
use App\Models\Pelanggaran;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PelanggaranSiswaController extends Controller
{
    /**
     * Display a listing of pelanggaran siswa
     */
    public function index(Request $request)
    {
        $query = PelanggaranSiswa::with(['siswa', 'pelanggaran.kategori', 'creator']);

        // Filter by siswa
        if ($request->has('siswa_id') && $request->siswa_id != '') {
            $query->where('siswa_id', $request->siswa_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date != '') {
            $query->where('tanggal_pelanggaran', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->where('tanggal_pelanggaran', '<=', $request->end_date);
        }

        // Search by siswa name or NIS
        if ($request->has('search') && $request->search != '') {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('student_full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('student_nis', 'like', '%' . $request->search . '%');
            });
        }

        $pelanggaranSiswa = $query->latest('tanggal_pelanggaran')->paginate(20);
        
        // For filter dropdowns
        $students = Student::orderBy('student_full_name')->get();

        return view('manage.bk.pelanggaran-siswa.index', compact('pelanggaranSiswa', 'students'));
    }

    /**
     * Show the form for creating a new record
     */
    public function create()
    {
        $students = Student::with('class')
            ->orderBy('student_full_name')
            ->get();
        $pelanggaran = Pelanggaran::with('kategori')->active()->get()->groupBy('kategori.nama');
        
        return view('manage.bk.pelanggaran-siswa.create', compact('students', 'pelanggaran'));
    }

    /**
     * Store a newly created record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:students,student_id',
            'pelanggaran_id' => 'required|exists:pelanggaran,id',
            'tanggal_pelanggaran' => 'required|date',
            'keterangan' => 'nullable|string',
            'pelapor' => 'required|string|max:255',
            'tempat' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $validated['created_by'] = Auth::id();

        // If auto-approve
        if ($validated['status'] === 'approved') {
            $validated['approved_by'] = Auth::id();
            $validated['approved_at'] = now();
        }

        PelanggaranSiswa::create($validated);

        return redirect()->route('manage.bk.pelanggaran-siswa.index')
            ->with('success', 'Pelanggaran siswa berhasil dicatat.');
    }

    /**
     * Display the specified record
     */
    public function show(PelanggaranSiswa $pelanggaranSiswa)
    {
        $pelanggaranSiswa->load(['siswa', 'pelanggaran.kategori', 'creator', 'approver']);
        
        // Get total point siswa
        $totalPoint = PelanggaranSiswa::getTotalPointSiswa($pelanggaranSiswa->siswa_id);
        
        // Get history pelanggaran siswa ini
        $history = PelanggaranSiswa::where('siswa_id', $pelanggaranSiswa->siswa_id)
            ->with(['pelanggaran.kategori'])
            ->where('status', 'approved')
            ->latest('tanggal_pelanggaran')
            ->limit(10)
            ->get();

        return view('manage.bk.pelanggaran-siswa.show', compact('pelanggaranSiswa', 'totalPoint', 'history'));
    }

    /**
     * Show the form for editing the specified record
     */
    public function edit(PelanggaranSiswa $pelanggaranSiswa)
    {
        $students = Student::orderBy('student_full_name')->get();
        $pelanggaran = Pelanggaran::with('kategori')->active()->get()->groupBy('kategori.nama');
        
        return view('manage.bk.pelanggaran-siswa.edit', compact('pelanggaranSiswa', 'students', 'pelanggaran'));
    }

    /**
     * Update the specified record
     */
    public function update(Request $request, PelanggaranSiswa $pelanggaranSiswa)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:students,id',
            'pelanggaran_id' => 'required|exists:pelanggaran,id',
            'tanggal_pelanggaran' => 'required|date',
            'keterangan' => 'nullable|string',
            'pelapor' => 'required|string|max:255',
            'tempat' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
            'catatan_admin' => 'nullable|string'
        ]);

        // Update approval info if status changed to approved
        if ($validated['status'] === 'approved' && $pelanggaranSiswa->status !== 'approved') {
            $validated['approved_by'] = Auth::id();
            $validated['approved_at'] = now();
        }

        $pelanggaranSiswa->update($validated);

        return redirect()->route('manage.bk.pelanggaran-siswa.index')
            ->with('success', 'Data pelanggaran berhasil diupdate.');
    }

    /**
     * Remove the specified record
     */
    public function destroy(PelanggaranSiswa $pelanggaranSiswa)
    {
        $pelanggaranSiswa->delete();

        return redirect()->route('manage.bk.pelanggaran-siswa.index')
            ->with('success', 'Data pelanggaran berhasil dihapus.');
    }

    /**
     * Approve pelanggaran
     */
    public function approve(PelanggaranSiswa $pelanggaranSiswa)
    {
        $pelanggaranSiswa->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Pelanggaran berhasil disetujui.');
    }

    /**
     * Reject pelanggaran
     */
    public function reject(Request $request, PelanggaranSiswa $pelanggaranSiswa)
    {
        $request->validate([
            'catatan_admin' => 'required|string'
        ]);

        $pelanggaranSiswa->update([
            'status' => 'rejected',
            'catatan_admin' => $request->catatan_admin,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Pelanggaran berhasil ditolak.');
    }

    /**
     * Report: Rekap pelanggaran per siswa
     */
    public function report(Request $request)
    {
        $query = Student::query();

        // Filter by class if needed
        $kelasId = $request->get('kelas_id', '');
        if ($kelasId != '') {
            $query->where('class_class_id', $kelasId);
        }

        $students = $query->with(['class', 'pelanggaranSiswa' => function($q) {
            $q->where('status', 'approved')->with('pelanggaran');
        }])->get();

        // Calculate total points for each student
        $students->each(function($student) {
            $student->total_point = $student->pelanggaranSiswa->sum(function($item) {
                return $item->pelanggaran->point ?? 0;
            });
            $student->jumlah_pelanggaran = $student->pelanggaranSiswa->count();
        });

        // Sort by highest points
        $students = $students->sortByDesc('total_point');

        // Get all classes for filter
        $classes = \App\Models\ClassModel::orderBy('class_name')->get();

        return view('manage.bk.pelanggaran-siswa.report', compact('students', 'classes', 'kelasId'));
    }

    /**
     * Export Report to PDF
     */
    public function exportPDF(Request $request)
    {
        $query = Student::query();

        // Filter by class if needed
        $kelasId = $request->get('kelas_id', '');
        $kelasName = 'Semua Kelas';
        
        if ($kelasId != '') {
            $query->where('class_class_id', $kelasId);
            $kelas = \App\Models\ClassModel::find($kelasId);
            if ($kelas) {
                $kelasName = $kelas->class_name;
            }
        }

        $students = $query->with(['class', 'pelanggaranSiswa' => function($q) {
            $q->where('status', 'approved')->with('pelanggaran');
        }])->get();

        // Calculate total points for each student
        $students->each(function($student) {
            $student->total_point = $student->pelanggaranSiswa->sum(function($item) {
                return $item->pelanggaran->point ?? 0;
            });
            $student->jumlah_pelanggaran = $student->pelanggaranSiswa->count();
        });

        // Sort by highest points
        $students = $students->sortByDesc('total_point');

        // Get school profile
        $schoolProfile = \App\Models\SchoolProfile::first();

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('manage.bk.pelanggaran-siswa.report-pdf', [
            'students' => $students,
            'kelasName' => $kelasName,
            'tanggal' => now()->format('d F Y'),
            'schoolProfile' => $schoolProfile
        ]);

        $fileName = 'Laporan_Pelanggaran_' . str_replace(' ', '_', $kelasName) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }

    /**
     * Search siswa for Select2 autocomplete
     */
    public function searchSiswa(Request $request)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = Student::query();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('student_nis', 'like', '%' . $search . '%')
                  ->orWhere('student_full_name', 'like', '%' . $search . '%');
            });
        }

        // Load relationship
        $query->with('class');

        $total = $query->count();
        $students = $query->skip(($page - 1) * $perPage)
                          ->take($perPage)
                          ->orderBy('student_full_name')
                          ->get();

        $items = $students->map(function($student) {
            return [
                'id' => $student->student_id,
                'text' => $student->student_nis . ' - ' . $student->student_full_name,
                'nis' => $student->student_nis,
                'name' => $student->student_full_name,
                'class' => $student->class ? $student->class->class_name : null
            ];
        });

        return response()->json([
            'items' => $items,
            'has_more' => ($page * $perPage) < $total
        ]);
    }

    /**
     * Cetak Surat Pernyataan Pelanggaran
     */
    public function cetakSurat($id)
    {
        $pelanggaranSiswa = PelanggaranSiswa::with(['siswa.class', 'pelanggaran.kategori', 'creator'])
            ->findOrFail($id);
        
        $schoolProfile = \App\Models\SchoolProfile::first();
        
        return view('manage.bk.pelanggaran-siswa.cetak-surat', compact('pelanggaranSiswa', 'schoolProfile'));
    }
}
