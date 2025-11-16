@extends('layouts.adminty')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark">
                <i class="fas fa-eye me-2 text-info"></i>Detail Bimbingan Konseling
            </h2>
            <p class="text-muted mb-0">Informasi lengkap bimbingan konseling siswa</p>
        </div>
        <div>
            <a href="{{ route('manage.bk.bimbingan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ route('manage.bk.bimbingan.edit', $bimbingan->bimbingan_id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Info -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Siswa & Bimbingan</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted small">NIS</label>
                            <p class="mb-0 fw-bold">{{ $bimbingan->siswa->student_nis }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted small">Nama Siswa</label>
                            <p class="mb-0 fw-bold">{{ $bimbingan->siswa->student_full_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted small">Kelas</label>
                            <p class="mb-0">
                                @if($bimbingan->siswa->class)
                                    <span class="badge bg-secondary">{{ $bimbingan->siswa->class->class_name }}</span>
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted small">Tanggal Bimbingan</label>
                            <p class="mb-0">{{ $bimbingan->tanggal_bimbingan->format('d F Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold text-muted small">Jenis Bimbingan</label>
                            <p class="mb-0">
                                <span class="badge bg-info">{{ ucfirst($bimbingan->jenis_bimbingan) }}</span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold text-muted small">Kategori</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $bimbingan->kategori_badge }}">{{ ucfirst($bimbingan->kategori) }}</span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-semibold text-muted small">Sesi Ke</label>
                            <p class="mb-0">
                                <span class="badge bg-primary">Sesi #{{ $bimbingan->sesi_ke }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permasalahan -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Permasalahan</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $bimbingan->permasalahan }}</p>
                </div>
            </div>

            <!-- Analisis -->
            @if($bimbingan->analisis)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="fas fa-search me-2"></i>Analisis</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $bimbingan->analisis }}</p>
                </div>
            </div>
            @endif

            <!-- Tindakan -->
            @if($bimbingan->tindakan)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Tindakan/Solusi</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $bimbingan->tindakan }}</p>
                </div>
            </div>
            @endif

            <!-- Hasil -->
            @if($bimbingan->hasil)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Hasil</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $bimbingan->hasil }}</p>
                </div>
            </div>
            @endif

            <!-- Catatan -->
            @if($bimbingan->catatan)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Catatan Tambahan</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $bimbingan->catatan }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Status & Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-semibold text-muted small">Status Bimbingan</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $bimbingan->status_badge }} fs-6">{{ ucfirst($bimbingan->status) }}</span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-semibold text-muted small">Guru BK</label>
                        <p class="mb-0">{{ $bimbingan->guruBk->name ?? '-' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-semibold text-muted small">Dibuat</label>
                        <p class="mb-0">{{ $bimbingan->created_at->format('d F Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="fw-semibold text-muted small">Terakhir Update</label>
                        <p class="mb-0">{{ $bimbingan->updated_at->format('d F Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Riwayat Bimbingan -->
            @if($riwayat->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Bimbingan Siswa</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($riwayat as $item)
                        <a href="{{ route('manage.bk.bimbingan.show', $item->bimbingan_id) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-muted">{{ $item->tanggal_bimbingan->format('d M Y') }}</small>
                                    <p class="mb-1 small fw-semibold">
                                        Sesi #{{ $item->sesi_ke }} - {{ ucfirst($item->jenis_bimbingan) }}
                                    </p>
                                    <span class="badge bg-{{ $item->status_badge }} badge-sm">{{ ucfirst($item->status) }}</span>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

