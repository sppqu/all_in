<?php

namespace App\Http\Controllers\Jurnal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JurnalHarian;
use App\Models\JurnalKategori;
use App\Models\JurnalEntry;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JurnalSiswaController extends Controller
{
    /**
     * Dashboard jurnal siswa
     */
    public function index()
    {
        // Untuk siswa yang login (nanti akan diimplementasikan sistem login siswa)
        // Sementara ambil dari user yang login
        
        // Get recent jurnals
        $recentJurnals = JurnalHarian::with(['entries.kategori'])
            ->orderBy('tanggal', 'desc')
            ->take(10)
            ->get();

        // Get statistics
        $stats = [
            'total_jurnal' => JurnalHarian::count(),
            'jurnal_bulan_ini' => JurnalHarian::whereMonth('tanggal', date('m'))
                ->whereYear('tanggal', date('Y'))
                ->count(),
            'jurnal_terverifikasi' => JurnalHarian::where('status', 'verified')->count(),
            'rata_rata_nilai' => JurnalEntry::avg('nilai') ?? 0,
        ];

        return view('jurnal.siswa.index', compact('recentJurnals', 'stats'));
    }

    /**
     * Show form untuk create jurnal baru
     */
    public function create(Request $request)
    {
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        
        // Check if jurnal already exists for this date
        $existingJurnal = JurnalHarian::where('tanggal', $tanggal)->first();
        
        if ($existingJurnal) {
            return redirect()->route('jurnal.siswa.edit', $existingJurnal->jurnal_id)
                ->with('info', 'Jurnal untuk tanggal ini sudah ada. Silakan edit.');
        }

        $kategori = JurnalKategori::active()->get();
        
        return view('jurnal.siswa.create', compact('tanggal', 'kategori'));
    }

    /**
     * Store jurnal baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'catatan_umum' => 'nullable|string',
            'refleksi' => 'nullable|string',
            'status' => 'required|in:draft,submitted',
        ]);

        // Get siswa_id (sementara ambil siswa pertama, nanti dari auth)
        $siswa = Student::first();
        $validated['siswa_id'] = $siswa->student_id;

        // Create jurnal
        $jurnal = JurnalHarian::create($validated);

        // Process entries
        if ($request->has('entries')) {
            foreach ($request->entries as $kategori_id => $entryData) {
                if (!empty($entryData['kegiatan'])) {
                    $entry = [
                        'jurnal_id' => $jurnal->jurnal_id,
                        'kategori_id' => $kategori_id,
                        'kegiatan' => $entryData['kegiatan'],
                        'nilai' => $entryData['nilai'] ?? 0,
                        'keterangan' => $entryData['keterangan'] ?? null,
                        'waktu_mulai' => $entryData['waktu_mulai'] ?? null,
                        'waktu_selesai' => $entryData['waktu_selesai'] ?? null,
                    ];

                    // Handle foto upload if exists
                    if (isset($entryData['foto']) && $entryData['foto']) {
                        $foto = $entryData['foto'];
                        $path = $foto->store('jurnal/foto', 'public');
                        $entry['foto'] = $path;
                    }

                    JurnalEntry::create($entry);
                }
            }
        }

        $message = $request->status == 'submitted' 
            ? 'Jurnal berhasil disimpan dan dikirim untuk verifikasi!' 
            : 'Jurnal berhasil disimpan sebagai draft!';

        return redirect()->route('jurnal.siswa.index')
            ->with('success', $message);
    }

    /**
     * Show jurnal detail
     */
    public function show($id)
    {
        $jurnal = JurnalHarian::with(['siswa', 'entries.kategori', 'verifiedBy'])
            ->findOrFail($id);

        return view('jurnal.siswa.show', compact('jurnal'));
    }

    /**
     * Edit jurnal
     */
    public function edit($id)
    {
        $jurnal = JurnalHarian::with('entries')->findOrFail($id);
        
        // Only allow edit if not verified
        if ($jurnal->status == 'verified') {
            return redirect()->route('jurnal.siswa.show', $id)
                ->with('warning', 'Jurnal yang sudah diverifikasi tidak dapat diedit.');
        }

        $kategori = JurnalKategori::active()->get();
        
        return view('jurnal.siswa.edit', compact('jurnal', 'kategori'));
    }

    /**
     * Update jurnal
     */
    public function update(Request $request, $id)
    {
        $jurnal = JurnalHarian::findOrFail($id);

        // Only allow update if not verified
        if ($jurnal->status == 'verified') {
            return redirect()->route('jurnal.siswa.show', $id)
                ->with('error', 'Jurnal yang sudah diverifikasi tidak dapat diedit.');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'catatan_umum' => 'nullable|string',
            'refleksi' => 'nullable|string',
            'status' => 'required|in:draft,submitted',
        ]);

        $jurnal->update($validated);

        // Delete old entries
        JurnalEntry::where('jurnal_id', $jurnal->jurnal_id)->delete();

        // Process new entries
        if ($request->has('entries')) {
            foreach ($request->entries as $kategori_id => $entryData) {
                if (!empty($entryData['kegiatan'])) {
                    $entry = [
                        'jurnal_id' => $jurnal->jurnal_id,
                        'kategori_id' => $kategori_id,
                        'kegiatan' => $entryData['kegiatan'],
                        'nilai' => $entryData['nilai'] ?? 0,
                        'keterangan' => $entryData['keterangan'] ?? null,
                        'waktu_mulai' => $entryData['waktu_mulai'] ?? null,
                        'waktu_selesai' => $entryData['waktu_selesai'] ?? null,
                    ];

                    // Handle foto upload if exists
                    if (isset($entryData['foto']) && $entryData['foto']) {
                        $foto = $entryData['foto'];
                        $path = $foto->store('jurnal/foto', 'public');
                        $entry['foto'] = $path;
                    }

                    JurnalEntry::create($entry);
                }
            }
        }

        return redirect()->route('jurnal.siswa.index')
            ->with('success', 'Jurnal berhasil diperbarui!');
    }

    /**
     * Delete jurnal
     */
    public function destroy($id)
    {
        $jurnal = JurnalHarian::findOrFail($id);

        // Only allow delete if draft
        if ($jurnal->status != 'draft') {
            return redirect()->back()
                ->with('error', 'Hanya jurnal draft yang dapat dihapus.');
        }

        // Delete associated photos
        foreach ($jurnal->entries as $entry) {
            if ($entry->foto) {
                Storage::disk('public')->delete($entry->foto);
            }
        }

        $jurnal->delete();

        return redirect()->route('jurnal.siswa.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }

    /**
     * Rekap bulanan
     */
    public function rekapBulanan(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        // Get all jurnals for the month
        $jurnals = JurnalHarian::with(['entries.kategori'])
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

        return view('jurnal.siswa.rekap-bulanan', compact('jurnals', 'rekapPerKategori', 'kategori', 'month', 'year'));
    }
}

