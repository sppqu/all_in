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
                                <strong>{{ strtoupper($result['payment_method']) }}</strong>
                                @if($result['payment_method'] == 'QRIS')
                                    <p class="mb-0 mt-2 small">Scan QR Code dengan aplikasi e-wallet Anda</p>
                                @elseif(str_contains($result['payment_method'], 'VA'))
                                    <p class="mb-0 mt-2 small">Transfer ke Virtual Account yang tersedia</p>
                                @else
                                    <p class="mb-0 mt-2 small">Ikuti instruksi pembayaran di halaman Tripay</p>
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
                    @if(isset($result['qr_url']) && $result['qr_url'])
                        <!-- QR Code Payment -->
                        <div class="text-center mb-4">
                            <h5 class="mb-3">Scan QR Code untuk Pembayaran</h5>
                            <img src="{{ $result['qr_url'] }}" alt="QR Code" class="img-fluid" style="max-width: 300px; border: 2px solid #ddd; padding: 10px; border-radius: 8px;">
                            <p class="text-muted mt-3 small">
                                <i class="fas fa-mobile-alt me-2"></i>
                                Buka aplikasi e-wallet Anda dan scan QR Code di atas
                            </p>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Reference ID:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ $result['reference'] ?? $result['merchant_ref'] }}" id="reference" readonly>
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
                                    <input type="text" class="form-control fw-bold text-success" value="Rp {{ number_format($result['amount'], 0, ',', '.') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(isset($result['expired_time']))
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Batas Waktu Pembayaran:</strong>
                        {{ date('d M Y H:i', $result['expired_time']) }} WIB
                        <span class="small">({{ round(($result['expired_time'] - time()) / 3600, 1) }} jam lagi)</span>
                    </div>
                    @endif

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Pembayaran aman dengan Tripay</strong>
                        <p class="mb-0 mt-2 small">Transaksi Anda dilindungi dan diproses secara otomatis. Setelah pembayaran berhasil, subscription akan langsung aktif.</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(isset($result['checkout_url']) && $result['checkout_url'])
                        <a href="{{ $result['checkout_url'] }}" target="_blank" class="btn btn-lg btn-primary">
                            <i class="fas fa-external-link-alt me-2"></i>
                            Lanjutkan ke Pembayaran
                        </a>
                        @endif
                        
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-2"></i>
                            Refresh Status Pembayaran
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

<script>
function copyToClipboard(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    document.execCommand('copy');
    
    // Show success toast
    alert('Reference ID berhasil disalin!');
}

// Auto refresh status every 10 seconds
let refreshInterval;
let refreshCount = 0;
const maxRefresh = 30; // Max 30 refresh (5 menit)

function checkPaymentStatus() {
    fetch('{{ route('manage.subscription.check-status') }}')
        .then(response => response.json())
        .then(data => {
            if (data.has_active_subscription) {
                clearInterval(refreshInterval);
                window.location.href = '{{ route('manage.subscription.index') }}?status=success';
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

// Start auto refresh after 10 seconds
setTimeout(() => {
    refreshInterval = setInterval(checkPaymentStatus, 10000);
}, 10000);
</script>
@endsection


