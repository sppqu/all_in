<?php $__env->startPush('styles'); ?>
<style>
    .container-fluid {
        min-height: calc(100vh - 200px);
        width: 100%;
        clear: both;
    }

    .stats-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border-left: 4px solid #008060;
        padding: 1rem;
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .stats-icon {
        font-size: 1.5rem;
        color: #008060;
        margin-right: 0.75rem;
        display: inline-flex;
        align-items: center;
    }

    .stats-content {
        display: flex;
        align-items: center;
        text-align: left;
    }

    .stats-info {
        flex: 1;
    }

    .stats-number {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: #333;
    }

    .stats-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
    }

    .table-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 25px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, #008060 0%, #006d52 100%);
        border: none;
        border-radius: 15px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 128, 96, 0.4);
    }

    .btn-outline-primary {
        border: 2px solid #008060;
        color: #008060;
        background: transparent;
        border-radius: 15px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: #008060;
        border-color: #008060;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 128, 96, 0.4);
    }

    /* Standardize all button styles */
    .btn {
        border-radius: 15px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn:hover {
        transform: translateY(-3px);
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        border: none;
        color: white;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #1e7e34 0%, #155724 100%);
        box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
    }

    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: none;
        color: white;
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #138496 0%, #0f6674 100%);
        box-shadow: 0 10px 25px rgba(23, 162, 184, 0.4);
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        border: none;
        color: #212529;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.4);
    }

    .btn-primary {
        background: linear-gradient(135deg, #008060 0%, #006d52 100%);
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #006d52 0%, #004d3a 100%);
        box-shadow: 0 10px 25px rgba(0, 128, 96, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        color: white;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
        box-shadow: 0 10px 25px rgba(220, 53, 69, 0.4);
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .alert {
        border-radius: 15px;
        border: none;
        padding: 15px 20px;
    }

    .table {
        border-radius: 15px;
        overflow: hidden;
    }

    .table thead th {
        background: white;
        color: #333;
        border: none;
        font-weight: 600;
        border-bottom: 2px solid #008060;
    }

    .table tbody tr:hover {
        background: rgba(0, 128, 96, 0.05);
    }

    /* Action Buttons Styling */
    .action-buttons {
        display: flex;
        gap: 2px;
        align-items: center;
        background: rgba(255, 255, 255, 0.9);
        border: 2px solid rgba(0, 128, 96, 0.1);
        border-radius: 15px;
        padding: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .action-buttons:hover {
        border-color: rgba(0, 128, 96, 0.3);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .action-btn {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s;
    }

    .action-btn:hover::before {
        left: 100%;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* Bulk Actions Styling */
    .bulk-actions {
        background: rgba(220, 53, 69, 0.1);
        border: 1px solid rgba(220, 53, 69, 0.3);
        border-radius: 15px;
        padding: 8px 16px;
        transition: all 0.3s ease;
    }

    .bulk-actions.show {
        background: rgba(220, 53, 69, 0.2);
        border-color: rgba(220, 53, 69, 0.5);
    }

    .selected-count {
        font-size: 0.9rem;
        font-weight: 500;
    }

    .dropdown-menu {
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border: none;
    }

    .dropdown-item {
        border-radius: 8px;
        margin: 2px 8px;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .dropdown-item.text-danger:hover {
        background: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }

    .dropdown-item.text-success:hover {
        background: rgba(40, 167, 69, 0.15);
        color: #28a745;
    }

    .dropdown-item.text-warning:hover {
        background: rgba(255, 193, 7, 0.15);
        color: #ffc107;
    }

    /* Checkbox styling */
    .form-check-input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: #008060;
        border-color: #008060;
    }

    /* Mobile Responsive Menu */
    .mobile-menu-container {
        display: none;
    }

    .mobile-menu-container.show {
        display: block;
    }

    .mobile-menu-toggle {
        display: none;
        background: #008060;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        margin-bottom: 15px;
    }

    .mobile-menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        margin-bottom: 20px;
    }

    .mobile-menu-item {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 15px 10px;
        text-align: center;
        text-decoration: none;
        color: #333;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .mobile-menu-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        text-decoration: none;
        color: #333;
    }

    .mobile-menu-item i {
        font-size: 24px;
        margin-bottom: 8px;
        display: block;
    }

    .mobile-menu-item .btn-text {
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
    }

    /* Button colors for mobile */
    .mobile-menu-item.btn-success { border-color: #28a745; color: #28a745; }
    .mobile-menu-item.btn-info { border-color: #17a2b8; color: #17a2b8; }
    .mobile-menu-item.btn-warning { border-color: #ffc107; color: #e0a800; }
    .mobile-menu-item.btn-primary { border-color: #007bff; color: #007bff; }
    .mobile-menu-item.btn-danger { border-color: #dc3545; color: #dc3545; }

    .mobile-menu-item.btn-success:hover { background: #28a745; color: white; }
    .mobile-menu-item.btn-info:hover { background: #17a2b8; color: white; }
    .mobile-menu-item.btn-warning:hover { background: #ffc107; color: #333; }
    .mobile-menu-item.btn-primary:hover { background: #007bff; color: white; }
    .mobile-menu-item.btn-danger:hover { background: #dc3545; color: white; }

    /* Desktop menu - hide on mobile */
    .desktop-menu {
        display: flex;
    }

    /* Mobile responsive breakpoints */
    @media (max-width: 768px) {
        .mobile-menu-toggle {
            display: block !important;
        }
        
        .mobile-menu-container {
            display: block;
        }
        
        .desktop-menu {
            display: none !important;
        }
        
        .mobile-menu-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 769px) {
        .mobile-menu-toggle {
            display: none !important;
        }
        
        .mobile-menu-container {
            display: none !important;
        }
        
        .desktop-menu {
            display: flex !important;
        }
    }

    @media (max-width: 480px) {
        .mobile-menu-grid {
            grid-template-columns: 1fr;
        }
        
        .mobile-menu-item {
            padding: 20px 15px;
        }
        
        .mobile-menu-item i {
            font-size: 28px;
        }
        
        .mobile-menu-item .btn-text {
            font-size: 14px;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                <i class="fas fa-graduation-cap me-2"></i>Dashboard SPMB Admin
            </h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola data pendaftaran SPMB</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stats-card">
                <div class="stats-content">
                    <div class="stats-icon text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-number"><?php echo e($stats['total']); ?></div>
                        <p class="stats-label mb-0">Total Pendaftar</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card">
                <div class="stats-content">
                    <div class="stats-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-number"><?php echo e($stats['completed']); ?></div>
                        <p class="stats-label mb-0">Diterima</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card">
                <div class="stats-content">
                    <div class="stats-icon text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-number"><?php echo e($stats['pending']); ?></div>
                        <p class="stats-label mb-0">Pending</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card">
                <div class="stats-content">
                    <div class="stats-icon text-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-number"><?php echo e($stats['ditolak']); ?></div>
                        <p class="stats-label mb-0">Pendaftar Ditolak</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SPMB Settings Info Card -->
    <?php if($spmbSettings): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="stats-card">
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-cogs me-2 text-primary"></i>Informasi Pengaturan SPMB
                        </h6>
                        <a href="<?php echo e(route('manage.spmb.settings')); ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="text-muted small d-block mb-1">Status Pendaftaran</label>
                            <div>
                                <?php if($spmbSettings->pendaftaran_dibuka): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-unlock me-1"></i>Dibuka
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-lock me-1"></i>Ditutup
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="text-muted small d-block mb-1">Periode</label>
                            <div class="fw-bold"><?php echo e($spmbSettings->tahun_pelajaran); ?></div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="text-muted small d-block mb-1">Tanggal Buka</label>
                            <div>
                                <?php if($spmbSettings->tanggal_buka): ?>
                                    <?php echo e($spmbSettings->tanggal_buka->format('d/m/Y')); ?>

                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="text-muted small d-block mb-1">Tanggal Tutup</label>
                            <div>
                                <?php if($spmbSettings->tanggal_tutup): ?>
                                    <?php echo e($spmbSettings->tanggal_tutup->format('d/m/Y')); ?>

                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars me-2"></i>Menu Admin SPMB
    </button>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Mobile Menu Toggle Function
    function toggleMobileMenu() {
        const mobileMenu = document.getElementById('mobileMenu');
        const toggleBtn = document.querySelector('.mobile-menu-toggle');
        
        if (mobileMenu.classList.contains('show')) {
            mobileMenu.classList.remove('show');
            toggleBtn.innerHTML = '<i class="fas fa-bars me-2"></i>Menu Admin SPMB';
        } else {
            mobileMenu.classList.add('show');
            toggleBtn.innerHTML = '<i class="fas fa-times me-2"></i>Tutup Menu';
        }
    }

    // Auto-hide mobile menu on window resize
    window.addEventListener('resize', function() {
        const mobileMenu = document.getElementById('mobileMenu');
        const toggleBtn = document.querySelector('.mobile-menu-toggle');
        
        if (window.innerWidth > 768) {
            mobileMenu.classList.remove('show');
            toggleBtn.innerHTML = '<i class="fas fa-bars me-2"></i>Menu Admin SPMB';
        }
    });

    // Initialize mobile menu state
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenu = document.getElementById('mobileMenu');
        const toggleBtn = document.querySelector('.mobile-menu-toggle');
        
        // Hide mobile menu by default
        mobileMenu.classList.remove('show');
        
        // Show/hide toggle button based on screen size
        if (window.innerWidth <= 768) {
            toggleBtn.style.display = 'block';
        } else {
            toggleBtn.style.display = 'none';
        }
    });

</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.coreui', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/admin/spmb/index.blade.php ENDPATH**/ ?>