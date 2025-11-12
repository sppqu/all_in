<?php $__env->startSection('title', 'Pembayaran Online'); ?>

<style>
/* Action Button Icon Colors - Ensure white icons */
.btn-success .fas,
.btn-success .fa {
    color: white !important;
}

.btn-primary .fas,
.btn-primary .fa {
    color: white !important;
}

.btn-warning .fas,
.btn-warning .fa {
    color: white !important;
}

.btn-danger .fas,
.btn-danger .fa {
    color: white !important;
}

.btn-info .fas,
.btn-info .fa {
    color: white !important;
}
</style>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Pembayaran Online
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle me-2"></i>Informasi Pembayaran Online</h5>
                                <p class="mb-0">
                                    Sistem pembayaran online memungkinkan siswa dan orang tua untuk melakukan pembayaran 
                                    SPP dan tagihan lainnya secara online melalui berbagai metode pembayaran yang tersedia.
                                </p>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <i class="fas fa-history fa-3x text-success mb-3"></i>
                                            <h5>Riwayat Pembayaran</h5>
                                            <p>Lihat riwayat pembayaran online yang telah dilakukan</p>
                                            <a href="<?php echo e(route('online-payment.history')); ?>" class="btn btn-success" style="color: white;">
                                                <i class="fas fa-history me-2" style="color: white;"></i>Lihat Riwayat
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h5><i class="fas fa-credit-card me-2"></i>Metode Pembayaran yang Tersedia</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-university fa-2x text-primary mb-2"></i>
                                                <h6>Transfer Bank</h6>
                                                <small class="text-muted">BCA, BNI, Mandiri, BRI</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-credit-card fa-2x text-success mb-2"></i>
                                                <h6>Kartu Kredit</h6>
                                                <small class="text-muted">Visa, Mastercard, JCB</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-mobile-alt fa-2x text-warning mb-2"></i>
                                                <h6>E-Wallet</h6>
                                                <small class="text-muted">OVO, DANA, GoPay, LinkAja</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik Pembayaran</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="text-primary"><?php echo e($totalPayments ?? 0); ?></h4>
                                                <small class="text-muted">Total Pembayaran</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-success"><?php echo e($successPayments ?? 0); ?></h4>
                                            <small class="text-muted">Berhasil</small>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="text-warning"><?php echo e($pendingPayments ?? 0); ?></h4>
                                                <small class="text-muted">Menunggu</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-danger"><?php echo e($failedPayments ?? 0); ?></h4>
                                            <small class="text-muted">Gagal</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Penting</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Pembayaran diproses secara real-time
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Bukti pembayaran otomatis dikirim
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Sistem keamanan terjamin
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Support 24/7 untuk bantuan
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Animasi untuk card
    $('.card').hover(
        function() {
            $(this).addClass('shadow-sm');
        },
        function() {
            $(this).removeClass('shadow-sm');
        }
    );
});
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.coreui', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/online-payment/index.blade.php ENDPATH**/ ?>