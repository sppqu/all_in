<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - SPMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
        }
        .payment-body {
            padding: 2rem;
        }
        .qr-code-container {
            background: #f8f9fa;
            border: 2px dashed #e9ecef;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
        }
        .qr-code {
            max-width: 250px;
            height: auto;
        }
        .amount-display {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
        }
        .btn-outline-secondary {
            border-radius: 10px;
            padding: 12px 24px;
        }
        .payment-methods {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .countdown {
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc3545;
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
                <div class="payment-card">
                    <div class="payment-header text-center">
                        <i class="fas fa-credit-card fa-3x mb-3"></i>
                        <h3 class="mb-0">Pembayaran {{ $payment->getTypeName() }}</h3>
                        <p class="mb-0">Lakukan pembayaran untuk melanjutkan pendaftaran</p>
                    </div>
                    <div class="payment-body">
                        <div class="text-center mb-4">
                            <h5 class="mb-3">Total Pembayaran</h5>
                            <div class="amount-display">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
                            <small class="text-muted d-block mt-2">
                                {{ $payment->getTypeName() }}
                            </small>
                        </div>

                        @if($payment->qr_code)
                        <div class="qr-code-container">
                            <h6 class="mb-3">
                                <i class="fas fa-qrcode me-2"></i>
                                Scan QR Code QRIS
                            </h6>
                            <img src="{{ $payment->qr_code }}" alt="QR Code QRIS" class="qr-code img-fluid" style="max-width: 300px; height: auto;">
                            <p class="mt-3 mb-0 text-muted">
                                <i class="fas fa-mobile-alt me-2"></i>
                                Scan dengan aplikasi e-wallet atau mobile banking
                            </p>
                        </div>
                        @else
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h6>QR Code Tidak Tersedia</h6>
                            <p class="mb-2">Silakan gunakan link pembayaran di bawah</p>
                        </div>
                        @endif

                        <div class="payment-methods">
                            <h6 class="mb-3">
                                <i class="fas fa-mobile-alt me-2"></i>Cara Pembayaran
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">E-Wallet</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>OVO</li>
                                        <li><i class="fas fa-check text-success me-2"></i>DANA</li>
                                        <li><i class="fas fa-check text-success me-2"></i>GoPay</li>
                                        <li><i class="fas fa-check text-success me-2"></i>ShopeePay</li>
                                        <li><i class="fas fa-check text-success me-2"></i>LinkAja</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Mobile Banking</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>BCA Mobile</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Mandiri Online</li>
                                        <li><i class="fas fa-check text-success me-2"></i>BRI Mobile</li>
                                        <li><i class="fas fa-check text-success me-2"></i>BNI Mobile</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        @if($payment->expired_at)
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-clock me-2"></i>Batas Waktu Pembayaran
                            </h6>
                            <p class="mb-0">
                                Pembayaran akan kadaluarsa pada: 
                                <strong>{{ $payment->expired_at->format('d/m/Y H:i') }}</strong>
                            </p>
                        </div>
                        @endif

                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Informasi Penting
                            </h6>
                            <ul class="mb-0">
                                <li>Pastikan nominal pembayaran sesuai dengan yang tertera</li>
                                <li>Pembayaran akan otomatis terverifikasi dalam beberapa menit</li>
                                <li>Jika pembayaran gagal, Anda dapat mencoba lagi</li>
                                <li>Simpan bukti pembayaran sebagai referensi</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('spmb.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                            </a>
                            @if($payment->payment_url)
                            <a href="{{ $payment->payment_url }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt me-1"></i>Buka Halaman Pembayaran
                            </a>
                            @endif
                        </div>
                        
                        {{-- Debug Info (hapus setelah testing) --}}
                        @if(config('app.debug'))
                        <div class="alert alert-secondary mt-4">
                            <small>
                                <strong>Debug Info:</strong><br>
                                Payment ID: {{ $payment->id }}<br>
                                Reference: {{ $payment->tripay_reference }}<br>
                                QR Code: {{ $payment->qr_code ? 'Available (' . strlen($payment->qr_code) . ' chars)' : 'NOT AVAILABLE' }}<br>
                                Payment URL: {{ $payment->payment_url ?? 'N/A' }}<br>
                                Amount: {{ $payment->amount }}<br>
                                Status: {{ $payment->status }}
                            </small>
                        </div>
                        @endif

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Pembayaran aman dan terjamin keamanannya
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh payment status every 30 seconds
        setInterval(function() {
            fetch('{{ route("spmb.payment.callback") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    reference: '{{ $payment->tripay_reference }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'PAID') {
                    window.location.href = '{{ route("spmb.payment.success") }}';
                }
            })
            .catch(error => console.log('Payment check failed'));
        }, 30000);
    </script>
</body>
</html>






