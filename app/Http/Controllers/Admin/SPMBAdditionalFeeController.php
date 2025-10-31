<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SPMBAdditionalFee;
use App\Models\SPMBWave;
use App\Helpers\WaveHelper;
use Illuminate\Support\Facades\Validator;

class SPMBAdditionalFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $additionalFees = SPMBAdditionalFee::ordered()->paginate(15);
        return view('admin.spmb.additional-fees.index', compact('additionalFees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = WaveHelper::getFeeCategories();
        $types = WaveHelper::getFeeTypes();
        
        return view('admin.spmb.additional-fees.create', compact('categories', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:spmb_additional_fees,code',
            'description' => 'nullable|string',
            'type' => 'required|in:mandatory,optional,conditional',
            'category' => 'required|in:seragam,buku,alat_tulis,kegiatan,lainnya',
            'amount' => 'required|numeric|min:0',
            'conditions' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        // Checkbox yang dicentang (value="1") akan dikirim, yang tidak dicentang tidak akan dikirim sama sekali
        $data['is_active'] = $request->filled('is_active');
        
        SPMBAdditionalFee::create($data);

        return redirect()->route('manage.spmb.additional-fees.index')
            ->with('success', 'Biaya tambahan berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $additionalFee = SPMBAdditionalFee::findOrFail($id);
        $waves = $additionalFee->waves()->paginate(10);
        
        return view('admin.spmb.additional-fees.show', compact('additionalFee', 'waves'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $additionalFee = SPMBAdditionalFee::findOrFail($id);
        $categories = WaveHelper::getFeeCategories();
        $types = WaveHelper::getFeeTypes();
        
        return view('admin.spmb.additional-fees.edit', compact('additionalFee', 'categories', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $additionalFee = SPMBAdditionalFee::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:spmb_additional_fees,code,' . $id,
            'description' => 'nullable|string',
            'type' => 'required|in:mandatory,optional,conditional',
            'category' => 'required|in:seragam,buku,alat_tulis,kegiatan,lainnya',
            'amount' => 'required|numeric|min:0',
            'conditions' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        // Checkbox yang dicentang (value="1") akan dikirim, yang tidak dicentang tidak akan dikirim sama sekali
        $data['is_active'] = $request->filled('is_active');
        
        $additionalFee->update($data);

        return redirect()->route('manage.spmb.additional-fees.index')
            ->with('success', 'Biaya tambahan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $additionalFee = SPMBAdditionalFee::findOrFail($id);
        
        // Check if fee is being used in any waves
        $waveCount = $additionalFee->waves()->count();
        if ($waveCount > 0) {
            return back()->withErrors([
                'error' => "Tidak dapat menghapus biaya tambahan karena sedang digunakan di {$waveCount} gelombang."
            ]);
        }

        // Check if fee is being used in any registrations
        $registrationCount = $additionalFee->registrations()->count();
        if ($registrationCount > 0) {
            return back()->withErrors([
                'error' => "Tidak dapat menghapus biaya tambahan karena sedang digunakan di {$registrationCount} pendaftaran."
            ]);
        }

        $additionalFee->delete();

        return redirect()->route('manage.spmb.additional-fees.index')
            ->with('success', 'Biaya tambahan berhasil dihapus!');
    }

    /**
     * Toggle status of additional fee
     */
    public function toggleStatus(Request $request, string $id)
    {
        $additionalFee = SPMBAdditionalFee::findOrFail($id);
        
        $additionalFee->update([
            'is_active' => !$additionalFee->is_active
        ]);

        $status = $additionalFee->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return back()->with('success', "Biaya tambahan berhasil {$status}!");
    }

    /**
     * Manage additional fees for a specific wave
     */
    public function manageWaveFees(Request $request, string $waveId)
    {
        $wave = SPMBWave::findOrFail($waveId);
        $allFees = SPMBAdditionalFee::active()->ordered()->get();
        $waveFees = $wave->additionalFees()->get();
        
        return view('admin.spmb.additional-fees.manage-wave', compact('wave', 'allFees', 'waveFees'));
    }

    /**
     * Save additional fees for a specific wave
     */
    public function saveWaveFees(Request $request, string $waveId)
    {
        $wave = SPMBWave::findOrFail($waveId);
        
        $validator = Validator::make($request->all(), [
            'additional_fees' => 'required|array',
            'additional_fees.*.fee_id' => 'required|exists:spmb_additional_fees,id',
            'additional_fees.*.amount' => 'required|numeric|min:0',
            'additional_fees.*.is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Clear existing fees for this wave
        $wave->additionalFees()->detach();

        // Add new fees
        foreach ($request->additional_fees as $feeData) {
            if ($feeData['fee_id']) {
                $wave->additionalFees()->attach($feeData['fee_id'], [
                    'amount' => $feeData['amount'],
                    'is_active' => isset($feeData['is_active']) ? true : false
                ]);
            }
        }

        return redirect()->route('manage.spmb.waves.show', $waveId)
            ->with('success', 'Biaya tambahan untuk gelombang berhasil disimpan!');
    }
}