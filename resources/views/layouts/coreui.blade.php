<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Content Security Policy untuk Midtrans -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://*.snap-assets.*.cdn.gtflabs.io https://api.midtrans.com https://api.sandbox.midtrans.com https://app.midtrans.com https://app.sandbox.midtrans.com https://pay.google.com https://js-agent.newrelic.com https://bam.nr-data.net https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; connect-src 'self' http://* https://* http://localhost:* https://localhost:* http://127.0.0.1:* https://127.0.0.1:* https://*.ngrok.io https://*.ngrok-free.app https://*.tunnel.com https://*.loca.lt https://api.midtrans.com https://api.sandbox.midtrans.com https://app.midtrans.com https://app.sandbox.midtrans.com {{ config('app.url') }}; frame-src 'self' https://app.midtrans.com https://app.sandbox.midtrans.com https://pay.google.com;">
    <title>{{ config('app.name', 'Laravel + CoreUI') }}</title>
    


    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/logo.png') }}">
    
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/coreui.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .app-sidebar { 
            min-height: 100vh; 
            background: linear-gradient(135deg, #008060 0%, #006d52 100%) !important;
        }
        
        /* Sidebar Header Styling */
        .sidebar-header {
            background: white !important;
            border: 1px solid #e9ecef !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header h4, .sidebar-header h6 {
            font-weight: 700;
            color: #198754 !important;
        }
        .app-sidebar .nav-link.active { background: #198754; color: #fff; }
        .app-sidebar .nav-link.collapsed .ms-auto b { transform: rotate(0deg); transition: transform 0.3s; }
        .app-sidebar .nav-link:not(.collapsed) .ms-auto b { transform: rotate(90deg); transition: transform 0.3s; }
        
        /* CoreUI Navigation Styles */
        .app-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.125rem;
            transition: all 0.15s ease-in-out;
        }
        
        .app-sidebar .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .app-sidebar .nav-link.active {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Nav Group Toggle */
        .app-sidebar .nav-group-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .app-sidebar .nav-group-toggle-icon {
            transition: transform 0.3s ease;
        }
        
        .app-sidebar .nav-group-toggle[aria-expanded="true"] .nav-group-toggle-icon {
            transform: rotate(90deg);
        }
        
        /* Nav Group Items */
        .app-sidebar .nav-group-items {
            margin-left: 1rem;
            border-left: 1px solid rgba(255, 255, 255, 0.2);
            padding-left: 1rem;
            list-style: none !important;
        }
        
        .app-sidebar .nav-group-items .nav-link {
            padding: 0.375rem 1rem;
            font-size: 0.875rem;
            margin-left: 0.5rem;
            list-style: none !important;
        }
        
        .app-sidebar .nav-group-items .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.12);
        }
        
        .app-sidebar .nav-group-items .nav-link.active {
            background-color: #198754 !important;
            color: #fff !important;
            border: 2px solid #fff !important;
            box-shadow: 0 0 10px rgba(25, 135, 84, 0.5) !important;
        }
        
        /* Premium menu styling */
        .app-sidebar .nav-link.disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }
        
        .app-sidebar .nav-link.disabled:hover {
            background-color: transparent !important;
            color: rgba(255, 255, 255, 0.5) !important;
        }
        
        .app-sidebar .nav-link .fa-crown {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* SweetAlert custom styling */
        .swal-wide {
            width: 500px !important;
        }
        
        /* Hapus ikon lingkaran pada submenu */
        .app-sidebar .nav-group-items .nav-item {
            list-style: none !important;
        }
        
        .app-sidebar .nav-group-items .nav-item::before,
        .app-sidebar .nav-group-items .nav-item::after,
        .app-sidebar .nav-group-items .nav-link::before,
        .app-sidebar .nav-group-items .nav-link::after {
            display: none !important;
            content: none !important;
        }
        
        .app-sidebar .nav-group-items *::before,
        .app-sidebar .nav-group-items *::after {
            display: none !important;
            content: none !important;
        }
        
        /* Nav Title */
        .nav-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            margin: 1rem 0 0.5rem 0.5rem;
            letter-spacing: 0.05em;
        }
        .nav-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #adb5bd;
            margin: 1rem 0 0.5rem 0.5rem;
            letter-spacing: 0.05em;
        }
        .app-sidebar .nav-link.active, .app-sidebar .nav-link:focus, .app-sidebar .nav-link:hover {
            background: #343a40;
            color: #fff;
        }
        .app-sidebar .nav-link.active {
            background: #198754 !important;
            color: #fff !important;
            font-weight: 600;
        }
        
        /* Override CoreUI default styles untuk menu aktif */
        .app-sidebar .nav-link.active,
        .app-sidebar .nav-group-items .nav-link.active,
        .app-sidebar .nav-item .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
            font-weight: 600 !important;
        }
        
        /* Force override untuk semua variasi menu aktif */
        .app-sidebar * .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
            font-weight: 600 !important;
        }
        .app-sidebar .collapse.show {
            background: rgba(255,255,255,0.08);
            border-radius: 8px;
            margin: 4px 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        body.dark-mode {
            background: #18191a !important;
            color: #e4e6eb !important;
        }
        body.dark-mode .bg-white { background: #23272b !important; color: #e4e6eb !important; }
        body.dark-mode .bg-light { background: #2b2f33 !important; color: #e4e6eb !important; }
        body.dark-mode .card-header { color: #e4e6eb !important; border-bottom-color: #3a3f44 !important; }
        body.dark-mode .text-dark { color: #e4e6eb !important; }
        body.dark-mode .text-secondary { color: #b0b3b8 !important; }
        body.dark-mode .text-muted { color: #a7acb1 !important; }
        body.dark-mode a.text-dark { color: #9ecbff !important; }
        /* Theme dropdown contrast fix for dark mode */
        body.dark-mode .dropdown-menu { background: #2b2f33 !important; color: #e4e6eb !important; border-color: #3a3f44 !important; }
        body.dark-mode .dropdown-item { color: #e4e6eb !important; }
        body.dark-mode .dropdown-item:hover, body.dark-mode .dropdown-item.active { background: #3a3f44 !important; color: #fff !important; }
        body.dark-mode .dropdown-divider { border-top-color: #3a3f44 !important; }
        body.dark-mode .dropdown-toggle { color: #e4e6eb !important; }
        body.dark-mode .border-bottom { border-bottom: 1px solid #444 !important; }
        body.dark-mode .app-sidebar { background: #23272b !important; }
        body.dark-mode .nav-link, body.dark-mode .nav-title { color: #e4e6eb !important; }
        body.dark-mode .card { background: #23272b !important; color: #e4e6eb !important; }
        body.dark-mode .table { color: #e4e6eb !important; }
        body.dark-mode .table-light { background: #23272b !important; }
        body.dark-mode .form-control, body.dark-mode .form-select { background: #23272b !important; color: #e4e6eb !important; border-color: #444; }
        body.dark-mode .btn-primary { background: #375a7f; border-color: #375a7f; }
        body.dark-mode .btn-secondary { background: #444; border-color: #444; }
        body.dark-mode .alert-success { background: #223322; color: #b6fcb6; }
        body.dark-mode .alert-danger { background: #331a1a; color: #fcb6b6; }
        .wrapper {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .header {
            flex-shrink: 0;
        }
        .body {
            flex-grow: 1;
            overflow-y: auto;
        }
        .footer {
            flex-shrink: 0;
            background: #f0f2f5;
            border-top: 1px solid #dee2e6;
        }
        body.dark-mode .footer {
            background: #23272b;
            border-top: 1px solid #444;
        }
        .toast-body { color: #fff !important; }
    </style>
    @stack('styles')
    @yield('head')
</head>
<body>
    <div class="d-flex flex-column" style="min-height:100vh;">
        <div class="d-flex flex-row flex-grow-1">
        <!-- Sidebar -->
        <div class="app-sidebar bg-dark text-white p-3" style="min-width:220px;">
            

            <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
            
            <!-- Custom Confirmation Modal -->
            <div id="customConfirmModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-0 pb-0">
                            <div class="modal-title d-flex align-items-center">
                                <div id="confirmIcon" class="me-3"></div>
                                <div>
                                    <h5 id="confirmTitle" class="mb-0 fw-bold"></h5>
                                    <p id="confirmSubtitle" class="mb-0 text-muted small"></p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <div id="confirmMessage" class="mb-3"></div>
                            <div id="confirmDetails" class="text-start small text-muted"></div>
                        </div>
                        <div class="modal-footer border-0 justify-content-center gap-2">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                                <i class="fa fa-times me-2"></i>Batal
                            </button>
                            <button type="button" class="btn btn-primary px-4" id="confirmActionBtn">
                                <i class="fa fa-check me-2"></i>Konfirmasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Toast Styles -->
            <style>
            #toast-container .toast {
                border-radius: 8px;
                border: none;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                margin-bottom: 10px;
            }
            
            #toast-container .toast-header {
                border-radius: 8px 8px 0 0;
                padding: 12px 16px;
                font-weight: 600;
            }
            
            #toast-container .toast-body {
                border-radius: 0 0 8px 8px;
                padding: 16px;
                line-height: 1.5;
                font-size: 14px;
            }
            
            #toast-container .toast-header .fa {
                font-size: 18px;
            }
            
            #toast-container .toast-header strong {
                font-size: 16px;
                font-weight: 700;
            }
            
            #toast-container .toast-body div {
                font-size: 14px;
                font-weight: 500;
                color: #333;
            }
            
            #toast-container .btn-close {
                opacity: 0.8;
                transition: opacity 0.2s;
            }
            
            #toast-container .btn-close:hover {
                opacity: 1;
            }
            
            /* Dark mode support */
            [data-bs-theme="dark"] #toast-container .toast-body {
                background-color: #f8f9fa !important;
                color: #212529 !important;
            }
            
            [data-bs-theme="dark"] #toast-container .toast-body div {
                color: #212529 !important;
            }
            
            /* Custom Confirmation Modal Styles */
            #customConfirmModal .modal-content {
                border-radius: 16px;
                overflow: hidden;
            }
            
            #customConfirmModal .modal-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 1.5rem;
            }
            
            #customConfirmModal .modal-title h5 {
                color: white;
                font-size: 1.25rem;
                font-weight: 700;
            }
            
            #customConfirmModal .modal-title p {
                color: rgba(255,255,255,0.8);
                font-size: 0.875rem;
            }
            
            #customConfirmModal .btn-close {
                filter: invert(1);
                opacity: 0.8;
            }
            
            #customConfirmModal .btn-close:hover {
                opacity: 1;
            }
            
            #customConfirmModal .modal-body {
                padding: 2rem 1.5rem;
            }
            
            #customConfirmModal .modal-footer {
                padding: 1rem 1.5rem 1.5rem;
            }
            
            #customConfirmModal .btn {
                border-radius: 8px;
                font-weight: 600;
                padding: 0.75rem 1.5rem;
                transition: all 0.3s ease;
            }
            
            #customConfirmModal .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            
            #customConfirmModal .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
            }
            
            #customConfirmModal .btn-light {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                color: #6c757d;
            }
            
            #customConfirmModal .btn-light:hover {
                background: #e9ecef;
                border-color: #adb5bd;
            }
            
            #confirmIcon {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: white;
                background: rgba(255,255,255,0.2);
                backdrop-filter: blur(10px);
            }
            
            #confirmMessage {
                font-size: 1.1rem;
                color: #495057;
                line-height: 1.6;
            }
            
            #confirmDetails {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 1rem;
                border-left: 4px solid #667eea;
            }
            
            /* Dark mode support for confirmation modal */
            [data-bs-theme="dark"] #customConfirmModal .modal-body {
                background-color: #212529;
                color: #f8f9fa;
            }
            
            [data-bs-theme="dark"] #customConfirmModal #confirmMessage {
                color: #e9ecef;
            }
            
            [data-bs-theme="dark"] #customConfirmModal #confirmDetails {
                background-color: #343a40;
                color: #adb5bd;
                border-left-color: #667eea;
            }
            </style>
            
            <!-- Sidebar Header with Logo and App Name -->
            <div class="sidebar-header text-center p-3 mb-4 rounded-3" style="background: white !important; border: 1px solid #e9ecef !important;">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <img src="{{ asset('images/logo.png') }}" alt="SPPQU Logo" style="width: 20px; height: 20px; object-fit: contain;" class="me-2">
                    <h6 class="mb-0 fw-bold" style="color: #198754 !important;">SPPQU</h6>
                </div>
            </div>
            
            <!-- Navigation Menu -->
            <div class="nav-title">APLIKASI</div>
            <ul class="nav flex-column">
                @php
                    // Always show menus - no subscription check for now
                    $showAllMenus = true;
                @endphp
                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal') || auth()->user()->role == 'superadmin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/manage/dashboard') }}">
                        <i class="fa fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                @endif
                
                <!-- Data Master Menu - HIDE for BK Users & Admin Jurnal (UNLESS Superadmin) -->
                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && menuCan('menu.data_master'))
                <li class="nav-item">
                    <a class="nav-link nav-group-toggle" href="#dataMasterSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="dataMasterSubmenu">
                        <i class="fa fa-folder-open me-2"></i> Data Master
                        <i class="fa fa-angle-right nav-group-toggle-icon ms-auto"></i>
                    </a>
                    <ul class="nav-group-items collapse" id="dataMasterSubmenu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/tahun-pelajaran') }}">
                                <i class="fa fa-calendar me-2"></i> Tahun Pelajaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/kelas') }}">
                                <i class="fa fa-graduation-cap me-2"></i> Kelas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/peserta-didik') }}">
                                <i class="fa fa-users me-2"></i> Peserta Didik
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                
                <!-- Setting Tarif Menu -->
                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && menuCan('menu.setting_tarif'))
                <li class="nav-item">
                    <a class="nav-link nav-group-toggle" href="#settingTarifSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="settingTarifSubmenu">
                        <i class="fa fa-money-bill-wave me-2"></i> Setting Tarif
                        <i class="fa fa-angle-right nav-group-toggle-icon ms-auto"></i>
                    </a>
                    <ul class="nav-group-items collapse" id="settingTarifSubmenu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pos.index') }}">
                                <i class="fa fa-list me-2"></i> Nama Pos Pembayaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('payment.index') }}">
                                <i class="fa fa-credit-card me-2"></i> Setting Tarif Pembayaran
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                
                <!-- Pembayaran Menu -->
                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && function_exists('canAccessPremium') && canAccessPremium('menu.pembayaran'))
                <li class="nav-item">
                    <x-subscription-guard>
                        <a class="nav-link nav-group-toggle" href="#pembayaranSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="pembayaranSubmenu">
                            <i class="fa fa-money-bill me-2"></i> Pembayaran
                            <i class="fa fa-angle-right nav-group-toggle-icon ms-auto"></i>
                        </a>
                    </x-subscription-guard>
                    <ul class="nav-group-items collapse" id="pembayaranSubmenu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('payment.cash') }}">
                                <i class="fa fa-cash-register me-2"></i> Pembayaran Tunai
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('online-payment.index') }}">
                                <i class="fa fa-globe me-2"></i> Pembayaran Online
                            </a>
                        </li>
                    </ul>
                </li>
                @elseif(function_exists('menuCan') && menuCan('menu.pembayaran'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('manage.pembayaran.index') }}">
                        <i class="fa fa-money-bill me-2"></i> Pembayaran
                    </a>
                </li>
                @endif
                
                <!-- Tabungan Menu -->
                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && function_exists('menuCan') && menuCan('menu.tabungan'))
                <li class="nav-item">
                    <a class="nav-link nav-group-toggle" href="#tabunganSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="tabunganSubmenu">
                        <i class="fa fa-bank me-2"></i> Tabungan
                        <i class="fa fa-angle-right nav-group-toggle-icon ms-auto"></i>
                    </a>
                    <ul class="nav-group-items collapse" id="tabunganSubmenu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.tabungan.index') }}">
                                <i class="fa fa-piggy-bank me-2"></i> Kelola Tabungan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.rekapitulasi-tabungan.index') }}">
                                <i class="fa fa-chart-pie me-2"></i> Rekapitulasi Tabungan
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                

                
                <!-- Akuntansi Menu -->
                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && function_exists('canAccessPremium') && canAccessPremium('menu.akuntansi'))
                <li class="nav-item">
                    <a class="nav-link nav-group-toggle" href="#akuntansiSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="akuntansiSubmenu">
                        <i class="fa fa-calculator me-2"></i> Keuangan
                        <i class="fa fa-angle-right nav-group-toggle-icon ms-auto"></i>
                    </a>
                    <ul class="nav-group-items collapse" id="akuntansiSubmenu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.accounting.kas.index') }}">
                                <i class="fa fa-wallet me-2"></i> Daftar Kas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.accounting.payment-methods.index') }}">
                                <i class="fa fa-credit-card me-2"></i> Metode Pembayaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.accounting.receipt-pos.index') }}">
                                <i class="fa fa-plus me-2"></i> Pos Penerimaan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.accounting.expense-pos.index') }}">
                                <i class="fa fa-minus me-2"></i> Pos Pengeluaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.accounting.cash-transfer.index') }}">
                                <i class="fa fa-exchange-alt me-2"></i> Pindah Buku Kas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.accounting.cashflow.index') }}">
                                <i class="fa fa-chart-line me-2"></i> Arus Kas
                            </a>
                        </li>
                    </ul>
                </li>
                @elseif(function_exists('menuCan') && menuCan('menu.akuntansi'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('manage.akuntansi.index') }}">
                        <i class="fa fa-calculator me-2"></i> Keuangan
                    </a>
                </li>
                @endif
                
                <!-- Laporan Menu -->
                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && function_exists('canAccessPremium') && canAccessPremium('menu.laporan'))
                <li class="nav-item">
                    <a class="nav-link nav-group-toggle" href="#laporanSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="laporanSubmenu">
                        <i class="fa fa-file-alt me-2"></i> Laporan Pembayaran
                        <i class="fa fa-angle-right nav-group-toggle-icon ms-auto"></i>
                    </a>
                    <ul class="nav-group-items collapse" id="laporanSubmenu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.laporan-perpos') }}">
                                <i class="fa fa-chart-bar me-2"></i> Laporan PerPos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.laporan-perkelas') }}">
                                <i class="fa fa-chart-pie me-2"></i> Laporan PerKelas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.laporan-rekapitulasi') }}">
                                <i class="fa fa-chart-line me-2"></i> Laporan Rekapitulasi
                            </a>
                        </li>
                        @php
                            $hasAnalisisTarget = \App\Models\UserAddon::where('user_id', auth()->id())
                                ->whereHas('addon', function($query) {
                                    $query->where('slug', 'analisis-target');
                                })
                                ->where('status', 'active')
                                ->exists();
                        @endphp
                        <li class="nav-item">
                            @if($hasAnalisisTarget)
                                <a class="nav-link" href="{{ route('manage.laporan.realisasi-pos') }}">
                                    <i class="fa fa-target me-2"></i> Analisis Target Capaian
                                </a>
                            @else
                                <a class="nav-link" href="{{ route('manage.analisis.index') }}">
                                    <i class="fa fa-target me-2"></i> Analisis Target Capaian
                                </a>
                            @endif
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manage.laporan.tunggakan-siswa') }}">
                                <i class="fa fa-exclamation-triangle me-2"></i> Tunggakan Siswa
                            </a>
                        </li>
                    </ul>
                </li>
                @elseif(menuCan('menu.laporan'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('manage.laporan.index') }}">
                        <i class="fa fa-file-alt me-2"></i> Laporan Pembayaran
                    </a>
                </li>
                @endif
                @endif
                
            </ul>
            
            <div class="nav-title">ADD-ONS</div>
            <ul class="nav flex-column">
                @if(!session('subscription_expired'))
                <!-- SPMB Menu - HIDE for BK Users & Admin Jurnal (UNLESS Superadmin) -->
                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && hasSPMBAddon())
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('manage.spmb.index') }}">
                        <i class="fa fa-user-graduate me-2"></i> SPMB
                    </a>
                </li>
                @endif
                
                <!-- BK Menu - Visible for BK admins and Superadmin -->
                @if(hasBKAddon() && (auth()->user()->is_bk || auth()->user()->role == 'superadmin'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('manage/bk*') ? 'active' : '' }}" href="{{ route('manage.bk.dashboard') }}">
                        <i class="fa fa-clipboard-list me-2"></i> Bimbingan Konseling
                    </a>
                </li>
                @endif
                
                <!-- E-Jurnal Menu - Visible for Admin Jurnal and Superadmin -->
                @if(hasEJurnalAddon() && (auth()->user()->role == 'admin_jurnal' || auth()->user()->role == 'superadmin'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('jurnal/*') ? 'active' : '' }}" href="{{ route('jurnal.guru.index') }}">
                        <i class="fa fa-book me-2" style="color: #ffffff;"></i> E-Jurnal
                    </a>
                </li>
                @endif
                
                <!-- E-Perpustakaan Menu - Visible for users with Library addon -->
                @if(hasLibraryAddon())
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('library/*') || request()->is('manage/library/*') ? 'active' : '' }}" href="{{ route('library.index') }}">
                        <i class="fa fa-book-reader me-2" style="color: #ffffff;"></i> E-Perpustakaan
                    </a>
                </li>
                @endif
            </ul>
            
            @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal') || auth()->user()->role == 'superadmin')
            <div class="nav-title">LAINNYA</div>
            <ul class="nav flex-column">
                @if(menuCan('menu.billing') && in_array(auth()->user()->role, ['admin', 'superadmin']))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('manage.subscription.index') }}" id="billingMenu">
                            <i class="fa fa-file-invoice-dollar me-2"></i> Berlangganan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('manage.addons.index') }}">
                            <i class="fa fa-puzzle-piece me-2"></i> Add-ons Premium
                        </a>
                    </li>
                @endif
                @if(function_exists('canAccessPremium') && canAccessPremium('menu.kirim_tagihan'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('manage.bulk-whatsapp.index') }}">
                            <i class="fa fa-paper-plane me-2"></i> Kirim Tagihan
                        </a>
                    </li>
                @elseif(menuCan('menu.kirim_tagihan'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('manage.bulk-whatsapp.index') }}">
                            <i class="fa fa-paper-plane me-2"></i> Kirim Tagihan
                        </a>
                    </li>
                @endif
                @if(function_exists('canAccessPremium') && canAccessPremium('menu.users'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('manage.users.index') }}">
                            <i class="fa fa-user-cog me-2"></i> Pengguna
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('manage.users.role-menu') }}">
                            <i class="fa fa-shield-alt me-2"></i> Hak Akses Menu
                        </a>
                    </li>
                @elseif(menuCan('menu.users'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('manage.users.index') }}">
                            <i class="fa fa-user-cog me-2"></i> Pengguna
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('manage.permissions.index') }}">
                            <i class="fa fa-shield-alt me-2"></i> Hak Akses Menu
                        </a>
                    </li>
                @endif
                @if(function_exists('canAccessPremium') && canAccessPremium('menu.general_setting'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('manage.general.setting') }}">
                            <i class="fa fa-cogs me-2"></i> General Setting
                        </a>
                    </li>
                @elseif(menuCan('menu.general_setting'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('manage.general-setting.index') }}">
                            <i class="fa fa-cogs me-2"></i> General Setting
                        </a>
                    </li>
                @endif
            </ul>
            @endif
            
            <hr class="my-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <button type="button" class="nav-link bg-transparent border-0 w-100 text-start" onclick="confirmLogout()">
                        <i class="fa fa-sign-out-alt me-2"></i> Logout
                    </button>
                </li>
            </ul>
        </div>
        <div class="wrapper">
            <!-- Topbar/Header -->
            <div class="header bg-white border-bottom d-flex align-items-center px-3" style="height:56px;">
                <button class="btn btn-link text-dark fs-4 me-3" id="sidebarToggle"><i class="fa fa-bars"></i></button>
                <!-- <nav class="d-flex align-items-center flex-grow-1">
                    <a href="#" class="me-4 text-dark text-decoration-none">Dashboard</a>
                    <a href="#" class="me-4 text-dark text-decoration-none">Users</a>
                    <a href="#" class="me-4 text-dark text-decoration-none">Settings</a>
                </nav> -->
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle" id="themeDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="color:inherit;">
                            <i class="fa fa-sun"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeDropdown" style="min-width: 140px;">
                            <li><a class="dropdown-item d-flex align-items-center" href="#" data-theme="light"><i class="fa fa-sun me-2"></i> Light</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="#" data-theme="dark"><i class="fa fa-moon me-2"></i> Dark</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="#" data-theme="auto"><i class="fa fa-adjust me-2"></i> Auto</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration:none; color:inherit;">
                            @php $avatar = auth()->check() ? (auth()->user()->avatar_path ?? null) : null; @endphp
                            <img src="{{ $avatar ? asset('storage/'.$avatar) : asset('images/logo.png') }}" alt="User" class="rounded-circle" style="width:32px; height:32px; object-fit:cover;">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 180px;">
                            @auth
                                <li><a class="dropdown-item" href="{{ route('manage.profile.edit') }}"><i class="fa fa-user me-2"></i> Edit Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('manage.logout') }}">
                                        @csrf
                                        <button class="dropdown-item" type="submit"><i class="fa fa-sign-out-alt me-2"></i> Logout</button>
                                    </form>
                                </li>
                            @else
                                <li><span class="dropdown-item text-muted">Tidak ada user</span></li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>
            <div class="body p-4">
                <!-- Subscription Warning Alert -->
                @if(session('subscription_warning'))
                    @php $warning = session('subscription_warning'); @endphp
                    <div class="alert alert-{{ $warning['type'] === 'critical' ? 'danger' : ($warning['type'] === 'warning' ? 'warning' : ($warning['type'] === 'error' ? 'danger' : 'info')) }} alert-dismissible fade show mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas {{ $warning['type'] === 'critical' ? 'fa-exclamation-triangle' : ($warning['type'] === 'warning' ? 'fa-exclamation-circle' : ($warning['type'] === 'error' ? 'fa-times-circle' : 'fa-info-circle')) }} me-2"></i>
                            <div class="flex-grow-1">
                                {{ $warning['message'] }}
                            </div>
                            <div class="ms-3">
                                <a href="{{ route('manage.subscription.plans') }}" class="btn btn-sm btn-{{ $warning['type'] === 'critical' ? 'danger' : ($warning['type'] === 'warning' ? 'warning' : ($warning['type'] === 'error' ? 'danger' : 'primary')) }}">
                                    {{ $warning['type'] === 'critical' || $warning['type'] === 'warning' || $warning['type'] === 'error' ? 'Perpanjang Sekarang' : 'Lihat Paket' }}
                                </a>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Subscription Modal -->
    @include('components.subscription-modal')
    
    <!-- Footer -->
    <footer class="footer p-2 d-flex justify-content-between align-items-center">
        <span>&copy; SPPQU</span>
        <span>Versi APP v{{ config('app.version') }}</span>
    </footer>
