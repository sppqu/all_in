@extends('layouts.coreui')

@section('title', 'Berlangganan Saya - SPPQU')

@section('active_menu', 'menu.billing')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('manage.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Berlangganan</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-crown me-2"></i>
                    Berlangganan Admin
                </h4>
            </div>
        </div>
    </div>

    @if(request('status') == 'success')
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Berhasil!</strong> Pembayaran berhasil diproses. Berlangganan Anda telah aktif.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(request('status') == 'pending')
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-clock me-2"></i>
        <strong>Menunggu!</strong> Pembayaran Anda sedang diproses. Mohon tunggu konfirmasi.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(request('status') == 'error')
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Gagal!</strong> Terjadi kesalahan dalam pembayaran. Silakan coba lagi.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Current Subscription Status -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Status Berlangganan
                    </h5>
                </div>
                <div class="card-body">
                    @if($activeSubscription && $activeSubscription->status == 'active')
                        <div class="text-center mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-check text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="text-success mb-1">Berlangganan Aktif</h5>
                            <p class="text-muted mb-0">{{ $activeSubscription->plan_name }}</p>
                            <small class="text-muted">Oleh: {{ $activeSubscription->user->name ?? 'Admin' }}</small>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Berakhir:</strong><br>
                            <span class="text-primary">{{ \Carbon\Carbon::parse($activeSubscription->expires_at)->format('d M Y H:i') }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Sisa Waktu:</strong><br>
                            <span class="text-info">{{ \Carbon\Carbon::now()->diffForHumans($activeSubscription->expires_at, ['parts' => 2]) }}</span>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-outline-danger" onclick="cancelSubscription({{ $activeSubscription->id }})">
                                <i class="fas fa-times me-2"></i>
                                Batalkan Berlangganan
                            </button>
                        </div>
                    @else
                        <div class="text-center mb-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="text-warning mb-1">Tidak Ada Berlangganan Aktif</h5>
                            <p class="text-muted mb-0">Tidak ada admin yang berlangganan aktif</p>
                        </div>

                        <div class="d-grid">
                            <a href="{{ route('manage.subscription.plans') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Berlangganan Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-3">
                <div class="d-flex gap-2">
                    <a href="{{ route('manage.addons.index') }}" class="btn btn-primary flex-fill">
                        <i class="fas fa-puzzle-piece me-2"></i>Add-ons Premium
                    </a>
                    <a href="{{ route('manage.subscription.premium-features') }}" class="btn btn-warning flex-fill">
                        <i class="fas fa-star me-2"></i>Lihat Fitur Premium
                    </a>
                </div>
            </div>
        </div>

        <!-- Subscription History -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Riwayat Berlangganan
                    </h5>
                </div>
                <div class="card-body">
                    @if($subscriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Admin</th>
                                        <th>Paket</th>
                                        <th>Harga</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subscriptions as $subscription)
                                    <tr>
                                        <td>
                                            <strong>{{ $subscription->user->name ?? 'Admin' }}</strong><br>
                                            <small class="text-muted">{{ $subscription->user->email ?? '' }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $subscription->plan_name }}</strong><br>
                                            <small class="text-muted">{{ $subscription->duration_days }} hari</small>
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($subscription->amount, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @if($subscription->status == 'active')
                                                <span class="badge bg-success">Aktif</span>
                                            @elseif($subscription->status == 'pending')
                                                <span class="badge bg-warning text-dark">Menunggu</span>
                                            @elseif($subscription->status == 'cancelled')
                                                <span class="badge bg-danger">Dibatalkan</span>
                                            @elseif($subscription->status == 'expired')
                                                <span class="badge bg-secondary">Berakhir</span>
                                            @else
                                                <span class="badge bg-info">{{ ucfirst($subscription->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ \Carbon\Carbon::parse($subscription->created_at)->format('d M Y') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($subscription->created_at)->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div>
                                                    @if($subscription->status == 'pending')
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-clock me-1"></i>
                                                            Menunggu Pembayaran
                                                        </span>
                                                        <small class="d-block mt-1 text-muted">
                                                            Silakan selesaikan pembayaran
                                                        </small>
                                                    @elseif($subscription->status == 'active')
                                                        <span class="text-success">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            Aktif
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </div>
                                                
                                                <div class="ms-auto d-flex gap-1">
                                                    @if($subscription->status == 'pending')
                                                        @if($subscription->payment_url)
                                                            <a href="{{ $subscription->payment_url }}" target="_blank" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-credit-card me-1"></i>
                                                                Bayar
                                                            </a>
                                                        @else
                                                            <button class="btn btn-sm btn-warning" onclick="alert('Link pembayaran tidak tersedia. Silakan buat subscription baru.')">
                                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                                Link Unavailable
                                                            </button>
                                                        @endif
                                                    @endif
                                                    
                                                    @if($subscription->invoice)
                                                        <a href="{{ route('manage.subscription.download-invoice', ['invoice_id' => $subscription->invoice->id]) }}" class="btn btn-sm btn-outline-info" title="Download Invoice">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">Belum Ada Riwayat Berlangganan</h5>
                            <p class="text-muted">Mulai berlangganan untuk melihat riwayat di sini</p>
                            <a href="{{ route('manage.subscription.plans') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Berlangganan Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelSubscription(subscriptionId) {
    if (confirm('Apakah Anda yakin ingin membatalkan berlangganan ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("manage.subscription.cancel") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const subscriptionIdInput = document.createElement('input');
        subscriptionIdInput.type = 'hidden';
        subscriptionIdInput.name = 'subscription_id';
        subscriptionIdInput.value = subscriptionId;
        
        form.appendChild(csrfToken);
        form.appendChild(subscriptionIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-refresh subscription status every 5 minutes
setInterval(function() {
            fetch('{{ route("manage.subscription.check-status") }}')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'expired') {
                location.reload();
            }
        });
}, 300000); // 5 minutes
</script>

<style>
.card {
    border-radius: 15px;
}

.gap-1 {
    gap: 0.25rem !important;
}

.gap-2 {
    gap: 0.5rem !important;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}
</style>
@endsection
