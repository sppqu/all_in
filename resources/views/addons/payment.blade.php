@extends('layouts.coreui')

@section('title', 'Pembayaran Add-on - SPPQU')

@section('active_menu', 'menu.billing')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('manage.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manage.addons.index') }}">Add-ons</a></li>
                        <li class="breadcrumb-item active">Pembayaran</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-credit-card me-2 text-primary"></i>
                    Pembayaran Add-on
                </h4>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-cart me-2 text-primary"></i>
                        Detail Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Add-on yang Dibeli</h6>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-credit-card text-primary me-3" style="font-size: 2rem;"></i>
                                <div>
                                    <h5 class="mb-1">{{ $addon->name }}</h5>
                                    <p class="text-muted mb-0">{{ $addon->description }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Total Pembayaran</h6>
                            <div class="text-end">
                                <h3 class="text-primary mb-0">Rp {{ number_format($addon->price, 0, ',', '.') }}</h3>
                                <small class="text-muted">Sekali Bayar</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="text-center">
                        <h6>Pilih Metode Pembayaran</h6>
                        <p class="text-muted">Klik tombol di bawah untuk memilih metode pembayaran yang tersedia</p>
                        
                        <button id="pay-button" class="btn btn-primary btn-lg">
                            <i class="fas fa-credit-card me-2"></i>
                            Pilih Metode Pembayaran
                        </button>
                    </div>

                    <div class="mt-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Pembayaran akan diproses melalui Midtrans Payment Gateway</li>
                                <li>Add-on akan aktif otomatis setelah pembayaran berhasil</li>
                                <li>Anda akan menerima email konfirmasi setelah pembayaran</li>
                                <li>Jika ada masalah, hubungi support kami</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Midtrans Script -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key', 'SB-Mid-client-lJRoDoWDFqA6NzlJ') }}"></script>

<script>
document.getElementById('pay-button').onclick = function(e) {
    e.preventDefault();
    
    snap.pay('{{ $snapToken }}', {
        onSuccess: function(result) {
            console.log('Payment success:', result);
            // Send payment result to our callback endpoint
            fetch('{{ route("manage.addons.callback") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    order_id: result.order_id,
                    transaction_status: result.transaction_status,
                    fraud_status: result.fraud_status || null,
                    payment_type: result.payment_type,
                    transaction_id: result.transaction_id
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Callback response:', data);
                window.location.href = '{{ route("manage.addons.index") }}?status=success';
            })
            .catch(error => {
                console.error('Callback error:', error);
                window.location.href = '{{ route("manage.addons.index") }}?status=success';
            });
        },
        onPending: function(result) {
            console.log('Payment pending:', result);
            // Send payment result to our callback endpoint
            fetch('{{ route("manage.addons.callback") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    order_id: result.order_id,
                    transaction_status: result.transaction_status,
                    fraud_status: result.fraud_status || null,
                    payment_type: result.payment_type,
                    transaction_id: result.transaction_id
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Callback response:', data);
                window.location.href = '{{ route("manage.addons.index") }}?status=pending';
            })
            .catch(error => {
                console.error('Callback error:', error);
                window.location.href = '{{ route("manage.addons.index") }}?status=pending';
            });
        },
        onError: function(result) {
            console.log('Payment error:', result);
            // Send payment result to our callback endpoint
            fetch('{{ route("manage.addons.callback") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    order_id: result.order_id,
                    transaction_status: result.transaction_status,
                    fraud_status: result.fraud_status || null,
                    payment_type: result.payment_type,
                    transaction_id: result.transaction_id
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Callback response:', data);
                window.location.href = '{{ route("manage.addons.index") }}?status=error';
            })
            .catch(error => {
                console.error('Callback error:', error);
                window.location.href = '{{ route("manage.addons.index") }}?status=error';
            });
        },
        onClose: function() {
            console.log('Customer closed the popup without finishing payment');
        }
    });
};
</script>
@endsection
