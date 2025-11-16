@extends('layouts.adminty')

@section('content')
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm payment-card" style="background: #ffffff; color:#2a9d5f; min-height:140px; border: 1px solid #e9ecef; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <div class="payment-icon me-2">
                                <i class="fas fa-credit-card fa-lg"></i>
                            </div>
                            <div class="widget-title" style="font-size:1.25rem;font-weight:700;">Pembayaran</div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-success p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('payment.cash') }}"><i class="fas fa-cash-register me-2"></i>Pembayaran Tunai</a></li>
                                <li><a class="dropdown-item" href="{{ route('online-payment.index') }}"><i class="fas fa-globe me-2"></i>Pembayaran Online</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-chart-line me-2"></i>Lihat Laporan</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="payment-stats">
                        <div class="widget-pay-row d-flex justify-content-between align-items-center">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-day me-1"></i>Hari ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($todayPayments ?? 0) }}
                            </span>
                        </div>
                        <div class="widget-pay-row d-flex justify-content-between align-items-center">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-week me-1"></i>Bulan ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($monthPayments ?? 0) }}
                            </span>
                        </div>
                        <div class="widget-pay-row d-flex justify-content-between align-items-center mb-2">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-alt me-1"></i>Tahun ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($yearPayments ?? 0) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="payment-trend mt-2">
                        <div style="height:24px;"><canvas id="widgetPembayaranChart" height="24"></canvas></div>
                    </div>
                    
                    <div class="payment-footer mt-2 d-flex justify-content-between align-items-center">
                        <small class="text-success">
                            <i class="fas fa-info-circle me-1"></i>
                            Total transaksi: {{ $todayPaymentsCount ?? 0 }}
                        </small>
                        <small class="text-success">
                            <i class="fas fa-clock me-1"></i>
                            {{ now()->format('d/m/Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm receipt-card" style="background: #ffffff; color:#2a9d5f; min-height:140px; border: 1px solid #e9ecef; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <div class="receipt-icon me-2">
                                <i class="fas fa-arrow-down fa-lg"></i>
                            </div>
                            <div class="widget-title" style="font-size:1.25rem;font-weight:700;">Penerimaan</div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-success p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
        
                                <li><a class="dropdown-item" href="#"><i class="fas fa-chart-line me-2"></i>Lihat Laporan</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="receipt-stats">
                        <div class="widget-pay-row d-flex justify-content-between align-items-center">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-day me-1"></i>Hari ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($todayReceipts ?? 0) }}
                            </span>
                        </div>
                        <div class="widget-pay-row d-flex justify-content-between align-items-center">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-week me-1"></i>Bulan ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($monthReceipts ?? 0) }}
                            </span>
                        </div>
                        <div class="widget-pay-row d-flex justify-content-between align-items-center mb-2">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-alt me-1"></i>Tahun ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($yearReceipts ?? 0) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="receipt-trend mt-2">
                        <div style="height:24px;"><canvas id="widgetPenerimaanChart" height="24"></canvas></div>
                    </div>
                    
                    <div class="receipt-footer mt-2 d-flex justify-content-between align-items-center">
                        <small class="text-success">
                            <i class="fas fa-info-circle me-1"></i>
                            Total: {{ $todayReceiptsCount ?? 0 }}
                        </small>
                        <small class="text-success">
                            <i class="fas fa-clock me-1"></i>
                            {{ now()->format('d/m/Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm expense-card" style="background: #ffffff; color:#2a9d5f; min-height:140px; border: 1px solid #e9ecef; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <div class="expense-icon me-2">
                                <i class="fas fa-arrow-up fa-lg"></i>
                            </div>
                            <div class="widget-title" style="font-size:1.25rem;font-weight:700;">Pengeluaran</div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-success p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
        
                                <li><a class="dropdown-item" href="#"><i class="fas fa-chart-line me-2"></i>Lihat Laporan</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="expense-stats">
                        <div class="widget-pay-row d-flex justify-content-between align-items-center">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-day me-1"></i>Hari ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($todayExpenses ?? 0) }}
                            </span>
                        </div>
                        <div class="widget-pay-row d-flex justify-content-between align-items-center">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-week me-1"></i>Bulan ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($monthExpenses ?? 0) }}
                            </span>
                        </div>
                        <div class="widget-pay-row d-flex justify-content-between align-items-center mb-2">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-alt me-1"></i>Tahun ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($yearExpenses ?? 0) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="expense-trend mt-2">
                        <div style="height:24px;"><canvas id="widgetPengeluaranChart" height="24"></canvas></div>
                    </div>
                    
                    <div class="expense-footer mt-2 d-flex justify-content-between align-items-center">
                        <small class="text-success">
                            <i class="fas fa-info-circle me-1"></i>
                            Total: {{ $todayExpensesCount ?? 0 }}
                        </small>
                        <small class="text-success">
                            <i class="fas fa-clock me-1"></i>
                            {{ now()->format('d/m/Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm savings-card" style="background: #ffffff; color:#2a9d5f; min-height:140px; border: 1px solid #e9ecef; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <div class="savings-icon me-2">
                                <i class="fas fa-bank fa-lg"></i>
                            </div>
                            <div class="widget-title" style="font-size:1.25rem;font-weight:700;">Tabungan</div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-success p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('manage.tabungan.index') }}"><i class="fas fa-plus-circle me-2"></i>Kelola Tabungan</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-chart-line me-2"></i>Lihat Laporan</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="savings-stats">
                        <div class="widget-pay-row d-flex justify-content-between align-items-center">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-day me-1"></i>Hari ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($todaySavings ?? 0) }}
                            </span>
                        </div>
                        <div class="widget-pay-row d-flex justify-content-between align-items-center">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-week me-1"></i>Bulan ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($monthSavings ?? 0) }}
                            </span>
                        </div>
                        <div class="widget-pay-row d-flex justify-content-between align-items-center mb-2">
                            <span class="widget-pay-label">
                                <i class="fas fa-calendar-alt me-1"></i>Tahun ini:
                            </span>
                            <span class="widget-pay-nominal text-success">
                                Rp {{ number_format($yearSavings ?? 0) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="savings-trend mt-2">
                        <div style="height:24px;"><canvas id="widgetTabunganChart" height="24"></canvas></div>
                    </div>
                    
                    <div class="savings-footer mt-2 d-flex justify-content-between align-items-center">
                        <small class="text-success">
                            <i class="fas fa-info-circle me-1"></i>
                            Total: {{ $todaySavingsCount ?? 0 }}
                        </small>
                        <small class="text-success">
                            <i class="fas fa-clock me-1"></i>
                            {{ now()->format('d/m/Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    


    {{-- Seksi "Jenis Pembayaran" dan "Ringkasan Keuangan" dihapus sesuai permintaan --}}

    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold" style="background: #ffffff; color: #2a9d5f; border-bottom: 2px solid #2a9d5f;">Jumlah Siswa Aktif per Kelas</div>
                <div class="card-body d-flex justify-content-center align-items-center" style="height:350px;">
                    <div style="width:100%;max-width:350px;aspect-ratio:1/1;">
                        <canvas id="kelasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center" style="background: #ffffff; color: #2a9d5f; border-bottom: 2px solid #2a9d5f;">
                    <span>Statistik Penerimaan/Pengeluaran</span>
                    <form method="GET" action="" class="d-flex align-items-center" style="gap:8px">
                        <label for="period_id" class="mb-0" style="font-size:.9rem;color:#6c757d;">Periode</label>
                        <select name="period_id" id="period_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 160px;">
                            @foreach(($periods ?? []) as $p)
                                <option value="{{ $p->period_id }}" {{ ($selectedPeriodId ?? '') == $p->period_id ? 'selected' : '' }}>{{ $p->period_start }}/{{ $p->period_end }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body d-flex align-items-center" style="height:350px;">
                    <canvas id="barPembayaranChart" style="width:100%;max-height:320px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center" style="background: #ffffff; color: #2a9d5f; border-bottom: 2px solid #2a9d5f;">
                    <span>Statistik Pembayaran</span>
                    <form method="GET" action="" class="d-flex align-items-center" style="gap:8px">
                        <label for="period_id" class="mb-0" style="font-size:.9rem;color:#6c757d;">Periode</label>
                        <select name="period_id" id="period_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 160px;">
                            @foreach(($periods ?? []) as $p)
                                <option value="{{ $p->period_id }}" {{ ($selectedPeriodId ?? '') == $p->period_id ? 'selected' : '' }}>{{ $p->period_start }}/{{ $p->period_end }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body d-flex align-items-center" style="height:360px;">
                    <canvas id="linePembayaranChart" style="width:100%;max-height:320px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Contoh radar chart jika ada -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold" style="background: #ffffff; color: #2a9d5f; border-bottom: 2px solid #2a9d5f;">Leaderboard Kelas (Transaksi Terbanyak)</div>
                <div class="card-body d-flex align-items-center" style="height:350px;">
                    <canvas id="leaderboardChart" style="width:100%;max-height:320px;"></canvas>
                </div>
            </div>
                    </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold" style="background: #ffffff; color: #2a9d5f; border-bottom: 2px solid #2a9d5f;">Log Aktivitas Terbaru</div>
                <div class="card-body" style="max-height:350px; overflow:auto;">
                    <ul class="list-group list-group-flush">
                        @forelse(($activityLogs ?? []) as $log)
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-start">
                                <span class="me-3"><i class="fas fa-history me-2" style="color: #33B86F;"></i>{{ $log->deskripsi }}</span>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($log->waktu)->diffForHumans() }}</small>
                            </li>
                        @empty
                            <li class="list-group-item bg-transparent text-muted">Belum ada aktivitas</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Doughnut chart
    if(document.getElementById('kelasChart')) {
        const ctx = document.getElementById('kelasChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($labels ?? []),
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: @json($data ?? []),
                    backgroundColor: [
                        'rgb(255, 99, 132)','rgb(54, 162, 235)','rgb(255, 205, 86)','rgb(75, 192, 192)','rgb(153, 102, 255)','rgb(255, 159, 64)','rgb(201, 203, 207)'
                    ],
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
    // Bar chart penerimaan vs pengeluaran (data nyata)
    if(document.getElementById('barPembayaranChart')) {
        new Chart(document.getElementById('barPembayaranChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($monthLabels),
                datasets: [
                    {
                        label: 'Penerimaan',
                        data: @json(array_values($receiptsMonthly ?? [])),
                        backgroundColor: 'rgba(91,110,225,0.7)',
                        borderRadius: 6,
                        maxBarThickness: 32
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json(array_values($expensesMonthly ?? [])),
                        backgroundColor: 'rgba(255,179,0,0.7)',
                        borderRadius: 6,
                        maxBarThickness: 32
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top', labels: { color: getComputedStyle(document.body).getPropertyValue('--chart-legend-color') || '#ddd' } },
                    title: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: (document.body.classList.contains('dark-mode') ? '#bbb' : '#666') } },
                    y: { beginAtZero: true, grid: { color: (document.body.classList.contains('dark-mode') ? '#333' : '#eee') }, ticks: { color: (document.body.classList.contains('dark-mode') ? '#bbb' : '#666') } }
                }
            }
        });
    }
    // Line chart statistik pembayaran per bulan (mulai Juli)
    if(document.getElementById('linePembayaranChart')) {
        const labelsAll = @json($monthLabels);
        // Putar label agar mulai dari Juli (index 6 jika Januari index 0)
        const startIndex = 6; // Juli
        const labelsJulyStart = labelsAll.slice(startIndex).concat(labelsAll.slice(0, startIndex));

        // Ambil data pembayaran dari server jika ada, jika tidak isi 0 untuk 12 bulan
        const paymentsFromServer = @json(array_values($paymentsMonthly ?? []));
        let pembayaranData = [];
        if (Array.isArray(paymentsFromServer) && paymentsFromServer.length === 12) {
            // Susun ulang data mengikuti urutan label yang dimulai Juli
            pembayaranData = paymentsFromServer.slice(startIndex).concat(paymentsFromServer.slice(0, startIndex));
        } else if (Array.isArray(paymentsFromServer) && paymentsFromServer.length > 0) {
            // Jika panjang tidak 12, pakai apa adanya lalu lengkapi nol hingga 12 dan putar
            const padded = paymentsFromServer.concat(Array(12 - paymentsFromServer.length).fill(0)).slice(0,12);
            pembayaranData = padded.slice(startIndex).concat(padded.slice(0, startIndex));
        } else {
            // Tidak ada data dari server
            pembayaranData = Array(12).fill(0);
        }

        new Chart(document.getElementById('linePembayaranChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labelsJulyStart,
                datasets: [{
                    label: 'Pembayaran',
                    data: pembayaranData,
                    borderColor: 'rgba(42,157,95,1)',
                    backgroundColor: 'rgba(42,157,95,0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: 'rgba(42,157,95,1)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: (document.body.classList.contains('dark-mode') ? '#bbb' : '#666') } },
                    y: { beginAtZero: true, grid: { color: (document.body.classList.contains('dark-mode') ? '#333' : '#eee') }, ticks: { color: (document.body.classList.contains('dark-mode') ? '#bbb' : '#666') } }
                }
            }
        });
    }
    // Leaderboard kelas berdasarkan jumlah transaksi terbanyak (bar horizontal)
    if(document.getElementById('leaderboardChart')) {
        new Chart(document.getElementById('leaderboardChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($rankingLabels ?? []),
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: @json($rankingData ?? []),
                    backgroundColor: 'rgba(51, 184, 111, 0.7)',
                    borderRadius: 6,
                    maxBarThickness: 32
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: (document.body.classList.contains('dark-mode') ? '#333' : '#eee') }, ticks: { color: (document.body.classList.contains('dark-mode') ? '#bbb' : '#666') } },
                    y: { grid: { display: false }, ticks: { color: (document.body.classList.contains('dark-mode') ? '#bbb' : '#666') } }
                }
            }
        });
    }
    // Widget mini chart dummy (jika ada)
    if(window.widgetPembayaranChart) window.widgetPembayaranChart.destroy();
    if(document.getElementById('widgetPembayaranChart')) {
        window.widgetPembayaranChart = new Chart(document.getElementById('widgetPembayaranChart').getContext('2d'), {
            type: 'line',
            data: { labels: [1,2,3,4,5,6,7], datasets: [{ data: [2,4,3,5,4,6,5], borderColor:'#fff', backgroundColor:'rgba(255,255,255,0.1)', tension:.4, fill:true, pointRadius:0, borderWidth:2 }] },
            options: { plugins:{legend:{display:false}}, scales:{x:{display:false},y:{display:false}}, elements:{line:{borderJoinStyle:'round'}}, responsive:true }
        });
    }
    // Widget lain (penerimaan, tabungan, dsb) bisa ditambahkan serupa
    if(document.getElementById('widgetPenerimaanChart')) {
        new Chart(document.getElementById('widgetPenerimaanChart').getContext('2d'), {
            type: 'line',
            data: { labels: [1,2,3,4,5,6,7], datasets: [{ data: [3,5,4,6,5,7,6], borderColor:'#fff', backgroundColor:'rgba(255,255,255,0.1)', tension:.4, fill:true, pointRadius:0, borderWidth:2 }] },
            options: { plugins:{legend:{display:false}}, scales:{x:{display:false},y:{display:false}}, elements:{line:{borderJoinStyle:'round'}}, responsive:true }
        });
    }
    if(document.getElementById('widgetPengeluaranChart')) {
        new Chart(document.getElementById('widgetPengeluaranChart').getContext('2d'), {
            type: 'line',
            data: { labels: [1,2,3,4,5,6,7], datasets: [{ data: [4,6,5,7,6,8,7], borderColor:'#fff', backgroundColor:'rgba(255,255,255,0.1)', tension:.4, fill:true, pointRadius:0, borderWidth:2 }] },
            options: { plugins:{legend:{display:false}}, scales:{x:{display:false},y:{display:false}}, elements:{line:{borderJoinStyle:'round'}}, responsive:true }
        });
    }
    if(document.getElementById('widgetTabunganChart')) {
        new Chart(document.getElementById('widgetTabunganChart').getContext('2d'), {
            type: 'line',
            data: { labels: [1,2,3,4,5,6,7], datasets: [{ data: [1,3,2,4,3,5,4], borderColor:'#fff', backgroundColor:'rgba(255,255,255,0.1)', tension:.4, fill:true, pointRadius:0, borderWidth:2 }] },
            options: { plugins:{legend:{display:false}}, scales:{x:{display:false},y:{display:false}}, elements:{line:{borderJoinStyle:'round'}}, responsive:true }
        });
    }
});
    </script>
    <style>
        /* Enhanced Card Styling */
        .payment-card, .receipt-card, .expense-card, .savings-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .payment-card:hover, .receipt-card:hover, .expense-card:hover, .savings-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .widget-pay-row { 
            border-bottom: 1px solid rgba(255,255,255,0.15); 
            padding: 4px 0 4px 0; 
            transition: all 0.2s ease;
        }
        
        .widget-pay-row:last-child { 
            border-bottom: 0; 
        }
        
        .widget-pay-row:hover {
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
            padding-left: 8px;
            padding-right: 8px;
        }
        
        .widget-pay-label { 
            font-size: 0.95rem; 
            opacity: 0.9; 
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .widget-pay-nominal { 
            font-size: 1rem; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            font-weight: 700; 
            letter-spacing: 0.5px; 
            text-align: right; 
            min-width: 120px; 
            color: #fff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .payment-icon, .receipt-icon, .expense-icon, .savings-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }
        
        .payment-footer, .receipt-footer, .expense-footer, .savings-footer {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 8px;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-radius: 12px;
        }
        
        .dropdown-item {
            padding: 8px 16px;
            border-radius: 8px;
            margin: 2px 8px;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(91, 110, 225, 0.1);
            transform: translateX(5px);
        }
        
        .dropdown-item i {
            width: 16px;
            text-align: center;
        }
        
        /* Color variations for different card types */
        .text-success { color: #4caf50 !important; }
        .text-warning { color: #ffeb3b !important; }
        .text-light { color: rgba(255,255,255,0.8) !important; }
        
        /* Dark mode readability for widget titles */
        body.dark-mode .widget-title { color: #fff !important; }
        body.dark-mode .widget-pay-label { color: #e4e6eb !important; opacity: .95; }
        body.dark-mode .widget-pay-nominal { color: #fff !important; }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .widget-pay-nominal {
                font-size: 0.9rem;
                min-width: 100px;
            }
            
            .widget-pay-label {
                font-size: 0.85rem;
            }
            
            .payment-icon, .receipt-icon, .expense-icon, .savings-icon {
                width: 32px;
                height: 32px;
            }
        }
    </style>
@endsection 