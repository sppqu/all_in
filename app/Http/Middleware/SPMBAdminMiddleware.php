<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SPMBAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('manage.login');
        }

        $user = Auth::user();
        
        // Check if user has admin role, superadmin role, or SPMB admin role
        if (!in_array($user->role, ['admin', 'superadmin', 'spmb_admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman SPMB Admin.');
        }

        return $next($request);
    }
}