</div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- CoreUI JS -->
    <script src="{{ asset('assets/vendor/js/coreui.bundle.min.js') }}"></script>
    
    <script>
        // Sidebar toggle (opsional, jika ingin sidebar collapse)
        document.getElementById('sidebarToggle').onclick = function() {
            document.querySelector('.app-sidebar').classList.toggle('d-none');
        };

        // CoreUI Navigation Initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all collapse elements
            var collapseElements = document.querySelectorAll('.nav-group-items');
            collapseElements.forEach(function(collapse) {
                new bootstrap.Collapse(collapse, {
                    toggle: false
                });
            });

            // Add click event listeners for nav-group-toggle
            var navGroupToggles = document.querySelectorAll('.nav-group-toggle');
            navGroupToggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get the target collapse element
                    var targetId = this.getAttribute('href');
                    var targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        // Toggle the collapse
                        var bsCollapse = bootstrap.Collapse.getInstance(targetElement);
                        if (bsCollapse) {
                            bsCollapse.toggle();
                        } else {
                            new bootstrap.Collapse(targetElement, {
                                toggle: true
                            });
                        }
                        
                        // Update aria-expanded attribute
                        var isExpanded = targetElement.classList.contains('show');
                        this.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                    }
                });
            });

            // Handle all nav-link clicks for active state
            var allNavLinks = document.querySelectorAll('.nav-link');
            allNavLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    // Remove active class from all nav links
                    allNavLinks.forEach(function(l) {
                        l.classList.remove('active');
                    });
                    
                    // Add active class to clicked link
                    this.classList.add('active');
                });
            });
        });

        // Theme mode dropdown logic
        function applyTheme(theme) {
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
            } else if (theme === 'light') {
                document.body.classList.remove('dark-mode');
            } else if (theme === 'auto') {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.body.classList.add('dark-mode');
                } else {
                    document.body.classList.remove('dark-mode');
                }
            }
        }
        function setTheme(theme) {
            localStorage.setItem('theme-mode', theme);
            applyTheme(theme);
        }
        // On load, apply saved or auto theme
        (function() {
            let theme = localStorage.getItem('theme-mode') || 'auto';
            applyTheme(theme);
            // Highlight selected
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.dropdown-item[data-theme]').forEach(function(item) {
                    item.classList.remove('active');
                    if (item.getAttribute('data-theme') === theme) {
                        item.classList.add('active');
                    }
                    item.onclick = function(e) {
                        e.preventDefault();
                        setTheme(this.getAttribute('data-theme'));
                        document.querySelectorAll('.dropdown-item[data-theme]').forEach(function(i) { i.classList.remove('active'); });
                        this.classList.add('active');
                    };
                });
            });
            // Listen to system theme changes if auto
            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                    if ((localStorage.getItem('theme-mode') || 'auto') === 'auto') {
                        applyTheme('auto');
                    }
                });
            }
        })();

        // Logout confirmation function
        function confirmLogout() {
            console.log('Logout confirmation requested');
            
            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-danger text-white border-0">
                                <h5 class="modal-title" id="logoutModalLabel">
                                    <i class="fa fa-sign-out-alt me-2"></i>Konfirmasi Logout
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <div class="mb-3">
                                    <i class="fa fa-question-circle text-warning" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="mb-3">Apakah Anda yakin ingin keluar dari aplikasi?</h5>
                                <p class="text-muted mb-0">Anda akan keluar dari sistem dan harus login kembali untuk mengakses aplikasi.</p>
                            </div>
                            <div class="modal-footer border-0 justify-content-center">
                                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                                    <i class="fa fa-times me-2"></i>Batal
                                </button>
                                <button type="button" class="btn btn-danger px-4" onclick="proceedLogout()">
                                    <i class="fa fa-sign-out-alt me-2"></i>Ya, Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('logoutModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
            modal.show();
        }
        
        function proceedLogout() {
            console.log('Proceeding with logout...');
            
            // Create form dynamically and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("manage.logout") }}';
            
            console.log('Form method:', form.method);
            console.log('Form action:', form.action);
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            
            console.log('Form created and ready to submit');
            console.log('CSRF Token:', csrfToken.value);
            
            form.submit();
        }

        function copyKodeAktifasi() {
            var kode = document.getElementById('kodeAktifasi').value;
            navigator.clipboard.writeText(kode).then(function() {
                var toast = document.getElementById('toast-copy');
                toast.style.display = 'block';
                setTimeout(function() { toast.style.display = 'none'; }, 2000);
            });
        }

        // Subscription notification system (disabled)
        function checkSubscriptionNotifications() { /* notifications disabled */ }

        function showNotification(notification) {
            // Create notification element
            const notificationDiv = document.createElement('div');
            notificationDiv.className = `alert alert-${getAlertType(notification.type)} alert-dismissible fade show position-fixed`;
            notificationDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
            
            notificationDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas ${getIcon(notification.type)} me-2"></i>
                    <div class="flex-grow-1">
                        <strong>${notification.title}</strong><br>
                        <small>${notification.message}</small>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="${notification.action_url}" class="btn btn-sm btn-${getButtonType(notification.type)}">
                        ${notification.action}
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="this.parentElement.parentElement.remove()">
                        Tutup
                    </button>
                </div>
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(notificationDiv);
            
            // Auto remove after 10 seconds for non-critical notifications
            if (notification.type !== 'critical') {
                setTimeout(() => {
                    if (notificationDiv.parentElement) {
                        notificationDiv.remove();
                    }
                }, 10000);
            }
        }

        function getAlertType(type) {
            switch (type) {
                case 'critical': return 'danger';
                case 'warning': return 'warning';
                case 'error': return 'danger';
                case 'info': return 'info';
                default: return 'info';
            }
        }

        function getIcon(type) {
            switch (type) {
                case 'critical': return 'fa-exclamation-triangle';
                case 'warning': return 'fa-exclamation-circle';
                case 'error': return 'fa-times-circle';
                case 'info': return 'fa-info-circle';
                default: return 'fa-info-circle';
            }
        }

        function getButtonType(type) {
            switch (type) {
                case 'critical': return 'danger';
                case 'warning': return 'warning';
                case 'error': return 'danger';
                case 'info': return 'primary';
                default: return 'primary';
            }
        }

        // Function to show subscription required popup
        function showSubscriptionRequired() {
            Swal.fire({
                title: '<i class="fas fa-crown text-warning me-2"></i>Fitur Premium',
                html: `
                    <div class="text-center">
                        <i class="fas fa-lock text-muted" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h5 class="mb-3">Fitur ini memerlukan berlangganan aktif</h5>
                        <p class="text-muted mb-4">
                            Untuk mengakses fitur premium ini, Anda perlu memiliki berlangganan yang aktif.
                            Berlangganan sekarang untuk menikmati semua fitur premium!
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-outline-secondary" onclick="Swal.close()">
                                <i class="fas fa-times me-2"></i>Nanti
                            </button>
                            <a href="{{ route('manage.subscription.plans') }}" class="btn btn-warning">
                                <i class="fas fa-crown me-2"></i>Berlangganan Sekarang
                            </a>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                customClass: {
                    popup: 'swal-wide',
                    title: 'text-warning'
                }
            });
        }

        // Function to show add-on required popup
        function showAddonRequired(addonSlug) {
            Swal.fire({
                title: '<i class="fas fa-puzzle-piece text-primary me-2"></i>Add-on Required',
                html: `
                    <div class="text-center">
                        <i class="fas fa-lock text-muted" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h5 class="mb-3">Fitur ini memerlukan add-on khusus</h5>
                        <p class="text-muted mb-4">
                            Untuk mengakses fitur ini, Anda perlu membeli add-on terlebih dahulu.
                            Add-on memberikan fitur tambahan yang dapat dipilih sesuai kebutuhan.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-outline-secondary" onclick="Swal.close()">
                                <i class="fas fa-times me-2"></i>Nanti
                            </button>
                            <a href="/manage/addons/${addonSlug}" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-2"></i>Beli Add-on
                            </a>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                customClass: {
                    popup: 'swal-wide',
                    title: 'text-primary'
                }
            });
        }

        // Notifications disabled: no automatic checks

        // Active menu highlighting for CoreUI
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            
            // Define route handlers first
            const routeHandlers = [
                {
                    path: '/account-codes',
                    menuId: 'akuntansiSubmenu',
                    linkSelector: 'a[href*="account-codes"]'
                },

                {
                    path: '/kas',
                    menuId: 'akuntansiSubmenu',
                    linkSelector: 'a[href*="kas"]'
                },

                {
                    path: '/tahun-pelajaran',
                    menuId: 'dataMasterSubmenu',
                    linkSelector: 'a[href*="tahun-pelajaran"]'
                },
                {
                    path: '/kelas',
                    menuId: 'dataMasterSubmenu',
                    linkSelector: 'a[href*="kelas"]'
                },
                {
                    path: '/peserta-didik',
                    menuId: 'dataMasterSubmenu',
                    linkSelector: 'a[href*="peserta-didik"]'
                },
                {
                    path: '/pos',
                    menuId: 'settingTarifSubmenu',
                    linkSelector: 'a[href*="pos"]'
                },
                {
                    path: '/payment',
                    menuId: 'settingTarifSubmenu',
                    linkSelector: 'a[href*="payment"]',
                    excludePaths: ['/payment/cash', '/payment/pembayaran-tunai', '/online-payment', '/manage/accounting/payment-methods', '/subscription/payment']
                },
                {
                    path: '/manage/accounting/payment-methods',
                    menuId: 'akuntansiSubmenu',
                    linkSelector: 'a[href*="manage.accounting.payment-methods"]'
                },
                {
                    path: '/manage/accounting/kas',
                    menuId: 'akuntansiSubmenu',
                    linkSelector: 'a[href*="manage.accounting.kas"]'
                },
                {
                    path: '/manage/accounting/receipt-pos',
                    menuId: 'akuntansiSubmenu',
                    linkSelector: 'a[href*="manage.accounting.receipt-pos"]'
                },
                {
                    path: '/manage/accounting/expense-pos',
                    menuId: 'akuntansiSubmenu',
                    linkSelector: 'a[href*="manage.accounting.expense-pos"]'
                },
                {
                    path: '/manage/accounting/cash-transfer',
                    menuId: 'akuntansiSubmenu',
                    linkSelector: 'a[href*="manage.accounting.cash-transfer"]'
                },
                {
                    path: '/manage/accounting/cashflow',
                    menuId: 'akuntansiSubmenu',
                    linkSelector: 'a[href*="manage.accounting.cashflow"]'
                },
                {
                    path: '/payment/pembayaran-tunai',
                    menuId: 'pembayaranSubmenu',
                    linkSelector: 'a[href*="payment/cash"]',
                    exactMatch: true
                },
                {
                    path: '/online-payment',
                    menuId: 'pembayaranSubmenu',
                    linkSelector: 'a[href*="online-payment"]'
                },
                {
                    path: '/laporan-perpos',
                    menuId: 'laporanSubmenu',
                    linkSelector: 'a[href*="laporan-perpos"]'
                },
                {
                    path: '/laporan-perkelas',
                    menuId: 'laporanSubmenu',
                    linkSelector: 'a[href*="laporan-perkelas"]'
                },
                {
                    path: '/laporan-rekapitulasi',
                    menuId: 'laporanSubmenu',
                    linkSelector: 'a[href*="laporan-rekapitulasi"]'
                },
                {
                    path: '/manage/tabungan',
                    menuId: 'tabunganSubmenu',
                    linkSelector: 'a[href*="manage.tabungan"]'
                },
                {
                    path: '/subscription',
                    menuId: 'billingMenu',
                    linkSelector: 'a[href*="manage.subscription"]'
                },

            ];
            
            // Highlight active menu based on current path (but skip if handled by specific route handlers)
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentPath.startsWith(href) && href !== '/') {
                    // Skip if this link will be handled by specific route handlers
                    const isHandledBySpecificHandler = routeHandlers.some(handler => {
                        const shouldActivate = currentPath.includes(handler.path) && 
                            (!handler.excludePaths || !handler.excludePaths.some(excludePath => currentPath.includes(excludePath)));
                        return shouldActivate && currentPath.includes(handler.path);
                    });
                    
                    if (!isHandledBySpecificHandler) {
                        link.classList.add('active');
                        
                        // If it's a submenu item, expand the parent menu
                        const parentMenu = link.closest('.nav-group-items');
                        if (parentMenu) {
                            parentMenu.classList.add('show');
                            const parentToggle = document.querySelector(`[href="#${parentMenu.id}"]`);
                            if (parentToggle) {
                                parentToggle.setAttribute('aria-expanded', 'true');
                            }
                        }
                    }
                }
            });
            
            // Special handling for specific routes
            
            routeHandlers.forEach(handler => {
                // Check if current path matches and is not excluded
                let shouldActivate;
                
                if (handler.exactMatch) {
                    // For exact match, check if the path exactly matches
                    shouldActivate = currentPath === handler.path;
                } else {
                    // For partial match, check if current path includes the handler path
                    shouldActivate = currentPath.includes(handler.path) && 
                        (!handler.excludePaths || !handler.excludePaths.some(excludePath => currentPath.includes(excludePath)));
                }
                
                if (shouldActivate) {
                    const menu = document.getElementById(handler.menuId);
                    if (menu) {
                        menu.classList.add('show');
                        const menuToggle = document.querySelector(`[href="#${handler.menuId}"]`);
                        if (menuToggle) {
                            menuToggle.setAttribute('aria-expanded', 'true');
                        }
                    }
                    
                    // Highlight the specific link
                    const link = document.querySelector(handler.linkSelector);
                    if (link) {
                        link.classList.add('active');
                    }
                }
            });
        });
    </script>
    @yield('scripts')
    
    <!-- Toast Container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>
    
    <!-- Toast Notification Script -->
    <script>
                    // Function to show toast notification
                function showToast(message, type = 'success') {
                    const toastContainer = document.getElementById('toast-container');
                    
                    // Create toast element
                    const toast = document.createElement('div');
                    toast.className = 'toast show shadow-lg';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    toast.style.minWidth = '350px';
                    toast.style.maxWidth = '450px';
                    
                    // Set background color and text color based on type
                    let bgColor, textColor, icon, title;
                    
                    if (type === 'success') {
                        bgColor = 'bg-success';
                        textColor = 'text-white';
                        icon = 'fa-check-circle';
                        title = 'Berhasil!';
                    } else if (type === 'error') {
                        bgColor = 'bg-danger';
                        textColor = 'text-white';
                        icon = 'fa-exclamation-circle';
                        title = 'Error!';
                    } else if (type === 'warning') {
                        bgColor = 'bg-warning';
                        textColor = 'text-dark';
                        icon = 'fa-exclamation-triangle';
                        title = 'Peringatan!';
                    } else {
                        bgColor = 'bg-info';
                        textColor = 'text-white';
                        icon = 'fa-info-circle';
                        title = 'Info!';
                    }
                    
                    toast.innerHTML = `
                        <div class="toast-header ${bgColor} ${textColor} border-0">
                            <i class="fa ${icon} me-2 fs-5"></i>
                            <strong class="me-auto fs-6 fw-bold">${title}</strong>
                            <button type="button" class="btn-close ${textColor === 'text-white' ? 'btn-close-white' : ''}" onclick="this.closest('.toast').remove()"></button>
                        </div>
                        <div class="toast-body bg-white text-dark p-3">
                            <div class="fs-6 fw-medium">${message}</div>
                        </div>
                    `;
                    
                    // Add to container
                    toastContainer.appendChild(toast);
                    
                    // Auto remove after 5 seconds
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 5000);
                }
    
                    // Check for session messages and show toast
                document.addEventListener('DOMContentLoaded', function() {
                    @if(session('success'))
                        showToast('{{ session('success') }}', 'success');
                    @endif
                    
                    @if(session('error'))
                        showToast('{{ session('error') }}', 'error');
                    @endif
                    
                    @if(session('warning'))
                        showToast('{{ session('warning') }}', 'warning');
                    @endif
                    
                    @if(session('info'))
                        showToast('{{ session('info') }}', 'info');
                    @endif
                });
                
                // Custom Confirmation Modal Function
                function showCustomConfirm(options) {
                    return new Promise((resolve) => {
                        const modal = document.getElementById('customConfirmModal');
                        const icon = document.getElementById('confirmIcon');
                        const title = document.getElementById('confirmTitle');
                        const subtitle = document.getElementById('confirmSubtitle');
                        const message = document.getElementById('confirmMessage');
                        const details = document.getElementById('confirmDetails');
                        const actionBtn = document.getElementById('confirmActionBtn');
                        
                        // Pastikan modal bersih dari event listener sebelumnya
                        const newModal = modal.cloneNode(true);
                        modal.parentNode.replaceChild(newModal, modal);
                        
                        // Ambil referensi baru
                        const freshModal = document.getElementById('customConfirmModal');
                        const freshIcon = freshModal.querySelector('#confirmIcon');
                        const freshTitle = freshModal.querySelector('#confirmTitle');
                        const freshSubtitle = freshModal.querySelector('#confirmSubtitle');
                        const freshMessage = freshModal.querySelector('#confirmMessage');
                        const freshDetails = freshModal.querySelector('#confirmDetails');
                        const freshActionBtn = freshModal.querySelector('#confirmActionBtn');
                        
                        // Set icon
                        if (options.icon) {
                            freshIcon.innerHTML = options.icon;
                        } else {
                            freshIcon.innerHTML = '<i class="fa fa-question-circle"></i>';
                        }
                        
                        // Set title and subtitle
                        freshTitle.textContent = options.title || 'Konfirmasi';
                        freshSubtitle.textContent = options.subtitle || 'Silakan konfirmasi tindakan Anda';
                        
                        // Set message
                        freshMessage.innerHTML = options.message || 'Apakah Anda yakin ingin melanjutkan?';
                        
                        // Set details if provided
                        if (options.details) {
                            freshDetails.innerHTML = options.details;
                            freshDetails.style.display = 'block';
                        } else {
                            freshDetails.style.display = 'none';
                        }
                        
                        // Set button text and color
                        if (options.confirmText) {
                            freshActionBtn.innerHTML = options.confirmText;
                        } else {
                            freshActionBtn.innerHTML = '<i class="fa fa-check me-2"></i>Konfirmasi';
                        }
                        
                        if (options.confirmColor) {
                            freshActionBtn.className = `btn px-4 ${options.confirmColor}`;
                        } else {
                            freshActionBtn.className = 'btn btn-primary px-4';
                        }
                        
                        // Handle confirmation
                        const handleConfirm = () => {
                            // Tutup modal terlebih dahulu
                            const bsModal = bootstrap.Modal.getInstance(freshModal);
                            if (bsModal) {
                                bsModal.hide();
                            }
                            
                            // Resolve dengan true
                            resolve(true);
                        };
                        
                        const handleCancel = () => {
                            // Resolve dengan false
                            resolve(false);
                        };
                        
                        // Add event listeners
                        freshActionBtn.addEventListener('click', handleConfirm);
                        freshModal.addEventListener('hidden.bs.modal', handleCancel);
                        
                        // Pastikan tidak ada modal lain yang terbuka
                        const existingModals = document.querySelectorAll('.modal.show');
                        existingModals.forEach(existingModal => {
                            if (existingModal !== freshModal) {
                                const bsExistingModal = bootstrap.Modal.getInstance(existingModal);
                                if (bsExistingModal) {
                                    bsExistingModal.hide();
                                }
                            }
                        });
                        
                        // Show modal
                        const bsModal = new bootstrap.Modal(freshModal);
                        bsModal.show();
                    });
                }
                
                // Notification dropdown and polling removed as requested
    </script>
    
    @stack('scripts')
    
</body>
</html> 