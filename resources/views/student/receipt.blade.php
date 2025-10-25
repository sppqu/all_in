@extends('layouts.student')

@section('title', 'Receipt Pembayaran')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>Receipt Pembayaran
                    </h5>
                    <button onclick="window.print()" class="btn btn-primary btn-sm">
                        <i class="fas fa-print me-2"></i>Cetak
                    </button>
                </div>
                <div class="card-body">
                    <!-- Receipt Header -->
                    <div class="text-center mb-4">
                        <h4 class="mb-1">{{ \App\Models\SchoolProfile::first()->school_name ?? 'SPPQU' }}</h4>
                        <p class="text-muted mb-0">Bukti Pembayaran</p>
                        <hr class="my-3">
                    </div>

                    <!-- Receipt Number -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>No. Receipt:</strong> #{{ $payment->id }}
                        </div>
                        <div class="col-md-6 text-end">
                            <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <!-- Student Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Siswa:</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>NIS:</strong></td>
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
                            <h6>Informasi Pembayaran:</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Jenis:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $payment->bill_type === 'bulanan' ? 'primary' : 'info' }}">
                                            {{ ucfirst($payment->bill_type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Metode:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $payment->payment_method === 'online' ? 'success' : 'warning' }}">
                                            {{ ucfirst($payment->payment_method) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Berhasil
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6>Detail Pembayaran:</h6>
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Deskripsi</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Pembayaran {{ ucfirst($payment->bill_type) }}</td>
                                        <td class="text-end">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="table-light">
                                        <td><strong>Total</strong></td>
                                        <td class="text-end"><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Description -->
                    @if($payment->payment_description)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6>Keterangan:</h6>
                            <p class="mb-0">{{ $payment->payment_description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="row mt-5">
                        <div class="col-md-6">
                            <div class="text-center">
                                <p class="mb-1">Dibuat oleh:</p>
                                <p class="mb-0"><strong>{{ $student->student_full_name }}</strong></p>
                                <small class="text-muted">Siswa</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <p class="mb-1">Diverifikasi oleh:</p>
                                <p class="mb-0"><strong>Admin Sekolah</strong></p>
                                <small class="text-muted">Sistem</small>
                            </div>
                        </div>
                    </div>

                    <!-- Print Notice -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                Simpan receipt ini sebagai bukti pembayaran yang sah
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, .sidebar, .btn, .breadcrumb {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .card-body {
        padding: 0 !important;
    }
}
</style>
@endsection 