<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPMB - Sistem Penerimaan Murid Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #ffffff;
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

        .hero-section {
            background: linear-gradient(135deg, rgb(8, 129, 45) 0%, #006d52 100%);
            color: white;
            padding: 60px 0 50px;
            position: relative;
            z-index: 1;
        }

        .welcome-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            animation: slideInLeft 1s ease-out;
            line-height: 1.3;
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
            font-size: 1rem;
            margin-bottom: 25px;
            opacity: 0.9;
            line-height: 1.6;
            animation: slideInLeft 1s ease-out 0.2s both;
        }
        
        @media (min-width: 768px) {
            .hero-section {
                padding: 80px 0 70px;
            }
            
            .welcome-title {
                font-size: 2.8rem;
            }
            
            .welcome-subtitle {
                font-size: 1.1rem;
            }
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            animation: slideInUp 1s ease-out 0.4s both;
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

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }

        .step-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-left: 4px solid #008060;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            animation: slideInUp 1s ease-out 0.6s both;
            transition: transform 0.2s ease;
        }
        
        .step-card:hover {
            transform: translateX(5px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
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
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 128, 96, 0.3);
        }

        .btn-outline-primary {
            border: 2px solid #008060;
            color: #008060;
            background: transparent;
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: #008060;
            border-color: #008060;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 128, 96, 0.3);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 0.75rem 0;
        }

        .navbar-brand {
            color: #008060 !important;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .nav-link {
            color: #008060 !important;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .nav-link:hover {
            color: #006d52 !important;
        }

        .section-bg {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 30px 20px;
            margin: 15px 0;
        }

        .section-bg-green {
            background: linear-gradient(135deg, rgb(8, 129, 45) 0%, #006d52 100%);
            border-radius: 20px;
            padding: 30px 20px;
            margin: 15px 0;
        }
        
        @media (min-width: 768px) {
            .section-bg {
                padding: 40px 30px;
            }
            
            .section-bg-green {
                padding: 40px 30px;
            }
        }

        .footer-bg {
            background: #343a40;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <?php if($schoolProfile && $schoolProfile->logo_sekolah): ?>
                    <img src="<?php echo e(asset('storage/' . $schoolProfile->logo_sekolah)); ?>" alt="<?php echo e($schoolProfile->nama_sekolah); ?> Logo" height="30" class="me-2">
                <?php else: ?>
                    <i class="fas fa-graduation-cap text-primary me-2"></i>
                <?php endif; ?>
                SPMB <?php echo e($schoolProfile->nama_sekolah ?? 'SPPQU'); ?>

            </a>
            <div class="navbar-nav ms-auto">
                <a class="btn btn-primary ms-2" href="<?php echo e(route('spmb.register')); ?>">Daftar Sekarang</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="welcome-title">
                        <span class="welcome-text">Selamat Datang di SPMB</span><br>
                        <span class="brand-text"><?php echo e($schoolProfile->nama_sekolah ?? 'SPPQU'); ?></span>
                    </h1>
                    <p class="welcome-subtitle">Sistem Penerimaan Murid Baru yang mudah, cepat, dan terpercaya. Daftarkan diri Anda sekarang dan bergabunglah dengan keluarga besar sekolah kami.</p>
                    <div class="d-flex gap-3">
                        <a href="<?php echo e(route('spmb.register')); ?>" class="btn btn-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                        </a>
                        <a href="<?php echo e(route('spmb.login')); ?>" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center d-none d-lg-block">
                    <i class="fas fa-graduation-cap" style="font-size: 120px; opacity: 0.25;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-4">
        <div class="container">
            <div class="section-bg">
                <div class="row text-center mb-4">
                    <div class="col-12">
                        <h2 class="fw-bold text-dark" style="font-size: 1.75rem;">Mengapa Memilih SPMB Kami?</h2>
                        <p class="text-muted" style="font-size: 0.95rem;">Proses pendaftaran yang mudah dan terpercaya</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-mobile-alt mb-2" style="font-size: 2.2rem; color: #008060;"></i>
                                <h5 class="card-title" style="font-size: 1.1rem; font-weight: 600;">Mudah & Cepat</h5>
                                <p class="card-text" style="font-size: 0.9rem;">Proses pendaftaran yang mudah dan cepat dengan sistem online yang user-friendly.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-shield-alt mb-2" style="font-size: 2.2rem; color: #008060;"></i>
                                <h5 class="card-title" style="font-size: 1.1rem; font-weight: 600;">Aman & Terpercaya</h5>
                                <p class="card-text" style="font-size: 0.9rem;">Sistem keamanan yang terjamin untuk melindungi data pribadi Anda.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center p-3">
                                <i class="fas fa-credit-card mb-2" style="font-size: 2.2rem; color: #008060;"></i>
                                <h5 class="card-title" style="font-size: 1.1rem; font-weight: 600;">Pembayaran Digital</h5>
                                <p class="card-text" style="font-size: 0.9rem;">Pembayaran yang mudah dengan berbagai metode pembayaran digital.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Steps Section -->
    <section class="py-4">
        <div class="container">
            <div class="section-bg-green">
                <div class="row text-center mb-4">
                    <div class="col-12">
                        <h2 class="fw-bold text-white" style="font-size: 1.75rem;">Langkah-langkah Pendaftaran</h2>
                        <p class="text-white-50" style="font-size: 0.95rem;">Ikuti langkah-langkah berikut untuk menyelesaikan pendaftaran</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="step-card">
                            <div class="d-flex align-items-center">
                                <div class="text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; background: #008060; font-size: 0.9rem;">
                                    <span class="fw-bold">1</span>
                                </div>
                                <div>
                                    <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">Daftar Akun</h6>
                                    <small class="text-muted" style="font-size: 0.8rem;">Buat akun dengan nama dan nomor HP</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="step-card">
                            <div class="d-flex align-items-center">
                                <div class="text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; background: #008060; font-size: 0.9rem;">
                                    <span class="fw-bold">2</span>
                                </div>
                                <div>
                                    <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">Bayar Biaya Pendaftaran</h6>
                                    <small class="text-muted" style="font-size: 0.8rem;">Lakukan pembayaran biaya pendaftaran</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="step-card">
                            <div class="d-flex align-items-center">
                                <div class="text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; background: #008060; font-size: 0.9rem;">
                                    <span class="fw-bold">3</span>
                                </div>
                                <div>
                                    <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">Lengkapi Formulir</h6>
                                    <small class="text-muted" style="font-size: 0.8rem;">Isi data pribadi dan informasi lainnya</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="step-card">
                            <div class="d-flex align-items-center">
                                <div class="text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; background: #008060; font-size: 0.9rem;">
                                    <span class="fw-bold">4</span>
                                </div>
                                <div>
                                    <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">Upload Dokumen</h6>
                                    <small class="text-muted" style="font-size: 0.8rem;">Upload dokumen yang diperlukan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="step-card">
                            <div class="d-flex align-items-center">
                                <div class="text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; background: #008060; font-size: 0.9rem;">
                                    <span class="fw-bold">5</span>
                                </div>
                                <div>
                                    <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">Bayar Biaya SPMB</h6>
                                    <small class="text-muted" style="font-size: 0.8rem;">Lakukan pembayaran biaya SPMB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="step-card">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0" style="font-size: 0.95rem; font-weight: 600;">Selesai</h6>
                                    <small class="text-muted" style="font-size: 0.8rem;">Pendaftaran selesai dan menunggu konfirmasi</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-4">
        <div class="container">
            <div class="section-bg">
                <div class="row">
                    <div class="col-12 text-center">
                        <h2 class="fw-bold mb-3 text-dark" style="font-size: 1.75rem;">Siap Memulai Pendaftaran?</h2>
                        <p class="mb-4 text-muted" style="font-size: 0.95rem;">Bergabunglah dengan ribuan siswa yang telah mempercayai sistem pendaftaran kami</p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <a href="<?php echo e(route('spmb.register')); ?>" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                            </a>
                            <a href="<?php echo e(route('spmb.login')); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-bg text-white py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <h5 class="mb-1" style="font-size: 1rem;">
                        <?php if($schoolProfile && $schoolProfile->logo_sekolah): ?>
                            <img src="<?php echo e(asset('storage/' . $schoolProfile->logo_sekolah)); ?>" alt="<?php echo e($schoolProfile->nama_sekolah); ?> Logo" height="18" class="me-2">
                        <?php else: ?>
                            <i class="fas fa-graduation-cap me-2"></i>
                        <?php endif; ?>
                        SPMB <?php echo e($schoolProfile->nama_sekolah ?? 'SPPQU'); ?>

                    </h5>
                    <p class="mb-0" style="font-size: 0.85rem;">Sistem Penerimaan Murid Baru <?php echo e($schoolProfile->nama_sekolah ?? 'SPPQU'); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0" style="font-size: 0.85rem;">&copy; <?php echo e(date('Y')); ?> SPMB <?php echo e($schoolProfile->nama_sekolah ?? 'SPPQU'); ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?php echo e($whatsappNumber ?? '6281234567890'); ?>?text=Halo,%20saya%20ingin%20bertanya%20tentang%20SPMB" 
       target="_blank" 
       class="whatsapp-float"
       title="Chat dengan Admin (<?php echo e($whatsappNumber ?? '6281234567890'); ?>)">
        <i class="fab fa-whatsapp"></i>
    </a>

    <style>
        .whatsapp-float {
            position: fixed;
            width: 56px;
            height: 56px;
            bottom: 25px;
            right: 25px;
            background-color: #25d366;
            color: #FFF;
            border-radius: 50px;
            text-align: center;
            font-size: 28px;
            box-shadow: 0 4px 16px rgba(37, 211, 102, 0.4);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .whatsapp-float:hover {
            background-color: #128c7e;
            color: #FFF;
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.5);
        }

        .whatsapp-float i {
            margin-top: 0;
        }

        @media screen and (max-width: 768px) {
            .whatsapp-float {
                width: 50px;
                height: 50px;
                font-size: 26px;
                bottom: 20px;
                right: 20px;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/spmb/landing.blade.php ENDPATH**/ ?>