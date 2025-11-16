<?php $__env->startPush('styles'); ?>
<style>
    .info-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .info-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .info-card-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #ffffff;
        flex-shrink: 0;
    }

    .info-card-icon.primary {
        background: #01a9ac;
    }

    .info-card-icon.success {
        background: #28a745;
    }

    .info-card-icon.warning {
        background: #ffc107;
    }

    .info-card-icon.danger {
        background: #dc3545;
    }

    .info-card-icon.info {
        background: #17a2b8;
    }

    .info-card-title {
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0;
        text-align: right;
        flex: 1;
        padding-left: 1rem;
    }

    .info-card-title.primary {
        color: #01a9ac;
    }

    .info-card-title.success {
        color: #28a745;
    }

    .info-card-title.warning {
        color: #ffc107;
    }

    .info-card-title.danger {
        color: #dc3545;
    }

    .info-card-title.info {
        color: #17a2b8;
    }

    .info-card-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .info-card-footer {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        margin-top: auto;
    }

    .info-card-footer-icon {
        font-size: 0.875rem;
    }

    .info-card-footer-text {
        color: #6c757d;
    }

    .info-card-footer.primary .info-card-footer-icon,
    .info-card-footer.primary .info-card-footer-text {
        color: #01a9ac;
    }

    .info-card-footer.success .info-card-footer-icon,
    .info-card-footer.success .info-card-footer-text {
        color: #28a745;
    }

    .info-card-footer.warning .info-card-footer-icon,
    .info-card-footer.warning .info-card-footer-text {
        color: #ffc107;
    }

    .info-card-footer.danger .info-card-footer-icon,
    .info-card-footer.danger .info-card-footer-text {
        color: #dc3545;
    }

    .info-card-footer.info .info-card-footer-icon,
    .info-card-footer.info .info-card-footer-text {
        color: #17a2b8;
    }

    .chart-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .chart-card-header {
        margin-bottom: 1.5rem;
    }

    .chart-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .chart-container {
        position: relative;
        height: 300px;
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

    <!-- Info Cards -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-card-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="info-card-title primary">Total Pendaftar</div>
                </div>
                <div class="info-card-value"><?php echo e($stats['total']); ?></div>
                <div class="info-card-footer primary">
                    <i class="fas fa-chart-line info-card-footer-icon"></i>
                    <span class="info-card-footer-text">Semua pendaftar</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-card-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="info-card-title success">Diterima</div>
                </div>
                <div class="info-card-value"><?php echo e($stats['completed']); ?></div>
                <div class="info-card-footer success">
                    <i class="fas fa-calendar-check info-card-footer-icon"></i>
                    <span class="info-card-footer-text">Status diterima</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-card-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-card-title warning">Pending</div>
                </div>
                <div class="info-card-value"><?php echo e($stats['pending']); ?></div>
                <div class="info-card-footer warning">
                    <i class="fas fa-hourglass-half info-card-footer-icon"></i>
                    <span class="info-card-footer-text">Menunggu verifikasi</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-card-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="info-card-title danger">Ditolak</div>
                </div>
                <div class="info-card-value"><?php echo e($stats['ditolak']); ?></div>
                <div class="info-card-footer danger">
                    <i class="fas fa-ban info-card-footer-icon"></i>
                    <span class="info-card-footer-text">Pendaftar ditolak</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mt-4">
        <!-- Grafik Garis - Data Pendaftar Per Hari -->
        <div class="col-md-8 mb-3">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h5 class="chart-card-title">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Data Pendaftar Per Hari (30 Hari Terakhir)
                    </h5>
                </div>
                <div class="chart-container">
                    <canvas id="dailyRegistrationsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Grafik Pie - Distribusi Status Pendaftar -->
        <div class="col-md-4 mb-3">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h5 class="chart-card-title">
                        <i class="fas fa-chart-pie me-2 text-success"></i>Distribusi Status Pendaftar
                    </h5>
                </div>
                <div class="chart-container">
                    <canvas id="statusDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grafik Garis - Data Pendaftar Per Hari
    const dailyCtx = document.getElementById('dailyRegistrationsChart').getContext('2d');
    const dailyChart = new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dailyLabels, 15, 512) ?>,
            datasets: [{
                label: 'Jumlah Pendaftar',
                data: <?php echo json_encode($dailyRegistrations, 15, 512) ?>,
                borderColor: '#01a9ac',
                backgroundColor: 'rgba(1, 169, 172, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#01a9ac',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
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
                            return 'Pendaftar: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });

    // Grafik Pie - Distribusi Status Pendaftar
    const pieCtx = document.getElementById('statusDistributionChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($pieData['labels'], 15, 512) ?>,
            datasets: [{
                data: <?php echo json_encode($pieData['data'], 15, 512) ?>,
                backgroundColor: <?php echo json_encode($pieData['colors'], 15, 512) ?>,
                borderColor: <?php echo json_encode($pieData['borderColors'], 15, 512) ?>,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
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
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/admin/spmb/index.blade.php ENDPATH**/ ?>