<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BKMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $user = auth()->user();

        // Check if user can access BK menu:
        // 1. User dengan role BK (is_bk = true)
        // 2. Superadmin (selalu bisa akses semua, tidak perlu addon)
        // 3. Admin utama (role = 'admin') dengan addon BK aktif
        $hasBKAddon = hasBKAddon();
        $canAccess = $user->is_bk || 
                     $user->role === 'superadmin' || 
                     ($user->role === 'admin' && $hasBKAddon);

        if (!$canAccess) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke menu Bimbingan Konseling.');
        }

        return $next($request);
    }
}
