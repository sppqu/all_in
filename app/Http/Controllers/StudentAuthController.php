<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;
use App\Services\IpaymuService;

class StudentAuthController extends Controller
{
    /**
     * Menampilkan halaman login siswa
     */
    public function showLoginForm()
    {
        return view('student.auth.login');
    }

    /**
     * Proses login siswa
     */
    public function login(Request $request)
    {
        $request->validate([
            'nis' => 'required|string',
            'password' => 'required|string'
        ]);

        $student = Student::where('student_nis', $request->nis)
                         ->where('student_status', 1)
                         ->first();

        if (!$student) {
            return back()->withErrors([
                'nis' => 'NIS tidak ditemukan atau siswa tidak aktif.'
            ])->withInput($request->only('nis'));
        }

        // Check password (assuming password is stored in student_password field)
        if (empty($student->student_password) || !Hash::check($request->password, $student->student_password)) {
            return back()->withErrors([
                'password' => 'Password salah.'
            ])->withInput($request->only('nis'));
        }

        // Store student data in session
        session([
            'student_id' => $student->student_id,
            'student_nis' => $student->student_nis,
            'student_name' => $student->student_full_name,
            'student_class' => $student->class ? $student->class->class_name : 'Kelas tidak ditemukan',
            'is_student' => true,
            'last_activity' => time()
        ]);

        \App\Helpers\ActivityLogger::log('login', 'student', 'Siswa login', [
            'nis' => $student->student_nis,
            'name' => $student->student_full_name,
        ], 'student', $student->student_id);
        return redirect()->route('student.dashboard');
    }

