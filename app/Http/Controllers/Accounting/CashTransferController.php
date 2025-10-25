<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $startDate = request()->get('start_date', date('Y-m-01'));
        $endDate = request()->get('end_date', date('Y-m-d'));
        
        // Ambil data kas untuk dropdown
        $kasList = DB::table('kas')
            ->orderBy('nama_kas')
            ->get();
        
        // Ambil data transfer kas
        $transfers = DB::table('cash_transfers as ct')
            ->leftJoin('kas as kas_asal', 'ct.kas_asal_id', '=', 'kas_asal.id')
            ->leftJoin('kas as kas_tujuan', 'ct.kas_tujuan_id', '=', 'kas_tujuan.id')
            ->leftJoin('users as u', 'ct.petugas_id', '=', 'u.id')
            ->select(
                'ct.id',
                'ct.tanggal_transfer',
                'ct.no_transaksi',
                'ct.keterangan',
                'ct.jumlah_transfer',
                'ct.nama_penyetor',
                'ct.nama_penerima',
                'ct.kas_asal_id',
                'ct.kas_tujuan_id',
                'kas_asal.nama_kas as kas_asal_nama',
                'kas_tujuan.nama_kas as kas_tujuan_nama',
                'u.name as petugas'
            )
            ->whereBetween('ct.tanggal_transfer', [$startDate, $endDate])
            ->orderBy('ct.tanggal_transfer', 'desc')
            ->get();
        
        return view('accounting.cash-transfer.index', compact('kasList', 'transfers', 'startDate', 'endDate'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.cash-transfer.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_transfer' => 'required|date',
            'kas_asal_id' => 'required|exists:kas,id',
            'kas_tujuan_id' => 'required|exists:kas,id|different:kas_asal_id',
            'jumlah_transfer' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'nama_penyetor' => 'nullable|string|max:100',
            'nama_penerima' => 'nullable|string|max:100'
        ]);

        try {
            DB::beginTransaction();
            
            // Generate no transaksi
            $today = new \DateTime();
            $noTransaksi = 'TRF-' . $today->format('ymd') . '-001';
            
            // Cek apakah kas asal memiliki saldo cukup
            $saldoKasAsal = DB::table('kas')
                ->where('id', $request->kas_asal_id)
                ->value('saldo') ?? 0;
            
            if ($saldoKasAsal < $request->jumlah_transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo kas asal tidak mencukupi untuk transfer ini!'
                ], 422);
            }
            
            // Insert transfer
            $transferId = DB::table('cash_transfers')->insertGetId([
                'tanggal_transfer' => $request->tanggal_transfer,
                'no_transaksi' => $noTransaksi,
                'kas_asal_id' => $request->kas_asal_id,
                'kas_tujuan_id' => $request->kas_tujuan_id,
                'jumlah_transfer' => $request->jumlah_transfer,
                'keterangan' => $request->keterangan,
                'nama_penyetor' => $request->nama_penyetor,
                'nama_penerima' => $request->nama_penerima,
                'petugas_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update saldo kas asal (kurangi)
            DB::table('kas')
                ->where('id', $request->kas_asal_id)
                ->decrement('saldo', $request->jumlah_transfer);
            
            // Update saldo kas tujuan (tambah)
            DB::table('kas')
                ->where('id', $request->kas_tujuan_id)
                ->increment('saldo', $request->jumlah_transfer);
            
            // Insert ke debit (kas tujuan)
            DB::table('debit')->insert([
                'tanggal' => $request->tanggal_transfer,
                'kas_id' => $request->kas_tujuan_id,
                'pos_id' => null, // Transfer antar kas
                'keterangan' => 'Transfer dari ' . $request->keterangan,
                'jumlah' => $request->jumlah_transfer,
                'operator' => auth()->user()->name,
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Insert ke kredit (kas asal)
            DB::table('kredit')->insert([
                'tanggal' => $request->tanggal_transfer,
                'kas_id' => $request->kas_asal_id,
                'pos_id' => null, // Transfer antar kas
                'keterangan' => 'Transfer ke ' . $request->keterangan,
                'jumlah' => $request->jumlah_transfer,
                'operator' => auth()->user()->name,
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transfer kas berhasil dilakukan!',
                    'no_transaksi' => $noTransaksi
                ]);
            }

            return redirect()->route('manage.accounting.cash-transfer.index')
                ->with('success', 'Transfer kas berhasil dilakukan!');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating cash transfer: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal melakukan transfer kas: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal melakukan transfer kas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $transfer = DB::table('cash_transfers as ct')
            ->leftJoin('kas as kas_asal', 'ct.kas_asal_id', '=', 'kas_asal.id')
            ->leftJoin('kas as kas_tujuan', 'ct.kas_tujuan_id', '=', 'kas_tujuan.id')
            ->leftJoin('users as u', 'ct.petugas_id', '=', 'u.id')
            ->select(
                'ct.*',
                'kas_asal.nama_kas as kas_asal_nama',
                'kas_tujuan.nama_kas as kas_tujuan_nama',
                'u.name as petugas'
            )
            ->where('ct.id', $id)
            ->first();
        
        if (!$transfer) {
            return response()->json([
                'success' => false,
                'message' => 'Data transfer tidak ditemukan!'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $transfer
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('accounting.cash-transfer.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur edit transfer belum diimplementasikan!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // Ambil data transfer
            $transfer = DB::table('cash_transfers')->where('id', $id)->first();
            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data transfer tidak ditemukan!'
                ], 404);
            }
            
            // Kembalikan saldo kas asal
            DB::table('kas')
                ->where('id', $transfer->kas_asal_id)
                ->increment('saldo', $transfer->jumlah_transfer);
            
            // Kurangi saldo kas tujuan
            DB::table('kas')
                ->where('id', $transfer->kas_tujuan_id)
                ->decrement('saldo', $transfer->jumlah_transfer);
            
            // Hapus record debit dan kredit yang terkait
            DB::table('debit')
                ->where('tanggal', $transfer->tanggal_transfer)
                ->where('keterangan', 'LIKE', '%Transfer%')
                ->where('jumlah', $transfer->jumlah_transfer)
                ->delete();
            
            DB::table('kredit')
                ->where('tanggal', $transfer->tanggal_transfer)
                ->where('keterangan', 'LIKE', '%Transfer%')
                ->where('jumlah', $transfer->jumlah_transfer)
                ->delete();
            
            // Hapus transfer
            DB::table('cash_transfers')->where('id', $id)->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Transfer kas berhasil dihapus!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting cash transfer: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transfer kas: ' . $e->getMessage()
            ], 500);
        }
    }
}
