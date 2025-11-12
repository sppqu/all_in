@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">Dashboard Yayasan</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ $foundation->nama_yayasan }}</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Tahun Ajaran Selector -->
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    TA: {{ $selectedPeriod ? $selectedPeriod->period_start . '/' . $selectedPeriod->period_end : date('Y') . '/' . (date('Y') + 1) }}
                </button>
                <ul class="dropdown-menu">
                    @foreach(\App\Models\Period::orderBy('period_start', 'desc')->get() as $period)
                    <li>
                        <a class="dropdown-item" href="{{ url('/manage/foundation/dashboard?period_id=' . $period->period_id) }}">
                            {{ $period->period_start }}/{{ $period->period_end }}
                            @if($period->period_status)
                                <span class="badge bg-success ms-2">Aktif</span>
                            @endif
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2" style="font-size: 0.8rem;">Pemasukan Hari Ini</h6>
                            <h5 class="fw-bold mb-0" style="font-size: 1.15rem;">Rp {{ number_format($totalIncomeToday, 0, ',', '.') }}</h5>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-calendar-day fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2" style="font-size: 0.8rem;">Pemasukan Bulan Ini</h6>
                            <h5 class="fw-bold mb-0" style="font-size: 1.15rem;">Rp {{ number_format($totalIncomeMonth, 0, ',', '.') }}</h5>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-calendar-alt fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2" style="font-size: 0.8rem;">Total Semua Pemasukan</h6>
                            <h5 class="fw-bold text-success mb-0" style="font-size: 1.15rem;">Rp {{ number_format($totalIncomeAll, 0, ',', '.') }}</h5>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2" style="font-size: 0.8rem;">Total Tunggakan (Thn Lalu & Ini)</h6>
                            <h5 class="fw-bold text-danger mb-0" style="font-size: 1.15rem;">Rp {{ number_format($totalArrears, 0, ',', '.') }}</h5>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rincian Pemasukan & Tunggakan per Sekolah -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h6 class="fw-bold mb-0" style="font-size: 1.1rem;">Rincian Pemasukan & Tunggakan per Sekolah</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr style="font-size: 0.9rem;">
                                    <th style="font-size: 0.9rem;">Nama Sekolah</th>
                                    <th class="text-center" style="font-size: 0.9rem;">Jumlah Siswa Aktif</th>
                                    <th class="text-end" style="font-size: 0.9rem;">Pemasukan T.A. Lalu</th>
                                    <th class="text-end" style="font-size: 0.9rem;">Pemasukan T.A. Ini</th>
                                    <th class="text-end" style="font-size: 0.9rem;">Tunggakan T.A. Lalu</th>
                                    <th class="text-end" style="font-size: 0.9rem;">Tunggakan T.A. Ini</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schoolStats as $stat)
                                <tr>
                                    <td style="font-size: 0.9rem;">
                                        <strong>{{ $stat['school']->nama_sekolah }}</strong>
                                        <br>
                                        <small class="text-muted" style="font-size: 0.8rem;">{{ $stat['school']->jenjang }}</small>
                                    </td>
                                    <td class="text-center" style="font-size: 0.9rem;">
                                        <span class="badge bg-primary" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">{{ number_format($stat['total_students'], 0, ',', '.') }} Siswa</span>
                                    </td>
                                    <td class="text-end" style="font-size: 0.9rem;">Rp {{ number_format($stat['income_last_year'], 0, ',', '.') }}</td>
                                    <td class="text-end" style="font-size: 0.9rem;">Rp {{ number_format($stat['income_this_year'], 0, ',', '.') }}</td>
                                    <td class="text-end text-danger" style="font-size: 0.9rem;">Rp {{ number_format($stat['arrears_last_year'], 0, ',', '.') }}</td>
                                    <td class="text-end text-danger" style="font-size: 0.9rem;">Rp {{ number_format($stat['arrears_this_year'], 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-muted mb-0">Belum ada sekolah terdaftar</p>
                                    </td>
                                </tr>
                                @endforelse
                                @if(count($schoolStats) > 0)
                                <tr class="table-primary fw-bold" style="font-size: 0.95rem;">
                                    <td>Total</td>
                                    <td class="text-center">
                                        <span class="badge bg-success" style="font-size: 0.85rem;">{{ number_format($totalActiveStudents, 0, ',', '.') }} Siswa</span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format(collect($schoolStats)->sum('income_last_year'), 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format(collect($schoolStats)->sum('income_this_year'), 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format(collect($schoolStats)->sum('arrears_last_year'), 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format(collect($schoolStats)->sum('arrears_this_year'), 0, ',', '.') }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pemasukan Harian (7 Hari Terakhir) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h6 class="fw-bold mb-0" style="font-size: 1.1rem;">Pemasukan Harian (7 Hari Terakhir)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr style="font-size: 0.9rem;">
                                    <th style="font-size: 0.9rem;">Nama Sekolah</th>
                                    @for($i = 0; $i < 7; $i++)
                                    <th class="text-end" style="font-size: 0.9rem;">{{ now()->subDays($i)->format('d/m') }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schoolStats as $stat)
                                <tr>
                                    <td style="font-size: 0.9rem;"><strong>{{ $stat['school']->nama_sekolah }}</strong></td>
                                    @for($i = 0; $i < 7; $i++)
                                    @php
                                        $date = now()->subDays($i)->format('d/m');
                                        $income = $stat['daily_income'][$date] ?? 0;
                                    @endphp
                                    <td class="text-end" style="font-size: 0.9rem;">Rp {{ number_format($income, 0, ',', '.') }}</td>
                                    @endfor
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <p class="text-muted mb-0">Belum ada data pemasukan</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rekap Statistik Jumlah Siswa Per Sekolah -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h6 class="fw-bold mb-0" style="font-size: 1.1rem;">Rekap Statistik Jumlah Siswa Per Sekolah (T.A. Aktif)</h6>
                </div>
                <div class="card-body">
                    @if(count($studentStats) > 0)
                        @foreach($studentStats as $stat)
                        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <span style="font-size: 0.9rem;"><strong>{{ $stat['school_name'] }}</strong></span>
                            <span class="badge bg-primary" style="font-size: 0.85rem;">Total Aktif: {{ number_format($stat['total_students'], 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                        <div class="d-flex justify-content-between align-items-center py-2 mt-2 pt-3 border-top">
                            <span class="fw-bold" style="font-size: 0.95rem;">Total</span>
                            <span class="badge bg-success" style="font-size: 0.85rem;">Total Aktif: {{ number_format($totalActiveStudents, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <p class="text-muted mb-0 text-center py-3">Belum ada data siswa aktif</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h6 class="fw-bold mb-0" style="font-size: 1.1rem;">Statistik Jumlah Siswa per Sekolah (T.A. Aktif)</h6>
                </div>
                <div class="card-body">
                    @if(count($studentStats) > 0)
                    <canvas id="studentChart" height="200"></canvas>
                    @else
                    <p class="text-muted mb-0 text-center py-4">Belum ada data untuk ditampilkan</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if(count($studentStats) > 0)
    const ctx = document.getElementById('studentChart').getContext('2d');
    const studentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($studentStats)->pluck('school_name')) !!},
            datasets: [{
                label: 'Jumlah Siswa Aktif',
                data: {!! json_encode(collect($studentStats)->pluck('total_students')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush
@endsection

