<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Student Panel') - SPPQU</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Smart Cache Control - Balance Performance & Freshness -->
    <meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#28a745">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SPPQU">
    <meta name="msapplication-TileColor" content="#28a745">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="application-name" content="SPPQU">
    <meta name="apple-touch-fullscreen" content="yes">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/logo.png') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Asset Versioning for Smart Cache Busting -->
    <style>
        :root {
            --app-version: "v{{ config('app.version', '1.0.0') }}";
            --build-time: "{{ now()->format('YmdHi') }}";
        }
    </style>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            padding-bottom: 80px; /* Space for bottom nav */
        }
        
        .navbar-brand {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
        }
        
        /* PWA Install Prompt Styling */
        .swal-wide {
            width: 90% !important;
            max-width: 500px !important;
        }
        
        .swal-wide .swal2-popup {
            border-radius: 20px !important;
        }
        
        /* PWA Install Button */
        .pwa-install-btn {
            position: fixed;
            bottom: 100px;
            right: 20px;
            z-index: 1000;
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
            display: none;
        }
        
        .pwa-install-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            color: white;
        }
        
        .pwa-install-btn.show {
            display: block;
        }
        
        /* Bottom Navigation - Modern Design with Green Theme */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            border-top: none;
            box-shadow: 0 -6px 20px rgba(0, 128, 96, 0.2);
            z-index: 1030;
            border-radius: 20px 20px 0 0;
            padding: 12px 8px 20px 8px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .bottom-nav .row {
            margin: 0;
        }
        
        .bottom-nav .col {
            padding: 0 5px;
        }
        
        .bottom-nav .nav-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.65rem;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 10px 6px;
            border-radius: 14px;
            position: relative;
            overflow: hidden;
        }
        
        .bottom-nav .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            transform: scale(0);
            transition: transform 0.3s ease;
        }
        
        .bottom-nav .nav-link:hover::before {
            transform: scale(1);
        }
        
        .bottom-nav .nav-link i {
            font-size: 1.2rem;
            margin-bottom: 5px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }
        
        .bottom-nav .nav-link span {
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .bottom-nav .nav-link.active {
            color: #ffffff;
            transform: translateY(-3px);
        }
        
        .bottom-nav .nav-link.active::before {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1);
        }
        
        .bottom-nav .nav-link.active i {
            color: #ffffff;
            transform: scale(1.1);
        }
        
        /* Home button special styling */
        .bottom-nav .home-item {
            position: relative;
        }
        
        .bottom-nav .home-item .nav-link {
            position: relative;
            top: -18px;
            background: linear-gradient(135deg, #ff6b35, #ff6b35);
            border-radius: 50%;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
            color: white;
            font-size: 0.75rem;
        }
        
        .bottom-nav .home-item .nav-link::before {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .bottom-nav .home-item .nav-link.active {
            background: linear-gradient(135deg, #ff6b35, #ff6b35);
            transform: translateY(-6px) scale(1.05);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.5);
        }
        
        .bottom-nav .home-item .nav-link.active i {
            color: white;
            transform: scale(1.2);
        }
        
        .bottom-nav .home-item .nav-link.active span {
            color: white;
            font-weight: 700;
        }
        
        /* Hover effects for all nav items */
        .bottom-nav .nav-link:hover {
            transform: translateY(-2px);
            color: rgba(255, 255, 255, 0.95);
        }
        
        .bottom-nav .nav-link:hover i {
            transform: scale(1.1);
        }
        
        /* Active state animations */
        .bottom-nav .nav-link.active {
            animation: navItemActive 0.4s ease-out;
        }
        
        @keyframes navItemActive {
            0% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-6px) scale(1.05);
            }
            100% {
                transform: translateY(-3px) scale(1);
            }
        }
        
        /* Home button special animation */
        .bottom-nav .home-item .nav-link.active {
            animation: homeButtonActive 0.5s ease-out;
        }
        
        @keyframes homeButtonActive {
            0% {
                transform: translateY(0) scale(1);
                box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
            }
            50% {
                transform: translateY(-10px) scale(1.1);
                box-shadow: 0 12px 35px rgba(255, 107, 53, 0.6);
            }
            100% {
                transform: translateY(-6px) scale(1.05);
                box-shadow: 0 10px 30px rgba(255, 107, 53, 0.5);
            }
        }
        
        /* Ripple effect for nav items */
        .bottom-nav .nav-link {
            position: relative;
            overflow: hidden;
        }
        
        .bottom-nav .nav-link::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .bottom-nav .nav-link:active::after {
            width: 300px;
            height: 300px;
        }
        
        /* Hide sidebar on mobile */
        @media (max-width: 768px) {
            .sidebar {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 15px;
                padding-top: 20px;
            }
        }
        
        /* Desktop styles */
        @media (min-width: 769px) {
            .bottom-nav {
                display: none !important;
            }
            
            .sidebar {
                display: block !important;
            }
        }
        
        /* Logout icon styling */
        .logout-icon {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .logout-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        }
        
        .logout-icon:active {
            transform: translateY(0);
        }

        /* SweetAlert2 Custom Styles */
        .swal2-popup {
            border-radius: 15px !important;
            font-family: 'Inter', sans-serif !important;
            font-size: 0.9rem !important;
            max-width: 350px !important;
            padding: 1.5rem !important;
        }

        .swal2-title {
            font-weight: 600 !important;
            color: #2c3e50 !important;
            font-size: 1.1rem !important;
        }

        .swal2-content {
            color: #6c757d !important;
            font-size: 0.85rem !important;
        }

        .swal2-confirm {
            border-radius: 8px !important;
            font-weight: 500 !important;
            padding: 8px 16px !important;
            font-size: 0.8rem !important;
        }

        .swal2-cancel {
            border-radius: 8px !important;
            font-weight: 500 !important;
            padding: 8px 16px !important;
            font-size: 0.8rem !important;
        }

        .swal2-icon {
            font-size: 1.8rem !important;
        }

        /* Animation classes */
        .animate__animated {
            animation-duration: 0.3s;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translate3d(0, -30px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes fadeOutUp {
            from {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
            to {
                opacity: 0;
                transform: translate3d(0, -30px, 0);
            }
        }

        .animate__fadeInDown {
            animation-name: fadeInDown;
        }

        .animate__fadeOutUp {
            animation-name: fadeOutUp;
        }
    </style>
</head>
<body>
    <!-- Simple Header -->
    @if(!request()->routeIs('student.cart'))
    <div class="bg-white shadow-sm py-2 sticky-top" style="z-index: 1020;">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="SPPQU Logo" style="width: 35px; height: 35px; object-fit: contain;" class="me-3">
                    <h6 class="mb-0 fw-bold">SPPQU</h6>
                </div>
                
                <div class="d-flex align-items-center">
                    <!-- Shopping Cart -->
                    <div class="position-relative me-3">
                        <a href="{{ route('student.cart') }}" class="text-decoration-none">
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                <i class="fas fa-shopping-cart text-white" style="font-size: 14px;"></i>
                            </div>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartBadge" style="font-size: 0.6rem; display: none;">
                                0
                            </span>
                        </a>
                    </div>
                    
                    <!-- Logout Button (uses GET to avoid 419) -->
                    <button type="button" class="border-0 bg-transparent p-0" style="cursor: pointer;" onclick="confirmLogout()">
                        <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center logout-icon" style="width: 35px; height: 35px;">
                            <i class="fas fa-sign-out-alt text-white" style="font-size: 14px;"></i>
                        </div>
                    </button>
                    
                    <!-- Hidden form removed: using GET logout to avoid CSRF issues -->
                </div>
            </div>
        </div>
                        </div>
    @endif
                    
    <div class="d-flex">
        <!-- Sidebar (Desktop) -->
        @if(!request()->routeIs('student.cart'))
        <div class="sidebar bg-white shadow-sm" style="width: 250px; min-height: calc(100vh - 60px); position: fixed; left: 0; top: 60px; z-index: 1010;">
            <div class="p-3">
                <div class="list-group list-group-flush">
                    <a href="{{ route('student.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                    <a href="{{ route('student.bills') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.bills') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice me-2"></i>Tagihan
                    </a>

                    <a href="{{ route('student.tabungan') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.tabungan') ? 'active' : '' }}">
                        <i class="fas fa-university me-2"></i>Tabungan
                    </a>
                    @php
                        $hasBK = hasBKAddon();
                    @endphp
                    @if($hasBK)
                    <a href="{{ route('student.bk.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.bk.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list me-2"></i>BK Siswa
                    </a>
                    @else
                    <a href="#" class="list-group-item list-group-item-action disabled" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" title="Add-on tidak aktif">
                        <i class="fas fa-clipboard-list me-2"></i>BK Siswa
                        <small class="text-muted d-block" style="font-size: 0.7rem;">(Add-on tidak aktif)</small>
                    </a>
                    @endif
                    <a href="{{ route('student.payment.history') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.payment.history') ? 'active' : '' }}">
                        <i class="fas fa-history me-2"></i>Riwayat Pembayaran
                    </a>
                    <a href="{{ route('student.profile') }}" class="list-group-item list-group-item-action {{ request()->routeIs('student.profile') ? 'active' : '' }}">
                        <i class="fas fa-user me-2"></i>Profile
                    </a>
                </div>
                        </div>
                        </div>
                    @endif

        <!-- Main Content -->
        <div class="main-content flex-grow-1" style="{{ request()->routeIs('student.cart') ? 'margin-left: 0; padding-top: 0;' : 'margin-left: 250px; padding-top: 15px;' }}">
                    @yield('content')
        </div>
    </div>

    <!-- Bottom Navigation (Mobile) - Modern Design -->
    <nav class="bottom-nav">
        <div class="row g-0">
            <div class="col">
                <a href="{{ route('student.bills') }}" class="nav-link {{ request()->routeIs('student.bills') ? 'active' : '' }}">
                    <i class="fas fa-receipt"></i>
                    <span>Tagihan</span>
                </a>
            </div>

            @php
                $hasEJurnal = hasEJurnalAddon();
                $hasBK = hasBKAddon();
            @endphp
            <div class="col">
                @if($hasEJurnal)
                <a href="{{ route('student.jurnal.index') }}" class="nav-link {{ request()->routeIs('student.jurnal.*') ? 'active' : '' }}">
                    <i class="fas fa-book" style="color: inherit !important;"></i>
                    <span>E-Jurnal</span>
                </a>
                @else
                <a href="#" class="nav-link disabled" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" title="Add-on tidak aktif">
                    <i class="fas fa-book" style="color: inherit !important;"></i>
                    <span>E-Jurnal</span>
                </a>
                @endif
            </div>

            <div class="col home-item">
                <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </div>

            <div class="col">
                @if($hasBK)
                <a href="{{ route('student.bk.index') }}" class="nav-link {{ request()->routeIs('student.bk.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>BK</span>
                </a>
                @else
                <a href="#" class="nav-link disabled" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" title="Add-on tidak aktif">
                    <i class="fas fa-clipboard-list"></i>
                    <span>BK</span>
                </a>
                @endif
            </div>

            <div class="col">
                <a href="{{ route('student.profile') }}" class="nav-link {{ request()->routeIs('student.profile') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i>
                    <span>Profil</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- PWA Install Button -->
    <button id="pwaInstallBtn" class="pwa-install-btn" onclick="showInstallPrompt()" title="Install SPPQU">
        <i class="fas fa-download"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @stack('scripts')
    
    <script>
        // Global cart badge update function
        function updateCartBadge() {
            const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
            
            // Update header cart badge
            const cartBadge = document.getElementById('cartBadge');
            if (cartBadge) {
                cartBadge.textContent = cart.length;
                cartBadge.style.display = cart.length > 0 ? 'block' : 'none';
            }
        }

        // Update cart badge on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartBadge();
        });

        // Listen for storage changes (when cart is updated from other pages)
        window.addEventListener('storage', function(e) {
            if (e.key === 'studentCart') {
                updateCartBadge();
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Mobile sidebar toggle (if needed)
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
        }

        // Enhanced logout confirmation with SweetAlert2 (GET logout to avoid 419)
        function confirmLogout() {
            Swal.fire({
                title: 'Keluar dari Aplikasi?',
                text: 'Apakah Anda yakin ingin keluar dari SPPQU Digital Payment?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-sign-out-alt me-2"></i>Ya, Keluar',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'animated fadeInDown',
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang keluar dari aplikasi',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    clearStudentData();
                    window.location.href = '{{ route('student.logout.get') }}';
                }
            });
        }

        // PWA Installation and Service Worker
        let deferredPrompt;
        let installButton;

        // PWA Install Prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show manual install button
            const installBtn = document.getElementById('pwaInstallBtn');
            if (installBtn) {
                installBtn.classList.add('show');
            }
            
            // Show install prompt after 5 seconds
            setTimeout(() => {
                showInstallPrompt();
            }, 5000);
        });

        function showInstallPrompt() {
            if (deferredPrompt) {
                Swal.fire({
                    title: '<i class="fas fa-download text-primary me-2"></i>Install SPPQU',
                    html: `
                        <div class="text-center">
                            <i class="fas fa-mobile-alt text-success mb-3" style="font-size: 3rem;"></i>
                            <h5 class="mb-3">Install SPPQU di HP Anda</h5>
                            <p class="text-muted mb-4">
                                Dapatkan pengalaman terbaik dengan menginstall SPPQU sebagai aplikasi di HP Anda.
                                Akses cepat, notifikasi real-time, dan fitur offline.
                            </p>
                            <div class="row text-start">
                                <div class="col-6">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <small>Akses Cepat</small>
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <small>Notifikasi Real-time</small>
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <small>Fitur Offline</small>
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <small>Update Otomatis</small>
                                </div>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-download me-2"></i>Install Sekarang',
                    cancelButtonText: '<i class="fas fa-times me-2"></i>Nanti',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                    customClass: {
                        popup: 'swal-wide',
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        installPWA();
                    }
                });
            }
        }

        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'SPPQU sedang diinstall di HP Anda',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                });
            }
        }

        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered: ', registration);
                    })
                    .catch(function(registrationError) {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }

        // Check if app is installed
        window.addEventListener('appinstalled', (evt) => {
            console.log('SPPQU was installed');
        });

        // Cache busting and localStorage management
        document.addEventListener('DOMContentLoaded', function() {
            // Clear localStorage if student ID has changed
            const currentStudentId = '{{ session("student_id") }}';
            const storedStudentId = localStorage.getItem('currentStudentId');
            
            if (storedStudentId && storedStudentId !== currentStudentId) {
                console.log('Student ID changed, clearing localStorage');
                localStorage.clear();
            }
            
            // Store current student ID
            localStorage.setItem('currentStudentId', currentStudentId);
            
            // Add timestamp to prevent caching
            const timestamp = new Date().getTime();
            localStorage.setItem('lastAccess', timestamp);
            
            // Force reload if page is cached (older than 5 minutes)
            const lastAccess = localStorage.getItem('lastAccess');
            const fiveMinutesAgo = timestamp - (5 * 60 * 1000);
            
            if (lastAccess && parseInt(lastAccess) < fiveMinutesAgo) {
                console.log('Page might be cached, forcing reload');
                window.location.reload(true);
            }
            
            // Auto-refresh CSRF token every 10 minutes to prevent 419 errors
            setInterval(function() {
                refreshCsrfToken();
            }, 10 * 60 * 1000); // 10 minutes
        });
        
        // Function to refresh CSRF token
        function refreshCsrfToken() {
            fetch('{{ route("student.refresh-csrf") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update CSRF token in meta tag
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf_token);
                    
                    // Update CSRF token in all forms
                    document.querySelectorAll('input[name="_token"]').forEach(input => {
                        input.value = data.csrf_token;
                    });
                    
                    console.log('CSRF token refreshed successfully');
                }
            })
            .catch(error => {
                console.error('Error refreshing CSRF token:', error);
            });
        }

        // Clear localStorage on logout
        function clearStudentData() {
            localStorage.clear();
            sessionStorage.clear();
        }

        // Override browser back/forward cache
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                console.log('Page loaded from cache, forcing reload');
                window.location.reload(true);
            }
        });

        // Prevent caching on page unload
        window.addEventListener('beforeunload', function() {
            // Clear any temporary data
            sessionStorage.removeItem('tempData');
        });

        // Smart Cache Management - Only reload when necessary
        window.addEventListener('pageshow', function(event) {
            // Only reload if page came from bfcache (back/forward navigation)
            if (event.persisted) {
                console.log('Page loaded from bfcache, reloading for fresh data');
                window.location.reload();
            }
        });
    </script>
</body>
</html> 