@extends('layouts.student')

@section('title', 'Detail Kuitansi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Detail Kuitansi</h6>
                        <a href="{{ route('student.payment.history') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($receipt->count() > 0)
                        @php
                            $firstItem = $receipt->first();
                            $totalAmount = $receipt->sum('amount');
                        @endphp
                        
                        <!-- Receipt Header -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <small class="fw-bold text-success mb-3 d-block">Informasi Pembayaran</small>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted small">Tanggal Pembayaran:</div>
                                    <div class="col-7 fw-bold small">{{ \Carbon\Carbon::parse($firstItem->payment_date)->format('d F Y') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted small">No. Ref:</div>
                                    <div class="col-7 fw-bold text-primary small">{{ preg_replace('/[^0-9]/', '', $firstItem->payment_number) }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted small">Petugas:</div>
                                    <div class="col-7 fw-bold small">{{ $type === 'online' ? 'Sistem' : 'Super Admin' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <small class="fw-bold text-success mb-3 d-block">Total Pembayaran</small>
                                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                    <div class="text-center">
                                        <h5 class="fw-bold text-success mb-0">Rp {{ number_format($totalAmount, 0, ',', '.') }}</h5>
                                        <small class="text-muted">{{ $receipt->count() }} item pembayaran</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt Items -->
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="fw-bold text-muted small">Item Pembayaran</th>
                                        <th class="fw-bold text-muted text-end small">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receipt as $item)
                                    <tr class="border-bottom">
                                        <td>
                                            <div class="fw-bold small">{{ $item->display_name }}</div>
                                            <small class="text-muted">{{ $item->bill_type }}</small>
                                        </td>
                                        <td class="text-end fw-bold small">
                                            Rp {{ number_format($item->amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td class="fw-bold small">Total</td>
                                        <td class="text-end fw-bold text-success small">
                                            Rp {{ number_format($totalAmount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end mt-4">
                            <!--<button class="btn btn-success me-2" onclick="window.print()">-->
                            <!--    <i class="fas fa-print me-1"></i>Cetak-->
                            <!--</button>-->
                            <!--<a href="{{ route('student.payment.receipt', $firstItem->receipt_id) }}" class="btn btn-outline-success">-->
                            <!--    <i class="fas fa-download me-1"></i>Download-->
                            <!--</a>-->
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-receipt text-muted fa-lg"></i>
                            </div>
                            <small class="text-muted fw-bold">Kuitansi tidak ditemukan</small>
                            <small class="text-muted mb-0">Detail kuitansi tidak tersedia</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .bottom-nav, .sidebar {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container-fluid {
        padding: 0 !important;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 12px;
    }
    
    .card-body {
        padding: 20px 15px;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
}
</style>
@endsection 