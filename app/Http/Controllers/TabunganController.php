<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Period;
use App\Models\ClassModel;
use App\Models\SchoolProfile;

class TabunganController extends Controller
{
    /**
     * Menampilkan halaman index tabungan
     */
    public function index(Request $request)
    {
        // Ambil data kelas untuk filter
        $classes = DB::table('class_models')
            ->select('class_id', 'class_name')
            ->orderBy('class_name')
            ->get();

        // Query dasar
        $query = DB::table('tabungan as t')
            ->join('students as s', 't.student_student_id', '=', 's.student_id')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->select(
                't.tabungan_id',
                't.saldo',
                't.tabungan_input_date',
                't.tabungan_last_update',
                's.student_full_name',
                's.student_nis',
                'c.class_name'
            );

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('s.student_nis', 'like', "%{$search}%")
                  ->orWhere('s.student_full_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('c.class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('s.student_status', 1);
            } elseif ($request->status === 'inactive') {
                $query->where('s.student_status', 0);
            }
        }

        $tabungan = $query->orderBy('s.student_full_name')->paginate(15);

        $totalSaldo = DB::table('tabungan')->sum('saldo');
        $totalStudents = DB::table('tabungan')->count();
        $averageSaldo = $totalStudents > 0 ? $totalSaldo / $totalStudents : 0;

