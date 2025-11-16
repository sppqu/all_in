@extends('layouts.adminty')

@push('styles')
<style>
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

    .settings-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 25px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .form-control:focus {
        border-color: #008060;
        box-shadow: 0 0 0 0.2rem rgba(0, 128, 96, 0.25);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                <i class="fas fa-cogs me-2"></i>Pengaturan SPMB
            </h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola pengaturan pendaftaran SPMB</p>
        </div>
        <a href="{{ route('manage.spmb.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <!-- Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end align-items-center">
                <div class="btn-group">
                    @if(menuCan('menu.spmb.waves'))
                    <a href="{{ route('manage.spmb.waves.index') }}" class="btn btn-outline-success me-2">
                        <i class="fas fa-wave-square me-1"></i>Gelombang Pendaftaran
                    </a>
                    @endif
                    <a href="{{ route('manage.spmb.additional-fees.index') }}" class="btn btn-outline-info me-2">
                        <i class="fas fa-plus-circle me-1"></i>Biaya Tambahan
                    </a>
                    @if($setting)
                    <a href="{{ route('manage.spmb.settings.edit', $setting->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit Pengaturan
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Card -->
    @if($setting)
    <div class="settings-card">
        <div class="p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Tahun Pelajaran</label>
                    <p class="mb-0 fw-bold">{{ $setting->tahun_pelajaran }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Status Pendaftaran</label>
                    <p class="mb-0">
                        @if($setting->pendaftaran_dibuka)
                            <span class="status-badge bg-success text-white">Dibuka</span>
                        @else
                            <span class="status-badge bg-danger text-white">Ditutup</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Tanggal Buka</label>
                    <p class="mb-0">
                        @if($setting->tanggal_buka)
                            {{ $setting->tanggal_buka->format('d/m/Y') }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Tanggal Tutup</label>
                    <p class="mb-0">
                        @if($setting->tanggal_tutup)
                            {{ $setting->tanggal_tutup->format('d/m/Y') }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Biaya Pendaftaran</label>
                    <p class="mb-0 fw-bold text-primary">Rp {{ number_format($setting->biaya_pendaftaran, 0, ',', '.') }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Biaya SPMB</label>
                    <p class="mb-0 fw-bold text-primary">Rp {{ number_format($setting->biaya_spmb, 0, ',', '.') }}</p>
                </div>
                @if($setting->deskripsi)
                <div class="col-12 mb-3">
                    <label class="text-muted small">Deskripsi</label>
                    <p class="mb-0">{{ $setting->deskripsi }}</p>
                </div>
                @endif
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <a href="{{ route('manage.spmb.settings.edit', $setting->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit Pengaturan
                        </a>
                        <form method="POST" action="{{ route('manage.spmb.settings.toggle-registration', $setting->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-{{ $setting->pendaftaran_dibuka ? 'danger' : 'success' }}">
                                <i class="fas fa-{{ $setting->pendaftaran_dibuka ? 'lock' : 'unlock' }} me-1"></i>
                                {{ $setting->pendaftaran_dibuka ? 'Tutup Pendaftaran' : 'Buka Pendaftaran' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Kejuruan Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="settings-card">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-graduation-cap me-2"></i>Data Kejuruan
                        </h5>
                        <a href="{{ route('manage.spmb.kejuruan.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-cogs me-1"></i>Kelola Kejuruan
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Kejuruan</th>
                                    <th>Kuota</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kejuruan as $k)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $k->kode_kejuruan }}</span></td>
                                    <td>{{ $k->nama_kejuruan }}</td>
                                    <td>{{ $k->kuota ?? 'Tidak Terbatas' }}</td>
                                    <td>
                                        @if($k->aktif)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">
                                        <span class="text-muted">Belum ada data kejuruan</span>
                                        <br>
                                        <a href="{{ route('manage.spmb.kejuruan.create') }}" class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-plus me-1"></i>Tambah Kejuruan
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
