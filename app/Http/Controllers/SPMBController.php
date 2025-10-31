<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\SPMBRegistration;
use App\Models\SPMBDocument;
use App\Models\SPMBPayment;
use App\Models\SchoolProfile;
use App\Services\IpaymuService;
use App\Services\WhatsAppService;
use App\Helpers\WaveHelper;

class SPMBController extends Controller
{
    protected $ipaymuServiceEnv;    // For Step 2 (internal system - QRIS)
    protected $ipaymuServiceDb;     // For Step 5 (student payment - registration fee)
    protected $whatsappService;

    public function __construct()
    {
        // Step 2 uses ENV config (QRIS - internal system, same as addon/subscription)
        $this->ipaymuServiceEnv = new IpaymuService(true);
        
        // Step 5 uses database config (registration fee - student payment)
        $this->ipaymuServiceDb = new IpaymuService(false);
        
        $this->whatsappService = new WhatsAppService();
    }

    /**
     * Show landing page
     */
    public function index()
    {
        $schoolProfile = SchoolProfile::first();
        $gateway = \App\Models\SetupGateway::first();
        
        // Get WhatsApp number from gateway (wa_gateway) or fallback to school phone (no_telp)
        $whatsappNumber = $gateway->wa_gateway ?? $schoolProfile->no_telp ?? '6281234567890';
        
        // Format nomor (remove leading 0, add 62 if needed)
        $whatsappNumber = preg_replace('/^0/', '', $whatsappNumber); // Remove leading 0
        if (!preg_match('/^62/', $whatsappNumber)) {
            $whatsappNumber = '62' . ltrim($whatsappNumber, '62'); // Ensure starts with 62
        }
        
        return view('spmb.landing', compact('schoolProfile', 'whatsappNumber'));
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        $schoolProfile = SchoolProfile::first();
        return view('spmb.login', compact('schoolProfile'));
    }

    /**
     * Show register form
     */
    public function showRegister()
    {
        $schoolProfile = SchoolProfile::first();
        return view('spmb.register', compact('schoolProfile'));
    }

