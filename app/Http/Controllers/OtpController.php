<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Http;

class OtpController extends Controller
{
    protected $fonnteApiKey;
    protected $fonnteUrl;

    public function __construct()
    {
        $this->fonnteApiKey = 'AZ2SMKdAxXWUUjnrCf2p';
        $this->fonnteUrl = 'https://api.fonnte.com/send';
    }

    // Tampilkan form request OTP
    public function showRequestForm()
    {
        return view('auth.otp-request');
    }

    // Request OTP
    public function requestOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^[0-9]{10,13}$/'
        ]);

        $phone = $this->formatPhone($request->phone);
        
        // Cek apakah user dengan nomor HP ini ada
        $user = User::where('nomor_wa', $phone)->first();
        
        if (!$user) {
            return back()->withErrors(['phone' => 'Nomor HP tidak terdaftar dalam sistem.']);
        }

        // Generate OTP 6 digit
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Simpan OTP ke database
        Otp::updateOrCreate(
            ['nomor' => $phone],
            [
                'otp' => strval($otp), // Simpan OTP sebagai string
                'waktu' => time() // Timestamp saat ini
            ]
        );

        // Kirim OTP via Fonnte
        $message = "Kode OTP SPPQU untuk Anda adalah: {$otp}\n\nKode ini berlaku selama 5 menit.\nJangan bagikan kode ini kepada siapapun!";
        
        $response = $this->sendWhatsApp($phone, $message);
        
        if ($response['success']) {
            // Simpan nomor HP ke session
            session(['phone' => $phone]);
            
            return redirect()->route('otp.verify')->with([
                'success' => 'Kode OTP telah dikirim ke WhatsApp Anda.'
            ]);
        } else {
            return back()->withErrors(['phone' => 'Gagal mengirim OTP: ' . $response['message']]);
        }
    }

    // Tampilkan form verifikasi OTP
    public function showVerifyForm()
    {
        if (!session('phone')) {
            return redirect()->route('otp.request');
        }
        
        return view('auth.otp-verify');
    }

    // Verifikasi OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $phone = session('phone');
        $otpCode = $request->otp;

        // Ambil OTP dari database
        $otpRecord = Otp::where('nomor', $phone)->first();
        
        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Kode OTP tidak ditemukan. Silakan request ulang.']);
        }

        // Cek apakah OTP sudah expired (5 menit)
        $fiveMinutesAgo = time() - (5 * 60);
        if ($otpRecord->waktu < $fiveMinutesAgo) {
            return back()->withErrors(['otp' => 'Kode OTP sudah expired. Silakan request ulang.']);
        }

        // Verifikasi OTP (bandingkan dengan konversi tipe data)
        if (trim($otpCode) !== trim(strval($otpRecord->otp))) {
            return back()->withErrors(['otp' => 'Kode OTP salah. Silakan coba lagi.']);
        }

        // OTP valid, login user
        $user = User::where('nomor_wa', $phone)->first();
        
        if ($user) {
            auth()->login($user);
            
            // Hapus OTP record
            $otpRecord->delete();
            
            // Clear session
            session()->forget('phone');
            // Past issues: users were redirected to previously intended URLs
            // such as `manage/notifications/unread` due to unauthenticated
            // prefetches on the login page. Force dashboard after login.
            session()->forget('url.intended');
            \App\Helpers\ActivityLogger::log('login', 'admin', 'Admin login via OTP', [
                'user_id' => $user->id,
                'name' => $user->name,
            ], 'user', $user->id);
            
            // Redirect ke Dashboard BK jika user adalah BK (dan bukan superadmin)
            // Superadmin bisa akses semua, jadi tetap ke dashboard utama
            if ($user->is_bk && $user->role !== 'superadmin') {
                return redirect()->route('manage.bk.dashboard');
            }
            
            // Redirect ke Dashboard E-Jurnal jika user adalah Admin Jurnal (dan bukan superadmin)
            if ($user->role === 'admin_jurnal') {
                return redirect()->route('jurnal.guru.index');
            }
            
            return redirect()->route('manage.admin.dashboard');
        }

        return back()->withErrors(['otp' => 'Terjadi kesalahan. Silakan coba lagi.']);
    }

    // Resend OTP
    public function resendOtp()
    {
        $phone = session('phone');
        
        if (!$phone) {
            return redirect()->route('otp.request');
        }

        // Generate OTP baru
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update OTP di database
        Otp::where('nomor', $phone)->update([
            'otp' => strval($otp), // Pastikan OTP tersimpan sebagai string
            'waktu' => time()
        ]);

        // Kirim OTP baru
        $message = "Kode OTP baru Anda adalah: {$otp}\n\nKode ini berlaku selama 5 menit.\nJangan bagikan kode ini kepada siapapun.";
        
        $response = $this->sendWhatsApp($phone, $message);
        
        if ($response['success']) {
            return back()->with('success', 'Kode OTP baru telah dikirim ke WhatsApp Anda.');
        } else {
            return back()->withErrors(['otp' => 'Gagal mengirim OTP: ' . $response['message']]);
        }
    }

    // Kirim WhatsApp via Fonnte
    protected function sendWhatsApp($phone, $message)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->fonnteApiKey
            ])->post($this->fonnteUrl, [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === true) {
                    return [
                        'success' => true,
                        'message' => 'OTP berhasil dikirim'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $data['message'] ?? 'Gagal mengirim OTP'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke API Fonnte'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    // Format nomor HP
    protected function formatPhone($phone)
    {
        // Hapus karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Jika dimulai dengan 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // Jika tidak dimulai dengan 62, tambahkan
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
}
