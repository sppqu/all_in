<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CashflowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Set default date range (current month)
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        // Ambil data kas
        $kasList = DB::table('kas')
            ->where('is_active', 1)
            ->orderBy('nama_kas')
            ->get();
        
        // Ambil data arus kas
        $cashflowData = $this->getCashflowData($startDate, $endDate);
        
        return view('accounting.cashflow.index', compact(
            'kasList',
            'cashflowData',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Get laporan arus kas
     */
    public function laporan(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'kas_id' => 'nullable|exists:kas,id'
        ]);
        
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $kasId = $request->kas_id;
        
        // Ambil data arus kas
        $cashflowData = $this->getCashflowData($startDate, $endDate, $kasId);
        
        return response()->json([
            'success' => true,
            'data' => $cashflowData
        ]);
    }
    
    /**
     * Calculate cashflow data
     */
    private function getCashflowData($startDate, $endDate, $kasId = null)
    {
        $data = [];
        
        // Query kas berdasarkan filter
        $kasQuery = DB::table('kas')->where('is_active', 1);
        if ($kasId) {
            $kasQuery->where('id', $kasId);
        }
        $kasList = $kasQuery->orderBy('nama_kas')->get();
        
        foreach ($kasList as $kas) {
            $kasData = [
                'kas_id' => $kas->id,
                'nama_kas' => $kas->nama_kas,
                'jenis_kas' => $kas->jenis_kas,
                'saldo_awal' => 0,
                'kas_masuk' => [],
                'kas_keluar' => [],
                'total_masuk' => 0,
                'total_keluar' => 0,
                'saldo_akhir' => 0
            ];
            
            // 1. Hitung saldo awal (sebelum tanggal mulai)
            $saldoAwal = $this->getSaldoAwal($kas->id, $startDate);
            $kasData['saldo_awal'] = $saldoAwal;
            
            // 2. Ambil data kas masuk dalam periode
            $kasMasuk = $this->getKasMasuk($kas->id, $startDate, $endDate);
            $kasData['kas_masuk'] = $kasMasuk;
            $kasData['total_masuk'] = array_sum(array_column($kasMasuk, 'jumlah'));
            
            // 3. Ambil data kas keluar dalam periode
            $kasKeluar = $this->getKasKeluar($kas->id, $startDate, $endDate);
            $kasData['kas_keluar'] = $kasKeluar;
            $kasData['total_keluar'] = array_sum(array_column($kasKeluar, 'jumlah'));
            
            // 4. Hitung saldo akhir
            $kasData['saldo_akhir'] = $kasData['saldo_awal'] + $kasData['total_masuk'] - $kasData['total_keluar'];
            
            $data[] = $kasData;
        }
        
        // Jika tidak ada filter kas, transaksi dengan kas_id NULL akan dikelompokkan berdasarkan payment_methods
        // dan ditambahkan ke kas yang sesuai di loop di atas
        
        return $data;
    }
    
    /**
     * Get saldo awal kas sebelum tanggal tertentu
     */
    private function getSaldoAwal($kasId, $tanggal)
    {
        $saldoAwal = 0;
        
        // Saldo awal dari tabel kas
        $kas = DB::table('kas')->where('id', $kasId)->first();
        $saldoAwal = $kas->saldo_awal ?? 0;
        
        // Tambah semua pemasukan sebelum tanggal
        $pemasukan = DB::table('transaksi_penerimaan as tp')
            ->leftJoin('payment_methods as pm', 'tp.metode_pembayaran_id', '=', 'pm.id')
            ->where(function($query) use ($kasId) {
                $query->where('tp.kas_id', $kasId)
                      ->orWhere(function($subQuery) use ($kasId) {
                          $subQuery->whereNull('tp.kas_id')
                                  ->where('pm.kas_id', $kasId);
                      });
            })
            ->where('tp.tanggal_penerimaan', '<', $tanggal)
            ->where('tp.status', 'confirmed')
            ->sum('tp.total_penerimaan');
        
        // Kurangi semua pengeluaran sebelum tanggal
        $pengeluaran = DB::table('transaksi_pengeluaran as tp')
            ->leftJoin('payment_methods as pm', 'tp.metode_pembayaran_id', '=', 'pm.id')
            ->where(function($query) use ($kasId) {
                $query->where('tp.kas_id', $kasId)
                      ->orWhere(function($subQuery) use ($kasId) {
                          $subQuery->whereNull('tp.kas_id')
                                  ->where('pm.kas_id', $kasId);
                      });
            })
            ->where('tp.tanggal_pengeluaran', '<', $tanggal)
            ->where('tp.status', 'confirmed')
            ->sum('tp.total_pengeluaran');
        
        return $saldoAwal + $pemasukan - $pengeluaran;
    }
    
    /**
     * Get kas masuk dalam periode
     */
    private function getKasMasuk($kasId, $startDate, $endDate)
    {
        $kasMasuk = [];
        
        // 1. Dari transaksi penerimaan
        $penerimaan = DB::table('transaksi_penerimaan as tp')
            ->leftJoin('payment_methods as pm', 'tp.metode_pembayaran_id', '=', 'pm.id')
            ->select(
                'tp.tanggal_penerimaan as tanggal',
                'tp.no_transaksi as referensi',
                'tp.diterima_dari as keterangan',
                'tp.total_penerimaan as jumlah',
                'pm.nama_metode as metode',
                DB::raw("'Penerimaan' as jenis")
            )
            ->where(function($query) use ($kasId) {
                // Ambil transaksi dengan kas_id yang sesuai
                $query->where('tp.kas_id', $kasId)
                      // Atau transaksi dengan kas_id NULL yang sesuai dengan payment_method
                      ->orWhere(function($subQuery) use ($kasId) {
                          $subQuery->whereNull('tp.kas_id')
                                  ->where('pm.kas_id', $kasId);
                      });
            })
            ->whereBetween('tp.tanggal_penerimaan', [$startDate, $endDate])
            ->where('tp.status', 'confirmed')
            ->get();
        
        // Gabungkan semua data
        $kasMasuk = $penerimaan->toArray();
        
        // Urutkan berdasarkan tanggal
        usort($kasMasuk, function($a, $b) {
            return strtotime($a->tanggal) - strtotime($b->tanggal);
        });
        
        return array_map(function($item) {
            return (array) $item;
        }, $kasMasuk);
    }
    
    /**
     * Get kas keluar dalam periode
     */
    private function getKasKeluar($kasId, $startDate, $endDate)
    {
        $kasKeluar = [];
        
        // 1. Dari transaksi pengeluaran
        $pengeluaran = DB::table('transaksi_pengeluaran as tp')
            ->leftJoin('payment_methods as pm', 'tp.metode_pembayaran_id', '=', 'pm.id')
            ->select(
                'tp.tanggal_pengeluaran as tanggal',
                'tp.no_transaksi as referensi',
                'tp.dibayar_ke as keterangan',
                'tp.total_pengeluaran as jumlah',
                'pm.nama_metode as metode',
                DB::raw("'Pengeluaran' as jenis")
            )
            ->where(function($query) use ($kasId) {
                // Ambil transaksi dengan kas_id yang sesuai
                $query->where('tp.kas_id', $kasId)
                      // Atau transaksi dengan kas_id NULL yang sesuai dengan payment_method
                      ->orWhere(function($subQuery) use ($kasId) {
                          $subQuery->whereNull('tp.kas_id')
                                  ->where('pm.kas_id', $kasId);
                      });
            })
            ->whereBetween('tp.tanggal_pengeluaran', [$startDate, $endDate])
            ->where('tp.status', 'confirmed')
            ->get();
        
        // Gabungkan semua data
        $kasKeluar = $pengeluaran->toArray();
        
        // Urutkan berdasarkan tanggal
        usort($kasKeluar, function($a, $b) {
            return strtotime($a->tanggal) - strtotime($b->tanggal);
        });
        
        return array_map(function($item) {
            return (array) $item;
        }, $kasKeluar);
    }
    

    
    /**
     * Export laporan arus kas
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'kas_id' => 'nullable|exists:kas,id',
            'format' => 'required|in:pdf,excel'
        ]);
        
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $kasId = $request->kas_id;
        $format = $request->format;
        
        // Ambil data arus kas
        $cashflowData = $this->getCashflowData($startDate, $endDate, $kasId);
        
        // Ambil data sekolah
        $schoolProfile = DB::table('schools')->first();
        
        if ($format == 'pdf') {
            return view('accounting.cashflow.export-pdf', compact(
                'cashflowData',
                'startDate',
                'endDate',
                'schoolProfile'
            ));
        } else {
            // Excel export akan ditambahkan nanti jika diperlukan
            return response()->json([
                'success' => false,
                'message' => 'Format Excel belum tersedia'
            ]);
        }
    }
    
    public function exportExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'kas_id' => 'nullable|exists:kas,id'
        ]);
        
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $kasId = $request->kas_id;
        
        // Ambil data arus kas
        $cashflowData = $this->getCashflowData($startDate, $endDate, $kasId);
        
        // Hitung grand total
        $grandTotal = [
            'saldo_awal' => collect($cashflowData)->sum('saldo_awal'),
            'kas_masuk' => collect($cashflowData)->sum('total_masuk'),
            'kas_keluar' => collect($cashflowData)->sum('total_keluar'),
            'saldo_akhir' => collect($cashflowData)->sum('saldo_akhir')
        ];
        
        // Ambil data sekolah
        $schoolData = DB::table('schools')->first();
        
        return view('accounting.cashflow.export-excel', compact(
            'cashflowData',
            'startDate',
            'endDate',
            'grandTotal',
            'schoolData'
        ));
    }
}
