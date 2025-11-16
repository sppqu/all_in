

<?php $__env->startSection('title', 'E-Perpustakaan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">📚 E-Perpustakaan Digital</h4>
            <p class="text-muted mb-0">Koleksi buku digital lengkap untuk pembelajaran</p>
        </div>
        <div>
            <?php if(auth()->user()->role == 'superadmin' || auth()->user()->role == 'admin'): ?>
            <a href="<?php echo e(route('manage.library.cards.index')); ?>" class="btn btn-outline-success me-2">
                <i class="fas fa-id-card me-2"></i>Cetak Kartu
            </a>
            <a href="<?php echo e(route('manage.library.loans.index')); ?>" class="btn btn-outline-info me-2">
                <i class="fas fa-list-alt me-2"></i>Kelola Peminjaman
            </a>
            <a href="<?php echo e(route('manage.library.books.index')); ?>" class="btn btn-primary">
                <i class="fas fa-cog me-2"></i>Kelola Buku
            </a>
            <?php else: ?>
            <a href="<?php echo e(route('library.card')); ?>" class="btn btn-outline-primary me-2">
                <i class="fas fa-id-card me-2"></i>Kartu Perpustakaan
            </a>
            <a href="<?php echo e(route('library.my-loans')); ?>" class="btn btn-primary">
                <i class="fas fa-book me-2"></i>Peminjaman Saya
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0"><?php echo e(number_format($totalBooks)); ?></h3>
                        <p class="mb-0">Total Buku</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2); z-index: 10;">
                        <i class="fas fa-book" style="font-size: 2rem; color: #ffffff !important;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0"><?php echo e(number_format($totalCategories)); ?></h3>
                        <p class="mb-0">Kategori</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2); z-index: 10;">
                        <i class="fas fa-layer-group" style="font-size: 2rem; color: #ffffff !important;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0"><?php echo e(number_format($activeLoans)); ?></h3>
                        <p class="mb-0">Dipinjam</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2); z-index: 10;">
                        <i class="fas fa-book-reader" style="font-size: 2rem; color: #ffffff !important;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0"><?php echo e(number_format($totalReads)); ?></h3>
                        <p class="mb-0">Pembacaan</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2); z-index: 10;">
                        <i class="fas fa-eye" style="font-size: 2rem; color: #ffffff !important;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: #01a9ac; color: #ffffff !important;">
                    <h5 class="mb-0" style="color: #ffffff !important;">
                        <i class="fas fa-book-reader me-2" style="color: #ffffff !important;"></i>Grafik Peminjaman Buku (30 Hari Terakhir)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="loansChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: #28a745; color: #ffffff !important;">
                    <h5 class="mb-0" style="color: #ffffff !important;">
                        <i class="fas fa-eye me-2" style="color: #ffffff !important;"></i>Grafik Pembacaan Ebook (30 Hari Terakhir)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="readsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>


    <?php if($featuredBooks->count() > 0): ?>
    <!-- Featured Books -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">⭐ Buku Unggulan</h5>
            <a href="<?php echo e(route('library.search')); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="row g-3">
            <?php $__currentLoopData = $featuredBooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3">
                <?php echo $__env->make('library.partials.book-card', ['book' => $book], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- My Loans Section (User) -->
    <?php if(auth()->user()->role !== 'superadmin' && auth()->user()->role !== 'admin'): ?>
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">📖 Peminjaman Saya</h5>
            <a href="<?php echo e(route('library.my-loans')); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <?php
            $myLoans = \App\Models\BookLoan::with('book')
                ->where('user_id', auth()->id())
                ->where('status', 'dipinjam')
                ->latest()
                ->take(3)
                ->get();
        ?>
        
        <?php if($myLoans->count() > 0): ?>
        <div class="row g-3">
            <?php $__currentLoopData = $myLoans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            <?php if($loan->book->cover_image): ?>
                            <img src="<?php echo e(asset('storage/' . $loan->book->cover_image)); ?>" 
                                 style="width: 60px; height: 80px; object-fit: cover;" 
                                 class="rounded me-3">
                            <?php else: ?>
                            <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center text-white"
                                 style="width: 60px; height: 80px;">
                                <i class="fas fa-book"></i>
                            </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo e(Str::limit($loan->book->judul, 40)); ?></h6>
                                <small class="text-muted d-block mb-2"><?php echo e($loan->book->pengarang); ?></small>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Kembali: <?php echo e(\Carbon\Carbon::parse($loan->tanggal_kembali_rencana)->format('d M Y')); ?>

                                    </small>
                                    <?php if($loan->isOverdue()): ?>
                                    <span class="badge bg-danger">
                                        Terlambat <?php echo e($loan->daysOverdue()); ?> hari
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-success">Aktif</span>
                                    <?php endif; ?>
                                </div>
                                <?php if($loan->isOverdue()): ?>
                                <div class="mt-2 alert alert-danger py-1 px-2 mb-0">
                                    <small><strong>Denda:</strong> Rp <?php echo e(number_format($loan->calculateFine())); ?></small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <i class="fas fa-book-reader fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Anda belum memiliki peminjaman aktif</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
</style>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Loans Chart
    const loansCtx = document.getElementById('loansChart');
    if (loansCtx) {
        new Chart(loansCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dailyLoanLabels ?? [], 15, 512) ?>,
                datasets: [{
                    label: 'Peminjaman Buku',
                    data: <?php echo json_encode($dailyLoans ?? [], 15, 512) ?>,
                    borderColor: '#01a9ac',
                    backgroundColor: 'rgba(1, 169, 172, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#01a9ac',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }
    
    // Reads Chart
    const readsCtx = document.getElementById('readsChart');
    if (readsCtx) {
        new Chart(readsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dailyReadLabels ?? [], 15, 512) ?>,
                datasets: [{
                    label: 'Pembacaan Ebook',
                    data: <?php echo json_encode($dailyReads ?? [], 15, 512) ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/library/dashboard.blade.php ENDPATH**/ ?>