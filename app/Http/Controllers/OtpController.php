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
        // OTP hanya menggunakan konfigurasi dari script/file (.env atau config), BUKAN dari database
        // Ambil dari config file atau .env
        $this->fonnteApiKey = config('fonnte.api_key') ?: env('FONNTE_API_KEY', '');
        $this->fonnteUrl = config('fonnte.base_url') ?: env('FONNTE_URL', 'https://api.fonnte.com/send');
        
        // Jika tidak ada di config/env, gunakan nilai default hardcoded sebagai fallback
        if (empty($this->fonnteApiKey)) {
            $this->fonnteApiKey = 'AZ2SMKdAxXWUUjnrCf2p';
        }
        if (empty($this->fonnteUrl)) {
            $this->fonnteUrl = 'https://api.fonnte.com/send';
        }
        
        // Pastikan URL tidak ada trailing slash
        $this->fonnteUrl = rtrim($this->fonnteUrl, '/');
        
        \Log::info('OTP: Constructor initialized', [
            'api_key_set' => !empty($this->fonnteApiKey),
            'url' => $this->fonnteUrl,
            'api_key_preview' => substr($this->fonnteApiKey, 0, 10) . '...'
        ]);
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
            
            // Set foundation dan school context setelah login
            // Untuk superadmin dan admin_yayasan, set foundation pertama
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                $foundation = \App\Models\Foundation::first();
                if ($foundation) {
                    session(['foundation_id' => $foundation->id]);
                    // Set sekolah pertama di foundation (tidak filter berdasarkan status, semua sekolah aktif selama tidak dihapus)
                    $school = \App\Models\School::where('foundation_id', $foundation->id)
                        ->first();
                    if ($school) {
                        session(['current_school_id' => $school->id]);
                    }
                }
                // Redirect ke foundation dashboard
                return redirect()->route('manage.foundation.dashboard');
            }
            
            // Untuk user sekolah, set school dari user_schools
            // Ambil semua sekolah yang user punya akses (tidak filter berdasarkan status, semua sekolah aktif selama tidak dihapus)
            $userSchools = DB::table('user_schools')
                ->where('user_id', $user->id)
                ->join('schools', 'user_schools.school_id', '=', 'schools.id')
                ->select('schools.id', 'schools.foundation_id', 'schools.nama_sekolah', 'user_schools.created_at')
                ->orderBy('user_schools.created_at', 'asc') // Ambil berdasarkan assignment pertama
                ->orderBy('schools.id', 'asc') // Fallback ke ID jika created_at sama
                ->get();
            
            if ($userSchools->count() > 0) {
                // Ambil sekolah yang di-assign pertama kali untuk user ini
                $userSchool = $userSchools->first();
                session(['current_school_id' => $userSchool->id]);
                session(['foundation_id' => $userSchool->foundation_id]);
                
                // Log untuk debugging - ini penting untuk troubleshooting
                \Log::info("User login: {$user->name} (ID: {$user->id}, Email: {$user->email})", [
                    'role' => $user->role,
                    'total_assigned_schools' => $userSchools->count(),
                    'assigned_schools' => $userSchools->map(function($s) {
                        return ['id' => $s->id, 'name' => $s->nama_sekolah];
                    })->toArray(),
                    'selected_school_id' => $userSchool->id,
                    'selected_school_name' => $userSchool->nama_sekolah,
                    'selected_school_foundation_id' => $userSchool->foundation_id,
                ]);
            } else {
                // Jika user tidak punya akses ke sekolah manapun, ini masalah!
                \Log::error("User {$user->name} (ID: {$user->id}, Email: {$user->email}) tidak memiliki akses ke sekolah manapun", [
                    'role' => $user->role,
                    'user_id' => $user->id,
                ]);
                
                // Redirect dengan error message
                return redirect()->route('otp.request')
                    ->withErrors(['login' => 'Akun Anda belum di-assign ke sekolah manapun. Silakan hubungi administrator.']);
            }
            
            // Redirect ke Dashboard BK jika user adalah BK (dan bukan superadmin)
            if ($user->is_bk && $user->role !== 'superadmin') {
                return redirect()->route('manage.bk.dashboard');
            }
            
            // Redirect ke Dashboard E-Jurnal jika user adalah Admin Jurnal (dan bukan superadmin)
            if ($user->role === 'admin_jurnal') {
                return redirect()->route('jurnal.guru.index');
            }
            
            // Redirect admin sekolah ke dashboard sekolah (admin dashboard biasa)
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
    // OTP hanya menggunakan konfigurasi dari script/file, BUKAN dari database
    protected function sendWhatsApp($phone, $message)
    {
        try {
            // Pastikan API key dan URL sudah terisi dari config/env
            if (empty($this->fonnteApiKey) || empty($this->fonnteUrl)) {
                \Log::error('OTP: Fonnte API key or URL is empty', [
                    'api_key_empty' => empty($this->fonnteApiKey),
                    'url_empty' => empty($this->fonnteUrl),
                    'api_key_length' => strlen($this->fonnteApiKey ?? ''),
                    'url' => $this->fonnteUrl
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Konfigurasi Fonnte API tidak ditemukan. Silakan cek file .env atau config/fonnte.php'
                ];
            }
            
            \Log::info('OTP: Sending WhatsApp via Fonnte', [
                'phone' => $phone,
                'url' => $this->fonnteUrl,
                'api_key_preview' => substr($this->fonnteApiKey, 0, 10) . '...'
            ]);
            
            // Format request sesuai dengan Fonnte API documentation
            // Gunakan format yang sama dengan WhatsAppService yang sudah berhasil
            $payload = [
                'target' => $phone,
                'message' => $message,
                'countryCode' => config('fonnte.country_code') ?: env('FONNTE_COUNTRY_CODE', '62')
            ];
            
            \Log::info('OTP: Fonnte API request details', [
                'url' => $this->fonnteUrl,
                'payload' => $payload,
                'api_key_length' => strlen($this->fonnteApiKey),
                'api_key_preview' => substr($this->fonnteApiKey, 0, 10) . '...'
            ]);
            
            // Coba dengan timeout yang lebih lama dan retry mechanism
            $maxRetries = 2;
            $retryDelay = 2; // detik
            $response = null;
            $lastException = null;
            
            try {
                for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                    try {
                        \Log::info("OTP: Attempt {$attempt} to send WhatsApp", [
                            'url' => $this->fonnteUrl,
                            'phone' => $phone
                        ]);
                        
                        $response = Http::timeout(60) // Increase timeout to 60 seconds
                            ->connectTimeout(10) // Connection timeout 10 seconds
                            ->withHeaders([
                                'Authorization' => $this->fonnteApiKey,
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json',
                                'User-Agent' => 'SPPQU-OTP/1.0'
                            ])
                            ->post($this->fonnteUrl, $payload);
                        
                        // Jika berhasil, break dari loop
                        if ($response->successful()) {
                            break;
                        }
                        
                        // Jika bukan timeout error, langsung return
                        if ($response->status() !== 502 && $response->status() !== 504) {
                            break;
                        }
                        
                        \Log::warning("OTP: Attempt {$attempt} failed, will retry", [
                            'status' => $response->status(),
                            'body' => substr($response->body(), 0, 200)
                        ]);
                        
                        // Tunggu sebentar sebelum retry
                        if ($attempt < $maxRetries) {
                            sleep($retryDelay);
                        }
                    } catch (\Illuminate\Http\Client\ConnectionException $e) {
                        $lastException = $e;
                        \Log::warning("OTP: Connection exception on attempt {$attempt}", [
                            'message' => $e->getMessage(),
                            'url' => $this->fonnteUrl
                        ]);
                        
                        // Jika bukan attempt terakhir, tunggu dan coba lagi
                        if ($attempt < $maxRetries) {
                            sleep($retryDelay);
                            continue;
                        }
                        
                        // Jika attempt terakhir, throw exception
                        throw $e;
                    }
                }
                
                // Jika setelah semua retry masih gagal, throw exception
                if (!$response || !$response->successful()) {
                    if ($lastException) {
                        throw $lastException;
                    }
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $errorMessage = $e->getMessage();
                
                // Parse pesan error untuk memberikan feedback yang lebih jelas
                if (strpos($errorMessage, 'timed out') !== false) {
                    $errorMessage = 'Koneksi ke API Fonnte timeout. Server mungkin sedang sibuk atau tidak dapat diakses. Silakan coba lagi beberapa saat.';
                } elseif (strpos($errorMessage, 'Connection refused') !== false) {
                    $errorMessage = 'Koneksi ke API Fonnte ditolak. Silakan cek konfigurasi URL di .env.';
                } elseif (strpos($errorMessage, 'Could not resolve host') !== false) {
                    $errorMessage = 'Tidak dapat menemukan server Fonnte. Silakan cek koneksi internet atau konfigurasi DNS.';
                }
                
                \Log::error('OTP: Connection exception after retries', [
                    'message' => $e->getMessage(),
                    'url' => $this->fonnteUrl,
                    'attempts' => $maxRetries
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke API Fonnte: ' . $errorMessage
                ];
            } catch (\Exception $e) {
                \Log::error('OTP: Exception during request', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'url' => $this->fonnteUrl
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }

            \Log::info('OTP: Fonnte API response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body()
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
                // Parse response body untuk mendapatkan pesan error yang lebih detail
                $responseBody = $response->body();
                $statusCode = $response->status();
                $errorMessage = 'Gagal terhubung ke API Fonnte';
                
                // Handle berdasarkan status code
                if ($statusCode === 502 || $statusCode === 504) {
                    $errorMessage = 'Server Fonnte sedang tidak dapat diakses (Error ' . $statusCode . '). Server mungkin sedang maintenance atau overload. Silakan coba lagi beberapa saat atau hubungi support Fonnte.';
                } elseif ($statusCode === 401) {
                    $errorMessage = 'API Key Fonnte tidak valid atau tidak terotorisasi. Silakan cek konfigurasi API key di file .env.';
                } elseif ($statusCode === 403) {
                    $errorMessage = 'Akses ke API Fonnte ditolak. Mungkin API key tidak memiliki izin atau quota sudah habis.';
                } elseif ($statusCode === 404) {
                    $errorMessage = 'Endpoint Fonnte tidak ditemukan. Silakan cek URL di file .env (seharusnya: https://api.fonnte.com/send).';
                } elseif ($statusCode >= 500) {
                    $errorMessage = 'Server Fonnte mengalami error internal (Error ' . $statusCode . '). Silakan coba lagi beberapa saat.';
                } else {
                    // Coba parse JSON response jika ada
                    try {
                        $errorData = $response->json();
                        if (isset($errorData['message'])) {
                            $errorMessage = $errorData['message'];
                        } elseif (isset($errorData['error'])) {
                            $errorMessage = $errorData['error'];
                        } else {
                            $errorMessage = 'Gagal terhubung ke API Fonnte (Error ' . $statusCode . '). ' . substr($responseBody, 0, 100);
                        }
                    } catch (\Exception $e) {
                        // Jika bukan JSON, gunakan pesan default dengan status code
                        $errorMessage = 'Gagal terhubung ke API Fonnte (Error ' . $statusCode . '). Silakan coba lagi beberapa saat.';
                    }
                }
                
                \Log::error('OTP: Fonnte API connection failed', [
                    'status' => $statusCode,
                    'status_text' => $response->reason(),
                    'body' => substr($responseBody, 0, 500), // Limit log size
                    'url' => $this->fonnteUrl,
                    'error_message' => $errorMessage
                ]);
                
                return [
                    'success' => false,
                    'message' => $errorMessage
                ];
            }
        } catch (\Exception $e) {
            \Log::error('OTP: Exception when sending WhatsApp', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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

    // Tampilkan form login email
    public function showEmailLoginForm()
    {
        return view('auth.login');
    }

    // Proses login email
    public function emailLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.'])->withInput($request->only('email'));
        }

        // Cek password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password salah.'])->withInput($request->only('email'));
        }

        // Login user
        auth()->login($user);

        // Clear session
        session()->forget('url.intended');
        
        \App\Helpers\ActivityLogger::log('login', 'admin', 'Admin login via Email', [
            'user_id' => $user->id,
            'name' => $user->name,
        ], 'user', $user->id);

        // Set foundation dan school context setelah login
        // Untuk superadmin dan admin_yayasan, set foundation pertama
        if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
            $foundation = \App\Models\Foundation::first();
            if ($foundation) {
                session(['foundation_id' => $foundation->id]);
                // Set sekolah pertama di foundation (tidak filter berdasarkan status, semua sekolah aktif selama tidak dihapus)
                $school = \App\Models\School::where('foundation_id', $foundation->id)
                    ->first();
                if ($school) {
                    session(['current_school_id' => $school->id]);
                }
            }
            // Redirect ke foundation dashboard
            return redirect()->route('manage.foundation.dashboard');
        }

        // Untuk user sekolah, set school dari user_schools
        // Ambil semua sekolah yang user punya akses (tidak filter berdasarkan status, semua sekolah aktif selama tidak dihapus)
        $userSchools = DB::table('user_schools')
            ->where('user_id', $user->id)
            ->join('schools', 'user_schools.school_id', '=', 'schools.id')
            ->select('schools.id', 'schools.foundation_id', 'schools.nama_sekolah', 'user_schools.created_at')
            ->orderBy('user_schools.created_at', 'asc') // Ambil berdasarkan assignment pertama
            ->orderBy('schools.id', 'asc') // Fallback ke ID jika created_at sama
            ->get();

        if ($userSchools->count() > 0) {
            // Ambil sekolah yang di-assign pertama kali untuk user ini
            $userSchool = $userSchools->first();
            session(['current_school_id' => $userSchool->id]);
            session(['foundation_id' => $userSchool->foundation_id]);

            // Log untuk debugging
            \Log::info("User login via email: {$user->name} (ID: {$user->id}, Email: {$user->email})", [
                'role' => $user->role,
                'total_assigned_schools' => $userSchools->count(),
                'assigned_schools' => $userSchools->map(function($s) {
                    return ['id' => $s->id, 'name' => $s->nama_sekolah];
                })->toArray(),
                'selected_school_id' => $userSchool->id,
                'selected_school_name' => $userSchool->nama_sekolah,
                'selected_school_foundation_id' => $userSchool->foundation_id,
            ]);
        } else {
            // Jika user tidak punya akses ke sekolah manapun, ini masalah!
            \Log::error("User {$user->name} (ID: {$user->id}, Email: {$user->email}) tidak memiliki akses ke sekolah manapun", [
                'role' => $user->role,
                'user_id' => $user->id,
            ]);

            // Redirect dengan error message
            return redirect()->route('otp.request')
                ->withErrors(['login' => 'Akun Anda belum di-assign ke sekolah manapun. Silakan hubungi administrator.']);
        }

        // Redirect ke Dashboard BK jika user adalah BK (dan bukan superadmin)
        if ($user->is_bk && $user->role !== 'superadmin') {
            return redirect()->route('manage.bk.dashboard');
        }

        // Redirect ke Dashboard E-Jurnal jika user adalah Admin Jurnal (dan bukan superadmin)
        if ($user->role === 'admin_jurnal') {
            return redirect()->route('jurnal.guru.index');
        }

        // Redirect admin sekolah ke dashboard sekolah (admin dashboard biasa)
        return redirect()->route('manage.admin.dashboard');
    }
}
