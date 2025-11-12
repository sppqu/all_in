<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - SPPQU</title>
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
            justify-content: center;
            padding: 20px;
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
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .otp-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
            animation: slide 20s linear infinite;
        }

        @keyframes slide {
            0% { transform: translateX(0); }
            100% { transform: translateX(-20px); }
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

        .otp-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .otp-body {
            padding: 50px 40px;
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

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .phone-display {
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
            border-left: 4px solid #28a745;
            animation: slideInDown 0.5s ease-out 0.2s both;
        }

        .phone-display .text-muted {
            font-size: 0.9rem;
            margin-bottom: 8px;
            color: #6c757d !important;
        }

        .phone-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #008060;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            font-size: 1.1rem;
        }

        .otp-input-container {
            position: relative;
            margin-bottom: 20px;
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
        }

        .otp-display {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
        }

        .otp-digit {
            width: 60px;
            height: 60px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            background: white;
            transition: all 0.3s ease;
            position: relative;
        }

        .otp-digit.filled {
            border-color: #008060;
            background: #f8f9fa;
        }

        .otp-digit.filled::after {
            content: attr(data-value);
            color: #008060;
            font-size: 24px;
            font-weight: 700;
        }

        .otp-digit.active {
            border-color: #008060;
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 96, 0.25);
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
            color: #ff8c00;
        }

        .resend-link {
            color: #008060;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border-radius: 10px;
            background: rgba(0, 128, 96, 0.1);
        }

        .resend-link:hover {
            color: #006d52;
            background: rgba(0, 128, 96, 0.15);
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
            color: #008060;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .action-link:hover {
            color: #006d52;
            transform: translateX(-5px);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .container-fluid {
                padding: 10px;
            }
            
            .otp-card {
                max-width: 100%;
            }
        }

        @media (max-width: 576px) {
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
        <!-- OTP Verification Card -->
        <div class="otp-card">
            <div class="otp-header">
                <div class="logo-sppqu">
                    <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Logo SPPQU" onerror="this.style.display='none'">
                </div>
                <h3>Verifikasi OTP</h3>
                <p>Masukkan kode 6 digit yang telah dikirim</p>
            </div>
            
            <div class="otp-body">
                <?php if(session('success')): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="phone-display">
                    <div class="text-muted">Kode OTP dikirim ke:</div>
                    <div class="phone-number">+<?php echo e(session('phone')); ?></div>
                </div>

                <form method="POST" action="<?php echo e(route('otp.verify')); ?>" id="otpForm">
                    <?php echo csrf_field(); ?>
                    
                    <div class="mb-4">
                        <label for="otp" class="form-label">Kode OTP</label>
                        <div class="otp-input-container">
                            <input type="text" 
                                   class="otp-hidden-input <?php $__errorArgs = ['otp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
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
                        
                        <a href="<?php echo e(route('otp.resend')); ?>" 
                           class="resend-link" 
                           id="resendLink">
                            <i class="fas fa-redo"></i>
                            Kirim Ulang OTP
                        </a>
                    </div>

                    <div class="action-links">
                        <a href="<?php echo e(route('otp.request')); ?>" class="action-link">
                            <i class="fas fa-arrow-left"></i>
                            Ganti Nomor HP
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                resendLink.href = "<?php echo e(route('otp.resend')); ?>";
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

        // Add ripple effect to resend link
        document.getElementById('resendLink').addEventListener('click', function(e) {
            if (!this.classList.contains('disabled')) {
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
            }
        });
    </script>

    <style>
    /* Ripple effect */
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(0, 128, 96, 0.3);
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
<?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/auth/otp-verify.blade.php ENDPATH**/ ?>