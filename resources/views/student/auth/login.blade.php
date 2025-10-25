@php
    $profile = \App\Models\SchoolProfile::first();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa - {{ $profile->school_name ?? 'SPPQU' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: #ffffff;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: #fff;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            position: relative;
        }
        
        .login-header {
            background: #ffffff;
            color: #008060;
            text-align: center;
            padding: 40px 30px 30px;
            position: relative;
            border-bottom: 2px solid #e9ecef;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23008060" opacity="0.05"/><circle cx="75" cy="75" r="1" fill="%23008060" opacity="0.05"/><circle cx="50" cy="10" r="0.5" fill="%23008060" opacity="0.05"/><circle cx="10" cy="60" r="0.5" fill="%23008060" opacity="0.05"/><circle cx="90" cy="40" r="0.5" fill="%23008060" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        
        .login-logo {
            position: relative;
            z-index: 1;
        }
        
        .login-logo .logo-circle {
            width: 80px;
            height: 80px;
            background: transparent;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: none;
        }
        
        .login-logo .logo-circle img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .login-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        
        .login-subtitle {
            font-size: 0.95rem;
            opacity: 0.8;
            color: #006d52;
            position: relative;
            z-index: 1;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 8px;
            color: #008060;
            width: 16px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: #008060;
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 96, 0.15);
            background: white;
        }
        
        .login-btn {
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 15px 0;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .login-btn:hover::before {
            left: 100%;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 128, 96, 0.3);
        }
        
        .login-footer {
            text-align: center;
            padding: 20px 30px 30px;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
        }
        
        .login-footer p {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 15px;
        }
        

        
        .alert {
            border-radius: 15px;
            border: none;
            margin-bottom: 25px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .invalid-feedback {
            font-size: 0.85rem;
            color: #dc3545;
            margin-top: 5px;
        }
        
        /* Mobile optimizations */
        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
            }
            
            .login-card {
                border-radius: 20px;
            }
            
            .login-header {
                padding: 30px 20px 25px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .login-footer {
                padding: 15px 20px 25px;
            }
            
            .login-title {
                font-size: 1.6rem;
            }
            
            .form-control {
                padding: 12px 16px;
                font-size: 0.95rem;
            }
            
            .login-btn {
                padding: 12px 0;
                font-size: 1rem;
            }
        }
        
        /* Password Toggle Button */
        .password-toggle {
            background: none;
            border: none;
            color: #6c757d;
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: #198754;
        }
        
        .password-toggle:focus {
            box-shadow: none;
            outline: none;
        }
        
        .password-toggle .fa-eye-slash {
            color: #198754;
        }
        
        /* Ensure input padding doesn't overlap with button */
        .position-relative .form-control {
            padding-right: 45px;
        }
        
        /* Animation */
        .login-card {
            animation: slideInUp 0.6s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header Section -->
            <div class="login-header">
                <div class="login-logo">
                    <div class="logo-circle">
                        <img src="{{ asset('images/logo.png') }}" alt="SPPQU Logo">
                    </div>
                    <h2 class="login-title">{{ $profile->nama_sekolah ?? 'SPPQU' }}</h2>
                    <p class="login-subtitle">Portal Pembayaran Siswa</p>
                </div>
                

            </div>

            <!-- Body Section -->
            <div class="login-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('student.login') }}">
                    @csrf
                    <div class="form-group">
                        <label for="nis" class="form-label">
                            <i class="fas fa-id-card"></i>Nomor Induk Siswa (NIS)
                        </label>
                        <input type="text" 
                               class="form-control @error('nis') is-invalid @enderror" 
                               id="nis" 
                               name="nis" 
                               value="{{ old('nis') }}"
                               placeholder="Masukkan NIS Anda"
                               required>
                        @error('nis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>Password
                        </label>
                        <div class="position-relative">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Masukkan password Anda"
                                   required>
                            <button type="button" class="btn btn-link position-absolute end-0 top-0 h-100 d-flex align-items-center pe-3 password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye text-muted"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk 
                    </button>
                </form>
            </div>

            <!-- Footer Section -->
            <div class="login-footer">
                <p>
                    <i class="fas fa-info-circle me-1"></i>
                    Hubungi admin sekolah jika lupa password
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.classList.remove('fa-eye');
                toggleButton.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleButton.classList.remove('fa-eye-slash');
                toggleButton.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html> 