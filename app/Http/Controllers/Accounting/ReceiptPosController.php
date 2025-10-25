<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiptPosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil dari tabel pos_pembayaran (default dari NAMA POS Pembayaran)
        $receiptPos = DB::table('pos_pembayaran')
            ->select('pos_id', 'pos_name', 'pos_description')
            ->orderBy('pos_name')
            ->get();
        
        // Ambil metode pembayaran untuk dropdown
        $paymentMethods = DB::table('payment_methods')
            ->where('status', 'ON')
            ->orderBy('nama_metode')
            ->get();
        
        // Ambil data transaksi penerimaan untuk ditampilkan di list
        $transaksiList = DB::table('transaksi_penerimaan as tp')
            ->leftJoin('payment_methods as pm', 'tp.metode_pembayaran_id', '=', 'pm.id')
            ->leftJoin('kas as k', 'tp.kas_id', '=', 'k.id')
            ->select(
                'tp.id',
                'tp.no_transaksi',
                'tp.tanggal_penerimaan',
                'tp.diterima_dari',
                'tp.keterangan_transaksi',
                'tp.total_penerimaan',
                'tp.operator',
                'tp.tahun_ajaran',
                'tp.kas_id',
                'pm.nama_metode as cara_transaksi',
                'k.nama_kas as kas_name'
            )
            ->orderBy('tp.tanggal_penerimaan', 'desc')
            ->get();
        
        // Ambil data profil sekolah untuk header kuitansi
        $schoolProfile = DB::table('school_profiles')->first();
        
        // Ambil data kas untuk dropdown
        $kasList = DB::table('kas')
            ->where('is_active', 1)
            ->orderBy('nama_kas')
            ->get();
        
        return view('accounting.receipt-pos.index', compact('receiptPos', 'paymentMethods', 'transaksiList', 'schoolProfile', 'kasList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.receipt-pos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log request data untuk debugging
        Log::info('ReceiptPos store request:', $request->all());
        
        // Validation yang fleksibel untuk 2 jenis input
        if ($request->has('nama_pos_baru')) {
            // Dari modal "Buat Pos Penerimaan Baru"
            $request->validate([
                'nama_pos_baru' => 'required|string|max:100',
                'keterangan_pos_baru' => 'nullable|string|max:100',
                'status_pos_baru' => 'required|in:ON,OFF'
            ]);
        } else {
            // Dari modal tambah pos biasa
            $request->validate([
                'pos_name' => 'required|string|max:100',
                'pos_description' => 'nullable|string|max:100'
            ]);
        }

        try {
            $insertData = [];
            
            // Jika ada nama_pos_baru, berarti dari modal "Buat Pos Penerimaan Baru"
            if ($request->has('nama_pos_baru')) {
                $insertData = [
                    'pos_name' => $request->nama_pos_baru,
                    'pos_description' => $request->keterangan_pos_baru
                ];
            } else {
                // Jika tidak ada, berarti dari modal tambah pos biasa
                $insertData = [
                    'pos_name' => $request->pos_name,
                    'pos_description' => $request->pos_description
                ];
            }
            
            // Log data yang akan diinsert
            Log::info('Inserting receipt pos data:', $insertData);
            
            $inserted = DB::table('pos_pembayaran')->insert($insertData);
            
            // Log hasil insert
            Log::info('Insert result:', ['success' => $inserted]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pos penerimaan berhasil ditambahkan!'
                ]);
            }

            return redirect()->route('manage.accounting.receipt-pos.index')
                ->with('success', 'Pos penerimaan berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error creating receipt pos: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan pos penerimaan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pos penerimaan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $receiptPos = DB::table('pos_pembayaran')->where('pos_id', $id)->first();
        
        if (!$receiptPos) {
            return redirect()->route('manage.accounting.receipt-pos.index')
                ->with('error', 'Pos penerimaan tidak ditemukan!');
        }

        return view('accounting.receipt-pos.edit', compact('receiptPos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'pos_name' => 'required|string|max:100',
            'pos_description' => 'nullable|string|max:100'
        ]);

        try {
            $updated = DB::table('pos_pembayaran')->where('pos_id', $id)->update([
                'pos_name' => $request->pos_name,
                'pos_description' => $request->pos_description
            ]);

            if (!$updated) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pos penerimaan tidak ditemukan atau tidak ada perubahan!'
                    ], 404);
                }
                
                return redirect()->route('manage.accounting.receipt-pos.index')
                    ->with('error', 'Pos penerimaan tidak ditemukan atau tidak ada perubahan!');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pos penerimaan berhasil diupdate!'
                ]);
            }

            return redirect()->route('manage.accounting.receipt-pos.index')
                ->with('success', 'Pos penerimaan berhasil diupdate!');
        } catch (\Exception $e) {
            Log::error('Error updating receipt pos: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate pos penerimaan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal mengupdate pos penerimaan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $transaksi = DB::table('transaksi_penerimaan as tp')
                ->leftJoin('payment_methods as pm', 'tp.metode_pembayaran_id', '=', 'pm.id')
                ->leftJoin('kas as k', 'tp.kas_id', '=', 'k.id')
                ->select(
                    'tp.*',
                    'pm.nama_metode as cara_transaksi',
                    'k.nama_kas as kas_name'
                )
                ->where('tp.id', $id)
                ->first();
            
            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan!'
                ], 404);
            }
            
            // Ambil detail transaksi
            $details = DB::table('transaksi_penerimaan_detail as tpd')
                ->leftJoin('pos_pembayaran as pp', 'tpd.pos_penerimaan_id', '=', 'pp.pos_id')
                ->select(
                    'tpd.id',
                    'tpd.transaksi_id',
                    'tpd.pos_penerimaan_id',
                    'tpd.keterangan_item',
                    'tpd.jumlah',
                    'pp.pos_name as pos_name',
                    'pp.pos_name as pos' // untuk backward compatibility
                )
                ->where('tpd.transaksi_id', $id)
                ->get();
            
            // Log data untuk debugging
            Log::info('Transaksi details for ID ' . $id . ':', $details->toArray());
            
            $transaksi->details = $details;
            
            // Log response untuk debugging
            $response = [
                'success' => true,
                'transaksi' => $transaksi
            ];
            Log::info('Show transaksi response:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error showing transaksi: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store transaksi penerimaan baru
     */
    public function storeTransaksi(Request $request)
    {
        // Log semua data yang diterima
        Log::info('StoreTransaksi request data:', $request->all());
        
        $request->validate([
            'tanggal_penerimaan' => 'required|date',
            'tahun_ajaran' => 'required|string|max:20',
            'diterima_dari' => 'required|string|max:100',
            'metode_pembayaran' => 'required|exists:payment_methods,id',
            'kas_id' => 'required|exists:kas,id',
            'keterangan_transaksi' => 'nullable|string',
            'pos_penerimaan' => 'required|array',
            'pos_penerimaan.*' => 'required|exists:pos_pembayaran,pos_id',
            'keterangan_item' => 'required|array',
            'keterangan_item.*' => 'required|string',
            'jumlah_penerimaan' => 'required|array',
            'jumlah_penerimaan.*' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();
            
            // Generate nomor transaksi
            $tahun = date('Y', strtotime($request->tanggal_penerimaan));
            $bulan = date('m', strtotime($request->tanggal_penerimaan));
            $lastTransaksi = DB::table('transaksi_penerimaan')
                ->whereYear('tanggal_penerimaan', $tahun)
                ->whereMonth('tanggal_penerimaan', $bulan)
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastTransaksi ? intval(substr($lastTransaksi->no_transaksi, -6)) + 1 : 1;
            $noTransaksi = 'TRM' . $tahun . $bulan . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            
            // Hitung total penerimaan
            $totalPenerimaan = array_sum($request->jumlah_penerimaan);
            
            // Log perhitungan total
            Log::info('Total calculation:', [
                'jumlah_penerimaan_array' => $request->jumlah_penerimaan,
                'total_calculated' => $totalPenerimaan
            ]);
            
            // Insert transaksi utama
            $transaksiId = DB::table('transaksi_penerimaan')->insertGetId([
                'no_transaksi' => $noTransaksi,
                'tanggal_penerimaan' => $request->tanggal_penerimaan,
                'tahun_ajaran' => $request->tahun_ajaran,
                'diterima_dari' => $request->diterima_dari,
                'metode_pembayaran_id' => $request->metode_pembayaran,
                'kas_id' => $request->kas_id,
                'keterangan_transaksi' => $request->keterangan_transaksi,
                'operator' => auth()->user()->name . ' - ' . (auth()->user()->role ?? 'User'),
                'total_penerimaan' => $totalPenerimaan,
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Insert detail transaksi
            $detailCount = 0;
            foreach ($request->pos_penerimaan as $index => $posId) {
                if (!empty($request->jumlah_penerimaan[$index]) && $request->jumlah_penerimaan[$index] > 0) {
                    $detailData = [
                        'transaksi_id' => $transaksiId,
                        'pos_penerimaan_id' => $posId,
                        'keterangan_item' => $request->keterangan_item[$index],
                        'jumlah' => $request->jumlah_penerimaan[$index],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    DB::table('transaksi_penerimaan_detail')->insert($detailData);
                    $detailCount++;
                    
                    // Log setiap detail yang diinsert
                    Log::info("Detail transaksi {$detailCount} inserted:", $detailData);
                }
            }
            
            Log::info("Total detail inserted: {$detailCount}");
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi penerimaan berhasil disimpan!',
                'no_transaksi' => $noTransaksi
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing transaksi penerimaan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi penerimaan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Cek apakah pos penerimaan masih digunakan
            $usedInBulan = DB::table('bulan')->where('pos_pembayaran_pos_id', $id)->exists();
            $usedInBebas = DB::table('bebas')->where('pos_pembayaran_pos_id', $id)->exists();
            $usedInTransaksi = DB::table('transaksi_penerimaan')->where('pos_id', $id)->exists();

            if ($usedInBulan || $usedInBebas || $usedInTransaksi) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pos penerimaan tidak dapat dihapus karena masih digunakan dalam transaksi!'
                    ], 422);
                }
                
                return redirect()->route('manage.accounting.receipt-pos.index')
                    ->with('error', 'Pos penerimaan tidak dapat dihapus karena masih digunakan dalam transaksi!');
            }

            $deleted = DB::table('pos_pembayaran')->where('pos_id', $id)->delete();
            
            if (!$deleted) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pos penerimaan tidak ditemukan atau sudah dihapus!'
                    ], 404);
                }
                
                return redirect()->route('manage.accounting.receipt-pos.index')
                    ->with('error', 'Pos penerimaan tidak ditemukan atau sudah dihapus!');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pos penerimaan berhasil dihapus!'
                ]);
            }

            return redirect()->route('manage.accounting.receipt-pos.index')
                ->with('success', 'Pos penerimaan berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting receipt pos: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus pos penerimaan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal menghapus pos penerimaan: ' . $e->getMessage());
        }
    }

    /**
     * Delete transaksi penerimaan
     */
    public function destroyTransaksi(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            // Hapus detail transaksi terlebih dahulu (cascade)
            DB::table('transaksi_penerimaan_detail')->where('transaksi_id', $id)->delete();
            
            // Hapus transaksi utama
            $deleted = DB::table('transaksi_penerimaan')->where('id', $id)->delete();
            
            if (!$deleted) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan atau sudah dihapus!'
                ], 404);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi penerimaan berhasil dihapus!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting transaksi penerimaan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi penerimaan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan halaman pos view dengan filter tanggal dan pendapatan per pos
     * Data diambil dari semua sumber: pembayaran bulanan, bebas, dan transaksi penerimaan lain
     */
    public function posView(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01')); // Default awal bulan
        $endDate = $request->get('end_date', date('Y-m-d')); // Default hari ini
        
        // Ambil semua pos penerimaan
        $receiptPos = DB::table('pos_pembayaran')
            ->select('pos_id', 'pos_name', 'pos_description')
            ->orderBy('pos_name', 'asc')
            ->get();
        
        // Hitung pendapatan per pos dalam range tanggal dari semua sumber
        $posIncomes = [];
        foreach ($receiptPos as $pos) {
            $totalIncome = 0;
            $transactionCount = 0;
            $incomeDetails = [];
            
            // 1. Pendapatan dari Transaksi Penerimaan Lain
            $transaksiIncome = DB::table('transaksi_penerimaan_detail as tpd')
                ->join('transaksi_penerimaan as tp', 'tpd.transaksi_id', '=', 'tp.id')
                ->where('tpd.pos_penerimaan_id', $pos->pos_id)
                ->whereBetween('tp.tanggal_penerimaan', [$startDate, $endDate])
                ->where('tp.status', 'confirmed')
                ->sum('tpd.jumlah');
            
            $transaksiCount = DB::table('transaksi_penerimaan_detail as tpd')
                ->join('transaksi_penerimaan as tp', 'tpd.transaksi_id', '=', 'tp.id')
                ->where('tpd.pos_penerimaan_id', $pos->pos_id)
                ->whereBetween('tp.tanggal_penerimaan', [$startDate, $endDate])
                ->where('tp.status', 'confirmed')
                ->count();
            
            if ($transaksiIncome > 0) {
                $incomeDetails[] = [
                    'source' => 'Transaksi Penerimaan Lain',
                    'amount' => $transaksiIncome,
                    'count' => $transaksiCount
                ];
                $totalIncome += $transaksiIncome;
                $transactionCount += $transaksiCount;
            }
            
            // 2. Pendapatan dari Pembayaran Bulanan (SPP)
            $sppIncome = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->whereBetween('b.bulan_date_pay', [$startDate, $endDate])
                ->where('b.bulan_status', 1) // 1 = confirmed/paid
                ->sum('b.bulan_bill');
            
            $sppCount = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->whereBetween('b.bulan_date_pay', [$startDate, $endDate])
                ->where('b.bulan_status', 1) // 1 = confirmed/paid
                ->count();
            
            if ($sppIncome > 0) {
                $incomeDetails[] = [
                    'source' => 'Pembayaran Bulanan (SPP)',
                    'amount' => $sppIncome,
                    'count' => $sppCount
                ];
                $totalIncome += $sppIncome;
                $transactionCount += $sppCount;
            }
            
            // 3. Pendapatan dari Pembayaran Bebas
            $bebasIncome = DB::table('bebas_pay as bp')
                ->join('bebas as b', 'bp.bebas_bebas_id', '=', 'b.bebas_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->whereBetween('bp.bebas_pay_input_date', [$startDate, $endDate])
                ->sum('bp.bebas_pay_bill');
            
            $bebasCount = DB::table('bebas_pay as bp')
                ->join('bebas as b', 'bp.bebas_bebas_id', '=', 'b.bebas_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->whereBetween('bp.bebas_pay_input_date', [$startDate, $endDate])
                ->count();
            
            if ($bebasIncome > 0) {
                $incomeDetails[] = [
                    'source' => 'Pembayaran Bebas',
                    'amount' => $bebasIncome,
                    'count' => $bebasCount
                ];
                $totalIncome += $bebasIncome;
                $transactionCount += $bebasCount;
            }
            
            // 4. Pendapatan dari Transfer (jika ada)
            // Note: Tabel transfer tidak memiliki pos_id langsung, 
            // jadi kita skip bagian ini untuk sementara
            // $transferIncome = 0;
            // $transferCount = 0;
            
            $posIncomes[] = [
                'pos_id' => $pos->pos_id,
                'pos_name' => $pos->pos_name,
                'pos_description' => $pos->pos_description,
                'total_income' => $totalIncome,
                'transaction_count' => $transactionCount,
                'income_details' => $incomeDetails
            ];
        }
        
        // Urutkan berdasarkan pendapatan terbesar
        usort($posIncomes, function($a, $b) {
            return $b['total_income'] <=> $a['total_income'];
        });
        
        // Ambil data profil sekolah untuk header cetak
        $schoolProfile = DB::table('school_profiles')->first();
        
        return view('accounting.pos-view.index', compact('posIncomes', 'startDate', 'endDate', 'schoolProfile'));
    }

    /**
     * Update transaksi penerimaan
     */
    public function updateTransaksi(Request $request, $id)
    {
        // Log request data untuk debugging
        Log::info('UpdateTransaksi request data: ' . json_encode($request->all()));
        Log::info('UpdateTransaksi ID: ' . $id);
        
        $request->validate([
            'tanggal_penerimaan' => 'required|date',
            'tahun_ajaran' => 'required|string|max:20',
            'diterima_dari' => 'required|string|max:100',
            'metode_pembayaran' => 'required|exists:payment_methods,id',
            'kas_id' => 'required|exists:kas,id',
            'keterangan_transaksi' => 'nullable|string',
            'pos_penerimaan' => 'required|array',
            'pos_penerimaan.*' => 'required|exists:pos_pembayaran,pos_id',
            'keterangan_item' => 'required|array',
            'keterangan_item.*' => 'required|string',
            'jumlah_penerimaan' => 'required|array',
            'jumlah_penerimaan.*' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();
            
            Log::info('Starting update transaction for ID: ' . $id);
            
            // Update transaksi utama
            $updateResult = DB::table('transaksi_penerimaan')
                ->where('id', $id)
                ->update([
                    'tanggal_penerimaan' => $request->tanggal_penerimaan,
                    'tahun_ajaran' => $request->tahun_ajaran,
                    'diterima_dari' => $request->diterima_dari,
                    'metode_pembayaran_id' => $request->metode_pembayaran,
                    'kas_id' => $request->kas_id,
                    'keterangan_transaksi' => $request->keterangan_transaksi,
                    'updated_at' => now()
                ]);
            
            Log::info('Update transaksi utama result: ' . $updateResult);
            
            // Hapus detail lama
            $deleteResult = DB::table('transaksi_penerimaan_detail')->where('transaksi_id', $id)->delete();
            Log::info('Delete detail lama result: ' . $deleteResult);
            
            // Insert detail baru
            $details = [];
            foreach ($request->pos_penerimaan as $index => $posId) {
                $details[] = [
                    'transaksi_id' => $id,
                    'pos_penerimaan_id' => $posId,
                    'keterangan_item' => $request->keterangan_item[$index],
                    'jumlah' => $request->jumlah_penerimaan[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            Log::info('Detail baru yang akan diinsert: ' . json_encode($details));
            
            $insertResult = DB::table('transaksi_penerimaan_detail')->insert($details);
            Log::info('Insert detail baru result: ' . $insertResult);
            
            // Update total penerimaan
            $totalPenerimaan = array_sum($request->jumlah_penerimaan);
            $updateTotalResult = DB::table('transaksi_penerimaan')
                ->where('id', $id)
                ->update(['total_penerimaan' => $totalPenerimaan]);
            
            Log::info('Update total penerimaan result: ' . $updateTotalResult . ', Total: ' . $totalPenerimaan);
            
            DB::commit();
            Log::info('Transaction committed successfully');
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi penerimaan berhasil diupdate!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating transaksi: ' . $e->getMessage());
            Log::error('Error stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
