

<?php $__env->startSection('title', 'Add-ons Premium - SPPQU'); ?>

<?php $__env->startSection('active_menu', 'menu.billing'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('manage.admin.dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('manage.subscription.index')); ?>">Berlangganan</a></li>
                        <li class="breadcrumb-item active">Add-ons Premium</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-puzzle-piece me-2 text-primary"></i>
                    Add-ons Premium SPPQU
                </h4>
            </div>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Purchased but Not Active Add-ons -->
    <?php if(isset($purchasedButInactive) && $purchasedButInactive->count() > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Add-ons Terbeli Belum Aktif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Anda memiliki add-on yang sudah terbeli namun belum aktif. Klik tombol "Aktifkan" untuk mengaktifkannya.
                    </div>
                    <div class="row">
                        <?php $__currentLoopData = $purchasedButInactive; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userAddon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($userAddon->addon): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <div class="mb-2">
                                            <i class="fas fa-exclamation-circle text-warning" style="font-size: 2rem;"></i>
                                        </div>
                                        <h6 class="card-title"><?php echo e($userAddon->addon->name); ?></h6>
                                        <p class="card-text text-muted small">
                                            <?php if($userAddon->purchased_at): ?>
                                                Dibeli: <?php echo e($userAddon->purchased_at->format('d M Y')); ?>

                                            <?php else: ?>
                                                Status: <?php echo e(ucfirst($userAddon->status)); ?>

                                            <?php endif; ?>
                                        </p>
                                        <span class="badge bg-warning text-dark mb-2">
                                            <i class="fas fa-clock me-1"></i><?php echo e(ucfirst($userAddon->status)); ?>

                                        </span>
                                        <div class="d-grid mt-2">
                                            <button class="btn btn-primary btn-sm activate-purchased-btn" 
                                                    data-user-addon-id="<?php echo e($userAddon->id); ?>"
                                                    data-addon-name="<?php echo e($userAddon->addon->name); ?>">
                                                <i class="fas fa-power-off me-2"></i>Aktifkan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Available Add-ons -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star me-2 text-warning"></i>
                        Add-ons Tersedia
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php $__currentLoopData = $addons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <?php if($addon->slug === 'payment-gateway'): ?>
                                            <i class="fas fa-credit-card text-primary" style="font-size: 2.5rem;"></i>
                                        <?php elseif($addon->slug === 'whatsapp-gateway'): ?>
                                            <i class="fab fa-whatsapp text-success" style="font-size: 2.5rem;"></i>
                                        <?php elseif($addon->slug === 'analisis-target'): ?>
                                            <i class="fas fa-chart-line text-info" style="font-size: 2.5rem;"></i>
                                        <?php elseif($addon->slug === 'spmb'): ?>
                                            <i class="fas fa-user-graduate text-primary" style="font-size: 2.5rem;"></i>
                                        <?php elseif($addon->slug === 'bk'): ?>
                                            <i class="fas fa-clipboard-list text-danger" style="font-size: 2.5rem;"></i>
                                        <?php elseif($addon->slug === 'ejurnal-7kaih'): ?>
                                            <i class="fas fa-book text-purple" style="font-size: 2.5rem; color: #6f42c1;"></i>
                                        <?php elseif($addon->slug === 'e-perpustakaan'): ?>
                                            <i class="fas fa-book-reader text-primary" style="font-size: 2.5rem; color: #667eea;"></i>
                                        <?php elseif($addon->slug === 'inventaris'): ?>
                                            <i class="fas fa-boxes text-info" style="font-size: 2.5rem;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-puzzle-piece text-primary" style="font-size: 2.5rem;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="card-title"><?php echo e($addon->name); ?></h5>
                                    <p class="card-text text-muted">
                                        <?php echo e($addon->description); ?>

                                    </p>
                                    <div class="mb-3">
                                        <h4 class="text-primary mb-0">
                                            Rp <?php echo e(number_format($addon->price, 0, ',', '.')); ?>

                                        </h4>
                                        <small class="text-muted">
                                            <?php if($addon->type === 'one_time'): ?>
                                                Sekali Bayar
                                            <?php else: ?>
                                                Berlangganan
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="mb-3">
                                        <?php
                                            $isPurchased = isset($allPurchasedAddons) && in_array($addon->id, $allPurchasedAddons);
                                            $isActive = $userAddons->where('addon_id', $addon->id)->count() > 0;
                                        ?>
                                        <?php if(in_array($addon->slug, ['inventaris'])): ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-clock me-1"></i>Segera
                                            </span>
                                        <?php elseif($isPurchased): ?>
                                            <?php if($isActive): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Sudah Dimiliki & Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i>Sudah Dimiliki (Belum Aktif)
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-lock me-1"></i>Belum Dimiliki
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-grid">
                                        <?php if(in_array($addon->slug, ['inventaris'])): ?>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-clock me-2"></i>Segera
                                            </button>
                                        <?php elseif($isPurchased): ?>
                                            <?php if($isActive): ?>
                                                <button class="btn btn-success" disabled>
                                                    <i class="fas fa-check me-2"></i>Sudah Dimiliki
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-warning" disabled>
                                                    <i class="fas fa-clock me-2"></i>Menunggu Aktivasi
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('manage.addons.show', $addon->slug)); ?>" class="btn btn-primary">
                                                <i class="fas fa-shopping-cart me-2"></i>Beli Sekarang
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Debug button for testing -->
                                    <?php if($addon->slug === 'payment-gateway'): ?>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-info refresh-status-btn" data-addon-slug="<?php echo e($addon->slug); ?>">
                                                <i class="fas fa-sync-alt me-1"></i>Refresh Status
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        Informasi Add-ons
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success me-2"></i>Keuntungan Add-ons</h6>
                            <ul class="text-muted">
                                <li>Fitur tambahan yang dapat dipilih sesuai kebutuhan</li>
                                <li>Pembayaran sekali bayar (tidak berlangganan)</li>
                                <li>Akses seumur hidup untuk add-on yang dibeli</li>
                                <li>Kompatibel dengan semua paket berlangganan</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-shield-alt text-primary me-2"></i>Garansi & Support</h6>
                            <ul class="text-muted">
                                <li>Garansi uang kembali 30 hari</li>
                                <li>Support teknis 24/7</li>
                                <li>Update fitur gratis</li>
                                <li>Dokumentasi lengkap</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh status button functionality
    document.querySelectorAll('.refresh-status-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const addonSlug = this.getAttribute('data-addon-slug');
            const button = this;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
            button.disabled = true;
            
            // Send request to refresh status
            fetch('<?php echo e(route("manage.addons.refresh-status")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({
                    addon_slug: addonSlug
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Refresh status response:', data);
                
                // Show result
                if (data.has_addon) {
                    alert('Status: Add-on AKTIF!');
                } else {
                    alert('Status: Add-on belum aktif');
                }
                
                // Reload page to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
            .catch(error => {
                console.error('Refresh status error:', error);
                alert('Error refreshing status');
            })
            .finally(() => {
                // Reset button state
                button.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Refresh Status';
                button.disabled = false;
            });
        });
    });

    // Activate purchased addon button functionality
    document.querySelectorAll('.activate-purchased-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const userAddonId = this.getAttribute('data-user-addon-id');
            const addonName = this.getAttribute('data-addon-name');
            const buttonElement = this;
            
            if (!confirm(`Apakah Anda yakin ingin mengaktifkan addon "${addonName}"?`)) {
                return;
            }
            
            // Show loading state
            const originalHtml = buttonElement.innerHTML;
            buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengaktifkan...';
            buttonElement.disabled = true;
            
            // Send request to activate
            fetch('<?php echo e(route("manage.addons.activate-purchased")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({
                    user_addon_id: userAddonId
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Activate purchased addon response:', data);
                
                if (data.success) {
                    alert('✅ ' + data.message);
                    // Reload page to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('❌ ' + (data.error || data.message || 'Gagal mengaktifkan addon'));
                    buttonElement.innerHTML = originalHtml;
                    buttonElement.disabled = false;
                }
            })
            .catch(error => {
                console.error('Activate purchased addon error:', error);
                alert('❌ Terjadi kesalahan saat mengaktifkan addon');
                buttonElement.innerHTML = originalHtml;
                buttonElement.disabled = false;
            });
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/addons/index.blade.php ENDPATH**/ ?>