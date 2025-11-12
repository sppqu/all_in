<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolProfile;
use Illuminate\Support\Facades\Storage;
use App\Models\SetupGateway;
use App\Models\UserAddon;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $currentSchoolId = currentSchoolId();
        
        if (!$currentSchoolId) {
            return redirect()->route('manage.foundation.dashboard')
                ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
        }
        
        $profile = currentSchool();
        $gateway = SetupGateway::where('school_id', $currentSchoolId)->first();
        
        // Jika gateway tidak ada, buat default object
        if (!$gateway) {
            $gateway = new SetupGateway();
            $gateway->school_id = $currentSchoolId;
            $gateway->midtrans_mode = 'sandbox';
            $gateway->midtrans_is_active = false;
            $gateway->midtrans_server_key_sandbox = '';
            $gateway->midtrans_client_key_sandbox = '';
            $gateway->midtrans_merchant_id_sandbox = '';
            $gateway->midtrans_server_key_production = '';
            $gateway->midtrans_client_key_production = '';
            $gateway->midtrans_merchant_id_production = '';
            $gateway->url_wagateway = '';
            $gateway->apikey_wagateway = '';
            $gateway->sender_wagateway = '';
            $gateway->wa_gateway = '';
            $gateway->enable_wa_notification = false;
        }
        
        \Log::info('General Setting data:', [
            'profile_exists' => $profile ? 'YES' : 'NO',
            'gateway_exists' => $gateway ? 'YES' : 'NO',
            'profile_data' => $profile ? [
                'jenjang' => $profile->jenjang,
                'nama_sekolah' => $profile->nama_sekolah,
                'alamat' => $profile->alamat,
                'no_telp' => $profile->no_telp,
                'logo_sekolah' => $profile->logo_sekolah
            ] : null,
            'gateway_data' => $gateway ? [
                'midtrans_mode' => $gateway->midtrans_mode,
                'midtrans_is_active' => $gateway->midtrans_is_active,
                'enable_wa_notification' => $gateway->enable_wa_notification
            ] : null
        ]);
        
        // Check if user has Payment Gateway add-on
        $hasPaymentGatewayAddon = UserAddon::where('user_id', auth()->id())
            ->whereHas('addon', function($query) {
                $query->where('slug', 'payment-gateway');
            })
            ->where('status', 'active')
            ->exists();
        
        // Check if user has WhatsApp Gateway add-on
        $hasWhatsAppGatewayAddon = UserAddon::where('user_id', auth()->id())
            ->whereHas('addon', function($query) {
                $query->where('slug', 'whatsapp-gateway');
            })
            ->where('status', 'active')
            ->exists();
        
        \Log::info('General Setting data:', [
            'current_school_id' => $currentSchoolId,
            'profile_exists' => $profile ? 'YES' : 'NO',
            'gateway_exists' => $gateway ? 'YES' : 'NO',
            'gateway_school_id' => $gateway ? $gateway->school_id : null,
        ]);
        
        return view('general-setting', compact('profile', 'gateway', 'hasPaymentGatewayAddon', 'hasWhatsAppGatewayAddon'));
    }

    public function update(Request $request)
    {
        // Method ini sudah tidak digunakan karena tab profil sekolah sudah dihapus
        // Profil sekolah di-edit melalui dashboard sekolah
        return redirect()->route('manage.general.setting')
            ->with('info', 'Edit profil sekolah dilakukan melalui dashboard sekolah.');
    }

    public function updateGateway(Request $request)
    {
        // Check if user has Payment Gateway add-on
        $hasPaymentGatewayAddon = UserAddon::where('user_id', auth()->id())
            ->whereHas('addon', function($query) {
                $query->where('slug', 'payment-gateway');
            })
            ->where('status', 'active')
            ->exists();
        
        // Check if user has WhatsApp Gateway add-on
        $hasWhatsAppGatewayAddon = UserAddon::where('user_id', auth()->id())
            ->whereHas('addon', function($query) {
                $query->where('slug', 'whatsapp-gateway');
            })
            ->where('status', 'active')
            ->exists();
        
        // Determine what user is trying to update based on request fields
        $isUpdatingPaymentGateway = $request->has('ipaymu_va') || 
                                     $request->has('ipaymu_api_key') || 
                                     $request->has('ipaymu_mode') || 
                                     $request->has('ipaymu_is_active');
        
        $isUpdatingWhatsApp = $request->has('url_wagateway') || 
                             $request->has('apikey_wagateway') || 
                             $request->has('sender_wagateway') || 
                             $request->has('wa_gateway') || 
                             $request->has('enable_wa_notification');
        
        // Validate addon access based on what user is trying to update
        if ($isUpdatingPaymentGateway && !$hasPaymentGatewayAddon) {
            return redirect()->route('manage.general.setting')
                ->with('error', 'Anda tidak memiliki akses ke Payment Gateway. Silakan beli add-on Payment Gateway terlebih dahulu.');
        }
        
        if ($isUpdatingWhatsApp && !$hasWhatsAppGatewayAddon) {
            return redirect()->route('manage.general.setting')
                ->with('error', 'Anda tidak memiliki akses ke WhatsApp Gateway. Silakan beli add-on WhatsApp Gateway terlebih dahulu.');
        }
        
        // If user doesn't have any addon and trying to update either gateway
        if (!$hasPaymentGatewayAddon && !$hasWhatsAppGatewayAddon) {
            return redirect()->route('manage.general.setting')
                ->with('error', 'Anda tidak memiliki akses ke Gateway settings. Silakan beli add-on terlebih dahulu.');
        }
        
        $currentSchoolId = currentSchoolId();
        
        if (!$currentSchoolId) {
            return redirect()->route('manage.foundation.dashboard')
                ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
        }
        
        // Get or create gateway for current school
        $gateway = SetupGateway::where('school_id', $currentSchoolId)->first();
        if (!$gateway) {
            $gateway = new SetupGateway();
            $gateway->school_id = $currentSchoolId;
        }
        
        // Log data sebelum update untuk debugging
        \Log::info('Gateway update - Data sebelum update:', [
            'school_id' => $currentSchoolId,
            'norek_bank' => $gateway->norek_bank,
            'nama_bank' => $gateway->nama_bank,
            'nama_rekening' => $gateway->nama_rekening,
            'request_data' => $request->all()
        ]);
        
        // Handle iPaymu settings (only if user has Payment Gateway addon)
        if ($hasPaymentGatewayAddon && $isUpdatingPaymentGateway) {
            if ($request->has('ipaymu_va')) {
                $gateway->ipaymu_va = $request->ipaymu_va;
            }
            if ($request->has('ipaymu_api_key')) {
                $gateway->ipaymu_api_key = $request->ipaymu_api_key;
            }
            if ($request->has('ipaymu_mode')) {
                $gateway->ipaymu_mode = $request->ipaymu_mode;
            }
            // Handle iPaymu active status
            $gateway->ipaymu_is_active = $request->has('ipaymu_is_active') ? 1 : 0;
            
            \Log::info('iPaymu settings updated', [
                'ipaymu_mode' => $gateway->ipaymu_mode,
                'ipaymu_is_active' => $gateway->ipaymu_is_active,
                'ipaymu_va_set' => !empty($gateway->ipaymu_va),
                'ipaymu_api_key_set' => !empty($gateway->ipaymu_api_key)
            ]);
        }
        
        // Handle WhatsApp settings (only if user has WhatsApp Gateway addon)
        if ($hasWhatsAppGatewayAddon && $isUpdatingWhatsApp) {
            $gateway->fill($request->only([
                'url_wagateway', 'apikey_wagateway', 'sender_wagateway', 'wa_gateway',
            ]));
            
            // Handle enable_wa_notification checkbox
            $gateway->enable_wa_notification = $request->has('enable_wa_notification') ? 1 : 0;
            
            \Log::info('WhatsApp settings updated', [
                'enable_wa_notification' => $gateway->enable_wa_notification,
                'url_wagateway' => $gateway->url_wagateway,
                'sender_wagateway' => $gateway->sender_wagateway
            ]);
        }
        
        // Handle Bank Account settings - hanya update jika ada input
        if ($request->filled('norek_bank')) {
            $gateway->norek_bank = $request->input('norek_bank');
        }
        if ($request->filled('nama_bank')) {
            $gateway->nama_bank = $request->input('nama_bank');
        }
        if ($request->filled('nama_rekening')) {
            $gateway->nama_rekening = $request->input('nama_rekening');
        }
        
        // Log data setelah update untuk debugging
        \Log::info('Gateway update - Data setelah update:', [
            'school_id' => $currentSchoolId,
            'norek_bank' => $gateway->norek_bank,
            'nama_bank' => $gateway->nama_bank,
            'nama_rekening' => $gateway->nama_rekening
        ]);
        
        $gateway->save();

        // Generate specific success message
        $successMessage = '';
        if ($isUpdatingPaymentGateway && $isUpdatingWhatsApp) {
            $successMessage = 'Pengaturan Payment Gateway dan WhatsApp Gateway berhasil disimpan!';
        } elseif ($isUpdatingPaymentGateway) {
            $successMessage = 'Pengaturan Payment Gateway berhasil disimpan!';
        } elseif ($isUpdatingWhatsApp) {
            $successMessage = 'Pengaturan WhatsApp Gateway berhasil disimpan!';
        } else {
            $successMessage = 'Pengaturan berhasil disimpan!';
        }

        return redirect()->route('manage.general.setting')->with('success', $successMessage);
    }

    public function updateRekening(Request $request)
    {
        try {
            $currentSchoolId = currentSchoolId();
            
            if (!$currentSchoolId) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            
            $request->validate([
                'norek_bank' => 'required|string|max:100',
                'nama_bank' => 'required|string|max:100',
                'nama_rekening' => 'required|string|max:100',
            ]);

            // Get or create gateway for current school
            $gateway = SetupGateway::where('school_id', $currentSchoolId)->first();
            if (!$gateway) {
                $gateway = new SetupGateway();
                $gateway->school_id = $currentSchoolId;
            }
            
            // Log data sebelum update
            \Log::info('Rekening update - Data sebelum update:', [
                'school_id' => $currentSchoolId,
                'norek_bank' => $gateway->norek_bank,
                'nama_bank' => $gateway->nama_bank,
                'nama_rekening' => $gateway->nama_rekening
            ]);
            
            // Update data rekening
            $gateway->norek_bank = $request->input('norek_bank');
            $gateway->nama_bank = $request->input('nama_bank');
            $gateway->nama_rekening = $request->input('nama_rekening');
            
            $gateway->save();
            
            // Log data setelah update
            \Log::info('Rekening update - Data setelah update:', [
                'school_id' => $currentSchoolId,
                'norek_bank' => $gateway->norek_bank,
                'nama_bank' => $gateway->nama_bank,
                'nama_rekening' => $gateway->nama_rekening
            ]);

            return redirect()->route('manage.general.setting')->with('success', 'Data rekening bank berhasil disimpan!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Rekening validation failed', [
                'errors' => $e->errors()
            ]);
            return redirect()->route('manage.general.setting')->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            \Log::error('Rekening update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('manage.general.setting')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 