        return view('admin.tabungan.index', compact('tabungan', 'totalSaldo', 'totalStudents', 'averageSaldo', 'classes'));
    }

    /**
     * Menampilkan form tambah tabungan
     */
    public function create()
    {
        $students = DB::table('students as s')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->where('s.student_status', 1)
            ->select('s.student_id', 's.student_full_name', 's.student_nis', 'c.class_name')
            ->orderBy('s.student_full_name')
            ->get();

        return view('admin.tabungan.create', compact('students'));
    }

    /**
     * Menyimpan data tabungan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'saldo' => 'required|string',
        ]);

        // Bersihkan input saldo dari separator ribuan
        $saldo = str_replace('.', '', $request->saldo);
        $saldo = (int) $saldo;

        if ($saldo < 0) {
            return back()->withErrors(['saldo' => 'Saldo tidak boleh negatif.']);
        }

        // Cek apakah siswa sudah memiliki tabungan
        $existingTabungan = DB::table('tabungan')
            ->where('student_student_id', $request->student_id)
            ->first();

        if ($existingTabungan) {
            return back()->withErrors(['student_id' => 'Siswa ini sudah memiliki tabungan.']);
        }

        DB::transaction(function () use ($request, $saldo) {
            // Insert tabungan record
            $tabunganId = DB::table('tabungan')->insertGetId([
                'student_student_id' => $request->student_id,
                'user_user_id' => auth()->id(),
                'saldo' => $saldo,
                'tabungan_input_date' => now(),
                'tabungan_last_update' => now(),
            ]);

            // Catat log setoran awal jika saldo > 0
            if ($saldo > 0) {
                DB::table('log_tabungan')->insert([
                    'tabungan_tabungan_id' => $tabunganId,
                    'student_student_id' => $request->student_id,
                    'kredit' => $saldo, // Setoran awal
                    'debit' => 0, // Tidak ada penarikan
                    'saldo' => $saldo,
                    'keterangan' => 'Setoran awal tabungan',
                    'log_tabungan_input_date' => now(),
                    'log_tabungan_last_update' => now(),
                ]);
            }
        });

        return redirect()->route('manage.tabungan.index')->with('success', 'Tabungan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tabungan = DB::table('tabungan as t')
            ->join('students as s', 't.student_student_id', '=', 's.student_id')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->select('t.*', 's.student_nis', 's.student_full_name', 'c.class_name')
            ->where('t.tabungan_id', $id)
            ->first();

        if (!$tabungan) {
            return redirect()->route('manage.tabungan.index')->with('error', 'Tabungan tidak ditemukan.');
        }

        return view('admin.tabungan.show', compact('tabungan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tabungan = DB::table('tabungan as t')
            ->join('students as s', 't.student_student_id', '=', 's.student_id')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->where('t.tabungan_id', $id)
            ->select(
                't.tabungan_id',
                't.saldo',
                't.tabungan_last_update',
                't.student_student_id',
                's.student_full_name',
                's.student_nis',
                'c.class_name'
            )
            ->first();

        if (!$tabungan) {
            return redirect()->route('manage.tabungan.index')->with('error', 'Tabungan tidak ditemukan.');
        }

        return view('admin.tabungan.edit', compact('tabungan'));
    }

    /**
     * Update data tabungan
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'saldo' => 'required|string',
        ]);

        // Bersihkan input saldo dari separator ribuan
        $saldo = str_replace('.', '', $request->saldo);
        $saldo = (int) $saldo;

        if ($saldo < 0) {
            return back()->withErrors(['saldo' => 'Saldo tidak boleh negatif.']);
        }

        DB::table('tabungan')
            ->where('tabungan_id', $id)
            ->update([
                'saldo' => $saldo,
                'tabungan_last_update' => now(),
            ]);

        return redirect()->route('manage.tabungan.index')->with('success', 'Tabungan berhasil diperbarui.');
    }

    /**
     * Hapus data tabungan
     */
    public function destroy($id)
    {
        DB::table('tabungan')->where('tabungan_id', $id)->delete();

        return redirect()->route('manage.tabungan.index')->with('success', 'Tabungan berhasil dihapus.');
    }

    /**
     * Menampilkan form setoran tabungan
     */
    public function setoran($id)
    {
        $tabungan = DB::table('tabungan as t')
            ->join('students as s', 't.student_student_id', '=', 's.student_id')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->where('t.tabungan_id', $id)
            ->select(
                't.tabungan_id',
                't.saldo',
                't.tabungan_last_update',
                't.student_student_id',
                's.student_full_name',
                's.student_nis',
                'c.class_name'
            )
            ->first();

        if (!$tabungan) {
            return redirect()->route('manage.tabungan.index')->with('error', 'Tabungan tidak ditemukan.');
        }

        return view('admin.tabungan.setoran', compact('tabungan'));
    }

    /**
     * Menyimpan setoran tabungan
     */
    public function storeSetoran(Request $request, $id)
    {
        $request->validate([
            'jumlah' => 'required|string',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // Bersihkan input jumlah dari separator ribuan
        $jumlah = str_replace('.', '', $request->jumlah);
        $jumlah = (int) $jumlah;

        if ($jumlah <= 0) {
            return back()->withErrors(['jumlah' => 'Jumlah setoran harus lebih dari 0.']);
        }

        $tabungan = DB::table('tabungan')->where('tabungan_id', $id)->first();
        
        if (!$tabungan) {
            return redirect()->route('manage.tabungan.index')->with('error', 'Tabungan tidak ditemukan.');
        }

        DB::transaction(function () use ($jumlah, $id, $tabungan, $request) {
            $newSaldo = $tabungan->saldo + $jumlah;
            
            // Update saldo tabungan
            DB::table('tabungan')
                ->where('tabungan_id', $id)
                ->update([
                    'saldo' => $newSaldo,
                    'tabungan_last_update' => now(),
                ]);

            // Catat log setoran
            DB::table('log_tabungan')->insert([
                'tabungan_tabungan_id' => $id,
                'student_student_id' => $tabungan->student_student_id,
                'kredit' => $jumlah, // Setoran masuk sebagai kredit
                'debit' => 0, // Tidak ada penarikan
                'saldo' => $newSaldo,
                'keterangan' => $request->keterangan ?? 'Setoran tabungan',
                'log_tabungan_input_date' => now(),
                'log_tabungan_last_update' => now(),
            ]);
        });

        // Kirim notifikasi WhatsApp jika diaktifkan
        try {
            $gateway = DB::table('setup_gateways')->first();
            if ($gateway && $gateway->enable_wa_notification) {
                $whatsappService = new \App\Services\WhatsAppService();
                $whatsappService->sendTabunganCashDepositNotification(
                    $tabungan->student_student_id,
                    $jumlah,
                    $request->keterangan ?? 'Setoran tabungan'
                );
                \Log::info("WhatsApp tabungan cash deposit notification sent for student_id: {$tabungan->student_student_id}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send WhatsApp notification for tabungan cash deposit: " . $e->getMessage());
            // Jangan gagalkan proses setoran jika notifikasi gagal
        }

        return redirect()->route('manage.tabungan.index')->with('success', 'Setoran tabungan berhasil ditambahkan.');
    }

    /**
     * Menampilkan form penarikan tabungan
     */
    public function penarikan($id)
    {
        $tabungan = DB::table('tabungan as t')
            ->join('students as s', 't.student_student_id', '=', 's.student_id')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->where('t.tabungan_id', $id)
            ->select(
                't.tabungan_id',
                't.saldo',
                't.student_student_id',
                's.student_full_name',
                's.student_nis',
                'c.class_name'
            )
            ->first();

        if (!$tabungan) {
            return redirect()->route('manage.tabungan.index')->with('error', 'Tabungan tidak ditemukan.');
        }

        return view('admin.tabungan.penarikan', compact('tabungan'));
    }

    /**
     * Menyimpan penarikan tabungan
     */
    public function storePenarikan(Request $request, $id)
    {
        $request->validate([
            'jumlah' => 'required|string',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // Bersihkan input jumlah dari separator ribuan
        $jumlah = str_replace('.', '', $request->jumlah);
        $jumlah = (int) $jumlah;

        if ($jumlah <= 0) {
            return back()->withErrors(['jumlah' => 'Jumlah penarikan harus lebih dari 0.']);
        }

        $tabungan = DB::table('tabungan')->where('tabungan_id', $id)->first();
        
        if (!$tabungan) {
            return redirect()->route('manage.tabungan.index')->with('error', 'Tabungan tidak ditemukan.');
        }

        if ($jumlah > $tabungan->saldo) {
            return back()->withErrors(['jumlah' => 'Saldo tidak mencukupi untuk penarikan ini.']);
        }

        DB::transaction(function () use ($jumlah, $id, $tabungan, $request) {
            $newSaldo = $tabungan->saldo - $jumlah;
            
            // Update saldo tabungan
            DB::table('tabungan')
                ->where('tabungan_id', $id)
                ->update([
                    'saldo' => $newSaldo,
                    'tabungan_last_update' => now(),
                ]);

            // Catat log penarikan
            DB::table('log_tabungan')->insert([
                'tabungan_tabungan_id' => $id,
                'student_student_id' => $tabungan->student_student_id,
                'kredit' => $jumlah,
                'debit' => $jumlah,
                'saldo' => $newSaldo,
                'keterangan' => $request->keterangan ?? 'Penarikan tabungan',
                'log_tabungan_input_date' => now(),
                'log_tabungan_last_update' => now(),
            ]);
        });

        // Kirim notifikasi WhatsApp jika diaktifkan
        try {
            $gateway = DB::table('setup_gateways')->first();
            if ($gateway && $gateway->enable_wa_notification) {
                $whatsappService = new \App\Services\WhatsAppService();
                $whatsappService->sendTabunganCashWithdrawalNotification(
                    $tabungan->student_student_id,
                    $jumlah,
                    $request->keterangan ?? 'Penarikan tabungan'
                );
                \Log::info("WhatsApp tabungan cash withdrawal notification sent for student_id: {$tabungan->student_student_id}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send WhatsApp notification for tabungan cash withdrawal: " . $e->getMessage());
            // Jangan gagalkan proses penarikan jika notifikasi gagal
        }

        return redirect()->route('manage.tabungan.index')->with('success', 'Penarikan tabungan berhasil diproses.');
    }

    /**
     * Menampilkan riwayat transaksi tabungan
     */
    public function riwayat($id)
    {
        $tabungan = DB::table('tabungan as t')
            ->join('students as s', 't.student_student_id', '=', 's.student_id')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->where('t.tabungan_id', $id)
            ->select(
                't.tabungan_id',
                't.saldo',
                't.student_student_id',
                's.student_full_name',
                's.student_nis',
                'c.class_name'
            )
            ->first();

        if (!$tabungan) {
            return redirect()->route('manage.tabungan.index')->with('error', 'Tabungan tidak ditemukan.');
        }

        $riwayat = DB::table('log_tabungan')
            ->where('tabungan_tabungan_id', $id)
            ->orderBy('log_tabungan_input_date', 'desc')
            ->get();

        return view('admin.tabungan.riwayat', compact('tabungan', 'riwayat'));
    }

    /**
     * Export mutasi tabungan ke PDF
     */
    public function exportMutasi($id)
    {
        $tabungan = DB::table('tabungan as t')
            ->join('students as s', 't.student_student_id', '=', 's.student_id')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->where('t.tabungan_id', $id)
            ->select(
                't.tabungan_id',
                't.saldo',
                't.student_student_id',
                's.student_full_name',
                's.student_nis',
                'c.class_name'
            )
            ->first();

        if (!$tabungan) {
            return redirect()->route('manage.tabungan.index')->with('error', 'Tabungan tidak ditemukan.');
        }

        $riwayat = DB::table('log_tabungan')
            ->where('tabungan_tabungan_id', $id)
            ->orderBy('log_tabungan_input_date', 'desc')
            ->get();

        // Ambil identitas sekolah
        $school = \App\Models\SchoolProfile::first();

        // Generate PDF menggunakan DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.tabungan.mutasi-pdf', compact('tabungan', 'riwayat', 'school'));
        
        $filename = 'Mutasi_Tabungan_' . $tabungan->student_nis . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Cetak kuitansi tabungan
     */
    public function cetakKuitansi($id, Request $request)
    {
        $tabungan = DB::table('tabungan as t')
            ->join('students as s', 't.student_student_id', '=', 's.student_id')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->where('t.tabungan_id', $id)
            ->select(
                't.tabungan_id',
                't.saldo',
                't.student_student_id',
                's.student_full_name',
                's.student_nis',
                'c.class_name'
            )
            ->first();

        if (!$tabungan) {
            return redirect()->route('manage.tabungan.index')->with('error', 'Tabungan tidak ditemukan.');
        }

        // Ambil parameter dari request
        $tanggalCetak = $request->get('tanggal_cetak', date('Y-m-d'));
        $jenisKuitansi = $request->get('jenis_kuitansi', 'semua');
        $periodeAwal = $request->get('periode_awal', date('Y-m-01'));
        $periodeAkhir = $request->get('periode_akhir', date('Y-m-d'));

        // Query untuk transaksi berdasarkan filter
        $query = DB::table('log_tabungan')
            ->where('tabungan_tabungan_id', $id)
            ->whereBetween('log_tabungan_input_date', [$periodeAwal . ' 00:00:00', $periodeAkhir . ' 23:59:59']);

        // Filter berdasarkan jenis kuitansi
        if ($jenisKuitansi === 'setoran') {
            $query->where('kredit', '>', 0);
        } elseif ($jenisKuitansi === 'penarikan') {
            $query->where('debit', '>', 0);
        }

        $transaksi = $query->orderBy('log_tabungan_input_date', 'desc')->get();

        // Hitung total
        $totalSetoran = $transaksi->where('kredit', '>', 0)->sum('kredit');
        $totalPenarikan = $transaksi->where('debit', '>', 0)->sum('debit');
        $saldoAkhir = $totalSetoran - $totalPenarikan;

        // Ambil identitas sekolah
        $school = \App\Models\SchoolProfile::first();

        // Data untuk view
        $data = [
            'tabungan' => $tabungan,
            'transaksi' => $transaksi,
            'tanggalCetak' => $tanggalCetak,
            'jenisKuitansi' => $jenisKuitansi,
            'periodeAwal' => $periodeAwal,
            'periodeAkhir' => $periodeAkhir,
            'totalSetoran' => $totalSetoran,
            'totalPenarikan' => $totalPenarikan,
            'saldoAkhir' => $saldoAkhir,
            'school' => $school
        ];

        // Generate PDF menggunakan DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.tabungan.kuitansi-pdf', $data);
        
        $filename = 'Kuitansi_Tabungan_' . $tabungan->student_nis . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->stream($filename);
    }

} 