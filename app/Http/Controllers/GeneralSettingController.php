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
        $profile = SchoolProfile::first();
        $gateway = SetupGateway::first();
        
        // Jika gateway tidak ada, buat default object
        if (!$gateway) {
            $gateway = new SetupGateway();
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
        
        return view('general-setting', compact('profile', 'gateway', 'hasPaymentGatewayAddon', 'hasWhatsAppGatewayAddon'));
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'jenjang' => 'required|string|max:50',
                'nama_sekolah' => 'required|string|max:255',
                'alamat' => 'required|string',
                'no_telp' => 'required|string|max:50',
                'logo_sekolah' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $profile = SchoolProfile::first() ?? new SchoolProfile();
            $profile->jenjang = $request->jenjang;
            $profile->nama_sekolah = $request->nama_sekolah;
            $profile->alamat = $request->alamat;
            $profile->no_telp = $request->no_telp;

            if ($request->hasFile('logo_sekolah')) {
                try {
                    // Hapus logo lama jika ada
                    if ($profile->logo_sekolah && $profile->logo_sekolah !== 'Logo' && Storage::disk('public')->exists($profile->logo_sekolah)) {
                        Storage::disk('public')->delete($profile->logo_sekolah);
                    }
                    
                    // Upload logo baru
                    $logoPath = $request->file('logo_sekolah')->store('logos', 'public');
                    $profile->logo_sekolah = $logoPath;
                    
                    \Log::info('Logo uploaded successfully', [
                        'original_name' => $request->file('logo_sekolah')->getClientOriginalName(),
                        'stored_path' => $logoPath,
                        'file_size' => $request->file('logo_sekolah')->getSize(),
                        'mime_type' => $request->file('logo_sekolah')->getMimeType()
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Logo upload failed', [
                        'error' => $e->getMessage(),
                        'file' => $request->file('logo_sekolah')->getClientOriginalName()
                    ]);
                    return redirect()->route('manage.general.setting')->with('error', 'Gagal upload logo: ' . $e->getMessage());
                }
            }

            $profile->save();
            
            \Log::info('School profile updated successfully', [
                'profile_id' => $profile->id,
                'has_logo' => !empty($profile->logo_sekolah)
            ]);

            return redirect()->route('manage.general.setting')->with('success', 'Profile sekolah berhasil disimpan!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors()
            ]);
            return redirect()->route('manage.general.setting')->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            \Log::error('Profile update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('manage.general.setting')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
        
        if (!$hasPaymentGatewayAddon) {
            return redirect()->route('manage.general.setting')->with('error', 'Anda tidak memiliki akses ke Payment Gateway. Silakan beli add-on Payment Gateway terlebih dahulu.');
        }
        
        // Check if user has WhatsApp Gateway add-on for WhatsApp settings
        $hasWhatsAppGatewayAddon = UserAddon::where('user_id', auth()->id())
            ->whereHas('addon', function($query) {
                $query->where('slug', 'whatsapp-gateway');
            })
            ->where('status', 'active')
            ->exists();
        
        $gateway = SetupGateway::first() ?? new SetupGateway();
        
        // Log data sebelum update untuk debugging
        \Log::info('Gateway update - Data sebelum update:', [
            'norek_bank' => $gateway->norek_bank,
            'nama_bank' => $gateway->nama_bank,
            'nama_rekening' => $gateway->nama_rekening,
            'request_data' => $request->all()
        ]);
        
        // Handle Midtrans settings based on mode
        if ($request->has('midtrans_mode')) {
            $gateway->midtrans_mode = $request->midtrans_mode;
            
            if ($request->midtrans_mode === 'sandbox') {
                $gateway->midtrans_server_key_sandbox = $request->midtrans_server_key_sandbox;
                $gateway->midtrans_client_key_sandbox = $request->midtrans_client_key_sandbox;
                $gateway->midtrans_merchant_id_sandbox = $request->midtrans_merchant_id_sandbox;
            } else {
                $gateway->midtrans_server_key_production = $request->midtrans_server_key_production;
                $gateway->midtrans_client_key_production = $request->midtrans_client_key_production;
                $gateway->midtrans_merchant_id_production = $request->midtrans_merchant_id_production;
            }
        }
        
        // Handle Midtrans active status
        if ($request->has('midtrans_is_active')) {
            $gateway->midtrans_is_active = $request->has('midtrans_is_active') ? 1 : 0;
        }
        
        // Handle WhatsApp settings
        $gateway->fill($request->only([
            'url_wagateway', 'apikey_wagateway', 'sender_wagateway', 'wa_gateway',
        ]));
        
        // Handle enable_wa_notification checkbox
        $gateway->enable_wa_notification = $request->has('enable_wa_notification') ? 1 : 0;
        
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
            'norek_bank' => $gateway->norek_bank,
            'nama_bank' => $gateway->nama_bank,
            'nama_rekening' => $gateway->nama_rekening
        ]);
        
        $gateway->save();

        return redirect()->route('manage.general.setting')->with('success', 'Pengaturan berhasil disimpan!');
    }

    public function updateRekening(Request $request)
    {
        try {
            $request->validate([
                'norek_bank' => 'required|string|max:100',
                'nama_bank' => 'required|string|max:100',
                'nama_rekening' => 'required|string|max:100',
            ]);

            $gateway = SetupGateway::first() ?? new SetupGateway();
            
            // Log data sebelum update
            \Log::info('Rekening update - Data sebelum update:', [
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