<?php if(session('subscription_warning')): ?>
    <?php
        $warning = session('subscription_warning');
    ?>
    
    <!-- Subscription Warning Modal -->
    <div class="modal fade" id="subscriptionModal" tabindex="-1" aria-labelledby="subscriptionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="subscriptionModalLabel">
                        <?php if($warning['type'] == 'error'): ?>
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                            Berlangganan Diperlukan
                        <?php elseif($warning['type'] == 'critical'): ?>
                            <i class="fas fa-exclamation-circle text-danger me-2"></i>
                            Berlangganan Akan Berakhir!
                        <?php elseif($warning['type'] == 'warning'): ?>
                            <i class="fas fa-clock text-warning me-2"></i>
                            Berlangganan Akan Berakhir
                        <?php else: ?>
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Informasi Berlangganan
                        <?php endif; ?>
                    </h5>
                    <?php if($warning['type'] != 'error'): ?>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <?php endif; ?>
                </div>
                <div class="modal-body pt-0">
                    <div class="alert alert-<?php echo e($warning['type'] == 'error' ? 'danger' : ($warning['type'] == 'critical' ? 'danger' : ($warning['type'] == 'warning' ? 'warning' : 'info'))); ?> border-0 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <?php if($warning['type'] == 'error'): ?>
                                    <i class="fas fa-ban fa-2x text-danger"></i>
                                <?php elseif($warning['type'] == 'critical'): ?>
                                    <i class="fas fa-fire fa-2x text-danger"></i>
                                <?php elseif($warning['type'] == 'warning'): ?>
                                    <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                                <?php else: ?>
                                    <i class="fas fa-bell fa-2x text-info"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-0 fw-medium"><?php echo e($warning['message']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($warning['type'] == 'error'): ?>
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-lock fa-3x text-muted"></i>
                            </div>
                            <h6 class="text-muted">Fitur Premium Terkunci</h6>
                            <p class="text-muted small">Untuk mengakses semua fitur, Anda perlu berlangganan terlebih dahulu.</p>
                        </div>
                    <?php elseif($warning['type'] == 'info' && strpos($warning['message'], 'pengingat awal') !== false): ?>
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-bell fa-3x text-info"></i>
                            </div>
                            <h6 class="text-info">Pengingat Perpanjangan</h6>
                            <p class="text-muted small">Ini adalah pengingat awal untuk memperpanjang berlangganan Anda.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <?php if($warning['type'] == 'error'): ?>
                        <a href="<?php echo e(route('manage.subscription.plans')); ?>" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-crown me-2"></i>
                            Berlangganan Sekarang
                        </a>
                    <?php elseif($warning['type'] == 'info' && strpos($warning['message'], 'pengingat awal') !== false): ?>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            Nanti Saja
                        </button>
                        <a href="<?php echo e(route('manage.subscription.plans')); ?>" class="btn btn-info">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Lihat Paket Berlangganan
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            Tutup
                        </button>
                        <a href="<?php echo e(route('manage.subscription.plans')); ?>" class="btn btn-primary">
                            <i class="fas fa-credit-card me-2"></i>
                            Perpanjang Berlangganan
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto show modal if show_popup is true -->
    <?php if(isset($warning['show_popup']) && $warning['show_popup']): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = new bootstrap.Modal(document.getElementById('subscriptionModal'));
                modal.show();
            });
        </script>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/components/subscription-modal.blade.php ENDPATH**/ ?>