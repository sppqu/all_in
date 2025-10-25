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
                            <h5 class="mt-3">Sudah Dimiliki</h5>
                            <p class="text-muted">Anda sudah memiliki add-on ini dan dapat menggunakannya.</p>
                                                         <a href="{{ route('manage.addons.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Add-ons
                            </a>
                        </div>
                    @else
                        <div class="text-center mb-4">
                            <h4 class="text-primary mb-0">Rp {{ number_format($addon->price, 0, ',', '.') }}</h4>
                            <small class="text-muted">Sekali Bayar</small>
                        </div>

                        <!-- Informasi Rekening Pembayaran -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-university me-2"></i>Informasi Rekening Pembayaran
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Bank</label>
                                            <p class="mb-0">BANK CIMB NIAGA</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nomor Rekening</label>
                                            <p class="mb-0">
                                                <code class="fs-5">763527686800</code>
                                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('763527686800')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Atas Nama</label>
                                            <p class="mb-0">AGUS MUNIF</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Instruksi Pembayaran:</strong>
                                    <ol class="mb-0 mt-2">
                                        <li>Transfer sesuai nominal ke rekening di atas</li>
                                        <li>Simpan bukti transfer</li>
                                        <li>Konfirmasi pembayaran melalui WhatsApp atau email</li>
                                        <li>Addon akan diaktifkan setelah konfirmasi</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <select class="form-select" disabled>
                                <option value="manual_transfer" selected>Transfer Bank Manual</option>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Metode pembayaran lainnya sedang dalam pengembangan
                            </div>
                        </div>

                        <div class="d-grid">
                            <a href="https://wa.me/6282188497818?text=Halo,%20saya%20ingin%20membeli%20addon%20SPMB%20dengan%20harga%20Rp%20199.000.%20Mohon%20informasi%20lebih%20lanjut%20untuk%20proses%20pembayaran." 
                               class="btn btn-success btn-lg text-white" 
                               target="_blank">
                                <i class="fab fa-whatsapp me-2 text-white"></i>
                                Beli Sekarang via WhatsApp
                            </a>
                        </div>

                        <hr>

                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Pembayaran aman dengan SSL encryption
                            </small>
                        </div>
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

<script>
function copyToClipboard(text) {
    // Try modern clipboard API first
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            console.error('Clipboard API failed:', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        // Fallback for older browsers or non-secure contexts
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    
    // Avoid scrolling to bottom
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess();
        } else {
            showCopyError();
        }
    } catch (err) {
        console.error('Fallback copy failed:', err);
        showCopyError();
    }
    
    document.body.removeChild(textArea);
}

function showCopySuccess() {
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.remove('btn-outline-secondary');
    button.classList.add('btn-success');
    
    // Reset after 2 seconds
    setTimeout(function() {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

function showCopyError() {
    alert('Menyalin nomor rekening berhasil!');
}
</script>
@endsection
