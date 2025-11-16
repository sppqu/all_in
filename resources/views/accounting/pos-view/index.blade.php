@extends('layouts.adminty')

@section('title', 'Lihat Pos Penerimaan')
@section('content-header', 'Lihat Pos Penerimaan')

@section('content')
<div class="container-fluid">
    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>Filter Periode
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('manage.accounting.pos-view') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ $startDate }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ $endDate }}" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('manage.accounting.pos-view') }}" class="btn btn-secondary">
                                <i class="fas fa-refresh me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white mb-0">{{ count($posIncomes) }}</h4>
                            <small>Total Pos</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white mb-0">Rp {{ number_format(array_sum(array_column($posIncomes, 'total_income')), 0, ',', '.') }}</h4>
                            <small>Total Pendapatan</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white mb-0">{{ array_sum(array_column($posIncomes, 'transaction_count')) }}</h4>
                            <small>Total Transaksi</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exchange-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white mb-0">
                                {{ \Carbon\Carbon::parse($startDate)->format('d/m') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                            </h4>
                            <small>Periode Filter</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pos Income Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Pendapatan Per Pos
                    </h5>
                    <div>
                        <a href="{{ route('manage.accounting.receipt-pos.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                        <button onclick="printReport()" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-print me-1"></i>Cetak
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="posTable">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="20%">Nama Pos</th>
                                    <th width="25%">Keterangan</th>
                                    <th width="15%">Jumlah Transaksi</th>
                                    <th width="20%">Total Pendapatan</th>
                                    <th width="15%">Detail Sumber</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posIncomes as $index => $pos)
                                <tr class="{{ $pos['total_income'] > 0 ? '' : 'table-secondary' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $pos['pos_name'] }}</strong>
                                        @if($pos['total_income'] > 0)
                                            <span class="badge bg-success ms-2">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary ms-2">Tidak Ada</span>
                                        @endif
                                    </td>
                                    <td>{{ $pos['pos_description'] ?: '-' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $pos['transaction_count'] }} transaksi</span>
                                    </td>
                                    <td>
                                        <strong class="text-{{ $pos['total_income'] > 0 ? 'success' : 'muted' }}">
                                            Rp {{ number_format($pos['total_income'], 0, ',', '.') }}
                                        </strong>
                                        @if($pos['total_income'] > 0)
                                            <br>
                                            <small class="text-muted">
                                                {{ $pos['transaction_count'] > 0 ? 'Rata-rata: Rp ' . number_format($pos['total_income'] / $pos['transaction_count'], 0, ',', '.') : '' }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pos['total_income'] > 0 && count($pos['income_details']) > 0)
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="showIncomeDetails({{ $index }})" 
                                                    title="Lihat detail sumber pendapatan">
                                                <i class="fas fa-chart-pie"></i> Detail
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <br>
                                        <strong>Tidak ada data pos yang ditemukan</strong>
                                        <br>
                                        <small class="text-muted">Silakan tambah pos penerimaan terlebih dahulu</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if(count($posIncomes) > 0)
                            <tfoot class="table-dark">
                                <tr>
                                    <th colspan="4">TOTAL KESELURUHAN</th>
                                    <th>
                                        <span class="badge bg-light text-dark">
                                            {{ array_sum(array_column($posIncomes, 'transaction_count')) }} transaksi
                                        </span>
                                    </th>
                                    <th>
                                        <strong>Rp {{ number_format(array_sum(array_column($posIncomes, 'total_income')), 0, ',', '.') }}</strong>
                                    </th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Sumber Pendapatan -->
<div class="modal fade" id="incomeDetailsModal" tabindex="-1" aria-labelledby="incomeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="incomeDetailsModalLabel">
                    <i class="fas fa-chart-pie me-2"></i>Detail Sumber Pendapatan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="incomeDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Data pos incomes dari controller
const posIncomesData = @json($posIncomes);

// Data sekolah untuk header cetak
let schoolProfileData = @json($schoolProfile);

function printReport() {
    // Buat konten cetak dengan header yang sama seperti bukti penerimaan
    const printContent = `
        <div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 15px;">
            <!-- Header dengan Nama Lembaga dan Judul Dokumen -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <div style="font-weight: normal; font-size: 18px; margin-bottom: 2px;">${schoolProfileData ? schoolProfileData.nama_sekolah : 'Ma\'had Tahfizh Al Qur\'an Assunnah Pangkalpinang'}</div>
                    <div style="font-size: 12px; line-height: 1.1; margin-bottom: 2px;">${schoolProfileData ? schoolProfileData.alamat : 'Jl. Melati 1 Gg. Dahlia 7'}</div>
                    <div style="font-size: 11px; line-height: 1.0;">Telp: ${schoolProfileData ? schoolProfileData.no_telp : '085267165034'} | Email: ${schoolProfileData ? schoolProfileData.email : 'admin@sekolahku.sch.id'} | Website: ${schoolProfileData ? schoolProfileData.website : 'www.sekolahku.sch.id'}</div>
                </div>
                
                <!-- Judul Dokumen -->
                <div style="flex: 0 0 auto; margin-left: 20px;">
                    <div style="border: 2px dashed #000; padding: 8px 15px; font-weight: normal; font-size: 16px;">
                        LAPORAN POS
                    </div>
                </div>
            </div>
            
            <!-- Garis Putus-putus di bawah header -->
            <div style="border-bottom: 2px dashed #000; margin-bottom: 10px;"></div>
            
            <!-- Detail Periode -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <div style="margin-bottom: 3px;">
                        <span style="font-weight: normal; font-size: 12px;">Periode Laporan:</span>
                        <span style="font-size: 12px; margin-left: 5px;">${new Date('{{ $startDate }}').toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'})} s/d ${new Date('{{ $endDate }}').toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'})}</span>
                    </div>
                    <div style="margin-bottom: 3px;">
                        <span style="font-weight: normal; font-size: 12px;">Total Pos:</span>
                        <span style="font-size: 12px; margin-left: 5px;">{{ count($posIncomes) }} pos</span>
                    </div>
                    <div style="margin-bottom: 3px;">
                        <span style="font-weight: normal; font-size: 12px;">Total Transaksi:</span>
                        <span style="font-size: 12px; margin-left: 5px;">{{ array_sum(array_column($posIncomes, 'transaction_count')) }} transaksi</span>
                    </div>
                </div>
                <div style="flex: 1; text-align: right;">
                    <div style="margin-bottom: 3px;">
                        <span style="font-weight: normal; font-size: 12px;">Total Pendapatan:</span>
                        <span style="font-size: 12px; margin-left: 5px;">Rp {{ number_format(array_sum(array_column($posIncomes, 'total_income')), 0, ',', '.') }}</span>
                    </div>
                    <div style="margin-bottom: 3px;">
                        <span style="font-weight: normal; font-size: 12px;">Dicetak:</span>
                        <span style="font-size: 12px; margin-left: 5px;">${new Date().toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'})}</span>
                    </div>
                    <div style="margin-bottom: 3px;">
                        <span style="font-weight: normal; font-size: 12px;">Operator:</span>
                        <span style="font-size: 12px; margin-left: 5px;">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Garis putus-putus di atas tulisan rincian -->
            <div style="border-bottom: 2px dashed #000; margin: 7px 0;"></div>
            
            <!-- Rincian Transaksi -->
            <div style="margin-bottom: 10px;">
                <div style="font-weight: normal; font-size: 12px; margin-bottom: 5px;">Dengan rincian pos penerimaan sebagai berikut:</div>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
                    <thead>
                        <tr style="background-color: #f0f0f0;">
                            <th style="border: 1px solid #000; padding: 3px; text-align: center; font-size: 11px; font-weight: normal; width: 8%;">No</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: left; font-size: 11px; font-weight: normal; width: 32%;">Nama Pos</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: left; font-size: 11px; font-weight: normal; width: 25%;">Keterangan</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: center; font-size: 11px; font-weight: normal; width: 15%;">Transaksi</th>
                            <th style="border: 1px solid #000; padding: 3px; text-align: right; font-size: 11px; font-weight: normal; width: 20%;">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${generatePrintTableRows()}
                    </tbody>
                </table>
                
                <!-- Jumlah Total di bawah tabel -->
                <div style="text-align: right; margin-top: 5px;">
                    <span style="font-weight: normal; font-size: 14px;">Total Pendapatan: Rp {{ number_format(array_sum(array_column($posIncomes, 'total_income')), 0, ',', '.') }}</span>
                </div>
            </div>
            
            <!-- Total dan Tanda Tangan -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 30px;">
                <div style="flex: 1; margin-right: 40px;">
                    <div style="margin-top: 20px;">
                        <div style="font-size: 12px; font-weight: normal; margin-bottom: 5px;">Dibuat Oleh,</div>
                        <br><br>
                        <div style="border-top: 1px solid #000; width: 150px; margin-top: 15px;"></div>
                    </div>
                </div>
                <div style="flex: 1; text-align: center; margin: 0 20px;">
                    <div style="margin-top: 20px;">
                        <div style="font-size: 12px; font-weight: normal; margin-bottom: 5px;">Diverifikasi Oleh,</div>
                        <br><br>
                        <div style="border-top: 1px solid #000; width: 150px; margin: 0 auto; margin-top: 15px;"></div>
                    </div>
                </div>
                <div style="flex: 1; text-align: right; margin-left: 40px;">
                    <div style="margin-top: 20px;">
                        <div style="font-size: 12px; font-weight: normal; margin-bottom: 5px;">Diketahui Oleh,</div>
                        <br><br>
                        <div style="border-top: 1px solid #000; width: 150px; margin-left: auto; margin-top: 15px;"></div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Buat window baru untuk cetak
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Laporan Pendapatan Per Pos - {{ $startDate }} s/d {{ $endDate }}</title>
                <style>
                    body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
                    @media print {
                        body { margin: 20px; }
                        .no-print { display: none !important; }
                    }
                    table { page-break-inside: avoid; }
                    .page-break { page-break-before: always; }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    // Tunggu sebentar lalu cetak
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}

// Fungsi untuk generate baris tabel cetak
function generatePrintTableRows() {
    let rows = '';
    posIncomesData.forEach((pos, index) => {
        rows += `
            <tr style="background-color: ${index % 2 === 0 ? '#f8f9fa' : '#ffffff'};">
                <td style="border: 1px solid #000; padding: 3px; text-align: center; font-size: 10px; font-weight: normal;">${index + 1}</td>
                <td style="border: 1px solid #000; padding: 3px; font-size: 11px; font-weight: normal;">${pos.pos_name}</td>
                <td style="border: 1px solid #000; padding: 3px; font-size: 11px;">${pos.pos_description || '-'}</td>
                <td style="border: 1px solid #000; padding: 3px; text-align: center; font-size: 11px; font-weight: normal;">${pos.transaction_count} transaksi</td>
                <td style="border: 1px solid #000; padding: 3px; text-align: right; font-size: 11px; font-weight: normal; color: #000000;">
                    Rp ${pos.total_income.toLocaleString('id-ID')}
                </td>
            </tr>
        `;
    });
    return rows;
}

// Auto submit form ketika tanggal berubah (optional)
document.getElementById('start_date').addEventListener('change', function() {
    // Optional: auto submit saat tanggal berubah
});

document.getElementById('end_date').addEventListener('change', function() {
    // Optional: auto submit saat tanggal berubah
});

// Tampilkan detail sumber pendapatan
function showIncomeDetails(index) {
    const pos = posIncomesData[index];
    if (!pos || !pos.income_details || pos.income_details.length === 0) {
        alert('Tidak ada detail sumber pendapatan untuk pos ini');
        return;
    }
    
    let content = `
        <div class="mb-3">
            <h6 class="text-primary mb-2">
                <i class="fas fa-list me-2"></i>${pos.pos_name}
            </h6>
            <p class="text-muted mb-3">${pos.pos_description || 'Tidak ada keterangan'}</p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Sumber Pendapatan</th>
                        <th class="text-center">Jumlah Transaksi</th>
                        <th class="text-end">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    pos.income_details.forEach(detail => {
        content += `
            <tr>
                <td>
                    <span class="badge bg-info me-2">${detail.source}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary">${detail.count} transaksi</span>
                </td>
                <td class="text-end">
                    <strong class="text-success">Rp ${detail.amount.toLocaleString('id-ID')}</strong>
                </td>
            </tr>
        `;
    });
    
    content += `
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2" class="text-end">TOTAL KESELURUHAN:</th>
                        <th class="text-end">
                            <strong class="text-primary">Rp ${pos.total_income.toLocaleString('id-ID')}</strong>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Periode:</strong> ${new Date('{{ $startDate }}').toLocaleDateString('id-ID')} s/d ${new Date('{{ $endDate }}').toLocaleDateString('id-ID')}
        </div>
    `;
    
    document.getElementById('incomeDetailsContent').innerHTML = content;
    
    // Tampilkan modal
    const modal = new bootstrap.Modal(document.getElementById('incomeDetailsModal'));
    modal.show();
}
</script>

<style>
@media print {
    .card-header .btn,
    .no-print {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
    
    /* Hide elements not needed for print */
    .btn,
    .modal,
    .card-header {
        display: none !important;
    }
    
    /* Ensure proper page breaks */
    .table {
        page-break-inside: avoid;
    }
    
    /* Print-specific styling */
    body {
        margin: 0;
        padding: 20px;
        font-size: 12px;
        line-height: 1.4;
    }
}

/* Custom styles untuk tabel */
.table th {
    background-color: #495057;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

/* Badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

/* Card gradients */
.bg-primary {
    background: linear-gradient(45deg, #007bff, #0056b3) !important;
}

.bg-success {
    background: linear-gradient(45deg, #28a745, #1e7e34) !important;
}

.bg-info {
    background: linear-gradient(45deg, #17a2b8, #117a8b) !important;
}

.bg-warning {
    background: linear-gradient(45deg, #ffc107, #e0a800) !important;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>
@endpush
