@extends('layouts.coreui')

@section('title', $addon->name . ' - SPPQU')

@section('active_menu', 'menu.billing')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('manage.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manage.addons.index') }}">Add-ons</a></li>
                        <li class="breadcrumb-item active">{{ $addon->name }}</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-credit-card me-2 text-primary"></i>
                    {{ $addon->name }}
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Add-on Details -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        Detail Add-on
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-credit-card text-primary" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="text-primary mb-0">
                                Rp {{ number_format($addon->price, 0, ',', '.') }}
                            </h4>
                            <small class="text-muted">
                                @if($addon->type === 'one_time')
                                    Sekali Bayar
                                @else
                                    Berlangganan
                                @endif
                            </small>
                        </div>
                        <div class="col-md-8">
                            <h5>{{ $addon->name }}</h5>
                            <p class="text-muted">{{ $addon->description }}</p>
                            
                            @if($userAddon)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Anda sudah memiliki add-on ini!</strong>
                                    <br>
                                    <small>Dibeli pada: {{ $userAddon->purchased_at->format('d M Y H:i') }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list-check me-2 text-success"></i>
                        Fitur yang Didapat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($addon->features as $feature)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>{{ $feature }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- How it Works -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-play-circle me-2 text-warning"></i>
                        Cara Kerja
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-shopping-cart text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <h6>1. Beli Add-on</h6>
                            <p class="text-muted small">Pilih metode pembayaran yang tersedia</p>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-credit-card text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h6>2. Pembayaran</h6>
                            <p class="text-muted small">Lakukan pembayaran melalui payment gateway</p>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-rocket text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <h6>3. Aktif Otomatis</h6>
                            <p class="text-muted small">Fitur langsung aktif setelah pembayaran berhasil</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Form -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-cart me-2 text-primary"></i>
                        Beli Add-on
                    </h5>
                </div>
                <div class="card-body">
                    @if($userAddon)
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Add-on Aktif</h5>
                            @if(auth()->user()->role === 'superadmin')
                                <p class="text-muted">Anda sudah memiliki add-on ini dan dapat menggunakannya.</p>
                            @else
                                <p class="text-muted">Add-on ini sudah dibeli oleh superadmin. Anda dapat menggunakannya.</p>
                            @endif
                            <a href="{{ route('manage.addons.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Add-ons
                            </a>
                        </div>
                    @elseif(!$canPurchase)
                        <div class="text-center">
                            <i class="fas fa-info-circle text-info" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Hanya Superadmin</h5>
                            <p class="text-muted">Hanya superadmin yang dapat membeli add-on. Setelah superadmin membeli, add-on akan otomatis tersedia untuk semua user.</p>
                            <div class="alert alert-info text-start mt-3">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Info:</strong>
                                <ul class="mb-0 mt-2 small">
                                    <li>Superadmin membeli addon sekali</li>
                                    <li>Semua user (admin, operator, dll) otomatis bisa menggunakan</li>
                                    <li>Tidak perlu beli per user</li>
                                </ul>
                            </div>
                            <a href="{{ route('manage.addons.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Add-ons
                            </a>
                        </div>
                    @else
                        <div class="text-center mb-4">
                            <h4 class="text-primary mb-0">Rp {{ number_format($addon->price, 0, ',', '.') }}</h4>
                            <small class="text-muted">
                                @if($addon->type === 'one_time')
                                    Sekali Bayar (Lifetime)
                                @else
                                    Per Bulan
                                @endif
                            </small>
                        </div>

                        <form action="{{ route('manage.addons.purchase', $addon->slug) }}" method="POST" id="purchaseForm">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Pilih Metode Pembayaran
                                </label>
                                <div class="payment-methods">
                                    <!-- QRIS -->
                                    <div class="form-check payment-option mb-3">
                                        <input class="form-check-input" type="radio" name="payment_method" id="qris" value="QRIS" required>
                                        <label class="form-check-label w-100" for="qris">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <i class="fas fa-qrcode me-2 text-primary"></i>
                                                    <strong>QRIS</strong>
                                                    <br>
                                                    <small class="text-muted">Scan & Pay via E-Wallet</small>
                                                </div>
                                                <span class="badge bg-success">Tercepat</span>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- BRI Virtual Account -->
                                    <div class="form-check payment-option mb-3">
                                        <input class="form-check-input" type="radio" name="payment_method" id="briva" value="BRIVA">
                                        <label class="form-check-label w-100" for="briva">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-university me-2 text-primary"></i>
                                                <strong>BRI Virtual Account</strong>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- BCA Virtual Account -->
                                    <div class="form-check payment-option mb-3">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bcava" value="BCAVA">
                                        <label class="form-check-label w-100" for="bcava">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-university me-2 text-info"></i>
                                                <strong>BCA Virtual Account</strong>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Mandiri Virtual Account -->
                                    <div class="form-check payment-option mb-3">
                                        <input class="form-check-input" type="radio" name="payment_method" id="mandiriva" value="MANDIRIVA">
                                        <label class="form-check-label w-100" for="mandiriva">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-university me-2 text-warning"></i>
                                                <strong>Mandiri Virtual Account</strong>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- BNI Virtual Account -->
                                    <div class="form-check payment-option mb-3">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bniva" value="BNIVA">
                                        <label class="form-check-label w-100" for="bniva">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-university me-2 text-danger"></i>
                                                <strong>BNI Virtual Account</strong>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Pembayaran aman menggunakan Tripay Payment Gateway
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-shield-alt me-2"></i>
                                <strong>Keamanan Terjamin</strong>
                                <ul class="mb-0 mt-2 small">
                                    <li>Transaksi dienkripsi dengan SSL</li>
                                    <li>Pembayaran otomatis terverifikasi</li>
                                    <li>Addon aktif setelah pembayaran berhasil</li>
                                </ul>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="btnPurchase">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Beli Sekarang
                                </button>
                                <a href="{{ route('manage.addons.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Kembali
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Benefits -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-gift me-2 text-success"></i>
                        Keuntungan
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Akses seumur hidup
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Update gratis
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Support 24/7
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Garansi 30 hari
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-option {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-option:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.payment-option:has(input[type="radio"]:checked) {
    border-color: #0d6efd;
    background-color: #e7f3ff;
}

.payment-option label {
    cursor: pointer;
    margin-bottom: 0;
    padding: 8px;
}

.payment-option input[type="radio"] {
    cursor: pointer;
}
</style>

<script>
// Handle form submission
document.getElementById('purchaseForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnPurchase');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
    }
});

// Add click event to payment options for better UX
document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', function() {
        const radio = this.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
        }
    });
});
</script>
@endsection
