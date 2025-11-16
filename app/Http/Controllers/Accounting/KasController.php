<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class KasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kasList = DB::table('kas')
            ->select('id', 'nama_kas', 'jenis_kas', 'deskripsi', 'is_active', 'saldo')
            ->orderBy('nama_kas')
            ->get();
        
        return view('accounting.kas.index', compact('kasList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.kas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_kas' => 'required|string|max:100',
                'jenis' => 'required|in:cash,bank,e_wallet',
                'deskripsi' => 'nullable|string'
            ]);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            DB::table('kas')->insert([
                'nama_kas' => $request->nama_kas,
                'deskripsi' => $request->deskripsi,
                'jenis_kas' => $request->jenis,
                'saldo' => $request->saldo ?? 0,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kas berhasil ditambahkan!'
                ]);
            }

            return redirect()->route('manage.accounting.kas.index')
                ->with('success', 'Kas berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error creating kas: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan kas: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal menambahkan kas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kas = DB::table('kas')->where('id', $id)->first();
        
        if (!$kas) {
            return redirect()->route('manage.accounting.kas.index')
                ->with('error', 'Kas tidak ditemukan!');
        }

        return view('accounting.kas.edit', compact('kas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nama_kas' => 'required|string|max:100',
                'jenis' => 'required|in:cash,bank,e_wallet',
                'deskripsi' => 'nullable|string'
            ]);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            DB::table('kas')->where('id', $id)->update([
                'nama_kas' => $request->nama_kas,
                'deskripsi' => $request->deskripsi,
                'jenis_kas' => $request->jenis,
                'saldo' => $request->saldo ?? 0,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'updated_at' => now()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kas berhasil diupdate!'
                ]);
            }

            return redirect()->route('manage.accounting.kas.index')
                ->with('success', 'Kas berhasil diupdate!');
        } catch (\Exception $e) {
            Log::error('Error updating kas: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate kas: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal mengupdate kas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Toggle kas status (aktif/tidak aktif)
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $kas = DB::table('kas')->where('id', $id)->first();
            
            if (!$kas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kas tidak ditemukan!'
                ], 404);
            }
            
            $isActive = $request->input('is_active', 0);
            
            DB::table('kas')
                ->where('id', $id)
                ->update([
                    'is_active' => $isActive,
                    'updated_at' => now()
                ]);
            
            $statusText = $isActive ? 'diaktifkan' : 'dinonaktifkan';
            
            return response()->json([
                'success' => true,
                'message' => "Kas berhasil {$statusText}!"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error toggling kas status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status kas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Cek apakah kas masih digunakan
            $usedInDebit = false; // debit table removed
            $usedInKredit = false; // kredit table removed

            if ($usedInDebit || $usedInKredit) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kas tidak dapat dihapus karena masih digunakan dalam transaksi!'
                    ], 422);
                }
                
                return redirect()->route('manage.accounting.kas.index')
                    ->with('error', 'Kas tidak dapat dihapus karena masih digunakan dalam transaksi!');
            }

            // Hapus kas
            $deleted = DB::table('kas')->where('id', $id)->delete();
            
            if (!$deleted) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kas tidak ditemukan atau sudah dihapus!'
                    ], 404);
                }
                
                return redirect()->route('manage.accounting.kas.index')
                    ->with('error', 'Kas tidak ditemukan atau sudah dihapus!');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kas berhasil dihapus!'
                ]);
            }

            return redirect()->route('manage.accounting.kas.index')
                ->with('success', 'Kas berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting kas: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus kas: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal menghapus kas: ' . $e->getMessage());
        }
    }
}
