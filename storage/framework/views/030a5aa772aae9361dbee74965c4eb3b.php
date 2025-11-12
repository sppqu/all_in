

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark">
                <i class="fas fa-clipboard-list me-2 text-primary"></i>Dashboard Bimbingan Konseling
            </h2>
            <p class="text-muted mb-0">Monitoring dan Analisis Pelanggaran Siswa</p>
        </div>
        <div>
            <span class="badge bg-success fs-6">
                <i class="far fa-calendar me-1"></i><?php echo e(\Carbon\Carbon::now()->isoFormat('D MMMM YYYY')); ?>

            </span>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Pelanggaran -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 fw-semibold small">TOTAL PELANGGARAN</p>
                            <h2 class="mb-0 fw-bold text-primary"><?php echo e(number_format($totalPelanggaran)); ?></h2>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> Disetujui
                            </small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 fw-semibold small">MENUNGGU REVIEW</p>
                            <h2 class="mb-0 fw-bold text-warning"><?php echo e(number_format($pelanggaranPending)); ?></h2>
                            <small class="text-warning">
                                <i class="fas fa-clock"></i> Pending
                            </small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulan Ini -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 fw-semibold small">BULAN INI</p>
                            <h2 class="mb-0 fw-bold text-info"><?php echo e(number_format($pelanggaranBulanIni)); ?></h2>
                            <small class="text-info">
                                <i class="far fa-calendar"></i> <?php echo e(\Carbon\Carbon::now()->isoFormat('MMMM')); ?>

                            </small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-calendar-day fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Siswa Bermasalah -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 fw-semibold small">SISWA BERMASALAH</p>
                            <h2 class="mb-0 fw-bold text-danger"><?php echo e(number_format($totalSiswaBermasalah)); ?></h2>
                            <small class="text-danger">
                                <i class="fas fa-user-times"></i> Perlu Perhatian
                            </small>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-users fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo e(route('manage.bk.pelanggaran-siswa.create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Catat Pelanggaran
                        </a>
                        <a href="<?php echo e(route('manage.bk.pelanggaran-siswa.index')); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Lihat Semua
                        </a>
                        <a href="<?php echo e(route('manage.bk.bimbingan-konseling')); ?>" class="btn btn-outline-info">
                            <i class="fas fa-user-friends me-2"></i>Bimbingan Konseling
                        </a>
                        <a href="<?php echo e(route('manage.bk.pelanggaran-siswa.report')); ?>" class="btn btn-outline-success">
                            <i class="fas fa-chart-bar me-2"></i>Laporan Rekap
                        </a>
                        <a href="<?php echo e(route('manage.bk.pelanggaran.index')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-cog me-2"></i>Master Pelanggaran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Chart -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Trend Pelanggaran 6 Bulan Terakhir
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="pelanggaranChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Stats by Category -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-layer-group me-2 text-primary"></i>Per Kategori
                    </h5>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $statsByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold"><?php echo e($stat->nama); ?></span>
                            <span class="badge bg-primary"><?php echo e($stat->total); ?></span>
                        </div>
                        <?php
                            $percentage = $totalPelanggaran > 0 ? ($stat->total / $totalPelanggaran) * 100 : 0;
                            $colorClass = $stat->nama == 'Pelanggaran Ringan' ? 'warning' : ($stat->nama == 'Pelanggaran Sedang' ? 'orange' : 'danger');
                        ?>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-<?php echo e($colorClass); ?>" role="progressbar" 
                                 style="width: <?php echo e($percentage); ?>%" aria-valuenow="<?php echo e($percentage); ?>" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted"><?php echo e(number_format($percentage, 1)); ?>%</small>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <!-- Top 5 Siswa Bermasalah -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-trophy me-2 text-warning"></i>Top 5 Siswa Bermasalah
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%" class="text-center">#</th>
                                    <th width="50%">Siswa</th>
                                    <th width="20%" class="text-center">Pelanggaran</th>
                                    <th width="20%" class="text-center">Point</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $topSiswaBermasalah; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if($index == 0): ?>
                                            <i class="fas fa-crown text-warning fs-5"></i>
                                        <?php else: ?>
                                            <?php echo e($index + 1); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo e($student->student_full_name); ?></strong><br>
                                        <small class="text-muted">NIS: <?php echo e($student->student_nis); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning"><?php echo e($student->total_pelanggaran); ?>x</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger fs-6"><?php echo e($student->total_point); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-smile fa-2x mb-2"></i>
                                        <p>Tidak ada siswa bermasalah</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-history me-2 text-info"></i>Aktivitas Terbaru (7 Hari)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php $__empty_1 = true; $__currentLoopData = $recentPelanggaran->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="list-group-item">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <div class="stat-icon-sm bg-<?php echo e($item->pelanggaran->kategori->nama == 'Pelanggaran Ringan' ? 'warning' : ($item->pelanggaran->kategori->nama == 'Pelanggaran Sedang' ? 'orange' : 'danger')); ?> bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-exclamation-triangle text-<?php echo e($item->pelanggaran->kategori->nama == 'Pelanggaran Ringan' ? 'warning' : ($item->pelanggaran->kategori->nama == 'Pelanggaran Sedang' ? 'orange' : 'danger')); ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?php echo e($item->siswa->student_full_name); ?></h6>
                                        <small class="text-muted"><?php echo e($item->tanggal_pelanggaran->diffForHumans()); ?></small>
                                    </div>
                                    <p class="mb-1 small text-muted"><?php echo e($item->pelanggaran->nama); ?></p>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge bg-<?php echo e($item->status == 'approved' ? 'success' : 'warning'); ?>">
                                            <?php echo e(ucfirst($item->status)); ?>

                                        </span>
                                        <span class="badge bg-danger"><?php echo e($item->pelanggaran->point); ?> Point</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="list-group-item text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">Belum ada aktivitas</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if($recentPelanggaran->count() > 5): ?>
                <div class="card-footer bg-white border-0">
                    <a href="<?php echo e(route('manage.bk.pelanggaran-siswa.index')); ?>" class="btn btn-sm btn-link text-decoration-none">
                        Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.stat-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-orange {
    background-color: #ff9800 !important;
}

.text-orange {
    color: #ff9800 !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Chart Configuration
const ctx = document.getElementById('pelanggaranChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartData['labels']); ?>,
        datasets: [{
            label: 'Jumlah Pelanggaran',
            data: <?php echo json_encode($chartData['data']); ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointBackgroundColor: '#0d6efd',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                },
                callbacks: {
                    label: function(context) {
                        return ' ' + context.parsed.y + ' Pelanggaran';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.coreui', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/manage/bk/dashboard.blade.php ENDPATH**/ ?>