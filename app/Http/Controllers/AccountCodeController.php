<?php

namespace App\Http\Controllers;

use App\Models\AccountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AccountCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AccountCode::query();

        // Filter berdasarkan tipe
        if ($request->filled('tipe')) {
            $query->byType($request->tipe);
        }

        // Filter berdasarkan kategori
        if ($request->filled('kategori')) {
            $query->byCategory($request->kategori);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Pencarian
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $accountCodes = $query->orderBy('kode')->paginate(10);

        $tipeOptions = [
            'aktiva' => 'Aktiva',
            'pasiva' => 'Pasiva',
            'modal' => 'Modal',
            'pendapatan' => 'Pendapatan',
            'beban' => 'Beban'
        ];

        $kategoriOptions = [
            'lancar' => 'Lancar',
            'tetap' => 'Tetap',
            'pendapatan' => 'Pendapatan',
            'beban_operasional' => 'Beban Operasional',
            'beban_non_operasional' => 'Beban Non-Operasional'
        ];

        return view('account-codes.index', compact('accountCodes', 'tipeOptions', 'kategoriOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipeOptions = [
            'aktiva' => 'Aktiva',
            'pasiva' => 'Pasiva',
            'modal' => 'Modal',
            'pendapatan' => 'Pendapatan',
            'beban' => 'Beban'
        ];

        $kategoriOptions = [
            'lancar' => 'Lancar',
            'tetap' => 'Tetap',
            'pendapatan' => 'Pendapatan',
            'beban_operasional' => 'Beban Operasional',
            'beban_non_operasional' => 'Beban Non-Operasional'
        ];

        return view('account-codes.create', compact('tipeOptions', 'kategoriOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug: Log request data
        \Log::info('AccountCode store method called', [
            'request_data' => $request->all(),
            'user_id' => auth()->id(),
            'url' => $request->url(),
            'method' => $request->method(),
            'headers' => $request->headers->all()
        ]);
        
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:32|unique:account_codes,kode',
            'nama' => 'required|string|max:128',
            'deskripsi' => 'nullable|string',
            'tipe' => 'required|in:aktiva,pasiva,modal,pendapatan,beban',
            'kategori' => 'nullable|in:lancar,tetap,pendapatan,beban_operasional,beban_non_operasional',
            'is_active' => 'boolean'
        ], [
            'kode.required' => 'Kode akun wajib diisi. Contoh: 1101, 2101, 4101',
            'kode.unique' => 'Kode akun sudah digunakan. Silakan gunakan kode yang berbeda.',
            'kode.max' => 'Kode akun terlalu panjang. Maksimal 32 karakter.',
            'kode.string' => 'Kode akun harus berupa teks.',
            'nama.required' => 'Nama akun wajib diisi. Contoh: Kas, Piutang Dagang, Pendapatan SPP',
            'nama.max' => 'Nama akun terlalu panjang. Maksimal 128 karakter.',
            'nama.string' => 'Nama akun harus berupa teks.',
            'deskripsi.string' => 'Deskripsi harus berupa teks.',
            'tipe.required' => 'Tipe akun wajib dipilih (Aktiva, Pasiva, Modal, Pendapatan, atau Beban)',
            'tipe.in' => 'Tipe akun tidak valid. Pilih salah satu: Aktiva, Pasiva, Modal, Pendapatan, atau Beban',
            'kategori.in' => 'Kategori akun tidak valid. Pilih salah satu: Lancar, Tetap, Pendapatan, Beban Operasional, atau Beban Non-Operasional',
            'is_active.boolean' => 'Status aktif harus berupa ya atau tidak.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            AccountCode::create([
                'kode' => strtoupper($request->kode),
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'tipe' => $request->tipe,
                'kategori' => $request->kategori,
                'is_active' => $request->has('is_active')
            ]);

            DB::commit();

            return redirect()->route('manage.account-codes.index')
                ->with('success', 'Kode akun berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('AccountCode store error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            // Handle specific database errors
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return redirect()->back()
                    ->with('error', 'Kode akun sudah digunakan. Silakan gunakan kode yang berbeda.')
                    ->withInput();
            }
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountCode $accountCode)
    {
        return view('account-codes.show', compact('accountCode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccountCode $accountCode)
    {
        $tipeOptions = [
            'aktiva' => 'Aktiva',
            'pasiva' => 'Pasiva',
            'modal' => 'Modal',
            'pendapatan' => 'Pendapatan',
            'beban' => 'Beban'
        ];

        $kategoriOptions = [
            'lancar' => 'Lancar',
            'tetap' => 'Tetap',
            'pendapatan' => 'Pendapatan',
            'beban_operasional' => 'Beban Operasional',
            'beban_non_operasional' => 'Beban Non-Operasional'
        ];

        return view('account-codes.edit', compact('accountCode', 'tipeOptions', 'kategoriOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountCode $accountCode)
    {
        // Debug: Log request data
        \Log::info('AccountCode update method called', [
            'account_code_id' => $accountCode->id,
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);
        
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:32|unique:account_codes,kode,' . $accountCode->id,
            'nama' => 'required|string|max:128',
            'deskripsi' => 'nullable|string',
            'tipe' => 'required|in:aktiva,pasiva,modal,pendapatan,beban',
            'kategori' => 'nullable|in:lancar,tetap,pendapatan,beban_operasional,beban_non_operasional',
            'is_active' => 'boolean'
        ], [
            'kode.required' => 'Kode akun wajib diisi. Contoh: 1101, 2101, 4101',
            'kode.unique' => 'Kode akun sudah digunakan. Silakan gunakan kode yang berbeda.',
            'kode.max' => 'Kode akun terlalu panjang. Maksimal 32 karakter.',
            'kode.string' => 'Kode akun harus berupa teks.',
            'nama.required' => 'Nama akun wajib diisi. Contoh: Kas, Piutang Dagang, Pendapatan SPP',
            'nama.max' => 'Nama akun terlalu panjang. Maksimal 128 karakter.',
            'nama.string' => 'Nama akun harus berupa teks.',
            'deskripsi.string' => 'Deskripsi harus berupa teks.',
            'tipe.required' => 'Tipe akun wajib dipilih (Aktiva, Pasiva, Modal, Pendapatan, atau Beban)',
            'tipe.in' => 'Tipe akun tidak valid. Pilih salah satu: Aktiva, Pasiva, Modal, Pendapatan, atau Beban',
            'kategori.in' => 'Kategori akun tidak valid. Pilih salah satu: Lancar, Tetap, Pendapatan, Beban Operasional, atau Beban Non-Operasional',
            'is_active.boolean' => 'Status aktif harus berupa ya atau tidak.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $accountCode->update([
                'kode' => strtoupper($request->kode),
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'tipe' => $request->tipe,
                'kategori' => $request->kategori,
                'is_active' => $request->has('is_active')
            ]);

            DB::commit();

            return redirect()->route('manage.account-codes.index')
                ->with('success', 'Kode akun berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('AccountCode update error: ' . $e->getMessage(), [
                'account_code_id' => $accountCode->id,
                'exception' => $e,
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            // Handle specific database errors
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return redirect()->back()
                    ->with('error', 'Kode akun sudah digunakan. Silakan gunakan kode yang berbeda.')
                    ->withInput();
            }
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountCode $accountCode)
    {
        try {
            DB::beginTransaction();

            $accountCode->delete();

            DB::commit();

            return redirect()->route('manage.account-codes.index')
                ->with('success', 'Kode akun berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Toggle status aktif/non-aktif
     */
    public function toggleStatus(AccountCode $accountCode)
    {
        try {
            $accountCode->update([
                'is_active' => !$accountCode->is_active
            ]);

            $status = $accountCode->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return redirect()->route('manage.account-codes.index')
                ->with('success', "Kode akun berhasil {$status}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get account codes for AJAX request
     */
    public function getAccountCodes(Request $request)
    {
        $query = AccountCode::active();

        if ($request->filled('tipe')) {
            $query->byType($request->tipe);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $accountCodes = $query->select('id', 'kode', 'nama', 'tipe')
            ->orderBy('kode')
            ->get();

        return response()->json($accountCodes);
    }
} 