    /**
     * Logout siswa
     */
    public function logout(Request $request)
    {
        try {
            // Log logout attempt
            Log::info('Student logout attempt', [
                'student_id' => session('student_id'),
                'student_nis' => session('student_nis'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Clear all session data
            $request->session()->forget([
                'student_id',
                'student_nis', 
                'student_name',
                'student_class',
                'is_student',
                'last_activity',
                'cart_items',
                'total_amount'
            ]);

            // Invalidate session first
            $request->session()->invalidate();
            
            // Regenerate token after invalidation
            $request->session()->regenerateToken();

            // Set cache headers untuk mencegah caching halaman login
            $response = redirect()->route('student.login')
                ->with('success', 'Anda telah berhasil logout.');
                
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            
            Log::info('Student logout successful', [
                'ip' => $request->ip()
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Error during student logout', [
                'error' => $e->getMessage(),
                'student_id' => session('student_id'),
                'ip' => $request->ip()
            ]);
            
            // Even if there's an error, try to clear session and redirect
            try {
                $request->session()->flush();
                $request->session()->regenerateToken();
            } catch (\Exception $sessionError) {
                Log::error('Error clearing session during logout', [
                    'error' => $sessionError->getMessage()
                ]);
            }
            
            return redirect()->route('student.login')
                ->with('success', 'Anda telah berhasil logout.');
        }
    }

    /**
     * Logout siswa dengan GET request (fallback untuk CSRF issues)
     */
    public function logoutGet(Request $request)
    {
        try {
            // Log logout attempt
            Log::info('Student logout attempt (GET)', [
                'student_id' => session('student_id'),
                'student_nis' => session('student_nis'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Clear all session data
            $request->session()->forget([
                'student_id',
                'student_nis', 
                'student_name',
                'student_class',
                'is_student',
                'last_activity',
                'cart_items',
                'total_amount'
            ]);

            // Invalidate session first
            $request->session()->invalidate();
            
            // Regenerate token after invalidation
            $request->session()->regenerateToken();

            // Set cache headers untuk mencegah caching halaman login
            $response = redirect()->route('student.login')
                ->with('success', 'Anda telah berhasil logout.');
                
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            
            Log::info('Student logout successful (GET)', [
                'ip' => $request->ip()
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Error during student logout (GET)', [
                'error' => $e->getMessage(),
                'student_id' => session('student_id'),
                'ip' => $request->ip()
            ]);
            
            // Even if there's an error, try to clear session and redirect
            try {
                $request->session()->flush();
                $request->session()->regenerateToken();
            } catch (\Exception $sessionError) {
                Log::error('Error clearing session during logout (GET)', [
                    'error' => $sessionError->getMessage()
                ]);
            }
            
            return redirect()->route('student.login')
                ->with('success', 'Anda telah berhasil logout.');
        }
    }

    /**
     * Refresh CSRF token untuk mencegah 419 error
     */
    public function refreshCsrfToken(Request $request)
    {
        try {
            if (!session('is_student')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Regenerate CSRF token
            $request->session()->regenerateToken();
            
            return response()->json([
                'success' => true,
                'csrf_token' => csrf_token(),
                'message' => 'CSRF token refreshed successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error refreshing CSRF token', [
                'error' => $e->getMessage(),
                'student_id' => session('student_id'),
                'ip' => $request->ip()
            ]);
            
            return response()->json(['success' => false, 'message' => 'Error refreshing CSRF token'], 500);
        }
    }

    /**
     * Dashboard siswa
     */
    public function dashboard()
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        
        // Validasi session - pastikan data siswa masih valid
        $student = Student::with(['class'])->find($studentId);
        if (!$student) {
            // Jika data siswa tidak ditemukan, clear session dan redirect ke login
            session()->forget([
                'student_id',
                'student_nis', 
                'student_name',
                'student_class',
                'is_student',
                'last_activity'
            ]);
            return redirect()->route('student.login')
                ->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }
        
        // Update session data dengan data terbaru dari database
        session([
            'student_id' => $student->student_id,
            'student_nis' => $student->student_nis,
            'student_name' => $student->student_full_name,
            'student_class' => $student->class ? $student->class->class_name : 'Kelas tidak ditemukan',
            'is_student' => true,
            'last_activity' => time()
        ]);

        // Get student's bills
        $bulananBills = DB::table('bulan as b')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('b.student_student_id', $studentId)
            ->select(
                'b.*',
                'pos.pos_name',
                'per.period_start',
                'per.period_end'
            )
            ->get();

        $bebasBills = DB::table('bebas as be')
            ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('be.student_student_id', $studentId)
            ->select(
                'be.*',
                'pos.pos_name',
                'per.period_start',
                'per.period_end'
            )
            ->get();

        // Get transaction history from transfer table for dashboard
        $transferTransactions = DB::table('transfer as t')
            ->leftJoin('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
            ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
            ->leftJoin('payment as p', function($join) {
                $join->on('b.payment_payment_id', '=', 'p.payment_id')
                     ->orOn('be.payment_payment_id', '=', 'p.payment_id');
            })
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('t.student_id', $studentId)
            ->where('t.status', 1) // Only confirmed payments
            ->select(
                't.transfer_id',
                't.confirm_date as payment_date',
                't.confirm_pay as amount',
                't.reference as payment_number',
                'td.payment_type',
                'td.subtotal',
                'pos.pos_name',
                'td.desc',
                DB::raw('CASE 
                    WHEN td.payment_type = 1 THEN "BULANAN"
                    WHEN td.payment_type = 2 THEN "BEBAS"
                    ELSE "ONLINE"
                END as transaction_type'),
                DB::raw('CASE 
                    WHEN td.payment_type = 1 THEN CONCAT(pos.pos_name, "-", 
                        CASE b.month_month_id
                            WHEN 1 THEN "Juli"
                            WHEN 2 THEN "Agustus"
                            WHEN 3 THEN "September"
                            WHEN 4 THEN "Oktober"
                            WHEN 5 THEN "November"
                            WHEN 6 THEN "Desember"
                            WHEN 7 THEN "Januari"
                            WHEN 8 THEN "Februari"
                            WHEN 9 THEN "Maret"
                            WHEN 10 THEN "April"
                            WHEN 11 THEN "Mei"
                            WHEN 12 THEN "Juni"
                            ELSE "Unknown"
                        END, " (", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"), ")"
                    )
                    WHEN td.payment_type = 2 THEN pos.pos_name
                    ELSE td.desc
                END as display_name')
            )
            ->get();

        // Get cash payment history from log_trx table for dashboard
        $cashBulananTransactions = DB::table('log_trx as lt')
            ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
            ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('lt.student_student_id', $studentId)
            ->whereNotNull('lt.bulan_bulan_id')
            ->select(
                'lt.log_trx_id',
                'lt.log_trx_input_date as payment_date',
                'p.payment_type',
                'pos.pos_name',
                'b.bulan_bill as amount',
                'b.bulan_number_pay as payment_number',
                DB::raw('"CASH_BULANAN" as transaction_type'),
                DB::raw('CONCAT(pos.pos_name, "-", 
                    CASE b.month_month_id
                        WHEN 1 THEN "Juli"
                        WHEN 2 THEN "Agustus"
                        WHEN 3 THEN "September"
                        WHEN 4 THEN "Oktober"
                        WHEN 5 THEN "November"
                        WHEN 6 THEN "Desember"
                        WHEN 7 THEN "Januari"
                        WHEN 8 THEN "Februari"
                        WHEN 9 THEN "Maret"
                        WHEN 10 THEN "April"
                        WHEN 11 THEN "Mei"
                        WHEN 12 THEN "Juni"
                        ELSE "Unknown"
                    END, " (", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"), ")"
                ) as display_name')
            )
            ->get();

        $cashBebasTransactions = DB::table('log_trx as lt')
            ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
            ->leftJoin('bebas as b', 'bp.bebas_bebas_id', '=', 'b.bebas_id')
            ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->leftJoin('users as u', 'bp.user_user_id', '=', 'u.id')
            ->where('lt.student_student_id', $studentId)
            ->whereNotNull('lt.bebas_pay_bebas_pay_id')
            ->select(
                'lt.log_trx_id',
                'lt.log_trx_input_date as payment_date',
                'p.payment_type',
                'pos.pos_name',
                'bp.bebas_pay_bill as amount',
                'bp.bebas_pay_number as payment_number',
                DB::raw('"CASH_BEBAS" as transaction_type'),
                DB::raw('1 as status'),
                DB::raw('COALESCE(u.name, "Super Admin") as cashier_name'),
                DB::raw('CONCAT(pos.pos_name, " - ", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026")) as display_name')
            )
            ->get();

        // Get online payment history from transfer table for dashboard
        $onlinePaymentTransactions = DB::table('transfer as t')
            ->where('t.student_id', $studentId)
            ->where('t.status', 1) // Only successful online payments
            ->select(
                't.transfer_id as log_trx_id',
                't.paid_at as payment_date',
                DB::raw('NULL as payment_type'),
                DB::raw('"Online Payment" as pos_name'),
                't.confirm_pay as amount',
                't.reference as payment_number',
                DB::raw('"ONLINE" as transaction_type'),
                DB::raw('CASE 
                    WHEN t.bill_type = "bulanan" THEN CONCAT("SPP-", 
                        CASE 
                            WHEN MONTH(t.paid_at) = 7 THEN "Juli"
                            WHEN MONTH(t.paid_at) = 8 THEN "Agustus"
                            WHEN MONTH(t.paid_at) = 9 THEN "September"
                            WHEN MONTH(t.paid_at) = 10 THEN "Oktober"
                            WHEN MONTH(t.paid_at) = 11 THEN "November"
                            WHEN MONTH(t.paid_at) = 12 THEN "Desember"
                            WHEN MONTH(t.paid_at) = 1 THEN "Januari"
                            WHEN MONTH(t.paid_at) = 2 THEN "Februari"
                            WHEN MONTH(t.paid_at) = 3 THEN "Maret"
                            WHEN MONTH(t.paid_at) = 4 THEN "April"
                            WHEN MONTH(t.paid_at) = 5 THEN "Mei"
                            WHEN MONTH(t.paid_at) = 6 THEN "Juni"
                            ELSE "Unknown"
                        END, " (2025/2026)")
                    ELSE "Pembayaran Online"
                END as display_name')
            )
            ->get();

        // Combine and sort transactions for dashboard
        $onlinePayments = $transferTransactions->concat($cashBulananTransactions)->concat($cashBebasTransactions)->concat($onlinePaymentTransactions)
            ->sortByDesc('payment_date')
            ->take(5);

        // Calculate unpaid bills (sisa tagihan yang belum dibayar)
        // Get current month in academic year format
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Convert current month to academic year month (July = 1, August = 2, etc.)
        $academicMonth = $currentMonth;
        if ($currentMonth >= 7) {
            // July to December (7-12) becomes 1-6 in academic year
            $academicMonth = $currentMonth - 6;
        } else {
            // January to June (1-6) becomes 7-12 in academic year
            $academicMonth = $currentMonth + 6;
        }
        
        // Calculate unpaid bills only up to current academic month
        $unpaidBulanan = $bulananBills
            ->where('bulan_date_pay', null)
            ->where('month_month_id', '<=', $academicMonth)
            ->sum('bulan_bill');
        
        // Calculate unpaid bebas bills (sisa tagihan bebas yang belum lunas)
        $unpaidBebas = $bebasBills
            ->filter(function($bill) {
                // Tagihan yang belum lunas: belum ada tanggal bayar ATAU total bayar masih kurang
                return $bill->bebas_date_pay === null || $bill->bebas_total_pay < $bill->bebas_bill;
            })
            ->sum(function($bill) {
                // Hitung sisa tagihan (tagihan - total bayar)
                return $bill->bebas_bill - ($bill->bebas_total_pay ?? 0);
            });
        
        // Get tabungan data
        $tabungan = DB::table('tabungan')
            ->where('student_student_id', $studentId)
            ->first();
        
        $totalTabungan = $tabungan ? $tabungan->saldo : 0;

        // Calculate statistics
        $totalBulanan = $bulananBills->count();
        $lunasBulanan = $bulananBills->where('bulan_date_pay', '!=', null)->count();
        $totalBebas = $bebasBills->count();
        $lunasBebas = $bebasBills->where('bebas_date_pay', '!=', null)->count();

        // Calculate payment info data
        $paidBulanan = $lunasBulanan;
        $unpaidBulananCount = $totalBulanan - $lunasBulanan;
        $paidBebasCount = $lunasBebas;
        $unpaidBebasCount = $totalBebas - $lunasBebas;
        
        // Get current period
        $currentPeriod = DB::table('periods')
            ->where('period_status', 1)
            ->first();
        $currentPeriod = $currentPeriod ? $currentPeriod->period_start . '/' . $currentPeriod->period_end : '2024/2025';
        


        // Get school profile data
        $schoolProfile = DB::table('school_profiles')->first();

        // Set cache headers untuk mencegah browser caching
        $response = response()->view('student.dashboard', compact(
            'student',
            'bulananBills',
            'bebasBills',
            'onlinePayments',
            'totalBulanan',
            'lunasBulanan',
            'totalBebas',
            'lunasBebas',
            'unpaidBulanan',
            'unpaidBebas',
            'totalTabungan',
            'paidBulanan',
            'unpaidBulananCount',
            'paidBebasCount',
            'unpaidBebasCount',
            'currentPeriod',
            'schoolProfile'
        ));
        
        // Smart Cache Headers - Balance performance & freshness
        $response->headers->set('Cache-Control', 'no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        $response->headers->set('ETag', md5($studentId . date('YmdH'))); // Cache per hour
        
        return $response;
    }

    /**
     * Clear cache dan refresh data siswa
     */
    public function clearCache(Request $request)
    {
        if (!session('is_student')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $studentId = session('student_id');
            
            // Clear session data
            $request->session()->forget([
                'student_id',
                'student_nis', 
                'student_name',
                'student_class',
                'is_student',
                'last_activity'
            ]);
            
            // Get fresh data from database
            $student = Student::with(['class'])->find($studentId);
            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }
            
            // Update session with fresh data
            session([
                'student_id' => $student->student_id,
                'student_nis' => $student->student_nis,
                'student_name' => $student->student_full_name,
                'student_class' => $student->class ? $student->class->class_name : 'Kelas tidak ditemukan',
                'is_student' => true,
                'last_activity' => time()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
                'student_data' => [
                    'id' => $student->student_id,
                    'nis' => $student->student_nis,
                    'name' => $student->student_full_name,
                    'class' => $student->class ? $student->class->class_name : 'Kelas tidak ditemukan'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error clearing student cache: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error clearing cache'], 500);
        }
    }

    /**
     * Profile siswa
     */
    public function profile()
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        $student = Student::with(['class', 'major'])->find($studentId);

        return view('student.profile', compact('student'));
    }

    // JSON endpoint to fetch current student identity for cache-busting on dashboard
    public function identityJson(Request $request)
    {
        if (!session('is_student')) {
            return response()->json(['success' => false], 401);
        }
        $studentId = session('student_id');
        $student = Student::select('student_id','student_full_name','student_nis')->find($studentId);
        return response()->json(['success' => true, 'student' => $student])->header('Cache-Control','no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Update password siswa
     */
    public function updatePassword(Request $request)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);

        $studentId = session('student_id');
        $student = Student::find($studentId);

        if (empty($student->student_password) || !Hash::check($request->current_password, $student->student_password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini salah.'
            ]);
        }

        $student->update([
            'student_password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    /**
     * Menampilkan tabungan siswa
     */
    public function tabungan()
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        $student = Student::with(['class'])->find($studentId);

        // Get student's savings data
        $tabungan = DB::table('tabungan')
            ->where('student_student_id', $studentId)
            ->first();

        $totalTabungan = $tabungan ? $tabungan->saldo : 0;
        
        // Get transaction history from log_tabungan
        $logTabungan = DB::table('log_tabungan')
            ->where('student_student_id', $studentId)
            ->orderBy('log_tabungan_input_date', 'desc')
            ->get();

        $totalSetoran = $logTabungan->sum('kredit');
        $totalPenarikan = $logTabungan->sum('debit');
        
        // Get pending transfer payments for tabungan
        $pendingTransfers = DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->where('t.student_id', $studentId)
            ->where('t.status', 0) // Pending status
            ->where('td.payment_type', 3) // Tabungan type
            ->orderBy('t.created_at', 'desc')
            ->get();

        return view('student.tabungan', compact('student', 'logTabungan', 'totalTabungan', 'totalSetoran', 'totalPenarikan', 'pendingTransfers'));
    }

    /**
     * E-Perpustakaan for students
     */
    public function library()
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        $student = Student::with(['class'])->find($studentId);
        
        // Get or create user for library features
        $userEmail = $student->student_email ?? 'student' . $student->student_id . '@temp.com';
        
        // Check if user exists by email
        $user = DB::table('users')->where('email', $userEmail)->first();
        
        if (!$user) {
            // Create user if not exists
            try {
                $userId = DB::table('users')->insertGetId([
                    'name' => $student->student_full_name,
                    'email' => $userEmail,
                    'password' => bcrypt('password'),
                    'role' => 'student',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $user = DB::table('users')->where('id', $userId)->first();
            } catch (\Exception $e) {
                // If insert fails (duplicate), try to get existing user
                $user = DB::table('users')->where('email', $userEmail)->first();
            }
        }
        
        // Get library statistics
        $activeLoans = DB::table('book_loans')
            ->where('user_id', $user->id)
            ->where('status', 'dipinjam')
            ->count();
            
        $totalBorrowed = DB::table('book_loans')
            ->where('user_id', $user->id)
            ->count();
            
        $booksRead = DB::table('reading_history')
            ->where('user_id', $user->id)
            ->where('activity_type', 'read')
            ->distinct('book_id')
            ->count();
        
        // Get loan history
        $loanHistory = DB::table('book_loans')
            ->join('books', 'book_loans.book_id', '=', 'books.id')
            ->leftJoin('book_categories', 'books.category_id', '=', 'book_categories.id')
            ->select(
                'book_loans.*', 
                'books.judul', 
                'books.pengarang', 
                'books.cover_image',
                'book_categories.nama_kategori',
                'book_categories.warna'
            )
            ->where('book_loans.user_id', $user->id)
            ->orderBy('book_loans.tanggal_pinjam', 'desc')
            ->limit(10)
            ->get();
        
        // Get recent books catalog
        $recentBooks = DB::table('books')
            ->join('book_categories', 'books.category_id', '=', 'book_categories.id')
            ->select(
                'books.*', 
                'book_categories.nama_kategori', 
                'book_categories.warna'
            )
            ->where('books.status', 'tersedia')
            ->orderBy('books.created_at', 'desc')
            ->limit(12)
            ->get();
            
        // Get book categories
        $categories = DB::table('book_categories')
            ->where('is_active', 1)
            ->orderBy('nama_kategori')
            ->get();
        
        // Get school profile
        $schoolProfile = DB::table('school_profiles')->first();
        
        return view('student.library', compact(
            'student', 
            'user',
            'activeLoans', 
            'totalBorrowed', 
            'booksRead',
            'loanHistory',
            'recentBooks',
            'categories',
            'schoolProfile'
        ));
    }

    /**
     * Menampilkan tagihan siswa
     */
    public function bills(Request $request)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        $student = Student::with(['class'])->find($studentId);

        // Get filter parameters
        $selectedMonth = $request->get('month', now()->month); // Default to current month
        $selectedYear = $request->get('year', now()->year); // Default to current year

        // Month mapping for academic year
        $months = [
            1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
            5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
            9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
        ];

        // Get current month in academic year format
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Convert current month to academic year month (July = 1, August = 2, etc.)
        $academicMonth = $currentMonth;
        if ($currentMonth >= 7) {
            // July to December (7-12) becomes 1-6 in academic year
            $academicMonth = $currentMonth - 6;
        } else {
            // January to June (1-6) becomes 7-12 in academic year
            $academicMonth = $currentMonth + 6;
        }
        
        // Set default to current academic month if no filter is applied
        if (!$request->has('month')) {
            $selectedMonth = $academicMonth;
        }

        // Get student's unpaid bills only (exclude pending transfers) - dengan DISTINCT untuk menghindari duplikasi
        $bulananBills = DB::table('bulan as b')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->leftJoin('transfer_detail as td', function($join) {
                $join->on('td.bulan_id', '=', 'b.bulan_id')
                     ->where('td.payment_type', '=', 1);
            })
            ->leftJoin('transfer as t', function($join) {
                $join->on('t.transfer_id', '=', 'td.transfer_id')
                     ->where('t.status', '=', 0); // Exclude pending transfers
            })
            ->where('b.student_student_id', $studentId)
            ->whereNull('b.bulan_date_pay') // Only unpaid bills
            ->whereNull('t.transfer_id') // Exclude items with pending transfers
            ->select(
                'b.bulan_id',
                'b.student_student_id',
                'b.payment_payment_id',
                'b.month_month_id',
                'b.bulan_bill',
                'b.bulan_status',
                'b.bulan_date_pay',
                'b.bulan_number_pay',
                'b.bulan_last_update',
                'pos.pos_name',
                'per.period_start',
                'per.period_end'
            )
            ->distinct() // Menghindari duplikasi
            ->orderBy('b.month_month_id', 'asc') // Order by month ID (1=Juli, 2=Agustus, etc.)
            ->get();

        // Filter bulanan bills based on selected month
        $bulananBills = $bulananBills->filter(function ($bill) use ($selectedMonth) {
            return $bill->month_month_id <= $selectedMonth;
        });

        // Gunakan raw SQL untuk menghindari masalah query builder
        $bebasBills = DB::select("
            SELECT DISTINCT
                be.bebas_id,
                be.student_student_id,
                be.payment_payment_id,
                be.bebas_bill,
                be.bebas_total_pay,
                be.bebas_desc,
                be.bebas_date_pay,
                be.bebas_number_pay,
                be.bebas_last_update,
                pos.pos_name,
                per.period_start,
                per.period_end
            FROM bebas be
            JOIN payment p ON be.payment_payment_id = p.payment_id
            JOIN pos_pembayaran pos ON p.pos_pos_id = pos.pos_id
            LEFT JOIN periods per ON p.period_period_id = per.period_id
            LEFT JOIN transfer_detail td ON td.bebas_id = be.bebas_id AND td.payment_type = 2
            LEFT JOIN transfer t ON t.transfer_id = td.transfer_id AND t.status = 0
            WHERE be.student_student_id = ?
            AND (COALESCE(be.bebas_total_pay,0) < COALESCE(be.bebas_bill,0))
            AND be.bebas_bill > 0
            AND t.transfer_id IS NULL
            ORDER BY be.bebas_id
        ", [$studentId]);
        
        // Convert to collection
        $bebasBills = collect($bebasBills);

        // Get default bill type from URL parameter
        $defaultType = $request->get('type', 'bulanan');
        

        
        return view('student.bills', compact('student', 'bulananBills', 'bebasBills', 'selectedMonth', 'selectedYear', 'months', 'defaultType'));
    }

    /**
     * Menampilkan halaman keranjang
     */
    public function cart()
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        $student = Student::with(['class'])->find($studentId);
        
        // Get school bank information
        $schoolBank = DB::table('setup_gateways')->first();

        return view('student.cart', compact('student', 'schoolBank'));
    }

    /**
     * Menampilkan halaman pembayaran online untuk siswa
     */
    public function onlinePayment()
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        $student = Student::with(['class'])->find($studentId);

        // Get student's bills - dengan DISTINCT untuk menghindari duplikasi
        $bulananBills = DB::table('bulan as b')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('b.student_student_id', $studentId)
            ->whereNull('b.bulan_date_pay')
            ->select(
                'b.bulan_id',
                'b.student_student_id',
                'b.payment_payment_id',
                'b.month_month_id',
                'b.bulan_bill',
                'b.bulan_status',
                'b.bulan_date_pay',
                'b.bulan_number_pay',
                'b.bulan_last_update',
                'pos.pos_name',
                'per.period_start',
                'per.period_end'
            )
            ->distinct()
            ->get();

        $bebasBills = DB::table('bebas as be')
            ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('be.student_student_id', $studentId)
            ->whereRaw('COALESCE(be.bebas_total_pay,0) < COALESCE(be.bebas_bill,0)')
            ->where('be.bebas_bill', '>', 0)
            ->select(
                'be.bebas_id',
                'be.student_student_id',
                'be.payment_payment_id',
                'be.bebas_bill',
                'be.bebas_total_pay',
                'be.bebas_desc',
                'be.bebas_date_pay',
                'be.bebas_number_pay',
                'be.bebas_last_update',
                'pos.pos_name',
                'per.period_start',
                'per.period_end'
            )
            ->distinct()
            ->get();
            
        // Get school bank information
        $schoolBank = DB::table('setup_gateways')->first();

        return view('student.online-payment', compact('student', 'bulananBills', 'bebasBills', 'schoolBank'));
    }

    /**
     * Menampilkan riwayat pembayaran siswa
     */
    public function paymentHistory(Request $request)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        $student = Student::with(['class'])->find($studentId);

        // Ambil parameter filter tanggal
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        // Get transaction history from transfer table (manual payments) - include all statuses
        $transferTransactionsQuery = DB::table('transfer as t')
            ->leftJoin('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
            ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
            ->leftJoin('payment as p', function($join) {
                $join->on('b.payment_payment_id', '=', 'p.payment_id')
                     ->orOn('be.payment_payment_id', '=', 'p.payment_id');
            })
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('t.student_id', $studentId)
            ->where(function($query) {
                $query->where('td.payment_type', '=', 1)  // Bulanan
                      ->orWhere('td.payment_type', '=', 2); // Bebas
            })
            ->distinct();
        if ($fromDate) {
            $transferTransactionsQuery->whereDate('t.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $transferTransactionsQuery->whereDate('t.created_at', '<=', $toDate);
        }
        $transferTransactions = $transferTransactionsQuery
            ->select(
                't.transfer_id',
                't.confirm_date as payment_date',
                't.created_at',
                't.confirm_pay as amount',
                't.reference',
                't.status',
                't.checkout_url',
                'td.payment_type',
                'td.subtotal',
                'pos.pos_name',
                'td.desc',
                DB::raw('CASE 
                    WHEN td.payment_type = 1 THEN "BULANAN"
                    WHEN td.payment_type = 2 THEN "BEBAS"
                    ELSE "UNKNOWN"
                END as transaction_type'),
                DB::raw('CASE 
                    WHEN td.payment_type = 1 AND b.month_month_id IS NOT NULL THEN CONCAT(COALESCE(pos.pos_name, "Pembayaran"), "-", 
                        CASE b.month_month_id
                            WHEN 1 THEN "Juli"
                            WHEN 2 THEN "Agustus"
                            WHEN 3 THEN "September"
                            WHEN 4 THEN "Oktober"
                            WHEN 5 THEN "November"
                            WHEN 6 THEN "Desember"
                            WHEN 7 THEN "Januari"
                            WHEN 8 THEN "Februari"
                            WHEN 9 THEN "Maret"
                            WHEN 10 THEN "April"
                            WHEN 11 THEN "Mei"
                            WHEN 12 THEN "Juni"
                            ELSE "Unknown"
                        END, " (", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"), ")"
                    )
                    WHEN td.payment_type = 2 THEN COALESCE(pos.pos_name, "Pembayaran Bebas")
                    ELSE COALESCE(td.desc, "Pembayaran Cart via Midtrans")
                END as display_name')
            )
            ->orderBy('t.status', 'asc') // Pending first, then successful
            ->orderBy('t.created_at', 'desc')
            ->get();

        // Get transaction history from transfer table (include all online payments except Midtrans pending)
        $onlinePaymentsQuery = DB::table('transfer as t')
            ->where('t.student_id', $studentId)
            ->where(function($query) {
                $query->whereNotNull('t.payment_method') // Online payments with payment_method
                      ->orWhere(function($subQuery) {
                          $subQuery->whereNull('t.payment_method')
                                   ->where('t.detail', 'like', '%Transfer Bank%'); // Bank transfer with NULL payment_method
                      });
            })
            ->where(function($query) {
                $query->where('t.status', '!=', 0) // Exclude pending payments
                      ->orWhere('t.payment_method', '!=', 'midtrans') // Or exclude Midtrans pending
                      ->orWhere(function($subQuery) {
                          $subQuery->whereNull('t.payment_method')
                                   ->where('t.detail', 'like', '%Transfer Bank%'); // Include bank transfer pending
                      });
            });
        if ($fromDate) {
            $onlinePaymentsQuery->whereDate('t.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $onlinePaymentsQuery->whereDate('t.created_at', '<=', $toDate);
        }
        $onlinePayments = $onlinePaymentsQuery
            ->select(
                't.transfer_id',
                't.created_at as payment_date',
                't.created_at',
                't.confirm_pay as amount',
                't.status',
                't.checkout_url',
                't.reference',
                DB::raw('NULL as payment_type'),
                't.confirm_pay as subtotal',
                DB::raw('CASE 
                    WHEN t.payment_method = "bank_transfer" OR (t.payment_method IS NULL AND t.detail LIKE "%Transfer Bank%") THEN "Bank Transfer"
                    ELSE "Online Payment"
                END as pos_name'),
                't.detail as desc',
                DB::raw('CASE 
                    WHEN t.payment_method = "midtrans" THEN "ONLINE"
                    WHEN t.payment_method = "bank_transfer" OR (t.payment_method IS NULL AND t.detail LIKE "%Transfer Bank%") THEN "BANK_TRANSFER"
                    ELSE "ONLINE"
                END as transaction_type'),
                DB::raw('CASE 
                    WHEN t.payment_method = "bank_transfer" OR (t.payment_method IS NULL AND t.detail LIKE "%Transfer Bank%") THEN COALESCE(t.detail, "Transfer Bank")
                    ELSE COALESCE(t.detail, "Pembayaran Cart via Midtrans")
                END as display_name'),
                't.payment_method',
                't.gateway_transaction_id'
            )
            ->orderBy('t.status', 'asc')
            ->orderBy('t.created_at', 'desc')
            ->get();

        // Get tabungan transactions specifically
        $tabunganTransactionsQuery = DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->where('t.student_id', $studentId)
            ->where('td.payment_type', 3) // Tabungan type
            ->where('td.is_tabungan', 1); // Marked as tabungan
            
        if ($fromDate) {
            $tabunganTransactionsQuery->whereDate('t.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $tabunganTransactionsQuery->whereDate('t.created_at', '<=', $toDate);
        }
        
        $tabunganTransactions = $tabunganTransactionsQuery
            ->select(
                't.transfer_id',
                't.created_at as payment_date',
                't.created_at',
                't.confirm_pay as amount',
                't.status',
                't.checkout_url',
                't.reference',
                DB::raw('3 as payment_type'),
                't.confirm_pay as subtotal',
                DB::raw('"Tabungan" as pos_name'),
                't.detail as desc',
                DB::raw('"TABUNGAN" as transaction_type'),
                DB::raw('COALESCE(t.detail, "Setor Tabungan") as display_name'),
                't.payment_method',
                't.gateway_transaction_id'
            )
            ->orderBy('t.status', 'asc')
            ->orderBy('t.created_at', 'desc')
            ->get();

        // Combine all transaction types
        $allTransferTransactions = $transferTransactions->concat($onlinePayments)->concat($tabunganTransactions);

        // Get cash payment history from log_trx table
        $cashBulananTransactionsQuery = DB::table('log_trx as lt')
            ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
            ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->leftJoin('users as u', 'b.user_user_id', '=', 'u.id')
            ->leftJoin('transfer as t', function($join) {
                $join->on('t.student_id', '=', 'lt.student_student_id')
                     ->on('t.confirm_date', '=', 'lt.log_trx_input_date')
                     ->on('t.confirm_pay', '=', 'b.bulan_bill');
            })
            ->where('lt.student_student_id', $studentId)
            ->whereNotNull('lt.bulan_bulan_id')
            ->distinct();
        if ($fromDate) {
            $cashBulananTransactionsQuery->whereDate('lt.log_trx_input_date', '>=', $fromDate);
        }
        if ($toDate) {
            $cashBulananTransactionsQuery->whereDate('lt.log_trx_input_date', '<=', $toDate);
        }
        $cashBulananTransactions = $cashBulananTransactionsQuery
            ->select(
                'lt.log_trx_id',
                'lt.log_trx_input_date as payment_date',
                'p.payment_type',
                'pos.pos_name',
                'b.bulan_bill as amount',
                'b.bulan_number_pay as payment_number',
                DB::raw('COALESCE(u.name, "Super Admin") as cashier_name'),
                DB::raw('"CASH_BULANAN" as transaction_type'),
                DB::raw('1 as status'),
                DB::raw('COALESCE(t.reference, CONCAT("CASH-", DATE_FORMAT(lt.log_trx_input_date, "%Y%m%d"), "-", lt.log_trx_id)) as reference'),
                DB::raw('CONCAT(pos.pos_name, "-", 
                    CASE b.month_month_id
                        WHEN 1 THEN "Juli"
                        WHEN 2 THEN "Agustus"
                        WHEN 3 THEN "September"
                        WHEN 4 THEN "Oktober"
                        WHEN 5 THEN "November"
                        WHEN 6 THEN "Desember"
                        WHEN 7 THEN "Januari"
                        WHEN 8 THEN "Februari"
                        WHEN 9 THEN "Maret"
                        WHEN 10 THEN "April"
                        WHEN 11 THEN "Mei"
                        WHEN 12 THEN "Juni"
                        ELSE "Unknown"
                    END, " (", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"), ")"
                ) as display_name')
            )
            ->get();

        $cashBebasTransactionsQuery = DB::table('log_trx as lt')
            ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
            ->leftJoin('bebas as b', 'bp.bebas_bebas_id', '=', 'b.bebas_id')
            ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->leftJoin('users as u', 'bp.user_user_id', '=', 'u.id')
            ->where('lt.student_student_id', $studentId)
            ->whereNotNull('lt.bebas_pay_bebas_pay_id')
            ->distinct();
        if ($fromDate) {
            $cashBebasTransactionsQuery->whereDate('lt.log_trx_input_date', '>=', $fromDate);
        }
        if ($toDate) {
            $cashBebasTransactionsQuery->whereDate('lt.log_trx_input_date', '<=', $toDate);
        }
        $cashBebasTransactions = $cashBebasTransactionsQuery
            ->select(
                'lt.log_trx_id',
                'lt.log_trx_input_date as payment_date',
                'p.payment_type',
                'pos.pos_name',
                'bp.bebas_pay_bill as amount',
                'bp.bebas_pay_number as payment_number',
                DB::raw('"CASH_BEBAS" as transaction_type'),
                DB::raw('1 as status'),
                DB::raw('COALESCE(u.name, "Super Admin") as cashier_name'),
                DB::raw('CONCAT("CASH-", DATE_FORMAT(lt.log_trx_input_date, "%Y%m%d"), "-", lt.log_trx_id) as reference'),
                DB::raw('CONCAT(pos.pos_name, " - ", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026")) as display_name')
            )
            ->get();

        // Get view type parameter
        $viewType = $request->get('view', 'kuitansi');
        
        if ($viewType === 'kuitansi') {
            // "Per Kuitansi" view - show all transactions grouped by reference (kuitansi number)
            $pendingOnlineTransactions = $allTransferTransactions->where('status', 0)->where('checkout_url', '!=', '')->where('checkout_url', '!=', null);
            
            // Get pending online payments from transfer table
            $pendingOnlinePayments = $onlinePayments->where('status', 0);
            
            // Combine all transactions
            $allTransactions = $pendingOnlineTransactions->concat($pendingOnlinePayments)->concat($cashBulananTransactions)->concat($cashBebasTransactions);
            
            // Remove duplicates based on unique identifier
            $uniqueTransactions = $allTransactions->unique(function ($item) {
                // Use log_trx_id for cash transactions, transfer_id for online transactions
                $uniqueId = $item->log_trx_id ?? $item->transfer_id ?? null;
                $paymentDate = $item->payment_date ?? $item->created_at;
                $amount = $item->amount;
                $transactionType = $item->transaction_type ?? '';
                $displayName = $item->display_name ?? '';
                
                // Create a unique key that includes all relevant fields
                return $uniqueId . '_' . $paymentDate . '_' . $amount . '_' . $transactionType . '_' . $displayName;
            });
            
            // Group by reference for pending transactions, by date for successful transactions
            $referenceGroups = $uniqueTransactions->groupBy(function($item) {
                $status = $item->status ?? 1;
                
                if ($status == 0) {
                    // For pending transactions, use reference
                    $reference = $item->reference ?? $item->payment_number ?? null;
                    
                    if (!$reference) {
                        $date = $item->payment_date ?? $item->created_at;
                        $transactionType = $item->transaction_type ?? '';
                        return date('Y-m-d', strtotime($date)) . '_' . $transactionType;
                    }
                    
                    return $reference;
                } else {
                    // For successful transactions, group by date
                    $date = $item->payment_date ?? $item->created_at;
                    return date('Y-m-d', strtotime($date));
                }
            });
            
            $receiptGroups = collect();
            
            foreach ($referenceGroups as $reference => $items) {
                $firstItem = $items->first();
                $totalAmount = $items->sum('amount');
                
                // Use reference for pending transactions, generate receipt number for successful transactions
                $status = $firstItem->status ?? 1;
                if ($status == 0) {
                    // For pending transactions, use reference
                    $receiptNumber = $reference ?? $firstItem->payment_number ?? 'PAY-' . time();
                } else {
                    // For successful transactions, generate receipt number based on transaction type
                    $date = $firstItem->payment_date ?? $firstItem->created_at;
                    
                    // Determine payment code based on transaction type (will be set after transaction type is determined)
                    $paymentCode = 'PAY'; // Default fallback
                    
                    $receiptNumber = $paymentCode . '-' . date('Ymd', strtotime($date)) . '-' . str_pad($items->count(), 4, '0', STR_PAD_LEFT);
                }
                
                // Determine transaction type and status based on the items in this group
                $hasPendingOnline = $items->where('status', 0)->where('checkout_url', '!=', '')->count() > 0;
                $hasPendingOnlinePayments = $items->where('status', 0)->where('transaction_type', 'ONLINE')->count() > 0;
                $hasSuccessfulOnlinePayments = $items->where('status', 1)->where('transaction_type', 'ONLINE')->count() > 0;
                $hasPendingBankTransfer = $items->where('status', 0)->where('transaction_type', 'BANK_TRANSFER')->count() > 0;
                $hasSuccessfulBankTransfer = $items->where('status', 1)->where('transaction_type', 'BANK_TRANSFER')->count() > 0;
                $hasPendingTabungan = $items->where('status', 0)->where('transaction_type', 'TABUNGAN')->count() > 0;
                $hasSuccessfulTabungan = $items->where('status', 1)->where('transaction_type', 'TABUNGAN')->count() > 0;
                $hasCash = $items->whereIn('transaction_type', ['CASH_BULANAN', 'CASH_BEBAS'])->count() > 0;
                
                if ($hasPendingOnline || $hasPendingOnlinePayments) {
                    $transactionType = 'ONLINE_PENDING';
                    $displayName = 'Pembayaran Online Pending';
                    $status = 0;
                    $checkoutUrl = $items->where('status', 0)->where('checkout_url', '!=', '')->first()->checkout_url ?? null;
                } elseif ($hasSuccessfulOnlinePayments) {
                    $transactionType = 'ONLINE';
                    $displayName = 'Pembayaran Online';
                    $status = 1;
                    $checkoutUrl = null;
                } elseif ($hasPendingBankTransfer) {
                    $transactionType = 'BANK_TRANSFER';
                    $displayName = 'Pembayaran Transfer Bank';
                    $status = 0;
                    $checkoutUrl = null;
                } elseif ($hasSuccessfulBankTransfer) {
                    $transactionType = 'BANK_TRANSFER';
                    $displayName = 'Pembayaran Transfer Bank';
                    $status = 1;
                    $checkoutUrl = null;
                } elseif ($hasPendingTabungan) {
                    $transactionType = 'TABUNGAN';
                    $displayName = 'Setor Tabungan';
                    $status = 0;
                    $checkoutUrl = null;
                } elseif ($hasSuccessfulTabungan) {
                    $transactionType = 'TABUNGAN';
                    $displayName = 'Setor Tabungan';
                    $status = 1;
                    $checkoutUrl = null;
                } else {
                    $transactionType = 'TUNAI';
                    $displayName = 'Pembayaran Tunai';
                    $status = 1;
                    $checkoutUrl = null;
                }
                
                // Update receipt number based on determined transaction type
                if ($status == 1) {
                    $date = $firstItem->payment_date ?? $firstItem->created_at;
                    
                    // Determine payment code based on transaction type
                    if (in_array($transactionType, ['ONLINE', 'ONLINE_PENDING'])) {
                        $paymentCode = 'PG'; // Payment Gateway
                    } elseif ($transactionType === 'BANK_TRANSFER') {
                        $paymentCode = 'TF'; // Transfer Bank
                    } elseif ($transactionType === 'TABUNGAN') {
                        $paymentCode = 'TB'; // Tabungan
                    } elseif (in_array($transactionType, ['CASH_BULANAN', 'CASH_BEBAS', 'TUNAI'])) {
                        $paymentCode = 'CASH'; // Cash payment
                    } else {
                        $paymentCode = 'PAY'; // Default fallback
                    }
                    
                    $receiptNumber = $paymentCode . '-' . date('Ymd', strtotime($date)) . '-' . str_pad($items->count(), 4, '0', STR_PAD_LEFT);
                }
                
                // Get cashier name from the first item
                $cashierName = $firstItem->cashier_name ?? 'Super Admin';
                
                // Use created_at for pending transactions, payment_date for completed ones
                $paymentDate = $firstItem->payment_date ?? $firstItem->created_at;
                
                // Get transfer_id from the first pending online payment item
                $transferId = null;
                if ($status == 0 && ($transactionType === 'ONLINE_PENDING' || $transactionType === 'ONLINE' || $transactionType === 'BANK_TRANSFER' || $transactionType === 'TABUNGAN')) {
                    // Look for any pending item with transfer_id
                    $pendingItem = $items->where('status', 0)->first();
                    if ($pendingItem) {
                        $transferId = $pendingItem->transfer_id ?? null;
                    }
                }
                
                $receiptGroups->push((object)[
                    'receipt_id' => $status == 0 ? $reference : date('Y-m-d', strtotime($paymentDate)),
                    'payment_number' => $receiptNumber,
                    'payment_date' => $paymentDate,
                    'amount' => $totalAmount,
                    'transaction_type' => $transactionType,
                    'display_name' => $displayName,
                    'cashier' => $cashierName,
                    'is_receipt' => true,
                    'items' => $items,
                    'status' => $status,
                    'checkout_url' => $checkoutUrl,
                    'transfer_id' => $transferId
                ]);
            }
            
            $transactions = $receiptGroups->sortByDesc('payment_date')->values();
        } else {
            // "Per Item" view - show only cash transactions (bulanan and bebas), exclude online payments
            $allTransactions = $cashBulananTransactions->concat($cashBebasTransactions);
            
            // Remove duplicates based on log_trx_id for cash transactions
            $transactions = $allTransactions->unique(function ($item) {
                return $item->log_trx_id;
            })
            ->sortByDesc('payment_date')
            ->values();
        }

        // Return all transactions without pagination
        $payments = $transactions;

        return view('student.payment-history', compact('student', 'payments', 'viewType'));
    }

    /**
     * Melanjutkan pembayaran pending
     */


    /**
     * Menampilkan detail pembayaran
     */
    public function paymentDetail($id)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        
        $payment = DB::table('transfer as t')
            ->leftJoin('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
            ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
            ->leftJoin('payment as p', function($join) {
                $join->on('b.payment_payment_id', '=', 'p.payment_id')
                     ->orOn('be.payment_payment_id', '=', 'p.payment_id');
            })
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->where('t.transfer_id', $id)
            ->where('t.student_id', $studentId)
            ->where('t.status', 1) // Only show successful payments
            ->select(
                't.*',
                'td.payment_type',
                'td.subtotal',
                'td.desc',
                'pos.pos_name',
                DB::raw('CASE 
                    WHEN td.payment_type = 1 THEN "BULANAN"
                    WHEN td.payment_type = 2 THEN "BEBAS"
                    ELSE "UNKNOWN"
                END as bill_type')
            )
            ->first();

        if (!$payment) {
            return redirect()->route('student.payment-history')->with('error', 'Pembayaran tidak ditemukan');
        }

        $student = Student::with(['class'])->find($studentId);

        return view('student.payment-detail', compact('student', 'payment'));
    }

    /**
     * Menampilkan detail kuitansi
     */
    public function receiptDetail($id, $type)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        $student = Student::with(['class'])->find($studentId);

        // Get all transactions for the specific date
        $targetDate = $id; // $id is now the date
        
        // Get transfer transactions for the date
        $transferTransactions = DB::table('transfer as t')
            ->leftJoin('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
            ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
            ->leftJoin('payment as p', function($join) {
                $join->on('b.payment_payment_id', '=', 'p.payment_id')
                     ->orOn('be.payment_payment_id', '=', 'p.payment_id');
            })
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->where('t.student_id', $studentId)
            ->where('t.status', 1)
            ->whereDate('t.confirm_date', $targetDate)
            ->select(
                't.transfer_id as receipt_id',
                't.confirm_date as payment_date',
                't.confirm_pay as total_amount',
                't.reference as payment_number',
                'td.payment_type',
                'td.subtotal as amount',
                'pos.pos_name',
                'td.desc',
                DB::raw('CASE 
                    WHEN td.payment_type = 1 THEN "BULANAN"
                    WHEN td.payment_type = 2 THEN "BEBAS"
                    ELSE "UNKNOWN"
                END as bill_type'),
                DB::raw('CASE 
                    WHEN td.payment_type = 1 THEN CONCAT(pos.pos_name, "-", 
                        CASE b.month_month_id
                            WHEN 1 THEN "Juli"
                            WHEN 2 THEN "Agustus"
                            WHEN 3 THEN "September"
                            WHEN 4 THEN "Oktober"
                            WHEN 5 THEN "November"
                            WHEN 6 THEN "Desember"
                            WHEN 7 THEN "Januari"
                            WHEN 8 THEN "Februari"
                            WHEN 9 THEN "Maret"
                            WHEN 10 THEN "April"
                            WHEN 11 THEN "Mei"
                            WHEN 12 THEN "Juni"
                            ELSE "Unknown"
                        END, " (", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"), ")"
                    )
                    WHEN td.payment_type = 2 THEN CONCAT(pos.pos_name, " - ", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"))
                    ELSE td.desc
                END as display_name')
            )
            ->get();

        // Get cash transactions for the date (only successful ones)
        $cashBulananTransactions = DB::table('log_trx as lt')
            ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
            ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->leftJoin('users as u', 'b.user_user_id', '=', 'u.id')
            ->where('lt.student_student_id', $studentId)
            ->whereNotNull('lt.bulan_bulan_id')
            ->whereDate('lt.log_trx_input_date', $targetDate)
            ->where('b.bulan_status', 1) // Only successful payments
            ->select(
                'lt.log_trx_id as receipt_id',
                'lt.log_trx_input_date as payment_date',
                'p.payment_type',
                'pos.pos_name',
                'b.bulan_bill as amount',
                'b.bulan_number_pay as payment_number',
                DB::raw('COALESCE(u.name, "Super Admin") as cashier_name'),
                DB::raw('"BULANAN" as bill_type'),
                DB::raw('CONCAT(pos.pos_name, "-", 
                    CASE b.month_month_id
                        WHEN 1 THEN "Juli"
                        WHEN 2 THEN "Agustus"
                        WHEN 3 THEN "September"
                        WHEN 4 THEN "Oktober"
                        WHEN 5 THEN "November"
                        WHEN 6 THEN "Desember"
                        WHEN 7 THEN "Januari"
                        WHEN 8 THEN "Februari"
                        WHEN 9 THEN "Maret"
                        WHEN 10 THEN "April"
                        WHEN 11 THEN "Mei"
                        WHEN 12 THEN "Juni"
                        ELSE "Unknown"
                    END, " (", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"), ")"
                ) as display_name')
            )
            ->get();

        $cashBebasTransactions = DB::table('log_trx as lt')
            ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
            ->leftJoin('bebas as b', 'bp.bebas_bebas_id', '=', 'b.bebas_id')
            ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
            ->leftJoin('users as u', 'bp.user_user_id', '=', 'u.id')
            ->where('lt.student_student_id', $studentId)
            ->whereNotNull('lt.bebas_pay_bebas_pay_id')
            ->whereDate('lt.log_trx_input_date', $targetDate)
            // Only successful payments (if there's a log_trx record, it means it's paid)
            ->select(
                'lt.log_trx_id as receipt_id',
                'lt.log_trx_input_date as payment_date',
                'p.payment_type',
                'pos.pos_name',
                'bp.bebas_pay_bill as amount',
                'bp.bebas_pay_number as payment_number',
                DB::raw('COALESCE(u.name, "Super Admin") as cashier_name'),
                DB::raw('"BEBAS" as bill_type'),
                DB::raw('CONCAT(pos.pos_name, " - ", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026")) as display_name')
            )
            ->get();

        // Combine all transactions for the date
        $receipt = $transferTransactions->concat($cashBulananTransactions)->concat($cashBebasTransactions);

        if ($receipt->isEmpty()) {
            return redirect()->route('student.payment.history')->with('error', 'Kuitansi tidak ditemukan');
        }

        return view('student.receipt-detail', compact('student', 'receipt', 'type'));
    }

    /**
     * Download receipt pembayaran
     */
    public function downloadReceipt($id)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        
        $payment = DB::table('transfer as t')
            ->leftJoin('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
            ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
            ->leftJoin('payment as p', function($join) {
                $join->on('b.payment_payment_id', '=', 'p.payment_id')
                     ->orOn('be.payment_payment_id', '=', 'p.payment_id');
            })
            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->where('t.transfer_id', $id)
            ->where('t.student_id', $studentId)
            ->where('t.status', 1) // Only show successful payments
            ->select(
                't.*',
                'td.payment_type',
                'td.subtotal',
                'td.desc',
                'pos.pos_name',
                DB::raw('CASE 
                    WHEN td.payment_type = 1 THEN "BULANAN"
                    WHEN td.payment_type = 2 THEN "BEBAS"
                    ELSE "UNKNOWN"
                END as bill_type')
            )
            ->first();

        if (!$payment) {
            return redirect()->route('student.payment-history')->with('error', 'Pembayaran tidak ditemukan');
        }

        // Generate receipt PDF
        $student = Student::with(['class'])->find($studentId);
        
        return view('student.receipt', compact('student', 'payment'));
    }

    /**
     * Menampilkan form pembayaran untuk tagihan tertentu
     */
    public function paymentForm($studentId, $billType, $billId)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        // Verify that the student is accessing their own bills
        if (session('student_id') != $studentId) {
            return redirect()->route('student.bills')->with('error', 'Akses ditolak');
        }

        $student = Student::with(['class'])->find($studentId);
        
        if ($billType === 'bulanan') {
            $bill = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('b.bulan_id', $billId)
                ->where('b.student_student_id', $studentId)
                ->first();
        } else {
            $bill = DB::table('bebas as be')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('be.bebas_id', $billId)
                ->where('be.student_student_id', $studentId)
                ->first();
        }

        if (!$bill) {
            return redirect()->route('student.bills')->with('error', 'Tagihan tidak ditemukan');
        }
        
        // Get school bank information
        $schoolBank = DB::table('setup_gateways')->first();

        return view('student.payment-form', compact('student', 'bill', 'billType', 'schoolBank'));
    }

    /**
     * Prepare bank transfer data
     */
    public function prepareBankTransfer(Request $request)
    {
        // Check if student is logged in
        if (!session('is_student')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'cart_items' => 'required|string',
            'total_amount' => 'required|numeric|min:1'
        ]);

        try {
            $cartItems = json_decode($request->cart_items, true);
            $totalAmount = $request->total_amount;

            // Validate cart items
            if (!is_array($cartItems) || empty($cartItems)) {
                return response()->json(['success' => false, 'message' => 'Data keranjang tidak valid']);
            }

            // Store in session
            session([
                'cart_items' => $cartItems,
                'total_amount' => $totalAmount
            ]);
            
            \Log::info('Bank transfer prepared successfully', [
                'student_id' => session('student_id'),
                'cart_count' => count($cartItems),
                'total_amount' => $totalAmount
            ]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Bank transfer prepare error', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
                'student_id' => session('student_id')
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Halaman transfer bank
     */
    public function bankTransfer()
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        
        // Get cart items from session
        $cartItems = session('cart_items', []);
        $totalAmount = session('total_amount', 0);
        
        if (empty($cartItems)) {
            return redirect()->route('student.cart')->with('error', 'Keranjang kosong');
        }

        // Get school bank account information
        $schoolBank = DB::table('setup_gateways')->first();
        
        return view('student.bank-transfer', compact('cartItems', 'totalAmount', 'schoolBank'));
    }

    /**
     * Proses pembayaran transfer bank
     */
    public function processBankTransfer(Request $request)
    {
        
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        
        // Validate request - simplified for debugging
        $request->validate([
            'transfer_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            
            DB::beginTransaction();

            // Generate payment number
            $paymentNumber = 'TF-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Handle file upload
            $proofFile = null;
            if ($request->hasFile('transfer_proof')) {
                try {
                    $file = $request->file('transfer_proof');
                    // Ensure we get the correct extension without duplication
                    $extension = strtolower($file->getClientOriginalExtension());
                    $fileName = 'transfer_proof_' . $paymentNumber . '.' . $extension;
                    $proofFile = $file->storeAs('payment_proofs', $fileName, 'public');
                } catch (\Exception $e) {
                    throw $e;
                }
            } else {
                throw new \Exception('File bukti transfer tidak ditemukan');
            }

            // Get cart items from session
            $cartItems = session('cart_items', []);
            $totalAmount = session('total_amount', 0);
            
            
            // Create transfer record
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'detail' => 'Pembayaran Transfer Bank - ' . ($request->notes ?? ''),
                'status' => 0, // Pending
                'confirm_name' => $request->sender_name ?? 'Transfer Bank',
                'confirm_bank' => $request->sender_bank ?? 'Transfer Bank',
                'confirm_accnum' => $request->sender_account ?? 'Transfer Bank',
                'confirm_photo' => $proofFile,
                'confirm_pay' => (int) $totalAmount,
                'reference' => $paymentNumber,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            
            // Create transfer detail records for each item
            foreach ($cartItems as $item) {
                
                // Convert amount from "Rp 20.000" format to integer
                $amount = (int) str_replace(['Rp ', '.', ','], '', $item['amount']);
                
                
                DB::table('transfer_detail')->insert([
                    'transfer_id' => $transferId,
                    'payment_type' => $item['type'] === 'bulanan' ? 1 : 2,
                    'bulan_id' => $item['type'] === 'bulanan' ? $item['id'] : null,
                    'bebas_id' => $item['type'] === 'bebas' ? $item['id'] : null,
                    'desc' => $item['name'],
                    'subtotal' => $amount,
                    'is_tabungan' => 0 // Not tabungan payment
                ]);
            }

            DB::commit();

            // Clear cart
            session()->forget(['cart_items', 'total_amount']);

            // Kirim notifikasi WhatsApp jika diaktifkan
            try {
                $gateway = DB::table('setup_gateways')->first();
                if ($gateway && $gateway->enable_wa_notification) {
                    $whatsappService = new WhatsAppService();
                    $whatsappService->sendPaymentPendingNotification($transferId);
                    Log::info("WhatsApp notification sent for transfer_id: {$transferId}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp notification: " . $e->getMessage());
                // Jangan gagalkan proses pembayaran jika notifikasi gagal
            }

            return redirect()->route('payment.history')
                ->with('success', 'Bukti transfer berhasil dikirim. Mohon menunggu persetujuan dari admin dalam 1-2 hari kerja.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Memproses pembayaran online
     */
    public function processPayment(Request $request)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        
        // Validate request
        $request->validate([
            'bill_type' => 'required|in:bulanan,bebas',
            'bill_id' => 'required|integer',
            'payment_type' => 'required|in:realtime,manual',
            'amount' => 'required|numeric|min:10000', // Minimal Rp 10.000 untuk iPaymu
            'description' => 'nullable|string|max:500',
            // Conditional validation
            'payment_method' => 'required_if:payment_type,realtime|string',
            'manual_proof_file' => 'required_if:payment_type,manual|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'manual_bank_name' => 'required_if:payment_type,manual|string',
            'manual_account_number' => 'required_if:payment_type,manual|string',
            'manual_account_name' => 'required_if:payment_type,manual|string',
            'manual_notes' => 'nullable|string'
        ]);

        try {
            // Validate payment amount against bill remaining amount
            $validationResult = $this->validatePaymentAmount($request->bill_type, $request->bill_id, $studentId, $request->amount);
            if (!$validationResult['valid']) {
                return back()->with('error', $validationResult['message']);
            }

            if ($request->payment_type === 'realtime') {
                // Process iPaymu payment
                return $this->processIpaymuPayment($request, $studentId);
            } else {
                // Process manual payment
                return $this->processManualPayment($request, $studentId);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Process payment for tabungan
     */
    public function processTabunganPayment(Request $request)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        
        // Validate request
        $request->validate([
            'payment_type' => 'required|in:realtime,manual',
            'amount' => 'required|numeric|min:10000',
            'description' => 'nullable|string|max:500',
            // Conditional validation
            'payment_method' => 'required_if:payment_type,realtime|string',
            'manual_proof_file' => 'required_if:payment_type,manual|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'manual_notes' => 'nullable|string'
        ]);

        try {
            if ($request->payment_type === 'realtime') {
                // Process iPaymu payment for tabungan
                return $this->processIpaymuTabunganPayment($request, $studentId);
            } else {
                // Process manual payment for tabungan
                return $this->processManualTabunganPayment($request, $studentId);
            }

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memproses pembayaran tabungan: ' . $e->getMessage()
                ], 500);
            } else {
                return back()->with('error', 'Terjadi kesalahan saat memproses pembayaran tabungan: ' . $e->getMessage());
            }
        }
    }

    /**
     * Process iPaymu payment for tabungan
     */
    private function processIpaymuTabunganPayment(Request $request, $studentId)
    {
        try {
            // Get student data
            $student = Student::findOrFail($studentId);

            // Initialize iPaymu service
            $ipaymuService = new IpaymuService();

            // Generate reference ID for tabungan
            $referenceId = 'TABUNGAN-' . $studentId . '-' . time();

            Log::info('🔵 Processing iPaymu tabungan payment', [
                'reference_id' => $referenceId,
                'student_id' => $studentId,
                'amount' => $request->amount
            ]);

            // Prepare product data
            $product = ['Setor Tabungan'];
            $qty = [1];
            $price = [(int) $request->amount];

            // Prepare customer data for iPaymu
            $customerName = $student->student_full_name ?? 'Student';
            $customerPhone = $student->student_parent_phone ?? $student->student_phone ?? '081234567890';
            $studentNis = $student->student_nis ?? $studentId;
            $customerEmail = 'student' . $studentNis . '@sppqu.id';
            
            // Ensure phone format is valid
            if (!preg_match('/^(08|62)\d{8,}$/', $customerPhone)) {
                $customerPhone = '081234567890';
            }

            // Create iPaymu payment
            $ipaymuResponse = $ipaymuService->createPayment(
                $referenceId,
                'Setor Tabungan',
                (int) $request->amount,
                $customerName,
                $customerPhone,
                $customerEmail,
                $product,
                $qty,
                $price,
                route('api.ipaymu.callback'),
                route('student.tabungan.index')
            );

            Log::info('🔵 iPaymu tabungan API Response', [
                'success' => $ipaymuResponse['success'] ?? false,
                'data' => $ipaymuResponse['data'] ?? null
            ]);

            if (!$ipaymuResponse['success']) {
                throw new \Exception($ipaymuResponse['message'] ?? 'Gagal membuat transaksi pembayaran');
            }

            // Store payment data in transfer table
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'detail' => 'Setor Tabungan via iPaymu',
                'status' => 0, // Pending
                'confirm_pay' => $request->amount,
                'reference' => $referenceId,
                'merchantRef' => $referenceId,
                'gateway_transaction_id' => $ipaymuResponse['data']['transaction_id'] ?? null,
                'payment_method' => 'ipaymu',
                'bill_type' => 'tabungan',
                'bill_id' => 0, // Use 0 for tabungan (no specific bill)
                'payment_number' => $referenceId,
                'payment_details' => json_encode([
                    'description' => $request->description ?? 'Setor Tabungan',
                    'amount' => $request->amount,
                    'ipaymu_response' => $ipaymuResponse['data'] ?? []
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert to transfer_detail table for tabungan
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => 3, // Tabungan type
                'bulan_id' => null,
                'bebas_id' => null,
                'desc' => $request->description ?? 'Setor Tabungan',
                'subtotal' => $request->amount,
                'is_tabungan' => 1 // Mark as tabungan transaction
            ]);

            Log::info('✅ Tabungan transfer record created', [
                'transfer_id' => $transferId,
                'reference_id' => $referenceId,
                'is_va_payment' => $ipaymuResponse['data']['is_va_payment'] ?? false
            ]);

            // Handle VA payment vs redirect URL
            $isVaPayment = $ipaymuResponse['data']['is_va_payment'] ?? false;
            $paymentUrl = $ipaymuResponse['data']['payment_url'] ?? null;
            
            // If VA payment without URL, create instruction page link
            if ($isVaPayment && !$paymentUrl) {
                $paymentUrl = route('student.payment.va-instructions', [
                    'transfer_id' => $transferId,
                    'reference' => $referenceId
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran tabungan berhasil diproses',
                'payment_url' => $paymentUrl,
                'is_va_payment' => $isVaPayment,
                'payment_data' => $ipaymuResponse['data'] ?? [],
                'reference_id' => $referenceId,
                'transfer_id' => $transferId
            ]);

        } catch (\Exception $e) {
            Log::error('❌ iPaymu tabungan payment error', [
                'message' => $e->getMessage(),
                'student_id' => $studentId,
                'amount' => $request->amount,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process manual payment for tabungan
     */
    private function processManualTabunganPayment(Request $request, $studentId)
    {
        try {
            DB::beginTransaction();

            // Generate payment number
            $paymentNumber = 'TB-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Handle file upload (required for tabungan manual payment)
            $filePath = null;
            if ($request->hasFile('manual_proof_file')) {
                $file = $request->file('manual_proof_file');
                // Ensure we get the correct extension without duplication
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = 'tabungan_proof_' . $paymentNumber . '.' . $extension;
                $filePath = $file->storeAs('tabungan_proofs', $fileName, 'public');
            } else {
                throw new \Exception('Bukti transfer wajib diupload');
            }

            // Insert to transfer table for tabungan
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'detail' => 'Setor Tabungan Manual',
                'status' => 0, // Pending
                'confirm_name' => 'Transfer Bank', // Default value
                'confirm_bank' => 'Transfer Bank', // Default value
                'confirm_accnum' => 'Transfer Bank', // Default value
                'confirm_photo' => $filePath,
                'confirm_pay' => $request->amount,
                'reference' => $paymentNumber,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert to transfer_detail table for tabungan
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => 3, // Tabungan type
                'bulan_id' => null,
                'bebas_id' => null,
                'desc' => $request->description ?? 'Setor Tabungan Manual',
                'subtotal' => $request->amount,
                'is_tabungan' => 1 // Mark as tabungan transaction
            ]);

            DB::commit();

            // Kirim notifikasi WhatsApp jika diaktifkan
            try {
                $gateway = DB::table('setup_gateways')->first();
                if ($gateway && $gateway->enable_wa_notification) {
                    $whatsappService = new WhatsAppService();
                    $whatsappService->sendPaymentPendingNotification($transferId);
                    Log::info("WhatsApp notification sent for tabungan manual payment transfer_id: {$transferId}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp notification for tabungan manual payment: " . $e->getMessage());
                // Jangan gagalkan proses pembayaran jika notifikasi gagal
            }

            // Check if this is an AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Setoran tabungan manual berhasil diajukan. Mohon menunggu persetujuan dari admin dalam 1-2 hari kerja.',
                    'transfer_id' => $transferId
                ]);
            } else {
                return redirect()->route('payment.history')
                    ->with('success', 'Setoran tabungan manual berhasil diajukan. Mohon menunggu persetujuan dari admin dalam 1-2 hari kerja.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memproses setoran tabungan: ' . $e->getMessage()
                ], 500);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Process iPaymu payment
     */
    private function processIpaymuPayment(Request $request, $studentId)
    {
        try {
            // Get student data
            $student = Student::findOrFail($studentId);

            // Initialize iPaymu service
            $ipaymuService = new IpaymuService();

            // Generate reference ID
            $referenceId = 'BULANAN-' . $studentId . '-' . $request->bill_id . '-' . time();
            
            if ($request->bill_type === 'bebas') {
                $referenceId = 'BEBAS-' . $studentId . '-' . $request->bill_id . '-' . time();
            }

            Log::info('🔵 Processing iPaymu payment from student portal', [
                'reference_id' => $referenceId,
                'student_id' => $studentId,
                'bill_type' => $request->bill_type,
                'bill_id' => $request->bill_id,
                'amount' => $request->amount
            ]);

            // Get bill details
            $billDetails = $this->getBillDetails($request->bill_type, $request->bill_id, $studentId);
            
            if (!$billDetails) {
                throw new \Exception('Tagihan tidak ditemukan');
            }

            // Prepare product data
            $product = [$billDetails['name']];
            $qty = [1];
            $price = [(int) $request->amount];

            // Prepare customer data for iPaymu
            $customerName = $student->student_full_name ?? 'Student';
            $customerPhone = $student->student_parent_phone ?? $student->student_phone ?? '081234567890';
            $studentNis = $student->student_nis ?? $studentId;
            $customerEmail = 'student' . $studentNis . '@sppqu.id';
            
            // Ensure phone format is valid
            if (!preg_match('/^(08|62)\d{8,}$/', $customerPhone)) {
                $customerPhone = '081234567890';
            }

            // Create iPaymu payment
            $ipaymuResponse = $ipaymuService->createPayment(
                $referenceId,
                $billDetails['name'],
                (int) $request->amount,
                $customerName,
                $customerPhone,
                $customerEmail,
                $product,
                $qty,
                $price,
                route('api.ipaymu.callback'),
                route('student.payment.history')
            );

            Log::info('🔵 iPaymu API Response', [
                'success' => $ipaymuResponse['success'] ?? false,
                'data' => $ipaymuResponse['data'] ?? null
            ]);

            if (!$ipaymuResponse['success']) {
                throw new \Exception($ipaymuResponse['message'] ?? 'Gagal membuat transaksi pembayaran');
            }

            DB::beginTransaction();

            // Insert to transfer table
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'detail' => $billDetails['name'],
                'status' => 0, // Pending
                'confirm_pay' => $request->amount,
                'reference' => $referenceId,
                'merchantRef' => $referenceId,
                'gateway_transaction_id' => $ipaymuResponse['data']['transaction_id'] ?? null,
                'payment_number' => $referenceId,
                'payment_method' => 'ipaymu',
                'bill_type' => $request->bill_type,
                'bill_id' => $request->bill_id,
                'payment_details' => json_encode([
                    'bill_details' => $billDetails,
                    'ipaymu_response' => $ipaymuResponse['data'] ?? []
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert to transfer_detail table
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => $request->bill_type === 'bulanan' ? 1 : 2,
                'bulan_id' => $request->bill_type === 'bulanan' ? $request->bill_id : null,
                'bebas_id' => $request->bill_type === 'bebas' ? $request->bill_id : null,
                'desc' => $billDetails['name'],
                'subtotal' => $request->amount,
                'is_tabungan' => 0 // Not tabungan payment
            ]);

            DB::commit();

            Log::info('✅ Transfer record created successfully', [
                'transfer_id' => $transferId,
                'reference_id' => $referenceId,
                'is_va_payment' => $ipaymuResponse['data']['is_va_payment'] ?? false
            ]);

            // Kirim notifikasi WhatsApp jika diaktifkan
            try {
                $gateway = DB::table('setup_gateways')->first();
                if ($gateway && $gateway->enable_wa_notification) {
                    $whatsappService = new WhatsAppService();
                    $whatsappService->sendPaymentPendingNotification($transferId);
                    Log::info("WhatsApp notification sent for iPaymu transfer_id: {$transferId}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp notification for iPaymu: " . $e->getMessage());
                // Jangan gagalkan proses pembayaran jika notifikasi gagal
            }

            // Handle VA payment vs redirect URL
            $isVaPayment = $ipaymuResponse['data']['is_va_payment'] ?? false;
            $paymentUrl = $ipaymuResponse['data']['payment_url'] ?? null;
            
            // If VA payment without URL, create instruction page link
            if ($isVaPayment && !$paymentUrl) {
                $paymentUrl = route('student.payment.va-instructions', [
                    'transfer_id' => $transferId,
                    'reference' => $referenceId
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'payment_url' => $paymentUrl,
                'is_va_payment' => $isVaPayment,
                'payment_data' => $ipaymuResponse['data'] ?? [],
                'reference_id' => $referenceId,
                'transfer_id' => $transferId,
                'payment_method' => 'ipaymu'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('❌ iPaymu payment error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process manual payment
     */
    private function processManualPayment(Request $request, $studentId)
    {
        try {
            DB::beginTransaction();

            // Generate payment number
            $paymentNumber = 'TF-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Handle file upload
            $filePath = null;
            if ($request->hasFile('manual_proof_file')) {
                $file = $request->file('manual_proof_file');
                // Ensure we get the correct extension without duplication
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = 'payment_proof_' . $paymentNumber . '.' . $extension;
                $filePath = $file->storeAs('payment_proofs', $fileName, 'public');
            }

            // Insert to transfer table
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'detail' => 'Pembayaran Manual',
                'status' => 0, // Pending
                'confirm_name' => 'Transfer Bank', // Default value
                'confirm_bank' => 'Transfer Bank', // Default value
                'confirm_accnum' => 'Transfer Bank', // Default value
                'confirm_photo' => $filePath,
                'confirm_pay' => $request->amount,
                'reference' => $paymentNumber,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert to transfer_detail table
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => $request->bill_type === 'bulanan' ? 1 : 2,
                'bulan_id' => $request->bill_type === 'bulanan' ? $request->bill_id : null,
                'bebas_id' => $request->bill_type === 'bebas' ? $request->bill_id : null,
                'desc' => 'Pembayaran Manual',
                'subtotal' => $request->amount,
                'is_tabungan' => 0 // Not tabungan payment
            ]);

            DB::commit();

            // Kirim notifikasi WhatsApp jika diaktifkan
            try {
                $gateway = DB::table('setup_gateways')->first();
                if ($gateway && $gateway->enable_wa_notification) {
                    $whatsappService = new WhatsAppService();
                    $whatsappService->sendPaymentPendingNotification($transferId);
                    Log::info("WhatsApp notification sent for manual payment transfer_id: {$transferId}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp notification for manual payment: " . $e->getMessage());
                // Jangan gagalkan proses pembayaran jika notifikasi gagal
            }

            return redirect()->route('student.payment.history')
                ->with('success', 'Pembayaran manual berhasil diajukan. Mohon menunggu persetujuan dari admin dalam 1-2 hari kerja.');

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Validate payment amount against bill remaining amount
     */
    private function validatePaymentAmount($billType, $billId, $studentId, $amount)
    {
        try {
            if ($billType === 'bulanan') {
                // Get bulan bill details
                $bill = DB::table('bulan as b')
                    ->where('b.bulan_id', $billId)
                    ->where('b.student_student_id', $studentId)
                    ->whereNull('b.bulan_date_pay') // Only unpaid bills
                    ->select('b.bulan_bill')
                    ->first();

                if (!$bill) {
                    return [
                        'valid' => false,
                        'message' => 'Tagihan bulanan tidak ditemukan atau sudah lunas'
                    ];
                }

                $totalBill = $bill->bulan_bill;
                $remainingAmount = $totalBill;

                if ($amount > $remainingAmount) {
                    return [
                        'valid' => false,
                        'message' => "Jumlah pembayaran (Rp " . number_format($amount, 0, ',', '.') . ") melebihi sisa tagihan (Rp " . number_format($remainingAmount, 0, ',', '.') . ")"
                    ];
                }

            } else {
                // Get bebas bill details
                $bill = DB::table('bebas as be')
                    ->where('be.bebas_id', $billId)
                    ->where('be.student_student_id', $studentId)
                    ->whereNull('be.bebas_date_pay') // Only unpaid bills
                    ->select('be.bebas_bill', 'be.bebas_total_pay')
                    ->first();

                if (!$bill) {
                    return [
                        'valid' => false,
                        'message' => 'Tagihan bebas tidak ditemukan atau sudah lunas'
                    ];
                }

                $totalBill = $bill->bebas_bill;
                $paidAmount = $bill->bebas_total_pay ?? 0;
                $remainingAmount = $totalBill - $paidAmount;

                if ($amount > $remainingAmount) {
                    return [
                        'valid' => false,
                        'message' => "Jumlah pembayaran (Rp " . number_format($amount, 0, ',', '.') . ") melebihi sisa tagihan (Rp " . number_format($remainingAmount, 0, ',', '.') . ")"
                    ];
                }
            }

            return [
                'valid' => true,
                'message' => 'Validasi berhasil'
            ];

        } catch (\Exception $e) {
            \Log::error('Error validating payment amount', [
                'bill_type' => $billType,
                'bill_id' => $billId,
                'student_id' => $studentId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'message' => 'Terjadi kesalahan saat validasi pembayaran'
            ];
        }
    }

    /**
     * Get bill details
     */
    private function getBillDetails($billType, $billId, $studentId)
    {
        if ($billType === 'bulanan') {
            $bill = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('b.bulan_id', $billId)
                ->where('b.student_student_id', $studentId)
                ->select('pos.pos_name', 'b.month_month_id')
                ->first();

            if ($bill) {
                $monthNames = [
                    1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
                    5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari', 
                    9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                ];
                
                return [
                    'name' => $bill->pos_name . ' - ' . ($monthNames[$bill->month_month_id] ?? 'Unknown')
                ];
            }
        } else {
            $bill = DB::table('bebas as be')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('be.bebas_id', $billId)
                ->where('be.student_student_id', $studentId)
                ->select('pos.pos_name')
                ->first();

            if ($bill) {
                return [
                    'name' => $bill->pos_name
                ];
            }
        }

        return null;
    }

    /**
     * Process payment from cart
     */
    public function processCartPayment(Request $request)
    {
        try {
            $studentId = auth()->guard('student')->id();
            $cartItems = session('cart_items', []);

            if (empty($cartItems)) {
                return response()->json(['error' => 'Cart is empty'], 400);
            }

            // Calculate total amount
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['amount'];
            }

            // Generate order ID
            $orderId = 'CART-' . $studentId . '-' . time();

            // Store cart payment data in database for webhook processing
            DB::table('cart_payment_temp')->insert([
                'order_id' => $orderId,
                'student_id' => $studentId,
                'cart_items' => json_encode($cartItems),
                'total_amount' => $totalAmount,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create Midtrans Snap Token
            $snapToken = null;
            try {
                $snapToken = \App\Helpers\MidtransHelper::createSnapToken([
                    'order_id' => $orderId,
                    'amount' => $totalAmount,
                    'customer_details' => [
                        'first_name' => auth()->guard('student')->user()->student_name,
                        'email' => auth()->guard('student')->user()->student_email ?? 'student@example.com',
                    ],
                    'item_details' => $cartItems,
                    'notification_url' => url('/api/midtrans/webhook')
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create Midtrans Snap Token', [
                    'error' => $e->getMessage(),
                    'order_id' => $orderId
                ]);
                
                // Fallback: try with SDK
                try {
                    $snapToken = \App\Helpers\MidtransHelper::createSnapTokenWithSDK([
                        'order_id' => $orderId,
                        'amount' => $totalAmount,
                        'customer_details' => [
                            'first_name' => auth()->guard('student')->user()->student_name,
                            'email' => auth()->guard('student')->user()->student_email ?? 'student@example.com',
                        ],
                        'item_details' => $cartItems,
                        'notification_url' => url('/api/midtrans/webhook')
                    ]);
                } catch (\Exception $e2) {
                    Log::error('Failed to create Midtrans Snap Token with SDK', [
                        'error' => $e2->getMessage(),
                        'order_id' => $orderId
                    ]);
                    return response()->json(['error' => 'Failed to create payment token'], 500);
                }
            }

            if (!$snapToken) {
                return response()->json(['error' => 'Failed to create payment token'], 500);
            }

            // Store cart payment data in database only (NO SESSION)
            // Session data will be retrieved from database in webhook

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId
            ]);

        } catch (\Exception $e) {
            Log::error('Cart payment processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * ==========================================
     * E-JURNAL HARIAN 7KAIH METHODS
     * ==========================================
     */

    /**
     * Dashboard E-Jurnal untuk siswa
     */
    public function jurnalIndex()
    {
        $studentId = session('student_id');
        
        // Get recent journals
        $journals = \App\Models\JurnalHarian::where('siswa_id', $studentId)
            ->orderBy('tanggal', 'desc')
            ->with('entries.kategori')
            ->take(7)
            ->get();
        
        // Get monthly stats
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $monthlyJournals = \App\Models\JurnalHarian::where('siswa_id', $studentId)
            ->whereMonth('tanggal', $currentMonth)
            ->whereYear('tanggal', $currentYear)
            ->count();
        
        // Monthly average no longer applicable with new format (checklist-based)
        $monthlyAvg = null;
        
        // Get pending verification count
        $pendingCount = \App\Models\JurnalHarian::where('siswa_id', $studentId)
            ->where('status', 'submitted')
            ->count();
        
        // Check if today's journal exists
        $todayJournal = \App\Models\JurnalHarian::where('siswa_id', $studentId)
            ->whereDate('tanggal', today())
            ->first();
        
        return view('student.jurnal.index', compact(
            'journals', 
            'monthlyJournals', 
            'monthlyAvg', 
            'pendingCount',
            'todayJournal'
        ));
    }

    /**
     * Form isi jurnal harian
     */
    public function jurnalCreate()
    {
        $studentId = session('student_id');
        
        // Check if today's journal already exists
        $existingJournal = \App\Models\JurnalHarian::where('siswa_id', $studentId)
            ->whereDate('tanggal', today())
            ->first();
        
        if ($existingJournal) {
            return redirect()
                ->route('student.jurnal.edit', $existingJournal->jurnal_id)
                ->with('info', 'Jurnal hari ini sudah ada. Silakan edit jika ingin mengubah.');
        }
        
        // Get all categories
        $categories = \App\Models\JurnalKategori::orderBy('urutan')->get();
        
        return view('student.jurnal.create', compact('categories'));
    }

    /**
     * Simpan jurnal harian
     */
    public function jurnalStore(Request $request)
    {
        $studentId = session('student_id');
        
        // Log incoming request for debugging
        \Log::info('Jurnal Store Request', [
            'student_id' => $studentId,
            'has_foto' => $request->hasFile('foto'),
            'kategori_count' => $request->has('kategori') ? count($request->kategori) : 0,
            'all_data' => $request->except('foto'),
        ]);
        
        // Check student_id
        if (!$studentId) {
            \Log::error('No student_id in session');
            return back()->with('error', 'Session expired. Silakan login kembali.');
        }
        
        // Validation
        try {
            $validated = $request->validate([
                'tanggal' => 'required|date',
                'catatan_umum' => 'nullable|string',
                'foto' => 'nullable|image|max:2048',
                'kategori' => 'required|array|min:7',
                'kategori.*.jam' => 'nullable|date_format:H:i',
                'kategori.*.checklist' => 'nullable|array',
                'kategori.*.keterangan' => 'nullable|string|max:1000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except('foto'),
            ]);
            throw $e;
        }
        
        // Check if journal for this date already exists
        $existingJournal = \App\Models\JurnalHarian::where('siswa_id', $studentId)
            ->whereDate('tanggal', $validated['tanggal'])
            ->first();
        
        if ($existingJournal) {
            \Log::warning('Journal already exists', [
                'student_id' => $studentId,
                'date' => $validated['tanggal'],
            ]);
            return back()->with('error', 'Jurnal untuk tanggal ini sudah ada.');
        }
        
        \DB::beginTransaction();
        try {
            // Handle photo upload
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                try {
                    $fotoPath = $request->file('foto')->store('jurnal_photos', 'public');
                    \Log::info('Photo uploaded', ['path' => $fotoPath]);
                } catch (\Exception $e) {
                    \Log::error('Photo upload failed', ['error' => $e->getMessage()]);
                }
            }
            
            // Create jurnal harian
            $jurnal = \App\Models\JurnalHarian::create([
                'siswa_id' => $studentId,
                'tanggal' => $validated['tanggal'],
                'catatan_umum' => $validated['catatan_umum'],
                'foto' => $fotoPath,
                'status' => 'submitted', // Changed from 'pending' to 'submitted'
            ]);
            
            \Log::info('Jurnal created', ['jurnal_id' => $jurnal->jurnal_id]);
            
            // Create entries for each category
            $entriesCreated = 0;
            foreach ($validated['kategori'] as $kategoriId => $data) {
                $entry = \App\Models\JurnalEntry::create([
                    'jurnal_id' => $jurnal->jurnal_id,
                    'kategori_id' => $kategoriId,
                    'jam' => $data['jam'] ?? null,
                    'checklist_data' => isset($data['checklist']) ? json_encode($data['checklist']) : null,
                    'keterangan' => $data['keterangan'] ?? null,
                ]);
                $entriesCreated++;
                \Log::info('Entry created', [
                    'entry_id' => $entry->entry_id,
                    'kategori_id' => $kategoriId,
                    'jam' => $data['jam'] ?? 'null',
                    'has_checklist' => isset($data['checklist']),
                ]);
            }
            
            \DB::commit();
            
            \Log::info('Jurnal saved successfully', [
                'jurnal_id' => $jurnal->jurnal_id,
                'entries_count' => $entriesCreated,
            ]);
            
            return redirect()
                ->route('student.jurnal.index')
                ->with('success', 'Jurnal berhasil disimpan! Menunggu verifikasi guru.');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error saving journal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan jurnal: ' . $e->getMessage());
        }
    }

    /**
     * Detail jurnal
     */
    public function jurnalShow($id)
    {
        $studentId = session('student_id');
        
        $jurnal = \App\Models\JurnalHarian::where('jurnal_id', $id)
            ->where('siswa_id', $studentId)
            ->with(['entries.kategori'])
            ->firstOrFail();
        
        return view('student.jurnal.show', compact('jurnal'));
    }

    /**
     * Form edit jurnal
     */
    public function jurnalEdit($id)
    {
        $studentId = session('student_id');
        
        $jurnal = \App\Models\JurnalHarian::where('jurnal_id', $id)
            ->where('siswa_id', $studentId)
            ->with(['entries.kategori'])
            ->firstOrFail();
        
        // Only allow edit if status is submitted or revised
        if (!in_array($jurnal->status, ['submitted', 'revised'])) {
            return redirect()
                ->route('student.jurnal.show', $id)
                ->with('error', 'Jurnal yang sudah diverifikasi tidak bisa diedit.');
        }
        
        $categories = \App\Models\JurnalKategori::orderBy('urutan')->get();
        
        return view('student.jurnal.edit', compact('jurnal', 'categories'));
    }

    /**
     * Update jurnal
     */
    public function jurnalUpdate(Request $request, $id)
    {
        $studentId = session('student_id');
        
        $jurnal = \App\Models\JurnalHarian::where('jurnal_id', $id)
            ->where('siswa_id', $studentId)
            ->firstOrFail();
        
        // Only allow edit if status is submitted or revised
        if (!in_array($jurnal->status, ['submitted', 'revised'])) {
            return redirect()
                ->route('student.jurnal.show', $id)
                ->with('error', 'Jurnal yang sudah diverifikasi tidak bisa diedit.');
        }
        
        // Validation
        $validated = $request->validate([
            'catatan_umum' => 'nullable|string',
            'foto' => 'nullable|image|max:2048',
            'kategori' => 'required|array|min:7',
            'kategori.*.jam' => 'nullable|date_format:H:i',
            'kategori.*.checklist' => 'nullable|array',
            'kategori.*.keterangan' => 'nullable|string|max:1000',
        ]);
        
        \DB::beginTransaction();
        try {
            // Handle photo upload
            if ($request->hasFile('foto')) {
                // Delete old photo
                if ($jurnal->foto) {
                    \Storage::disk('public')->delete($jurnal->foto);
                }
                $jurnal->foto = $request->file('foto')->store('jurnal_photos', 'public');
            }
            
            // Update jurnal
            $jurnal->catatan_umum = $validated['catatan_umum'];
            $jurnal->status = 'submitted'; // Reset to submitted for re-verification
            $jurnal->save();
            
            // Update entries
            foreach ($validated['kategori'] as $kategoriId => $data) {
                \App\Models\JurnalEntry::updateOrCreate(
                    [
                        'jurnal_id' => $jurnal->jurnal_id,
                        'kategori_id' => $kategoriId,
                    ],
                    [
                        'jam' => $data['jam'] ?? null,
                        'checklist_data' => isset($data['checklist']) ? json_encode($data['checklist']) : null,
                        'keterangan' => $data['keterangan'] ?? null,
                    ]
                );
            }
            
            \DB::commit();
            
            return redirect()
                ->route('student.jurnal.index')
                ->with('success', 'Jurnal berhasil diperbarui!');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating journal: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memperbarui jurnal.');
        }
    }

    /**
     * Delete jurnal
     */
    public function jurnalDelete($id)
    {
        $studentId = session('student_id');
        
        $jurnal = \App\Models\JurnalHarian::where('jurnal_id', $id)
            ->where('siswa_id', $studentId)
            ->firstOrFail();
        
        // Only allow delete if status is submitted
        if ($jurnal->status !== 'submitted') {
            return back()->with('error', 'Jurnal yang sudah diverifikasi tidak bisa dihapus.');
        }
        
        try {
            // Delete photo if exists
            if ($jurnal->foto) {
                \Storage::disk('public')->delete($jurnal->foto);
            }
            
            $jurnal->delete();
            
            return redirect()
                ->route('student.jurnal.index')
                ->with('success', 'Jurnal berhasil dihapus.');
            
        } catch (\Exception $e) {
            \Log::error('Error deleting journal: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus jurnal.');
        }
    }

    /**
     * Rekap jurnal (list months)
     */
    public function jurnalRekap()
    {
        $studentId = session('student_id');
        
        // Get available months with journals
        $months = \App\Models\JurnalHarian::where('siswa_id', $studentId)
            ->selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month, COUNT(*) as count')
            ->groupByRaw('YEAR(tanggal), MONTH(tanggal)')
            ->orderByRaw('YEAR(tanggal) DESC, MONTH(tanggal) DESC')
            ->get();
        
        return view('student.jurnal.rekap', compact('months'));
    }

    /**
     * Rekap bulanan dengan grafik
     */
    public function jurnalRekapBulanan($month, $year)
    {
        $studentId = session('student_id');
        
        // Get all journals for the month
        $journals = \App\Models\JurnalHarian::where('siswa_id', $studentId)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->with(['entries.kategori'])
            ->orderBy('tanggal', 'asc')
            ->get();
        
        if ($journals->isEmpty()) {
            return redirect()
                ->route('student.jurnal.rekap')
                ->with('error', 'Tidak ada data jurnal untuk bulan ini.');
        }
        
        // Prepare data for charts
        $categories = \App\Models\JurnalKategori::orderBy('urutan')->get();
        
        // Average per category
        $categoryAverages = [];
        foreach ($categories as $category) {
            $avg = \App\Models\JurnalEntry::whereHas('jurnalHarian', function($q) use ($studentId, $month, $year) {
                    $q->where('siswa_id', $studentId)
                      ->whereMonth('tanggal', $month)
                      ->whereYear('tanggal', $year);
                })
                ->where('kategori_id', $category->kategori_id)
                ->avg('nilai');
            
            $categoryAverages[$category->nama_kategori] = round($avg, 2);
        }
        
        // Daily trends
        $dailyTrends = [];
        foreach ($journals as $jurnal) {
            $avgScore = $jurnal->entries->avg('nilai');
            $dailyTrends[] = [
                'date' => $jurnal->tanggal->format('d M'),
                'score' => round($avgScore, 2),
            ];
        }
        
        $monthName = \Carbon\Carbon::create($year, $month)->format('F Y');
        
        return view('student.jurnal.rekap-bulanan', compact(
            'journals',
            'categories',
            'categoryAverages',
            'dailyTrends',
            'monthName',
            'month',
            'year'
        ));
    }

    /**
     * ==========================================
     * BIMBINGAN KONSELING (BK) METHODS
     * ==========================================
     */

    /**
     * Halaman BK Siswa - menampilkan pelanggaran dan bimbingan konseling
     */
    public function bkIndex()
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        
        // Get student data
        $student = Student::with(['class'])->find($studentId);

        // Get pelanggaran siswa
        $pelanggaran = DB::table('pelanggaran_siswa as ps')
            ->join('pelanggaran as p', 'ps.pelanggaran_id', '=', 'p.id')
            ->join('pelanggaran_kategori as pk', 'p.kategori_id', '=', 'pk.id')
            ->where('ps.siswa_id', $studentId)
            ->select(
                'ps.*',
                'p.nama as nama_pelanggaran',
                'pk.nama as kategori',
                'p.point as poin',
                'p.keterangan as deskripsi_pelanggaran'
            )
            ->orderBy('ps.tanggal_pelanggaran', 'desc')
            ->get();

        // Calculate statistics
        $totalPoin = $pelanggaran->sum('poin');
        $totalPelanggaran = $pelanggaran->count();
        
        // Group by kategori
        $pelanggaranByKategori = $pelanggaran->groupBy('kategori')->map(function($items) {
            return [
                'count' => $items->count(),
                'poin' => $items->sum('poin')
            ];
        });

        // Get bimbingan konseling
        $bimbingan = DB::table('bimbingan_konseling as bk')
            ->where('bk.siswa_id', $studentId)
            ->leftJoin('users as u', 'bk.guru_bk_id', '=', 'u.id')
            ->select('bk.*', 'u.name as nama_guru')
            ->orderBy('bk.tanggal_bimbingan', 'desc')
            ->get();

        $totalBimbingan = $bimbingan->count();

        return view('student.bk.index', compact(
            'student',
            'pelanggaran',
            'totalPoin',
            'totalPelanggaran',
            'pelanggaranByKategori',
            'bimbingan',
            'totalBimbingan'
        ));
    }

    /**
     * Form pengajuan bimbingan konseling online
     */
    public function bkCreateBimbingan()
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');
        $student = Student::with(['class'])->find($studentId);

        return view('student.bk.create-bimbingan', compact('student'));
    }

    /**
     * Store pengajuan bimbingan konseling online
     */
    public function bkStoreBimbingan(Request $request)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jenis_bimbingan' => 'required|string|max:100',
            'deskripsi_masalah' => 'required|string',
            'harapan' => 'nullable|string',
        ]);

        $studentId = session('student_id');

        // Get default admin BK or first superadmin as temporary guru_bk_id
        $defaultGuruBK = DB::table('users')
            ->where(function($query) {
                $query->where('is_bk', 1)
                      ->orWhere('role', 'superadmin');
            })
            ->first();

        if (!$defaultGuruBK) {
            return redirect()->route('student.bk.index')
                ->with('error', 'Tidak ada Guru BK yang tersedia. Silakan hubungi admin.');
        }

        DB::table('bimbingan_konseling')->insert([
            'siswa_id' => $studentId,
            'jenis_bimbingan' => $validated['jenis_bimbingan'],
            'kategori' => 'ringan', // Default kategori
            'permasalahan' => $validated['deskripsi_masalah'],
            'hasil' => $validated['harapan'] ?? null,
            'tanggal_bimbingan' => $validated['tanggal'],
            'sesi_ke' => 1,
            'status' => 'dijadwalkan',
            'catatan' => 'Pengajuan dari siswa',
            'guru_bk_id' => $defaultGuruBK->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('student.bk.index')
            ->with('success', 'Pengajuan bimbingan konseling berhasil dikirim. Guru BK akan segera menghubungi Anda.');
    }

    /**
     * Detail pelanggaran siswa
     */
    public function bkShowPelanggaran($id)
    {
        if (!session('is_student')) {
            return redirect()->route('student.login');
        }

        $studentId = session('student_id');

        $pelanggaran = DB::table('pelanggaran_siswa as ps')
            ->join('pelanggaran as p', 'ps.pelanggaran_id', '=', 'p.id')
            ->join('pelanggaran_kategori as pk', 'p.kategori_id', '=', 'pk.id')
            ->join('students as s', 'ps.siswa_id', '=', 's.student_id')
            ->join('classes as c', 's.class_class_id', '=', 'c.class_id')
            ->leftJoin('users as u', 'ps.created_by', '=', 'u.id')
            ->where('ps.id', $id)
            ->where('ps.siswa_id', $studentId) // Pastikan hanya bisa lihat pelanggaran sendiri
            ->select(
                'ps.*',
                'p.nama as nama_pelanggaran',
                'pk.nama as kategori',
                'p.point as poin',
                'p.keterangan as deskripsi_pelanggaran',
                's.student_full_name',
                's.student_nis',
                'c.class_name',
                'u.name as nama_guru'
            )
            ->first();

        if (!$pelanggaran) {
            return redirect()->route('student.bk.index')
                ->with('error', 'Data pelanggaran tidak ditemukan.');
        }

        return view('student.bk.show-pelanggaran', compact('pelanggaran'));
    }

    /**
     * Process cart payment via iPaymu
     */
    public function processCartPaymentIpaymu(Request $request)
    {
        try {
            $studentId = session('student_id');
            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi expired. Silakan login kembali.'
                ], 401);
            }

            // Get student data
            $student = Student::findOrFail($studentId);

            // Parse cart items
            $cartItems = json_decode($request->cart_items, true);
            if (!$cartItems || count($cartItems) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang kosong'
                ], 400);
            }

            $totalAmount = (int) $request->total_amount;

            // Validate minimum amount for iPaymu (Rp 10.000)
            if ($totalAmount < 10000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total pembayaran minimal Rp 10.000 untuk pembayaran online iPaymu. Total saat ini: Rp ' . number_format($totalAmount, 0, ',', '.')
                ], 400);
            }

            // Initialize iPaymu service
            $ipaymuService = new IpaymuService();

            // Generate reference ID for cart payment
            $referenceId = 'CART-' . $studentId . '-' . time();

            Log::info('🛒 Processing cart payment via iPaymu', [
                'reference_id' => $referenceId,
                'student_id' => $studentId,
                'total_amount' => $totalAmount,
                'items_count' => count($cartItems)
            ]);

            // Prepare product data from cart items
            $products = [];
            $quantities = [];
            $prices = [];
            
            foreach ($cartItems as $item) {
                $itemName = $item['bill_name'] ?? 'Item Pembayaran';
                
                // Clean amount: remove "Rp", spaces, dots, and convert to integer
                $itemPrice = $item['amount'] ?? 0;
                if (is_string($itemPrice)) {
                    $itemPrice = (int) preg_replace('/[^0-9]/', '', $itemPrice);
                } else {
                    $itemPrice = (int) $itemPrice;
                }
                
                $products[] = $itemName;
                $quantities[] = 1;
                $prices[] = $itemPrice;
            }

            // Prepare customer data for iPaymu
            $customerName = $student->student_full_name ?? 'Student';
            $customerPhone = $student->student_parent_phone ?? $student->student_phone ?? '081234567890';
            
            // Generate valid email (iPaymu doesn't like generic emails)
            $studentNis = $student->student_nis ?? $studentId;
            $customerEmail = 'student' . $studentNis . '@sppqu.id';
            
            // Ensure phone format is valid (starts with 08 or 62, min 10 digits)
            if (!preg_match('/^(08|62)\d{8,}$/', $customerPhone)) {
                $customerPhone = '081234567890'; // Fallback to valid format
            }

            // Create iPaymu payment
            $ipaymuResponse = $ipaymuService->createPayment(
                $referenceId,
                'Pembayaran Keranjang - ' . count($cartItems) . ' item',
                $totalAmount,
                $customerName,
                $customerPhone,
                $customerEmail,
                $products,
                $quantities,
                $prices,
                route('api.ipaymu.callback'),
                route('student.payment.history')
            );

            Log::info('🔵 iPaymu cart payment response', [
                'success' => $ipaymuResponse['success'] ?? false,
                'reference_id' => $referenceId
            ]);

            if (!$ipaymuResponse['success']) {
                throw new \Exception($ipaymuResponse['message'] ?? 'Gagal membuat transaksi pembayaran');
            }

            DB::beginTransaction();

            // Create transfer record for cart payment
            // Note: transfer table uses 'transfer_id' as primary key
            $transferId = DB::table('transfer')->insertGetId([
                'student_id' => $studentId,
                'detail' => 'Pembayaran Keranjang - ' . count($cartItems) . ' item',
                'status' => 0, // Pending
                'confirm_pay' => $totalAmount,
                'reference' => $referenceId,
                'merchantRef' => $referenceId,
                'gateway_transaction_id' => $ipaymuResponse['data']['transaction_id'] ?? null,
                'payment_number' => $referenceId,
                'payment_method' => 'ipaymu',
                'bill_type' => 'cart',
                'bill_id' => 0,
                'payment_details' => json_encode([
                    'cart_items' => $cartItems,
                    'ipaymu_response' => $ipaymuResponse['data'] ?? []
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert transfer details for each cart item
            foreach ($cartItems as $item) {
                // Clean amount: remove "Rp", spaces, dots, and convert to integer
                $cleanAmount = $item['amount'] ?? 0;
                if (is_string($cleanAmount)) {
                    $cleanAmount = (int) preg_replace('/[^0-9]/', '', $cleanAmount);
                }
                
                DB::table('transfer_detail')->insert([
                    'transfer_id' => $transferId,
                    'payment_type' => $item['type'] === 'bulanan' ? 1 : 2,
                    'bulan_id' => $item['type'] === 'bulanan' ? $item['id'] : null,
                    'bebas_id' => $item['type'] === 'bebas' ? $item['id'] : null,
                    'desc' => $item['bill_name'] ?? 'Pembayaran',
                    'subtotal' => $cleanAmount,
                    'is_tabungan' => 0
                ]);
            }

            DB::commit();

            Log::info('✅ Cart payment transfer created', [
                'transfer_id' => $transferId,
                'reference_id' => $referenceId,
                'is_va_payment' => $ipaymuResponse['data']['is_va_payment'] ?? false
            ]);

            // For VA payments, redirect to instruction page instead of payment URL
            $isVaPayment = $ipaymuResponse['data']['is_va_payment'] ?? false;
            $paymentUrl = $ipaymuResponse['data']['payment_url'] ?? null;
            
            // If VA payment, create instruction URL
            if ($isVaPayment && !$paymentUrl) {
                $paymentUrl = route('student.payment.va-instructions', [
                    'transfer_id' => $transferId,
                    'reference' => $referenceId
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'payment_url' => $paymentUrl,
                'is_va_payment' => $isVaPayment,
                'payment_data' => $ipaymuResponse['data'] ?? [],
                'reference_id' => $referenceId,
                'transfer_id' => $transferId
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('❌ Cart payment iPaymu error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show VA payment instructions
     */
    public function showVaInstructions(Request $request)
    {
        try {
            $transferId = $request->get('transfer_id');
            $reference = $request->get('reference');
            
            Log::info('🔍 VA Instructions requested', [
                'transfer_id' => $transferId,
                'reference' => $reference
            ]);
            
            if (!$transferId || !$reference) {
                return redirect()->route('student.payment.history')
                    ->with('error', 'Data pembayaran tidak valid');
            }

            // Get transfer data - use parameterized query
            // Note: transfer table uses 'transfer_id' as primary key, not 'id'
            $transfer = DB::table('transfer')
                ->where('transfer_id', '=', (int) $transferId)
                ->where('reference', '=', $reference)
                ->first();

            Log::info('🔍 Transfer query result', [
                'found' => $transfer ? 'YES' : 'NO',
                'transfer_id' => $transferId
            ]);

            if (!$transfer) {
                return redirect()->route('student.payment.history')
                    ->with('error', 'Pembayaran tidak ditemukan');
            }

            // Parse payment details
            $paymentDetails = json_decode($transfer->payment_details, true);
            $ipaymuData = $paymentDetails['ipaymu_response'] ?? [];

            return view('student.va-instructions', [
                'transfer' => $transfer,
                'paymentNo' => $ipaymuData['payment_no'] ?? '-',
                'channel' => $ipaymuData['channel'] ?? 'BCA',
                'total' => $ipaymuData['total'] ?? $transfer->confirm_pay,
                'fee' => $ipaymuData['fee'] ?? 0,
                'expired' => $ipaymuData['expired_time'] ?? null,
                'reference' => $reference
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ VA Instructions error', [
                'message' => $e->getMessage(),
                'transfer_id' => $request->get('transfer_id'),
                'reference' => $request->get('reference'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('student.payment.history')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 