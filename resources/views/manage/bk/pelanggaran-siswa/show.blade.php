@extends('layouts.adminty')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-eye me-2"></i>Detail Pelanggaran Siswa</h4>
            <p class="text-muted mb-0">Informasi lengkap pelanggaran siswa</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('manage.bk.pelanggaran-siswa.cetak-surat', $pelanggaranSiswa->id) }}" 
               class="btn btn-success" target="_blank">
                <i class="fas fa-print me-2"></i>Cetak Surat Pernyataan
            </a>
            <a href="{{ route('manage.bk.pelanggaran-siswa.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Siswa -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Siswa</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="40%" class="text-muted">NIS</td>
                            <td><strong>{{ $pelanggaranSiswa->siswa->student_nis }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama Lengkap</td>
                            <td><strong>{{ $pelanggaranSiswa->siswa->student_full_name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kelas</td>
                            <td>
                                @if($pelanggaranSiswa->siswa->class)
                                    <span class="badge bg-info">{{ $pelanggaranSiswa->siswa->class->class_name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Point</td>
                            <td>
                                <span class="badge bg-danger fs-6">{{ $totalPoint }} Point</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Status -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Status Pelanggaran</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Status Saat Ini</label>
                        <div>
                            {!! $pelanggaranSiswa->status_badge !!}
                        </div>
                    </div>

                    @if($pelanggaranSiswa->status === 'approved' && $pelanggaranSiswa->approved_at)
                    <div class="mb-3">
                        <label class="form-label small text-muted">Disetujui Oleh</label>
                        <div>
                            <strong>{{ $pelanggaranSiswa->approver->name ?? 'System' }}</strong><br>
                            <small class="text-muted">{{ $pelanggaranSiswa->approved_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    @endif

                    @if($pelanggaranSiswa->status === 'pending')
                    <div class="d-grid gap-2">
                        <form action="{{ route('manage.bk.pelanggaran-siswa.approve', $pelanggaranSiswa->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Setujui pelanggaran ini?')">
                                <i class="fas fa-check me-2"></i>Setujui
                            </button>
                        </form>
                        <form action="{{ route('manage.bk.pelanggaran-siswa.reject', $pelanggaranSiswa->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Tolak pelanggaran ini?')">
                                <i class="fas fa-times me-2"></i>Tolak
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Detail Pelanggaran -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Detail Pelanggaran</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Tanggal Pelanggaran</label>
                            <div class="fw-semibold">
                                <i class="far fa-calendar me-2 text-primary"></i>
                                {{ $pelanggaranSiswa->tanggal_pelanggaran->format('d F Y') }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Kategori</label>
                            <div>
                                @php
                                    $kategoriNama = $pelanggaranSiswa->pelanggaran->kategori->nama;
                                    $badgeClass = $kategoriNama == 'Pelanggaran Ringan' ? 'warning' : ($kategoriNama == 'Pelanggaran Sedang' ? 'orange' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $badgeClass }} fs-6">{{ $kategoriNama }}</span>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label small text-muted">Jenis Pelanggaran</label>
                            <div class="alert alert-light border mb-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $pelanggaranSiswa->pelanggaran->nama }}</strong><br>
                                        <small class="text-muted">Kode: {{ $pelanggaranSiswa->pelanggaran->kode }}</small>
                                    </div>
                                    <span class="badge bg-danger fs-5">{{ $pelanggaranSiswa->pelanggaran->point }} Point</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Pelapor</label>
                            <div class="fw-semibold">
                                <i class="fas fa-user-tie me-2 text-primary"></i>
                                {{ $pelanggaranSiswa->pelapor }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Tempat Kejadian</label>
                            <div class="fw-semibold">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                {{ $pelanggaranSiswa->tempat ?? '-' }}
                            </div>
                        </div>

                        @if($pelanggaranSiswa->keterangan)
                        <div class="col-md-12 mb-3">
                            <label class="form-label small text-muted">Keterangan/Kronologi</label>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ $pelanggaranSiswa->keterangan }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <div class="fw-semibold">Pelanggaran Dicatat</div>
                                <small class="text-muted">
                                    {{ $pelanggaranSiswa->created_at->format('d/m/Y H:i') }}
                                    @if($pelanggaranSiswa->creator)
                                        oleh {{ $pelanggaranSiswa->creator->name }}
                                    @endif
                                </small>
                            </div>
                        </div>

                        @if($pelanggaranSiswa->status === 'approved' && $pelanggaranSiswa->approved_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <div class="fw-semibold">Pelanggaran Disetujui</div>
                                <small class="text-muted">
                                    {{ $pelanggaranSiswa->approved_at->format('d/m/Y H:i') }}
                                    @if($pelanggaranSiswa->approver)
                                        oleh {{ $pelanggaranSiswa->approver->name }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @endif

                        @if($pelanggaranSiswa->status === 'rejected')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <div class="fw-semibold">Pelanggaran Ditolak</div>
                                <small class="text-muted">
                                    {{ $pelanggaranSiswa->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-orange {
    background-color: #ff9800 !important;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -23px;
    top: 8px;
    bottom: -12px;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px;
}

.timeline-content {
    padding-left: 10px;
}
</style>
@endsection

