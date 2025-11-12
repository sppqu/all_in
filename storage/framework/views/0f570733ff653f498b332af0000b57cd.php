<?php $__env->startSection('title', 'Pilih Paket - SPPQU'); ?>

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
                    Pilih Paket
                </h4>
            </div>
        </div>
    </div>

    <!-- Session Status Badge -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert" id="sessionStatusAlert" style="display: none !important;">
                <i class="fas fa-shield-alt me-2"></i>
                <div>
                    <strong>Sesi Aman!</strong> CSRF token telah di-refresh otomatis. Anda dapat mengisi form dengan aman.
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Package Selection Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="fw-bold text-dark mb-2">Pilih Paket</h2>
                    <p class="text-muted mb-0">Nikmati fitur lengkap SPPQU dengan berlangganan yang fleksibel dan hemat</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="packageTypeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cloud me-2"></i>SPPQU Premium
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="packageTypeDropdown">
                        <li><a class="dropdown-item active" href="#"><i class="fas fa-crown me-2"></i>SPPQU Premium</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-star me-2"></i>SPPQU Enterprise</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Cards -->
    <div class="row g-4">
        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-lg-3 col-md-6">
            <div class="card package-card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-0 text-center py-4">
                    <h5 class="fw-bold text-dark mb-2"><?php echo e($plan['name']); ?></h5>
                    <?php if($plan['discount'] > 0): ?>
                        <span class="badge bg-success text-white px-3 py-2">Hemat <?php echo e($plan['discount']); ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="card-body d-flex flex-column">
                    <!-- Pricing Section -->
                    <div class="text-center mb-4">
                        <?php if($plan['discount'] > 0): ?>
                            <div class="mb-2">
                                <span class="text-muted text-decoration-line-through">Rp <?php echo e(number_format($plan['original_price'], 0, ',', '.')); ?></span>
                            </div>
                        <?php endif; ?>
                        <h3 class="text-primary fw-bold mb-1">
                            Rp <?php echo e(number_format($plan['price'], 0, ',', '.')); ?>

                        </h3>
                        <?php if(isset($plan['monthly_price'])): ?>
                            <small class="text-muted">Rp <?php echo e(number_format($plan['monthly_price'], 0, ',', '.')); ?>/bulan</small>
                        <?php else: ?>
                            <small class="text-muted">per <?php echo e($plan['name']); ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Features Section -->
                    <div class="features-section mb-4">
                        <h6 class="fw-bold text-dark mb-3">Fitur yang Didapat:</h6>
                        <ul class="list-unstyled">
                            <?php $__currentLoopData = $plan['features']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-check text-success me-2 mt-1"></i>
                                <span class="small"><?php echo e($feature); ?></span>
                            </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-auto">
                        <button class="btn btn-primary w-100 py-3 fw-bold" onclick="selectPlan('<?php echo e($plan['id']); ?>')">
                            <i class="fas fa-rocket me-2"></i>
                            BERLANGGANAN SEKARANG
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Payment Method Modal -->
    <div class="modal fade" id="paymentMethodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Metode Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="subscriptionForm" action="<?php echo e(route('manage.subscription.create')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="plan_id" id="selectedPlanId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Metode Pembayaran iPaymu</label>
                            <p class="text-muted small mb-3">Pembayaran menggunakan QRIS</p>
                            
                            <!-- QRIS Only -->
                            <div class="mb-2">
                                <div class="form-check p-3 border rounded bg-light">
                                    <input class="form-check-input" type="radio" name="payment_method" value="QRIS" id="qris" checked>
                                    <label class="form-check-label w-100" for="qris">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-qrcode text-primary me-3" style="font-size: 2rem;"></i>
                                            <div>
                                                <strong class="text-dark">QRIS</strong>
                                                <div class="small text-muted">Scan QR Code - Semua E-Wallet & Mobile Banking</div>
                                                <div class="mt-1">
                                                    <span class="badge bg-success me-1">GoPay</span>
                                                    <span class="badge bg-info me-1">OVO</span>
                                                    <span class="badge bg-primary me-1">DANA</span>
                                                    <span class="badge bg-warning text-dark me-1">ShopeePay</span>
                                                    <span class="badge bg-secondary">Bank</span>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="alert alert-success mt-3 mb-0">
                                <small>
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Pembayaran aman</strong> melalui iPaymu Payment Gateway
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-arrow-right me-2"></i>
                            Lanjutkan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.package-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px;
    border: 1px solid #e9ecef;
}

