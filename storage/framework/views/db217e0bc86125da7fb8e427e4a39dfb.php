<?php $__env->startSection('title', 'Berlangganan Saya - SPPQU'); ?>

<?php $__env->startSection('active_menu', 'menu.billing'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('manage.admin.dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Berlangganan</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-crown me-2"></i>
                    Berlangganan Admin
                </h4>
            </div>
        </div>
    </div>

    <?php if(request('status') == 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Berhasil!</strong> Pembayaran berhasil diproses. Berlangganan Anda telah aktif.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if(request('status') == 'pending'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-clock me-2"></i>
        <strong>Menunggu!</strong> Pembayaran Anda sedang diproses. Mohon tunggu konfirmasi.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if(request('status') == 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Gagal!</strong> Terjadi kesalahan dalam pembayaran. Silakan coba lagi.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Current Subscription Status -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Status Berlangganan
                    </h5>
                </div>
                <div class="card-body">
                    <?php if($activeSubscription && $activeSubscription->status == 'active'): ?>
                        <div class="text-center mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-check text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="text-success mb-1">Berlangganan Aktif</h5>
                            <p class="text-muted mb-0"><?php echo e($activeSubscription->plan_name); ?></p>
                            <small class="text-muted">Oleh: <?php echo e($activeSubscription->user->name ?? 'Admin'); ?></small>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Berakhir:</strong><br>
                            <span class="text-primary"><?php echo e(\Carbon\Carbon::parse($activeSubscription->expires_at)->format('d M Y H:i')); ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Sisa Waktu:</strong><br>
                            <span class="text-info"><?php echo e(\Carbon\Carbon::now()->diffForHumans($activeSubscription->expires_at, ['parts' => 2])); ?></span>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-outline-danger" onclick="cancelSubscription(<?php echo e($activeSubscription->id); ?>)">
                                <i class="fas fa-times me-2"></i>
                                Batalkan Berlangganan
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="text-center mb-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="text-warning mb-1">Tidak Ada Berlangganan Aktif</h5>
                            <p class="text-muted mb-0">Tidak ada admin yang berlangganan aktif</p>
                        </div>

                        <div class="d-grid">
                            <a href="<?php echo e(route('manage.subscription.plans')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Berlangganan Sekarang
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-3">
                <div class="d-grid">
                    <a href="<?php echo e(route('manage.addons.index')); ?>" class="btn btn-primary">
                        <i class="fas fa-puzzle-piece me-2"></i>Add-ons Premium
                    </a>
                </div>
            </div>
        </div>

        <!-- Billing History dengan Tab -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        Riwayat Billing & Pembelian
                    </h5>
                </div>
                <div class="card-body p-0">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs px-3 pt-3" id="billingTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="subscription-tab" data-toggle="tab" data-target="#subscription-content" type="button" role="tab">
                                <i class="fas fa-sync-alt me-2"></i>
                                Berlangganan Bulanan
                                <span class="badge bg-primary rounded-pill ms-2"><?php echo e($subscriptions->count()); ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="addon-tab" data-toggle="tab" data-target="#addon-content" type="button" role="tab">
                                <i class="fas fa-puzzle-piece me-2"></i>
                                Pembelian Add-on
                                <span class="badge bg-success rounded-pill ms-2"><?php echo e($addonPurchases->count()); ?></span>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-3" id="billingTabContent">
                        <!-- Tab Subscription -->
                        <div class="tab-pane fade show active" id="subscription-content" role="tabpanel">
                            <?php if($subscriptions->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Admin</th>
                                                <th>Paket</th>
                                                <th>Harga</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo e($subscription->user->name ?? 'Admin'); ?></strong><br>
                                                    <small class="text-muted"><?php echo e($subscription->user->email ?? ''); ?></small>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo e($subscription->plan_name); ?></strong>
                                                        <br>
                                                        <span class="badge bg-info text-white">
                                                            <i class="fas fa-sync-alt me-1"></i>
                                                            Bulanan
                                                        </span>
                                                    </div>
                                                    <small class="text-muted"><?php echo e($subscription->duration_days); ?> hari</small>
                                                </td>
                                                <td>
                                                    <strong>Rp <?php echo e(number_format($subscription->amount, 0, ',', '.')); ?></strong>
                                                </td>
                                                <td>
                                                    <?php if($subscription->status == 'active'): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i>Aktif
                                                        </span>
                                                    <?php elseif($subscription->status == 'pending'): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-clock me-1"></i>Menunggu
                                                        </span>
                                                    <?php elseif($subscription->status == 'cancelled'): ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times-circle me-1"></i>Dibatalkan
                                                        </span>
                                                    <?php elseif($subscription->status == 'expired'): ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-calendar-times me-1"></i>Berakhir
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info"><?php echo e(ucfirst($subscription->status)); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div><?php echo e(\Carbon\Carbon::parse($subscription->created_at)->format('d M Y')); ?></div>
                                                    <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($subscription->created_at)->format('H:i')); ?></small>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <?php if($subscription->status == 'pending'): ?>
                                                            <?php if($subscription->payment_url): ?>
                                                                <a href="<?php echo e($subscription->payment_url); ?>" target="_blank" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-credit-card me-1"></i>
                                                                    Bayar
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-sm btn-warning" onclick="alert('Link pembayaran tidak tersedia.')">
                                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($subscription->invoice): ?>
                                                            <a href="<?php echo e(route('manage.subscription.download-invoice', ['invoice_id' => $subscription->invoice->id])); ?>" class="btn btn-sm btn-outline-info" title="Download Invoice">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-history text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">Belum Ada Riwayat Berlangganan</h5>
                                    <p class="text-muted">Mulai berlangganan untuk melihat riwayat di sini</p>
                                    <a href="<?php echo e(route('manage.subscription.plans')); ?>" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>
                                        Berlangganan Sekarang
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Add-on Purchases -->
                        <div class="tab-pane fade" id="addon-content" role="tabpanel">
                            <?php if($addonPurchases->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Admin</th>
                                                <th>Add-on</th>
                                                <th>Harga</th>
                                                <th>Tipe</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $addonPurchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo e($addon->user_name); ?></strong><br>
                                                    <small class="text-muted"><?php echo e($addon->user_email); ?></small>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo e($addon->addon_name); ?></strong>
                                                        <br>
                                                        <span class="badge bg-purple">
                                                            <i class="fas fa-puzzle-piece me-1"></i>
                                                            Premium
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>Rp <?php echo e(number_format($addon->amount_paid, 0, ',', '.')); ?></strong>
                                                </td>
                                                <td>
                                                    <?php if($addon->addon_type === 'one_time'): ?>
                                                        <span class="badge bg-gradient-success">
                                                            <i class="fas fa-infinity me-1"></i>
                                                            Lifetime
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-gradient-primary">
                                                            <i class="fas fa-sync-alt me-1"></i>
                                                            Berulang
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($addon->status == 'active'): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i>Aktif
                                                        </span>
                                                    <?php elseif($addon->status == 'pending'): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-clock me-1"></i>Menunggu
                                                        </span>
                                                    <?php elseif($addon->status == 'cancelled'): ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times-circle me-1"></i>Dibatalkan
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?php echo e(ucfirst($addon->status)); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div><?php echo e(\Carbon\Carbon::parse($addon->created_at)->format('d M Y')); ?></div>
                                                    <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($addon->created_at)->format('H:i')); ?></small>
                                                </td>
                                                <td>
                                                    <?php if($addon->status == 'active'): ?>
                                                        <a href="<?php echo e(route('manage.addons.download-invoice', $addon->id)); ?>" class="btn btn-sm btn-outline-success" title="Unduh Invoice" target="_blank">
                                                            <i class="fas fa-download me-1"></i>
                                                            Invoice
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small" title="Invoice tidak tersedia">
                                                            -
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-puzzle-piece text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">Belum Ada Pembelian Add-on</h5>
                                    <p class="text-muted">Beli add-on premium untuk meningkatkan fitur aplikasi</p>
                                    <a href="<?php echo e(route('manage.addons.index')); ?>" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>
                                        Lihat Add-ons Premium
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize tabs
    function initializeTabs() {
        // Handle tab click
        $('#billingTabs button[data-toggle="tab"]').on('click', function(e) {
            e.preventDefault();
            
            var target = $(this).data('target');
            
            // Remove active class from all tabs and panes
            $('#billingTabs .nav-link').removeClass('active');
            $('.tab-pane').removeClass('show active');
            
            // Add active class to clicked tab and corresponding pane
            $(this).addClass('active');
            $(target).addClass('show active');
        });
    }
    
    // Initialize tabs on document ready
    initializeTabs();
});

function cancelSubscription(subscriptionId) {
    if (confirm('Apakah Anda yakin ingin membatalkan berlangganan ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo e(route("manage.subscription.cancel")); ?>';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?php echo e(csrf_token()); ?>';
        
        const subscriptionIdInput = document.createElement('input');
        subscriptionIdInput.type = 'hidden';
        subscriptionIdInput.name = 'subscription_id';
        subscriptionIdInput.value = subscriptionId;
        
        form.appendChild(csrfToken);
        form.appendChild(subscriptionIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-refresh subscription status every 5 minutes
setInterval(function() {
            fetch('<?php echo e(route("manage.subscription.check-status")); ?>')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'expired') {
                location.reload();
            }
        });
}, 300000); // 5 minutes
</script>

<style>
.card {
    border-radius: 15px;
}

.gap-1 {
    gap: 0.25rem !important;
}

.gap-2 {
    gap: 0.5rem !important;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}

/* Custom Tab Styling */
.nav-tabs {
    border-bottom: 2px solid #e9ecef;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    border: none;
    color: #0d6efd;
    background-color: #f8f9fa;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    background-color: transparent;
    border: none;
    border-bottom: 3px solid #0d6efd;
}

/* Custom Badge Colors */
.bg-purple {
    background-color: #6f42c1 !important;
    color: white !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #198754, #20c997) !important;
    color: white !important;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd, #0dcaf0) !important;
    color: white !important;
}

/* Table Hover Effect */
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transition: all 0.2s ease;
}

/* Badge Animation */
.badge {
    transition: transform 0.2s ease;
}

.badge:hover {
    transform: scale(1.05);
}

/* Empty State */
.py-5 {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/subscription/index.blade.php ENDPATH**/ ?>