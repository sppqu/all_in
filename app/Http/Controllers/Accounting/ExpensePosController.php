<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpensePosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $startDate = request()->get('start_date', date('Y-m-d', strtotime('-3 months')));
        $endDate = request()->get('end_date', date('Y-m-d'));
        
        // Ambil data pos pengeluaran
        $expensePos = DB::table('expense_pos')
            ->orderBy('pos_name')
            ->get();
        
        // Ambil current school_id
        $currentSchoolId = currentSchoolId();
        
        // Ambil data pos penerimaan untuk dropdown sumber dana
        // Filter berdasarkan school_id
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
        
        // Log untuk debugging
        Log::info('ExpensePos ReceiptPos Count: ' . $receiptPos->count());
        Log::info('ExpensePos ReceiptPos Data: ', $receiptPos->toArray());
        
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
        
        // Log untuk debugging
        Log::info('ExpensePos Kas List Count: ' . $kasList->count());
        Log::info('ExpensePos Kas List Data: ', $kasList->toArray());
        
        // Ambil data transaksi pengeluaran
        $transactions = DB::table('expense_transactions as et')
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
            ->whereBetween('et.tanggal', [$startDate, $endDate])
            ->orderBy('et.tanggal', 'desc')
            ->get();
        
        // Pastikan kasList dikonversi ke array untuk JSON
        $kasListArray = $kasList->map(function($kas) {
            return [
                'id' => $kas->id,
                'nama_kas' => $kas->nama_kas,
                'deskripsi' => $kas->deskripsi ?? null,
                'is_active' => $kas->is_active ?? 1,
                'jenis_kas' => $kas->jenis_kas ?? 'cash'
            ];
        })->values()->all();
        
        // Log untuk debugging
        Log::info('ExpensePos Kas List Array Count: ' . count($kasListArray));
        Log::info('ExpensePos Kas List Array: ', $kasListArray);
        
        // Ambil data profil sekolah untuk header kuitansi
        $schoolProfile = DB::table('schools')->first();
        
        return view('accounting.expense-pos.index', compact('expensePos', 'receiptPos', 'paymentMethods', 'kasList', 'kasListArray', 'transactions', 'startDate', 'endDate', 'schoolProfile'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.expense-pos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pos_name' => 'required|string|max:100',
            'pos_code' => 'required|string|max:20|unique:expense_pos,pos_code',
            'pos_type' => 'required|in:operasional,administrasi,akademik,fasilitas,lainnya',
            'pos_description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            DB::table('expense_pos')->insert([
                'pos_name' => $request->pos_name,
                'pos_code' => $request->pos_code,
                'pos_type' => $request->pos_type,
                'pos_description' => $request->pos_description,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pos pengeluaran berhasil ditambahkan!'
                ]);
            }

            return redirect()->route('manage.accounting.expense-pos.index')
                ->with('success', 'Pos pengeluaran berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error creating expense pos: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan pos pengeluaran: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pos pengeluaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $expensePos = DB::table('expense_pos')->where('id', $id)->first();
        
        if (!$expensePos) {
            return redirect()->route('manage.accounting.expense-pos.index')
                ->with('error', 'Pos pengeluaran tidak ditemukan!');
        }

        return view('accounting.expense-pos.edit', compact('expensePos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'pos_name' => 'required|string|max:100',
            'pos_code' => 'required|string|max:20|unique:expense_pos,pos_code,' . $id,
            'pos_type' => 'required|in:operasional,administrasi,akademik,fasilitas,lainnya',
            'pos_description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            DB::table('expense_pos')->where('id', $id)->update([
                'pos_name' => $request->pos_name,
                'pos_code' => $request->pos_code,
                'pos_type' => $request->pos_type,
                'pos_description' => $request->pos_description,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'updated_at' => now()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pos pengeluaran berhasil diupdate!'
                ]);
            }

            return redirect()->route('manage.accounting.expense-pos.index')
                ->with('success', 'Pos pengeluaran berhasil diupdate!');
        } catch (\Exception $e) {
            Log::error('Error updating expense pos: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate pos pengeluaran: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal mengupdate pos pengeluaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Cek apakah pos pengeluaran masih digunakan
            $usedInKredit = false; // kredit table removed

            if ($usedInKredit) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pos pengeluaran tidak dapat dihapus karena masih digunakan dalam transaksi!'
                    ], 422);
                }
                
                return redirect()->route('manage.accounting.expense-pos.index')
                    ->with('error', 'Pos pengeluaran tidak dapat dihapus karena masih digunakan dalam transaksi!');
            }

            $deleted = DB::table('expense_pos')->where('id', $id)->delete();
            
            if (!$deleted) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pos pengeluaran tidak ditemukan atau sudah dihapus!'
                    ], 404);
                }
                
                return redirect()->route('manage.accounting.expense-pos.index')
                    ->with('error', 'Pos pengeluaran tidak ditemukan atau sudah dihapus!');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pos pengeluaran berhasil dihapus!'
                ]);
            }

            return redirect()->route('manage.accounting.expense-pos.index')
                ->with('success', 'Pos pengeluaran berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting expense pos: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus pos pengeluaran: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal menghapus pos pengeluaran: ' . $e->getMessage());
        }
    }
}
