@extends('layouts.student')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Welcome Section -->
    @php
        // Get student bill statistics from bebas table
        $studentId = session('student_id');
        
        // Get all bills (bebas)
        $allBills = \DB::table('bebas')->where('student_student_id', $studentId)->get();
        $totalBills = $allBills->count();
        
        // Count paid bills (bebas_total_pay >= bebas_bill)
        $paidBills = $allBills->filter(function($bill) {
            return $bill->bebas_total_pay >= $bill->bebas_bill;
        })->count();
        
        $unpaidBills = $totalBills - $paidBills;
        $progressPercentage = $totalBills > 0 ? round(($paidBills / $totalBills) * 100) : 0;
    @endphp
    <div class="welcome-banner mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h6 class="mb-1 fw-normal text-white-50">Selamat Datang 👋</h6>
                <h4 class="mb-1 fw-bold text-white">{{ session('student_name') }}</h4>
                <p class="mb-0 text-white-50 small">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</p>
            </div>
            <div>
                @if($totalBills > 0)
                <div class="chart-container">
                    <canvas id="billProgressChart"></canvas>
                    <div class="chart-center-text">
                        <div class="chart-percentage">{{ $progressPercentage }}%</div>
                        <div class="chart-label">Lunas</div>
                    </div>
                </div>
                @else
                <div class="chart-container">
                    <div class="no-bill-badge">
                        <i class="fas fa-check-circle"></i>
                        <div class="no-bill-text">Belum Ada Tagihan</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Pending Payment Alert -->
    @php
        $pendingPayments = \DB::table('transfer')
            ->where('student_id', session('student_id'))
            ->where('status', 0)
            ->whereNotNull('checkout_url')
            ->count();
    @endphp
    
    @if($pendingPayments > 0)
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px; border-left: 5px solid #ffc107;">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Pembayaran Belum Selesai!</strong>
                <p class="mb-0 small">Anda memiliki {{ $pendingPayments }} pembayaran yang belum selesai. 
                    <a href="{{ route('student.payment.history') }}" class="alert-link fw-bold">Klik di sini</a> untuk melanjutkan.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h6 class="mb-3 fw-bold" style="color: #2c3e50;">
            <i class="fas fa-bolt me-2" style="color: #f39c12;"></i>Aksi Cepat
        </h6>
    <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="{{ route('student.bills') }}" class="quick-action-card">
                    <div class="quick-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-receipt"></i>
                </div>
                    <span>Lihat Tagihan</span>
            </a>
        </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('student.tabungan') }}" class="quick-action-card">
                    <div class="quick-icon" style="background: linear-gradient(135deg, #fc4a1a, #f7b733);">
                        <i class="fas fa-bank"></i>
                        </div>
                    <span>Tabungan</span>
                </a>
                        </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('student.jurnal.create') }}" class="quick-action-card">
                    <div class="quick-icon" style="background: linear-gradient(135deg, #6f42c1, #9d5bd2);">
                        <i class="fas fa-pen"></i>
                    </div>
                    <span>Jurnal</span>
            </a>
        </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('student.payment.history') }}" class="quick-action-card">
                    <div class="quick-icon" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                        <i class="fas fa-history"></i>
                </div>
                    <span>Riwayat Bayar</span>
            </a>
        </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('student.bk.index') }}" class="quick-action-card">
                    <div class="quick-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <span>BK Siswa</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('student.library') }}" class="quick-action-card">
                    <div class="quick-icon" style="background: linear-gradient(135deg,rgb(22, 54, 197),rgb(56, 161, 231));">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <span>E-Perpus</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, #008060 0%, #006d52 100%);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 8px 25px rgba(0, 128, 96, 0.3);
}

/* Chart Container */
.chart-container {
    position: relative;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.chart-container canvas {
    max-width: 100%;
    max-height: 100%;
}

.chart-center-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
    z-index: 10;
}

.chart-percentage {
    font-size: 1.2rem;
    font-weight: bold;
    color: white;
    line-height: 1;
}

.chart-label {
    font-size: 0.6rem;
    color: rgba(255, 255, 255, 0.8);
    margin-top: 2px;
}

.no-bill-badge {
    text-align: center;
    padding: 15px 10px;
}

.no-bill-badge i {
    font-size: 1.5rem;
    color: white;
    opacity: 0.5;
}

.no-bill-text {
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.7);
    margin-top: 5px;
}

/* Desktop Chart Size */
@media (min-width: 768px) {
    .chart-container {
        width: 120px;
        height: 120px;
    }
    
    .chart-percentage {
        font-size: 1.5rem;
    }
    
    .chart-label {
        font-size: 0.7rem;
    }
    
    .no-bill-badge {
        padding: 20px;
    }
    
    .no-bill-badge i {
        font-size: 2rem;
    }
    
    .no-bill-text {
        font-size: 0.75rem;
    }
}

/* Quick Actions */
.quick-actions {
    margin-top: 30px;
}

.quick-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 20px 15px;
    background: white;
    border-radius: 15px;
    text-decoration: none;
    color: #2c3e50;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    min-height: 120px;
}

.quick-action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    color: #2c3e50;
}

.quick-icon {
    width: 55px;
    height: 55px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-bottom: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.quick-action-card span {
    font-size: 0.85rem;
    font-weight: 600;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .welcome-banner {
        padding: 15px;
    }
    
    .welcome-banner h6 {
        font-size: 0.75rem;
    }
    
    .welcome-banner h4 {
        font-size: 1rem;
    }
    
    .welcome-banner p {
        font-size: 0.7rem;
    }
    
    .quick-action-card {
        min-height: 100px;
        padding: 15px 10px;
    }
    
    .quick-icon {
        width: 45px;
        height: 45px;
        font-size: 1.3rem;
    }
    
    .quick-action-card span {
        font-size: 0.75rem;
    }
}

</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Bill Progress Chart...');
    
    // Bill Progress Donut Chart
    const ctx = document.getElementById('billProgressChart');
    
    if (!ctx) {
        console.error('Canvas element not found!');
        return;
    }
    
    const paidBills = {{ $paidBills }};
    const unpaidBills = {{ $unpaidBills }};
    const totalBills = {{ $totalBills }};
    
    console.log('Bills data:', { paidBills, unpaidBills, totalBills });
    
    try {
        const myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Lunas', 'Belum Lunas'],
                datasets: [{
                    data: [paidBills, unpaidBills],
                    backgroundColor: [
                        '#10B981', // Green for paid
                        '#EF4444'  // Red for unpaid
                    ],
                    borderColor: [
                        '#ffffff',
                        '#ffffff'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
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
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const percentage = totalBills > 0 ? Math.round((value / totalBills) * 100) : 0;
                                return label + ': ' + value + ' tagihan (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        console.log('Chart initialized successfully!', myChart);
    } catch(error) {
        console.error('Error creating chart:', error);
    }
});
</script>
@endpush

@endsection 
