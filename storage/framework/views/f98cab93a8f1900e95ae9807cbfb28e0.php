<?php $__env->startSection('title', 'Riwayat Pembayaran'); ?>

<?php $__env->startSection('content'); ?>
<!-- Midtrans Snap JavaScript -->

<div class="container-fluid">
    
    <!-- Filter Section -->
    <div class="card mb-4" style="border-radius: 15px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="viewType" id="perKuitansi" value="kuitansi" <?php echo e(request('view', 'kuitansi') == 'kuitansi' ? 'checked' : ''); ?> onchange="switchViewType('kuitansi')">
                        <label class="form-check-label text-muted" for="perKuitansi">
                            PerKuitansi
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="viewType" id="perItem" value="item" <?php echo e(request('view', 'kuitansi') == 'item' ? 'checked' : ''); ?> onchange="switchViewType('item')">
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
                            <?php if($viewType == 'kuitansi'): ?>
                                <?php echo e($payments->count()); ?> Kuitansi Terakhir
                            <?php else: ?>
                                <?php echo e($payments->count()); ?> Item Terakhir
                            <?php endif; ?>
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="row">
        <div class="col-12">
            <?php if($payments->count() > 0): ?>
                <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="card border-0 shadow-sm mb-3 transaction-card" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <!-- Left Column - Payment Details -->
                            <div class="flex-grow-1">
                                <?php if($viewType == 'kuitansi'): ?>
                                    <h6 class="mb-1 fw-bold text-dark payment-title">
                                        <?php if(isset($payment->reference) && $payment->reference): ?>
                                            <?php echo e($payment->reference); ?>

                                        <?php else: ?>
                                            TF-<?php echo e(date('Ymd', strtotime($payment->payment_date))); ?>

                                        <?php endif; ?>
                                    </h6>
                                    <p class="mb-0 text-muted payment-date">
                                        <?php echo e(\Carbon\Carbon::parse($payment->payment_date)->format('d M Y')); ?>

                                    </p>
                                <?php else: ?>
                                    <h6 class="mb-1 fw-bold text-dark payment-title">
                                        <?php echo e($payment->display_name); ?>

                                    </h6>
                                    <p class="mb-0 text-muted payment-date">
                                        <?php echo e(\Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y H:i')); ?>

                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Right Column - Amount & Method -->
                            <div class="text-end">
                                <div class="fw-bold payment-amount">
                                    Rp <?php echo e(number_format($payment->amount, 0, ',', '.')); ?>

                                </div>
                                <?php if($viewType == 'kuitansi'): ?>
                                    <div class="mt-1">
                                        <?php if($payment->transaction_type === 'ONLINE_PENDING'): ?>
                                            <span class="badge bg-info bg-opacity-10 text-info payment-method">
                                                <i class="fas fa-globe me-1"></i>Online
                                            </span>
                                            <?php if(isset($payment->is_expired) && $payment->is_expired): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger ms-1">
                                                    <i class="fas fa-times-circle me-1"></i>Kadaluarsa
                                                </span>
                                            <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning ms-1">
                                                <i class="fas fa-clock me-1"></i>Menunggu Pembayaran
                                            </span>
                                            <?php endif; ?>
                                        <?php elseif($payment->transaction_type === 'BANK_TRANSFER'): ?>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-info bg-opacity-10 text-info payment-method mb-1">
                                                    <i class="fas fa-university me-1"></i>Transfer Bank
                                                </span>
                                                <?php if($payment->status == 0): ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                                        <i class="fas fa-clock me-1"></i>Menunggu Persetujuan Admin
                                                    </span>
                                                    <small class="text-muted mt-1">
                                                        <i class="fas fa-info-circle me-1"></i>Bukti transfer sedang diverifikasi admin
                                                    </small>
                                                <?php elseif($payment->status == 1): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-check me-1"></i>Berhasil
                                                    </span>
                                                <?php elseif($payment->status == 2): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                                        <i class="fas fa-times me-1"></i>Ditolak
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif($payment->transaction_type === 'TUNAI'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success payment-method">
                                                Sukses
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2">
                                        <?php if($payment->status == 0): ?>
                                            <!-- Check if payment is expired -->
                                            <?php if(isset($payment->is_expired) && $payment->is_expired): ?>
                                                <small class="text-danger d-block">
                                                    <i class="fas fa-info-circle me-1"></i>Link pembayaran sudah kadaluarsa. Silakan buat pembayaran baru.
                                                </small>
                                            <?php else: ?>
                                                <!-- Pembayaran pending - tampilkan tombol Bayar Sekarang untuk online payment -->
                                                <?php
                                                    // Simplified logic: Show button for online payments (ONLINE_PENDING type or has payment_method)
                                                    $isOnlinePayment = $payment->transaction_type === 'ONLINE_PENDING' || 
                                                                      !empty($payment->payment_method) ||
                                                                      !empty($payment->reference) ||
                                                                      !empty($payment->merchantRef);
                                                    
                                                    $paymentUrl = null;
                                                    
                                                    if ($isOnlinePayment) {
                                                        // Try to get payment URL from checkout_url first
                                                        $paymentUrl = $payment->checkout_url ?? null;
                                                        
                                                        // If no checkout_url, try to get from payment_details
                                                        if (!$paymentUrl && !empty($payment->payment_details)) {
                                                            $paymentDetails = is_string($payment->payment_details) 
                                                                ? json_decode($payment->payment_details, true) 
                                                                : $payment->payment_details;
                                                            $paymentUrl = $paymentDetails['payment_url'] ?? 
                                                                         $paymentDetails['redirect_url'] ?? null;
                                                        }
                                                        
                                                        // If still no URL but has transfer_id, create a check status page URL
                                                        if (!$paymentUrl && isset($payment->transfer_id)) {
                                                            // For now, show message to contact admin or create route for check status
                                                            $paymentUrl = '#'; // Placeholder
                                                        }
                                                    }
                                                ?>
                                                
                                                <?php if($isOnlinePayment): ?>
                                                    <?php if($paymentUrl && $paymentUrl !== '#'): ?>
                                                        <a href="<?php echo e($paymentUrl); ?>" class="btn btn-primary btn-sm" target="_blank">
                                                            <i class="fas fa-credit-card me-1"></i>Bayar Sekarang
                                                        </a>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-warning btn-sm" onclick="alert('Link pembayaran sedang diproses. Silakan refresh halaman dalam beberapa saat.')">
                                                            <i class="fas fa-hourglass-half me-1"></i>Menunggu Link
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- Pembayaran sukses - tampilkan "Detail" -->
                                            <?php if(isset($payment->receipt_id) && $payment->receipt_id): ?>
                                                <a href="<?php echo e(route('student.receipt.detail', ['id' => $payment->receipt_id, 'type' => 'cash'])); ?>" class="btn btn-success btn-sm">Detail</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-1">
                                        <?php if($payment->transaction_type === 'CASH_BULANAN' || $payment->transaction_type === 'CASH_BEBAS'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success payment-method">
                                                <i class="fas fa-check me-1"></i>Sukses
                                            </span>
                                        <?php elseif($payment->transaction_type === 'BANK_TRANSFER'): ?>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-info bg-opacity-10 text-info payment-method mb-1">
                                                    <i class="fas fa-university me-1"></i>Transfer Bank
                                                </span>
                                                <?php if($payment->status == 0): ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                                        <i class="fas fa-clock me-1"></i>Menunggu Persetujuan Admin
                                                    </span>
                                                    <small class="text-muted mt-1">
                                                        <i class="fas fa-info-circle me-1"></i>Bukti transfer sedang diverifikasi admin
                                                    </small>
                                                <?php elseif($payment->status == 1): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-check me-1"></i>Berhasil
                                                    </span>
                                                <?php elseif($payment->status == 2): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                                        <i class="fas fa-times me-1"></i>Ditolak
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif($payment->transaction_type === 'TABUNGAN'): ?>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-primary bg-opacity-10 text-primary payment-method mb-1">
                                                    <i class="fas fa-piggy-bank me-1"></i>Tabungan
                                                </span>
                                                <?php if($payment->status == 0): ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                                        <i class="fas fa-clock me-1"></i>Menunggu Verifikasi
                                                    </span>
                                                    <small class="text-muted mt-1">
                                                        <i class="fas fa-info-circle me-1"></i>Setoran tabungan sedang diverifikasi admin
                                                    </small>
                                                <?php elseif($payment->status == 1): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-check me-1"></i>Berhasil
                                                    </span>
                                                <?php elseif($payment->status == 2): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                                        <i class="fas fa-times me-1"></i>Ditolak
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif($payment->status == 0): ?>
                                            <?php if(isset($payment->is_expired) && $payment->is_expired): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger payment-method">
                                                    <i class="fas fa-times-circle me-1"></i>Kadaluarsa
                                                </span>
                                                <div class="mt-1">
                                                    <small class="text-danger">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>Link pembayaran sudah kadaluarsa
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning payment-method">
                                                <i class="fas fa-clock me-1"></i>Menunggu Verifikasi
                                            </span>
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>Pembayaran sedang diverifikasi admin
                                                </small>
                                            </div>
                                            <?php endif; ?>
                                        <?php elseif($payment->status == 2): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger payment-method">
                                                <i class="fas fa-times me-1"></i>Ditolak
                                            </span>
                                            <div class="mt-1">
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Pembayaran ditolak, silakan coba lagi
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success payment-method">
                                                <i class="fas fa-check me-1"></i>Diterima
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($viewType == 'kuitansi'): ?>
                                        <?php if($payment->status == 0): ?>
                                            <?php if(isset($payment->is_expired) && $payment->is_expired): ?>
                                                <div class="mt-2">
                                                    <small class="text-danger d-block">
                                                        <i class="fas fa-info-circle me-1"></i>Link pembayaran sudah kadaluarsa
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <!-- Pembayaran pending - tampilkan tombol Bayar Sekarang untuk online payment -->
                                                <?php
                                                    // Simplified logic: Show button for online payments
                                                    $isOnlinePayment = $payment->transaction_type === 'ONLINE_PENDING' || 
                                                                      !empty($payment->payment_method) ||
                                                                      !empty($payment->reference) ||
                                                                      !empty($payment->merchantRef);
                                                    
                                                    $paymentUrl = $payment->checkout_url ?? null;
                                                    
                                                    if ($isOnlinePayment && !$paymentUrl && !empty($payment->payment_details)) {
                                                        $paymentDetails = is_string($payment->payment_details) 
                                                            ? json_decode($payment->payment_details, true) 
                                                            : $payment->payment_details;
                                                        $paymentUrl = $paymentDetails['payment_url'] ?? 
                                                                     $paymentDetails['redirect_url'] ?? null;
                                                    }
                                                    
                                                    if (!$paymentUrl && isset($payment->transfer_id)) {
                                                        $paymentUrl = '#';
                                                    }
                                                ?>
                                                
                                                <?php if($isOnlinePayment): ?>
                                                    <div class="mt-2">
                                                        <?php if($paymentUrl && $paymentUrl !== '#'): ?>
                                                            <a href="<?php echo e($paymentUrl); ?>" class="btn btn-primary btn-sm" target="_blank">
                                                                <i class="fas fa-credit-card me-1"></i>Bayar Sekarang
                                                            </a>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-warning btn-sm" onclick="alert('Link pembayaran sedang diproses. Silakan refresh halaman.')">
                                                                <i class="fas fa-hourglass-half me-1"></i>Menunggu Link
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- Pembayaran sukses - tampilkan "Detail" -->
                                            <div class="mt-2">
                                                <?php if(isset($payment->receipt_id) && $payment->receipt_id): ?>
                                                    <a href="<?php echo e(route('student.receipt.detail', ['id' => $payment->receipt_id, 'type' => 'cash'])); ?>" class="btn btn-success btn-sm">Detail</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- Per Item view - tampilkan untuk pending (online payment) dan sukses -->
                                        <?php if($payment->status == 0): ?>
                                            <?php if(isset($payment->is_expired) && $payment->is_expired): ?>
                                                <div class="mt-2">
                                                    <small class="text-danger d-block">
                                                        <i class="fas fa-info-circle me-1"></i>Link pembayaran sudah kadaluarsa
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <!-- Pembayaran pending online payment -->
                                                <?php
                                                    // Simplified logic: Show button for online payments
                                                    $isOnlinePayment = !empty($payment->payment_method) ||
                                                                      !empty($payment->reference) ||
                                                                      !empty($payment->merchantRef);
                                                    
                                                    $paymentUrl = $payment->checkout_url ?? null;
                                                    
                                                    if ($isOnlinePayment && !$paymentUrl && !empty($payment->payment_details)) {
                                                        $paymentDetails = is_string($payment->payment_details) 
                                                            ? json_decode($payment->payment_details, true) 
                                                            : $payment->payment_details;
                                                        $paymentUrl = $paymentDetails['payment_url'] ?? 
                                                                     $paymentDetails['redirect_url'] ?? null;
                                                    }
                                                    
                                                    if (!$paymentUrl && isset($payment->transfer_id)) {
                                                        $paymentUrl = '#';
                                                    }
                                                ?>
                                                
                                                <?php if($isOnlinePayment): ?>
                                                    <div class="mt-2">
                                                        <?php if($paymentUrl && $paymentUrl !== '#'): ?>
                                                            <a href="<?php echo e($paymentUrl); ?>" class="btn btn-primary btn-sm" target="_blank">
                                                                <i class="fas fa-credit-card me-1"></i>Bayar Sekarang
                                                            </a>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-warning btn-sm" onclick="alert('Link pembayaran sedang diproses. Silakan refresh halaman.')">
                                                                <i class="fas fa-hourglass-half me-1"></i>Menunggu Link
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php elseif($payment->status == 1 || $payment->transaction_type === 'CASH_BULANAN' || $payment->transaction_type === 'CASH_BEBAS'): ?>
                                            <!-- Pembayaran sukses -->
                                            <div class="mt-2">
                                                <?php if(isset($payment->receipt_id) && $payment->receipt_id): ?>
                                                    <a href="<?php echo e(route('student.receipt.detail', ['id' => $payment->receipt_id, 'type' => 'cash'])); ?>" class="btn btn-success btn-sm">Detail</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-history text-muted fa-2x"></i>
                        </div>
                        <h6 class="text-muted">Belum ada riwayat pembayaran</h6>
                        <p class="text-muted mb-0">Riwayat pembayaran online akan muncul di sini</p>
                    </div>
                </div>
            <?php endif; ?>
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
                <form id="filterForm" method="GET" action="<?php echo e(route('student.payment.history')); ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Periode</label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo e(request('start_date')); ?>">
                    </div>

                    <div class="mb-4">
                        <label for="end_date" class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo e(request('end_date')); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100" style="border-radius: 10px;">
                        <i class="fas fa-check me-2"></i>Terapkan Filter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.student', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/student/payment-history.blade.php ENDPATH**/ ?>