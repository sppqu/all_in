<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PwaController extends Controller
{
    public function manifest()
    {
        $manifest = [
            'name' => 'SPPQU - Sistem Pembayaran SPP Sekolah',
            'short_name' => 'SPPQU',
            'description' => 'Aplikasi pembayaran SPP sekolah yang mudah dan aman',
            'start_url' => '/student/dashboard',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#28a745',
            'orientation' => 'portrait',
            'scope' => '/student/',
            'lang' => 'id',
            'icons' => [
                [
                    'src' => '/images/pwa/icon-72x72.png',
                    'sizes' => '72x72',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => '/images/pwa/icon-96x96.png',
                    'sizes' => '96x96',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => '/images/pwa/icon-128x128.png',
                    'sizes' => '128x128',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => '/images/pwa/icon-144x144.png',
                    'sizes' => '144x144',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => '/images/pwa/icon-152x152.png',
                    'sizes' => '152x152',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => '/images/pwa/icon-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => '/images/pwa/icon-384x384.png',
                    'sizes' => '384x384',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => '/images/pwa/icon-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ],
            'categories' => ['education', 'finance'],
            'screenshots' => [
                [
                    'src' => '/images/pwa/screenshot-1.png',
                    'sizes' => '1280x720',
                    'type' => 'image/png',
                    'form_factor' => 'wide'
                ],
                [
                    'src' => '/images/pwa/screenshot-2.png',
                    'sizes' => '750x1334',
                    'type' => 'image/png',
                    'form_factor' => 'narrow'
                ]
            ]
        ];

        return response()->json($manifest);
    }

    public function offline()
    {
        return view('pwa.offline');
    }

    // Static method untuk handle PWA routes tanpa route definition
    public static function handleManifest()
    {
        $controller = new self();
        return $controller->manifest();
    }

    public static function handleOffline()
    {
        $controller = new self();
        return $controller->offline();
    }
}
