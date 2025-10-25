<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SPMBFormSettings;
use Illuminate\Http\Request;

class SPMBFormSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fields = SPMBFormSettings::orderBy('field_section')->orderBy('field_order')->get();
        $fieldsBySection = $fields->groupBy('field_section');
        
        return view('admin.spmb.form-settings.index', compact('fields', 'fieldsBySection'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sections = [
            'personal' => 'Data Pribadi',
            'parent' => 'Data Orang Tua',
            'academic' => 'Data Akademik'
        ];
        
        $fieldTypes = [
            'text' => 'Text Input',
            'textarea' => 'Textarea',
            'select' => 'Select Dropdown',
            'date' => 'Date Input',
            'email' => 'Email Input',
            'tel' => 'Phone Input',
            'number' => 'Number Input'
        ];
        
        return view('admin.spmb.form-settings.create', compact('sections', 'fieldTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'field_name' => 'required|string|max:255|unique:s_p_m_b_form_settings,field_name',
            'field_label' => 'required|string|max:255',
            'field_type' => 'required|string|max:50',
            'field_section' => 'required|string|max:50',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'show_in_print' => 'boolean',
            'field_placeholder' => 'nullable|string|max:255',
            'field_help_text' => 'nullable|string',
            'field_order' => 'nullable|integer|min:0',
            'field_options' => 'nullable|array'
        ]);

        // Handle checkbox fields that might not be sent when unchecked
        $data = $request->all();
        
        // Set boolean fields to false if not present in request
        $data['is_required'] = $request->has('is_required');
        $data['is_active'] = $request->has('is_active');
        $data['show_in_print'] = $request->has('show_in_print');
        
        SPMBFormSettings::create($data);

        return redirect()->route('manage.spmb.form-settings.index')
            ->with('success', 'Field form berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $field = SPMBFormSettings::findOrFail($id);
        return view('admin.spmb.form-settings.show', compact('field'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $field = SPMBFormSettings::findOrFail($id);
        
        $sections = [
            'personal' => 'Data Pribadi',
            'parent' => 'Data Orang Tua',
            'academic' => 'Data Akademik'
        ];
        
        $fieldTypes = [
            'text' => 'Text Input',
            'textarea' => 'Textarea',
            'select' => 'Select Dropdown',
            'date' => 'Date Input',
            'email' => 'Email Input',
            'tel' => 'Phone Input',
            'number' => 'Number Input'
        ];
        
        return view('admin.spmb.form-settings.edit', compact('field', 'sections', 'fieldTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $field = SPMBFormSettings::findOrFail($id);
        
        $request->validate([
            'field_name' => 'required|string|max:255|unique:s_p_m_b_form_settings,field_name,' . $id,
            'field_label' => 'required|string|max:255',
            'field_type' => 'required|string|max:50',
            'field_section' => 'required|string|max:50',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'show_in_print' => 'boolean',
            'field_placeholder' => 'nullable|string|max:255',
            'field_help_text' => 'nullable|string',
            'field_order' => 'nullable|integer|min:0',
            'field_options' => 'nullable|array'
        ]);

        // Handle checkbox fields that might not be sent when unchecked
        $data = $request->all();
        
        // Set boolean fields to false if not present in request
        $data['is_required'] = $request->has('is_required');
        $data['is_active'] = $request->has('is_active');
        $data['show_in_print'] = $request->has('show_in_print');
        
        $field->update($data);

        return redirect()->route('manage.spmb.form-settings.index')
            ->with('success', 'Field form berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $field = SPMBFormSettings::findOrFail($id);
        $field->delete();

        return redirect()->route('manage.spmb.form-settings.index')
            ->with('success', 'Field form berhasil dihapus.');
    }

    /**
     * Toggle field active status
     */
    public function toggleStatus(Request $request, $id)
    {
        $field = SPMBFormSettings::findOrFail($id);
        $field->update(['is_active' => !$field->is_active]);

        return back()->with('success', 'Status field berhasil diperbarui.');
    }

    /**
     * Update field order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*.id' => 'required|exists:s_p_m_b_form_settings,id',
            'fields.*.order' => 'required|integer|min:0'
        ]);

        foreach ($request->fields as $fieldData) {
            SPMBFormSettings::where('id', $fieldData['id'])
                ->update(['field_order' => $fieldData['order']]);
        }

        return response()->json(['success' => true]);
    }
}
