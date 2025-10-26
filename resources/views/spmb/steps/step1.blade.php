<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langkah 1 - SPMB</title>
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
        }

        .navbar {
            background: #ffffff !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #e9ecef;
        }

        .navbar-brand {
            color: #008060 !important;
            font-weight: 700;
        }

        .navbar-text {
            color: #008060 !important;
            font-weight: 600;
        }

        .step-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
            animation: slideInUp 1s ease-out;
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

        .step-header {
            background: linear-gradient(135deg, rgb(8, 129, 45) 0%, #006d52 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 25px 25px 0 0;
            position: relative;
            overflow: hidden;
        }

        .step-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .step-body {
            padding: 2.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            border: none;
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 16px;
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

        .btn-outline-secondary {
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.4);
        }

        .info-box {
            background: rgba(0, 128, 96, 0.05);
            border: 2px solid rgba(0, 128, 96, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('spmb.dashboard') }}">
                <i class="fas fa-graduation-cap me-2"></i>SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>{{ session('spmb_name') }}
                </span>
                <form method="POST" action="{{ route('spmb.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="step-card">
                    <div class="step-header text-center">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h3 class="mb-0">Langkah 1: Pendaftaran</h3>
                        <p class="mb-0">Akun Anda telah berhasil dibuat</p>
                    </div>
                    <div class="step-body">
                        <div class="info-box">
                            <h6 class="mb-2"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Akun</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Nama:</strong> {{ $registration->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>No. HP:</strong> {{ $registration->phone }}</p>
                                </div>
                            </div>
                            <p class="mb-0"><strong>Password:</strong> 6 digit terakhir nomor HP Anda</p>
                        </div>

                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="fas fa-check-circle me-2"></i>Pendaftaran Berhasil!
                            </h6>
                            <p class="mb-0">
                                Akun Anda telah berhasil dibuat. Silakan lanjutkan ke langkah berikutnya untuk melakukan pembayaran biaya pendaftaran.
                            </p>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('spmb.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                            </a>
                            <a href="{{ route('spmb.step', ['step' => 2]) }}" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-1"></i>Lanjutkan ke Langkah 2
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

