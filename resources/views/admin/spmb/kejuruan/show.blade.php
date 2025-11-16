@extends('layouts.adminty')

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
                        <i class="fas fa-eye me-2"></i>Detail Kejuruan SPMB
                    </h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Detail informasi kejuruan</p>
                </div>
                <a href="{{ route('manage.spmb.kejuruan.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="info-card">
                <div class="p-4">
                    <!-- Status -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-toggle-on me-1"></i>Status
                                </strong>
                            </div>
                            <div class="col-md-9">
                                @if($kejuruan->aktif)
                                    <span class="status-badge bg-success text-white">
                                        <i class="fas fa-check me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="status-badge bg-secondary text-white">
                                        <i class="fas fa-pause me-1"></i>Tidak Aktif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Nama Kejuruan -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-graduation-cap me-1"></i>Nama Kejuruan
                                </strong>
                            </div>
                            <div class="col-md-9">
                                <span class="fs-5 fw-bold">{{ $kejuruan->nama_kejuruan }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kode Kejuruan -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-code me-1"></i>Kode Kejuruan
                                </strong>
                            </div>
                            <div class="col-md-9">
                                <span class="badge bg-primary fs-6">{{ $kejuruan->kode_kejuruan }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    @if($kejuruan->deskripsi)
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-align-left me-1"></i>Deskripsi
                                </strong>
                            </div>
                            <div class="col-md-9">
                                <p class="text-muted mb-0">{{ $kejuruan->deskripsi }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Kuota -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>
                                    <i class="fas fa-users me-1"></i>Kuota
                                </strong>
                            </div>
                            <div class="col-md-9">
                                @if($kejuruan->kuota)
                                    <span class="text-info fs-5 fw-bold">{{ $kejuruan->kuota }}</span>
                                    <small class="text-muted ms-2">siswa</small>
                                @else
                                    <span class="text-success fs-5 fw-bold">Tidak Terbatas</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-plus me-1"></i>
                                    <strong>Dibuat:</strong> {{ $kejuruan->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-edit me-1"></i>
                                    <strong>Diupdate:</strong> {{ $kejuruan->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="info-card mt-4">
                <div class="p-4">
                    <h6 class="mb-3">
                        <i class="fas fa-chart-bar me-2"></i>Statistik Pendaftar
                    </h6>
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="text-primary">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h4>{{ $stats['total_pendaftar'] }}</h4>
                                <small class="text-muted">Total Pendaftar</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="text-success">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h4>{{ $stats['diterima'] }}</h4>
                                <small class="text-muted">Diterima</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="text-warning">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h4>{{ $stats['pending'] }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="text-danger">
                                <i class="fas fa-times-circle fa-2x mb-2"></i>
                                <h4>{{ $stats['ditolak'] }}</h4>
                                <small class="text-muted">Ditolak</small>
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
                            <a href="{{ route('manage.spmb.kejuruan.edit', $kejuruan->id) }}" 
                               class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <form method="POST" action="{{ route('manage.spmb.kejuruan.toggle-status', $kejuruan->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-{{ $kejuruan->aktif ? 'warning' : 'success' }}">
                                    <i class="fas fa-{{ $kejuruan->aktif ? 'pause' : 'play' }} me-1"></i>
                                    {{ $kejuruan->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" onclick="deleteKejuruan({{ $kejuruan->id }})">
                                <i class="fas fa-trash me-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pendaftar List -->
            @if($kejuruan->registrations->count() > 0)
            <div class="info-card mt-4">
                <div class="p-4">
                    <h6 class="mb-3">
                        <i class="fas fa-list me-2"></i>Daftar Pendaftar
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>No. Pendaftaran</th>
                                    <th>Nama</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Tanggal Daftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kejuruan->registrations as $registration)
                                <tr>
                                    <td>
                                        @if($registration->nomor_pendaftaran)
                                            <span class="badge bg-info">{{ $registration->nomor_pendaftaran }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $registration->name }}</td>
                                    <td>{{ $registration->phone }}</td>
                                    <td>{!! $registration->getStatusPendaftaranBadge() !!}</td>
                                    <td>{{ $registration->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
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
                <p>Apakah Anda yakin ingin menghapus kejuruan ini?</p>
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
    function deleteKejuruan(id) {
        document.getElementById('deleteForm').action = '{{ route("manage.spmb.kejuruan.destroy", ":id") }}'.replace(':id', id);
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush
