<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user adalah student yang sudah login
        if (!session('is_student')) {
            // Clear session yang mungkin masih ada
            session()->forget([
                'student_id',
                'student_nis', 
                'student_name',
                'student_class',
                'is_student'
            ]);
            
            // Redirect ke halaman login student
            return redirect()->route('student.login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }

        // Cek session timeout (2 jam)
        $lastActivity = session('last_activity');
        $timeout = 2 * 60 * 60; // 2 jam dalam detik
        
        if ($lastActivity && (time() - $lastActivity) > $timeout) {
            // Session expired, logout user
            session()->forget([
                'student_id',
                'student_nis', 
                'student_name',
                'student_class',
                'is_student',
                'last_activity'
            ]);
            
            return redirect()->route('student.login')
                ->with('error', 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.');
        }

        // Update last activity
        session(['last_activity' => time()]);

        return $next($request);
    }
} 