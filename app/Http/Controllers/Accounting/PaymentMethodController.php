<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentMethods = DB::table('payment_methods as pm')
            ->leftJoin('kas as k', 'pm.kas_id', '=', 'k.id')
            ->select('pm.*', 'k.nama_kas as kas_nama')
            ->orderBy('pm.nama_metode')
            ->get();
        
        // Get kas list for dropdown
        $kasList = DB::table('kas')
            ->select('id', 'nama_kas')
            ->where('is_active', 1)
            ->orderBy('nama_kas')
            ->get();
        
        return view('accounting.payment-methods.index', compact('paymentMethods', 'kasList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.payment-methods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_metode' => 'required|string|max:100',
            'kas_id' => 'required|exists:kas,id',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|in:ON,OFF'
        ]);

        try {
            DB::table('payment_methods')->insert([
                'nama_metode' => $request->nama_metode,
                'kas_id' => $request->kas_id,
                'keterangan' => $request->keterangan,
                'status' => $request->status ?: 'OFF',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Metode pembayaran berhasil ditambahkan!'
                ]);
            }

            return redirect()->route('manage.accounting.payment-methods.index')
                ->with('success', 'Metode pembayaran berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error creating payment method: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan metode pembayaran: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal menambahkan metode pembayaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $paymentMethod = DB::table('payment_methods')->where('id', $id)->first();
        
        if (!$paymentMethod) {
            return redirect()->route('manage.accounting.payment-methods.index')
                ->with('error', 'Metode pembayaran tidak ditemukan!');
        }

        return view('accounting.payment-methods.edit', compact('paymentMethod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_metode' => 'required|string|max:100',
            'kas_id' => 'required|exists:kas,id',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|in:ON,OFF'
        ]);

        try {
            DB::table('payment_methods')->where('id', $id)->update([
                'nama_metode' => $request->nama_metode,
                'kas_id' => $request->kas_id,
                'keterangan' => $request->keterangan,
                'status' => $request->status ?: 'OFF',
                'updated_at' => now()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Metode pembayaran berhasil diupdate!'
                ]);
            }

            return redirect()->route('manage.accounting.payment-methods.index')
                ->with('success', 'Metode pembayaran berhasil diupdate!');
        } catch (\Exception $e) {
            Log::error('Error updating payment method: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate metode pembayaran: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal mengupdate metode pembayaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Toggle payment method status (ON/OFF)
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $paymentMethod = DB::table('payment_methods')->where('id', $id)->first();
            
            if (!$paymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metode pembayaran tidak ditemukan!'
                ], 404);
            }
            
            $status = $request->input('status', 'OFF');
            
            DB::table('payment_methods')
                ->where('id', $id)
                ->update([
                    'status' => $status,
                    'updated_at' => now()
                ]);
            
            $statusText = $status == 'ON' ? 'diaktifkan' : 'dinonaktifkan';
            
            return response()->json([
                'success' => true,
                'message' => "Metode pembayaran berhasil {$statusText}!"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error toggling payment method status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status metode pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Hapus metode pembayaran langsung (tidak ada relasi dengan tabel lain)
            $deleted = DB::table('payment_methods')->where('id', $id)->delete();
            
            if (!$deleted) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Metode pembayaran tidak ditemukan atau sudah dihapus!'
                    ], 404);
                }
                
                return redirect()->route('manage.accounting.payment-methods.index')
                    ->with('error', 'Metode pembayaran tidak ditemukan atau sudah dihapus!');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Metode pembayaran berhasil dihapus!'
                ]);
            }

            return redirect()->route('manage.accounting.payment-methods.index')
                ->with('success', 'Metode pembayaran berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting payment method: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus metode pembayaran: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal menghapus metode pembayaran: ' . $e->getMessage());
        }
    }
}
