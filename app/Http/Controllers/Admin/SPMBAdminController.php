<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SPMBRegistration;
use App\Models\SPMBDocument;
use App\Models\SPMBPayment;
use App\Models\Bebas;
use App\Models\Payment;
use App\Models\Pos;
use App\Helpers\WaveHelper;
use Illuminate\Support\Facades\Storage;

class SPMBAdminController extends Controller
{
    /**
     * Show SPMB dashboard
     */
    public function index(Request $request)
    {
        $stats = [
            'total' => SPMBRegistration::count(),
            'completed' => SPMBRegistration::where('status_pendaftaran', 'diterima')->count(),
            'pending' => SPMBRegistration::where('status_pendaftaran', 'pending')->count(),
            'ditolak' => SPMBRegistration::where('status_pendaftaran', 'ditolak')->count(),
            'paid_registration' => SPMBRegistration::where('registration_fee_paid', true)->count(),
            'paid_spmb' => SPMBRegistration::where('spmb_fee_paid', true)->count(),
            'status_pending' => SPMBRegistration::where('status_pendaftaran', 'pending')->count(),
            'status_diterima' => SPMBRegistration::where('status_pendaftaran', 'diterima')->count(),
            'status_ditolak' => SPMBRegistration::where('status_pendaftaran', 'ditolak')->count(),
        ];
        
        // Get SPMB settings
        $spmbSettings = \App\Models\SPMBSettings::first();

        // Data untuk grafik garis (pendaftar per hari - 30 hari terakhir)
        $dailyRegistrations = [];
        $dailyLabels = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dateLabel = now()->subDays($i)->format('d/m');
            $count = SPMBRegistration::whereDate('created_at', $date)->count();
            $dailyLabels[] = $dateLabel;
            $dailyRegistrations[] = $count;
        }

        // Data untuk grafik pie (distribusi status)
        $pieData = [
            'labels' => ['Diterima', 'Pending', 'Ditolak'],
            'data' => [
                $stats['completed'],
                $stats['pending'],
                $stats['ditolak']
            ],
            'colors' => [
                'rgba(40, 167, 69, 0.8)',   // Hijau untuk Diterima
                'rgba(255, 193, 7, 0.8)',   // Kuning untuk Pending
                'rgba(220, 53, 69, 0.8)'    // Merah untuk Ditolak
            ],
            'borderColors' => [
                'rgba(40, 167, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(220, 53, 69, 1)'
            ]
        ];

