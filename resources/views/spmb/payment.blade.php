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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .payment-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .payment-header {
                padding: 1.5rem 1rem;
            }

            .payment-header h3 {
                font-size: 1.25rem;
            }

            .payment-header .fa-3x {
                font-size: 2rem !important;
            }

            .payment-body {
                padding: 1.25rem;
            }

            .amount-display {
                font-size: 1.5rem;
            }

            .qr-code-container {
                padding: 15px;
            }

            .qr-code {
                max-width: 200px;
            }
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
            transform: translateY(-1px);
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
                        <div class="qr-code-container" id="qrCodeContainer">
                            <h6 class="mb-3">
                                <i class="fas fa-qrcode me-2"></i>
                                Scan QR Code QRIS
                            </h6>
                            @php
                                // Check if qr_code is an image URL or QR string
                                $isImageUrl = str_starts_with($payment->qr_code, 'http') || str_starts_with($payment->qr_code, 'data:image');
                            @endphp
                            @if($isImageUrl)
                            <img src="{{ $payment->qr_code }}" alt="QR Code QRIS" class="qr-code img-fluid" id="qrCodeImage" style="max-width: 300px; height: auto;">
                            @else
                            <div id="qrcode" style="display: inline-block; padding: 20px; background: white; border-radius: 10px;"></div>
                            @endif
                            <p class="text-muted mt-3 small mb-0">
                                <i class="fas fa-mobile-alt me-1"></i>
                                Scan dengan aplikasi e-wallet Anda (GoPay, OVO, Dana, LinkAja, ShopeePay, dll)
                            </p>
                        </div>
                        @else
                        <div class="qr-code-container" id="qrCodeContainer">
                            <div class="text-center" id="qrLoading">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h6>Mengambil QR Code...</h6>
                                <p class="text-muted mb-0">Mohon tunggu sebentar</p>
                            </div>
                            <div class="d-none" id="qrCodeReady">
                                <h6 class="mb-3 text-center">
                                    <i class="fas fa-qrcode me-2"></i>
                                    Scan QR Code QRIS
                                </h6>
                                <div class="text-center">
                                    <img src="" alt="QR Code QRIS" class="qr-code img-fluid" id="qrCodeImage" style="max-width: 300px; height: auto;">
                                </div>
                            </div>
                            <div class="alert alert-warning text-center d-none" id="qrNotAvailable">
                                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                <h6>QR Code Tidak Tersedia</h6>
                                <p class="mb-2">Silakan gunakan link pembayaran di bawah</p>
                            </div>
                        </div>
                        @endif


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

                        <div class="d-grid gap-2 mb-3">
                            <button type="button" class="btn btn-success btn-lg" id="btnCheckStatus" onclick="checkPaymentStatus()">
                                <i class="fas fa-sync-alt me-2"></i>
                                <span id="statusBtnText">Cek Status Pembayaran</span>
                            </button>
                        </div>

                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('spmb.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                            @if($payment->payment_url)
                            <a href="{{ $payment->payment_url }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt me-1"></i>Lihat Detail
                            </a>
                            @endif
                        </div>
                        

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Generate QR Code if qr_code is a string (not image URL)
        @if($payment->qr_code && !str_starts_with($payment->qr_code, 'http') && !str_starts_with($payment->qr_code, 'data:image'))
        document.addEventListener('DOMContentLoaded', function() {
            new QRCode(document.getElementById("qrcode"), {
                text: "{{ $payment->qr_code }}",
                width: 256,
                height: 256,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });
        @endif

        @if(!$payment->qr_code)
        // Fetch QR Code if not available
        function fetchQRCode() {
            console.log('Fetching QR Code from Tripay...');
            
            fetch('{{ $payment->payment_url }}')
                .then(response => {
                    // Try to parse QR from Tripay checkout page
                    return response.text();
                })
                .then(html => {
                    // Try to extract QR code from HTML
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const qrImage = doc.querySelector('img[alt*="QR"], img[src*="qr"], .qr-code img, #qr-code img');
                    
                    if (qrImage && qrImage.src) {
                        document.getElementById('qrCodeImage').src = qrImage.src;
                        document.getElementById('qrLoading').classList.add('d-none');
                        document.getElementById('qrCodeReady').classList.remove('d-none');
                        console.log('QR Code loaded successfully');
                    } else {
                        // QR code not found in HTML
                        document.getElementById('qrLoading').classList.add('d-none');
                        document.getElementById('qrNotAvailable').classList.remove('d-none');
                        console.log('QR Code not found in Tripay page');
                    }
                })
                .catch(error => {
                    console.error('Error fetching QR Code:', error);
                    document.getElementById('qrLoading').classList.add('d-none');
                    document.getElementById('qrNotAvailable').classList.remove('d-none');
                });
        }

        // Try to fetch QR code after 2 seconds
        setTimeout(fetchQRCode, 2000);
        @endif

        // Payment ID
        const paymentId = {{ $payment->id }};
        let checkInterval;

        // Function to check payment status
        function checkPaymentStatus() {
            const btn = document.getElementById('btnCheckStatus');
            const btnText = document.getElementById('statusBtnText');
            
            // Disable button
            btn.disabled = true;
            btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengecek...';
            
            fetch('{{ route("spmb.dashboard") }}')
                .then(response => response.text())
                .then(html => {
                    // Parse response to check if payment status changed
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Try to detect if user can proceed (payment success)
                    // You can customize this based on your dashboard HTML structure
                    
                    // For now, just reload to check
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error checking payment:', error);
                    btn.disabled = false;
                    btnText.textContent = 'Cek Status Pembayaran';
                    alert('Gagal mengecek status. Silakan coba lagi.');
                });
        }

        // Auto check payment status every 10 seconds
        function startAutoCheck() {
            checkInterval = setInterval(function() {
                console.log('Auto-checking payment status...');
                
                fetch('{{ route("spmb.dashboard") }}')
                    .then(response => response.text())
                    .then(html => {
                        // If dashboard loads successfully, payment might be complete
                        // Reload page to check
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Check if there's a success indicator or if we can proceed
                        // For simplicity, we'll just check the payment record in DB via reload
                        fetch(window.location.href)
                            .then(r => r.text())
                            .then(pageHtml => {
                                if (pageHtml.includes('success') || pageHtml.includes('berhasil')) {
                                    window.location.href = '{{ route("spmb.dashboard") }}';
                                }
                            });
                    })
                    .catch(error => console.log('Auto-check failed:', error));
            }, 10000); // Check every 10 seconds
        }

        // Start auto-check after 5 seconds
        setTimeout(startAutoCheck, 5000);

        // Stop auto-check when user leaves page
        window.addEventListener('beforeunload', function() {
            if (checkInterval) {
                clearInterval(checkInterval);
            }
        });
    </script>
</body>
</html>






