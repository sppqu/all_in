<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek session timeout untuk admin (jika ada session admin)
        if (auth()->check()) {
            $lastActivity = session('admin_last_activity');
            $timeout = 2 * 60 * 60; // 2 jam dalam detik
            
            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                auth()->logout();
                session()->forget(['admin_last_activity']);
                
                return redirect()->route('manage.login')
                    ->with('error', 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.');
            }
            
            session(['admin_last_activity' => time()]);
        }

        return $next($request);
    }
}