        return view('admin.spmb.index', compact('stats', 'spmbSettings', 'dailyLabels', 'dailyRegistrations', 'pieData'));
    }

    /**
     * Show registrations list
     */
    public function registrations(Request $request)
    {
        $query = SPMBRegistration::with(['documents', 'payments', 'kejuruan']);

        // Filter by status pendaftaran
        if ($request->filled('status_pendaftaran')) {
            $query->where('status_pendaftaran', $request->status_pendaftaran);
        }

        // Filter by kejuruan
        if ($request->filled('kejuruan_id')) {
            $query->where('kejuruan_id', $request->kejuruan_id);
        }

        // Search by name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('nomor_pendaftaran', 'like', "%{$search}%");
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get kejuruan for filter
        $kejuruan = \App\Models\SPMBKejuruan::getActive();

        return view('admin.spmb.registrations', compact('registrations', 'kejuruan'));
    }

    /**
     * Show registration detail
     */
    public function show($id)
    {
        $registration = SPMBRegistration::with(['documents', 'payments'])
            ->findOrFail($id);

        return view('admin.spmb.show', compact('registration'));
    }

    /**
     * Show edit form for registration
     */
    public function edit($id)
    {
        $registration = SPMBRegistration::with(['documents', 'payments'])
            ->findOrFail($id);
        $kejuruan = \App\Models\SPMBKejuruan::getActive();
        $formSettings = \App\Models\SPMBFormSettings::getActiveFieldsBySection();

        return view('admin.spmb.edit', compact('registration', 'kejuruan', 'formSettings'));
    }

    /**
     * Update registration
     */
    public function update(Request $request, $id)
    {
        $registration = SPMBRegistration::findOrFail($id);

        // Get dynamic form settings for validation
        $formSettings = \App\Models\SPMBFormSettings::getActiveFields();
        
        // Check if there are active kejuruans
        $hasKejuruans = \App\Models\SPMBKejuruan::getActive()->count() > 0;
        
        $validationRules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:spmb_registrations,phone,' . $id,
            'kejuruan_id' => $hasKejuruans ? 'required|exists:s_p_m_b_kejuruans,id' : 'nullable|exists:s_p_m_b_kejuruans,id',
            'status_pendaftaran' => 'required|in:pending,diterima,ditolak',
            'registration_fee_paid' => 'nullable|boolean',
            'spmb_fee_paid' => 'nullable|boolean'
        ];
        
        foreach ($formSettings as $field) {
            $rule = [];
            if ($field->is_required) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }
            
            // Add type-specific validation
            switch ($field->field_type) {
                case 'email':
                    $rule[] = 'email';
                    break;
                case 'tel':
                    $rule[] = 'string|max:20';
                    break;
                case 'number':
                    $rule[] = 'numeric';
                    break;
                case 'date':
                    $rule[] = 'date';
                    break;
                default:
                    $rule[] = 'string|max:255';
                    break;
            }
            
            $validationRules[$field->field_name] = implode('|', $rule);
        }

        $request->validate($validationRules);

        // Update basic registration data
        $registration->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'kejuruan_id' => $request->kejuruan_id,
            'status_pendaftaran' => $request->status_pendaftaran,
            'registration_fee_paid' => $request->has('registration_fee_paid'),
            'spmb_fee_paid' => $request->has('spmb_fee_paid')
        ]);

        // Update form data from dynamic fields
        $formData = [];
        foreach ($formSettings as $field) {
            if ($request->has($field->field_name)) {
                $formData[$field->field_name] = $request->input($field->field_name);
            }
        }

        // Update form_data (Laravel will automatically convert array to JSON due to model cast)
        $registration->update(['form_data' => $formData]);

        return redirect()->route('manage.spmb.show', $registration->id)
            ->with('success', 'Data pendaftaran berhasil diperbarui.');
    }

    /**
     * Update registration status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,completed'
        ]);

        $registration = SPMBRegistration::findOrFail($id);
        $registration->update(['status' => $request->status]);

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    /**
     * Update document status
     */
    public function updateDocumentStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string'
        ]);

        $document = SPMBDocument::findOrFail($id);
        $document->update([
            'status' => $request->status,
            'notes' => $request->notes
        ]);

        return back()->with('success', 'Status dokumen berhasil diperbarui.');
    }

    /**
     * Download document
     */
    public function downloadDocument($id)
    {
        $document = SPMBDocument::findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['error' => 'File tidak ditemukan.']);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * View document
     */
    public function viewDocument($id)
    {
        $document = SPMBDocument::findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['error' => 'File tidak ditemukan.']);
        }

        $filePath = Storage::disk('public')->path($document->file_path);
        $mimeType = $document->mime_type;

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $document->file_name . '"'
        ]);
    }

    /**
     * Show payments
     */
    public function payments(Request $request)
    {
        $query = SPMBPayment::with('registration');

        // Apply filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get stats based on filters
        $statsQuery = SPMBPayment::query();
        if ($request->filled('start_date')) {
            $statsQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $statsQuery->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('type')) {
            $statsQuery->where('type', $request->type);
        }
        if ($request->filled('payment_method')) {
            $statsQuery->where('payment_method', $request->payment_method);
        }
        if ($request->filled('status')) {
            $statsQuery->where('status', $request->status);
        }

        $stats = [
            'total' => $statsQuery->count(),
            'paid' => (clone $statsQuery)->where('status', 'paid')->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'expired' => (clone $statsQuery)->where('status', 'expired')->count(),
            'total_amount' => (clone $statsQuery)->where('status', 'paid')->sum('amount')
        ];

        // Get filter options
        $filterOptions = [
            'types' => SPMBPayment::distinct()->pluck('type')->filter(),
            'payment_methods' => SPMBPayment::distinct()->pluck('payment_method')->filter(),
            'statuses' => SPMBPayment::distinct()->pluck('status')->filter(),
        ];

        return view('admin.spmb.payments', compact('payments', 'stats', 'filterOptions'));
    }

    /**
     * Export payments to PDF
     */
    public function exportPaymentsPDF(Request $request)
    {
        $query = SPMBPayment::with('registration');

        // Apply same filters as payments method
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Calculate stats
        $totalAmount = $payments->where('status', 'paid')->sum('amount');
        $paidCount = $payments->where('status', 'paid')->count();
        $pendingCount = $payments->where('status', 'pending')->count();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.spmb.payments-pdf', compact('payments', 'totalAmount', 'paidCount', 'pendingCount'))
            ->setPaper('A4', 'landscape');

        $filename = 'laporan-pembayaran-spmb-' . date('Y-m-d-H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Handle bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,update_status',
            'selected_ids' => 'required|array|min:1',
            'selected_ids.*' => 'exists:spmb_registrations,id',
        ]);

        $selectedIds = $request->selected_ids;
        $action = $request->action;

        try {
            switch ($action) {
                case 'delete':
                    $count = SPMBRegistration::whereIn('id', $selectedIds)->delete();
                    $message = "Berhasil menghapus {$count} data pendaftaran.";
                    break;

                case 'update_status':
                    $request->validate([
                        'status_pendaftaran' => 'required|in:pending,diterima,ditolak'
                    ]);
                    
                    $count = SPMBRegistration::whereIn('id', $selectedIds)
                        ->update(['status_pendaftaran' => $request->status_pendaftaran]);
                    $message = "Berhasil mengubah status {$count} data pendaftaran menjadi " . ucfirst($request->status_pendaftaran) . ".";
                    break;

                default:
                    return redirect()->back()->withErrors(['error' => 'Aksi tidak valid.']);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Bulk action error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memproses aksi bulk.']);
        }
    }

    /**
     * Export registrations
     */
    public function exportRegistrations()
    {
        $registrations = SPMBRegistration::with(['documents', 'payments'])->get();
        
        $filename = 'spmb_registrations_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Nama', 'No. HP', 'Status', 'Step', 
                'Biaya Pendaftaran', 'Biaya SPMB', 'Tanggal Daftar'
            ]);

            foreach ($registrations as $registration) {
                fputcsv($file, [
                    $registration->id,
                    $registration->name,
                    $registration->phone,
                    $registration->status,
                    $registration->step,
                    $registration->registration_fee_paid ? 'Lunas' : 'Belum',
                    $registration->spmb_fee_paid ? 'Lunas' : 'Belum',
                    $registration->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete registration
     */
    public function destroy($id)
    {
        $registration = SPMBRegistration::findOrFail($id);
        
        // Delete associated documents
        foreach ($registration->documents as $document) {
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
        }
        
        $registration->delete();

        return back()->with('success', 'Pendaftaran berhasil dihapus.');
    }

    /**
     * Show form to add new registration
     */
    public function create()
    {
        $kejuruan = \App\Models\SPMBKejuruan::getActive();
        $formSettings = \App\Models\SPMBFormSettings::getActiveFieldsBySection();
        return view('admin.spmb.create', compact('kejuruan', 'formSettings'));
    }

    /**
     * Store new registration from admin
     */
    public function store(Request $request)
    {
        // Get dynamic form settings for validation
        $formSettings = \App\Models\SPMBFormSettings::getActiveFields();
        
        // Check if there are active kejuruans
        $hasKejuruans = \App\Models\SPMBKejuruan::getActive()->count() > 0;
        
        $validationRules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:spmb_registrations,phone',
            'kejuruan_id' => $hasKejuruans ? 'required|exists:s_p_m_b_kejuruans,id' : 'nullable|exists:s_p_m_b_kejuruans,id',
            'status_pendaftaran' => 'required|in:pending,diterima,ditolak',
            'registration_fee_paid' => 'nullable|boolean',
            'spmb_fee_paid' => 'nullable|boolean'
        ];
        
        foreach ($formSettings as $field) {
            $rule = [];
            if ($field->is_required) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }
            
            // Add type-specific validation
            switch ($field->field_type) {
                case 'email':
                    $rule[] = 'email';
                    break;
                case 'tel':
                    $rule[] = 'string|max:20';
                    break;
                case 'number':
                    $rule[] = 'numeric';
                    break;
                case 'date':
                    $rule[] = 'date';
                    break;
                default:
                    $rule[] = 'string|max:255';
                    break;
            }
            
            $validationRules[$field->field_name] = implode('|', $rule);
        }

        $request->validate($validationRules);

        // Generate password from last 6 digits of phone
        $password = substr($request->phone, -6);

        // Create form data from dynamic fields
        $formData = [];
        foreach ($formSettings as $field) {
            if ($request->has($field->field_name)) {
                $formData[$field->field_name] = $request->input($field->field_name);
            }
        }

        // Create registration
        $registration = SPMBRegistration::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => $password,
            'step' => 6, // Skip to completed
            'status' => 'completed',
            'registration_fee_paid' => $request->has('registration_fee_paid') ? true : false,
            'spmb_fee_paid' => $request->has('spmb_fee_paid') ? true : false,
            'form_data' => $formData,
            'kejuruan_id' => $request->kejuruan_id,
            'status_pendaftaran' => $request->status_pendaftaran,
        ]);

        // Generate nomor pendaftaran (selalu generate, tidak peduli status)
        $registration->generateNomorPendaftaran();

        // Create mock payments for tracking based on payment status
        if ($request->has('registration_fee_paid')) {
            \App\Models\SPMBPayment::create([
                'registration_id' => $registration->id,
                'type' => 'registration_fee',
                'amount' => 50000,
                'payment_method' => 'ADMIN_ADD',
                'payment_reference' => 'ADMIN-REG-' . time() . '-' . $registration->id,
                'tripay_reference' => 'ADMIN-' . time() . '-' . rand(1000, 9999),
                'status' => 'paid',
                'paid_at' => now()
            ]);
        }

        if ($request->has('spmb_fee_paid')) {
            \App\Models\SPMBPayment::create([
                'registration_id' => $registration->id,
                'type' => 'spmb_fee',
                'amount' => 100000,
                'payment_method' => 'ADMIN_ADD',
                'payment_reference' => 'ADMIN-SPMB-' . time() . '-' . $registration->id,
                'tripay_reference' => 'ADMIN-' . time() . '-' . rand(1000, 9999),
                'status' => 'paid',
                'paid_at' => now()
            ]);
        }

        return redirect()->route('manage.spmb.index')
            ->with('success', 'Pendaftar baru berhasil ditambahkan. Nomor pendaftaran: ' . $registration->nomor_pendaftaran);
    }

    /**
     * Print registration form
     */
    public function printForm($id)
    {
        $registration = SPMBRegistration::with(['kejuruan'])->findOrFail($id);
        $schoolProfile = currentSchool() ?? \App\Models\School::first();
        $printFields = \App\Models\SPMBFormSettings::getPrintFieldsBySection();
        
        return view('admin.spmb.print-form', compact('registration', 'schoolProfile', 'printFields'));
    }

    /**
     * Show edit documents page for admin
     */
    public function editDocuments($id)
    {
        $registration = SPMBRegistration::with(['documents'])->findOrFail($id);
        $schoolProfile = currentSchool() ?? \App\Models\School::first();
        
        return view('admin.spmb.edit-documents', compact('registration', 'schoolProfile'));
    }

    /**
     * Update documents for admin
     */
    public function updateDocuments(Request $request, $id)
    {
        $registration = SPMBRegistration::findOrFail($id);
        
        $request->validate([
            'documents.kk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'documents.akte_lahir' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'documents.ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'documents.skl' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'documents.foto' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $documents = $request->file('documents', []);
        
        foreach ($documents as $type => $file) {
            if ($file) {
                // Delete existing document of this type
                $existingDoc = $registration->documents()->where('document_type', $type)->first();
                if ($existingDoc) {
                    // Delete file from storage
                    if (\Storage::exists($existingDoc->file_path)) {
                        \Storage::delete($existingDoc->file_path);
                    }
                    $existingDoc->delete();
                }
                
                // Upload new file
                $filename = time() . '_' . $type . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('spmb/documents', $filename, 'public');
                
                // Create new document record
                $registration->documents()->create([
                    'document_type' => $type,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'status' => 'pending'
                ]);
            }
        }

        return redirect()->route('manage.spmb.show', $registration->id)
            ->with('success', 'Dokumen berhasil diperbarui!');
    }

    /**
     * Show edit payment registration page for admin
     */
    public function editPaymentRegistration($id)
    {
        $registration = SPMBRegistration::with(['payments'])->findOrFail($id);
        $schoolProfile = currentSchool() ?? \App\Models\School::first();
        
        return view('admin.spmb.edit-payment-registration', compact('registration', 'schoolProfile'));
    }

    /**
     * Show edit payment SPMB page for admin
     */
    public function editPaymentSpmb($id)
    {
        $registration = SPMBRegistration::with(['payments'])->findOrFail($id);
        $schoolProfile = currentSchool() ?? \App\Models\School::first();
        
        // Get gateway information from setup_gateways table
        $gatewayInfo = \DB::table('setup_gateways')->first();
        $settings = \App\Models\SPMBSettings::where('pendaftaran_dibuka', true)->first();
        
        return view('admin.spmb.edit-payment-spmb', compact('registration', 'schoolProfile', 'gatewayInfo', 'settings'));
    }

    /**
     * Update payment
     */
    public function updatePayment(Request $request, $id)
    {
        $payment = \App\Models\SPMBPayment::findOrFail($id);
        
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,transfer,online',
            'status' => 'required|string|in:pending,paid,expired,failed'
        ]);

        $payment->update([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'status' => $request->status,
            'updated_at' => now()
        ]);

        // Update registration status based on payment type and status
        $registration = $payment->registration;
        if ($payment->type === 'registration_fee' && $request->status === 'paid') {
            $registration->update(['registration_fee_paid' => true]);
        } elseif ($payment->type === 'spmb_fee' && $request->status === 'paid') {
            $registration->update(['spmb_fee_paid' => true]);
        }

        return redirect()->route('manage.spmb.show', $registration->id)
            ->with('success', 'Pembayaran berhasil diperbarui!');
    }

    /**
     * Delete payment
     */
    public function deletePayment($id)
    {
        $payment = \App\Models\SPMBPayment::findOrFail($id);
        $registration = $payment->registration;
        
        // Update registration status if payment was paid
        if ($payment->status === 'paid') {
            if ($payment->type === 'registration_fee') {
                $registration->update(['registration_fee_paid' => false]);
            } elseif ($payment->type === 'spmb_fee') {
                $registration->update(['spmb_fee_paid' => false]);
            }
        }

        $payment->delete();

        return redirect()->route('manage.spmb.show', $registration->id)
            ->with('success', 'Pembayaran berhasil dihapus!');
    }

    /**
     * Print invoice
     */
    public function printInvoice($id)
    {
        $payment = \App\Models\SPMBPayment::with(['registration'])->findOrFail($id);
        $schoolProfile = currentSchool() ?? \App\Models\School::first();
        
        return view('admin.spmb.print-invoice', compact('payment', 'schoolProfile'));
    }

    /**
     * Export SPMB data to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = SPMBRegistration::with(['payments', 'documents']);
        
        // Filter by status pendaftaran if provided
        if ($request->has('status_pendaftaran') && $request->status_pendaftaran != '') {
            $query->where('status', $request->status_pendaftaran);
        }
        
        // Filter by kejuruan if provided
        if ($request->has('kejuruan_id') && $request->kejuruan_id != '') {
            $query->where('kejuruan_id', $request->kejuruan_id);
        }
        
        // Filter by search if provided
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('nomor_pendaftaran', 'like', "%{$search}%");
            });
        }
        
        $registrations = $query->orderBy('created_at', 'desc')->get();
        $schoolProfile = currentSchool() ?? \App\Models\School::first();
        
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadView('admin.spmb.export-pdf', compact('registrations', 'schoolProfile'));
        
        $filename = 'spmb_data_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export SPMB data to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = SPMBRegistration::with(['payments', 'documents']);
        
        // Filter by status pendaftaran if provided
        if ($request->has('status_pendaftaran') && $request->status_pendaftaran != '') {
            $query->where('status', $request->status_pendaftaran);
        }
        
        // Filter by kejuruan if provided
        if ($request->has('kejuruan_id') && $request->kejuruan_id != '') {
            $query->where('kejuruan_id', $request->kejuruan_id);
        }
        
        // Filter by search if provided
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('nomor_pendaftaran', 'like', "%{$search}%");
            });
        }
        
        $registrations = $query->orderBy('created_at', 'desc')->get();
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SPMBRegistrationExport($registrations), 'spmb_data_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Create payment for SPMB fee
     */
    public function createPaymentSpmb(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:transfer_manual,tripay',
            'notes' => 'nullable|string|max:1000',
        ]);

        $registration = SPMBRegistration::findOrFail($id);

        // Note: spmb_payments table doesn't have proof_of_payment field
        // File upload functionality removed for now

        // Get settings for default amount if not provided
        $settings = \App\Models\SPMBSettings::where('pendaftaran_dibuka', true)->first();
        $amount = $request->amount ?: ($settings ? $settings->biaya_spmb : 200000);

        // Create payment record
        $payment = new \App\Models\SPMBPayment();
        $payment->registration_id = $registration->id;
        $payment->type = 'spmb_fee';
        $payment->amount = $amount;
        $payment->payment_method = $request->payment_method;
        $payment->payment_reference = $request->payment_reference;
        $payment->notes = $request->notes;
        $payment->status = 'pending';
        $payment->save();

        // Update registration status if payment is approved
        if ($request->status === 'paid') {
            $registration->spmb_fee_paid = true;
            $registration->save();
        }

        return redirect()->route('manage.spmb.edit-payment-spmb', $registration->id)
            ->with('success', 'Pembayaran SPMB berhasil dibuat. Status: Pending');
    }

    /**
     * View payment proof
     */
    public function viewPaymentProof($id)
    {
        $payment = \App\Models\SPMBPayment::findOrFail($id);
        
        // If payment proof file exists, display it
        if ($payment->proof_of_payment && Storage::disk('public')->exists($payment->proof_of_payment)) {
            $filePath = Storage::disk('public')->path($payment->proof_of_payment);
            $mimeType = mime_content_type($filePath);
            
            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($payment->proof_of_payment) . '"'
            ]);
        }
        
        // Create a simple HTML page showing payment details if no proof file
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Detail Pembayaran</title>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .container { max-width: 600px; margin: 0 auto; }
                .header { background: #008060; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .row { display: flex; justify-content: space-between; margin: 10px 0; }
                .label { font-weight: bold; }
                .status { padding: 5px 10px; border-radius: 5px; color: white; }
                .status.pending { background: #ffc107; }
                .status.paid { background: #28a745; }
                .status.failed { background: #dc3545; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Detail Pembayaran SPMB</h2>
                </div>
                <div class="content">
                    <div class="row">
                        <span class="label">Jenis Pembayaran:</span>
                        <span>' . ($payment->type === 'registration_fee' ? 'Biaya Pendaftaran' : 'Biaya SPMB') . '</span>
                    </div>
                    <div class="row">
                        <span class="label">Jumlah:</span>
                        <span>Rp ' . number_format($payment->amount, 0, ',', '.') . '</span>
                    </div>
                    <div class="row">
                        <span class="label">Metode:</span>
                        <span>' . ucfirst(str_replace('_', ' ', $payment->payment_method)) . '</span>
                    </div>
                    <div class="row">
                        <span class="label">Status:</span>
                        <span class="status ' . $payment->status . '">' . ucfirst($payment->status) . '</span>
                    </div>
                    <div class="row">
                        <span class="label">Referensi:</span>
                        <span>' . $payment->payment_reference . '</span>
                    </div>
                    <div class="row">
                        <span class="label">Tanggal:</span>
                        <span>' . $payment->created_at->format('d/m/Y H:i') . '</span>
                    </div>
                    ' . ($payment->notes ? '<div class="row"><span class="label">Catatan:</span><span>' . $payment->notes . '</span></div>' : '') . '
                    <div class="row">
                        <span class="label">Bukti Pembayaran:</span>
                        <span>Tidak ada bukti pembayaran</span>
                    </div>
                </div>
            </div>
        </body>
        </html>';
        
        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Get payment proof data for modal (JSON response)
     */
    public function getPaymentProof($id)
    {
        try {
            \Log::info('getPaymentProof called with ID: ' . $id);
            
            $payment = \App\Models\SPMBPayment::findOrFail($id);
            \Log::info('Payment found: ' . $payment->id);
            
            $gatewayInfo = \DB::table('setup_gateways')->first();
            \Log::info('Gateway info: ' . ($gatewayInfo ? 'found' : 'not found'));
            
            $response = [
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'type' => $payment->type,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_reference' => $payment->payment_reference,
                    'status' => $payment->status,
                    'notes' => $payment->notes,
                    'proof_of_payment' => $payment->proof_of_payment,
                    'created_at' => $payment->created_at,
                    'paid_at' => $payment->paid_at,
                ],
                'gatewayInfo' => $gatewayInfo ? [
                    'nama_bank' => $gatewayInfo->nama_bank,
                    'norek_bank' => $gatewayInfo->norek_bank,
                    'nama_rekening' => $gatewayInfo->nama_rekening,
                ] : null
            ];
            
            \Log::info('Response prepared: ' . json_encode($response));
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('getPaymentProof error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test payment proof endpoint
     */
    public function testPaymentProof($id)
    {
        try {
            \Log::info('testPaymentProof called with ID: ' . $id);
            
            $payment = \App\Models\SPMBPayment::findOrFail($id);
            \Log::info('Payment found: ' . $payment->id);
            \Log::info('Proof of payment: ' . ($payment->proof_of_payment ?? 'NULL'));
            
            $gatewayInfo = \DB::table('setup_gateways')->first();
            \Log::info('Gateway info: ' . ($gatewayInfo ? 'found' : 'not found'));
            
            $response = [
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'type' => $payment->type,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_reference' => $payment->payment_reference,
                    'status' => $payment->status,
                    'notes' => $payment->notes,
                    'proof_of_payment' => $payment->proof_of_payment,
                    'created_at' => $payment->created_at,
                    'paid_at' => $payment->paid_at,
                ],
                'gatewayInfo' => $gatewayInfo ? [
                    'nama_bank' => $gatewayInfo->nama_bank,
                    'norek_bank' => $gatewayInfo->norek_bank,
                    'nama_rekening' => $gatewayInfo->nama_rekening,
                ] : null
            ];
            
            \Log::info('Response prepared: ' . json_encode($response));
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('testPaymentProof error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payment
     */
    public function verifyPayment($id)
    {
        $payment = \App\Models\SPMBPayment::findOrFail($id);
        
        // Update payment status
        $payment->status = 'paid';
        $payment->paid_at = now();
        $payment->save();
        
        // Update registration payment status
        $registration = $payment->registration;
        if ($payment->type === 'registration_fee') {
            $registration->registration_fee_paid = true;
        } elseif ($payment->type === 'spmb_fee') {
            $registration->spmb_fee_paid = true;
        }
        $registration->save();
        
        return redirect()->back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    /**
     * Reject payment
     */
    public function rejectPayment(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $payment = \App\Models\SPMBPayment::findOrFail($id);
        
        // Update payment status
        $payment->status = 'failed';
        $payment->notes = $payment->notes . "\n\nRejection Reason: " . $request->rejection_reason;
        $payment->save();
        
        return redirect()->back()->with('success', 'Pembayaran berhasil ditolak.');
    }

    /**
     * Show transfer to students page
     */
    public function transferToStudents()
    {
        // Get accepted registrations that haven't been transferred yet
        $registrations = SPMBRegistration::where('status_pendaftaran', 'diterima')
            ->where('step', 6)
            ->where('status', 'completed')
            ->where('transferred_to_students', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available classes
        $classes = \App\Models\ClassModel::orderBy('class_name')->get();
        
        // Get available periods
        $periods = \App\Models\Period::orderBy('period_start', 'desc')->get();

        return view('admin.spmb.transfer-to-students', compact('registrations', 'classes', 'periods'));
    }

    /**
     * Process transfer to students
     */
    public function processTransferToStudents(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array|min:1',
            'registration_ids.*' => 'exists:spmb_registrations,id',
            'class_id' => 'required|exists:class_models,class_id',
            'period_id' => 'required|exists:periods,period_id',
            'create_spmb_bill' => 'nullable|boolean' // Opsi untuk membuat tagihan SPMB
        ]);

        try {
            $classId = $request->class_id;
            $periodId = $request->period_id;
            $registrationIds = $request->registration_ids;
            $createSpmbBill = $request->has('create_spmb_bill') && $request->create_spmb_bill;
            
            $transferredCount = 0;
            $errors = [];
            $billsCreated = 0;

            // Setup untuk tagihan SPMB (jika opsi diaktifkan)
            $spmbPaymentId = null;
            if ($createSpmbBill) {
                $spmbPaymentId = $this->getOrCreateSpmbPayment($periodId);
            }

            foreach ($registrationIds as $registrationId) {
                $registration = SPMBRegistration::findOrFail($registrationId);
                
                // Check if already transferred
                $existingStudent = \App\Models\Student::where('student_phone', $registration->phone)->first();
                if ($existingStudent) {
                    $errors[] = "Siswa {$registration->name} sudah ada di sistem";
                    continue;
                }

                // Generate NIS (Nomor Induk Siswa)
                $currentYear = date('Y');
                $lastNIS = \App\Models\Student::where('student_nis', 'like', $currentYear . '%')
                    ->orderBy('student_nis', 'desc')
                    ->value('student_nis');
                
                $nextNumber = $lastNIS ? (intval(substr($lastNIS, -4)) + 1) : 1;
                $nis = $currentYear . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                // Convert gender from form data to database format
                $gender = $registration->form_data['gender'] ?? 'male';
                $studentGender = ($gender === 'female') ? 'P' : 'L';

                // Get class to get school_id
                $class = \App\Models\ClassModel::find($classId);
                $currentSchoolId = currentSchoolId();
                $schoolId = $class->school_id ?? $currentSchoolId;

                if (!$schoolId) {
                    $errors[] = "Siswa {$registration->name}: School ID tidak ditemukan";
                    continue;
                }

                // Create student record
                $student = \App\Models\Student::create([
                    'student_nis' => $nis,
                    'student_full_name' => $registration->name,
                    'student_phone' => $registration->phone,
                    'class_class_id' => $classId,
                    'school_id' => $schoolId,
                    'student_status' => 1, // Active
                    'student_gender' => $studentGender,
                    'student_born_date' => $registration->form_data['birth_date'] ?? null,
                    'student_born_place' => $registration->form_data['birth_place'] ?? null,
                    'student_address' => $registration->form_data['address'] ?? null,
                    'student_name_of_mother' => $registration->form_data['parent_name'] ?? null,
                    'student_parent_phone' => $registration->form_data['parent_phone'] ?? null,
                ]);

                // Buat tagihan SPMB jika opsi diaktifkan dan siswa belum membayar
                if ($createSpmbBill && $spmbPaymentId && !$registration->spmb_fee_paid) {
                    $spmbFee = WaveHelper::getSpmbFee($registration);
                    
                    if ($spmbFee > 0) {
                        // Ambil payment untuk cek jenisnya
                        $payment = Payment::find($spmbPaymentId);
                        
                        if ($payment->payment_type === 'BEBAS') {
                            // Cek apakah sudah ada tagihan bebas untuk biaya SPMB
                            $existingBebas = Bebas::where('student_student_id', $student->student_id)
                                ->where('payment_payment_id', $spmbPaymentId)
                                ->first();
                            
                            if (!$existingBebas) {
                                Bebas::create([
                                    'student_student_id' => $student->student_id,
                                    'payment_payment_id' => $spmbPaymentId,
                                    'bebas_bill' => $spmbFee,
                                    'bebas_total_pay' => 0, // Belum dibayar
                                    'bebas_desc' => 'Biaya SPMB - ' . ($registration->nomor_pendaftaran ?? 'No. ' . $registration->id),
                                    'bebas_input_date' => now(),
                                    'bebas_last_update' => now()
                                ]);
                                $billsCreated++;
                            }
                        } elseif ($payment->payment_type === 'BULAN') {
                            // Untuk bulanan, buat tagihan di bulan pertama (month_id = 1)
                            $existingBulan = \DB::table('bulan')
                                ->where('student_student_id', $student->student_id)
                                ->where('payment_payment_id', $spmbPaymentId)
                                ->where('month_month_id', 1)
                                ->first();
                            
                            if (!$existingBulan) {
                                \DB::table('bulan')->insert([
                                    'student_student_id' => $student->student_id,
                                    'payment_payment_id' => $spmbPaymentId,
                                    'month_month_id' => 1, // Bulan pertama
                                    'bulan_bill' => $spmbFee,
                                    'bulan_status' => 0, // Belum dibayar
                                    'bulan_input_date' => now(),
                                    'bulan_last_update' => now()
                                ]);
                                $billsCreated++;
                            }
                        }
                    }
                }

                // Mark as transferred (add this field to spmb_registrations table)
                $registration->update(['transferred_to_students' => true]);
                
                $transferredCount++;
            }

            $message = "Berhasil mentransfer {$transferredCount} siswa ke tabel students.";
            if ($createSpmbBill && $billsCreated > 0) {
                $message .= " Tagihan SPMB dibuat untuk {$billsCreated} siswa.";
            }
            if (!empty($errors)) {
                $message .= " Error: " . implode(', ', $errors);
            }

            return redirect()->route('manage.spmb.transfer-to-students')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get Payment record yang aktif untuk SPMB berdasarkan period
     */
    private function getOrCreateSpmbPayment($periodId)
    {
        $currentSchoolId = currentSchoolId();
        
        if (!$currentSchoolId) {
            throw new \Exception('School ID tidak ditemukan. Silakan pilih sekolah terlebih dahulu.');
        }

        // Cari payment yang sudah dikonfigurasi untuk SPMB dengan period yang sesuai
        $payment = Payment::where('is_for_spmb', true)
            ->where('period_period_id', $periodId)
            ->where('school_id', $currentSchoolId)
            ->first();

        if (!$payment) {
            throw new \Exception('Payment untuk SPMB belum dikonfigurasi. Silakan buat payment dengan opsi "Aktifkan untuk SPMB" di menu Setting Tarif Pos untuk period ini.');
        }

        return $payment->payment_id;
    }
}
