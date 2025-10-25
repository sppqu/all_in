<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableCsrfMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Disable CSRF by removing session instead of setting to null
        // This is safer than setting session to null
        return $next($request);
    }
} 