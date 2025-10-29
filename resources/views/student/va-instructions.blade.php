<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Instruksi Pembayaran VA - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .va-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 2px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #667eea;
        }
        .copy-btn {
            cursor: pointer;
        }
        .instruction-step {
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 5px;
        }
        .bank-logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }
        .countdown {
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Success Alert -->
                <div class="alert alert-success mb-4">
                    <i class="fas fa-check-circle"></i>
                    <strong>Pembayaran Berhasil Dibuat!</strong> Silakan lakukan transfer ke nomor Virtual Account di bawah ini.
                </div>

                <!-- Payment Info Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-university fa-3x text-primary mb-3"></i>
                            <h4 class="card-title">{{ $channel }} Virtual Account</h4>
                            <p class="text-muted">Nomor Referensi: <strong>{{ $reference }}</strong></p>
                        </div>

                        <!-- VA Number -->
                        <div class="text-center mb-4">
                            <label class="form-label"><strong>Nomor Virtual Account</strong></label>
                            <div class="va-number" id="vaNumber">
                                {{ $paymentNo }}
                            </div>
                            <button class="btn btn-outline-primary btn-sm mt-2 copy-btn" onclick="copyVA()">
                                <i class="fas fa-copy"></i> Salin Nomor VA
                            </button>
                        </div>

                        <!-- Amount -->
                        <div class="row text-center mb-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted">Total Tagihan</small>
                                    <h5 class="mb-0">Rp {{ number_format($total, 0, ',', '.') }}</h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted">Berlaku Hingga</small>
                                    <h5 class="mb-0" id="expiredTime">
                                        {{ $expired ? \Carbon\Carbon::parse($expired)->format('d M Y H:i') : '-' }}
                                    </h5>
                                </div>
                            </div>
                        </div>

                        @if($expired)
                        <!-- Countdown Timer -->
                        <div class="text-center mb-3">
                            <small class="text-muted">Sisa Waktu:</small>
                            <div class="countdown" id="countdown"></div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Instructions Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list-ol"></i> Cara Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="instruction-step">
                            <strong>1. ATM {{ $channel }}</strong>
                            <ul class="mt-2">
                                <li>Masukkan kartu ATM dan PIN</li>
                                <li>Pilih menu <strong>Transaksi Lainnya</strong></li>
                                <li>Pilih <strong>Transfer</strong></li>
                                <li>Pilih <strong>Ke Rekening {{ $channel }}</strong></li>
                                <li>Masukkan nomor Virtual Account: <strong>{{ $paymentNo }}</strong></li>
                                <li>Masukkan jumlah yang akan dibayar: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></li>
                                <li>Ikuti instruksi untuk menyelesaikan transaksi</li>
                            </ul>
                        </div>

                        <div class="instruction-step">
                            <strong>2. Mobile Banking {{ $channel }}</strong>
                            <ul class="mt-2">
                                <li>Login ke aplikasi mobile banking</li>
                                <li>Pilih menu <strong>Transfer</strong></li>
                                <li>Pilih <strong>Transfer ke {{ $channel }} Virtual Account</strong></li>
                                <li>Masukkan nomor Virtual Account: <strong>{{ $paymentNo }}</strong></li>
                                <li>Masukkan jumlah: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></li>
                                <li>Konfirmasi dan selesaikan pembayaran</li>
                            </ul>
                        </div>

                        <div class="instruction-step">
                            <strong>3. Internet Banking {{ $channel }}</strong>
                            <ul class="mt-2">
                                <li>Login ke internet banking</li>
                                <li>Pilih menu <strong>Transfer Dana</strong></li>
                                <li>Pilih <strong>Transfer ke {{ $channel }} Virtual Account</strong></li>
                                <li>Masukkan nomor Virtual Account: <strong>{{ $paymentNo }}</strong></li>
                                <li>Masukkan nominal: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></li>
                                <li>Ikuti instruksi untuk menyelesaikan transaksi</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Informasi Penting:</h6>
                    <ul class="mb-0">
                        <li>Pembayaran akan otomatis terverifikasi setelah Anda melakukan transfer</li>
                        <li>Status pembayaran dapat dicek di halaman <strong>Riwayat Pembayaran</strong></li>
                        <li>Pastikan transfer sebelum batas waktu berakhir</li>
                        <li>Jika ada kendala, hubungi admin sekolah</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <a href="{{ route('student.payment.history') }}" class="btn btn-primary w-100">
                            <i class="fas fa-history"></i> Lihat Riwayat Pembayaran
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-home"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Copy VA Number
        function copyVA() {
            const vaNumber = document.getElementById('vaNumber').textContent.trim();
            navigator.clipboard.writeText(vaNumber).then(() => {
                alert('Nomor VA berhasil disalin!');
            }).catch(err => {
                console.error('Gagal menyalin:', err);
            });
        }

        // Countdown Timer
        @if($expired)
        const expiredTime = new Date('{{ $expired }}').getTime();
        
        const countdownInterval = setInterval(function() {
            const now = new Date().getTime();
            const distance = expiredTime - now;
            
            if (distance < 0) {
                clearInterval(countdownInterval);
                document.getElementById('countdown').innerHTML = '<span class="text-danger">KADALUARSA</span>';
                return;
            }
            
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('countdown').innerHTML = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }, 1000);
        @endif
    </script>
</body>
</html>