.package-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.package-card .card-header {
    border-radius: 15px 15px 0 0 !important;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.package-card .btn {
    border-radius: 12px;
    font-weight: 600;
    padding: 15px 20px;
    background: linear-gradient(135deg, #008060 0%, #006d52 100%);
    border: none;
    transition: all 0.3s ease;
}

.package-card .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 128, 96, 0.3);
}

.package-card .features-section {
    flex-grow: 1;
}

.package-card .list-unstyled li {
    padding: 6px 0;
    border-bottom: 1px solid #f8f9fa;
    font-size: 0.9rem;
}

.package-card .list-unstyled li:last-child {
    border-bottom: none;
}

.badge {
    font-size: 0.8rem;
    font-weight: 600;
}

.text-decoration-line-through {
    text-decoration: line-through;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .package-card .btn {
        padding: 12px 16px;
        font-size: 0.9rem;
    }
    
    .package-card h3 {
        font-size: 1.5rem;
    }
    
    .package-card .features-section h6 {
        font-size: 0.9rem;
    }
    
    .package-card .list-unstyled li {
        font-size: 0.8rem;
    }
}
</style>

<script>
function selectPlan(planId) {
    document.getElementById('selectedPlanId').value = planId;
    new bootstrap.Modal(document.getElementById('paymentMethodModal')).show();
}

// Auto-refresh CSRF token setiap 60 menit untuk mencegah error 419
function refreshCSRFToken() {
    fetch('<?php echo e(route("manage.subscription.plans")); ?>', {
        method: 'GET',
        credentials: 'same-origin'
    }).then(response => response.text()).then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newToken = doc.querySelector('meta[name="csrf-token"]');
        if (newToken) {
            const tokenValue = newToken.getAttribute('content');
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', tokenValue);
            // Update semua form CSRF token
            document.querySelectorAll('input[name="_token"]').forEach(input => {
                input.value = tokenValue;
            });
            console.log('[' + new Date().toLocaleTimeString() + '] CSRF token refreshed successfully');
            
            // Show success notification
            const alert = document.getElementById('sessionStatusAlert');
            if (alert) {
                alert.style.display = 'flex';
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            }
        }
    }).catch(err => {
        console.error('[' + new Date().toLocaleTimeString() + '] Token refresh error:', err);
    });
}

// Run immediately on page load (silent)
console.log('[' + new Date().toLocaleTimeString() + '] CSRF auto-refresh initialized');

// Refresh every 60 minutes
setInterval(refreshCSRFToken, 3600000); // 60 menit

// Also refresh when user focuses on the page (if they left it for a while)
let lastRefresh = Date.now();
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        const timeSinceRefresh = Date.now() - lastRefresh;
        // If more than 30 minutes since last refresh
        if (timeSinceRefresh > 1800000) {
            console.log('[' + new Date().toLocaleTimeString() + '] Page focused after 30+ min, refreshing token...');
            refreshCSRFToken();
            lastRefresh = Date.now();
        }
    }
});

// Form submission with loading state and error handling
document.getElementById('subscriptionForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default first
    
    const form = this;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
    submitBtn.disabled = true;
    
    // Get form data
    const formData = new FormData(form);
    
    // Submit via fetch to catch errors
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json, text/html, application/xhtml+xml'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            // Log error details
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            });
        }
        
        // Check if JSON or redirect
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If HTML, it's probably a redirect - follow it
            return response.text().then(html => {
                document.open();
                document.write(html);
                document.close();
            });
        }
    })
    .then(data => {
        if (data && data.error) {
            alert('Error: ' + data.error);
            submitBtn.innerHTML = originalHTML;
            submitBtn.disabled = false;
        } else if (data && data.redirect) {
            window.location.href = data.redirect;
        }
        // Otherwise, page will be replaced by HTML response
    })
    .catch(error => {
        console.error('Subscription error:', error);
        alert('Gagal membuat pembayaran: ' + error.message + '\n\nCheck browser console (F12) for details.');
        submitBtn.innerHTML = originalHTML;
        submitBtn.disabled = false;
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.coreui', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/subscription/plans.blade.php ENDPATH**/ ?>