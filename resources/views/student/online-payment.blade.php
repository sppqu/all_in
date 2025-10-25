@extends('layouts.student')

@section('title', 'Pembayaran Online')

@section('content')
<div class="container-fluid">
    <!-- Student Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">
                                <i class="fas fa-user-graduate me-2"></i>Informasi Siswa
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>NIS:</strong></td>
                                    <td>{{ $student->student_nis }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama:</strong></td>
                                    <td>{{ $student->student_full_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kelas:</strong></td>
                                    <td>{{ $student->class ? $student->class->class_name : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Informasi Pembayaran</h6>
                                <p class="mb-1">Total Tagihan Belum Lunas: <strong>{{ $bulananBills->count() + $bebasBills->count() }}</strong></p>
                                <p class="mb-1">Tagihan Bulanan: <strong>{{ $bulananBills->count() }}</strong></p>
                                <p class="mb-0">Tagihan Bebas: <strong>{{ $bebasBills->count() }}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bills Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="billsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="bulanan-tab" data-bs-toggle="tab" data-bs-target="#bulanan" type="button" role="tab">
                                <i class="fas fa-calendar me-2"></i>Tagihan Bulanan
                                <span class="badge bg-primary ms-2">{{ $bulananBills->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bebas-tab" data-bs-toggle="tab" data-bs-target="#bebas" type="button" role="tab">
                                <i class="fas fa-dollar-sign me-2"></i>Tagihan Bebas
                                <span class="badge bg-info ms-2">{{ $bebasBills->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="billsTabContent">
                        <!-- Tagihan Bulanan -->
                        <div class="tab-pane fade show active" id="bulanan" role="tabpanel">
                            @if($bulananBills->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Jenis Tagihan</th>
                                                <th>Periode</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bulananBills as $index => $bill)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $bill->pos_name }}</strong>
                                                    </td>
                                                    <td>
                                                        @if($bill->period_start && $bill->period_end)
                                                            {{ \Carbon\Carbon::parse($bill->period_start)->format('M Y') }} - 
                                                            {{ \Carbon\Carbon::parse($bill->period_end)->format('M Y') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>Rp {{ number_format($bill->bulan_bill, 0, ',', '.') }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>Belum Lunas
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('student.payment-form', [$student->student_id, 'bulanan', $bill->bulan_id]) }}" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="fas fa-credit-card me-1"></i>Bayar
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <p class="text-muted">Semua tagihan bulanan sudah lunas!</p>
                                </div>
                            @endif
                        </div>

                        <!-- Tagihan Bebas -->
                        <div class="tab-pane fade" id="bebas" role="tabpanel">
                            @if($bebasBills->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Jenis Tagihan</th>
                                                <th>Periode</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bebasBills as $index => $bill)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $bill->pos_name }}</strong>
                                                    </td>
                                                    <td>
                                                        @if($bill->period_start && $bill->period_end)
                                                            {{ \Carbon\Carbon::parse($bill->period_start)->format('M Y') }} - 
                                                            {{ \Carbon\Carbon::parse($bill->period_end)->format('M Y') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>Rp {{ number_format($bill->bebas_bill, 0, ',', '.') }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>Belum Lunas
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('student.payment-form', [$student->student_id, 'bebas', $bill->bebas_id]) }}" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="fas fa-credit-card me-1"></i>Bayar
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <p class="text-muted">Semua tagihan bebas sudah lunas!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 