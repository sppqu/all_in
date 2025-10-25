<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - SPMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .success-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .success-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
        }
        .success-body {
            padding: 2rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
        }
        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            border-radius: 10px;
            padding: 12px 24px;
        }
        .btn-outline-primary:hover {
            background: #667eea;
            border-color: #667eea;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .next-steps {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
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
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="success-card">
                    <div class="success-header text-center">
                        <i class="fas fa-check-circle fa-4x mb-3"></i>
                        <h3 class="mb-0">Pembayaran Berhasil!</h3>
                        <p class="mb-0">Terima kasih telah melakukan pembayaran</p>
                    </div>
                    <div class="success-body">
                        <div class="text-center mb-4">
                            <div class="success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h4 class="text-success">Pembayaran Anda telah berhasil diproses</h4>
                            <p class="text-muted">
                                Pembayaran telah terverifikasi dan Anda dapat melanjutkan ke langkah berikutnya.
                            </p>
                        </div>

                        <div class="next-steps">
                            <h6 class="mb-3">
                                <i class="fas fa-list-ol me-2"></i>Langkah Selanjutnya
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <span>Pembayaran Selesai</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                            <span>2</span>
                                        </div>
                                        <span>Lanjutkan ke Langkah Berikutnya</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Informasi Penting
                            </h6>
                            <ul class="mb-0">
                                <li>Pembayaran Anda telah tercatat dalam sistem</li>
                                <li>Anda akan menerima notifikasi melalui WhatsApp</li>
                                <li>Silakan lanjutkan ke langkah berikutnya untuk menyelesaikan pendaftaran</li>
                                <li>Jika ada pertanyaan, hubungi admin sekolah</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('spmb.dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt me-1"></i>Kembali ke Dashboard
                            </a>
                            <a href="{{ route('spmb.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-home me-1"></i>Beranda
                            </a>
                        </div>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Data Anda aman dan terlindungi
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>






