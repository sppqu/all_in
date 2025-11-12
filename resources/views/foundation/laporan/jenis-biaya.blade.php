@extends('layouts.coreui')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Dashboard Laporan Keuangan</h2>
        <button class="btn btn-success">
            <i class="fa fa-file-excel me-2"></i> EXPORT EXCEL
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('manage.foundation.laporan.jenis-biaya') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Sekolah</label>
                    <select name="school_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Sekolah</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ $selectedSchoolId == $school->id ? 'selected' : '' }}>
                                {{ $school->nama_sekolah }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tahun Ajaran</label>
                    <select name="period_id" class="form-select" onchange="this.form.submit()">
                        @foreach($periods as $period)
                            <option value="{{ $period->period_id }}" {{ $selectedPeriodId == $period->period_id ? 'selected' : '' }}>
                                {{ $period->period_start }}/{{ $period->period_end }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mode Rentang</label>
                    <select name="range_mode" class="form-select" onchange="this.form.submit()">
                        <option value="per_bulan" {{ $rangeMode == 'per_bulan' ? 'selected' : '' }}>Per Bulan</option>
                        <option value="per_tahun" {{ $rangeMode == 'per_tahun' ? 'selected' : '' }}>Per Tahun</option>
                    </select>
                </div>
                @if($rangeMode == 'per_bulan')
                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <input type="month" name="month" class="form-select" value="{{ $monthYear ?? date('Y-m') }}" onchange="this.form.submit()">
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Ringkasan Total Keuangan -->
    <div class="mb-4">
        <h5 class="fw-bold mb-3">Ringkasan Total Keuangan ({{ $selectedPeriod ? $selectedPeriod->period_start . '/' . $selectedPeriod->period_end : '' }})</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background-color: #e3f2fd;">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2" style="color: #1976d2; font-weight: 600;">GRAND TOTAL DITAGIH</h6>
                        <h3 class="card-title mb-0 fw-bold" style="color: #0d47a1;">Rp {{ number_format($grandTotalDitagih, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background-color: #e8f5e9;">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2" style="color: #388e3c; font-weight: 600;">GRAND TOTAL TERBAYAR</h6>
                        <h3 class="card-title mb-0 fw-bold" style="color: #1b5e20;">Rp {{ number_format($grandTotalTerbayar, 0, ',', '.') }}</h3>
                        <small class="text-muted" style="color: #2e7d32;">{{ number_format($paymentPercentage, 1) }}% Lunas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="background-color: #ffebee;">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2" style="color: #d32f2f; font-weight: 600;">GRAND TOTAL TUNGGAKAN</h6>
                        <h3 class="card-title mb-0 fw-bold" style="color: #b71c1c;">Rp {{ number_format($grandTotalTunggakan, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Cards: 5 Tunggakan Terbesar & 5 Siswa Penunggak -->
    <div class="row mb-4">
        <!-- 5 Tunggakan Terbesar (Biaya Lainnya) -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">5 Tunggakan Terbesar (Biaya Lainnya)</h6>
                </div>
                <div class="card-body">
                    @if($top5Arrears->count() > 0)
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="arrearsChart"></canvas>
                        </div>
                    @else
                        <div class="chart-container" style="height: 250px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-center">
                                <p class="text-muted small mb-0">Tidak ada data</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 5 Siswa Penunggak Terbesar -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">5 Siswa Penunggak Terbesar</h6>
                </div>
                <div class="card-body">
                    @forelse($top5DelinquentStudents as $student)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <div class="fw-bold mb-1">{{ $student->student_full_name }}</div>
                            <small class="text-muted">{{ $student->class_name }}</small>
                        </div>
                        <div class="text-danger fw-bold text-end">
                            Rp {{ number_format($student->total_tunggakan, 0, ',', '.') }}
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center small">Tidak ada data</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan SPP -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Ringkasan SPP ({{ $selectedPeriod ? $selectedPeriod->period_start . '/' . $selectedPeriod->period_end : '' }})</h5>
        </div>
        <div class="card-body">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <small class="text-muted d-block mb-2">Total Ditagih</small>
                        <h4 class="mb-0 fw-bold">Rp {{ number_format($sppBulanData->sum('ditagih'), 0, ',', '.') }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <small class="text-muted d-block mb-2">Total Terbayar</small>
                        <h4 class="mb-0 fw-bold">Rp {{ number_format($sppBulanData->sum('terbayar'), 0, ',', '.') }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <small class="text-muted d-block mb-2">Sisa (Tunggakan)</small>
                        <h4 class="mb-0 fw-bold text-danger">Rp {{ number_format($sppBulanData->sum('sisa'), 0, ',', '.') }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <small class="text-muted d-block mb-2">Siswa Bayar / Belum</small>
                        <h4 class="mb-0 fw-bold">{{ $sppBulanData->sum('siswa_bayar') }} / {{ $sppBulanData->sum('siswa_belum') }}</h4>
                    </div>
                </div>
            </div>
            
            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th class="text-end">Ditagih</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-end">Siswa Bayar</th>
                            <th class="text-end">Siswa Belum</th>
                            <th>Per Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sppBulanData as $bulan)
                        <tr>
                            <td>{{ $bulan->bulan }}</td>
                            <td class="text-end">Rp {{ number_format($bulan->ditagih, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($bulan->terbayar, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($bulan->sisa, 0, ',', '.') }}</td>
                            <td class="text-end">{{ $bulan->siswa_bayar }}</td>
                            <td class="text-end">{{ $bulan->siswa_belum }}</td>
                            <td>
                                <a href="#" class="text-primary text-decoration-none">► Lihat</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Tidak ada data SPP untuk rentang ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ringkasan Biaya Lainnya -->
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Ringkasan Biaya Lainnya ({{ $selectedPeriod ? $selectedPeriod->period_start . '/' . $selectedPeriod->period_end : '' }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Jenis Biaya</th>
                            <th class="text-end">Ditagih</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-end">Siswa Bayar</th>
                            <th class="text-end">Siswa Belum</th>
                            <th>Per Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($otherCostsData as $cost)
                        <tr>
                            <td>{{ $cost->jenis_biaya }}</td>
                            <td class="text-end">Rp {{ number_format($cost->ditagih, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($cost->terbayar, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($cost->sisa, 0, ',', '.') }}</td>
                            <td class="text-end">{{ $cost->siswa_bayar }}</td>
                            <td class="text-end">{{ $cost->siswa_belum }}</td>
                            <td>
                                <a href="#" class="text-primary text-decoration-none">► Lihat</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Tidak ada data biaya lainnya pada rentang ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart for 5 Largest Arrears
    @if($top5Arrears->count() > 0)
    const ctx = document.getElementById('arrearsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($top5Arrears->pluck('jenis_biaya')->toArray()) !!},
                datasets: [{
                    label: 'Tunggakan',
                    data: {!! json_encode($top5Arrears->pluck('sisa')->toArray()) !!},
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    @endif
</script>
@endsection

