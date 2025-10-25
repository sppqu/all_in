@extends('layouts.student')

@section('title', 'Rekap Bulanan - ' . $monthName)

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('student.jurnal.rekap') }}" class="btn btn-sm btn-outline-secondary me-3" style="border-radius: 10px;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-1" style="color: #6f42c1;">📊 Rekap {{ $monthName }}</h5>
            <p class="text-muted small mb-0">{{ $journals->count() }} jurnal ditemukan</p>
        </div>
    </div>

    <!-- Chart: Rata-rata Per Kategori -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3" style="color: #6f42c1;">
                <i class="fas fa-chart-bar me-2"></i>Rata-rata Nilai Per Kategori
            </h6>
            <canvas id="categoryChart" height="250"></canvas>
        </div>
    </div>

    <!-- Chart: Tren Harian -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3" style="color: #6f42c1;">
                <i class="fas fa-chart-line me-2"></i>Tren Nilai Harian
            </h6>
            <canvas id="dailyTrendChart" height="200"></canvas>
        </div>
    </div>

    <!-- Detail Per Kategori -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3" style="color: #6f42c1;">
                <i class="fas fa-list-check me-2"></i>Detail Per Kategori
            </h6>
            @foreach($categories as $category)
            @php
                $avg = $categoryAverages[$category->nama_kategori] ?? 0;
                $percentage = ($avg / 10) * 100;
            @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="d-flex align-items-center">
                        <i class="{{ $category->icon }} me-2" style="color: {{ $category->warna }};"></i>
                        <span class="small fw-bold">{{ $category->nama_kategori }}</span>
                    </div>
                    <span class="badge bg-primary" style="border-radius: 8px;">{{ number_format($avg, 1) }}/10</span>
                </div>
                <div class="progress" style="height: 10px; border-radius: 10px;">
                    <div class="progress-bar" 
                         role="progressbar" 
                         style="width: {{ $percentage }}%; background: linear-gradient(135deg, {{ $category->warna }}, {{ $category->warna }}dd);" 
                         aria-valuenow="{{ $percentage }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- List Jurnal -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3" style="color: #6f42c1;">
                <i class="fas fa-history me-2"></i>Riwayat Jurnal Bulan Ini
            </h6>
            @foreach($journals as $journal)
            <div class="d-flex align-items-center p-2 mb-2 rounded {{ !$loop->last ? 'border-bottom' : '' }}">
                <div class="flex-shrink-0 me-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-calendar-day text-white small"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0 small fw-bold">{{ $journal->tanggal->format('d M Y') }}</h6>
                            <small class="text-muted">
                                <i class="fas fa-star text-warning"></i> {{ number_format($journal->entries->avg('nilai'), 1) }}/10
                            </small>
                        </div>
                        <a href="{{ route('student.jurnal.show', $journal->jurnal_id) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data
    const categoryLabels = @json(array_keys($categoryAverages));
    const categoryData = @json(array_values($categoryAverages));
    const dailyLabels = @json(array_column($dailyTrends, 'date'));
    const dailyData = @json(array_column($dailyTrends, 'score'));
    
    // Category Chart (Bar)
    const ctxCategory = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCategory, {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Rata-rata Nilai',
                data: categoryData,
                backgroundColor: [
                    'rgba(111, 66, 193, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(253, 126, 20, 0.8)',
                    'rgba(102, 16, 242, 0.8)'
                ],
                borderColor: [
                    'rgba(111, 66, 193, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(253, 126, 20, 1)',
                    'rgba(102, 16, 242, 1)'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 2
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Daily Trend Chart (Line)
    const ctxDaily = document.getElementById('dailyTrendChart').getContext('2d');
    new Chart(ctxDaily, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Nilai Harian',
                data: dailyData,
                borderColor: 'rgba(111, 66, 193, 1)',
                backgroundColor: 'rgba(111, 66, 193, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: 'rgba(111, 66, 193, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 2
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endsection

