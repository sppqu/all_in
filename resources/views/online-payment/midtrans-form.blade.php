@extends('layouts.coreui')

@section('title', 'Pembayaran via Midtrans')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Pembayaran via Midtrans
                        </h5>
                        <a href="{{ route('online-payment.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Payment Form -->
                            <form id="midtransPaymentForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="student_id" class="form-label">Pilih Siswa</label>
                                            <select class="form-select" id="student_id" name="student_id" required>
                                                <option value="">Pilih Siswa</option>
                                                @foreach($students as $student)
                                                    <option value="{{ $student->id }}" 
                                                            data-name="{{ $student->full_name }}"
                                                            data-email="{{ $student->email }}"
                                                            data-phone="{{ $student->phone }}">
                                                        {{ $student->nis }} - {{ $student->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_type" class="form-label">Jenis Pembayaran</label>
                                            <select class="form-select" id="payment_type" name="payment_type" required>
                                                <option value="">Pilih Jenis Pembayaran</option>
                                                <option value="spp">SPP</option>
                                                <option value="bebas">Bebas</option>
                                                <option value="other">Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="period" class="form-label">Periode (untuk SPP)</label>
                                            <select class="form-select" id="period" name="period">
                                                <option value="">Pilih Periode</option>
                                                @foreach($periods as $period)
                                                    <option value="{{ $period->id }}">{{ $period->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Jumlah Pembayaran</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control" id="amount" name="amount" 
                                                       placeholder="0" min="1000" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi Pembayaran</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="3" placeholder="Deskripsi pembayaran..." required></textarea>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary" id="payButton">
                                        <i class="fas fa-credit-card me-2"></i>
                                        Bayar Sekarang
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Payment Info -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Informasi Pembayaran
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6>Metode Pembayaran yang Tersedia:</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-university text-primary me-2"></i>Transfer Bank</li>
                                            <li><i class="fas fa-mobile-alt text-success me-2"></i>E-Wallet</li>
                                            <li><i class="fas fa-qrcode text-warning me-2"></i>QRIS</li>
                                            <li><i class="fas fa-credit-card text-info me-2"></i>Kartu Kredit</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        <strong>Keamanan Terjamin</strong><br>
                                        Pembayaran diproses oleh Midtrans yang telah terverifikasi dan aman.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6>Memproses Pembayaran...</h6>
                <p class="text-muted">Mohon tunggu sebentar</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ \App\Helpers\MidtransHelper::getSnapJsUrl() }}" data-client-key="{{ \App\Helpers\MidtransHelper::getClientKey() }}"></script>
<script>
$(document).ready(function() {
    // Handle form submission
    $('#midtransPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading modal
        $('#loadingModal').modal('show');
        
        // Get form data
        const formData = {
            student_id: $('#student_id').val(),
            payment_type: $('#payment_type').val(),
            period: $('#period').val(),
            amount: $('#amount').val(),
            description: $('#description').val(),
            _token: $('input[name="_token"]').val()
        };
        
        // Create payment via AJAX
        $.ajax({
            url: '{{ route("midtrans.create") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Trigger Snap popup
                    snap.pay(response.snap_token, {
                        onSuccess: function(result) {
                            console.log('Payment success:', result);
                            window.location.href = '{{ route("payment.success") }}?order_id=' + response.order_id;
                        },
                        onPending: function(result) {
                            console.log('Payment pending:', result);
                            window.location.href = '{{ route("payment.pending") }}?order_id=' + response.order_id;
                        },
                        onError: function(result) {
                            console.log('Payment error:', result);
                            window.location.href = '{{ route("payment.error") }}?order_id=' + response.order_id;
                        },
                        onClose: function() {
                            console.log('Payment popup closed');
                            $('#loadingModal').modal('hide');
                        }
                    });
                } else {
                    $('#loadingModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Terjadi kesalahan saat membuat pembayaran'
                    });
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                let errorMessage = 'Terjadi kesalahan saat memproses pembayaran';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    });
    
    // Auto-fill description based on payment type
    $('#payment_type, #period').on('change', function() {
        const paymentType = $('#payment_type').val();
        const period = $('#period option:selected').text();
        
        if (paymentType === 'spp' && period) {
            $('#description').val('Pembayaran SPP ' + period);
        } else if (paymentType === 'bebas') {
            $('#description').val('Pembayaran Bebas');
        } else if (paymentType === 'other') {
            $('#description').val('Pembayaran Lainnya');
        }
    });
    
    // Format amount input
    $('#amount').on('input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
            $(this).val(value);
        }
    });
});
</script>
@endpush 