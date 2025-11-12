@extends('layouts.coreui')

@push('styles')
<style>
    .info-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
    }
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .info-item {
        border-bottom: 1px solid #e9ecef;
        padding: 15px 0;
    }
    .info-item:last-child {
        border-bottom: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                        <i class="fas fa-eye me-2"></i>Detail Pengaturan SPMB
                    </h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Detail informasi pengaturan SPMB</p>
                </div>
                <a href="{{ route('manage.spmb.settings') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="info-card">
                <div class="p-4">
                    <!-- Status Pendaftaran -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-toggle-on me-1"></i>Status Pendaftaran
                                </strong>
                            </div>
                            <div class="col-md-9">
                                @if($settings->pendaftaran_dibuka)
                                    <span class="status-badge bg-success text-white">
                                        <i class="fas fa-unlock me-1"></i>Dibuka
                                    </span>
                                @else
                                    <span class="status-badge bg-danger text-white">
                                        <i class="fas fa-lock me-1"></i>Ditutup
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tahun Pelajaran -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-calendar me-1"></i>Tahun Pelajaran
                                </strong>
                            </div>
                            <div class="col-md-9">
                                <span class="badge bg-primary fs-6">{{ $settings->tahun_pelajaran }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Buka -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-calendar-plus me-1"></i>Tanggal Buka
                                </strong>
                            </div>
                            <div class="col-md-9">
                                @if($settings->tanggal_buka)
                                    <span class="text-success">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        {{ $settings->tanggal_buka->format('d F Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">
                                        <i class="fas fa-calendar-times me-1"></i>Tidak ditentukan
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Tutup -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-calendar-minus me-1"></i>Tanggal Tutup
                                </strong>
                            </div>
                            <div class="col-md-9">
                                @if($settings->tanggal_tutup)
                                    <span class="text-danger">
                                        <i class="fas fa-calendar-times me-1"></i>
                                        {{ $settings->tanggal_tutup->format('d F Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">
                                        <i class="fas fa-calendar-times me-1"></i>Tidak ditentukan
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Biaya Pendaftaran -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-money-bill me-1"></i>Biaya Pendaftaran
                                </strong>
                            </div>
                            <div class="col-md-9">
                                <span class="text-success fs-5 fw-bold">
                                    Rp {{ number_format($settings->biaya_pendaftaran, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Biaya SPMB -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-money-bill-wave me-1"></i>Biaya SPMB
                                </strong>
                            </div>
                            <div class="col-md-9">
                                <span class="text-success fs-5 fw-bold">
                                    Rp {{ number_format($settings->biaya_spmb, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    @if($settings->deskripsi)
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-align-left me-1"></i>Deskripsi
                                </strong>
                            </div>
                            <div class="col-md-9">
                                <p class="text-muted mb-0">{{ $settings->deskripsi }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Timestamps -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-plus me-1"></i>
                                    <strong>Dibuat:</strong> {{ $settings->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-edit me-1"></i>
                                    <strong>Diupdate:</strong> {{ $settings->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="info-card mt-4">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>Aksi
                        </h6>
                        <div class="btn-group" role="group">
                            <a href="{{ route('manage.spmb.settings.edit', $settings->id) }}" 
                               class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <form method="POST" action="{{ route('manage.spmb.settings.toggle-registration', $settings->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-{{ $settings->pendaftaran_dibuka ? 'danger' : 'success' }}">
                                    <i class="fas fa-{{ $settings->pendaftaran_dibuka ? 'lock' : 'unlock' }} me-1"></i>
                                    {{ $settings->pendaftaran_dibuka ? 'Tutup' : 'Buka' }} Pendaftaran
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" onclick="deleteSetting({{ $settings->id }})">
                                <i class="fas fa-trash me-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pengaturan SPMB ini?</p>
                <p class="text-danger"><strong>Data yang dihapus tidak dapat dikembalikan!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteSetting(id) {
        document.getElementById('deleteForm').action = '{{ route("manage.spmb.settings.destroy", ":id") }}'.replace(':id', id);
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush
