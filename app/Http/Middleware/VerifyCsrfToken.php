<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Payment callbacks and webhooks - Use wildcards
        '*callback*',
        '*webhook*',
        'manage/tripay/*',
        'api/tripay/*',
        'subscription/*',
        'addons/*',
        
        // Student logout (fallback route)
        'student/logout'
    ];
} 