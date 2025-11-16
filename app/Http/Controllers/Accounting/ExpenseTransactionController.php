<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ExpenseTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $startDate = request()->get('start_date', date('Y-m-01'));
        $endDate = request()->get('end_date', date('Y-m-d'));
        
        // Ambil data pos pengeluaran untuk dropdown
        $expensePos = DB::table('pos_pengeluaran')
            ->where('is_active', 1)
            ->orderBy('pos_name')
            ->get();
        
        // Ambil data pos penerimaan untuk dropdown sumber dana
        // Filter berdasarkan school_id
        $currentSchoolId = currentSchoolId();
        $receiptPosQuery = DB::table('pos_pembayaran')
            ->select('pos_id', 'pos_name', 'pos_description', 'school_id');
        
        if ($currentSchoolId) {
            $receiptPosQuery->where(function($q) use ($currentSchoolId) {
                $q->where('school_id', $currentSchoolId)
                  ->orWhereNull('school_id'); // Backward compatibility untuk data lama
            });
        } else {
            // Jika tidak ada school_id, hanya ambil yang school_id NULL
            $receiptPosQuery->whereNull('school_id');
        }
        
        $receiptPos = $receiptPosQuery->orderBy('pos_name')->get();
            
        // Debug: Force check data
        \Log::info('CONTROLLER DEBUG - receiptPos count: ' . $receiptPos->count());
        if ($receiptPos->count() > 0) {
            \Log::info('CONTROLLER DEBUG - First item: ' . json_encode($receiptPos->first()));
        }
        
        // Debug: Pastikan data tersedia
        if ($receiptPos->isEmpty()) {
            Log::warning('Tidak ada data pos_pembayaran yang tersedia');
        } else {
            Log::info('Data pos_pembayaran tersedia: ' . $receiptPos->count() . ' items');
            // Debug: Log beberapa data pertama
            foreach ($receiptPos->take(3) as $pos) {
                Log::info("ReceiptPos: ID={$pos->pos_id}, Name={$pos->pos_name}");
            }
        }
        
        // Debug expensePos juga
        if ($expensePos->isEmpty()) {
            Log::warning('Tidak ada data pos_pengeluaran yang tersedia');
        } else {
            Log::info('Data pos_pengeluaran tersedia: ' . $expensePos->count() . ' items');
            foreach ($expensePos->take(3) as $pos) {
                Log::info("ExpensePos: ID={$pos->pos_id}, Name={$pos->pos_name}, Type={$pos->pos_type}");
            }
        }
        
        // Ambil data metode pembayaran untuk dropdown
        $paymentMethods = DB::table('payment_methods')
            ->where('status', 'ON')
            ->orderBy('nama_metode')
            ->get();
        
        // Ambil data kas untuk dropdown
        // Tampilkan semua kas (aktif dan tidak aktif) untuk memastikan semua muncul
        $kasList = DB::table('kas')
            ->orderBy('nama_kas')
            ->get();
        
        // Debug: Log jumlah kas
        Log::info('ExpenseTransaction Kas List Count: ' . $kasList->count());
        if ($kasList->count() > 0) {
            Log::info('ExpenseTransaction Kas List: ' . json_encode($kasList->pluck('nama_kas')->toArray()));
        }
        
        // Ambil data transaksi pengeluaran dari tabel baru (hanya transaksi utama, bukan detail)
        $transactions = DB::table('transaksi_pengeluaran as tp')
            ->select(
                'tp.id',
                'tp.tanggal_pengeluaran as tanggal',
                'tp.no_transaksi',
                'tp.keterangan_transaksi as keterangan',
                'tp.total_pengeluaran as jumlah_pengeluaran',
                'tp.operator',
                'tp.status',
                'tp.created_at'
            )
            ->whereBetween('tp.tanggal_pengeluaran', [$startDate, $endDate])
            ->orderBy('tp.tanggal_pengeluaran', 'desc')
            ->get();
        
        // Pastikan semua variabel tersedia
        $receiptPos = $receiptPos ?? collect();
        $paymentMethods = $paymentMethods ?? collect();
        $expensePos = $expensePos ?? collect();
        $kasList = $kasList ?? collect();
        $transactions = $transactions ?? collect();
        
        // Debug: Pastikan variabel yang dikirim ke view
        \Log::info('CONTROLLER DEBUG - Variables sent to view:');
        \Log::info('expensePos: ' . $expensePos->count());
        \Log::info('receiptPos: ' . $receiptPos->count());
        \Log::info('paymentMethods: ' . $paymentMethods->count());
        \Log::info('kasList: ' . $kasList->count());
        \Log::info('transactions: ' . $transactions->count());
        
        // Jika request AJAX, return JSON
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'transactions' => $transactions,
                'expensePos' => $expensePos,
                'receiptPos' => $receiptPos,
                'paymentMethods' => $paymentMethods,
                'kasList' => $kasList,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
        }
        
        return view('accounting.expense-pos.index', compact('expensePos', 'receiptPos', 'paymentMethods', 'kasList', 'transactions', 'startDate', 'endDate'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_pengeluaran' => 'required|date',
            'no_transaksi' => 'required|string|max:50|unique:transaksi_pengeluaran,no_transaksi',
            'tahun_ajaran' => 'required|string|max:20',
            'dibayar_ke' => 'required|string|max:100',
            'metode_pembayaran_id' => 'required|exists:payment_methods,id',
            'kas_id' => 'required|exists:kas,id',
            'keterangan_transaksi' => 'nullable|string|max:500',
            'pos_sumber_dana' => 'required|array',
            'pos_sumber_dana.*' => 'required|exists:pos_pembayaran,pos_id',
            'pos_pengeluaran' => 'required|array',
            'pos_pengeluaran.*' => 'required|exists:pos_pengeluaran,pos_id',
            'keterangan_item' => 'required|array',
            'keterangan_item.*' => 'required|string',
            'jumlah_pengeluaran' => 'required|array',
            'jumlah_pengeluaran.*' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();
            
            // Insert transaksi pengeluaran
            $transactionId = DB::table('transaksi_pengeluaran')->insertGetId([
                'no_transaksi' => $request->no_transaksi,
                'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
                'tahun_ajaran' => $request->tahun_ajaran,
                'dibayar_ke' => $request->dibayar_ke,
                'metode_pembayaran_id' => $request->metode_pembayaran_id,
                'kas_id' => $request->kas_id,
                'keterangan_transaksi' => $request->keterangan_transaksi,
                'operator' => auth()->user()->name ?? 'Admin',
                'total_pengeluaran' => array_sum($request->jumlah_pengeluaran),
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Insert detail transaksi
            foreach ($request->pos_sumber_dana as $index => $posSumberDana) {
                DB::table('transaksi_pengeluaran_detail')->insert([
                    'transaksi_id' => $transactionId,
                    'pos_sumber_dana_id' => $posSumberDana,
                    'pos_pengeluaran_id' => $request->pos_pengeluaran[$index],
                    'keterangan_item' => $request->keterangan_item[$index],
                    'jumlah' => $request->jumlah_pengeluaran[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Update saldo kas (kurangi saldo)
            $totalPengeluaran = array_sum($request->jumlah_pengeluaran);
            DB::table('kas')
                ->where('id', $request->kas_id)
                ->decrement('saldo', $totalPengeluaran);
            
            Log::info('Kas saldo updated (expense)', [
                'kas_id' => $request->kas_id,
                'total_pengeluaran' => $totalPengeluaran,
                'new_saldo' => DB::table('kas')->where('id', $request->kas_id)->value('saldo')
            ]);
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi pengeluaran berhasil ditambahkan!',
                    'data' => ['id' => $transactionId]
                ]);
            }
            
            return redirect()->back()->with('success', 'Transaksi pengeluaran berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating expense transaction: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan transaksi pengeluaran: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal menambahkan transaksi pengeluaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_pengeluaran' => 'required|date',
            'tahun_ajaran' => 'required|string|max:20',
            'metode_pembayaran_id' => 'required|exists:payment_methods,id',
            'dibayar_ke' => 'required|string|max:100',
            'kas_id' => 'required|exists:kas,id',
            'keterangan_transaksi' => 'nullable|string|max:500',
            'pos_sumber_dana' => 'required|array',
            'pos_sumber_dana.*' => 'required|exists:pos_pembayaran,pos_id',
            'pos_pengeluaran' => 'required|array',
            'pos_pengeluaran.*' => 'required|exists:pos_pengeluaran,pos_id',
            'keterangan_item' => 'required|array',
            'keterangan_item.*' => 'required|string',
            'jumlah_pengeluaran' => 'required|array',
            'jumlah_pengeluaran.*' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();
            
            // Get old transaction data
            $oldTransaction = DB::table('transaksi_pengeluaran')->where('id', $id)->first();
            if (!$oldTransaction) {
                throw new \Exception('Transaksi tidak ditemukan');
            }
            
            // Calculate total pengeluaran
            $totalPengeluaran = array_sum($request->jumlah_pengeluaran);
            
            // Update transaksi pengeluaran utama
            DB::table('transaksi_pengeluaran')
                ->where('id', $id)
                ->update([
                    'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
                    'tahun_ajaran' => $request->tahun_ajaran,
                    'dibayar_ke' => $request->dibayar_ke,
                    'metode_pembayaran_id' => $request->metode_pembayaran_id,
                    'kas_id' => $request->kas_id,
                    'keterangan_transaksi' => $request->keterangan_transaksi,
                    'total_pengeluaran' => $totalPengeluaran,
                    'updated_at' => now()
                ]);
            
            // Delete old detail items
            DB::table('transaksi_pengeluaran_detail')->where('transaksi_id', $id)->delete();
            
            // Insert new detail items
            foreach ($request->pos_sumber_dana as $index => $posSumberDana) {
                DB::table('transaksi_pengeluaran_detail')->insert([
                    'transaksi_id' => $id,
                    'pos_sumber_dana_id' => $posSumberDana,
                    'pos_pengeluaran_id' => $request->pos_pengeluaran[$index],
                    'keterangan_item' => $request->keterangan_item[$index],
                    'jumlah' => $request->jumlah_pengeluaran[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Update saldo kas
            $oldTotalPengeluaran = (float) ($oldTransaction->total_pengeluaran ?? 0);
            $oldKasId = $oldTransaction->kas_id;
            $newKasId = $request->kas_id;
            
            // Jika kas_id berubah, kembalikan saldo kas lama dan kurangi saldo kas baru
            if ($oldKasId != $newKasId) {
                // Kembalikan saldo kas lama (tambah karena pengeluaran dibatalkan)
                DB::table('kas')
                    ->where('id', $oldKasId)
                    ->increment('saldo', $oldTotalPengeluaran);
                
                // Kurangi saldo kas baru
                DB::table('kas')
                    ->where('id', $newKasId)
                    ->decrement('saldo', $totalPengeluaran);
            } else {
                // Jika kas_id sama, cukup selisihkan total
                $selisih = $totalPengeluaran - $oldTotalPengeluaran;
                if ($selisih != 0) {
                    if ($selisih > 0) {
                        DB::table('kas')
                            ->where('id', $newKasId)
                            ->decrement('saldo', $selisih);
                    } else {
                        DB::table('kas')
                            ->where('id', $newKasId)
                            ->increment('saldo', abs($selisih));
                    }
                }
            }
            
            Log::info('Kas saldo updated (expense update)', [
                'old_kas_id' => $oldKasId,
                'new_kas_id' => $newKasId,
                'old_total' => $oldTotalPengeluaran,
                'new_total' => $totalPengeluaran,
                'selisih' => $totalPengeluaran - $oldTotalPengeluaran
            ]);
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi pengeluaran berhasil diupdate!'
                ]);
            }
            
            return redirect()->back()->with('success', 'Transaksi pengeluaran berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating expense transaction: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate transaksi pengeluaran: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal mengupdate transaksi pengeluaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // Get transaction data dari tabel baru
            $transaction = DB::table('transaksi_pengeluaran')->where('id', $id)->first();
            if (!$transaction) {
                throw new \Exception('Transaksi tidak ditemukan');
            }
            
            // Delete detail transaksi terlebih dahulu (foreign key constraint)
            DB::table('transaksi_pengeluaran_detail')->where('transaksi_id', $id)->delete();
            
            // Delete transaksi pengeluaran utama
            DB::table('transaksi_pengeluaran')->where('id', $id)->delete();
            
            // Update saldo kas (tambah kembali saldo karena transaksi pengeluaran dihapus)
            if ($transaction->kas_id && $transaction->total_pengeluaran) {
                DB::table('kas')
                    ->where('id', $transaction->kas_id)
                    ->increment('saldo', (float) $transaction->total_pengeluaran);
                
                Log::info('Kas saldo updated (expense delete)', [
                    'kas_id' => $transaction->kas_id,
                    'total_pengeluaran' => $transaction->total_pengeluaran
                ]);
            }
            
            DB::commit();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi pengeluaran berhasil dihapus!'
                ]);
            }
            
            return redirect()->back()->with('success', 'Transaksi pengeluaran berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting expense transaction: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus transaksi pengeluaran: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal menghapus transaksi pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Get transaction data for editing
     */
    public function show($id)
    {
        $transaction = DB::table('expense_transactions as et')
            ->leftJoin('expense_pos as ep', 'et.pos_pengeluaran_id', '=', 'ep.id')
            ->select(
                'et.id',
                'et.tanggal',
                'et.no_transaksi',
                'et.keterangan',
                'et.jumlah_pengeluaran',
                'et.pos_pengeluaran_id',
                'ep.pos_name as pos_name'
            )
            ->where('et.id', $id)
            ->first();
        
        if (!$transaction) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }
            
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan');
        }
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);
        }
        
        return view('accounting.expense-pos.show', compact('transaction'));
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            // Ambil transaksi utama
            $transaction = DB::table('transaksi_pengeluaran')
                ->where('id', $id)
                ->first();
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }
            
            // Ambil detail items
            $details = DB::table('transaksi_pengeluaran_detail as tpd')
                ->leftJoin('pos_pembayaran as ps', 'tpd.pos_sumber_dana_id', '=', 'ps.pos_id')
                ->leftJoin('pos_pengeluaran as pp', 'tpd.pos_pengeluaran_id', '=', 'pp.pos_id')
                ->select(
                    'tpd.id',
                    'tpd.pos_sumber_dana_id',
                    'tpd.pos_pengeluaran_id',
                    'tpd.keterangan_item',
                    'tpd.jumlah',
                    'ps.pos_name as pos_sumber_dana_name',
                    'pp.pos_name as pos_pengeluaran_name'
                )
                ->where('tpd.transaksi_id', $id)
                ->get();
            
            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'details' => $details
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting transaction for edit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan data transaksi untuk edit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction details with line items
     */
    public function getTransactionDetails($id)
    {
        try {
            // Ambil transaksi utama
            $transaction = DB::table('transaksi_pengeluaran')
                ->where('id', $id)
                ->first();
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }
            
            // Ambil detail items
            $details = DB::table('transaksi_pengeluaran_detail as tpd')
                ->leftJoin('pos_pembayaran as ps', 'tpd.pos_sumber_dana_id', '=', 'ps.pos_id')
                ->leftJoin('pos_pengeluaran as pp', 'tpd.pos_pengeluaran_id', '=', 'pp.pos_id')
                ->select(
                    'tpd.id',
                    'tpd.pos_sumber_dana_id',
                    'tpd.pos_pengeluaran_id',
                    'tpd.keterangan_item',
                    'tpd.jumlah',
                    'ps.pos_name as pos_sumber_dana_name',
                    'pp.pos_name as pos_pengeluaran_name'
                )
                ->where('tpd.transaksi_id', $id)
                ->get();
            
            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'details' => $details
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting transaction details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan detail transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Print the specified expense transaction receipt
     */
    public function print($id)
    {
        try {
            // Ambil transaksi utama
            $transaction = DB::table('transaksi_pengeluaran')
                ->where('id', $id)
                ->first();
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }
            
            // Ambil detail items
            $details = DB::table('transaksi_pengeluaran_detail as tpd')
                ->leftJoin('pos_pembayaran as ps', 'tpd.pos_sumber_dana_id', '=', 'ps.pos_id')
                ->leftJoin('pos_pengeluaran as pp', 'tpd.pos_pengeluaran_id', '=', 'pp.pos_id')
                ->select(
                    'tpd.id',
                    'tpd.pos_sumber_dana_id',
                    'tpd.pos_pengeluaran_id',
                    'tpd.keterangan_item',
                    'tpd.jumlah',
                    'ps.pos_name as pos_sumber_dana_name',
                    'pp.pos_name as pos_pengeluaran_name'
                )
                ->where('tpd.transaksi_id', $id)
                ->get();
            
            // Ambil data sekolah dari general settings
            $schoolProfile = DB::table('schools')->first();
            
            // Ambil data kas
            $kasData = DB::table('kas')
                ->where('id', $transaction->kas_id)
                ->first();
            
            // Ambil data metode pembayaran
            $paymentMethod = DB::table('payment_methods')
                ->where('id', $transaction->metode_pembayaran_id)
                ->first();
            
            return view('accounting.expense-pos.print', compact(
                'transaction', 
                'details', 
                'schoolProfile', 
                'kasData', 
                'paymentMethod'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error printing expense transaction: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencetak bukti pengeluaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get next transaction number for today
     */
    public function getNextNumber()
    {
        try {
            $today = date('Y-m-d');
            $baseNo = 'EXP-' . date('Ymd');
            
            // Cek nomor transaksi terakhir untuk hari ini
            $lastTransaction = DB::table('transaksi_pengeluaran')
                ->where('no_transaksi', 'like', $baseNo . '%')
                ->orderBy('no_transaksi', 'desc')
                ->first();
            
            if ($lastTransaction) {
                // Extract nomor urut dari nomor transaksi terakhir
                $lastNumber = $lastTransaction->no_transaksi;
                $lastSequence = (int) substr($lastNumber, -3); // Ambil 3 digit terakhir
                $nextSequence = $lastSequence + 1;
            } else {
                $nextSequence = 1;
            }
            
            // Format: EXPYYYYMMDDXXX (tanpa tanda -)
            $nextNumber = $baseNo . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
            
            return response()->json([
                'success' => true,
                'next_number' => $nextNumber,
                'base_no' => $baseNo,
                'sequence' => $nextSequence
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting next transaction number: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan nomor transaksi berikutnya: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new expense position
     */
    public function storeExpensePos(Request $request)
    {
        $request->validate([
            'nama_pos_pengeluaran_baru' => 'required|string|max:100',
            'keterangan_pos_pengeluaran_baru' => 'nullable|string|max:100',
            'status_pos_pengeluaran_baru' => 'required|in:0,1'
        ]);

        try {
            $posId = DB::table('pos_pengeluaran')->insertGetId([
                'pos_name' => $request->nama_pos_pengeluaran_baru,
                'pos_description' => $request->keterangan_pos_pengeluaran_baru,
                'pos_type' => 'operasional', // Default type
                'is_active' => $request->status_pos_pengeluaran_baru,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pos pengeluaran baru berhasil dibuat!',
                'pos_id' => $posId
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating expense pos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pos pengeluaran baru: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all expense positions
     */
    public function getExpensePos()
    {
        try {
            Log::info('getExpensePos method called');
            
            $posPengeluaran = DB::table('pos_pengeluaran')
                ->orderBy('pos_name')
                ->get();

            Log::info('Found ' . $posPengeluaran->count() . ' expense positions');

            return response()->json([
                'success' => true,
                'pos_pengeluaran' => $posPengeluaran
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting expense pos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pos pengeluaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expense position detail
     */
    public function getExpensePosDetail($id)
    {
        try {
            $pos = DB::table('pos_pengeluaran')
                ->where('pos_id', $id)
                ->first();

            if (!$pos) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pos pengeluaran tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'pos' => $pos
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting expense pos detail: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pos pengeluaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update expense position
     */
    public function updateExpensePos(Request $request, $id)
    {
        // Log incoming request data
        Log::info('updateExpensePos called with data:', [
            'id' => $id,
            'request_data' => $request->all(),
            'method' => $request->method()
        ]);

        // Validasi input
        try {
            $validated = $request->validate([
                'nama_pos' => 'required|string|max:100',
                'keterangan_pos' => 'nullable|string|max:100',
                'edit_status_pos' => 'required|in:0,1'
            ]);
            
            Log::info('Validation passed:', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $updated = DB::table('pos_pengeluaran')
                ->where('pos_id', $id)
                ->update([
                    'pos_name' => $validated['nama_pos'],
                    'pos_description' => $validated['keterangan_pos'],
                    'is_active' => $validated['edit_status_pos'],
                    'updated_at' => now()
                ]);

            Log::info('Database update result:', ['updated' => $updated, 'id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Pos pengeluaran berhasil diupdate!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating expense pos: ' . $e->getMessage(), [
                'id' => $id,
                'validated_data' => $validated ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate pos pengeluaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete expense position
     */
    public function deleteExpensePos($id)
    {
        try {
            // Check if pos is used in transactions
            $usedInTransaction = DB::table('transaksi_pengeluaran_detail')
                ->where('pos_pengeluaran_id', $id)
                ->exists();

            if ($usedInTransaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pos pengeluaran tidak dapat dihapus karena masih digunakan dalam transaksi'
                ], 400);
            }

            DB::table('pos_pengeluaran')
                ->where('pos_id', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pos pengeluaran berhasil dihapus!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting expense pos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pos pengeluaran: ' . $e->getMessage()
            ], 500);
        }
    }
}
