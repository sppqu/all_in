@extends('layouts.coreui')

@section('title', 'Laporan Arus Kas')

@section('content')
<div class="container-fluid">
    <div class="fade-in">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fa fa-chart-line me-2"></i>
                            Laporan Arus Kas
                        </h4>
                                                  <div class="d-flex gap-2">
                              <button type="button" class="btn btn-danger text-white" onclick="exportLaporan('pdf')">
                                  <i class="fa fa-file-pdf me-1"></i> Export PDF
                              </button>
                              <button type="button" class="btn btn-success text-white" onclick="exportLaporan('excel')">
                                  <i class="fa fa-file-excel me-1"></i> Export Excel
                              </button>
                              <button type="button" class="btn btn-primary" onclick="refreshLaporan()">
                                  <i class="fa fa-refresh me-1"></i> Refresh
                              </button>
                          </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Filter Laporan</h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal Akhir</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Kas</label>
                                    <select class="form-select" id="kas_id" name="kas_id">
                                        <option value="">Semua Kas</option>
                                        @foreach($kasList as $kas)
                                            <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-search me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="row mb-4" id="summaryCards">
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="h4 mb-0" id="totalSaldoAwal">Rp 0</div>
                                <small>Total Saldo Awal</small>
                            </div>
                            <i class="fa fa-wallet fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="h4 mb-0" id="totalKasMasuk">Rp 0</div>
                                <small>Total Kas Masuk</small>
                            </div>
                            <i class="fa fa-arrow-down fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="h4 mb-0" id="totalKasKeluar">Rp 0</div>
                                <small>Total Kas Keluar</small>
                            </div>
                            <i class="fa fa-arrow-up fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="h4 mb-0" id="totalSaldoAkhir">Rp 0</div>
                                <small>Total Saldo Akhir</small>
                            </div>
                            <i class="fa fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data arus kas...</p>
        </div>

        <!-- Data Table -->
        <div id="dataContainer">
            @if(count($cashflowData) > 0)
                @foreach($cashflowData as $kasData)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fa fa-wallet me-2"></i>
                                    {{ $kasData['nama_kas'] }} 
                                    <span class="badge bg-secondary ms-2">{{ $kasData['jenis_kas'] == 'cash' ? 'Tunai' : 'Bank' }}</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Summary per Kas -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between">
                                            <strong>Saldo Awal:</strong>
                                            <span class="text-info">Rp {{ number_format($kasData['saldo_awal'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Masuk:</strong>
                                            <span class="text-success">Rp {{ number_format($kasData['total_masuk'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Keluar:</strong>
                                            <span class="text-danger">Rp {{ number_format($kasData['total_keluar'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between">
                                            <strong>Saldo Akhir:</strong>
                                            <span class="text-warning fw-bold">Rp {{ number_format($kasData['saldo_akhir'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabs untuk Kas Masuk dan Keluar -->
                                <ul class="nav nav-tabs" id="kasTab{{ $kasData['kas_id'] }}" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="masuk-tab{{ $kasData['kas_id'] }}" data-bs-toggle="tab" data-bs-target="#masuk{{ $kasData['kas_id'] }}" type="button" role="tab">
                                            <i class="fa fa-arrow-down me-1"></i> Kas Masuk ({{ count($kasData['kas_masuk']) }})
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="keluar-tab{{ $kasData['kas_id'] }}" data-bs-toggle="tab" data-bs-target="#keluar{{ $kasData['kas_id'] }}" type="button" role="tab">
                                            <i class="fa fa-arrow-up me-1"></i> Kas Keluar ({{ count($kasData['kas_keluar']) }})
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="kasTabContent{{ $kasData['kas_id'] }}">
                                    <!-- Tab Kas Masuk -->
                                    <div class="tab-pane fade show active" id="masuk{{ $kasData['kas_id'] }}" role="tabpanel">
                                        <div class="table-responsive mt-3">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-success">
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Tanggal</th>
                                                        <th>Referensi</th>
                                                        <th>Keterangan</th>
                                                        <th>Jenis</th>
                                                        <th>Metode</th>
                                                        <th class="text-end">Jumlah</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(count($kasData['kas_masuk']) > 0)
                                                        @foreach($kasData['kas_masuk'] as $index => $masuk)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ date('d/m/Y', strtotime($masuk['tanggal'])) }}</td>
                                                            <td>{{ $masuk['referensi'] }}</td>
                                                            <td>{{ $masuk['keterangan'] }}</td>
                                                            <td><span class="badge bg-success">{{ $masuk['jenis'] }}</span></td>
                                                            <td>{{ $masuk['metode'] }}</td>
                                                            <td class="text-end">Rp {{ number_format($masuk['jumlah'], 0, ',', '.') }}</td>
                                                        </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">Tidak ada data kas masuk</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Tab Kas Keluar -->
                                    <div class="tab-pane fade" id="keluar{{ $kasData['kas_id'] }}" role="tabpanel">
                                        <div class="table-responsive mt-3">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-danger">
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Tanggal</th>
                                                        <th>Referensi</th>
                                                        <th>Keterangan</th>
                                                        <th>Jenis</th>
                                                        <th>Metode</th>
                                                        <th class="text-end">Jumlah</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(count($kasData['kas_keluar']) > 0)
                                                        @foreach($kasData['kas_keluar'] as $index => $keluar)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ date('d/m/Y', strtotime($keluar['tanggal'])) }}</td>
                                                            <td>{{ $keluar['referensi'] }}</td>
                                                            <td>{{ $keluar['keterangan'] }}</td>
                                                            <td><span class="badge bg-danger">{{ $keluar['jenis'] }}</span></td>
                                                            <td>{{ $keluar['metode'] }}</td>
                                                            <td class="text-end">Rp {{ number_format($keluar['jumlah'], 0, ',', '.') }}</td>
                                                        </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">Tidak ada data kas keluar</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Tidak ada data arus kas</h5>
                                <p class="text-muted">Silakan ubah filter untuk melihat data arus kas</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Initialize summary
$(document).ready(function() {
    updateSummary();
});

// Filter form submission
$('#filterForm').on('submit', function(e) {
    e.preventDefault();
    loadLaporan();
});

// Load laporan function
function loadLaporan() {
    const formData = new FormData($('#filterForm')[0]);
    
    $('#loading').show();
    $('#dataContainer').hide();
    
    fetch('{{ route("manage.accounting.cashflow.laporan") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderCashflowData(data.data);
            updateSummary(data.data);
        } else {
            showAlert('error', 'Error', data.message || 'Gagal memuat data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error', 'Terjadi kesalahan saat memuat data');
    })
    .finally(() => {
        $('#loading').hide();
        $('#dataContainer').show();
    });
}

// Render cashflow data
function renderCashflowData(cashflowData) {
    let html = '';
    
    if (cashflowData.length === 0) {
        html = `
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada data arus kas</h5>
                            <p class="text-muted">Silakan ubah filter untuk melihat data arus kas</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        cashflowData.forEach(kasData => {
            html += generateKasCard(kasData);
        });
    }
    
    $('#dataContainer').html(html);
}

// Generate kas card HTML
function generateKasCard(kasData) {
    return `
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fa fa-wallet me-2"></i>
                            ${kasData.nama_kas}
                                                                <span class="badge bg-secondary ms-2">${kasData.jenis_kas == 'cash' ? 'Tunai' : 'Bank'}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        ${generateKasSummary(kasData)}
                        ${generateKasTabs(kasData)}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Generate kas summary
function generateKasSummary(kasData) {
    return `
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="d-flex justify-content-between">
                    <strong>Saldo Awal:</strong>
                    <span class="text-info">Rp ${formatNumber(kasData.saldo_awal)}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between">
                    <strong>Total Masuk:</strong>
                    <span class="text-success">Rp ${formatNumber(kasData.total_masuk)}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between">
                    <strong>Total Keluar:</strong>
                    <span class="text-danger">Rp ${formatNumber(kasData.total_keluar)}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-between">
                    <strong>Saldo Akhir:</strong>
                    <span class="text-warning fw-bold">Rp ${formatNumber(kasData.saldo_akhir)}</span>
                </div>
            </div>
        </div>
    `;
}

// Generate kas tabs
function generateKasTabs(kasData) {
    return `
        <ul class="nav nav-tabs" id="kasTab${kasData.kas_id}" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="masuk-tab${kasData.kas_id}" data-bs-toggle="tab" data-bs-target="#masuk${kasData.kas_id}" type="button" role="tab">
                    <i class="fa fa-arrow-down me-1"></i> Kas Masuk (${kasData.kas_masuk.length})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="keluar-tab${kasData.kas_id}" data-bs-toggle="tab" data-bs-target="#keluar${kasData.kas_id}" type="button" role="tab">
                    <i class="fa fa-arrow-up me-1"></i> Kas Keluar (${kasData.kas_keluar.length})
                </button>
            </li>
        </ul>
        <div class="tab-content" id="kasTabContent${kasData.kas_id}">
            <div class="tab-pane fade show active" id="masuk${kasData.kas_id}" role="tabpanel">
                ${generateTransactionTable(kasData.kas_masuk, 'success', 'masuk')}
            </div>
            <div class="tab-pane fade" id="keluar${kasData.kas_id}" role="tabpanel">
                ${generateTransactionTable(kasData.kas_keluar, 'danger', 'keluar')}
            </div>
        </div>
    `;
}

// Generate transaction table
function generateTransactionTable(transactions, badgeClass, type) {
    let rows = '';
    
    if (transactions.length === 0) {
        rows = `<tr><td colspan="7" class="text-center text-muted">Tidak ada data kas ${type}</td></tr>`;
    } else {
        transactions.forEach((trans, index) => {
            rows += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${formatDate(trans.tanggal)}</td>
                    <td>${trans.referensi}</td>
                    <td>${trans.keterangan}</td>
                    <td><span class="badge bg-${badgeClass}">${trans.jenis}</span></td>
                    <td>${trans.metode}</td>
                    <td class="text-end">Rp ${formatNumber(trans.jumlah)}</td>
                </tr>
            `;
        });
    }
    
    return `
        <div class="table-responsive mt-3">
            <table class="table table-striped table-hover">
                <thead class="table-${badgeClass}">
                    <tr>
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th>Referensi</th>
                        <th>Keterangan</th>
                        <th>Jenis</th>
                        <th>Metode</th>
                        <th class="text-end">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
        </div>
    `;
}

// Update summary cards
function updateSummary(cashflowData = @json($cashflowData)) {
    let totalSaldoAwal = 0;
    let totalKasMasuk = 0;
    let totalKasKeluar = 0;
    let totalSaldoAkhir = 0;
    
    cashflowData.forEach(kasData => {
        totalSaldoAwal += kasData.saldo_awal;
        totalKasMasuk += kasData.total_masuk;
        totalKasKeluar += kasData.total_keluar;
        totalSaldoAkhir += kasData.saldo_akhir;
    });
    
    $('#totalSaldoAwal').text('Rp ' + formatNumber(totalSaldoAwal));
    $('#totalKasMasuk').text('Rp ' + formatNumber(totalKasMasuk));
    $('#totalKasKeluar').text('Rp ' + formatNumber(totalKasKeluar));
    $('#totalSaldoAkhir').text('Rp ' + formatNumber(totalSaldoAkhir));
}

// Refresh laporan
function refreshLaporan() {
    loadLaporan();
}

// Export laporan
function exportLaporan(format) {
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    const kasId = $('#kas_id').val();
    
    if (!startDate || !endDate) {
        showAlert('warning', 'Peringatan', 'Silakan pilih tanggal mulai dan akhir');
        return;
    }
    
    const params = new URLSearchParams({
        start_date: startDate,
        end_date: endDate
    });
    
    if (kasId) {
        params.append('kas_id', kasId);
    }
    
    if (format === 'excel') {
        window.location.href = '{{ route("manage.accounting.cashflow.export-excel") }}?' + params.toString();
    } else {
        params.append('format', format);
        window.open('{{ route("manage.accounting.cashflow.export") }}?' + params.toString(), '_blank');
    }
}

// Utility functions
function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID');
}

function showAlert(type, title, message) {
    // Implementation depends on your alert system
    alert(title + ': ' + message);
}
</script>
@endpush
@endsection
