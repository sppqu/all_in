@extends('layouts.adminty')

@push('styles')
<style>
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .btn-primary {
        background: linear-gradient(135deg, #008060 0%, #00a86b 100%);
        border: none;
        border-radius: 10px;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #006b4f 0%, #008060 100%);
        transform: translateY(-1px);
    }
    .stats-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 25px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        border-left: 4px solid #008060;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
    }
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                        <i class="fas fa-eye me-2"></i>{{ $additionalFee->name }}
                    </h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ $additionalFee->code }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('manage.spmb.additional-fees.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
                    </a>
                    <a href="{{ route('manage.spmb.additional-fees.edit', $additionalFee->id) }}" class="btn btn-outline-warning me-2">
                        <i class="fas fa-edit me-1"></i>Edit Biaya
                    </a>
                    <form method="POST" action="{{ route('manage.spmb.additional-fees.toggle-status', $additionalFee->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-{{ $additionalFee->is_active ? 'secondary' : 'success' }}">
                            <i class="fas fa-{{ $additionalFee->is_active ? 'pause' : 'play' }} me-1"></i>
                            {{ $additionalFee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card p-4 text-center">
                <i class="fas fa-tag fa-2x text-primary mb-3"></i>
                <h4 class="mb-1">{{ $additionalFee->formatted_amount }}</h4>
                <p class="text-muted mb-0">Jumlah Biaya</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-4 text-center">
                <i class="fas fa-layer-group fa-2x text-info mb-3"></i>
                <h4 class="mb-1">{!! $additionalFee->category_badge !!}</h4>
                <p class="text-muted mb-0">Kategori</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-4 text-center">
                <i class="fas fa-info-circle fa-2x text-warning mb-3"></i>
                <h4 class="mb-1">{!! $additionalFee->type_badge !!}</h4>
                <p class="text-muted mb-0">Jenis Biaya</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-4 text-center">
                <i class="fas fa-power-off fa-2x {{ $additionalFee->is_active ? 'text-success' : 'text-secondary' }} mb-3"></i>
                <h4 class="mb-1">
                    @if($additionalFee->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </h4>
                <p class="text-muted mb-0">Status</p>
            </div>
        </div>
    </div>

    <!-- Detail Information -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Detail</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Informasi Dasar</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nama Biaya:</strong></td>
                                    <td>{{ $additionalFee->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kode:</strong></td>
                                    <td><code>{{ $additionalFee->code }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Kategori:</strong></td>
                                    <td>{!! $additionalFee->category_badge !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis:</strong></td>
                                    <td>{!! $additionalFee->type_badge !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jumlah:</strong></td>
                                    <td><strong class="text-primary">{{ $additionalFee->formatted_amount }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Urutan:</strong></td>
                                    <td>{{ $additionalFee->sort_order }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Status & Kondisi</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($additionalFee->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Dibuat:</strong></td>
                                    <td>{{ $additionalFee->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Diupdate:</strong></td>
                                    <td>{{ $additionalFee->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($additionalFee->conditions)
                                    <tr>
                                        <td><strong>Kondisi:</strong></td>
                                        <td>
                                            @foreach($additionalFee->conditions as $key => $value)
                                                <span class="badge bg-info me-1">{{ ucfirst($key) }}: {{ $value }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($additionalFee->description)
                        <hr>
                        <h6 class="text-primary">Deskripsi</h6>
                        <p class="text-muted">{{ $additionalFee->description }}</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-wave-square me-2"></i>Gelombang yang Menggunakan</h5>
                </div>
                <div class="card-body">
                    @if($waves->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Gelombang</th>
                                        <th>Biaya</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($waves as $wave)
                                        <tr>
                                            <td>
                                                <strong>{{ $wave->name }}</strong>
                                                <br><small class="text-muted">{{ $wave->description }}</small>
                                            </td>
                                            <td>
                                                <strong class="text-primary">
                                                    Rp {{ number_format($wave->pivot->amount, 0, ',', '.') }}
                                                </strong>
                                            </td>
                                            <td>
                                                @if($wave->pivot->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($waves->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $waves->links() }}
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <i class="fas fa-wave-square"></i>
                            <h6>Belum Digunakan</h6>
                            <p class="text-muted">Biaya ini belum digunakan di gelombang manapun.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
