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
    
    <title>Login OTP - {{ config('app.name', 'SPPQU') }}</title>
    
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
            background: #2d2d2d;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Open Sans', sans-serif;
            position: relative;
            overflow: hidden;
            padding: 20px;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.03"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.03"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.03"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.03"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.03"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
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
            max-width: 450px;
            padding: 20px;
            margin: 0 auto;
        }
        
        .login-wrapper {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            padding: 40px;
        }
        
        .login-card {
            width: 100%;
        }
        
        .login-logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            width: 75px;
            height: 75px;
            background: #f0f0f0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .login-logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }
        
        .login-card-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-card-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        
        .login-card-header p {
            color: #6c757d;
            font-size: 0.95rem;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
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
            padding: 15px 20px 15px 80px;
            font-size: 15px;
            transition: all 0.3s ease;
            height: 50px;
        }
        
        .input-group .form-control:focus {
            border-color: #01a9ac;
            box-shadow: 0 0 0 0.2rem rgba(1, 169, 172, 0.25);
            outline: none;
        }
        
        .input-group-prefix {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #01a9ac !important;
            font-weight: 700;
            font-size: 16px;
            z-index: 10;
            pointer-events: none;
            transition: none !important;
            opacity: 1 !important;
            visibility: visible !important;
            display: inline-block !important;
        }
        
        .input-group:focus-within .input-group-prefix,
        .input-group .form-control:focus ~ .input-group-prefix,
        .input-group .form-control:active ~ .input-group-prefix,
        .input-group .form-control:hover ~ .input-group-prefix,
        .input-group .form-control:valid ~ .input-group-prefix,
        .input-group .form-control:invalid ~ .input-group-prefix {
            color: #01a9ac !important;
            opacity: 1 !important;
            visibility: visible !important;
            display: inline-block !important;
        }
        
        .form-text {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 8px;
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
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
        }
        
        .login-footer a {
            color: #01a9ac;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .login-footer a:hover {
            color: #008060;
        }
        
        .login-footer a i {
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                padding: 15px;
                max-width: 100%;
            }
            
            .login-wrapper {
                padding: 30px 25px;
                border-radius: 12px;
            }
            
            .login-card-header h2 {
                font-size: 1.5rem;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
            
            .input-group .form-control {
                padding: 12px 15px 12px 70px;
                font-size: 14px;
                height: 45px;
            }
            
            .btn-login {
                padding: 12px 25px;
                font-size: 15px;
                height: 45px;
            }
        }
        
        @media (max-width: 576px) {
            .login-wrapper {
                padding: 25px 20px;
            }
            
            .login-card-header h2 {
                font-size: 1.35rem;
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
            <div class="login-card">
                <!-- Logo Section -->
                <div class="login-logo-section">
                    <div class="login-logo">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo SPPQU" onerror="this.style.display='none'">
                    </div>
                </div>
                
                <!-- Login Header -->
                <div class="login-card-header">
                    <h2>Log In</h2>
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

                    <form method="POST" action="{{ route('otp.request') }}" id="otpForm">
                        @csrf
                        
                        <div class="form-group">
                            <label for="phone">Nomor HP</label>
                            <div class="input-group">
                                <span class="input-group-prefix">+62</span>
                                <input type="tel" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       placeholder="8xxxxxxxxxx"
                                       value="{{ old('phone') }}"
                                       maxlength="13"
                                       required>
                            </div>
                            <small class="form-text">
                                Masukkan nomor HP tanpa awalan 0 (contoh: 81234567890)
                            </small>
                            @error('phone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-login" id="submitBtn">
                                <span class="btn-text">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Kirim Kode OTP
                                </span>
                                <span class="btn-loading" style="display: none;">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Memproses...
                                </span>
                            </button>
                        </div>

                        <div class="login-footer">
                            <a href="{{ route('login') }}">
                                <i class="fas fa-envelope"></i>
                                Login dengan Email
                            </a>
                        </div>
                    </form>
                </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('template-assets/bower_components/jquery/js/jquery.min.js') }}"></script>
    <!-- Bootstrap JS -->
    <script src="{{ asset('template-assets/bower_components/bootstrap/js/bootstrap.min.js') }}"></script>
    
    <script>
        // Format input nomor HP
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Hapus angka 0 di depan
            if (value.startsWith('0')) {
                value = value.substring(1);
            }
            
            // Batasi maksimal 13 digit
            if (value.length > 13) {
                value = value.substring(0, 13);
            }
            
            e.target.value = value;
        });

        // Form submission with loading animation
        document.getElementById('otpForm').addEventListener('submit', function(e) {
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
