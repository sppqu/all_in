@extends('layouts.coreui')

@section('title', 'Laporan Rekapitulasi')

@section('content')
<style>
    .filter-section {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .filter-section .row {
        align-items: end;
    }
    .filter-section .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    .filter-section .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
    }
    .filter-section .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .btn-filter {
        border-radius: 6px;
        font-weight: 500;
    }
    .alert-info {
        border-left: 4px solid #17a2b8;
    }
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Laporan Rekapitulasi</h4>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="filter-section">
                        <form id="filterForm" method="GET" action="{{ route('manage.laporan-rekapitulasi') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Awal <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Akhir <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jenis Pembayaran</label>
                                <select name="payment_type" class="form-control">
                                    <option value="">Semua Jenis</option>
                                    <option value="Tunai" {{ $paymentType == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                    <option value="Transfer Bank" {{ $paymentType == 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank</option>
                                    <option value="Payment Gateway" {{ $paymentType == 'Payment Gateway' ? 'selected' : '' }}>Payment Gateway</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Pos Pembayaran</label>
                                <select name="pos_id" class="form-control">
                                    <option value="">Semua Pos</option>
                                    @foreach($posList ?? [] as $pos)
                                        <option value="{{ $pos->pos_id }}" {{ ($posId ?? '') == $pos->pos_id ? 'selected' : '' }}>
                                            {{ $pos->pos_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Kelas</label>
                                <select name="class_id" class="form-control">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classList ?? [] as $class)
                                        <option value="{{ $class->class_id }}" {{ ($classId ?? '') == $class->class_id ? 'selected' : '' }}>
                                            {{ $class->class_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-filter">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                    
                                    @if($data && ($data ?? collect())->count() > 0)
                                        <button type="button" class="btn btn-danger btn-filter ms-2" onclick="exportPDF()" style="color: white;">
                                            <i class="fas fa-file-pdf me-2" style="color: white;"></i>Export PDF
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>

                    @if($data && ($data ?? collect())->count() > 0)
                        <div class="alert alert-info">
                            <strong>Laporan Rekapitulasi</strong><br>
                            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}<br>
                            @if($paymentType)
                                Jenis Pembayaran: {{ $paymentType }}<br>
                            @endif
                            @if($posId)
                                @php
                                    $selectedPos = ($posList ?? collect())->where('pos_id', $posId)->first();
                                @endphp
                                Pos Pembayaran: {{ $selectedPos ? $selectedPos->pos_name : 'N/A' }}<br>
                            @endif
                            @if($classId)
                                @php
                                    $selectedClass = ($classList ?? collect())->where('class_id', $classId)->first();
                                @endphp
                                Kelas: {{ $selectedClass ? $selectedClass->class_name : 'N/A' }}<br>
                            @endif
                            Total Data: {{ ($data ?? collect())->count() }} transaksi<br>
                            <strong>Total Penerimaan:</strong><br>
                            @if(!$paymentType || $paymentType == 'Tunai')
                                • Tunai: Rp {{ number_format(($data ?? collect())->sum('cash_amount'), 0, ',', '.') }}<br>
                            @endif
                            @if(!$paymentType || $paymentType == 'Transfer Bank')
                                • Transfer Bank: Rp {{ number_format(($data ?? collect())->sum('transfer_amount'), 0, ',', '.') }}<br>
                            @endif
                            @if(!$paymentType || $paymentType == 'Payment Gateway')
                                • Payment Gateway: Rp {{ number_format(($data ?? collect())->sum('gateway_amount'), 0, ',', '.') }}<br>
                            @endif
                            <strong>Grand Total: Rp {{ number_format(($data ?? collect())->sum('cash_amount') + ($data ?? collect())->sum('transfer_amount') + ($data ?? collect())->sum('gateway_amount'), 0, ',', '.') }}</strong>
                        </div>

                        <!-- Tabel Laporan Rekapitulasi -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Pos Pembayaran</th>
                                        <th>Tanggal Bayar</th>
                                        @if(!$paymentType || $paymentType == 'Tunai')
                                            <th>Penerimaan Tunai</th>
                                        @endif
                                        @if(!$paymentType || $paymentType == 'Transfer Bank')
                                            <th>Penerimaan Transfer Bank</th>
                                        @endif
                                        @if(!$paymentType || $paymentType == 'Payment Gateway')
                                            <th>Penerimaan Payment Gateway</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data ?? [] as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['student_name'] }}</td>
                                            <td>{{ $item['class_name'] }}</td>
                                            <td>{{ $item['pos_name'] }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item['payment_date'])->format('d/m/Y') }}</td>
                                            @if(!$paymentType || $paymentType == 'Tunai')
                                                <td class="text-end">
                                                    @if($item['cash_amount'] > 0)
                                                        Rp {{ number_format($item['cash_amount'], 0, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @endif
                                            @if(!$paymentType || $paymentType == 'Transfer Bank')
                                                <td class="text-end">
                                                    @if($item['transfer_amount'] > 0)
                                                        Rp {{ number_format($item['transfer_amount'], 0, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @endif
                                            @if(!$paymentType || $paymentType == 'Payment Gateway')
                                                <td class="text-end">
                                                    @if($item['gateway_amount'] > 0)
                                                        Rp {{ number_format($item['gateway_amount'], 0, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                    @if(($data ?? collect())->count() > 0)
                                        <tr class="table-info">
                                            @php
                                                $colspan = 5; // No, Nama Siswa, Kelas, Pos Pembayaran, Tanggal Bayar
                                                $totalColumns = 0;
                                                if (!$paymentType || $paymentType == 'Tunai') {
                                                    $colspan++;
                                                    $totalColumns++;
                                                }
                                                if (!$paymentType || $paymentType == 'Transfer Bank') {
                                                    $colspan++;
                                                    $totalColumns++;
                                                }
                                                if (!$paymentType || $paymentType == 'Payment Gateway') {
                                                    $colspan++;
                                                    $totalColumns++;
                                                }
                                            @endphp
                                            <td colspan="{{ $colspan - $totalColumns }}" class="text-end fw-bold">TOTAL</td>
                                            @if(!$paymentType || $paymentType == 'Tunai')
                                                <td class="text-end fw-bold">
                                                    Rp {{ number_format(($data ?? collect())->sum('cash_amount'), 0, ',', '.') }}
                                                </td>
                                            @endif
                                            @if(!$paymentType || $paymentType == 'Transfer Bank')
                                                <td class="text-end fw-bold">
                                                    Rp {{ number_format(($data ?? collect())->sum('transfer_amount'), 0, ',', '.') }}
                                                </td>
                                            @endif
                                            @if(!$paymentType || $paymentType == 'Payment Gateway')
                                                <td class="text-end fw-bold">
                                                    Rp {{ number_format(($data ?? collect())->sum('gateway_amount'), 0, ',', '.') }}
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @elseif($startDate && $endDate && (!$data || ($data ?? collect())->count() == 0))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Tidak ada data pembayaran untuk kriteria yang dipilih.
                        </div>
                    @elseif(!$startDate || !$endDate)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Silakan pilih filter di atas untuk melihat data laporan.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getFilterValues() {
    const form = document.getElementById('filterForm');
    const fd = new FormData(form);
    const pick = (name) => fd.has(name) && String(fd.get(name)).trim() !== '' ? fd.get(name) : (form.querySelector(`[name="${name}"]`)?.value ?? null);
    return {
        start_date: pick('start_date'),
        end_date: pick('end_date'),
        payment_type: pick('payment_type'),
        pos_id: pick('pos_id'),
        class_id: pick('class_id')
    };
}
function validateRequired() {
    const v = getFilterValues();
    const isFilled = (x) => x !== null && String(x).trim() !== '';
    if (!isFilled(v.start_date) || !isFilled(v.end_date)) {
        alert('Mohon isi Tanggal Awal dan Tanggal Akhir.');
        return false;
    }
    return true;
}
function exportPDF() {
    if (!validateRequired()) return;
    const values = getFilterValues();
    
    // Buat form untuk export PDF
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '{{ route("manage.export-laporan-rekapitulasi") }}';
    
    // Tambahkan CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    exportForm.appendChild(csrfToken);
    
    // Tambahkan data form yang dipastikan terisi
    Object.entries(values).forEach(([key, value]) => {
        if (value !== null && String(value).trim() !== '') {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            exportForm.appendChild(input);
        }
    });
    
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
}
</script>
@endsection 