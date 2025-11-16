@extends('layouts.adminty')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Top Row: Statistik Pembayaran & Traffic Pembayaran -->
    <div class="row mb-4">
        <!-- Statistik Pembayaran (Line Chart) -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px; height: 100%;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.9rem;">💰 Statistik Pembayaran</h6>
                            <h3 class="fw-bold text-primary mb-0">Rp {{ number_format($totalPaymentsThisYear ?? 0, 0, ',', '.') }}</h3>
                            <small class="text-muted">Januari - {{ now()->format('F Y') }}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-pill" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('payment.index') }}"><i class="fas fa-list me-2"></i>Lihat Semua</a></li>
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

        <!-- Traffic Pembayaran (Bar Chart) -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px; height: 100%;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted mb-0" style="font-size: 0.9rem;">📊 Transaksi Per Bulan</h6>
                        <span class="badge bg-success">2024</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Januari 1, 2024 - Desember 31, 2024</small>
                    </div>
                    <div style="height: 220px;">
                        <canvas id="transactionsBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Jumlah Siswa & Persentase Pembayaran -->
    <div class="row mb-4">
        <!-- Jumlah Siswa Per Kelas (Card) -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-white bg-opacity-25 rounded-circle p-3 me-3">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0" style="font-size: 0.85rem; opacity: 0.9;">Total Siswa</h6>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">{{ number_format($totalStudents ?? 0) }}</h2>
                    <div class="d-flex align-items-center">
                        @php
                            $studentGrowth = $studentGrowthPercent ?? 0;
                            $isGrowthPositive = $studentGrowth >= 0;
                        @endphp
                        <span class="badge {{ $isGrowthPositive ? 'bg-success' : 'bg-danger' }} me-2">
                            <i class="fas fa-arrow-{{ $isGrowthPositive ? 'up' : 'down' }} me-1"></i>{{ abs($studentGrowth) }}%
                        </span>
                        <small style="opacity: 0.9;">vs bulan lalu</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Hari Ini -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-white bg-opacity-25 rounded-circle p-3 me-3">
                            <i class="fas fa-receipt fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0" style="font-size: 0.85rem; opacity: 0.9;">Transaksi Hari Ini</h6>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">{{ number_format($todayTransactions ?? 0) }}</h2>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-white text-dark me-2">
                            Rp {{ number_format($todayPayments ?? 0, 0, ',', '.') }}
                        </span>
                        <small style="opacity: 0.9;">total</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Persentase Lunas -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-white bg-opacity-25 rounded-circle p-3 me-3">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0" style="font-size: 0.85rem; opacity: 0.9;">Pembayaran Lunas</h6>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">{{ number_format($paymentCompletionPercent ?? 0, 1) }}%</h2>
                    <div class="progress bg-white bg-opacity-25" style="height: 6px; border-radius: 10px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: {{ $paymentCompletionPercent ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tunggakan -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-white bg-opacity-25 rounded-circle p-3 me-3">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0" style="font-size: 0.85rem; opacity: 0.9;">Total Tunggakan</h6>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2" style="font-size: 1.5rem;">Rp {{ number_format($totalArrears ?? 0, 0, ',', '.') }}</h2>
                    <div class="d-flex align-items-center">
                        <small style="opacity: 0.9;">{{ $arrearsCount ?? 0 }} siswa</small>
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
                    <span class="badge bg-light text-dark">Total: {{ $totalStudents ?? 0 }}</span>
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
                            @foreach(($periods ?? []) as $p)
                                <option value="{{ $p->period_id }}" {{ ($selectedPeriodId ?? '') == $p->period_id ? 'selected' : '' }}>
                                    {{ $p->period_start }}/{{ $p->period_end }}
                                </option>
                            @endforeach
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
                                @forelse($paymentProgressByClass ?? [] as $index => $class)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $class['class_name'] }}</strong></td>
                                    <td>{{ $class['total_students'] }} siswa</td>
                                    <td><span class="badge bg-success">{{ $class['paid_students'] }}</span></td>
                                    <td><span class="badge bg-danger">{{ $class['unpaid_students'] }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 20px; border-radius: 10px;">
                                                <div class="progress-bar bg-gradient" 
                                                     style="width: {{ $class['percentage'] }}%; 
                                                            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);" 
                                                     role="progressbar">
                                                    <span class="small fw-bold">{{ number_format($class['percentage'], 1) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                                @endforelse
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
        new Chart(document.getElementById('paymentsLineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($monthLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']),
                datasets: [{
                    label: 'Pembayaran',
                    data: @json(array_values($paymentsMonthly ?? [])),
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

    // 2. Traffic Transaksi (Bar Chart)
    if (document.getElementById('transactionsBarChart')) {
        new Chart(document.getElementById('transactionsBarChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($monthLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']),
                datasets: [{
                    data: @json(array_values($transactionsMonthly ?? [])),
                    backgroundColor: '#5b6ee1',
                    borderRadius: 8,
                    maxBarThickness: 20
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
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af', font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: { color: '#9ca3af', font: { size: 10 } }
                    }
                }
            }
        });
    }

    // 3. Distribusi Siswa Per Kelas (Doughnut)
    if (document.getElementById('studentsPerClassChart')) {
        new Chart(document.getElementById('studentsPerClassChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json($classLabels ?? []),
                datasets: [{
                    data: @json($classData ?? []),
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
        new Chart(document.getElementById('incomeExpenseChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($monthLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']),
                datasets: [
                    {
                        label: 'Penerimaan',
                        data: @json(array_values($receiptsMonthly ?? [])),
                        backgroundColor: '#10b981',
                        borderRadius: 8,
                        maxBarThickness: 25
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json(array_values($expensesMonthly ?? [])),
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
@endsection

