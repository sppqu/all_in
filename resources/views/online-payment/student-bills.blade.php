@extends('layouts.coreui')

@section('title', 'Tagihan Siswa - Pembayaran Online')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-file-invoice me-2"></i>
                            Tagihan Siswa
                        </h4>
                        <a href="{{ route('online-payment.search') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Informasi Siswa -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Siswa</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="120"><strong>NIS:</strong></td>
                                                    <td>{{ $student->student_nis }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Nama:</strong></td>
                                                    <td>{{ $student->student_full_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Kelas:</strong></td>
                                                    <td>{{ $student->class->class_name ?? 'Kelas tidak ditemukan' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        <span class="badge bg-{{ $student->student_status ? 'success' : 'danger' }}">
                                                            {{ $student->student_status ? 'Aktif' : 'Tidak Aktif' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="alert alert-info">
                                                <h6><i class="fas fa-info-circle me-2"></i>Informasi Tagihan</h6>
                                                <p class="mb-1">Total Tagihan: <strong>{{ $totalBills ?? 0 }}</strong></p>
                                                <p class="mb-1">Sudah Dibayar: <strong>{{ $paidBills ?? 0 }}</strong></p>
                                                <p class="mb-0">Belum Dibayar: <strong>{{ $unpaidBills ?? 0 }}</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-credit-card fa-3x text-primary mb-3"></i>
                                    <h6>Pembayaran Online</h6>
                                    <p class="text-muted small">Pilih tagihan yang ingin dibayar secara online</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="billsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="bulanan-tab" data-bs-toggle="tab" data-bs-target="#bulanan" type="button" role="tab">
                                <i class="fas fa-calendar-alt me-2"></i>Tagihan Bulanan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bebas-tab" data-bs-toggle="tab" data-bs-target="#bebas" type="button" role="tab">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Tagihan Bebas
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="billsTabContent">
                        <!-- Tagihan Bulanan -->
                        <div class="tab-pane fade show active" id="bulanan" role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Tagihan Bulanan</h6>
                                </div>
                                <div class="card-body">
                                    @if($bulananBills->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Bulan</th>
                                                        <th>Jenis Pembayaran</th>
                                                        <th>Tahun Ajaran</th>
                                                        <th>Tagihan</th>
                                                        <th>Status</th>
                                                        <th>Tanggal Bayar</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($bulananBills as $bill)
                                                        @php
                                                            $monthNames = [
                                                                1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
                                                                5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
                                                                9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                                                            ];
                                                            $monthName = $monthNames[$bill->month_month_id] ?? 'Unknown';
                                                            $periodName = $bill->period_start && $bill->period_end ? 
                                                                        $bill->period_start . '/' . $bill->period_end : 'Tidak ditentukan';
                                                            $isPaid = !empty($bill->bulan_date_pay);
                                                            $statusClass = $isPaid ? 'success' : 'warning';
                                                            $statusText = $isPaid ? 'Lunas' : 'Belum Lunas';
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $monthName }}</strong></td>
                                                            <td>{{ $bill->pos_name }}</td>
                                                            <td>{{ $periodName }}</td>
                                                            <td>
                                                                <strong>Rp {{ number_format($bill->bulan_bill, 0, ',', '.') }}</strong>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $statusClass }}">
                                                                    {{ $statusText }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if($isPaid)
                                                                    {{ \Carbon\Carbon::parse($bill->bulan_date_pay)->format('d/m/Y') }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(!$isPaid)
                                                                    <a href="{{ route('online-payment.form', ['studentId' => $student->student_id, 'billType' => 'bulanan', 'billId' => $bill->bulan_id]) }}" 
                                                                       class="btn btn-primary btn-sm">
                                                                        <i class="fas fa-credit-card me-1"></i>Bayar
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted">Sudah dibayar</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                            <p>Tidak ada tagihan bulanan</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tagihan Bebas -->
                        <div class="tab-pane fade" id="bebas" role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Tagihan Bebas</h6>
                                </div>
                                <div class="card-body">
                                    @if($bebasBills->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Jenis Pembayaran</th>
                                                        <th>Tahun Ajaran</th>
                                                        <th>Tagihan</th>
                                                        <th>Status</th>
                                                        <th>Tanggal Bayar</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($bebasBills as $bill)
                                                        @php
                                                            $periodName = $bill->period_start && $bill->period_end ? 
                                                                        $bill->period_start . '/' . $bill->period_end : 'Tidak ditentukan';
                                                            $isPaid = !empty($bill->bebas_date_pay);
                                                            $statusClass = $isPaid ? 'success' : 'warning';
                                                            $statusText = $isPaid ? 'Lunas' : 'Belum Lunas';
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $bill->pos_name }}</strong></td>
                                                            <td>{{ $periodName }}</td>
                                                            <td>
                                                                <strong>Rp {{ number_format($bill->bebas_bill, 0, ',', '.') }}</strong>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $statusClass }}">
                                                                    {{ $statusText }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if($isPaid)
                                                                    {{ \Carbon\Carbon::parse($bill->bebas_date_pay)->format('d/m/Y') }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(!$isPaid)
                                                                    <a href="{{ route('online-payment.form', ['studentId' => $student->student_id, 'billType' => 'bebas', 'billId' => $bill->bebas_id]) }}" 
                                                                       class="btn btn-primary btn-sm">
                                                                        <i class="fas fa-credit-card me-1"></i>Bayar
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted">Sudah dibayar</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>
                                            <p>Tidak ada tagihan bebas</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Hitung total tagihan
    let totalBills = {{ $bulananBills->count() + $bebasBills->count() }};
    let paidBills = 0;
    let unpaidBills = 0;

    // Hitung tagihan bulanan
    @foreach($bulananBills as $bill)
        @if(!empty($bill->bulan_date_pay))
            paidBills++;
        @else
            unpaidBills++;
        @endif
    @endforeach

    // Hitung tagihan bebas
    @foreach($bebasBills as $bill)
        @if(!empty($bill->bebas_date_pay))
            paidBills++;
        @else
            unpaidBills++;
        @endif
    @endforeach

    // Update informasi tagihan
    $('.alert-info p:eq(0) strong').text(totalBills);
    $('.alert-info p:eq(1) strong').text(paidBills);
    $('.alert-info p:eq(2) strong').text(unpaidBills);
});
</script>
@endpush 