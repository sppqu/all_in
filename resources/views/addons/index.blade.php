@extends('layouts.coreui')

@section('title', 'Add-ons Premium - SPPQU')

@section('active_menu', 'menu.billing')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('manage.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manage.subscription.index') }}">Berlangganan</a></li>
                        <li class="breadcrumb-item active">Add-ons Premium</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-puzzle-piece me-2 text-primary"></i>
                    Add-ons Premium SPPQU
                </h4>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- User's Active Add-ons -->
    @if($userAddons->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-check-circle me-2 text-success"></i>
                        Add-ons Aktif Anda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($userAddons as $userAddon)
                            @if($userAddon->addon)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="mb-2">
                                            <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                                        </div>
                                        <h6 class="card-title">{{ $userAddon->addon->name }}</h6>
                                        <p class="card-text text-muted small">
                                            Dibeli: {{ $userAddon->purchased_at->format('d M Y') }}
                                        </p>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Aktif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Available Add-ons -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star me-2 text-warning"></i>
                        Add-ons Tersedia
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($addons as $addon)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        @if($addon->slug === 'payment-gateway')
                                            <i class="fas fa-credit-card text-primary" style="font-size: 2.5rem;"></i>
                                        @elseif($addon->slug === 'whatsapp-gateway')
                                            <i class="fab fa-whatsapp text-success" style="font-size: 2.5rem;"></i>
                                        @elseif($addon->slug === 'analisis-target')
                                            <i class="fas fa-chart-line text-info" style="font-size: 2.5rem;"></i>
                                        @elseif($addon->slug === 'spmb')
                                            <i class="fas fa-user-graduate text-primary" style="font-size: 2.5rem;"></i>
                                        @elseif($addon->slug === 'bk')
                                            <i class="fas fa-clipboard-list text-danger" style="font-size: 2.5rem;"></i>
                                        @elseif($addon->slug === 'ejurnal-7kaih')
                                            <i class="fas fa-book text-purple" style="font-size: 2.5rem; color: #6f42c1;"></i>
                                        @elseif($addon->slug === 'e-perpustakaan')
                                            <i class="fas fa-book-reader text-primary" style="font-size: 2.5rem; color: #667eea;"></i>
                                        @elseif($addon->slug === 'inventaris')
                                            <i class="fas fa-boxes text-info" style="font-size: 2.5rem;"></i>
                                        @else
                                            <i class="fas fa-puzzle-piece text-primary" style="font-size: 2.5rem;"></i>
                                        @endif
                                    </div>
                                    <h5 class="card-title">{{ $addon->name }}</h5>
                                    <p class="card-text text-muted">
                                        {{ $addon->description }}
                                    </p>
                                    <div class="mb-3">
                                        <h4 class="text-primary mb-0">
                                            Rp {{ number_format($addon->price, 0, ',', '.') }}
                                        </h4>
                                        <small class="text-muted">
                                            @if($addon->type === 'one_time')
                                                Sekali Bayar
                                            @else
                                                Berlangganan
                                            @endif
                                        </small>
                                    </div>
                                    <div class="mb-3">
                                        @if(in_array($addon->slug, ['inventaris']))
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-clock me-1"></i>Segera
                                            </span>
                                        @elseif($addon->slug === 'spmb')
                                            @if($userAddons->where('addon_id', $addon->id)->count() > 0)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Sudah Dimiliki
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-lock me-1"></i>Belum Dimiliki
                                                </span>
                                            @endif
                                        @elseif($userAddons->where('addon_id', $addon->id)->count() > 0)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Sudah Dimiliki
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-lock me-1"></i>Belum Dimiliki
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-grid">
                                        @if(in_array($addon->slug, ['inventaris']))
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-clock me-2"></i>Segera
                                            </button>
                                        @elseif($addon->slug === 'spmb')
                                            @if($userAddons->where('addon_id', $addon->id)->count() > 0)
                                                <button class="btn btn-success" disabled>
                                                    <i class="fas fa-check me-2"></i>Sudah Dimiliki
                                                </button>
                                            @else
                                                <a href="{{ route('manage.addons.show', $addon->slug) }}" class="btn btn-primary">
                                                    <i class="fas fa-shopping-cart me-2"></i>Beli Sekarang
                                                </a>
                                            @endif
                                        @elseif($userAddons->where('addon_id', $addon->id)->count() > 0)
                                            <button class="btn btn-success" disabled>
                                                <i class="fas fa-check me-2"></i>Sudah Dimiliki
                                            </button>
                                        @else
                                            <a href="{{ route('manage.addons.show', $addon->slug) }}" class="btn btn-primary">
                                                <i class="fas fa-shopping-cart me-2"></i>Beli Sekarang
                                            </a>
                                        @endif
                                    </div>
                                    
                                    <!-- Debug button for testing -->
                                    @if($addon->slug === 'payment-gateway')
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-info refresh-status-btn" data-addon-slug="{{ $addon->slug }}">
                                                <i class="fas fa-sync-alt me-1"></i>Refresh Status
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        Informasi Add-ons
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success me-2"></i>Keuntungan Add-ons</h6>
                            <ul class="text-muted">
                                <li>Fitur tambahan yang dapat dipilih sesuai kebutuhan</li>
                                <li>Pembayaran sekali bayar (tidak berlangganan)</li>
                                <li>Akses seumur hidup untuk add-on yang dibeli</li>
                                <li>Kompatibel dengan semua paket berlangganan</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-shield-alt text-primary me-2"></i>Garansi & Support</h6>
                            <ul class="text-muted">
                                <li>Garansi uang kembali 30 hari</li>
                                <li>Support teknis 24/7</li>
                                <li>Update fitur gratis</li>
                                <li>Dokumentasi lengkap</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh status button functionality
    document.querySelectorAll('.refresh-status-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const addonSlug = this.getAttribute('data-addon-slug');
            const button = this;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
            button.disabled = true;
            
            // Send request to refresh status
            fetch('{{ route("manage.addons.refresh-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    addon_slug: addonSlug
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Refresh status response:', data);
                
                // Show result
                if (data.has_addon) {
                    alert('Status: Add-on AKTIF!');
                } else {
                    alert('Status: Add-on belum aktif');
                }
                
                // Reload page to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
            .catch(error => {
                console.error('Refresh status error:', error);
                alert('Error refreshing status');
            })
            .finally(() => {
                // Reset button state
                button.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Refresh Status';
                button.disabled = false;
            });
        });
    });
});
</script>
@endsection
