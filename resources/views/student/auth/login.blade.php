@php
    $profile = currentSchool() ?? \App\Models\School::first();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Login Siswa - {{ $profile->school_name ?? 'SPPQU' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f5f5;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            position: relative;
            overflow-x: hidden;
            padding: 0;
            margin: 0;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(1, 169, 172, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 128, 96, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(0, 212, 170, 0.08) 0%, transparent 50%);
            animation: gradientShift 15s ease infinite;
            z-index: 0;
        }

        @keyframes gradientShift {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }

        /* Floating Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite;
        }

        .particle:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            width: 120px;
            height: 120px;
            left: 70%;
            animation-delay: 2s;
        }

        .particle:nth-child(3) {
            width: 60px;
            height: 60px;
            left: 40%;
            animation-delay: 4s;
        }

        .particle:nth-child(4) {
            width: 100px;
            height: 100px;
            left: 80%;
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 0.3;
            }
            25% {
                transform: translateY(-100px) translateX(50px) rotate(90deg);
                opacity: 0.6;
            }
            50% {
                transform: translateY(-200px) translateX(-30px) rotate(180deg);
                opacity: 0.4;
            }
            75% {
                transform: translateY(-100px) translateX(30px) rotate(270deg);
                opacity: 0.5;
            }
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 2;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            position: relative;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #01a9ac 0%, #008060 100%);
            padding: 50px 30px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .logo-wrapper {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }

        .logo-circle {
            width: 100px;
            height: 100px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border: 3px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .logo-circle img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        .login-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .login-subtitle {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
            font-weight: 400;
        }

        .login-body {
            padding: 40px 30px;
            background: white;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #01a9ac;
            font-size: 1rem;
            width: 20px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 16px 20px 16px 50px;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8f9fa;
            color: #333;
            width: 100%;
        }

        .form-control:focus {
            border-color: #01a9ac;
            box-shadow: 0 0 0 4px rgba(1, 169, 172, 0.1);
            background: white;
            outline: none;
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #01a9ac;
            font-size: 1.1rem;
            z-index: 2;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .form-control:focus ~ .input-icon {
            color: #008060;
            transform: translateY(-50%) scale(1.1);
        }

        .password-toggle-btn {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            font-size: 1.1rem;
            cursor: pointer;
            z-index: 2;
            padding: 5px;
            transition: all 0.3s ease;
        }

        .password-toggle-btn:hover {
            color: #01a9ac;
            transform: translateY(-50%) scale(1.1);
        }

        .login-btn {
            background: linear-gradient(135deg, #01a9ac 0%, #008060 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 16px 0;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(1, 169, 172, 0.3);
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(1, 169, 172, 0.4);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .login-btn i {
            margin-right: 8px;
        }

        .login-footer {
            text-align: center;
            padding: 25px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .login-footer p {
            color: #6c757d;
            font-size: 0.85rem;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .login-footer i {
            color: #01a9ac;
        }

        .alert {
            border-radius: 15px;
            border: none;
            margin-bottom: 25px;
            padding: 15px 20px;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee 0%, #fdd 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .invalid-feedback {
            font-size: 0.85rem;
            color: #dc3545;
            margin-top: 8px;
            display: block;
        }

        /* Mobile Optimizations */
        @media (max-width: 576px) {
            body {
                background: #f5f5f5;
            }

            .login-container {
                padding: 15px;
                align-items: flex-start;
                padding-top: 40px;
            }

            .login-card {
                border-radius: 25px;
                max-width: 100%;
                margin-top: 20px;
            }

            .login-header {
                padding: 40px 25px 35px;
            }

            .logo-circle {
                width: 90px;
                height: 90px;
            }

            .logo-circle img {
                width: 60px;
                height: 60px;
            }

            .login-title {
                font-size: 1.6rem;
            }

            .login-subtitle {
                font-size: 0.9rem;
            }

            .login-body {
                padding: 35px 25px;
            }

            .form-control {
                padding: 14px 18px 14px 48px;
                font-size: 16px; /* Prevent zoom on iOS */
            }

            .input-icon {
                left: 16px;
                font-size: 1rem;
            }

            .login-btn {
                padding: 14px 0;
                font-size: 1rem;
            }

            .login-footer {
                padding: 20px 25px;
            }
        }

        @media (max-width: 400px) {
            .login-header {
                padding: 35px 20px 30px;
            }

            .login-body {
                padding: 30px 20px;
            }

            .logo-circle {
                width: 80px;
                height: 80px;
            }

            .logo-circle img {
                width: 55px;
                height: 55px;
            }

            .login-title {
                font-size: 1.4rem;
            }
        }

        /* Loading State */
        .login-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .login-btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Smooth transitions */
        * {
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body>
    <!-- Floating Particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Header Section -->
            <div class="login-header">
                <div class="logo-wrapper">
                    <div class="logo-circle">
                        <img src="{{ asset('images/logo.png') }}" alt="SPPQU Logo" onerror="this.style.display='none'">
                    </div>
                    <h2 class="login-title">{{ $profile->school_name ?? $profile->nama_sekolah ?? 'SPPQU' }}</h2>
                    <p class="login-subtitle">Portal Pembayaran Siswa</p>
                </div>
            </div>

            <!-- Body Section -->
            <div class="login-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0" style="padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('student.login') }}" id="loginForm">
                    @csrf
                    <div class="form-group">
                        <label for="nis" class="form-label">
                            <i class="fas fa-id-card"></i>
                            <span>Nomor Induk Siswa (NIS)</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   class="form-control @error('nis') is-invalid @enderror" 
                                   id="nis" 
                                   name="nis" 
                                   value="{{ old('nis') }}"
                                   placeholder="Masukkan NIS Anda"
                                   required
                                   autocomplete="username"
                                   autofocus>
                            <i class="fas fa-id-card input-icon"></i>
                        </div>
                        @error('nis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            <span>Password</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Masukkan password Anda"
                                   required
                                   autocomplete="current-password">
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="password-toggle-btn" onclick="togglePassword()" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" id="passwordToggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="login-btn" id="loginBtn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="btn-text">Masuk</span>
                    </button>
                </form>
            </div>

            <!-- Footer Section -->
            <div class="login-footer">
                <p>
                    <i class="fas fa-info-circle"></i>
                    <span>Hubungi admin sekolah jika lupa password</span>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
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

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const btnText = btn.querySelector('.btn-text');
            
            btn.classList.add('loading');
            btnText.style.opacity = '0';
            btn.disabled = true;
        });

        // Auto-focus on NIS input
        document.getElementById('nis').focus();

        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>
