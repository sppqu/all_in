<?php

namespace App\Http\Controllers\Jurnal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JurnalHarian;
use App\Models\JurnalKategori;
use App\Models\Student;
use App\Models\ClassModel;
use Illuminate\Support\Facades\Auth;

class JurnalGuruController extends Controller
{
    /**
     * Dashboard guru - monitoring semua jurnal
     */
    public function index(Request $request)
    {
        $query = JurnalHarian::with(['siswa.class', 'entries']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by class
        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('class_class_id', $request->kelas_id);
            });
        }

        // Filter by student ID
        if ($request->has('siswa_id') && $request->siswa_id != '') {
            $query->where('siswa_id', $request->siswa_id);
        }

        // Filter by date range
        if ($request->has('tanggal_dari')) {
            $query->where('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->has('tanggal_sampai')) {
            $query->where('tanggal', '<=', $request->tanggal_sampai);
        }

        $jurnals = $query->orderBy('tanggal', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total_jurnal' => JurnalHarian::count(),
            'pending_verifikasi' => JurnalHarian::where('status', 'submitted')->count(),
            'terverifikasi' => JurnalHarian::where('status', 'verified')->count(),
            'draft' => JurnalHarian::where('status', 'draft')->count(),
        ];

        $classes = ClassModel::orderBy('class_name')->get();
        $students = Student::with('class')
            ->where('student_status', 1)
            ->orderBy('student_full_name')
            ->get();

        return view('jurnal.guru.index', compact('jurnals', 'stats', 'classes', 'students'));
    }

    /**
     * View detail jurnal siswa
     */
    public function show($id)
    {
        $jurnal = JurnalHarian::with(['siswa.class', 'entries.kategori', 'verifiedBy'])
            ->findOrFail($id);

        return view('jurnal.guru.show', compact('jurnal'));
    }

    /**
     * Verify jurnal
     */
    public function verify(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_guru' => 'nullable|string',
        ]);

        $jurnal = JurnalHarian::findOrFail($id);
        $jurnal->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'catatan_guru' => $validated['catatan_guru'],
        ]);

        return redirect()->back()
            ->with('success', 'Jurnal berhasil diverifikasi!');
    }

    /**
     * Request revision
     */
    public function requestRevision(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_guru' => 'required|string',
        ]);

        $jurnal = JurnalHarian::findOrFail($id);
        $jurnal->update([
            'status' => 'revised',
            'catatan_guru' => $validated['catatan_guru'],
        ]);

        return redirect()->back()
            ->with('success', 'Permintaan revisi berhasil dikirim ke siswa!');
    }

    /**
     * Edit jurnal
     */
    public function edit($id)
    {
        $jurnal = JurnalHarian::with(['siswa.class', 'entries.kategori'])
            ->findOrFail($id);
        
        $kategori = JurnalKategori::active()->orderBy('urutan')->get();
        
        return view('jurnal.guru.edit', compact('jurnal', 'kategori'));
    }

    /**
     * Update jurnal
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'catatan_umum' => 'nullable|string',
            'foto' => 'nullable|image|max:2048',
            'kategori' => 'required|array|min:7',
            'kategori.*.jam' => 'nullable|date_format:H:i',
            'kategori.*.checklist' => 'nullable|array',
            'kategori.*.keterangan' => 'nullable|string|max:1000',
        ]);

        $jurnal = JurnalHarian::findOrFail($id);

        // Handle foto upload
        $fotoPath = $jurnal->foto;
        if ($request->hasFile('foto')) {
            // Delete old foto
            if ($fotoPath && \Storage::disk('public')->exists($fotoPath)) {
                \Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto')->store('jurnal', 'public');
        }

        // Update jurnal
        $jurnal->update([
            'tanggal' => $validated['tanggal'],
            'catatan_umum' => $validated['catatan_umum'],
            'foto' => $fotoPath,
        ]);

        // Delete existing entries
        $jurnal->entries()->delete();

        // Create new entries
        foreach ($validated['kategori'] as $kategoriId => $data) {
            \App\Models\JurnalEntry::create([
                'jurnal_id' => $jurnal->jurnal_id,
                'kategori_id' => $kategoriId,
                'jam' => $data['jam'] ?? null,
                'checklist_data' => isset($data['checklist']) ? json_encode($data['checklist']) : null,
                'keterangan' => $data['keterangan'] ?? null,
            ]);
        }

        return redirect()->route('jurnal.guru.show', $jurnal->jurnal_id)
            ->with('success', 'Jurnal berhasil diperbarui!');
    }

    /**
     * Laporan Per Siswa
     */
    public function laporanSiswa(Request $request)
    {
        $students = Student::with('class')
            ->where('student_status', 1)
            ->orderBy('student_full_name')
            ->get();
        
        $classes = ClassModel::orderBy('class_name')->get();
        $kategori = JurnalKategori::active()->orderBy('urutan')->get();
        
        $laporan = null;
        $siswa = null;
        
        if ($request->has('siswa_id') && $request->siswa_id != '') {
            $siswa = Student::with('class')->findOrFail($request->siswa_id);
            
            $query = JurnalHarian::with(['entries.kategori'])
                ->where('siswa_id', $request->siswa_id);
            
            if ($request->has('tanggal_dari')) {
                $query->where('tanggal', '>=', $request->tanggal_dari);
            }
            if ($request->has('tanggal_sampai')) {
                $query->where('tanggal', '<=', $request->tanggal_sampai);
            }
            
            $laporan = $query->orderBy('tanggal', 'desc')->get();
        }
        
        return view('jurnal.guru.laporan-siswa', compact('students', 'classes', 'kategori', 'laporan', 'siswa'));
    }

    /**
     * Laporan Per Siswa PDF
     */
    public function laporanSiswaPdf(Request $request)
    {
        if (!$request->has('siswa_id') || $request->siswa_id == '') {
            return redirect()->route('jurnal.guru.laporan-siswa')
                ->with('error', 'Silakan pilih siswa terlebih dahulu');
        }

        $siswa = Student::with('class')->findOrFail($request->siswa_id);
        $kategori = JurnalKategori::active()->orderBy('urutan')->get();
        $schoolProfile = currentSchool() ?? \App\Models\School::first();
        
        $query = JurnalHarian::with(['entries.kategori'])
            ->where('siswa_id', $request->siswa_id);
        
        if ($request->has('tanggal_dari')) {
            $query->where('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->has('tanggal_sampai')) {
            $query->where('tanggal', '<=', $request->tanggal_sampai);
        }
        
        $laporan = $query->orderBy('tanggal', 'asc')->get();
        
        // Group by kategori
        $dataPerKategori = [];
        foreach ($kategori as $kat) {
            $dataPerKategori[$kat->kategori_id] = [
                'kategori' => $kat,
                'entries' => []
            ];
        }
        
        // Populate entries
        foreach ($laporan as $jurnal) {
            foreach ($jurnal->entries as $entry) {
                $dataPerKategori[$entry->kategori_id]['entries'][] = [
                    'tanggal' => $jurnal->tanggal,
                    'entry' => $entry,
                    'jurnal' => $jurnal
                ];
            }
        }
        
        $pdf = \PDF::loadView('jurnal.guru.laporan-siswa-pdf', compact('siswa', 'kategori', 'laporan', 'dataPerKategori', 'schoolProfile'));
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'Jurnal_7KAIH_' . $siswa->student_nis . '_' . $siswa->student_full_name . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->stream($filename);
    }

    /**
     * Laporan Per Kelas
     */
    public function laporanKelas(Request $request)
    {
        $classes = ClassModel::orderBy('class_name')->get();
        $kategori = JurnalKategori::active()->orderBy('urutan')->get();
        
        $laporan = null;
        $kelas = null;
        
        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $kelas = ClassModel::findOrFail($request->kelas_id);
            
            // Get all students in class
            $students = Student::where('class_class_id', $request->kelas_id)
                ->where('student_status', 1)
                ->orderBy('student_full_name')
                ->get();
            
            $laporan = [];
            foreach ($students as $siswa) {
                $query = JurnalHarian::with(['entries.kategori'])
                    ->where('siswa_id', $siswa->student_id);
                
                if ($request->has('tanggal_dari')) {
                    $query->where('tanggal', '>=', $request->tanggal_dari);
                }
                if ($request->has('tanggal_sampai')) {
                    $query->where('tanggal', '<=', $request->tanggal_sampai);
                }
                
                $jurnals = $query->orderBy('tanggal', 'desc')->get();
                
                // Count per kategori
                $countPerKategori = [];
                foreach ($kategori as $kat) {
                    $countPerKategori[$kat->kategori_id] = 0;
                }
                
                foreach ($jurnals as $jurnal) {
                    foreach ($jurnal->entries as $entry) {
                        if (isset($countPerKategori[$entry->kategori_id])) {
                            $countPerKategori[$entry->kategori_id]++;
                        }
                    }
                }
                
                $laporan[] = [
                    'siswa' => $siswa,
                    'jurnals' => $jurnals,
                    'total_jurnal' => $jurnals->count(),
                    'verified' => $jurnals->where('status', 'verified')->count(),
                    'pending' => $jurnals->where('status', 'submitted')->count(),
                    'count_per_kategori' => $countPerKategori,
                ];
            }
        }
        
        return view('jurnal.guru.laporan-kelas', compact('classes', 'kategori', 'laporan', 'kelas'));
    }

    /**
     * Laporan Per Kelas PDF
     */
    public function laporanKelasPdf(Request $request)
    {
        if (!$request->has('kelas_id') || $request->kelas_id == '') {
            return redirect()->route('jurnal.guru.laporan-kelas')
                ->with('error', 'Silakan pilih kelas terlebih dahulu');
        }

        $kelas = ClassModel::findOrFail($request->kelas_id);
        $kategori = JurnalKategori::active()->orderBy('urutan')->get();
        $schoolProfile = currentSchool() ?? \App\Models\School::first();
        
        // Get all students in class
        $students = Student::where('class_class_id', $request->kelas_id)
            ->where('student_status', 1)
            ->orderBy('student_full_name')
            ->get();
        
        $laporan = [];
        foreach ($students as $siswa) {
            $query = JurnalHarian::with(['entries.kategori'])
                ->where('siswa_id', $siswa->student_id);
            
            if ($request->has('tanggal_dari')) {
                $query->where('tanggal', '>=', $request->tanggal_dari);
            }
            if ($request->has('tanggal_sampai')) {
                $query->where('tanggal', '<=', $request->tanggal_sampai);
            }
            
            $jurnals = $query->orderBy('tanggal', 'desc')->get();
            
            // Count per kategori
            $countPerKategori = [];
            foreach ($kategori as $kat) {
                $countPerKategori[$kat->kategori_id] = 0;
            }
            
            foreach ($jurnals as $jurnal) {
                foreach ($jurnal->entries as $entry) {
                    if (isset($countPerKategori[$entry->kategori_id])) {
                        $countPerKategori[$entry->kategori_id]++;
                    }
                }
            }
            
            $laporan[] = [
                'siswa' => $siswa,
                'jurnals' => $jurnals,
                'total_jurnal' => $jurnals->count(),
                'verified' => $jurnals->where('status', 'verified')->count(),
                'pending' => $jurnals->where('status', 'submitted')->count(),
                'count_per_kategori' => $countPerKategori,
            ];
        }
        
        $pdf = \PDF::loadView('jurnal.guru.laporan-kelas-pdf', compact('kelas', 'kategori', 'laporan', 'schoolProfile'));
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'Jurnal_7KAIH_Kelas_' . str_replace(' ', '_', $kelas->class_name) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->stream($filename);
    }

    /**
     * Rekap per siswa
     */
    public function rekapPerSiswa($siswa_id, Request $request)
    {
        $siswa = Student::with('class')->findOrFail($siswa_id);
        
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        // Get all jurnals for the month
        $jurnals = JurnalHarian::with(['entries.kategori'])
            ->where('siswa_id', $siswa_id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal')
            ->get();

        // Group by kategori
        $kategori = JurnalKategori::active()->get();
        
        $rekapPerKategori = [];
        foreach ($kategori as $kat) {
            $nilaiList = [];
            foreach ($jurnals as $jurnal) {
                $entry = $jurnal->entries->where('kategori_id', $kat->kategori_id)->first();
                if ($entry) {
                    $nilaiList[] = $entry->nilai;
                }
            }
            
            $rekapPerKategori[$kat->kategori_id] = [
                'kategori' => $kat,
                'total_entry' => count($nilaiList),
                'rata_rata' => count($nilaiList) > 0 ? round(array_sum($nilaiList) / count($nilaiList), 2) : 0,
                'total_nilai' => array_sum($nilaiList),
                'nilai_list' => $nilaiList,
            ];
        }

        return view('jurnal.guru.rekap-siswa', compact('siswa', 'jurnals', 'rekapPerKategori', 'kategori', 'month', 'year'));
    }

    /**
     * Rekap per kelas
     */
    public function rekapPerKelas($kelas_id, Request $request)
    {
        $kelas = ClassModel::findOrFail($kelas_id);
        
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        // Get all students in class
        $students = Student::where('class_class_id', $kelas_id)->get();

        $rekapSiswa = [];
        foreach ($students as $siswa) {
            $jurnals = JurnalHarian::with('entries')
                ->where('siswa_id', $siswa->student_id)
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->get();

            $totalNilai = 0;
            $totalEntry = 0;
            foreach ($jurnals as $jurnal) {
                $totalNilai += $jurnal->entries->sum('nilai');
                $totalEntry += $jurnal->entries->count();
            }

            $rekapSiswa[] = [
                'siswa' => $siswa,
                'total_jurnal' => $jurnals->count(),
                'total_nilai' => $totalNilai,
                'rata_rata' => $totalEntry > 0 ? round($totalNilai / $totalEntry, 2) : 0,
            ];
        }

        // Sort by rata-rata desc
        usort($rekapSiswa, function($a, $b) {
            return $b['rata_rata'] <=> $a['rata_rata'];
        });

        return view('jurnal.guru.rekap-kelas', compact('kelas', 'rekapSiswa', 'month', 'year'));
    }
}

