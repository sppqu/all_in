<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle TokenMismatchException (Error 419 - Page Expired)
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Session expired',
                    'message' => 'Sesi Anda telah berakhir. Silakan refresh halaman dan coba lagi.'
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('error', 'Sesi Anda telah berakhir. Silakan coba lagi.');
        });

        // Handle RouteNotFoundException
        $this->renderable(function (RouteNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Route tidak ditemukan',
                    'message' => 'Halaman yang Anda cari tidak ditemukan.'
                ], 404);
            }

            // Redirect ke halaman yang sesuai berdasarkan context
            if (str_contains($request->path(), 'student')) {
                return redirect()->route('student.login')
                    ->with('error', 'Halaman tidak ditemukan. Silakan login kembali.');
            } elseif (str_contains($request->path(), 'manage')) {
                return redirect()->route('manage.login')
                    ->with('error', 'Halaman tidak ditemukan. Silakan login kembali.');
            } else {
                return redirect()->route('student.login')
                    ->with('error', 'Halaman tidak ditemukan. Silakan login kembali.');
            }
        });
    }
}
