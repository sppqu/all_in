<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login OTP - SPPQU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .animated-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }

        /* Floating Elements */
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .floating-element {
            position: absolute;
            background: rgba(255, 140, 0, 0.15);
            border-radius: 50%;
            animation: floatElement 15s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 15%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 70%;
            left: 10%;
            animation-delay: 3s;
        }

        .floating-element:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 15%;
            left: 25%;
            animation-delay: 6s;
        }

        @keyframes floatElement {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
            50% { transform: translateY(-40px) rotate(180deg); opacity: 0.6; }
        }

        .container-fluid {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }

        .left-section {
            flex: 1;
            color: white;
            padding: 40px;
            position: relative;
            z-index: 1;
        }

        .welcome-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            animation: slideInLeft 1s ease-out;
        }
        
        .welcome-title .welcome-text {
            color: white;
        }
        
        .welcome-title .brand-text {
            color: #ff8c00;
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

        .welcome-subtitle {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
            animation: slideInLeft 1s ease-out 0.2s both;
        }

        .features-list {
            list-style: none;
            animation: slideInLeft 1s ease-out 0.4s both;
        }

        .features-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }

        .features-list li i {
            margin-right: 15px;
            font-size: 1.2rem;
            color: #ff8c00;
        }

        .otp-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
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

        .otp-header {
            background: white;
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }



        .logo-sppqu {
            width: 90px;
            height: 90px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
            animation: bounceIn 1s ease-out 0.5s both;
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

        .logo-sppqu img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .otp-header h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .otp-header h3 .login-text {
            color: #008060;
        }
        
        .otp-header h3 .brand-text {
            color: #ff8c00;
        }

        .otp-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
            color: #008060;
        }

        .otp-body {
            padding: 50px 40px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            font-size: 1.1rem;
        }

        .phone-input {
            position: relative;
            margin-bottom: 20px;
        }

        .phone-input .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 18px 20px 18px 60px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .phone-input .form-control:focus {
            border-color: #008060;
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 96, 0.25);
            transform: translateY(-2px);
        }

        .phone-prefix {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #008060;
            font-weight: 700;
            font-size: 16px;
            z-index: 2;
        }

        .form-text {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            border: none;
            border-radius: 15px;
            padding: 18px 30px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 128, 96, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .back-link {
            color: #008060;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #006d52;
            transform: translateX(-5px);
        }

        .back-link i {
            margin-right: 8px;
        }

        .alert {
            border: none;
            border-radius: 15px;
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

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }

        /* Loading Animation */
        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .container-fluid {
                flex-direction: column;
            }
            
            .left-section {
                text-align: center;
                padding: 20px;
            }
            
            .welcome-title {
                font-size: 2.5rem;
            }
            
            .otp-card {
                margin-top: 30px;
            }
        }

        @media (max-width: 576px) {
            .welcome-title {
                font-size: 2rem;
            }
            
            .welcome-subtitle {
                font-size: 1.1rem;
            }
            
            .otp-body {
                padding: 30px 25px;
            }
            
            .otp-header {
                padding: 30px 25px;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #006d52 0%, #005a45 100%);
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-bg"></div>

    <!-- Floating Elements -->
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <div class="container-fluid">
        <!-- Left Section - Welcome Content -->
        <div class="left-section">
            <h1 class="welcome-title">
                <span class="welcome-text">Welcome to </span>
                <span class="brand-text">SPPQU</span>
            </h1>
            <p class="welcome-subtitle">
                Experience secure and convenient login with WhatsApp OTP. 
                Get instant access to your school's financial management system.
            </p>
            
            <ul class="features-list">
                <li>
                    <i class="fas fa-shield-alt"></i>
                    Secure OTP authentication via WhatsApp
                </li>
                <li>
                    <i class="fas fa-bolt"></i>
                    Instant login without remembering passwords
                </li>
                <li>
                    <i class="fas fa-mobile-alt"></i>
                    Works on any device with WhatsApp
                </li>
                <li>
                    <i class="fas fa-chart-line"></i>
                    Access comprehensive financial reports
                </li>
            </ul>
        </div>

        <!-- Right Section - OTP Card -->
        <div class="otp-card">
            <div class="otp-header">
                <div class="logo-sppqu">
                    <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Logo SPPQU" onerror="this.style.display='none'">
                </div>
                <h3>
                    <span class="login-text">Login </span>
                    <span class="brand-text">SPPQU</span>
                </h3>
                <p>Masukkan nomor HP Anda untuk menerima kode OTP</p>
            </div>
            
            <div class="otp-body">
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('otp.request')); ?>" id="otpForm">
                    <?php echo csrf_field(); ?>
                    
                    <div class="mb-4">
                        <label for="phone" class="form-label">Nomor HP</label>
                        <div class="phone-input">
                            <span class="phone-prefix">+62</span>
                            <input type="tel" 
                                   class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="8xxxxxxxxxx"
                                   value="<?php echo e(old('phone')); ?>"
                                   maxlength="13"
                                   required>
                        </div>
                        <div class="form-text">
                            Masukkan nomor HP tanpa awalan 0 (contoh: 81234567890)
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="btn-text">
                                <i class="fas fa-paper-plane me-2"></i>
                                Kirim Kode OTP
                            </span>
                            <div class="loading">
                                <div class="spinner"></div>
                            </div>
                        </button>
                    </div>

                    <div class="text-center">
                        <a href="<?php echo e(route('login')); ?>" class="back-link">
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke Login Email
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            const loading = btn.querySelector('.loading');
            
            btnText.style.display = 'none';
            loading.style.display = 'block';
            btn.disabled = true;
        });

        // Add ripple effect to button
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    </script>

    <style>
    /* Ripple effect */
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }

    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    </style>
</body>
</html>
<?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/auth/otp-request.blade.php ENDPATH**/ ?>