    /**
     * Process registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:spmb_registrations,phone'
        ]);

        // Automatically generate password from last 6 digits of phone
        $password = substr($request->phone, -6);

        $registration = SPMBRegistration::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => $password,
            'step' => 1,
            'status' => 'active'
        ]);

        // Store in session
        Session::put('spmb_registration_id', $registration->id);
        Session::put('spmb_name', $registration->name);
        Session::put('spmb_phone', $registration->phone);

        // Update step to 2 after successful registration
        $registration->update(['step' => 2]);

        return redirect()->route('spmb.step', ['step' => 2])
            ->with('success', 'Pendaftaran berhasil! Silakan lanjutkan ke langkah berikutnya.');
    }

    /**
     * Process login
     */
    public function login(Request $request)
    {
        // Trim whitespace dari input
        $phone = trim($request->phone);
        $password = trim($request->password);

        \Log::info('=== SPMB Login Attempt ===', [
            'phone_raw' => $request->phone,
            'phone_trimmed' => $phone,
            'phone_length' => strlen($phone),
            'password_length' => strlen($password),
            'password_has_whitespace' => strlen($password) !== strlen(trim($password))
        ]);

        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string'
        ]);

        // Login untuk SEMUA status (tidak terbatas hanya 'active')
        $registration = SPMBRegistration::where('phone', $phone)->first();

        \Log::info('User lookup result', [
            'phone' => $phone,
            'user_found' => $registration ? 'YES' : 'NO',
            'user_id' => $registration ? $registration->id : null,
            'user_status' => $registration ? $registration->status : null,
            'phone_in_db' => $registration ? $registration->phone : null
        ]);

        if (!$registration) {
            \Log::warning('Login failed: User not found', ['phone' => $phone]);
            
            // Log all users untuk debugging
            $allUsers = SPMBRegistration::all(['id', 'phone', 'name', 'status']);
            \Log::info('All users in database', ['users' => $allUsers->toArray()]);
            
            return back()->withErrors([
                'phone' => 'Nomor HP atau password salah.'
            ])->withInput($request->only('phone'));
        }

        $passwordCheck = $registration->checkPassword($password);
        \Log::info('Password check result', [
            'phone' => $phone,
            'password_valid' => $passwordCheck ? 'YES' : 'NO',
            'password_input_length' => strlen($password),
            'stored_password_hash' => substr($registration->password, 0, 30) . '...',
            'hash_check_direct' => \Hash::check($password, $registration->password) ? 'YES' : 'NO'
        ]);

        if (!$passwordCheck) {
            \Log::warning('Login failed: Invalid password', [
                'phone' => $phone,
                'user_id' => $registration->id,
                'password_length' => strlen($password)
            ]);
            return back()->withErrors([
                'phone' => 'Nomor HP atau password salah.'
            ])->withInput($request->only('phone'));
        }

        \Log::info('Login successful', [
            'phone' => $phone,
            'user_id' => $registration->id,
            'name' => $registration->name,
            'status' => $registration->status,
            'status_pendaftaran' => $registration->status_pendaftaran,
            'step' => $registration->step
        ]);

        // Store in session - ALLOW ALL STATUS (pending, active, etc)
        Session::put('spmb_registration_id', $registration->id);
        Session::put('spmb_name', $registration->name);
        Session::put('spmb_phone', $registration->phone);
        Session::put('spmb_status', $registration->status);
        Session::put('spmb_status_pendaftaran', $registration->status_pendaftaran);

        \Log::info('Session stored, redirecting to dashboard', [
            'session_id' => $registration->id,
            'redirect_to' => 'spmb.dashboard'
        ]);

        return redirect()->route('spmb.dashboard');
    }

    /**
     * Show dashboard
     */
    public function dashboard()
    {
        $registrationId = Session::get('spmb_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('spmb.login');
        }

        $registration = SPMBRegistration::findOrFail($registrationId);
        $schoolProfile = SchoolProfile::first();
        $gateway = \App\Models\SetupGateway::first();
        
        // Get WhatsApp number from gateway (wa_gateway) or fallback to school phone (no_telp)
        $whatsappNumber = $gateway->wa_gateway ?? $schoolProfile->no_telp ?? '6281234567890';
        
        // Format nomor (remove leading 0, add 62 if needed)
        $whatsappNumber = preg_replace('/^0/', '', $whatsappNumber); // Remove leading 0
        if (!preg_match('/^62/', $whatsappNumber)) {
            $whatsappNumber = '62' . ltrim($whatsappNumber, '62'); // Ensure starts with 62
        }
        
        return view('spmb.dashboard', compact('registration', 'schoolProfile', 'whatsappNumber'));
    }

    /**
     * Show step form
     */
    public function showStep($step)
    {
        $registrationId = Session::get('spmb_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('spmb.login');
        }

        $registration = SPMBRegistration::findOrFail($registrationId);
        
        if ($registration->step < $step) {
            return redirect()->route('spmb.step', ['step' => $registration->step]);
        }

        // For step 3, get dynamic form settings
        if ($step == 3) {
            $formSettings = \App\Models\SPMBFormSettings::getActiveFieldsBySection();
            $kejuruans = \App\Models\SPMBKejuruan::getActive();
            return view('spmb.steps.step' . $step, compact('registration', 'formSettings', 'kejuruans'));
        }

        // For step 2 and 5, get gateway information and settings
        if ($step == 2 || $step == 5) {
            $gatewayInfo = \DB::table('setup_gateways')->first();
            $settings = \App\Models\SPMBSettings::where('pendaftaran_dibuka', true)->first();
            
            // Get additional fees for step 5 (SPMB fee payment)
            if ($step == 5) {
                $additionalFees = \App\Helpers\WaveHelper::getAvailableAdditionalFees($registration);
            } else {
                $additionalFees = collect();
            }
            
            return view('spmb.steps.step' . $step, compact('registration', 'gatewayInfo', 'settings', 'additionalFees'));
        }

        return view('spmb.steps.step' . $step, compact('registration'));
    }

    /**
     * Process step 2 - Registration fee payment
     */
    public function processStep2(Request $request)
    {
        \Log::info('=== PROCESS STEP 2 START ===', [
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_ip' => $request->ip(),
            'has_csrf' => $request->hasHeader('X-CSRF-TOKEN') || $request->has('_token'),
            'all_request_data' => $request->all(),
            'session_data' => [
                'registration_id' => Session::get('spmb_registration_id'),
                'name' => Session::get('spmb_name'),
                'phone' => Session::get('spmb_phone')
            ]
        ]);
        
        $registrationId = Session::get('spmb_registration_id');
        
        if (!$registrationId) {
            \Log::error('No registration ID in session', [
                'session_all' => Session::all()
            ]);
            return redirect()->route('spmb.login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }
        
        $registration = SPMBRegistration::findOrFail($registrationId);

        \Log::info('Registration found', [
            'registration_id' => $registration->id,
            'current_step' => $registration->step
        ]);

        if ($registration->step != 2) {
            \Log::warning('Not on step 2', [
                'current_step' => $registration->step,
                'expected_step' => 2
            ]);
            return redirect()->route('spmb.step', ['step' => $registration->step]);
        }

        try {
            \Log::info('Creating iPaymu SPMB payment (Step 2 - using ENV config)...');
            
            // Use iPaymu with ENV config for Step 2 (internal system QRIS)
            $ipaymuResponse = $this->ipaymuServiceEnv->createSPMBPayment([
                'registration_id' => $registration->id,
                'amount' => WaveHelper::getStep2QrisFee(),
                'method' => 'qris', // Default QRIS for Step 2
                'product_name' => 'Step 2 Registration Fee',
                'customer_name' => $registration->name, // Fixed: use 'name' column
                'customer_phone' => $registration->phone, // Fixed: use 'phone' column
                'customer_email' => $registration->email ?? 'spmb@sppqu.com',
                'return_url' => route('spmb.payment.success'),
                'callback_url' => url('/api/manage/ipaymu/callback')
            ]);

            \Log::info('iPaymu SPMB response received', [
                'response' => $ipaymuResponse
            ]);

            if (!$ipaymuResponse || !isset($ipaymuResponse['success']) || !$ipaymuResponse['success']) {
                \Log::error('SPMB Payment failed', [
                    'registration_id' => $registration->id,
                    'response' => $ipaymuResponse,
                    'error_code' => $ipaymuResponse['error_code'] ?? null
                ]);
                
                $errorMessage = 'Gagal membuat pembayaran iPaymu: ' . ($ipaymuResponse['message'] ?? 'Silakan coba lagi.');
                
                // Add specific message for 401 error
                if (isset($ipaymuResponse['error_code']) && $ipaymuResponse['error_code'] == 401) {
                    $errorMessage .= ' (Konfigurasi iPaymu belum diatur atau tidak valid)';
                }
                
                return back()->with('error', $errorMessage);
            }

            // Get QR code from response
            $qrCode = $ipaymuResponse['qr_string'] 
                ?? $ipaymuResponse['qr_code'] 
                ?? null;

            // Save payment record
            $payment = SPMBPayment::create([
                'registration_id' => $registration->id,
                'type' => 'registration_fee',
                'amount' => WaveHelper::getStep2QrisFee(),
                'payment_method' => 'QRIS',
                'payment_reference' => $ipaymuResponse['reference_id'],
                'tripay_reference' => $ipaymuResponse['transaction_id'] ?? $ipaymuResponse['session_id'],
                'status' => 'pending',
                'payment_url' => $ipaymuResponse['payment_url'],
                'qr_code' => $qrCode,
                'expired_at' => now()->addHours(24)
            ]);

            \Log::info('SPMB Payment created successfully with iPaymu', [
                'payment_id' => $payment->id,
                'registration_id' => $registration->id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'has_qr_code' => !empty($payment->qr_code) ? 'YES' : 'NO',
                'qr_code_length' => !empty($payment->qr_code) ? strlen($payment->qr_code) : 0,
                'payment_url' => $payment->payment_url,
                'reference_id' => $payment->payment_reference
            ]);

            return redirect()->route('spmb.payment', ['id' => $payment->id]);
        } catch (\Exception $e) {
            \Log::error('SPMB Payment error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback: create mock payment
            $payment = SPMBPayment::create([
                'registration_id' => $registration->id,
                'type' => 'registration_fee',
                'amount' => WaveHelper::getStep2QrisFee(),
                'payment_method' => 'QRIS',
                'payment_reference' => 'MOCK-REG-' . time() . '-' . $registration->id,
                'tripay_reference' => 'MOCK-' . time() . '-' . rand(1000, 9999),
                'status' => 'pending',
                'payment_url' => 'https://tripay.co.id/checkout/mock',
                'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                'expired_at' => now()->addHours(24)
            ]);

            return redirect()->route('spmb.payment', ['id' => $payment->id]);
        }
    }

    /**
     * Process step 3 - Complete form
     */
    public function processStep3(Request $request)
    {
        $registrationId = Session::get('spmb_registration_id');
        $registration = SPMBRegistration::findOrFail($registrationId);

        if ($registration->step != 3) {
            return redirect()->route('spmb.step', ['step' => $registration->step]);
        }

        // Get dynamic form settings for validation
        $formSettings = \App\Models\SPMBFormSettings::getActiveFields();
        $validationRules = [];
        
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
        
        // Add kejuruan validation - only required if there are active kejuruans
        $hasKejuruans = \App\Models\SPMBKejuruan::getActive()->count() > 0;
        if ($hasKejuruans) {
            $validationRules['kejuruan_id'] = 'required|exists:s_p_m_b_kejuruans,id';
        } else {
            $validationRules['kejuruan_id'] = 'nullable|exists:s_p_m_b_kejuruans,id';
        }

        $request->validate($validationRules);

        $formData = $request->except(['_token', 'kejuruan_id']);
        $registration->update([
            'form_data' => $formData,
            'kejuruan_id' => $request->kejuruan_id ?? null,
            'step' => 4
        ]);

        // Generate nomor pendaftaran setelah berhasil mengisi formulir
        $registration->generateNomorPendaftaran();

        return redirect()->route('spmb.step', ['step' => 4])
            ->with('success', 'Formulir berhasil disimpan! Nomor pendaftaran Anda: ' . $registration->nomor_pendaftaran);
    }

    /**
     * Process step 4 - Upload documents
     */
    public function processStep4(Request $request)
    {
        $registrationId = Session::get('spmb_registration_id');
        $registration = SPMBRegistration::findOrFail($registrationId);

        if ($registration->step != 4) {
            return redirect()->route('spmb.step', ['step' => $registration->step]);
        }

        $request->validate([
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $documentTypes = [
            'kk' => 'Kartu Keluarga',
            'akte_lahir' => 'Akte Kelahiran',
            'ijazah' => 'Ijazah',
            'skl' => 'Surat Keterangan Lulus',
            'foto' => 'Foto',
            'raport' => 'Raport'
        ];

        $action = $request->input('action', 'upload');
        
        if ($action === 'skip') {
            $registration->update(['step' => 5]);
            return redirect()->route('spmb.step', ['step' => 5])
                ->with('info', 'Langkah upload dokumen dilewati. Anda dapat mengupload dokumen nanti.');
        }
        
        $uploadedCount = 0;
        $documents = $request->file('documents', []);
        
        foreach ($documents as $type => $file) {
            if ($file) {
                // Delete existing document of this type
                $existingDoc = $registration->documents()->where('document_type', $type)->first();
                if ($existingDoc) {
                    // Delete file from storage
                    $existingFilePath = storage_path('app/public/' . $existingDoc->file_path);
                    if (file_exists($existingFilePath)) {
                        unlink($existingFilePath);
                    }
                    $existingDoc->delete();
                }

                $fileName = $registration->id . '_' . $type . '_' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('spmb/documents', $fileName, 'public');

                SPMBDocument::create([
                    'registration_id' => $registration->id,
                    'document_type' => $type,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'status' => 'pending'
                ]);
                $uploadedCount++;
            }
        }

        $registration->update(['step' => 5]);

        if ($uploadedCount > 0) {
            return redirect()->route('spmb.step', ['step' => 5])
                ->with('success', 'Dokumen berhasil diupload!');
        } else {
            return redirect()->route('spmb.step', ['step' => 5])
                ->with('info', 'Langkah upload dokumen dilewati. Anda dapat mengupload dokumen nanti.');
        }
    }

    /**
     * Process step 5 - SPMB fee payment
     */
    public function processStep5(Request $request)
    {
        $registrationId = Session::get('spmb_registration_id');
        $registration = SPMBRegistration::findOrFail($registrationId);

        if ($registration->step != 5) {
            return redirect()->route('spmb.step', ['step' => $registration->step]);
        }

        // Create payment for SPMB fee via iPaymu (Step 5 - using Database config)
        $ipaymuResponse = $this->ipaymuServiceDb->createSPMBPayment([
            'registration_id' => $registration->id,
            'amount' => WaveHelper::getSpmbFee($registration),
            'customer_name' => $registration->name, // Fixed: use 'name' column
            'customer_phone' => $registration->phone, // Fixed: use 'phone' column
            'customer_email' => $registration->email,
            'method' => 'qris', // Default QRIS for SPMB
            'callback_url' => route('api.ipaymu.callback'),
            'return_url' => route('spmb.payment.status', ['registration_id' => $registration->id])
        ]);

        if (!$ipaymuResponse || !$ipaymuResponse['success']) {
            return back()->withErrors(['error' => 'Gagal membuat pembayaran. Silakan coba lagi.']);
        }

        // Save payment record
        $payment = SPMBPayment::create([
            'registration_id' => $registration->id,
            'type' => 'spmb_fee',
            'amount' => WaveHelper::getSpmbFee($registration),
            'payment_method' => 'iPaymu',
            'payment_reference' => $ipaymuResponse['reference_id'],
            'tripay_reference' => $ipaymuResponse['reference_id'], // Keep field name for compatibility
            'status' => 'pending',
            'payment_url' => $ipaymuResponse['payment_url'] ?? null,
            'qr_code' => $ipaymuResponse['qr_code'] ?? null,
            'expired_at' => isset($ipaymuResponse['expired_time']) ? \Carbon\Carbon::parse($ipaymuResponse['expired_time']) : now()->addHours(24)
        ]);

        return redirect()->route('spmb.payment', ['id' => $payment->id]);
    }

    /**
     * Show payment page
     */
    public function showPayment($id)
    {
        $payment = SPMBPayment::findOrFail($id);
        $registrationId = Session::get('spmb_registration_id');
        
        if ($payment->registration_id != $registrationId) {
            return redirect()->route('spmb.dashboard');
        }

        // Check if using mock payment
        if ($payment->tripay_reference && strpos($payment->tripay_reference, 'MOCK-') === 0) {
            return view('spmb.payment-mock', compact('payment'));
        }

        return view('spmb.payment', compact('payment'));
    }

    /**
     * Payment success
     */
    public function paymentSuccess(Request $request)
    {
        $registrationId = Session::get('spmb_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('spmb.login');
        }

        $registration = SPMBRegistration::findOrFail($registrationId);
        
        // Get the latest payment for this registration
        $latestPayment = SPMBPayment::where('registration_id', $registration->id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Check if there's a paid payment
        if (!$latestPayment || $latestPayment->status !== 'paid') {
            \Log::warning('Payment success page accessed but no paid payment found', [
                'registration_id' => $registration->id,
                'latest_payment_status' => $latestPayment ? $latestPayment->status : 'no_payment',
                'payment_id' => $latestPayment ? $latestPayment->id : null
            ]);
            
            // Redirect to dashboard with warning
            return redirect()->route('spmb.dashboard')
                ->with('warning', 'Pembayaran belum terverifikasi. Mohon tunggu beberapa saat atau selesaikan pembayaran Anda.');
        }
        
        \Log::info('Payment success page accessed with valid paid payment', [
            'registration_id' => $registration->id,
            'payment_id' => $latestPayment->id,
            'payment_type' => $latestPayment->type,
            'paid_at' => $latestPayment->paid_at
        ]);

        return view('spmb.payment-success', compact('latestPayment', 'registration'));
    }

    /**
     * Payment callback
     */
    public function paymentCallback(Request $request)
    {
        $reference = $request->reference;
        $status = $request->status;

        $payment = SPMBPayment::where('tripay_reference', $reference)->first();
        
        if ($payment) {
            if ($status === 'PAID') {
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);

                // Update registration
                $registration = $payment->registration;
                if ($payment->type === 'registration_fee') {
                    $registration->update(['registration_fee_paid' => true]);
                } elseif ($payment->type === 'spmb_fee') {
                    $registration->update([
                        'spmb_fee_paid' => true,
                        'step' => 6,
                        'status' => 'completed'
                    ]);
                }
            } elseif ($status === 'EXPIRED') {
                $payment->update(['status' => 'expired']);
            } elseif ($status === 'FAILED') {
                $payment->update(['status' => 'failed']);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Fix step for existing registration
     */
    public function fixStep()
    {
        $registrationId = Session::get('spmb_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('spmb.login');
        }

        $registration = SPMBRegistration::findOrFail($registrationId);
        
        // Force update to step 2 if still at step 1
        if ($registration->step == 1) {
            $registration->update(['step' => 2]);
        }

        return redirect()->route('spmb.step', ['step' => $registration->step])
            ->with('success', 'Step telah diperbaiki! Silakan lanjutkan.');
    }

    /**
     * Skip step 2 (payment) and go to step 3
     * DISABLED: Step-2 QRIS payment is now mandatory
     */
    public function skipStep2()
    {
        // Step-2 QRIS is now mandatory - cannot skip
        abort(403, 'Step-2 QRIS payment is mandatory and cannot be skipped');
        
        /* DISABLED CODE - DO NOT REMOVE (for reference)
        $registrationId = Session::get('spmb_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('spmb.login');
        }

        $registration = SPMBRegistration::findOrFail($registrationId);
        
        \Log::info('Skip Step 2 - Before', [
            'registration_id' => $registration->id,
            'current_step' => $registration->step,
            'registration_fee_paid' => $registration->registration_fee_paid
        ]);
        
        // Force update to step 3 and mark registration fee as paid
        $registration->update([
            'step' => 3,
            'registration_fee_paid' => true
        ]);

        // Create mock payment record for tracking
        $payment = SPMBPayment::create([
            'registration_id' => $registration->id,
            'type' => 'registration_fee',
            'amount' => config('tripay.spmb.registration_fee', 50000),
            'payment_method' => 'SKIP',
            'payment_reference' => 'SKIP-REG-' . time() . '-' . $registration->id,
            'tripay_reference' => 'SKIP-' . time() . '-' . rand(1000, 9999),
            'status' => 'skipped',
            'payment_url' => null,
            'qr_code' => null,
            'expired_at' => null,
            'paid_at' => null
        ]);

        \Log::info('Skip Step 2 - After', [
            'registration_id' => $registration->id,
            'new_step' => $registration->step,
            'registration_fee_paid' => $registration->registration_fee_paid,
            'payment_id' => $payment->id
        ]);

        return redirect()->route('spmb.step', ['step' => 3])
            ->with('success', 'Step 2 (pembayaran) di-skip! Lanjut ke step 3.');
        */
    }

    /**
     * Force skip from step 1 to step 3 (for debugging)
     */
    public function forceSkipToStep3()
    {
        $registrationId = Session::get('spmb_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('spmb.login');
        }

        $registration = SPMBRegistration::findOrFail($registrationId);
        
        \Log::info('Force Skip to Step 3 - Before', [
            'registration_id' => $registration->id,
            'current_step' => $registration->step,
            'registration_fee_paid' => $registration->registration_fee_paid
        ]);
        
        // Force update to step 3 and mark registration fee as paid
        $registration->update([
            'step' => 3,
            'registration_fee_paid' => true
        ]);

        // Create mock payment record for tracking
        $payment = SPMBPayment::create([
            'registration_id' => $registration->id,
            'type' => 'registration_fee',
            'amount' => config('tripay.spmb.registration_fee', 50000),
            'payment_method' => 'FORCE_SKIP',
            'payment_reference' => 'FORCE-SKIP-REG-' . time() . '-' . $registration->id,
            'tripay_reference' => 'FORCE-SKIP-' . time() . '-' . rand(1000, 9999),
            'status' => 'skipped',
            'payment_url' => null,
            'qr_code' => null,
            'expired_at' => null,
            'paid_at' => null
        ]);

        \Log::info('Force Skip to Step 3 - After', [
            'registration_id' => $registration->id,
            'new_step' => $registration->step,
            'registration_fee_paid' => $registration->registration_fee_paid,
            'payment_id' => $payment->id
        ]);

        return redirect()->route('spmb.step', ['step' => 3])
            ->with('success', 'Dipaksa skip ke step 3! Lanjut ke formulir.');
    }

    /**
     * Logout
     */
    public function logout()
    {
        Session::forget(['spmb_registration_id', 'spmb_name', 'spmb_phone']);
        return redirect()->route('spmb.index');
    }

    /**
     * Download registration form
     */
    public function downloadForm()
    {
        $registrationId = Session::get('spmb_registration_id');
        $registration = SPMBRegistration::with(['kejuruan'])->findOrFail($registrationId);
        $schoolProfile = \App\Models\SchoolProfile::first();
        
        return view('spmb.download-form', compact('registration', 'schoolProfile'));
    }

    /**
     * Download document
     */
    public function downloadDocument($id)
    {
        $registrationId = Session::get('spmb_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('spmb.login');
        }

        $document = \App\Models\SPMBDocument::where('id', $id)
            ->where('registration_id', $registrationId)
            ->firstOrFail();

        $filePath = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        return response()->download($filePath, $document->file_name);
    }

    /**
     * View document
     */
    public function viewDocument($id)
    {
        $registrationId = Session::get('spmb_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('spmb.login');
        }

        $document = \App\Models\SPMBDocument::where('id', $id)
            ->where('registration_id', $registrationId)
            ->firstOrFail();

        $filePath = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        return response()->file($filePath);
    }

    /**
     * Delete document
     */
    public function deleteDocument($id)
    {
        $registrationId = Session::get('spmb_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('spmb.login');
        }

        $document = \App\Models\SPMBDocument::where('id', $id)
            ->where('registration_id', $registrationId)
            ->firstOrFail();

        // Delete file from storage
        $filePath = storage_path('app/public/' . $document->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete document record
        $document->delete();

        return redirect()->route('spmb.step', ['step' => 4])
            ->with('success', 'Dokumen berhasil dihapus!');
    }

    /**
     * Process step 2 transfer manual payment
     */
    public function processStep2Transfer(Request $request)
    {
        $request->validate([
            'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'notes' => 'nullable|string|max:1000',
        ]);

        $registrationId = Session::get('spmb_registration_id');
        $registration = SPMBRegistration::findOrFail($registrationId);

        if (!$registration) {
            return redirect()->route('spmb.login')->with('error', 'Sesi tidak valid.');
        }

        // Handle file upload
        $proofOfPaymentPath = null;
        if ($request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');
            $filename = time() . '_' . $registration->id . '_' . $file->getClientOriginalName();
            $proofOfPaymentPath = $file->storeAs('payment_proofs', $filename, 'public');
        }

        // Get gateway info and settings for amount
        $gatewayInfo = \DB::table('setup_gateways')->first();
        $amount = WaveHelper::getRegistrationFee($registration);

        // Check if payment already exists
        $existingPayment = \App\Models\SPMBPayment::where('registration_id', $registration->id)
            ->where('type', 'registration_fee')
            ->first();

        if ($existingPayment) {
            // Update existing payment
            $existingPayment->amount = $amount;
            $existingPayment->payment_method = 'transfer_manual';
            $existingPayment->payment_reference = 'TRF-' . time(); // Auto generate reference
            $existingPayment->notes = $request->notes;
            $existingPayment->proof_of_payment = $proofOfPaymentPath;
            $existingPayment->status = 'pending';
            $existingPayment->save();
        } else {
            // Create new payment record
            $payment = new \App\Models\SPMBPayment();
            $payment->registration_id = $registration->id;
            $payment->type = 'registration_fee';
            $payment->amount = $amount;
            $payment->payment_method = 'transfer_manual';
            $payment->payment_reference = 'TRF-' . time(); // Auto generate reference
            $payment->notes = $request->notes;
            $payment->proof_of_payment = $proofOfPaymentPath;
            $payment->status = 'pending';
            $payment->save();
        }

        // Update registration step
        $registration->step = 3;
        $registration->save();

        return redirect()->route('spmb.dashboard')->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.');
    }

    /**
     * Process step 5 transfer manual payment
     */
    public function processStep5Transfer(Request $request)
    {
        $request->validate([
            'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'notes' => 'nullable|string|max:1000',
        ]);

        $registrationId = Session::get('spmb_registration_id');
        $registration = SPMBRegistration::findOrFail($registrationId);

        if (!$registration) {
            return redirect()->route('spmb.login')->with('error', 'Sesi tidak valid.');
        }

        // Handle file upload
        $proofOfPaymentPath = null;
        if ($request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');
            $filename = time() . '_' . $registration->id . '_' . $file->getClientOriginalName();
            $proofOfPaymentPath = $file->storeAs('payment_proofs', $filename, 'public');
        }

        // Get gateway info and settings for amount
        $gatewayInfo = \DB::table('setup_gateways')->first();
        $amount = WaveHelper::getSpmbFee($registration);

        // Check if payment already exists
        $existingPayment = \App\Models\SPMBPayment::where('registration_id', $registration->id)
            ->where('type', 'spmb_fee')
            ->first();

        if ($existingPayment) {
            // Update existing payment
            $existingPayment->amount = $amount;
            $existingPayment->payment_method = 'transfer_manual';
            $existingPayment->payment_reference = 'TRF-' . time(); // Auto generate reference
            $existingPayment->notes = $request->notes;
            $existingPayment->proof_of_payment = $proofOfPaymentPath;
            $existingPayment->status = 'pending';
            $existingPayment->save();
        } else {
            // Create new payment record
            $payment = new \App\Models\SPMBPayment();
            $payment->registration_id = $registration->id;
            $payment->type = 'spmb_fee';
            $payment->amount = $amount;
            $payment->payment_method = 'transfer_manual';
            $payment->payment_reference = 'TRF-' . time(); // Auto generate reference
            $payment->notes = $request->notes;
            $payment->proof_of_payment = $proofOfPaymentPath;
            $payment->status = 'pending';
            $payment->save();
        }

        // Update registration step
        $registration->step = 6;
        $registration->status = 'completed';
        $registration->save();

        return redirect()->route('spmb.dashboard')->with('success', 'Bukti pembayaran SPMB berhasil dikirim. Menunggu verifikasi admin.');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        $schoolProfile = SchoolProfile::first();
        return view('spmb.forgot-password', compact('schoolProfile'));
    }

    /**
     * Process forgot password request
     */
    public function processForgotPassword(Request $request)
    {
        \Log::info('=== SPMB Password Reset Request ===', [
            'phone' => $request->phone
        ]);

        $request->validate([
            'phone' => 'required|string'
        ]);

        // Cek apakah nomor HP terdaftar
        $registration = SPMBRegistration::where('phone', $request->phone)->first();

        if (!$registration) {
            \Log::warning('Password reset failed: Phone not registered', ['phone' => $request->phone]);
            return back()->with('error', 'Nomor HP tidak terdaftar dalam sistem.');
        }

        \Log::info('User found for password reset', [
            'phone' => $request->phone,
            'name' => $registration->name
        ]);

        // Generate token reset password
        $token = strtoupper(Str::random(6)); // 6 digit kode uppercase
        $expiresAt = now()->addHours(1); // Token berlaku 1 jam

        // Simpan ke database
        \DB::table('spmb_password_resets')->insert([
            'phone' => $request->phone,
            'token' => $token,
            'expires_at' => $expiresAt,
            'used' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        \Log::info('Reset token created', [
            'phone' => $request->phone,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);

        // Kirim notifikasi WhatsApp
        $schoolProfile = SchoolProfile::first();
        $schoolName = $schoolProfile->nama_sekolah ?? 'SPPQU';
        
        $message = "🔑 *Reset Password SPMB*\n\n";
        $message .= "Halo *{$registration->name}*,\n\n";
        $message .= "Anda telah meminta reset password untuk akun SPMB {$schoolName}.\n\n";
        $message .= "Kode Reset Password: *{$token}*\n\n";
        $message .= "Klik link berikut untuk reset password:\n";
        $message .= route('spmb.reset-password', ['token' => $token, 'phone' => $request->phone]) . "\n\n";
        $message .= "⚠️ PENTING: Gunakan NOMOR HP yang sama saat reset password.\n";
        $message .= "Nomor HP Anda: *{$request->phone}*\n\n";
        $message .= "Kode berlaku hingga: " . $expiresAt->format('d/m/Y H:i') . "\n\n";
        $message .= "Jika Anda tidak meminta reset password, abaikan pesan ini.\n\n";
        $message .= "Terima kasih,\n{$schoolName}";

        \Log::info('WhatsApp message prepared', [
            'phone' => $request->phone,
            'message_length' => strlen($message)
        ]);

        try {
            $result = $this->whatsappService->sendMessage($request->phone, $message);
            
            \Log::info('WhatsApp send result', [
                'phone' => $request->phone,
                'result' => $result ? 'SUCCESS' : 'FAILED'
            ]);

            if ($result) {
                return back()->with('success', 'Kode reset password telah dikirim ke WhatsApp Anda. Kode: ' . $token);
            } else {
                return back()->with('error', 'Terjadi kesalahan saat mengirim kode reset. Kode manual: ' . $token);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending WhatsApp reset password', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'WhatsApp tidak terkirim. Gunakan kode manual: ' . $token);
        }
    }

    /**
     * Show reset password form
     */
    public function showResetPassword($token, Request $request)
    {
        \Log::info('Show reset password form', [
            'token' => $token,
            'phone_from_url' => $request->get('phone')
        ]);

        // Cek apakah token valid (case insensitive)
        $reset = \DB::table('spmb_password_resets')
            ->whereRaw('UPPER(token) = ?', [strtoupper($token)])
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        \Log::info('Token lookup result', [
            'token_found' => $reset ? 'YES' : 'NO',
            'phone_in_db' => $reset ? $reset->phone : null
        ]);

        if (!$reset) {
            return redirect()->route('spmb.forgot-password')
                ->with('error', 'Kode reset tidak valid atau sudah kadaluarsa. Silakan request ulang.');
        }

        $schoolProfile = SchoolProfile::first();
        $phone = $reset->phone;
        
        return view('spmb.reset-password', compact('schoolProfile', 'token', 'phone'));
    }

    /**
     * Process reset password
     */
    public function processResetPassword(Request $request)
    {
        // Trim whitespace dari semua input
        $token = trim($request->token);
        $phone = trim($request->phone);
        $password = trim($request->password);

        \Log::info('=== SPMB Process Reset Password ===', [
            'phone_raw' => $request->phone,
            'phone_trimmed' => $phone,
            'token' => $token,
            'password_length' => strlen($password)
        ]);

        $request->validate([
            'token' => 'required',
            'phone' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        // Cek token (case insensitive)
        $reset = \DB::table('spmb_password_resets')
            ->whereRaw('UPPER(token) = ?', [strtoupper($token)])
            ->where('phone', $phone)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        \Log::info('Token check result', [
            'token_found' => $reset ? 'YES' : 'NO',
            'input_token' => $token,
            'phone' => $phone
        ]);

        if (!$reset) {
            \Log::warning('Reset token not found', [
                'token' => $token,
                'phone' => $phone
            ]);
            return back()->with('error', 'Kode reset tidak valid atau sudah kadaluarsa.');
        }

        // Cari user
        $registration = SPMBRegistration::where('phone', $phone)->first();

        if (!$registration) {
            \Log::warning('User not found for reset', ['phone' => $phone]);
            
            // Log semua users untuk debugging
            $allUsers = SPMBRegistration::all(['id', 'phone', 'name', 'status']);
            \Log::info('All users in database', ['users' => $allUsers->toArray()]);
            
            return back()->with('error', 'Nomor HP tidak ditemukan.');
        }

        \Log::info('Before password update', [
            'user_id' => $registration->id,
            'phone' => $registration->phone,
            'user_status' => $registration->status,
            'old_password_hash' => substr($registration->password, 0, 20) . '...',
            'password_to_set' => '(length: ' . strlen($password) . ')'
        ]);

        // Update password menggunakan update untuk memastikan mutator dipanggil
        $registration->update([
            'password' => $password
        ]);

        // Refresh untuk mendapatkan data terbaru
        $registration->refresh();

        \Log::info('After password update', [
            'user_id' => $registration->id,
            'new_password_hash' => substr($registration->password, 0, 20) . '...',
            'password_changed' => true,
            'hash_starts_with_2y' => str_starts_with($registration->password, '$2y$')
        ]);

        // Test password baru
        $testResult = $registration->checkPassword($password);
        \Log::info('Password test result', [
            'test_passed' => $testResult ? 'YES' : 'NO',
            'input_password_length' => strlen($password),
            'hash_check_direct' => \Hash::check($password, $registration->password) ? 'YES' : 'NO'
        ]);

        // Tandai token sebagai sudah digunakan
        \DB::table('spmb_password_resets')
            ->where('id', $reset->id)
            ->update(['used' => true]);

        // Kirim notifikasi WhatsApp
        $schoolProfile = SchoolProfile::first();
        $schoolName = $schoolProfile->nama_sekolah ?? 'SPPQU';
        
        $message = "✅ *Password Berhasil Direset*\n\n";
        $message .= "Halo *{$registration->name}*,\n\n";
        $message .= "Password SPMB Anda telah berhasil direset.\n\n";
        $message .= "Silakan login menggunakan password baru Anda.\n\n";
        $message .= "Login di: " . route('spmb.login') . "\n\n";
        $message .= "Terima kasih,\n{$schoolName}";

        try {
            $this->whatsappService->sendMessage($phone, $message);
        } catch (\Exception $e) {
            \Log::error('Error sending WhatsApp confirmation: ' . $e->getMessage());
        }

        \Log::info('Password reset completed successfully', [
            'user_id' => $registration->id,
            'phone' => $phone,
            'can_login' => $testResult ? 'YES' : 'NO'
        ]);

        return redirect()->route('spmb.login')
            ->with('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
    }
}
