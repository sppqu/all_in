@extends('layouts.adminty')

@section('title', 'Rekapitulasi Tabungan')

@section('head')
<style>
    /* Fix untuk widget card icon tidak tertutup */
    .widget-card-1 {
        overflow: visible !important;
        margin-top: 20px;
    }
    
    .widget-card-1 .card-block {
        padding: 1.25rem;
        padding-top: 15px;
        padding-right: 80px;
        position: relative;
        text-align: left !important;
    }
    
    .widget-card-1 .card1-icon {
        position: absolute;
        top: -15px;
        right: 20px;
        left: auto;
        width: 60px;
        height: 60px;
        font-size: 35px;
        border-radius: 8px;
        display: flex;
        color: #fff;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease-in-out;
        z-index: 10;
    }
    
    .widget-card-1:hover .card1-icon {
        top: -25px;
    }
    
    .widget-card-1 .card-block h6,
    .widget-card-1 .card-block h4,
    .widget-card-1 .card-block span,
    .widget-card-1 .card-block small,
    .widget-card-1 .card-block div {
        text-align: left !important;
    }
    
    .widget-card-1 .card-block > h6:first-child {
        margin-right: 70px;
        padding-right: 0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Rekapitulasi Tabungan
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('rekapitulasi-tabungan.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="{{ $startDate }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="{{ $endDate }}" required>
                            </div>
                            <div class="col-md-2">
                                <label for="class_id" class="form-label">Kelas</label>
                                <select class="form-control select-primary" id="class_id" name="class_id">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->class_id }}" 
                                                {{ $classId == $class->class_id ? 'selected' : '' }}>
                                            {{ $class->class_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="payment_method" class="form-label">Metode Pembayaran</label>
                                <select class="form-control select-primary" id="payment_method" name="payment_method">
                                    <option value="">Semua Metode</option>
                                    <option value="tunai" {{ $paymentMethod == 'tunai' ? 'selected' : '' }}>Tunai</option>
                                    <option value="transfer_bank" {{ $paymentMethod == 'transfer_bank' ? 'selected' : '' }}>Transfer Bank</option>
                                    <option value="payment_gateway" {{ $paymentMethod == 'payment_gateway' ? 'selected' : '' }}>Payment Gateway</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('rekapitulasi-tabungan.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-refresh me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                        <input type="hidden" name="filter_submitted" value="1">
                    </form>

                    @if($hasFilters)
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="card widget-card-1" style="position: relative; background: white;">
                                    <div class="card-block">
                                        <div class="card1-icon bg-c-green" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                            <i class="feather icon-arrow-up text-white"></i>
                                        </div>
                                        <h6 class="mb-0" style="color: #919aa3; font-size: 0.85rem;">Total Setoran</h6>
                                        <h4 class="mt-2 mb-0" style="color: #10b981; font-weight: 600;">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card widget-card-1" style="position: relative; background: white;">
                                    <div class="card-block">
                                        <div class="card1-icon bg-c-red" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                            <i class="feather icon-arrow-down text-white"></i>
                                        </div>
                                        <h6 class="mb-0" style="color: #919aa3; font-size: 0.85rem;">Total Penarikan</h6>
                                        <h4 class="mt-2 mb-0" style="color: #ef4444; font-weight: 600;">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card widget-card-1" style="position: relative; background: white;">
                                    <div class="card-block">
                                        <div class="card1-icon bg-c-blue" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                            <i class="feather icon-credit-card text-white"></i>
                                        </div>
                                        <h6 class="mb-0" style="color: #919aa3; font-size: 0.85rem;">Total Saldo</h6>
                                        <h4 class="mt-2 mb-0" style="color: #3b82f6; font-weight: 600;">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Export Buttons -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="btn-group" role="group">
                                                                         <a href="{{ route('rekapitulasi-tabungan.export-pdf') }}?start_date={{ $startDate }}&end_date={{ $endDate }}&class_id={{ $classId }}&payment_method={{ $paymentMethod }}" 
                                        class="btn btn-danger text-white">
                                         <i class="fas fa-file-pdf me-1"></i>Export PDF
                                     </a>
                                     <a href="{{ route('rekapitulasi-tabungan.export-excel') }}?start_date={{ $startDate }}&end_date={{ $endDate }}&class_id={{ $classId }}&payment_method={{ $paymentMethod }}" 
                                        class="btn btn-success text-white">
                                         <i class="fas fa-file-excel me-1"></i>Export Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Data Table -->
                        @if($rekapitulasiData->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas</th>
                                            <th>Total Setoran</th>
                                            <th>Total Penarikan</th>
                                            <th>Saldo Akhir</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                                                                 @foreach($rekapitulasiData as $index => $data)
                                             <tr>
                                                 <td>{{ $index + 1 }}</td>
                                                 <td>{{ $data['student_nis'] }}</td>
                                                 <td>{{ $data['student_name'] }}</td>
                                                 <td>{{ $data['class_name'] }}</td>
                                                 <td class="text-success">Rp {{ number_format($data['total_setoran'], 0, ',', '.') }}</td>
                                                 <td class="text-danger">Rp {{ number_format($data['jumlah_penarikan'], 0, ',', '.') }}</td>
                                                 <td class="text-primary fw-bold">Rp {{ number_format($data['saldo_akhir'], 0, ',', '.') }}</td>
                                                 <td>
                                                     <button type="button" 
                                                             class="btn btn-sm btn-info text-white" 
                                                             onclick="showDetailModal({{ $data['student_id'] }}, '{{ $data['student_name'] }}', '{{ $data['student_nis'] }}')">
                                                         <i class="fas fa-eye me-1"></i>Detail
                                                     </button>
                                                 </td>
                                             </tr>
                                         @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada data rekapitulasi untuk periode yang dipilih.</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-filter fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Silakan pilih filter untuk melihat data rekapitulasi tabungan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 80%; width: 80%;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-list-alt me-2"></i>Detail Transaksi Tabungan
                </h5>
                <button type="button" class="close text-white" onclick="closeDetailModal()" aria-label="Close" style="opacity: 1; font-size: 1.5rem; padding: 0; margin-left: 0.5rem; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Student Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-user me-2"></i>Informasi Siswa
                                </h6>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">NIS:</small><br>
                                        <strong id="modalStudentNis">-</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Nama:</small><br>
                                        <strong id="modalStudentName">-</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-calendar me-2"></i>Periode Transaksi
                                </h6>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Dari:</small><br>
                                        <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Sampai:</small><br>
                                        <strong>{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading -->
                <div id="detailLoading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data transaksi...</p>
                </div>

                <!-- Transactions Table -->
                <div id="detailContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Setoran</th>
                                    <th>Penarikan</th>
                                    <th>Metode Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- No Data -->
                <div id="detailNoData" class="text-center py-4" style="display: none;">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Tidak ada data transaksi untuk periode yang dipilih.</p>
                </div>

                <!-- Error -->
                <div id="detailError" class="text-center py-4" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <p class="text-danger">Terjadi kesalahan saat memuat data.</p>
                    <button type="button" class="btn btn-primary" onclick="loadDetailData()">
                        <i class="fas fa-refresh me-1"></i>Coba Lagi
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDetailModal()">
                    <i class="fas fa-times me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentStudentId = null;

$(document).ready(function() {
    // Auto-submit form when date changes
    $('#start_date, #end_date').change(function() {
        if ($('#start_date').val() && $('#end_date').val()) {
            $('form').submit();
        }
    });
    
    // Handle modal close events
    $('#detailModal').on('hidden.bs.modal', function () {
        // Reset current student ID when modal is closed
        currentStudentId = null;
    });
});

function showDetailModal(studentId, studentName, studentNis) {
    currentStudentId = studentId;
    
    // Update modal info
    $('#modalStudentName').text(studentName);
    $('#modalStudentNis').text(studentNis);
    
    // Show modal
    $('#detailModal').modal('show');
    
    // Load data
    loadDetailData();
}

function closeDetailModal() {
    // Hide modal using jQuery (Bootstrap 4 compatible)
    $('#detailModal').modal('hide');
    
    // Reset current student ID
    currentStudentId = null;
}

function loadDetailData() {
    if (!currentStudentId) return;
    
    // Show loading
    $('#detailLoading').show();
    $('#detailContent').hide();
    $('#detailNoData').hide();
    $('#detailError').hide();
    
    // Get current filter values
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    const paymentMethod = $('#payment_method').val();
    
    // Make AJAX request
    $.ajax({
        url: `{{ route('rekapitulasi-tabungan.detail', ':studentId') }}`.replace(':studentId', currentStudentId),
        method: 'GET',
        data: {
            start_date: startDate,
            end_date: endDate,
            payment_method: paymentMethod
        },
        success: function(response) {
            $('#detailLoading').hide();
            
            if (response.success && response.transactions && response.transactions.length > 0) {
                displayTransactions(response.transactions);
                $('#detailContent').show();
            } else {
                $('#detailNoData').show();
            }
        },
        error: function(xhr, status, error) {
            $('#detailLoading').hide();
            $('#detailError').show();
            console.error('Error loading detail data:', error);
        }
    });
}

function displayTransactions(transactions) {
    const tbody = $('#detailTableBody');
    tbody.empty();
    
    transactions.forEach((transaction, index) => {
        const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${formatDateTime(transaction.tanggal)}</td>
                <td>${transaction.keterangan || '-'}</td>
                <td class="text-success">
                    ${transaction.kredit > 0 ? 'Rp ' + formatNumber(transaction.kredit) : '-'}
                </td>
                <td class="text-danger">
                    ${transaction.debit > 0 ? 'Rp ' + formatNumber(transaction.debit) : '-'}
                </td>
                <td>
                    <span class="badge bg-${getPaymentMethodBadge(transaction.metode_pembayaran)}">
                        ${transaction.metode_pembayaran}
                    </span>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }) + ' ' + date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

function getPaymentMethodBadge(method) {
    switch(method.toLowerCase()) {
        case 'tunai':
            return 'success';
        case 'transfer bank':
            return 'info';
        case 'payment gateway':
            return 'warning';
        default:
            return 'secondary';
    }
}
</script>
@endpush
