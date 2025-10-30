@extends('layouts.student')

@section('title', 'Riwayat Pembayaran')

@section('content')
<!-- Midtrans Snap JavaScript -->

<div class="container-fluid">
    
    <!-- Filter Section -->
    <div class="card mb-4" style="border-radius: 15px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="viewType" id="perKuitansi" value="kuitansi" {{ request('view', 'kuitansi') == 'kuitansi' ? 'checked' : '' }} onchange="switchViewType('kuitansi')">
                        <label class="form-check-label text-muted" for="perKuitansi">
                            PerKuitansi
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="viewType" id="perItem" value="item" {{ request('view', 'kuitansi') == 'item' ? 'checked' : '' }} onchange="switchViewType('item')">
                        <label class="form-check-label text-muted" for="perItem">
                            PerItem
                        </label>
                    </div>
                </div>
                <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            
            <!-- Summary Bar -->
            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                <div class="d-flex justify-content-center align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold text-success">
                            @if($viewType == 'kuitansi')
                                {{ $payments->count() }} Kuitansi Terakhir
                            @else
                                {{ $payments->count() }} Item Terakhir
                            @endif
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="row">
        <div class="col-12">
            @if($payments->count() > 0)
                @foreach($payments as $payment)
                <div class="card border-0 shadow-sm mb-3 transaction-card" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <!-- Left Column - Payment Details -->
                            <div class="flex-grow-1">
                                @if($viewType == 'kuitansi')
                                    <h6 class="mb-1 fw-bold text-dark payment-title">
                                        @if(isset($payment->reference) && $payment->reference)
                                            {{ $payment->reference }}
                                        @else
                                            TF-{{ date('Ymd', strtotime($payment->payment_date)) }}
                                        @endif
                                    </h6>
                                    <p class="mb-0 text-muted payment-date">
                                        {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                    </p>
                                @else
                                    <h6 class="mb-1 fw-bold text-dark payment-title">
                                        {{ $payment->display_name }}
                                    </h6>
                                    <p class="mb-0 text-muted payment-date">
                                        {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                            
                            <!-- Right Column - Amount & Method -->
                            <div class="text-end">
                                <div class="fw-bold payment-amount">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </div>
                                @if($viewType == 'kuitansi')
                                    <div class="mt-1">
                                        @if($payment->transaction_type === 'ONLINE_PENDING')
                                            <span class="badge bg-info bg-opacity-10 text-info payment-method">
                                                <i class="fas fa-globe me-1"></i>Online
                                            </span>
                                            @if(isset($payment->is_expired) && $payment->is_expired)
                                                <span class="badge bg-danger bg-opacity-10 text-danger ms-1">
                                                    <i class="fas fa-times-circle me-1"></i>Kadaluarsa
                                                </span>
                                            @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning ms-1">
                                                <i class="fas fa-clock me-1"></i>Menunggu Pembayaran
                                            </span>
                                            @endif
                                        @elseif($payment->transaction_type === 'BANK_TRANSFER')
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-info bg-opacity-10 text-info payment-method mb-1">
                                                    <i class="fas fa-university me-1"></i>Transfer Bank
                                                </span>
                                                @if($payment->status == 0)
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                                        <i class="fas fa-clock me-1"></i>Menunggu Persetujuan Admin
                                                    </span>
                                                    <small class="text-muted mt-1">
                                                        <i class="fas fa-info-circle me-1"></i>Bukti transfer sedang diverifikasi admin
                                                    </small>
                                                @elseif($payment->status == 1)
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-check me-1"></i>Berhasil
                                                    </span>
                                                @elseif($payment->status == 2)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                                        <i class="fas fa-times me-1"></i>Ditolak
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif($payment->transaction_type === 'TUNAI')
                                            <span class="badge bg-success bg-opacity-10 text-success payment-method">
                                                Sukses
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-2">
                                        @if($payment->status == 0)
                                            <!-- Check if payment is expired -->
                                            @if(isset($payment->is_expired) && $payment->is_expired)
                                                <small class="text-danger d-block">
                                                    <i class="fas fa-info-circle me-1"></i>Link pembayaran sudah kadaluarsa. Silakan buat pembayaran baru.
                                                </small>
                                            @else
                                                <!-- Pembayaran pending - tampilkan tombol Bayar Sekarang untuk online payment -->
                                                @php
                                                    // Check if this is an online payment (ipaymu, gateway, tripay, etc)
                                                    $isOnlinePayment = isset($payment->payment_method) && 
                                                        in_array($payment->payment_method, ['ipaymu', 'gateway', 'tripay', 'midtrans', 'duitku']);
                                                    
                                                    // Or check if has reference/merchantRef (online transaction identifier)
                                                    $hasReference = !empty($payment->reference) || !empty($payment->merchantRef);
                                                    
                                                    $showPayButton = $isOnlinePayment || $hasReference;
                                                    $paymentUrl = null;
                                                    
                                                    if ($showPayButton) {
                                                        $paymentDetails = json_decode($payment->payment_details ?? '{}', true);
                                                        $ipaymuData = $paymentDetails['ipaymu_response'] ?? $paymentDetails;
                                                        $paymentUrl = $ipaymuData['payment_url'] ?? null;
                                                        $paymentNo = $ipaymuData['payment_no'] ?? $ipaymuData['PaymentNo'] ?? null;
                                                        
                                                        // If no payment URL but has VA number or reference, create instruction page link
                                                        if (!$paymentUrl && ($paymentNo || $hasReference)) {
                                                            $paymentUrl = route('student.payment.va-instructions', [
                                                                'transfer_id' => $payment->transfer_id ?? $payment->id,
                                                                'reference' => $payment->reference ?? $payment->merchantRef
                                                            ]);
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($showPayButton && $paymentUrl)
                                                    <a href="{{ $paymentUrl }}" class="btn btn-primary btn-sm" target="_blank">
                                                        <i class="fas fa-credit-card me-1"></i>Bayar Sekarang
                                                    </a>
                                                @endif
                                            @endif
                                        @else
                                            <!-- Pembayaran sukses - tampilkan "Detail" -->
                                            @if(isset($payment->receipt_id) && $payment->receipt_id)
                                                <a href="{{ route('student.receipt.detail', ['id' => $payment->receipt_id, 'type' => 'cash']) }}" class="btn btn-success btn-sm">Detail</a>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <div class="mt-1">
                                        @if($payment->transaction_type === 'CASH_BULANAN' || $payment->transaction_type === 'CASH_BEBAS')
                                            <span class="badge bg-success bg-opacity-10 text-success payment-method">
                                                <i class="fas fa-check me-1"></i>Sukses
                                            </span>
                                        @elseif($payment->transaction_type === 'BANK_TRANSFER')
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-info bg-opacity-10 text-info payment-method mb-1">
                                                    <i class="fas fa-university me-1"></i>Transfer Bank
                                                </span>
                                                @if($payment->status == 0)
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                                        <i class="fas fa-clock me-1"></i>Menunggu Persetujuan Admin
                                                    </span>
                                                    <small class="text-muted mt-1">
                                                        <i class="fas fa-info-circle me-1"></i>Bukti transfer sedang diverifikasi admin
                                                    </small>
                                                @elseif($payment->status == 1)
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-check me-1"></i>Berhasil
                                                    </span>
                                                @elseif($payment->status == 2)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                                        <i class="fas fa-times me-1"></i>Ditolak
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif($payment->transaction_type === 'TABUNGAN')
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-primary bg-opacity-10 text-primary payment-method mb-1">
                                                    <i class="fas fa-piggy-bank me-1"></i>Tabungan
                                                </span>
                                                @if($payment->status == 0)
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                                        <i class="fas fa-clock me-1"></i>Menunggu Verifikasi
                                                    </span>
                                                    <small class="text-muted mt-1">
                                                        <i class="fas fa-info-circle me-1"></i>Setoran tabungan sedang diverifikasi admin
                                                    </small>
                                                @elseif($payment->status == 1)
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-check me-1"></i>Berhasil
                                                    </span>
                                                @elseif($payment->status == 2)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                                        <i class="fas fa-times me-1"></i>Ditolak
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif($payment->status == 0)
                                            @if(isset($payment->is_expired) && $payment->is_expired)
                                                <span class="badge bg-danger bg-opacity-10 text-danger payment-method">
                                                    <i class="fas fa-times-circle me-1"></i>Kadaluarsa
                                                </span>
                                                <div class="mt-1">
                                                    <small class="text-danger">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>Link pembayaran sudah kadaluarsa
                                                    </small>
                                                </div>
                                            @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning payment-method">
                                                <i class="fas fa-clock me-1"></i>Menunggu Verifikasi
                                            </span>
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>Pembayaran sedang diverifikasi admin
                                                </small>
                                            </div>
                                            @endif
                                        @elseif($payment->status == 2)
                                            <span class="badge bg-danger bg-opacity-10 text-danger payment-method">
                                                <i class="fas fa-times me-1"></i>Ditolak
                                            </span>
                                            <div class="mt-1">
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Pembayaran ditolak, silakan coba lagi
                                                </small>
                                            </div>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success payment-method">
                                                <i class="fas fa-check me-1"></i>Diterima
                                            </span>
                                        @endif
                                    </div>
                                    @if($viewType == 'kuitansi')
                                        @if($payment->status == 0)
                                            @if(isset($payment->is_expired) && $payment->is_expired)
                                                <div class="mt-2">
                                                    <small class="text-danger d-block">
                                                        <i class="fas fa-info-circle me-1"></i>Link pembayaran sudah kadaluarsa
                                                    </small>
                                                </div>
                                            @else
                                                <!-- Pembayaran pending - tampilkan tombol Bayar Sekarang untuk online payment -->
                                                @php
                                                    // Check if this is an online payment
                                                    $isOnlinePayment = isset($payment->payment_method) && 
                                                        in_array($payment->payment_method, ['ipaymu', 'gateway', 'tripay', 'midtrans', 'duitku']);
                                                    
                                                    // Or check if has reference/merchantRef
                                                    $hasReference = !empty($payment->reference) || !empty($payment->merchantRef);
                                                    
                                                    $showPayButton = $isOnlinePayment || $hasReference;
                                                    $paymentUrl = null;
                                                    
                                                    if ($showPayButton) {
                                                        $paymentDetails = json_decode($payment->payment_details ?? '{}', true);
                                                        $ipaymuData = $paymentDetails['ipaymu_response'] ?? $paymentDetails;
                                                        $paymentUrl = $ipaymuData['payment_url'] ?? null;
                                                        $paymentNo = $ipaymuData['payment_no'] ?? $ipaymuData['PaymentNo'] ?? null;
                                                        
                                                        // If no payment URL but has VA number or reference
                                                        if (!$paymentUrl && ($paymentNo || $hasReference)) {
                                                            $paymentUrl = route('student.payment.va-instructions', [
                                                                'transfer_id' => $payment->transfer_id ?? $payment->id,
                                                                'reference' => $payment->reference ?? $payment->merchantRef
                                                            ]);
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($showPayButton && $paymentUrl)
                                                    <div class="mt-2">
                                                        <a href="{{ $paymentUrl }}" class="btn btn-primary btn-sm" target="_blank">
                                                            <i class="fas fa-credit-card me-1"></i>Bayar Sekarang
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                        @else
                                            <!-- Pembayaran sukses - tampilkan "Detail" -->
                                            <div class="mt-2">
                                                @if(isset($payment->receipt_id) && $payment->receipt_id)
                                                    <a href="{{ route('student.receipt.detail', ['id' => $payment->receipt_id, 'type' => 'cash']) }}" class="btn btn-success btn-sm">Detail</a>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <!-- Per Item view - tampilkan untuk pending (online payment) dan sukses -->
                                        @if($payment->status == 0)
                                            @if(isset($payment->is_expired) && $payment->is_expired)
                                                <div class="mt-2">
                                                    <small class="text-danger d-block">
                                                        <i class="fas fa-info-circle me-1"></i>Link pembayaran sudah kadaluarsa
                                                    </small>
                                                </div>
                                            @else
                                                <!-- Pembayaran pending online payment -->
                                                @php
                                                    // Check if this is an online payment
                                                    $isOnlinePayment = isset($payment->payment_method) && 
                                                        in_array($payment->payment_method, ['ipaymu', 'gateway', 'tripay', 'midtrans', 'duitku']);
                                                    
                                                    // Or check if has reference/merchantRef
                                                    $hasReference = !empty($payment->reference) || !empty($payment->merchantRef);
                                                    
                                                    $showPayButton = $isOnlinePayment || $hasReference;
                                                    $paymentUrl = null;
                                                    
                                                    if ($showPayButton) {
                                                        $paymentDetails = json_decode($payment->payment_details ?? '{}', true);
                                                        $ipaymuData = $paymentDetails['ipaymu_response'] ?? $paymentDetails;
                                                        $paymentUrl = $ipaymuData['payment_url'] ?? null;
                                                        $paymentNo = $ipaymuData['payment_no'] ?? $ipaymuData['PaymentNo'] ?? null;
                                                        
                                                        // If no payment URL but has VA number or reference
                                                        if (!$paymentUrl && ($paymentNo || $hasReference)) {
                                                            $paymentUrl = route('student.payment.va-instructions', [
                                                                'transfer_id' => $payment->transfer_id ?? $payment->id,
                                                                'reference' => $payment->reference ?? $payment->merchantRef
                                                            ]);
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($showPayButton && $paymentUrl)
                                                    <div class="mt-2">
                                                        <a href="{{ $paymentUrl }}" class="btn btn-primary btn-sm" target="_blank">
                                                            <i class="fas fa-credit-card me-1"></i>Bayar Sekarang
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                        @elseif($payment->status == 1 || $payment->transaction_type === 'CASH_BULANAN' || $payment->transaction_type === 'CASH_BEBAS')
                                            <!-- Pembayaran sukses -->
                                            <div class="mt-2">
                                                @if(isset($payment->receipt_id) && $payment->receipt_id)
                                                    <a href="{{ route('student.receipt.detail', ['id' => $payment->receipt_id, 'type' => 'cash']) }}" class="btn btn-success btn-sm">Detail</a>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-history text-muted fa-2x"></i>
                        </div>
                        <h6 class="text-muted">Belum ada riwayat pembayaran</h6>
                        <p class="text-muted mb-0">Riwayat pembayaran online akan muncul di sini</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<style>
@media (max-width: 768px) {
    .container-fluid {
        padding: 12px;
    }
    
    .card {
        border-radius: 10px;
    }
    
    /* Mobile transaction card */
    .transaction-card {
        margin-bottom: 8px;
    }
    
    /* Mobile status icon */
    .status-icon {
        width: 35px;
        height: 35px;
    }
    
    .status-icon i {
        font-size: 0.9rem;
    }
    
    /* Mobile payment title */
    .payment-title {
        font-size: 0.8rem;
    }
    
    /* Mobile payment date */
    .payment-date {
        font-size: 0.7rem;
    }
    
    /* Mobile payment amount */
    .payment-amount {
        font-size: 0.8rem;
    }
    
    /* Mobile payment method badge */
    .payment-method {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
    }
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.bg-opacity-10 {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

/* Transaction card styling */
.transaction-card {
    border: 1px solid #e9ecef;
    margin-bottom: 12px;
}

.transaction-card:not(:last-child) {
    border-bottom: 1px solid #e9ecef;
}

/* Status icon */
.status-icon {
    width: 40px;
    height: 40px;
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.status-icon i {
    font-size: 1rem;
    color: #198754;
}

/* Payment title */
.payment-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 4px;
}

/* Payment date */
.payment-date {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0;
}

/* Payment amount */
.payment-amount {
    font-size: 0.85rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 4px;
}

/* Payment method badge */
.payment-method {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    background-color: rgba(40, 167, 69, 0.1) !important;
    color: #198754 !important;
    border-radius: 6px;
}


/* Global font size reduction for mobile */
@media (max-width: 768px) {
    .card-body {
        padding: 0.75rem;
    }
    
    .card-body h6 {
        font-size: 0.85rem !important;
    }
    
    .card-body small {
        font-size: 0.7rem !important;
    }
    
    .card-body p {
        font-size: 0.75rem !important;
    }
}

/* Custom green theme for radio buttons */
.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.form-check-input:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}

.form-check-input:checked:focus {
    background-color: #198754;
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}
</style>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="filterModalLabel">
                    <i class="fas fa-filter me-2"></i>Filter Riwayat Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm" method="GET" action="{{ route('student.payment.history') }}">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Periode</label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                    </div>

                    <div class="mb-4">
                        <label for="end_date" class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100" style="border-radius: 10px;">
                        <i class="fas fa-check me-2"></i>Terapkan Filter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function switchViewType(type) {
        // Get current URL and parameters
        const url = new URL(window.location);
        url.searchParams.set('view', type);
        
        // Redirect to new URL
        window.location.href = url.toString();
    }
    

    
    // Clear cart from localStorage if payment was successful
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there's a success message (indicating successful payment)
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            // Clear cart from localStorage
            localStorage.removeItem('studentCart');
            
            // Update cart badge if function exists
            if (typeof updateCartBadge === 'function') {
                updateCartBadge();
            }
        }
        
        // Check URL parameters for payment status (Tripay callback)
        const urlParams = new URLSearchParams(window.location.search);
        const paymentStatus = urlParams.get('status');
        if (paymentStatus === 'PAID' || paymentStatus === 'success') {
            // Clear cart from localStorage after successful Tripay payment
            localStorage.removeItem('studentCart');
            
            // Update cart badge if function exists
            if (typeof updateCartBadge === 'function') {
                updateCartBadge();
            }
        }
    });
</script>
@endpush 