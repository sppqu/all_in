<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Mock - SPMB</title>
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
        .mock-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 15px;
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
                <div class="payment-card">
                    <div class="payment-header text-center">
                        <i class="fas fa-credit-card fa-3x mb-3"></i>
                        <h3 class="mb-0">Pembayaran {{ $payment->getTypeName() }}</h3>
                        <p class="mb-0">Mode Development - Mock Payment</p>
                    </div>
                    <div class="payment-body">
                        <div class="mock-notice">
                            <h6 class="mb-2">
                                <i class="fas fa-info-circle me-2"></i>Mode Development
                            </h6>
                            <p class="mb-0">
                                Ini adalah mode development dengan mock payment. 
                                Untuk production, pastikan konfigurasi Tripay sudah benar di file .env
                            </p>
                        </div>

                        <div class="text-center mb-4">
                            <h5 class="mb-3">Total Pembayaran</h5>
                            <div class="amount-display">{{ $payment->getAmountFormattedAttribute() }}</div>
                        </div>

                        <div class="text-center mb-4">
                            <h6 class="mb-3">Mock QR Code</h6>
                            <div class="bg-light p-4 rounded">
                                <i class="fas fa-qrcode fa-5x text-muted"></i>
                                <p class="mt-3 text-muted">QR Code Mock untuk Testing</p>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Informasi Mock Payment
                            </h6>
                            <ul class="mb-0">
                                <li>Ini adalah simulasi pembayaran untuk development</li>
                                <li>Klik tombol "Simulasi Pembayaran Berhasil" untuk melanjutkan</li>
                                <li>Untuk production, gunakan konfigurasi Tripay yang valid</li>
                                <li>Pastikan file .env sudah dikonfigurasi dengan benar</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('spmb.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                            </a>
                            <button onclick="simulatePayment()" class="btn btn-primary">
                                <i class="fas fa-check me-1"></i>Simulasi Pembayaran Berhasil
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="fas fa-code me-1"></i>
                                Development Mode - Mock Payment System
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function simulatePayment() {
            // Simulate payment success
            fetch('{{ route("spmb.payment.callback") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    reference: '{{ $payment->tripay_reference }}',
                    status: 'PAID'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = '{{ route("spmb.payment.success") }}';
                }
            })
            .catch(error => {
                console.log('Payment simulation failed');
                // Still redirect to success page for demo
                window.location.href = '{{ route("spmb.payment.success") }}';
            });
        }
    </script>
</body>
</html>






