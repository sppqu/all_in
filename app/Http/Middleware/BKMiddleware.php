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

        // Check if user has BK role OR is superadmin (superadmin can access all)
        if (!$user->is_bk && $user->role !== 'superadmin') {
            return redirect()->route('manage.admin.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke menu Bimbingan Konseling.');
        }

        return $next($request);
    }
}
