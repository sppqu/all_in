@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark">
                <i class="fas fa-chart-line me-2 text-info"></i>Rekap Bulanan Jurnal
            </h2>
            <p class="text-muted mb-0">Grafik Perkembangan 7 Kebiasaan Anak Indonesia Hebat</p>
        </div>
        <div>
            <a href="{{ route('jurnal.siswa.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Filter Bulan -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('jurnal.siswa.rekap-bulanan') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Bulan</label>
                    <select name="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tahun</label>
                    <select name="year" class="form-select">
                        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white text-center">
                    <i class="fas fa-book fa-2x mb-2 opacity-75"></i>
                    <h3 class="mb-1">{{ $jurnals->count() }}</h3>
                    <p class="mb-0 small">Jurnal Terisi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white text-center">
                    <i class="fas fa-calendar-check fa-2x mb-2 opacity-75"></i>
                    <h3 class="mb-1">{{ cal_days_in_month(CAL_GREGORIAN, $month, $year) }}</h3>
                    <p class="mb-0 small">Total Hari</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white text-center">
                    <i class="fas fa-tasks fa-2x mb-2 opacity-75"></i>
                    @php
                        $totalKegiatan = 0;
                        foreach($jurnals as $j) {
                            $totalKegiatan += $j->entries->count();
                        }
                    @endphp
                    <h3 class="mb-1">{{ $totalKegiatan }}</h3>
                    <p class="mb-0 small">Total Kegiatan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white text-center">
                    <i class="fas fa-star fa-2x mb-2 opacity-75"></i>
                    @php
                        $avgNilai = 0;
                        if($jurnals->count() > 0) {
                            $totalNilai = 0;
                            $totalEntry = 0;
                            foreach($jurnals as $j) {
                                foreach($j->entries as $e) {
                                    $totalNilai += $e->nilai;
                                    $totalEntry++;
                                }
                            }
                            $avgNilai = $totalEntry > 0 ? $totalNilai / $totalEntry : 0;
                        }
                    @endphp
                    <h3 class="mb-1">{{ number_format($avgNilai, 1) }}</h3>
                    <p class="mb-0 small">Rata-rata Nilai</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Per Kategori -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Grafik Perkembangan Per Kategori</h5>
        </div>
        <div class="card-body">
            <canvas id="chartPerKategori" style="max-height: 400px;"></canvas>
        </div>
    </div>

    <!-- Tabel Detail Per Kategori -->
    <div class="row g-4">
        @foreach($rekapPerKategori as $kategoriId => $rekap)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header" style="background: {{ $rekap['kategori']->warna }}20; border-left: 4px solid {{ $rekap['kategori']->warna }};">
                    <h6 class="mb-0" style="color: {{ $rekap['kategori']->warna }};">
                        <i class="{{ $rekap['kategori']->icon }} me-2"></i>{{ $rekap['kategori']->nama_kategori }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Jumlah Entry</span>
                            <span class="badge bg-secondary">{{ $rekap['total_entry'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Total Nilai</span>
                            <strong>{{ $rekap['total_nilai'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Rata-rata</span>
                            @php
                                $avg = $rekap['rata_rata'];
                                $badgeClass = $avg >= 8 ? 'success' : ($avg >= 6 ? 'info' : ($avg >= 4 ? 'warning' : 'danger'));
                            @endphp
                            <span class="badge bg-{{ $badgeClass }}">{{ number_format($avg, 2) }}</span>
                        </div>
                    </div>
                    
                    @if(count($rekap['nilai_list']) > 0)
                    <div class="mt-3">
                        <canvas id="sparkline_{{ $kategoriId }}" height="60"></canvas>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Tren Harian -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Tren Harian Bulan Ini</h5>
        </div>
        <div class="card-body">
            <canvas id="chartTrenHarian" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Daftar Jurnal -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Jurnal</h5>
        </div>
        <div class="card-body">
            @if($jurnals->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Kegiatan</th>
                            <th>Total Nilai</th>
                            <th>Rata-rata</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jurnals as $jurnal)
                        <tr>
                            <td>{{ $jurnal->tanggal->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $jurnal->entries->count() }}</span>
                            </td>
                            <td><strong>{{ $jurnal->total_nilai }}</strong></td>
                            <td>
                                @php
                                    $avg = $jurnal->rata_rata_nilai;
                                    $badgeClass = $avg >= 8 ? 'success' : ($avg >= 6 ? 'info' : ($avg >= 4 ? 'warning' : 'danger'));
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">{{ number_format($avg, 1) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $jurnal->status_badge }}">
                                    {{ ucfirst($jurnal->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('jurnal.siswa.show', $jurnal->jurnal_id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada jurnal di bulan ini.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data dari PHP
    const rekapData = @json($rekapPerKategori);
    const kategoriData = @json($kategori);
    
    // Chart Per Kategori (Bar Chart)
    const ctxBar = document.getElementById('chartPerKategori').getContext('2d');
    const labels = [];
    const dataRataRata = [];
    const dataTotal = [];
    const colors = [];
    
    kategoriData.forEach(kat => {
        const rekap = rekapData[kat.kategori_id];
        if (rekap) {
            labels.push(kat.nama_kategori);
            dataRataRata.push(rekap.rata_rata);
            dataTotal.push(rekap.total_entry);
            colors.push(kat.warna);
        }
    });
    
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Rata-rata Nilai',
                data: dataRataRata,
                backgroundColor: colors.map(c => c + '80'),
                borderColor: colors,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            const index = context.dataIndex;
                            return 'Total Entry: ' + dataTotal[index];
                        }
                    }
                }
            }
        }
    });
    
    // Sparklines untuk setiap kategori
    kategoriData.forEach(kat => {
        const rekap = rekapData[kat.kategori_id];
        if (rekap && rekap.nilai_list.length > 0) {
            const canvas = document.getElementById('sparkline_' + kat.kategori_id);
            if (canvas) {
                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: rekap.nilai_list.map((v, i) => i + 1),
                        datasets: [{
                            data: rekap.nilai_list,
                            borderColor: kat.warna,
                            backgroundColor: kat.warna + '20',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { display: false },
                            y: { display: false }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        }
                    }
                });
            }
        }
    });
    
    // Tren Harian (Line Chart)
    const jurnalsData = @json($jurnals);
    const trenLabels = [];
    const trenData = [];
    
    jurnalsData.forEach(jurnal => {
        trenLabels.push(new Date(jurnal.tanggal).getDate());
        trenData.push(jurnal.rata_rata_nilai);
    });
    
    const ctxLine = document.getElementById('chartTrenHarian').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: trenLabels,
            datasets: [{
                label: 'Rata-rata Nilai Harian',
                data: trenData,
                borderColor: '#667eea',
                backgroundColor: '#667eea20',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#667eea'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tanggal'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
});
</script>

<style>
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}
</style>
@endsection

