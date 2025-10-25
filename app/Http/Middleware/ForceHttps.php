<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force HTTPS for production
        if (app()->environment('production') && !$request->isSecure()) {
            return redirect()->secure($request->getRequestUri());
        }

        // Force HTTPS if APP_URL is https
        $appUrl = config('app.url');
        if (is_string($appUrl) && str_starts_with($appUrl, 'https://') && !$request->isSecure()) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
