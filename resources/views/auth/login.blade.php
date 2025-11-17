<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SPPQU - Sistem Pembayaran Peserta Didik">
    <meta name="keywords" content="SPPQU, Pembayaran, Sekolah">
    <meta name="author" content="SPPQU">
    
    <title>Login - {{ config('app.name', 'SPPQU') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    
    <!-- Required Framework - Bootstrap -->
    <link rel="stylesheet" type="text/css" href="{{ asset('template-assets/bower_components/bootstrap/css/bootstrap.min.css') }}">
    
    <!-- Feather Icons -->
    <link rel="stylesheet" type="text/css" href="{{ asset('template-assets/assets/icon/feather/css/feather.css') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('template-assets/assets/css/style.css') }}">
    
    <!-- Custom Login Styles -->
    <style>
        body {
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Open Sans', sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="black" opacity="0.05"/><circle cx="75" cy="75" r="1" fill="black" opacity="0.05"/><circle cx="50" cy="10" r="0.5" fill="black" opacity="0.05"/><circle cx="10" cy="60" r="0.5" fill="black" opacity="0.05"/><circle cx="90" cy="40" r="0.5" fill="black" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
            z-index: 0;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }
        
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1200px;
            padding: 20px;
        }
        
        .login-wrapper {
            display: flex;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            min-height: 600px;
        }
        
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #01a9ac 0%, #008060 100%);
            color: white;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 15s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .login-left-content {
            position: relative;
            z-index: 1;
        }
        
        .login-logo {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: bounceIn 1s ease-out;
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .login-logo img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        
        .login-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            animation: slideInLeft 1s ease-out 0.2s both;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .login-subtitle {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.95;
            line-height: 1.6;
            animation: slideInLeft 1s ease-out 0.4s both;
        }
        
        .login-features {
            list-style: none;
            padding: 0;
            margin: 0;
            animation: slideInLeft 1s ease-out 0.6s both;
        }
        
        .login-features li {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }
        
        .login-features li i {
            margin-right: 15px;
            font-size: 1.3rem;
            color: #ffd700;
            width: 24px;
            text-align: center;
        }
        
        .login-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fff;
        }
        
        .login-card {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .login-card-header {
            text-align: center;
            margin-bottom: 40px;
            animation: slideInRight 1s ease-out 0.3s both;
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .login-card-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #01a9ac;
            margin-bottom: 10px;
        }
        
        .login-card-header p {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
            animation: slideInRight 1s ease-out 0.5s both;
        }
        
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: block;
            font-size: 0.95rem;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 15px 50px 15px 50px;
            font-size: 15px;
            transition: all 0.3s ease;
            height: 50px;
        }
        
        .input-group .form-control:focus {
            border-color: #01a9ac;
            box-shadow: 0 0 0 0.2rem rgba(1, 169, 172, 0.25);
            outline: none;
        }
        
        .input-group-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #01a9ac !important;
            font-size: 18px;
            z-index: 10;
            pointer-events: none;
            transition: none !important;
            opacity: 1 !important;
            visibility: visible !important;
            display: inline-block !important;
        }
        
        .input-group:focus-within .input-group-icon,
        .input-group .form-control:focus + .input-group-icon,
        .input-group .form-control:active + .input-group-icon,
        .input-group .form-control:hover + .input-group-icon,
        .input-group .form-control:valid + .input-group-icon,
        .input-group .form-control:invalid + .input-group-icon {
            color: #01a9ac !important;
            opacity: 1 !important;
            visibility: visible !important;
            display: inline-block !important;
        }
        
        .input-group-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d !important;
            cursor: pointer;
            font-size: 18px;
            z-index: 10;
            transition: color 0.3s ease;
            pointer-events: auto;
        }
        
        .input-group-toggle:hover {
            color: #01a9ac !important;
        }
        
        .input-group .form-control:focus ~ .input-group-toggle {
            color: #6c757d !important;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #01a9ac 0%, #008060 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
            height: 50px;
            position: relative;
            overflow: hidden;
            animation: slideInRight 1s ease-out 0.7s both;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(1, 169, 172, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 25px;
            border: none;
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            animation: slideInRight 1s ease-out 0.9s both;
        }
        
        .login-footer a {
            color: #01a9ac;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .login-footer a:hover {
            color: #008060;
        }
        
        .login-footer a i {
            margin-right: 8px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                padding: 10px;
                max-width: 100%;
            }
            
            .login-wrapper {
                flex-direction: column;
                min-height: auto;
            }
            
            .login-left {
                padding: 40px 30px;
                min-height: auto;
            }
            
            .login-title {
                font-size: 2rem;
            }
            
            .login-subtitle {
                font-size: 1.1rem;
            }
            
            .login-features li {
                font-size: 1rem;
                margin-bottom: 15px;
            }
            
            .login-right {
                padding: 40px 30px;
            }
        }
        
        @media (max-width: 768px) {
            .login-container {
                padding: 5px;
            }
            
            .login-wrapper {
                border-radius: 15px;
            }
            
            .login-left {
                padding: 30px 20px;
            }
            
            .login-logo {
                width: 80px;
                height: 80px;
                margin-bottom: 20px;
            }
            
            .login-logo img {
                width: 60px;
                height: 60px;
            }
            
            .login-title {
                font-size: 1.75rem;
                margin-bottom: 10px;
            }
            
            .login-subtitle {
                font-size: 1rem;
                margin-bottom: 30px;
            }
            
            .login-features {
                margin-top: 20px;
            }
            
            .login-features li {
                font-size: 0.95rem;
                margin-bottom: 12px;
            }
            
            .login-features li i {
                font-size: 1.1rem;
                width: 20px;
            }
            
            .login-right {
                padding: 30px 20px;
            }
            
            .login-card-header h2 {
                font-size: 1.75rem;
            }
            
            .login-card-header p {
                font-size: 0.95rem;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .input-group .form-control {
                padding: 12px 45px 12px 45px;
                font-size: 14px;
                height: 45px;
            }
            
            .input-group-icon {
                left: 15px;
                font-size: 16px;
            }
            
            .btn-login {
                padding: 12px 25px;
                font-size: 15px;
                height: 45px;
            }
        }
        
        @media (max-width: 576px) {
            .login-left {
                padding: 25px 15px;
            }
            
            .login-logo {
                width: 70px;
                height: 70px;
                margin-bottom: 15px;
            }
            
            .login-logo img {
                width: 50px;
                height: 50px;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
            
            .login-subtitle {
                font-size: 0.9rem;
                margin-bottom: 20px;
            }
            
            .login-features li {
                font-size: 0.9rem;
                margin-bottom: 10px;
            }
            
            .login-right {
                padding: 25px 15px;
            }
            
            .login-card-header {
                margin-bottom: 30px;
            }
            
            .login-card-header h2 {
                font-size: 1.5rem;
            }
            
            .login-card-header p {
                font-size: 0.9rem;
            }
        }
        
        /* Loading Spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-wrapper">
            <!-- Left Section - Welcome -->
            <div class="login-left">
                <div class="login-left-content">
                    <div class="login-logo">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo SPPQU" onerror="this.style.display='none'">
                    </div>
                    <h1 class="login-title">Welcome to SPPQU</h1>
                    <p class="login-subtitle">
                        Sistem Pembayaran Peserta Didik yang aman dan terpercaya. 
                        Akses semua fitur manajemen keuangan sekolah Anda dengan mudah.
                    </p>
                    <ul class="login-features">
                        <li>
                            <i class="fas fa-shield-alt"></i>
                            <span>Autentikasi email dan password yang aman</span>
                        </li>
                        <li>
                            <i class="fas fa-bolt"></i>
                            <span>Akses cepat dan andal ke akun Anda</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>Login dengan alamat email terdaftar</span>
                        </li>
                        <li>
                            <i class="fas fa-chart-line"></i>
                            <span>Akses laporan keuangan lengkap</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Right Section - Login Form -->
            <div class="login-right">
                <div class="login-card">
                    <div class="login-card-header">
                        <h2>Login</h2>
                        <p>Masukkan email dan password Anda untuk masuk</p>
                    </div>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0" style="padding-left: 20px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                        @csrf
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-group">
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       placeholder="nama@email.com"
                                       value="{{ old('email') }}"
                                       required
                                       autofocus>
                                <i class="fas fa-envelope input-group-icon"></i>
                            </div>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Masukkan password Anda"
                                       required>
                                <i class="fas fa-lock input-group-icon"></i>
                                <span class="input-group-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                </span>
                            </div>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-login" id="submitBtn">
                                <span class="btn-text">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Masuk
                                </span>
                                <span class="btn-loading" style="display: none;">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Memproses...
                                </span>
                            </button>
                        </div>

                        <div class="login-footer">
                            <a href="{{ route('otp.request') }}">
                                <i class="fas fa-arrow-left"></i>
                                Login dengan WhatsApp OTP
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('template-assets/bower_components/jquery/js/jquery.min.js') }}"></script>
    <!-- Bootstrap JS -->
    <script src="{{ asset('template-assets/bower_components/bootstrap/js/bootstrap.min.js') }}"></script>
    
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form submission with loading animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');
            
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            btn.disabled = true;
        });
    </script>
</body>
</html>
