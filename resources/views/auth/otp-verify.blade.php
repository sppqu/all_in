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
    
    <title>Verifikasi OTP - {{ config('app.name', 'SPPQU') }}</title>
    
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
        
        .otp-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            padding: 40px;
        }
        
        .otp-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-sppqu {
            width: 60px;
            height: 60px;
            background: #f0f0f0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .logo-sppqu img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        
        .otp-header h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        
        .otp-header p {
            color: #6c757d;
            font-size: 0.95rem;
            margin: 0;
        }
        
        .otp-body {
            padding: 0;
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 25px;
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
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .phone-display {
            background: #e8f5e8;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
            border-left: 4px solid #28a745;
            animation: slideInDown 0.5s ease-out 0.2s both;
            word-break: break-word;
        }
        
        .phone-display .text-muted {
            font-size: 0.9rem;
            margin-bottom: 8px;
            color: #6c757d !important;
        }
        
        .phone-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #01a9ac;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            display: block;
            font-size: 1rem;
            text-align: center;
        }
        
        .otp-input-container {
            position: relative;
            margin-bottom: 20px;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        
        .otp-hidden-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            z-index: 1;
            cursor: pointer;
            text-align: center;
            font-size: 24px;
            letter-spacing: 20px;
            border: none;
            background: transparent;
        }
        
        .otp-display {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            width: 100%;
        }
        
        .otp-digit {
            width: 60px;
            height: 60px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            background: white;
            transition: all 0.3s ease;
            position: relative;
            flex-shrink: 0;
            min-width: 60px;
        }
        
        .otp-digit.filled {
            border-color: #01a9ac;
            background: #f8f9fa;
        }
        
        .otp-digit.filled::after {
            content: attr(data-value);
            color: #01a9ac;
            font-size: 24px;
            font-weight: 700;
        }
        
        .otp-digit.active {
            border-color: #01a9ac;
            box-shadow: 0 0 0 0.2rem rgba(1, 169, 172, 0.25);
            transform: translateY(-2px);
        }
        
        .form-text {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 8px;
            text-align: center;
        }
        
        .countdown-section {
            text-align: center;
            margin-bottom: 25px;
            animation: slideInDown 0.5s ease-out 0.4s both;
        }
        
        .countdown {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .countdown i {
            color: #ffd700;
        }
        
        .resend-link {
            color: #01a9ac;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border-radius: 10px;
            background: rgba(1, 169, 172, 0.1);
        }
        
        .resend-link:hover {
            color: #008060;
            background: rgba(1, 169, 172, 0.15);
            transform: translateY(-2px);
        }
        
        .resend-link.disabled {
            color: #6c757d;
            background: rgba(108, 117, 125, 0.1);
            pointer-events: none;
        }
        
        .action-links {
            text-align: center;
            animation: slideInDown 0.5s ease-out 0.6s both;
        }
        
        .action-link {
            color: #01a9ac;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            transition: all 0.3s ease;
        }
        
        .action-link:hover {
            color: #008060;
            transform: translateX(-5px);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            body {
                padding: 15px;
            }
            
            .login-container {
                max-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                max-width: 100%;
            }
            
            .otp-card {
                border-radius: 15px;
            }
            
            .otp-body {
                padding: 30px 25px;
            }
            
            .otp-header {
                padding: 30px 25px;
            }
            
            .logo-sppqu {
                width: 80px;
                height: 80px;
                margin-bottom: 20px;
            }
            
            .logo-sppqu img {
                width: 60px;
                height: 60px;
            }
            
            .otp-header h3 {
                font-size: 1.75rem;
            }
            
            .otp-header p {
                font-size: 1rem;
            }
            
            .otp-display {
                gap: 10px;
            }
            
            .otp-digit {
                width: 55px;
                height: 55px;
                font-size: 22px;
                min-width: 55px;
            }
            
            .otp-digit.filled::after {
                font-size: 22px;
            }
            
            .otp-hidden-input {
                font-size: 22px;
                letter-spacing: 18px;
            }
            
            .phone-display {
                padding: 18px;
            }
            
            .phone-number {
                font-size: 1.15rem;
            }
            
            .countdown {
                font-size: 0.95rem;
            }
            
            .resend-link {
                padding: 12px 20px;
                font-size: 0.95rem;
            }
        }
        
        @media (max-width: 768px) {
            .login-container {
                padding: 15px;
                max-width: 100%;
            }
            
            .otp-card {
                padding: 30px 25px;
                border-radius: 12px;
            }
            
            .otp-header h3 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .otp-card {
                padding: 25px 20px;
            }
            
            .logo-sppqu {
                width: 50px;
                height: 50px;
                margin-bottom: 12px;
            }
            
            .logo-sppqu img {
                width: 35px;
                height: 35px;
            }
            
            .otp-header h3 {
                font-size: 1.35rem;
            }
            
            .otp-header p {
                font-size: 0.9rem;
            }
            
            .otp-display {
                gap: 8px;
                justify-content: center;
            }
            
            .otp-digit {
                width: 50px;
                height: 50px;
                font-size: 20px;
                border-radius: 8px;
                min-width: 50px;
            }
            
            .otp-digit.filled::after {
                font-size: 20px;
            }
            
            .otp-hidden-input {
                font-size: 20px;
                letter-spacing: 16px;
            }
            
            .phone-display {
                padding: 15px;
                margin-bottom: 25px;
            }
            
            .phone-display .text-muted {
                font-size: 0.85rem;
            }
            
            .phone-number {
                font-size: 1.1rem;
            }
            
            .form-label {
                font-size: 0.95rem;
                margin-bottom: 15px;
            }
            
            .form-text {
                font-size: 0.85rem;
            }
            
            .countdown-section {
                margin-bottom: 20px;
            }
            
            .countdown {
                font-size: 0.9rem;
                margin-bottom: 12px;
            }
            
            .resend-link {
                padding: 10px 18px;
                font-size: 0.9rem;
            }
            
            .action-link {
                font-size: 0.9rem;
                margin-top: 12px;
            }
            
            .alert {
                padding: 12px 18px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 400px) {
            body {
                padding: 5px;
            }
            
            .otp-body {
                padding: 20px 15px;
            }
            
            .otp-header {
                padding: 20px 15px;
            }
            
            .logo-sppqu {
                width: 60px;
                height: 60px;
                margin-bottom: 12px;
            }
            
            .logo-sppqu img {
                width: 45px;
                height: 45px;
            }
            
            .otp-header h3 {
                font-size: 1.3rem;
            }
            
            .otp-header p {
                font-size: 0.85rem;
            }
            
            .otp-display {
                gap: 6px;
            }
            
            .otp-digit {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
            
            .otp-digit.filled::after {
                font-size: 18px;
            }
            
            .otp-hidden-input {
                font-size: 18px;
                letter-spacing: 15px;
            }
            
            .phone-display {
                padding: 12px;
            }
            
            .phone-number {
                font-size: 1rem;
            }
            
            .countdown {
                font-size: 0.85rem;
            }
            
            .resend-link {
                padding: 8px 15px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="otp-card">
            <div class="otp-header">
                <div class="logo-sppqu">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SPPQU" onerror="this.style.display='none'">
                </div>
                <h3>Verifikasi OTP</h3>
                <p>Masukkan kode 6 digit yang telah dikirim</p>
            </div>
            
            <div class="otp-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0" style="padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="phone-display">
                    <div class="text-muted">Kode OTP dikirim ke:</div>
                    <div class="phone-number">+{{ session('phone') }}</div>
                </div>

                <form method="POST" action="{{ route('otp.verify') }}" id="otpForm">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="otp" class="form-label">Kode OTP</label>
                        <div class="otp-input-container">
                            <input type="text" 
                                   class="otp-hidden-input @error('otp') is-invalid @enderror" 
                                   id="otp" 
                                   name="otp" 
                                   maxlength="6"
                                   required
                                   autocomplete="off">
                            <div class="otp-display">
                                <div class="otp-digit" data-index="0"></div>
                                <div class="otp-digit" data-index="1"></div>
                                <div class="otp-digit" data-index="2"></div>
                                <div class="otp-digit" data-index="3"></div>
                                <div class="otp-digit" data-index="4"></div>
                                <div class="otp-digit" data-index="5"></div>
                            </div>
                        </div>
                        <div class="form-text">
                            Masukkan 6 digit kode OTP
                        </div>
                    </div>

                    <div class="countdown-section">
                        <div class="countdown">
                            <i class="fas fa-clock"></i>
                            <span id="countdown">05:00</span>
                        </div>
                        
                        <a href="{{ route('otp.resend') }}" 
                           class="resend-link" 
                           id="resendLink">
                            <i class="fas fa-redo"></i>
                            Kirim Ulang OTP
                        </a>
                    </div>

                    <div class="action-links">
                        <a href="{{ route('otp.request') }}" class="action-link">
                            <i class="fas fa-arrow-left"></i>
                            Ganti Nomor HP
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
        // Auto-focus ke input OTP
        document.getElementById('otp').focus();

        // OTP Input dengan kotak terpisah
        const otpInput = document.getElementById('otp');
        const otpDigits = document.querySelectorAll('.otp-digit');

        otpInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Hanya terima angka, maksimal 6 digit
            if (value.length > 6) {
                value = value.substring(0, 6);
            }
            
            // Update tampilan kotak
            otpDigits.forEach((digit, index) => {
                if (index < value.length) {
                    digit.classList.add('filled');
                    digit.setAttribute('data-value', value[index]);
                } else {
                    digit.classList.remove('filled');
                    digit.removeAttribute('data-value');
                }
                
                // Highlight kotak aktif
                if (index === value.length) {
                    digit.classList.add('active');
                } else {
                    digit.classList.remove('active');
                }
            });
            
            // Auto-submit setelah 6 digit
            if (value.length === 6) {
                setTimeout(() => {
                    document.getElementById('otpForm').submit();
                }, 500);
            }
        });

        // Focus management
        otpInput.addEventListener('focus', function() {
            const currentLength = this.value.replace(/\D/g, '').length;
            if (currentLength < 6) {
                otpDigits[currentLength].classList.add('active');
            }
        });

        otpInput.addEventListener('blur', function() {
            otpDigits.forEach(digit => digit.classList.remove('active'));
        });

        // Click pada kotak untuk focus
        otpDigits.forEach((digit, index) => {
            digit.addEventListener('click', () => {
                otpInput.focus();
            });
        });

        // Countdown timer
        let timeLeft = 300; // 5 menit dalam detik
        const countdownElement = document.getElementById('countdown');
        const resendLink = document.getElementById('resendLink');

        function updateCountdown() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            countdownElement.textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 0) {
                countdownElement.textContent = '00:00';
                resendLink.classList.remove('disabled');
                resendLink.href = "{{ route('otp.resend') }}";
                return;
            }

            timeLeft--;
            setTimeout(updateCountdown, 1000);
        }

        // Mulai countdown
        updateCountdown();

        // Disable resend link selama countdown
        resendLink.classList.add('disabled');
        resendLink.href = "#";
    </script>
</body>
</html>
