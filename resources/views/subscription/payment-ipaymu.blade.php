@extends('layouts.coreui')

@section('title', 'Pembayaran Berlangganan - SPPQU')

@section('active_menu', 'menu.billing')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('manage.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manage.subscription.plans') }}">Berlangganan</a></li>
                        <li class="breadcrumb-item active">Pembayaran</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-credit-card me-2"></i>
                    Pembayaran Berlangganan
                </h4>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Detail Pembayaran -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Detail Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Informasi Paket</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Paket:</strong></td>
                                    <td>{{ $plan['name'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Harga:</strong></td>
                                    <td class="text-success fw-bold">Rp {{ number_format($plan['price'], 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Durasi:</strong></td>
                                    <td>{{ $plan['duration'] }} hari</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge bg-warning">Menunggu Pembayaran</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Metode Pembayaran</h6>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>{{ strtoupper($result['payment_channel'] ?? 'Virtual Account') }}</strong>
                                @if(isset($result['payment_name']))
                                    <p class="mb-0 mt-2 small">{{ $result['payment_name'] }}</p>
                                @endif
                                @if(isset($result['qr_string']) && $result['qr_string'])
                                    <p class="mb-0 mt-2 small">Scan QR Code dengan aplikasi e-wallet Anda</p>
                                @elseif(isset($result['va_number']) && $result['va_number'])
                                    <p class="mb-0 mt-2 small">Transfer ke Virtual Account yang tersedia</p>
                                @else
                                    <p class="mb-0 mt-2 small">Ikuti instruksi pembayaran di halaman iPaymu</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instruksi Pembayaran -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Informasi Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($result['qr_string']) && $result['qr_string'])
                        <!-- QR Code Payment -->
                        <div class="text-center mb-4">
                            <h5 class="mb-3">Scan QR Code untuk Pembayaran</h5>
                            <div class="d-flex justify-content-center mb-3">
                                <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                    <div id="qrcode"></div>
                                </div>
                            </div>
                            <p class="text-muted mt-3 small">
                                <i class="fas fa-mobile-alt me-2"></i>
                                Buka aplikasi e-wallet Anda dan scan QR Code di atas
                            </p>
                        </div>
                    @endif

                    @php
                        // Check multiple possible VA number fields
                        $vaNumber = $result['va_number'] ?? $result['payment_no'] ?? $result['payment_code'] ?? null;
                    @endphp
                    
                    @if($vaNumber)
                        <!-- Virtual Account Payment -->
                        <div class="alert alert-light border mb-4">
                            <h6 class="text-center mb-3">Nomor Virtual Account</h6>
                            <div class="input-group input-group-lg">
                                <input type="text" class="form-control text-center fw-bold" value="{{ $vaNumber }}" id="vaNumber" readonly style="font-size: 1.5rem; letter-spacing: 0.1em;">
                                <button class="btn btn-outline-primary" type="button" onclick="copyToClipboard('vaNumber')">
                                    <i class="fas fa-copy"></i> Salin
                                </button>
                            </div>
                            <p class="text-center text-muted mt-2 mb-0 small">
                                <i class="fas fa-university me-1"></i>
                                Transfer ke nomor VA di atas menggunakan {{ $result['payment_name'] ?? 'Bank' }}
                            </p>
                        </div>
                    @endif
                    
                    {{-- Debug Info (remove after testing) --}}
                    @if(config('app.debug'))
                    <div class="alert alert-info small">
                        <strong>Debug Info:</strong><br>
                        VA Number: {{ $result['va_number'] ?? 'null' }}<br>
                        Payment No: {{ $result['payment_no'] ?? 'null' }}<br>
                        Payment Code: {{ $result['payment_code'] ?? 'null' }}<br>
                        Payment URL: {{ $result['payment_url'] ?? 'null' }}
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Reference ID:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ $result['reference_id'] }}" id="reference" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('reference')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Jumlah yang Harus Dibayar:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control fw-bold text-success" value="Rp {{ number_format($plan['price'], 0, ',', '.') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(isset($result['expired']))
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Batas Waktu Pembayaran:</strong>
                        {{ $result['expired'] }}
                    </div>
                    @endif

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Pembayaran aman dengan iPaymu</strong>
                        <p class="mb-0 mt-2 small">Transaksi Anda dilindungi dan diproses secara otomatis. Setelah pembayaran berhasil, subscription akan langsung aktif.</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(isset($result['payment_url']) && $result['payment_url'])
                        <a href="{{ $result['payment_url'] }}" target="_blank" class="btn btn-lg btn-primary">
                            <i class="fas fa-external-link-alt me-2"></i>
                            Lanjutkan ke Pembayaran iPaymu
                        </a>
                        @endif
                        
                        <button type="button" class="btn btn-outline-secondary" id="btnRefreshStatus" onclick="checkPaymentStatusManual()">
                            <i class="fas fa-sync-alt me-2"></i>
                            <span id="statusBtnText">Refresh Status Pembayaran</span>
                        </button>
                        
                        <a href="{{ route('manage.subscription.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali ke Berlangganan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            <div class="alert alert-secondary mt-4">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    Catatan Penting:
                </h6>
                <ul class="mb-0 small">
                    <li>Simpan Reference ID untuk referensi pembayaran Anda</li>
                    <li>Pembayaran akan diverifikasi secara otomatis oleh sistem</li>
                    <li>Setelah pembayaran berhasil, subscription langsung aktif</li>
                    <li>Jika ada kendala, silakan hubungi administrator</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- QRCode.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// Generate QR Code if QRIS payment
@if(isset($result['qr_string']) && $result['qr_string'])
document.addEventListener('DOMContentLoaded', function() {
    new QRCode(document.getElementById("qrcode"), {
        text: "{{ $result['qr_string'] }}",
        width: 256,
        height: 256,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
});
@endif

function copyToClipboard(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    
    // Show success message
    alert('Berhasil disalin ke clipboard!');
}

// Auto refresh status every 10 seconds
let refreshInterval;
let refreshCount = 0;
const maxRefresh = 36; // Max 36 refresh (6 menit)
const referenceId = '{{ $result['reference_id'] }}';

function checkPaymentStatus() {
    console.log('Auto-checking payment status...', referenceId);
    
    fetch('{{ route('manage.subscription.check-payment') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            reference: referenceId
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log('Payment status:', data);
            
            if (data.success && data.status === 'paid') {
                clearInterval(refreshInterval);
                // Show success message
                alert('✅ ' + data.message);
                // Redirect to subscription page
                window.location.href = '{{ route('manage.subscription.index') }}?status=success';
            } else if (data.status === 'expired' || data.status === 'failed') {
                clearInterval(refreshInterval);
                alert('❌ Pembayaran ' + data.status + ': ' + data.message);
            } else {
                refreshCount++;
                if (refreshCount >= maxRefresh) {
                    clearInterval(refreshInterval);
                    console.log('Max refresh reached');
                }
            }
        })
        .catch(error => {
            console.error('Error checking status:', error);
        });
}

// Manual check payment status (when user clicks button)
function checkPaymentStatusManual() {
    const btn = document.getElementById('btnRefreshStatus');
    const btnText = document.getElementById('statusBtnText');
    
    // Disable button
    btn.disabled = true;
    btnText.textContent = 'Mengecek status...';
    
    fetch('{{ route('manage.subscription.check-payment') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            reference: referenceId
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log('Manual check result:', data);
            
            if (data.success && data.status === 'paid') {
                alert('✅ ' + data.message);
                window.location.href = '{{ route('manage.subscription.index') }}?status=success';
            } else if (data.status === 'expired' || data.status === 'failed') {
                alert('❌ Pembayaran ' + data.status + ': ' + data.message);
                btn.disabled = false;
                btnText.textContent = 'Refresh Status Pembayaran';
            } else {
                alert('ℹ️ Status: ' + data.message);
                btn.disabled = false;
                btnText.textContent = 'Refresh Status Pembayaran';
            }
        })
        .catch(error => {
            console.error('Error checking status:', error);
            alert('❌ Gagal mengecek status pembayaran. Silakan coba lagi.');
            btn.disabled = false;
            btnText.textContent = 'Refresh Status Pembayaran';
        });
}

// Start auto refresh after 10 seconds
setTimeout(() => {
    console.log('Starting auto-refresh payment status...');
    refreshInterval = setInterval(checkPaymentStatus, 10000);
}, 10000);
</script>
@endsection

