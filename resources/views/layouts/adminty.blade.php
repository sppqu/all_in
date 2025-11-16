<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SPPQU - Sistem Pembayaran Peserta Didik">
    <meta name="keywords" content="SPPQU, Pembayaran, Sekolah">
    <meta name="author" content="SPPQU">
    
    <title>{{ config('app.name', 'SPPQU') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    
    <!-- Required Framework - Bootstrap -->
    <link rel="stylesheet" type="text/css" href="{{ asset('template-assets/bower_components/bootstrap/css/bootstrap.min.css') }}">
    
    <!-- Feather Icons -->
    <link rel="stylesheet" type="text/css" href="{{ asset('template-assets/assets/icon/feather/css/feather.css') }}">
    
    <!-- Font Awesome (for compatibility with existing icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- PNotify CSS -->
    <link rel="stylesheet" href="{{ asset('template-assets/bower_components/pnotify/css/pnotify.css') }}">
    <link rel="stylesheet" href="{{ asset('template-assets/bower_components/pnotify/css/pnotify.buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('template-assets/bower_components/pnotify/css/pnotify.brighttheme.css') }}">
    
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('template-assets/assets/css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('template-assets/assets/css/jquery.mCustomScrollbar.css') }}">
    
    <!-- Custom Styles -->
    <style>
        body {
            background: #f4f5f7;
        }
        
        /* Custom styling untuk menu aktif */
        .pcoded-navbar .pcoded-item > li.pcoded-trigger > a,
        .pcoded-navbar .pcoded-item > li.active > a {
            background: #008060 !important;
            color: #fff !important;
        }
        
        .pcoded-navbar .pcoded-submenu li.active > a {
            background: #006d52 !important;
            color: #fff !important;
        }
        
        /* Styling untuk tanda chevron pada menu dengan submenu */
        /* Sembunyikan chevron bawaan template jika ada */
        .pcoded-hasmenu > a .pcoded-mcaret {
            display: inline-block !important;
            float: right;
            font-size: 14px;
            line-height: 1.5;
            margin-left: auto;
            text-align: right;
            width: 20px;
            transition: transform 0.3s ease;
            position: relative;
            background: transparent !important;
            border: none !important;
            height: auto !important;
        }
        
        /* Sembunyikan styling bawaan template yang membuat border/arrow */
        .pcoded-hasmenu.active > a > .pcoded-mcaret,
        .pcoded-hasmenu.pcoded-trigger > a > .pcoded-mcaret {
            background: transparent !important;
            border: none !important;
            height: auto !important;
            width: 20px !important;
            position: relative !important;
            top: auto !important;
            right: auto !important;
        }
        
        /* Sembunyikan icon chevron bawaan template jika ada */
        .pcoded-hasmenu > a .pcoded-mcaret i,
        .pcoded-hasmenu > a .pcoded-mcaret::after {
            display: none !important;
        }
        
        /* Tampilkan chevron custom dari ::before */
        .pcoded-hasmenu > a .pcoded-mcaret::before {
            content: ">" !important;
            display: block !important;
            color: inherit;
            font-weight: bold;
        }
        
        .pcoded-hasmenu.pcoded-trigger > a .pcoded-mcaret {
            transform: rotate(90deg);
        }
        
        /* Pastikan tidak ada chevron lain dari template pada tag <a> */
        .pcoded-hasmenu > a::after,
        .pcoded-hasmenu > a::before {
            display: none !important;
        }
        
        /* Logo styling */
        .navbar-logo img {
            max-height: 40px;
        }
        
        /* User profile dropdown */
        .user-profile .dropdown-toggle {
            color: #333;
        }
        
        /* Content area */
        .pcoded-content {
            padding: 20px;
        }
        
        /* Card styling */
        .card {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* Toast container */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        /* Toast styling */
        .toast {
            border-radius: 8px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-bottom: 10px;
            max-width: 400px;
            word-wrap: break-word;
        }
        
        .toast-header {
            border-radius: 8px 8px 0 0;
            padding: 12px 16px;
            font-weight: 600;
        }
        
        .toast-body {
            border-radius: 0 0 8px 8px;
            padding: 12px 16px;
            line-height: 1.5;
            font-size: 14px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .toast.show {
            display: block;
            opacity: 1;
        }
        
        /* Logout Menu Styling */
        .pcoded-item li form a {
            display: block;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .pcoded-item li form a:hover {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
        }
        
        .pcoded-item li form a .pcoded-micon {
            color: #dc3545;
        }
        
        .pcoded-item li form a .pcoded-mtext {
            color: #dc3545;
            font-weight: 500;
        }
        
        /* Custom Select Styling */
        .form-control.select-default,
        select.form-control.select-default {
            border: 1px solid #cccccc;
            border-radius: 2px;
            padding: 8px 12px;
            font-size: 14px;
            background-color: #fff;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .form-control.select-default:focus,
        select.form-control.select-default:focus {
            border-color: #01a9ac;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(1, 169, 172, 0.25);
        }
        
        .form-control.select-primary,
        select.form-control.select-primary {
            border: 1px solid #01a9ac;
            border-radius: 2px;
            padding: 1px 12px;
            font-size: 14px;
            background-color: #fff;
            color: #333;
            transition: all 0.3s ease;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2301a9ac' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        
        .form-control.select-primary:focus,
        select.form-control.select-primary:focus {
            border-color: #008080;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(1, 169, 172, 0.25);
        }
        
        /* Override form-select untuk kompatibilitas */
        select.form-control,
        .form-control select {
            height: auto;
            line-height: 1.5;
        }
        
        /* Select2 styling untuk form-control */
        .form-control.select2-container {
            width: 100% !important;
        }
        
        .select2-container--default .select2-selection--single {
            border: 1px solid #cccccc;
            border-radius: 2px;
            height: 38px;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #01a9ac;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding-left: 12px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 10px;
        }
        
        /* Switch Toggle Styling */
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            cursor: pointer;
            background-color: #cccccc;
            border: 1px solid #cccccc;
            border-radius: 2em;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        
        .form-switch .form-check-input:checked {
            background-color: #01a9ac;
            border-color: #01a9ac;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
            background-position: right center;
        }
        
        .form-switch .form-check-input:focus {
            border-color: #01a9ac;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(1, 169, 172, 0.25);
        }
        
        .form-switch .form-check-label {
            margin-left: 0.5rem;
            cursor: pointer;
            font-weight: 500;
        }
        
        /* Checkbox Primary Styling */
        .form-control.checkbox-primary,
        input.form-control[type="checkbox"].checkbox-primary {
            width: 20px;
            height: 20px;
            cursor: pointer;
            border: 2px solid #01a9ac !important;
            border-radius: 4px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: #fff;
            position: relative;
            margin: 0;
            margin-right: 0.5rem;
            vertical-align: middle;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        
        .form-control.checkbox-primary:checked,
        input.form-control[type="checkbox"].checkbox-primary:checked {
            background-color: #01a9ac;
            border-color: #01a9ac !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 14px 14px;
        }
        
        .form-control.checkbox-primary:focus,
        input.form-control[type="checkbox"].checkbox-primary:focus {
            border-color: #01a9ac !important;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(1, 169, 172, 0.25);
        }
        
        .form-control.checkbox-primary:hover,
        input.form-control[type="checkbox"].checkbox-primary:hover {
            border-color: #008080 !important;
        }
        
        .form-check {
            display: flex;
            align-items: center;
        }
        
        .form-check-label {
            cursor: pointer;
            margin-left: 0;
            line-height: 1.5;
            vertical-align: middle;
            display: inline-block;
        }
    </style>
    
    @stack('styles')
    @yield('head')
</head>

<body>
    <!-- Pre-loader start -->
    <div class="theme-loader">
        <div class="ball-scale">
            <div class='contain'>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
            </div>
        </div>
    </div>
    <!-- Pre-loader end -->
    
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            
            <!-- Header/Navbar -->
            <nav class="navbar header-navbar pcoded-header">
                <div class="navbar-wrapper">
                    <div class="navbar-logo">
                        <a class="mobile-menu" id="mobile-collapse" href="#!">
                            <i class="feather icon-menu"></i>
                        </a>
                        <a href="{{ route('admin.dashboard') }}">
                            <img class="img-fluid" src="{{ asset('images/logo.png') }}" alt="SPPQU Logo" style="max-height: 40px;">
                        </a>
                        <a class="mobile-options">
                            <i class="feather icon-more-horizontal"></i>
                        </a>
                    </div>
                    
                    <div class="navbar-container container-fluid">
                        <ul class="nav-left">
                            <li class="header-search">
                                <div class="main-search morphsearch-search">
                                    <div class="input-group">
                                        <span class="input-group-addon search-close"><i class="feather icon-x"></i></span>
                                        <input type="text" class="form-control" placeholder="Cari...">
                                        <span class="input-group-addon search-btn"><i class="feather icon-search"></i></span>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <a href="#!" onclick="javascript:toggleFullScreen()">
                                    <i class="feather icon-maximize full-screen"></i>
                                </a>
                            </li>
                        </ul>
                        <ul class="nav-right">
                            @if(auth()->check() && (auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan'))
                                @php
                                    $foundationId = session('foundation_id');
                                    $foundation = $foundationId ? \App\Models\Foundation::find($foundationId) : null;
                                    $schools = $foundation ? $foundation->activeSchools()->get() : collect();
                                    $currentSchool = currentSchool();
                                @endphp
                                @if($foundation && $schools->count() > 0)
                                <li class="header-notification">
                                    <div class="dropdown-primary dropdown">
                                        <div class="dropdown-toggle" data-toggle="dropdown">
                                            <i class="feather icon-school"></i>
                                            <span class="badge bg-c-blue">{{ $currentSchool ? Str::limit($currentSchool->nama_sekolah, 15) : 'Pilih Sekolah' }}</span>
                                        </div>
                                        <ul class="show-notification notification-view dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                            <li><h6>{{ Str::limit($foundation->nama_yayasan, 30) }}</h6></li>
                                            @foreach($schools as $school)
                                            <li>
                                                <form action="{{ route('manage.foundation.schools.switch', $school->id) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item {{ session('current_school_id') == $school->id ? 'active' : '' }}" style="width: 100%; text-align: left; border: none; background: transparent;">
                                                        <i class="feather icon-check {{ session('current_school_id') == $school->id ? '' : 'd-none' }}"></i>
                                                        {{ $school->nama_sekolah }}
                                                    </button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                                @endif
                            @endif
                            
                            <li class="user-profile header-notification">
                                <div class="dropdown-primary dropdown">
                                    <div class="dropdown-toggle" data-toggle="dropdown">
                                        @php $avatar = auth()->check() ? (auth()->user()->avatar_path ?? null) : null; @endphp
                                        <img src="{{ $avatar ? asset('storage/'.$avatar) : asset('images/logo.png') }}" class="img-radius" alt="User-Profile-Image" style="width: 40px; height: 40px; object-fit: cover;">
                                        <span>{{ auth()->user()->name ?? 'User' }}</span>
                                        <i class="feather icon-chevron-down"></i>
                                    </div>
                                    <ul class="show-notification profile-notification dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                        <li>
                                            <a href="{{ route('manage.profile.edit') }}">
                                                <i class="feather icon-user"></i> Profil
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('manage.profile.edit') }}">
                                                <i class="feather icon-settings"></i> Pengaturan
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('manage.logout') }}" class="m-0">
                                                @csrf
                                                <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; border: none; background: transparent; padding: 10px 20px;">
                                                    <i class="feather icon-log-out"></i> Logout
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <!-- Main Container -->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <!-- Sidebar Navigation -->
                    <nav class="pcoded-navbar" pcoded-header-position="relative">
                        <div class="pcoded-inner-navbar main-menu">
                            
                            <!-- YAYASAN Menu (Superadmin & Admin Yayasan) -->
                            @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan')
                            <div class="pcoded-navigatio-lavel">YAYASAN</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="{{ request()->routeIs('manage.foundation.dashboard') ? 'active' : '' }}">
                                    <a href="{{ route('manage.foundation.dashboard') }}">
                                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                                        <span class="pcoded-mtext">Dashboard</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('manage.foundation.schools.*') ? 'active' : '' }}">
                                    <a href="{{ route('manage.foundation.schools.index') }}">
                                        <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                                        <span class="pcoded-mtext">Kelola Sekolah</span>
                                    </a>
                                </li>
                                
                                @if(function_exists('menuCan') && menuCan('menu.users'))
                                <li class="{{ request()->routeIs('manage.users.*') ? 'active' : '' }}">
                                    <a href="{{ route('manage.users.index') }}">
                                        <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                                        <span class="pcoded-mtext">Kelola Pengguna</span>
                                    </a>
                                </li>
                                @endif
                                
                                <li class="pcoded-hasmenu {{ request()->routeIs('manage.foundation.laporan.*') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-bar-chart-2"></i></span>
                                        <span class="pcoded-mtext">Monitoring Keuangan</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('manage.foundation.laporan.pemasukan') ? 'active' : '' }}">
                                            <a href="{{ route('manage.foundation.laporan.pemasukan') }}">
                                                <span class="pcoded-mtext">Laporan Pemasukan</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.foundation.laporan.tunggakan') ? 'active' : '' }}">
                                            <a href="{{ route('manage.foundation.laporan.tunggakan') }}">
                                                <span class="pcoded-mtext">Tunggakan</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.foundation.laporan.jenis-biaya') ? 'active' : '' }}">
                                            <a href="{{ route('manage.foundation.laporan.jenis-biaya') }}">
                                                <span class="pcoded-mtext">Laporan Jenis Biaya</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                
                                @if(function_exists('menuCan') && menuCan('menu.billing'))
                                <li class="{{ request()->routeIs('manage.subscription.*') ? 'active' : '' }}">
                                    <a href="{{ route('manage.subscription.index') }}">
                                        <span class="pcoded-micon"><i class="feather icon-credit-card"></i></span>
                                        <span class="pcoded-mtext">Berlangganan</span>
                                    </a>
                                </li>
                                @endif
                                
                                @if(function_exists('menuCan') && menuCan('menu.billing'))
                                <li class="{{ request()->routeIs('manage.addons.*') ? 'active' : '' }}">
                                    <a href="{{ route('manage.addons.index') }}">
                                        <span class="pcoded-micon"><i class="feather icon-package"></i></span>
                                        <span class="pcoded-mtext">Add-ons Premium</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                            @endif
                            
                            <!-- APLIKASI Menu -->
                            @if(auth()->user()->role !== 'superadmin' && auth()->user()->role !== 'admin_yayasan')
                            <div class="pcoded-navigatio-lavel">APLIKASI</div>
                            <ul class="pcoded-item pcoded-left-item">
                                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal') || auth()->user()->role == 'superadmin')
                                <li class="{{ request()->is('manage/dashboard') ? 'active' : '' }}">
                                    <a href="{{ url('/manage/dashboard') }}">
                                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                                        <span class="pcoded-mtext">Dashboard</span>
                                    </a>
                                </li>
                                @endif
                                
                                <!-- Data Master Menu -->
                                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && menuCan('menu.data_master'))
                                <li class="pcoded-hasmenu {{ request()->is('tahun-pelajaran') || request()->is('kelas') || request()->is('peserta-didik') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-folder"></i></span>
                                        <span class="pcoded-mtext">Data Master</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->is('tahun-pelajaran') ? 'active' : '' }}">
                                            <a href="{{ url('/tahun-pelajaran') }}">
                                                <span class="pcoded-mtext">Tahun Pelajaran</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->is('kelas') ? 'active' : '' }}">
                                            <a href="{{ url('/kelas') }}">
                                                <span class="pcoded-mtext">Kelas</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->is('peserta-didik') ? 'active' : '' }}">
                                            <a href="{{ url('/peserta-didik') }}">
                                                <span class="pcoded-mtext">Peserta Didik</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                                
                                <!-- Setting Tarif Menu -->
                                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && menuCan('menu.setting_tarif'))
                                <li class="pcoded-hasmenu {{ request()->routeIs('pos.*') || request()->routeIs('payment.index') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-sliders"></i></span>
                                        <span class="pcoded-mtext">Setting Tarif</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('pos.index') ? 'active' : '' }}">
                                            <a href="{{ route('pos.index') }}">
                                                <span class="pcoded-mtext">Nama Pos Pembayaran</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('payment.index') ? 'active' : '' }}">
                                            <a href="{{ route('payment.index') }}">
                                                <span class="pcoded-mtext">Setting Tarif Pembayaran</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                                
                                <!-- Pembayaran Menu -->
                                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && function_exists('menuCan') && menuCan('menu.pembayaran'))
                                <li class="pcoded-hasmenu {{ request()->routeIs('payment.cash') || request()->routeIs('online-payment.*') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-credit-card"></i></span>
                                        <span class="pcoded-mtext">Pembayaran</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('payment.cash') ? 'active' : '' }}">
                                            <a href="{{ route('payment.cash') }}">
                                                <span class="pcoded-mtext">Pembayaran Tunai</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('online-payment.*') ? 'active' : '' }}">
                                            <a href="{{ route('online-payment.index') }}">
                                                <span class="pcoded-mtext">Pembayaran Online</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                                
                                <!-- Tabungan Menu -->
                                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && function_exists('menuCan') && menuCan('menu.tabungan'))
                                <li class="pcoded-hasmenu {{ request()->routeIs('manage.tabungan.*') || request()->routeIs('admin.rekapitulasi-tabungan.*') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-save"></i></span>
                                        <span class="pcoded-mtext">Tabungan</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('manage.tabungan.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.tabungan.index') }}">
                                                <span class="pcoded-mtext">Kelola Tabungan</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('admin.rekapitulasi-tabungan.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.rekapitulasi-tabungan.index') }}">
                                                <span class="pcoded-mtext">Rekapitulasi Tabungan</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                                
                                <!-- Akuntansi Menu -->
                                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && function_exists('menuCan') && menuCan('menu.akuntansi'))
                                <li class="pcoded-hasmenu {{ request()->routeIs('manage.accounting.*') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-trending-up"></i></span>
                                        <span class="pcoded-mtext">Keuangan</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('manage.accounting.kas.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.accounting.kas.index') }}">
                                                <span class="pcoded-mtext">Daftar Kas</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.accounting.payment-methods.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.accounting.payment-methods.index') }}">
                                                <span class="pcoded-mtext">Metode Pembayaran</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.accounting.receipt-pos.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.accounting.receipt-pos.index') }}">
                                                <span class="pcoded-mtext">Pos Penerimaan</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.accounting.expense-pos.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.accounting.expense-pos.index') }}">
                                                <span class="pcoded-mtext">Pos Pengeluaran</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.accounting.cash-transfer.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.accounting.cash-transfer.index') }}">
                                                <span class="pcoded-mtext">Pindah Buku Kas</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.accounting.cashflow.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.accounting.cashflow.index') }}">
                                                <span class="pcoded-mtext">Arus Kas</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                                
                                <!-- Laporan Menu -->
                                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && function_exists('menuCan') && menuCan('menu.laporan'))
                                <li class="pcoded-hasmenu {{ request()->routeIs('manage.laporan*') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                                        <span class="pcoded-mtext">Laporan Pembayaran</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('manage.laporan-perpos') ? 'active' : '' }}">
                                            <a href="{{ route('manage.laporan-perpos') }}">
                                                <span class="pcoded-mtext">Laporan PerPos</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.laporan-perkelas') ? 'active' : '' }}">
                                            <a href="{{ route('manage.laporan-perkelas') }}">
                                                <span class="pcoded-mtext">Laporan PerKelas</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.laporan-rekapitulasi') ? 'active' : '' }}">
                                            <a href="{{ route('manage.laporan-rekapitulasi') }}">
                                                <span class="pcoded-mtext">Laporan Rekapitulasi</span>
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
                                        @if($hasAnalisisTarget)
                                        <li class="{{ request()->routeIs('manage.laporan.realisasi-pos') ? 'active' : '' }}">
                                            <a href="{{ route('manage.laporan.realisasi-pos') }}">
                                                <span class="pcoded-mtext">Analisis Target Capaian</span>
                                            </a>
                                        </li>
                                        @endif
                                        <li class="{{ request()->routeIs('manage.laporan.tunggakan-siswa') ? 'active' : '' }}">
                                            <a href="{{ route('manage.laporan.tunggakan-siswa') }}">
                                                <span class="pcoded-mtext">Tunggakan Siswa</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                            </ul>
                            @endif
                            
                            <!-- ADD-ONS Menu -->
                            @if(auth()->user()->role !== 'superadmin' && auth()->user()->role !== 'admin_yayasan')
                            <div class="pcoded-navigatio-lavel">ADD-ONS</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <!-- SPMB Menu -->
                                @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal' || auth()->user()->role == 'superadmin') && hasSPMBAddon())
                                <li class="pcoded-hasmenu {{ request()->routeIs('manage.spmb.*') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-user-plus"></i></span>
                                        <span class="pcoded-mtext">SPMB</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('manage.spmb.index') ? 'active' : '' }}">
                                            <a href="{{ route('manage.spmb.index') }}">
                                                <span class="pcoded-mtext">Dashboard</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.spmb.registrations*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.spmb.registrations') }}">
                                                <span class="pcoded-mtext">Data Pendaftaran</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.spmb.settings*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.spmb.settings') }}">
                                                <span class="pcoded-mtext">Pengaturan</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.spmb.transfer-to-students*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.spmb.transfer-to-students') }}">
                                                <span class="pcoded-mtext">Transfer ke Students</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.spmb.form-settings*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.spmb.form-settings.index') }}">
                                                <span class="pcoded-mtext">Pengaturan Form</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.spmb.payments*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.spmb.payments') }}">
                                                <span class="pcoded-mtext">Lihat Pembayaran</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                                
                                <!-- BK Menu -->
                                @if(hasBKAddon())
                                <li class="pcoded-hasmenu {{ request()->routeIs('manage.bk.*') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-clipboard"></i></span>
                                        <span class="pcoded-mtext">Bimbingan Konseling</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('manage.bk.dashboard') ? 'active' : '' }}">
                                            <a href="{{ route('manage.bk.dashboard') }}">
                                                <span class="pcoded-mtext">Dashboard</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.bk.pelanggaran-siswa.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.bk.pelanggaran-siswa.index') }}">
                                                <span class="pcoded-mtext">Pelanggaran Siswa</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.bk.bimbingan.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.bk.bimbingan.index') }}">
                                                <span class="pcoded-mtext">Bimbingan Konseling</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.bk.bimbingan-konseling') ? 'active' : '' }}">
                                            <a href="{{ route('manage.bk.bimbingan-konseling') }}">
                                                <span class="pcoded-mtext">Siswa Perlu Bimbingan</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.bk.pelanggaran.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.bk.pelanggaran.index') }}">
                                                <span class="pcoded-mtext">Master Pelanggaran</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                                
                                <!-- E-Jurnal Menu -->
                                @if(hasEJurnalAddon())
                                <li class="pcoded-hasmenu {{ request()->is('jurnal/guru*') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-book"></i></span>
                                        <span class="pcoded-mtext">E-Jurnal</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('jurnal.guru.index') ? 'active' : '' }}">
                                            <a href="{{ route('jurnal.guru.index') }}">
                                                <span class="pcoded-mtext">Dashboard</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('jurnal.guru.lihat') ? 'active' : '' }}">
                                            <a href="{{ route('jurnal.guru.lihat') }}">
                                                <span class="pcoded-mtext">Lihat Jurnal</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                                
                                <!-- E-Perpustakaan Menu -->
                                @if(hasLibraryAddon())
                                <li class="pcoded-hasmenu {{ request()->is('library/*') || request()->is('manage/library/*') ? 'active pcoded-trigger' : '' }}">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-book"></i></span>
                                        <span class="pcoded-mtext">E-Perpustakaan</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('library.index') ? 'active' : '' }}">
                                            <a href="{{ route('library.index') }}">
                                                <span class="pcoded-mtext">Dashboard</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.library.categories.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.library.categories.index') }}">
                                                <span class="pcoded-mtext">Kategori Buku</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.library.books.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.library.books.index') }}">
                                                <span class="pcoded-mtext">Kelola Buku</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('manage.library.loans.*') ? 'active' : '' }}">
                                            <a href="{{ route('manage.library.loans.index') }}">
                                                <span class="pcoded-mtext">Peminjaman</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('library.search') && request('sort') == 'latest' ? 'active' : '' }}">
                                            <a href="{{ route('library.search', ['sort' => 'latest']) }}">
                                                <span class="pcoded-mtext">Buku Terbaru</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                            </ul>
                            @endif
                            
                            <!-- LAINNYA Menu -->
                            @if(auth()->user()->role !== 'superadmin' && auth()->user()->role !== 'admin_yayasan')
                            @if((!auth()->user()->is_bk && auth()->user()->role !== 'admin_jurnal') || auth()->user()->role == 'superadmin')
                            <div class="pcoded-navigatio-lavel">LAINNYA</div>
                            <ul class="pcoded-item pcoded-left-item">
                                @if(menuCan('menu.billing') && in_array(auth()->user()->role, ['admin', 'superadmin']))
                                <li class="{{ request()->routeIs('manage.subscription.*') ? 'active' : '' }}">
                                    <a href="{{ route('manage.subscription.index') }}">
                                        <span class="pcoded-micon"><i class="feather icon-credit-card"></i></span>
                                        <span class="pcoded-mtext">Berlangganan</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('manage.addons.*') ? 'active' : '' }}">
                                    <a href="{{ route('manage.addons.index') }}">
                                        <span class="pcoded-micon"><i class="feather icon-package"></i></span>
                                        <span class="pcoded-mtext">Add-ons Premium</span>
                                    </a>
                                </li>
                                @endif
                                @if(function_exists('menuCan') && menuCan('menu.kirim_tagihan'))
                                <li class="{{ request()->routeIs('manage.bulk-whatsapp.*') ? 'active' : '' }}">
                                    <a href="{{ route('manage.bulk-whatsapp.index') }}">
                                        <span class="pcoded-micon"><i class="feather icon-mail"></i></span>
                                        <span class="pcoded-mtext">Kirim Tagihan</span>
                                    </a>
                                </li>
                                @endif
                                @if(function_exists('menuCan') && menuCan('menu.users'))
                                <li class="{{ request()->routeIs('manage.users.*') ? 'active' : '' }}">
                                    <a href="{{ route('manage.users.index') }}">
                                        <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                                        <span class="pcoded-mtext">Pengguna</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('manage.users.role-menu') ? 'active' : '' }}">
                                    <a href="{{ route('manage.users.role-menu') }}">
                                        <span class="pcoded-micon"><i class="feather icon-shield"></i></span>
                                        <span class="pcoded-mtext">Hak Akses Menu</span>
                                    </a>
                                </li>
                                @endif
                                @if(function_exists('menuCan') && menuCan('menu.general_setting'))
                                <li class="{{ request()->routeIs('manage.general.setting') ? 'active' : '' }}">
                                    <a href="{{ route('manage.general.setting') }}">
                                        <span class="pcoded-micon"><i class="feather icon-settings"></i></span>
                                        <span class="pcoded-mtext">General Setting</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                            @endif
                            @endif
                            
                            <!-- Logout Menu -->
                            <div class="pcoded-navigatio-lavel" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;"></div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li>
                                    <form method="POST" action="{{ route('manage.logout') }}" id="logoutForm" style="margin: 0;">
                                        @csrf
                                        <a href="javascript:void(0)" onclick="document.getElementById('logoutForm').submit();" style="color: #dc3545;">
                                            <span class="pcoded-micon"><i class="feather icon-log-out"></i></span>
                                            <span class="pcoded-mtext">Logout</span>
                                        </a>
                                    </form>
                                </li>
                            </ul>
                            
                        </div>
                    </nav>
                    
                    <!-- Content Area -->
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <!-- Subscription Warning Alert -->
                            @if(session('subscription_warning'))
                                @php $warning = session('subscription_warning'); @endphp
                                <div id="subscriptionWarningAlert" class="alert alert-dismissible fade show mb-4 mx-3 mt-3" 
                                     role="alert" 
                                     style="border: 1px solid #01a9ac; border-radius: 12px; background-color: #f0f9ff; padding: 1rem 1.25rem;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 32px; height: 32px; background-color: #01a9ac; flex-shrink: 0;">
                                                <i class="fas fa-info-circle text-white" style="font-size: 0.875rem;"></i>
                                            </div>
                                            <div class="flex-grow-1" style="color: #01a9ac; font-size: 0.9rem; line-height: 1.5;">
                                                {{ $warning['message'] }}
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 ms-3">
                                            <a href="{{ route('manage.subscription.plans') }}" 
                                               class="btn btn-sm" 
                                               style="background: linear-gradient(135deg, #01a9ac 0%, #0ac282 100%); border: none; border-radius: 8px; padding: 0.4rem 1rem; color: white; font-weight: 500; white-space: nowrap;">
                                                {{ $warning['type'] === 'critical' || $warning['type'] === 'warning' || $warning['type'] === 'error' ? 'Perpanjang Sekarang' : 'Lihat Paket' }}
                                            </a>
                                            <button type="button" 
                                                    class="close" 
                                                    onclick="closeSubscriptionAlert()"
                                                    style="opacity: 0.6; font-size: 1.5rem; padding: 0; margin-left: 0.5rem; line-height: 1; color: #01a9ac;"
                                                    aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                function closeSubscriptionAlert() {
                                    const alert = document.getElementById('subscriptionWarningAlert');
                                    if (alert) {
                                        alert.style.transition = 'opacity 0.3s ease-out';
                                        alert.style.opacity = '0';
                                        setTimeout(() => {
                                            alert.remove();
                                        }, 300);
                                    }
                                }
                                </script>
                            @endif
                            
                            <!-- Toast Container -->
                            <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
                            
                            <!-- Main Content -->
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Required Jquery -->
    <script type="text/javascript" src="{{ asset('template-assets/bower_components/jquery/js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('template-assets/bower_components/jquery-ui/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('template-assets/bower_components/popper.js/js/popper.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('template-assets/bower_components/bootstrap/js/bootstrap.min.js') }}"></script>
    
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="{{ asset('template-assets/bower_components/jquery-slimscroll/js/jquery.slimscroll.js') }}"></script>
    
    <!-- modernizr js -->
    <script type="text/javascript" src="{{ asset('template-assets/bower_components/modernizr/js/modernizr.js') }}"></script>
    <script type="text/javascript" src="{{ asset('template-assets/bower_components/modernizr/js/css-scrollbars.js') }}"></script>
    
    <!-- Custom js -->
    <script src="{{ asset('template-assets/assets/js/pcoded.min.js') }}"></script>
    <script src="{{ asset('template-assets/assets/js/menu/menu-sidebar-fixed.js') }}"></script>
    <script src="{{ asset('template-assets/assets/js/jquery.mCustomScrollbar.concat.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('template-assets/assets/js/script.js') }}"></script>
    
    <!-- SweetAlert2 for compatibility -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- PNotify JS -->
    <script src="{{ asset('template-assets/bower_components/pnotify/js/pnotify.js') }}"></script>
    <script src="{{ asset('template-assets/bower_components/pnotify/js/pnotify.buttons.js') }}"></script>
    <script src="{{ asset('template-assets/bower_components/pnotify/js/pnotify.nonblock.js') }}"></script>
    
    <!-- CSRF Token Setup -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    <!-- Full Screen Toggle -->
    <script>
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>
    
    <!-- PNotify Notification System -->
    <script>
        // Function to show PNotify notification (available immediately)
        function showToastInternal(message, type = 'success', title = null) {
            // Check if PNotify is available
            if (typeof PNotify === 'undefined') {
                console.error('PNotify is not loaded');
                // Fallback to alert
                alert((title || 'Notification') + ': ' + message);
                return;
            }
            
            // Map type to PNotify type and addon class
            let pnotifyType = 'success';
            let addonClass = 'pnotify-success';
            let defaultTitle = 'Berhasil!';
            
            if (type === 'success') {
                pnotifyType = 'success';
                addonClass = 'pnotify-success';
                defaultTitle = 'Berhasil!';
            } else if (type === 'error' || type === 'danger') {
                pnotifyType = 'error';
                addonClass = 'pnotify-danger';
                defaultTitle = 'Error!';
            } else if (type === 'warning') {
                pnotifyType = 'notice';
                addonClass = 'pnotify-default';
                defaultTitle = 'Peringatan!';
            } else {
                pnotifyType = 'info';
                addonClass = 'pnotify-default';
                defaultTitle = 'Info!';
            }
            
            // Use provided title or default
            const notificationTitle = title || defaultTitle;
            
            // Show PNotify
            new PNotify({
                title: notificationTitle,
                text: message,
                type: pnotifyType,
                addclass: addonClass,
                styling: 'bootstrap3',
                delay: 5000,
                buttons: {
                    closer: true,
                    sticker: false
                }
            });
        }
        
        // Overload function for compatibility with old calls (available immediately)
        window.showToast = function(param1, param2, param3) {
            // If called with 3 parameters
            if (arguments.length === 3) {
                // Check if first param is type (string) or message
                if (typeof param1 === 'string' && (param1 === 'success' || param1 === 'error' || param1 === 'danger' || param1 === 'warning' || param1 === 'info')) {
                    // Format: (type, title, message)
                    showToastInternal(param3, param1, param2);
                } else {
                    // Format: (message, type, title) - unlikely but handle it
                    showToastInternal(param1, param2, param3);
                }
            } 
            // If called with 2 parameters
            else if (arguments.length === 2) {
                // Check if second param is type
                if (typeof param2 === 'string' && (param2 === 'success' || param2 === 'error' || param2 === 'danger' || param2 === 'warning' || param2 === 'info')) {
                    // Format: (message, type)
                    showToastInternal(param1, param2);
                } else {
                    // Format: (type, title) - unlikely but handle it
                    showToastInternal('', param1, param2);
                }
            }
            // If called with 1 parameter (message)
            else {
                showToastInternal(arguments[0], 'success');
            }
        };
        
        // Wait for DOM ready to show session messages
        $(document).ready(function() {
            // Check for session messages and show PNotify
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
    </script>
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>

