@extends('layouts.student')

@section('title', 'Detail Pembayaran')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Payment Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Informasi Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>ID Pembayaran:</strong></td>
                                    <td>#{{ $payment->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Tagihan:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $payment->bill_type === 'bulanan' ? 'primary' : 'info' }}">
                                            {{ ucfirst($payment->bill_type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Jumlah:</strong></td>
                                    <td>
                                        <strong class="text-primary">
                                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Metode:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $payment->payment_method === 'online' ? 'success' : 'warning' }}">
                                            {{ ucfirst($payment->payment_method) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($payment->status === 'success')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Berhasil
                                            </span>
                                        @elseif($payment->status === 'pending')
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>Menunggu
                                            </span>
                                        @elseif($payment->status === 'failed')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Gagal
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-spinner me-1"></i>Proses
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Update Terakhir:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($payment->updated_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($payment->payment_description)
                                <tr>
                                    <td><strong>Keterangan:</strong></td>
                                    <td>{{ $payment->payment_description }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($payment->payment_proof)
                    <div class="mt-4">
                        <h6><i class="fas fa-image me-2"></i>Bukti Pembayaran</h6>
                        <div class="text-center">
                            @if(in_array(pathinfo($payment->payment_proof, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ asset('storage/payment_proofs/' . $payment->payment_proof) }}" 
                                     alt="Bukti Pembayaran" class="img-fluid" style="max-width: 400px;">
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    <a href="{{ asset('storage/payment_proofs/' . $payment->payment_proof) }}" 
                                       target="_blank" class="btn btn-primary">
                                        Lihat Bukti Pembayaran
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Aksi
                    </h5>
                </div>
                <div class="card-body">
                    @if($payment->status === 'success')
                        <a href="{{ route('student.payment.receipt', $payment->id) }}" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-download me-2"></i>Download Receipt
                        </a>
                    @endif
                    <a href="{{ route('student.payment.history') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 