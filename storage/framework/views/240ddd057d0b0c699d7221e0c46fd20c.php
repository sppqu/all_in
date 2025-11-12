<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <!-- Pratinjau Profil Sekolah -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <h5 class="fw-bold mb-0">Pratinjau Profil Sekolah</h5>
                <?php
                    $isFoundationLevel = auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan';
                ?>
                <?php if($isFoundationLevel): ?>
                    <a href="<?php echo e(route('manage.foundation.schools.edit', $currentSchool->id)); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-edit me-2"></i>Lengkapi Profil
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('manage.foundation.schools.edit', $currentSchool->id)); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-edit me-2"></i>Lengkapi Profil
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Nama Sekolah</label>
                        <div class="fw-bold"><?php echo e($currentSchool->nama_sekolah ?? 'Belum diisi'); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Kepala Sekolah</label>
                        <div class="fw-bold"><?php echo e($currentSchool->kepala_sekolah ?? '-'); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small mb-1">NPSN</label>
                        <div class="fw-bold"><?php echo e($currentSchool->npsn ?? '-'); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Alamat</label>
                        <div class="fw-bold">
                            <?php if($currentSchool->alamat_baris_1): ?>
                                <?php echo e($currentSchool->alamat_baris_1); ?>

                                <?php if($currentSchool->alamat_baris_2): ?>
                                    <br><?php echo e($currentSchool->alamat_baris_2); ?>

                                <?php endif; ?>
                            <?php else: ?>
                                <?php echo e($currentSchool->alamat ?? 'Belum diisi'); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Row: Statistik Pembayaran & Traffic Pembayaran -->
    <div class="row mb-4">
        <!-- Statistik Pembayaran (Line Chart) -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px; height: 100%;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.9rem;">💰 Statistik Pembayaran</h6>
                            <h3 class="fw-bold text-primary mb-0">Rp <?php echo e(number_format($totalPaymentsThisYear ?? 0, 0, ',', '.')); ?></h3>
                            <small class="text-muted">Juli - Juni <?php echo e(now()->year); ?></small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-pill" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo e(route('payment.index')); ?>"><i class="fas fa-list me-2"></i>Lihat Semua</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Export Data</a></li>
                            </ul>
                        </div>
                    </div>
                    <div style="height: 250px;">
                        <canvas id="paymentsLineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Rank Pembayaran User -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px; height: 100%;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted mb-0" style="font-size: 0.9rem;">🏆 Top Rank Pembayaran</h6>
                        <span class="badge bg-success"><?php echo e(now()->year); ?></span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Berdasarkan Jumlah Transaksi</small>
                    </div>
                    <div style="height: 220px; overflow-y: auto;">
                        <?php $__empty_1 = true; $__currentLoopData = $topPaymentUsers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="d-flex align-items-center mb-3 p-2 rounded" style="background: <?php echo e($index < 3 ? 'rgba(34, 197, 94, 0.1)' : 'rgba(243, 244, 246, 0.5)'); ?>;">
                            <div class="me-3">
                                <?php if($index === 0): ?>
                                    <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.8rem;">🥇</span>
                                <?php elseif($index === 1): ?>
                                    <span class="badge bg-secondary fw-bold" style="font-size: 0.8rem;">🥈</span>
                                <?php elseif($index === 2): ?>
                                    <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.8rem;">🥉</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark fw-bold" style="font-size: 0.8rem;">#<?php echo e($index + 1); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?php echo e($user['name']); ?></div>
                                <small class="text-muted"><?php echo e($user['class'] ?? 'N/A'); ?></small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success"><?php echo e($user['transaction_count']); ?>x</div>
                                <small class="text-muted">transaksi</small>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-chart-bar fa-2x mb-2"></i>
                            <div>Belum ada data transaksi</div>
                    </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Persentase Pembayaran & Cards -->
    <div class="row mb-4">
        <!-- Pembayaran Bulan Ini -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-white bg-opacity-25 rounded-circle p-3 me-3">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0" style="font-size: 0.85rem; opacity: 0.9;">Pembayaran Bulan Ini</h6>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">Rp <?php echo e(number_format($monthPayments ?? 0, 0, ',', '.')); ?></h2>
                    <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                            <span class="badge bg-white text-dark me-2" style="font-size: 0.7rem;">
                                <?php echo e(number_format($monthPaymentsCount ?? 0)); ?> transaksi
                            </span>
                        </div>
                        <?php
                            $monthGrowth = $monthPaymentsGrowth ?? 0;
                            $isMonthGrowthPositive = $monthGrowth >= 0;
                        ?>
                        <span class="badge" style="background-color: <?php echo e($isMonthGrowthPositive ? '#10b981' : '#ef4444'); ?>; font-size: 0.75rem; padding: 0.35rem 0.65rem;">
                            <i class="fas fa-arrow-<?php echo e($isMonthGrowthPositive ? 'up' : 'down'); ?>"></i> <?php echo e(abs($monthGrowth)); ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Persentase Lunas -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle p-3 me-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-bullseye fa-lg text-white"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted" style="font-size: 0.85rem;">Target Prosentase Pembayaran Bulan Ini</h6>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2 text-dark"><?php echo e(number_format($paymentCompletionPercent ?? 0, 1)); ?>%</h2>
                    <div class="progress" style="height: 10px; border-radius: 10px; background-color: #e9ecef;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: <?php echo e($paymentCompletionPercent ?? 0); ?>%; 
                                    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
                                    border-radius: 10px;
                                    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Countdown Berlangganan -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle p-3 me-3" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fas fa-clock fa-lg text-white"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted" style="font-size: 0.85rem;">Sisa Waktu Berlangganan</h6>
                        </div>
                    </div>
                    <?php if(isset($subscriptionDaysLeft) && $subscriptionDaysLeft > 0): ?>
                        <?php if($subscriptionDaysLeft >= 30): ?>
                            <h2 class="fw-bold mb-2" style="color: #10b981;"><?php echo e(floor($subscriptionDaysLeft / 30)); ?> Bulan</h2>
                            <div class="progress" style="height: 10px; border-radius: 10px; background-color: #e9ecef;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: 100%; 
                                            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
                                            border-radius: 10px;
                                            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);"></div>
                            </div>
                        <?php elseif($subscriptionDaysLeft >= 7): ?>
                            <h2 class="fw-bold mb-2" style="color: #f59e0b;"><?php echo e(number_format($subscriptionDaysLeft, 0, '', '')); ?> Hari</h2>
                            <div class="progress" style="height: 10px; border-radius: 10px; background-color: #e9ecef;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo e(floor(($subscriptionDaysLeft / 30) * 100)); ?>%; 
                                            background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
                                            border-radius: 10px;
                                            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.4);"></div>
                            </div>
                        <?php else: ?>
                            <h2 class="fw-bold mb-2" style="color: #dc2626;"><?php echo e(number_format($subscriptionDaysLeft, 0, '', '')); ?> Hari</h2>
                            <div class="progress" style="height: 10px; border-radius: 10px; background-color: #e9ecef;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo e(floor(($subscriptionDaysLeft / 30) * 100)); ?>%; 
                                            background: linear-gradient(90deg, #dc2626 0%, #b91c1c 100%);
                                            border-radius: 10px;
                                            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);"></div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <h2 class="fw-bold mb-2 text-danger">Expired</h2>
                        <div class="progress" style="height: 10px; border-radius: 10px; background-color: #e9ecef;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 0%;"></div>
                        </div>
                    <?php endif; ?>
                    <div class="mt-2">
                        <small class="text-muted">Berakhir: <?php echo e($subscriptionExpiresAt ?? '-'); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kalender -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle p-3 me-3" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                            <i class="fas fa-calendar-alt fa-lg text-white"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted" style="font-size: 0.85rem;">Kalender Hari Ini</h6>
                        </div>
                    </div>
                    <?php
                        $currentDate = \Carbon\Carbon::now();
                        $dayName = $currentDate->locale('id')->isoFormat('dddd');
                        $dateNumber = $currentDate->format('d');
                        $monthYear = $currentDate->locale('id')->isoFormat('MMMM YYYY');
                    ?>
                    <div class="text-center mb-3">
                        <div class="fw-bold mb-1" style="color: #8b5cf6; font-size: 3rem; line-height: 1;"><?php echo e($dateNumber); ?></div>
                        <div class="text-muted" style="font-size: 0.9rem;"><?php echo e($monthYear); ?></div>
                        <div class="badge mt-2" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); font-size: 0.75rem;">
                            <?php echo e($dayName); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Third Row: Grafik Jumlah Siswa/Kelas & Penerimaan/Pengeluaran -->
    <div class="row mb-4">
        <!-- Grafik Siswa Per Kelas (Doughnut) -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center" style="padding: 1.25rem 1.5rem;">
                    <h6 class="mb-0 fw-bold">📚 Distribusi Siswa Per Kelas</h6>
                    <span class="badge bg-light text-dark">Total: <?php echo e($totalStudents ?? 0); ?></span>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="studentsPerClassChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Penerimaan/Pengeluaran (Bar) -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center" style="padding: 1.25rem 1.5rem;">
                    <h6 class="mb-0 fw-bold">💸 Penerimaan vs Pengeluaran</h6>
                    <form method="GET" action="" class="d-flex align-items-center" style="gap:8px">
                        <select name="period_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 150px; border-radius: 20px;">
                            <?php $__currentLoopData = ($periods ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->period_id); ?>" <?php echo e(($selectedPeriodId ?? '') == $p->period_id ? 'selected' : ''); ?>>
                                    <?php echo e($p->period_start); ?>/<?php echo e($p->period_end); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </form>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px;">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fourth Row: Persentase Pembayaran Per Kelas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0" style="padding: 1.25rem 1.5rem;">
                    <h6 class="mb-0 fw-bold">📈 Persentase Pembayaran Per Kelas</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Kelas</th>
                                    <th width="15%">Jumlah Siswa</th>
                                    <th width="15%">Lunas</th>
                                    <th width="15%">Belum Lunas</th>
                                    <th width="25%">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $paymentProgressByClass ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><strong><?php echo e($class['class_name']); ?></strong></td>
                                    <td><?php echo e($class['total_students']); ?> siswa</td>
                                    <td><span class="badge bg-success"><?php echo e($class['paid_students']); ?></span></td>
                                    <td><span class="badge bg-danger"><?php echo e($class['unpaid_students']); ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 24px; border-radius: 12px; background-color: #e9ecef;">
                                                <div class="progress-bar" 
                                                     style="width: <?php echo e($class['percentage']); ?>%; 
                                                            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
                                                            border-radius: 12px;
                                                            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);" 
                                                     role="progressbar">
                                                    <span class="small fw-bold text-white" style="text-shadow: 0 1px 2px rgba(0,0,0,0.2);"><?php echo e(number_format($class['percentage'], 1)); ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Statistik Pembayaran (Line Chart)
    if (document.getElementById('paymentsLineChart')) {
        const monthLabels = <?php echo json_encode($monthLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']); ?>;
        const paymentsData = <?php echo json_encode(array_values($paymentsMonthly ?? [0,0,0,0,0,0,0,0,0,0,0,0])); ?>;
        
        new Chart(document.getElementById('paymentsLineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Pembayaran',
                    data: paymentsData,
                    borderColor: '#5b6ee1',
                    backgroundColor: 'rgba(91, 110, 225, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#5b6ee1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#000',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: {
                            color: '#9ca3af',
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Top Rank Pembayaran User - No chart needed, using HTML list

    // 3. Distribusi Siswa Per Kelas (Doughnut)
    if (document.getElementById('studentsPerClassChart')) {
        const classLabels = <?php echo json_encode($classLabels ?? []); ?>;
        const classData = <?php echo json_encode($classData ?? []); ?>;
        
        new Chart(document.getElementById('studentsPerClassChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: classLabels,
                datasets: [{
                    data: classData,
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c',
                        '#4facfe', '#00f2fe', '#43e97b', '#38f9d7',
                        '#fa709a', '#fee140', '#30cfd0', '#330867'
                    ],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: { size: 12 },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#000',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' siswa';
                            }
                        }
                    }
                }
            }
        });
    }

    // 4. Penerimaan vs Pengeluaran (Bar)
    if (document.getElementById('incomeExpenseChart')) {
        const receiptsData = <?php echo json_encode(array_values($receiptsMonthly ?? [0,0,0,0,0,0,0,0,0,0,0,0])); ?>;
        const expensesData = <?php echo json_encode(array_values($expensesMonthly ?? [0,0,0,0,0,0,0,0,0,0,0,0])); ?>;
        
        new Chart(document.getElementById('incomeExpenseChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [
                    {
                        label: 'Penerimaan',
                        data: receiptsData,
                        backgroundColor: '#10b981',
                        borderRadius: 8,
                        maxBarThickness: 25
                    },
                    {
                        label: 'Pengeluaran',
                        data: expensesData,
                        backgroundColor: '#ef4444',
                        borderRadius: 8,
                        maxBarThickness: 25
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#000',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: {
                            color: '#9ca3af',
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<style>
.icon-box {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.progress-bar {
    transition: width 0.6s ease;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

/* Responsive */
@media (max-width: 768px) {
    .card-body h2 {
        font-size: 1.5rem !important;
    }
    
    .icon-box {
        width: 40px;
        height: 40px;
    }
}
</style>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.coreui', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/dashboard.blade.php ENDPATH**/ ?>