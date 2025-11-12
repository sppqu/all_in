<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SPMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8f9fa;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
        }

        .navbar {
            background: #ffffff !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid #e9ecef;
            padding: 0.75rem 0;
        }

        .navbar-brand {
            color: #008060 !important;
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                font-size: 13px;
            }

            .navbar-brand {
                font-size: 1rem;
            }

            .container {
                padding-left: 12px;
                padding-right: 12px;
            }
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .card-text {
            font-size: 0.9rem;
        }

        /* Card Mobile Responsive */
        @media (max-width: 768px) {
            .card {
                border-radius: 10px;
                margin-bottom: 1rem;
            }

            .card-title {
                font-size: 1.1rem;
            }

            .card-text {
                font-size: 0.85rem;
            }

            .card-body {
                padding: 1rem;
            }
        }

        /* Stat Cards */
        .stat-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* Stat Cards Mobile Responsive */
        @media (max-width: 768px) {
            .stat-card {
                padding: 0.85rem;
                gap: 0.6rem;
            }

            .stat-icon {
                width: 36px;
                height: 36px;
                font-size: 0.9rem;
            }

            .stat-value {
                font-size: 1rem;
            }

            .stat-label {
                font-size: 0.7rem;
            }
        }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.15rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 128, 96, 0.3);
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
        }

        .badge {
            border-radius: 6px;
            padding: 0.375rem 0.75rem;
            font-weight: 600;
            font-size: 0.75rem;
        }

        /* Step Detail Cards */
        .step-detail-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border-left: 3px solid #e9ecef;
        }

        .step-detail-card.completed {
            border-left-color: #28a745;
        }

        .step-detail-card.current {
            border-left-color: #008060;
            background: linear-gradient(to right, rgba(0, 128, 96, 0.03), #ffffff);
        }

        .step-detail-card:hover {
            transform: translateX(3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .step-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .step-number-badge {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            background: #e9ecef;
            color: #6c757d;
        }

        .step-number-badge.completed {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .step-number-badge.current {
            background: linear-gradient(135deg, #008060, #006d52);
            color: white;
        }

        /* Step 2 (Payment) - Green Theme */
        .step-detail-card[data-step="2"] {
            border-left: 4px solid #28a745 !important;
            background: linear-gradient(to right, rgba(40, 167, 69, 0.05), #ffffff);
        }

        .step-detail-card[data-step="2"] .step-number-badge {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
            color: white !important;
        }

        .step-detail-card[data-step="2"] .btn-primary {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
            border: none !important;
        }

        .step-detail-card[data-step="2"] .btn-primary:hover {
            background: linear-gradient(135deg, #20c997, #28a745) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        /* Step Cards Mobile Responsive */
        @media (max-width: 768px) {
            .step-detail-card {
                padding: 1rem;
                margin-bottom: 0.75rem;
            }

            .step-header {
                gap: 0.6rem;
                margin-bottom: 0.6rem;
            }

            .step-number-badge {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
            }

            .step-title {
                font-size: 0.95rem !important;
            }

            .step-description {
                font-size: 0.75rem !important;
            }

            .btn {
                font-size: 0.85rem;
                padding: 0.5rem 1rem;
            }

            .btn-sm {
                font-size: 0.75rem;
                padding: 0.4rem 0.8rem;
            }
        }

        .step-title {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .step-description {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.75rem;
        }

        .alert {
            border-radius: 8px;
            font-size: 0.85rem;
            padding: 0.75rem 1rem;
        }

        /* Profile Dropdown */
        .profile-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
        }

        .profile-name {
            color: #008060;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .dropdown-menu {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            padding: 0.5rem 0;
            margin-top: 0.5rem;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: rgba(0, 128, 96, 0.08);
            color: #008060;
        }

        .dropdown-item.bg-danger:hover {
            background: #dc3545 !important;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .stepper-container {
                flex-wrap: wrap;
            }

            .stepper-line {
                display: none;
            }

            .step-label {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <?php if($schoolProfile && $schoolProfile->logo_sekolah): ?>
                    <img src="<?php echo e(asset('storage/' . $schoolProfile->logo_sekolah)); ?>" alt="<?php echo e($schoolProfile->nama_sekolah); ?> Logo" height="28" class="me-2">
                <?php else: ?>
                    <i class="fas fa-graduation-cap me-2"></i>
                <?php endif; ?>
                SPMB <?php echo e($schoolProfile->nama_sekolah ?? 'SPPQU'); ?>

            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="profile-avatar me-2">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="profile-name"><?php echo e(session('spmb_name')); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li>
                            <div class="dropdown-header">
                                <div class="fw-bold" style="font-size: 0.9rem;"><?php echo e(session('spmb_name')); ?></div>
                                <small class="text-muted"><?php echo e(session('spmb_phone')); ?></small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user me-2"></i>Profil Saya
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('spmb.step', ['step' => 3])); ?>">
                                <i class="fas fa-edit me-2"></i>Edit Formulir
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('spmb.step', ['step' => 4])); ?>">
                                <i class="fas fa-upload me-2"></i>Edit Upload
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="<?php echo e(route('spmb.logout')); ?>" class="d-inline w-100">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="dropdown-item text-white bg-danger" style="font-weight: 600;">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Alert Messages -->
        <?php if(session('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo e(session('warning')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-2">
                            <i class="fas fa-tachometer-alt text-primary me-2"></i>
                            Dashboard SPMB
                        </h5>
                        <p class="card-text text-muted mb-3">
                            Selamat datang di SPMB <strong><?php echo e($schoolProfile->nama_sekolah ?? 'SPPQU'); ?></strong>, <strong><?php echo e($registration->name); ?></strong>! 
                            Ikuti langkah-langkah berikut untuk menyelesaikan pendaftaran Anda.
                        </p>
                        
                        <!-- Stats Row -->
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <div class="stat-card">
                                    <div class="stat-icon" style="background: #008060;">
                                        <i class="fas fa-route"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-value"><?php echo e($registration->step); ?> / 6</div>
                                        <div class="stat-label">Progress</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="stat-card">
                                    <?php
                                        $statusClass = 'background: #ffc107;'; // Default warning untuk pending
                                        $statusIcon = 'fas fa-clock';
                                        $statusText = 'Pending';
                                        
                                        if ($registration->status_pendaftaran === 'diterima') {
                                            $statusClass = 'background: #28a745;';
                                            $statusIcon = 'fas fa-check-circle';
                                            $statusText = 'Diterima';
                                        } elseif ($registration->status_pendaftaran === 'ditolak') {
                                            $statusClass = 'background: #dc3545;';
                                            $statusIcon = 'fas fa-times-circle';
                                            $statusText = 'Ditolak';
                                        }
                                    ?>
                                    <div class="stat-icon" style="<?php echo e($statusClass); ?>">
                                        <i class="<?php echo e($statusIcon); ?>"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-value"><?php echo e($statusText); ?></div>
                                        <div class="stat-label">Status</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="stat-card">
                                    <div class="stat-icon" style="background: #17a2b8;">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-value">
                                            <?php
                                                $registrationPayment = $registration->payments()->where('type', 'registration_fee')->first();
                                                if ($registrationPayment) {
                                                    if ($registrationPayment->status === 'paid') {
                                                        echo 'Lunas';
                                                    } elseif ($registrationPayment->status === 'skipped') {
                                                        echo 'Di-skip';
                                                    } elseif ($registrationPayment->status === 'pending') {
                                                        echo 'Pending';
                                                    } elseif ($registrationPayment->status === 'failed') {
                                                        echo 'Ditolak';
                                                    } else {
                                                        echo 'Belum';
                                                    }
                                                } else {
                                                    echo 'Belum';
                                                }
                                            ?>
                                        </div>
                                        <div class="stat-label">Biaya Daftar</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="stat-card">
                                    <div class="stat-icon" style="background: #ffc107;">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-value">
                                            <?php
                                                $spmbPayment = $registration->payments()->where('type', 'spmb_fee')->first();
                                                if ($spmbPayment) {
                                                    if ($spmbPayment->status === 'paid') {
                                                        echo 'Lunas';
                                                    } elseif ($spmbPayment->status === 'skipped') {
                                                        echo 'Di-skip';
                                                    } elseif ($spmbPayment->status === 'pending') {
                                                        echo 'Pending';
                                                    } elseif ($spmbPayment->status === 'failed') {
                                                        echo 'Ditolak';
                                                    } else {
                                                        echo 'Belum';
                                                    }
                                                } else {
                                                    echo 'Belum';
                                                }
                                            ?>
                                        </div>
                                        <div class="stat-label">Biaya SPMB</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($registration->nomor_pendaftaran): ?>
                        <div class="alert alert-info mb-0 text-center">
                            <i class="fas fa-id-card me-2"></i>
                            <strong>Nomor Pendaftaran:</strong>
                            <span class="badge bg-primary ms-2"><?php echo e($registration->nomor_pendaftaran); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Pendaftaran dengan Progress Bar -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="mb-0" style="font-size: 1rem; font-weight: 600; color: #2c3e50;">
                                <i class="fas fa-tasks me-2 text-primary"></i>Progress Pendaftaran
                            </h6>
                        </div>
                        
                        <?php
                            $progressPercentage = round((($registration->step - 1) / 5) * 100);
                            if ($registration->step >= 6) {
                                $progressPercentage = 100;
                            }
                        ?>
                        
                        <div class="progress-info mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span style="font-size: 0.85rem; color: #6c757d;">
                                    Langkah <?php echo e($registration->step); ?> dari 6
                                </span>
                                <span style="font-size: 0.9rem; font-weight: 600; color: #008060;">
                                    <?php echo e($progressPercentage); ?>%
                                </span>
                            </div>
                        </div>
                        
                        <div class="progress" style="height: 28px; border-radius: 14px; background-color: #e9ecef;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?php echo e($progressPercentage); ?>%; background: linear-gradient(90deg, #008060, #28a745); font-size: 0.85rem; font-weight: 600;"
                                 aria-valuenow="<?php echo e($progressPercentage); ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo e($progressPercentage); ?>% Selesai
                            </div>
                        </div>
                        
                        <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                            <?php for($i = 1; $i <= 6; $i++): ?>
                                <div class="text-center" style="flex: 1; min-width: 60px;">
                                    <div class="mb-1">
                                        <?php if($registration->step > $i): ?>
                                            <i class="fas fa-check-circle" style="color: #28a745; font-size: 1.3rem;"></i>
                                        <?php elseif($registration->step == $i): ?>
                                            <i class="fas fa-dot-circle" style="color: #008060; font-size: 1.3rem;"></i>
                                        <?php else: ?>
                                            <i class="far fa-circle" style="color: #dee2e6; font-size: 1.3rem;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <small style="font-size: 0.7rem; color: <?php echo e($registration->step >= $i ? '#2c3e50' : '#adb5bd'); ?>; display: block; font-weight: <?php echo e($registration->step == $i ? '600' : '400'); ?>;">
                                        <?php if($i == 1): ?> Daftar
                                        <?php elseif($i == 2): ?> Bayar
                                        <?php elseif($i == 3): ?> Formulir
                                        <?php elseif($i == 4): ?> Dokumen
                                        <?php elseif($i == 5): ?> Biaya SPMB
                                        <?php else: ?> Selesai
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step Details -->
        <div class="row mb-3">
            <div class="col-12">
                <h6 class="mb-3" style="font-size: 0.95rem; font-weight: 600; color: #2c3e50;">
                    Detail Langkah Pendaftaran
                </h6>
            </div>
        </div>

        <div class="row g-3">
            <!-- Step 1 -->
            <div class="col-md-6 col-lg-4">
                <div class="step-detail-card <?php echo e($registration->step > 1 ? 'completed' : ($registration->step == 1 ? 'current' : '')); ?>" data-step="1">
                    <div class="step-header">
                        <div class="step-number-badge <?php echo e($registration->step > 1 ? 'completed' : ($registration->step == 1 ? 'current' : '')); ?>">
                            <?php if($registration->step > 1): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                1
                            <?php endif; ?>
                        </div>
                        <h6 class="step-title">Pendaftaran</h6>
                    </div>
                    <p class="step-description">Buat akun dengan nama dan nomor HP</p>
                    <?php if($registration->step > 1): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Selesai
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="col-md-6 col-lg-4">
                <div class="step-detail-card <?php echo e($registration->step > 2 ? 'completed' : ($registration->step == 2 ? 'current' : '')); ?>" data-step="2">
                    <div class="step-header">
                        <div class="step-number-badge <?php echo e($registration->step > 2 ? 'completed' : ($registration->step == 2 ? 'current' : '')); ?>">
                            <?php if($registration->step > 2): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                2
                            <?php endif; ?>
                        </div>
                        <h6 class="step-title">Biaya Pendaftaran</h6>
                    </div>
                    <p class="step-description">Pembayaran biaya pendaftaran</p>
                    <?php
                        $registrationPayment = $registration->payments()->where('type', 'registration_fee')->first();
                        $paymentStatus = $registrationPayment ? $registrationPayment->status : null;
                    ?>
                    
                    <?php if($paymentStatus === 'paid'): ?>
                        
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Lunas
                        </span>
                    <?php elseif($paymentStatus === 'pending'): ?>
                        
                        <div class="alert alert-warning mb-2">
                            <i class="fas fa-clock me-1"></i>
                            <strong>Menunggu Pembayaran</strong>
                            <p class="mb-1 mt-1" style="font-size: 0.8rem;">Selesaikan pembayaran QRIS Anda</p>
                            <a href="<?php echo e(route('spmb.step', ['step' => 2])); ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-qrcode me-1"></i>Lanjutkan
                            </a>
                        </div>
                    <?php elseif($paymentStatus === 'failed'): ?>
                        
                        <div class="alert alert-danger mb-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Pembayaran Ditolak</strong>
                            <p class="mb-1 mt-1" style="font-size: 0.8rem;">Upload ulang bukti pembayaran.</p>
                            <a href="<?php echo e(route('spmb.step', ['step' => 2])); ?>" class="btn btn-danger btn-sm">
                                <i class="fas fa-upload me-1"></i>Upload Ulang
                            </a>
                        </div>
                    <?php elseif($registration->step == 2): ?>
                        
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?php echo e(route('spmb.step', ['step' => 2])); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-qrcode me-1"></i>Bayar QRIS
                            </a>
                            <span class="badge bg-danger align-self-center">Wajib</span>
                        </div>
                    <?php else: ?>
                        
                        <span class="badge bg-secondary">
                            <i class="fas fa-minus me-1"></i>Terlewati
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="col-md-6 col-lg-4">
                <div class="step-detail-card <?php echo e($registration->step > 3 ? 'completed' : ($registration->step == 3 ? 'current' : '')); ?>" data-step="3">
                    <div class="step-header">
                        <div class="step-number-badge <?php echo e($registration->step > 3 ? 'completed' : ($registration->step == 3 ? 'current' : '')); ?>">
                            <?php if($registration->step > 3): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                3
                            <?php endif; ?>
                        </div>
                        <h6 class="step-title">Lengkapi Formulir</h6>
                    </div>
                    <p class="step-description">Isi data pribadi dan informasi lainnya</p>
                    <?php if($registration->step == 3): ?>
                        <a href="<?php echo e(route('spmb.step', ['step' => 3])); ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Lengkapi
                        </a>
                    <?php elseif($registration->step > 3): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Selesai
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="col-md-6 col-lg-4">
                <div class="step-detail-card <?php echo e($registration->step > 4 ? 'completed' : ($registration->step == 4 ? 'current' : '')); ?>" data-step="4">
                    <div class="step-header">
                        <div class="step-number-badge <?php echo e($registration->step > 4 ? 'completed' : ($registration->step == 4 ? 'current' : '')); ?>">
                            <?php if($registration->step > 4): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                4
                            <?php endif; ?>
                        </div>
                        <h6 class="step-title">Upload Dokumen</h6>
                    </div>
                    <p class="step-description">Upload dokumen yang diperlukan</p>
                    <?php
                        $documentCount = $registration->documents()->count();
                    ?>
                    <?php if($registration->step == 4): ?>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <a href="<?php echo e(route('spmb.step', ['step' => 4])); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload me-1"></i>Upload
                            </a>
                            <?php if($documentCount > 0): ?>
                                <span class="badge bg-info">
                                    <?php echo e($documentCount); ?> Dokumen
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php elseif($registration->step > 4): ?>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Selesai
                            </span>
                            <?php if($documentCount > 0): ?>
                                <span class="badge bg-info">
                                    <?php echo e($documentCount); ?> Dok
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="col-md-6 col-lg-4">
                <div class="step-detail-card <?php echo e($registration->step > 5 ? 'completed' : ($registration->step == 5 ? 'current' : '')); ?>" data-step="5">
                    <div class="step-header">
                        <div class="step-number-badge <?php echo e($registration->step > 5 ? 'completed' : ($registration->step == 5 ? 'current' : '')); ?>">
                            <?php if($registration->step > 5): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                5
                            <?php endif; ?>
                        </div>
                        <h6 class="step-title">Bayar Biaya SPMB</h6>
                    </div>
                    <p class="step-description">Pembayaran biaya SPMB</p>
                    <?php
                        $spmbPayment = $registration->payments()->where('type', 'spmb_fee')->first();
                        $spmbPaymentStatus = $spmbPayment ? $spmbPayment->status : null;
                    ?>
                    
                    <?php if($registration->step == 5): ?>
                        <a href="<?php echo e(route('spmb.step', ['step' => 5])); ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-credit-card me-1"></i>Bayar
                        </a>
                    <?php elseif($registration->step > 5): ?>
                        <?php if($spmbPaymentStatus === 'failed'): ?>
                            <div class="alert alert-danger mb-2">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <strong>Ditolak!</strong>
                                <p class="mb-1 mt-1" style="font-size: 0.8rem;">Upload ulang bukti pembayaran.</p>
                                <a href="<?php echo e(route('spmb.step', ['step' => 5])); ?>" class="btn btn-danger btn-sm">
                                    <i class="fas fa-upload me-1"></i>Upload Ulang
                                </a>
                            </div>
                        <?php elseif($spmbPaymentStatus === 'pending'): ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-clock me-1"></i>
                                <strong>Pending</strong>
                                <p class="mb-0 mt-1" style="font-size: 0.8rem;">Menunggu verifikasi admin</p>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Lunas
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 6 -->
            <div class="col-md-6 col-lg-4">
                <div class="step-detail-card <?php echo e($registration->step >= 6 ? 'completed' : ''); ?>">
                    <div class="step-header">
                        <div class="step-number-badge <?php echo e($registration->step >= 6 ? 'completed' : ''); ?>">
                            <?php if($registration->step >= 6): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                6
                            <?php endif; ?>
                        </div>
                        <h6 class="step-title">Selesai</h6>
                    </div>
                    <p class="step-description">Pendaftaran selesai, menunggu konfirmasi</p>
                    <?php if($registration->step >= 6): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Selesai
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <?php if($registration->step < 6): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3" style="font-size: 1rem; font-weight: 600;">
                            <i class="fas fa-bolt text-warning me-2"></i>Aksi Cepat
                        </h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php if($registration->step == 2): ?>
                                <a href="<?php echo e(route('spmb.step', ['step' => 2])); ?>" class="btn btn-primary">
                                    <i class="fas fa-credit-card me-1"></i>Bayar Biaya Pendaftaran
                                </a>
                            <?php elseif($registration->step == 3): ?>
                                <a href="<?php echo e(route('spmb.step', ['step' => 3])); ?>" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i>Lengkapi Formulir
                                </a>
                            <?php elseif($registration->step == 4): ?>
                                <a href="<?php echo e(route('spmb.step', ['step' => 4])); ?>" class="btn btn-primary">
                                    <i class="fas fa-upload me-1"></i>Upload Dokumen
                                </a>
                            <?php elseif($registration->step == 5): ?>
                                <a href="<?php echo e(route('spmb.step', ['step' => 5])); ?>" class="btn btn-primary">
                                    <i class="fas fa-credit-card me-1"></i>Bayar Biaya SPMB
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Download Form -->
        <?php if($registration->step >= 3): ?>
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2" style="font-size: 1rem; font-weight: 600;">
                            <i class="fas fa-download text-success me-2"></i>Unduh Formulir
                        </h6>
                        <p class="text-muted mb-3" style="font-size: 0.85rem;">
                            Anda dapat mengunduh formulir pendaftaran untuk keperluan administrasi.
                        </p>
                        <a href="<?php echo e(route('spmb.download-form')); ?>" class="btn btn-success" target="_blank">
                            <i class="fas fa-download me-1"></i>Unduh Formulir Pendaftaran
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?php echo e($whatsappNumber ?? '6281234567890'); ?>?text=Halo,%20saya%20butuh%20bantuan%20mengenai%20pendaftaran%20SPMB" 
       target="_blank" 
       class="whatsapp-float"
       title="Chat dengan Admin (<?php echo e($whatsappNumber ?? '6281234567890'); ?>)">
        <i class="fab fa-whatsapp"></i>
    </a>

    <style>
        .whatsapp-float {
            position: fixed;
            width: 60px;
            height: 60px;
            bottom: 30px;
            right: 30px;
            background-color: #25d366;
            color: #FFF;
            border-radius: 50px;
            text-align: center;
            font-size: 30px;
            box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.3);
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
            box-shadow: 2px 2px 20px rgba(0, 0, 0, 0.4);
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
<?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/spmb/dashboard.blade.php ENDPATH**/ ?>