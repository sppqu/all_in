@extends('layouts.adminty')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fa fa-chart-line me-2"></i>
                    Laporan Arus KAS
                </h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('manage.arus-kas.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="fa fa-file-excel me-1"></i> Export Excel
                    </a>
                    <a href="{{ route('manage.arus-kas.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-success" target="_blank">
                        <i class="fa fa-file-pdf me-1"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tanggal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('manage.arus-kas.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fa fa-search me-1"></i> Filter
                            </button>
                            <a href="{{ route('manage.arus-kas.index') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fa fa-arrow-down fa-2x"></i>
                    </div>
                    <h5 class="card-title text-success">Total Pemasukan</h5>
                    <h3 class="text-success">Rp {{ number_format($totalPemasukan) }}</h3>
                    <small class="text-muted">{{ $startDate }} - {{ $endDate }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="fa fa-arrow-up fa-2x"></i>
                    </div>
                    <h5 class="card-title text-danger">Total Pengeluaran</h5>
                    <h3 class="text-danger">Rp {{ number_format($totalPengeluaran) }}</h3>
                    <small class="text-muted">{{ $startDate }} - {{ $endDate }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card {{ $saldoKas >= 0 ? 'border-primary' : 'border-warning' }}">
                <div class="card-body text-center">
                    <div class="{{ $saldoKas >= 0 ? 'text-primary' : 'text-warning' }} mb-2">
                        <i class="fa fa-wallet fa-2x"></i>
                    </div>
                    <h5 class="card-title {{ $saldoKas >= 0 ? 'text-primary' : 'text-warning' }}">Saldo KAS</h5>
                    <h3 class="{{ $saldoKas >= 0 ? 'text-primary' : 'text-warning' }}">
                        Rp {{ number_format(abs($saldoKas)) }}
                        @if($saldoKas < 0)
                            <small class="text-warning">(Defisit)</small>
                        @endif
                    </h3>
                    <small class="text-muted">{{ $startDate }} - {{ $endDate }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fa fa-chart-area me-2"></i>
                        Grafik Arus KAS
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="arusKasChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Transaksi -->
    <div class="row">
        <!-- Pemasukan -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-arrow-down me-2"></i>
                        Detail Pemasukan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($detailPemasukan as $pemasukan)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($pemasukan->tanggal_penerimaan)->format('d/m/Y') }}</td>
                                    <td>{{ $pemasukan->keterangan_transaksi }}</td>
                                    <td class="text-end text-success">Rp {{ number_format($pemasukan->total_penerimaan) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Tidak ada data pemasukan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengeluaran -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-arrow-up me-2"></i>
                        Detail Pengeluaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($detailPengeluaran as $pengeluaran)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($pengeluaran->tanggal_pengeluaran)->format('d/m/Y') }}</td>
                                    <td>{{ $pengeluaran->keterangan_transaksi }}</td>
                                    <td class="text-end text-danger">Rp {{ number_format($pengeluaran->total_pengeluaran) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Tidak ada data pengeluaran</td>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data untuk chart
    const chartData = @json($chartData);
    
    // Siapkan data untuk Chart.js
    const labels = chartData.map(item => {
        const date = new Date(item.tanggal);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit' });
    });
    
    const pemasukanData = chartData.map(item => item.pemasukan);
    const pengeluaranData = chartData.map(item => item.pengeluaran);
    const saldoData = chartData.map(item => item.saldo);
    
    // Buat chart
    const ctx = document.getElementById('arusKasChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: pemasukanData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Pengeluaran',
                    data: pengeluaranData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Saldo KAS',
                    data: saldoData,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: false,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Arus KAS Periode {{ $startDate }} - {{ $endDate }}'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});

// Export functions sudah tidak diperlukan karena menggunakan link langsung
</script>
@endsection
