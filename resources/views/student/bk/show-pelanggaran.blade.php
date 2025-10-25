@extends('layouts.student')

@section('title', 'Detail Pelanggaran')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('student.bk.index') }}" class="btn btn-sm btn-outline-secondary me-3" style="border-radius: 10px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h5 class="fw-bold mb-1" style="color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Detail Pelanggaran
                </h5>
                <p class="text-muted small mb-0">Informasi lengkap pelanggaran</p>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm mb-4" style="border-radius: 15px; border: none;">
        <div class="card-body p-4">
            <!-- Header Info -->
            <div class="pelanggaran-header mb-4">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="fw-bold mb-2">{{ $pelanggaran->nama_pelanggaran }}</h4>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge {{ $pelanggaran->kategori == 'Ringan' ? 'bg-success' : ($pelanggaran->kategori == 'Sedang' ? 'bg-warning text-dark' : 'bg-danger') }} px-3 py-2">
                                <i class="fas fa-tag me-1"></i>{{ $pelanggaran->kategori }}
                            </span>
                            <span class="badge bg-secondary px-3 py-2">
                                <i class="fas fa-star me-1"></i>{{ $pelanggaran->poin }} Poin
                            </span>
                            <span class="badge bg-info px-3 py-2">
                                <i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($pelanggaran->tanggal_pelanggaran)->format('d F Y') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="poin-display {{ $pelanggaran->kategori == 'Ringan' ? 'bg-success' : ($pelanggaran->kategori == 'Sedang' ? 'bg-warning' : 'bg-danger') }}">
                            <div class="poin-number">{{ $pelanggaran->poin }}</div>
                            <div class="poin-label">POIN</div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Student Info -->
            <div class="info-section mb-4">
                <h6 class="fw-bold mb-3" style="color: #2c3e50;">
                    <i class="fas fa-user me-2" style="color: #3498db;"></i>Informasi Siswa
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="info-box">
                            <label class="text-muted small">Nama Lengkap</label>
                            <p class="fw-semibold mb-0">{{ $pelanggaran->student_full_name }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <label class="text-muted small">NIS</label>
                            <p class="fw-semibold mb-0">{{ $pelanggaran->student_nis }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <label class="text-muted small">Kelas</label>
                            <p class="fw-semibold mb-0">{{ $pelanggaran->class_name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deskripsi Pelanggaran -->
            @if($pelanggaran->deskripsi_pelanggaran)
            <div class="info-section mb-4">
                <h6 class="fw-bold mb-3" style="color: #2c3e50;">
                    <i class="fas fa-file-alt me-2" style="color: #f39c12;"></i>Deskripsi Pelanggaran
                </h6>
                <div class="content-box">
                    <p class="mb-0">{{ $pelanggaran->deskripsi_pelanggaran }}</p>
                </div>
            </div>
            @endif

            <!-- Kronologi/Keterangan -->
            @if($pelanggaran->keterangan)
            <div class="info-section mb-4">
                <h6 class="fw-bold mb-3" style="color: #2c3e50;">
                    <i class="fas fa-list-ol me-2" style="color: #e74c3c;"></i>Kronologi Kejadian
                </h6>
                <div class="content-box">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $pelanggaran->keterangan }}</p>
                </div>
            </div>
            @endif

            <!-- Tempat/Lokasi -->
            @if($pelanggaran->tempat)
            <div class="info-section mb-4">
                <h6 class="fw-bold mb-3" style="color: #2c3e50;">
                    <i class="fas fa-map-marker-alt me-2" style="color: #f39c12;"></i>Lokasi Kejadian
                </h6>
                <div class="content-box">
                    <p class="mb-0">{{ $pelanggaran->tempat }}</p>
                </div>
            </div>
            @endif

            <!-- Status -->
            <div class="info-section mb-4">
                <h6 class="fw-bold mb-3" style="color: #2c3e50;">
                    <i class="fas fa-info-circle me-2" style="color: #3498db;"></i>Status
                </h6>
                <div class="content-box">
                    @if($pelanggaran->status == 'approved')
                        <span class="badge bg-success px-3 py-2">
                            <i class="fas fa-check-circle me-1"></i>Disetujui
                        </span>
                    @elseif($pelanggaran->status == 'rejected')
                        <span class="badge bg-danger px-3 py-2">
                            <i class="fas fa-times-circle me-1"></i>Ditolak
                        </span>
                    @else
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="fas fa-clock me-1"></i>Menunggu Persetujuan
                        </span>
                    @endif
                </div>
            </div>

            <!-- Catatan Admin -->
            @if($pelanggaran->catatan_admin)
            <div class="info-section mb-4">
                <h6 class="fw-bold mb-3" style="color: #2c3e50;">
                    <i class="fas fa-comment-dots me-2" style="color: #9b59b6;"></i>Catatan Admin BK
                </h6>
                <div class="alert alert-info" style="border-radius: 10px; border-left: 4px solid #3498db;">
                    <p class="mb-0">{{ $pelanggaran->catatan_admin }}</p>
                </div>
            </div>
            @endif

            <!-- Pelapor -->
            @if($pelanggaran->pelapor)
            <div class="info-section mb-4">
                <h6 class="fw-bold mb-3" style="color: #2c3e50;">
                    <i class="fas fa-user-tie me-2" style="color: #34495e;"></i>Pelapor
                </h6>
                <div class="content-box">
                    <p class="mb-0">
                        <strong>{{ $pelanggaran->pelapor }}</strong>
                    </p>
                </div>
            </div>
            @endif

            <!-- Admin/Guru BK yang Menginput -->
            <div class="info-section mb-4">
                <h6 class="fw-bold mb-3" style="color: #2c3e50;">
                    <i class="fas fa-user-check me-2" style="color: #34495e;"></i>Diinput Oleh
                </h6>
                <div class="content-box">
                    <p class="mb-0">
                        <strong>{{ $pelanggaran->nama_guru ?? 'Admin BK' }}</strong>
                        <br>
                        <span class="text-muted small">Dicatat pada: {{ \Carbon\Carbon::parse($pelanggaran->created_at)->format('d F Y, H:i') }} WIB</span>
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 justify-content-between align-items-center mt-4 pt-3 border-top">
                <a href="{{ route('student.bk.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
                <a href="{{ route('student.bk.create-bimbingan') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #3498db, #2980b9); border: none; border-radius: 10px;">
                    <i class="fas fa-hand-holding-heart me-1"></i>Ajukan Bimbingan
                </a>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="card shadow-sm" style="border-radius: 15px; border: none; border-left: 4px solid #f39c12;">
        <div class="card-body">
            <div class="d-flex align-items-start">
                <i class="fas fa-lightbulb me-3" style="font-size: 1.8rem; color: #f39c12; margin-top: 3px;"></i>
                <div>
                    <h6 class="fw-bold mb-2" style="color: #f39c12;">Catatan Penting</h6>
                    <ul class="mb-0 small text-muted">
                        <li>Gunakan pelanggaran ini sebagai pembelajaran untuk menjadi pribadi yang lebih baik</li>
                        <li>Jika ada yang ingin Anda diskusikan, silakan ajukan bimbingan konseling</li>
                        <li>Pelanggaran yang tercatat akan mempengaruhi penilaian sikap dan perilaku Anda</li>
                        <li>Tunjukkan perubahan positif untuk meningkatkan citra Anda di sekolah</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Header */
.pelanggaran-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid #e74c3c;
}

/* Poin Display */
.poin-display {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 100px;
    border-radius: 15px;
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.poin-number {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
}

.poin-label {
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 5px;
}

/* Info Boxes */
.info-box {
    background: white;
    padding: 15px;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
}

.info-box label {
    display: block;
    font-size: 0.85rem;
    margin-bottom: 5px;
}

/* Content Box */
.content-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
}

/* Info Section */
.info-section h6 i {
    width: 22px;
    text-align: center;
}

/* Badges */
.badge {
    font-weight: 600;
    font-size: 0.85rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .pelanggaran-header {
        padding: 15px;
    }

    .poin-display {
        width: 80px;
        height: 80px;
    }

    .poin-number {
        font-size: 2rem;
    }

    .card-body {
        padding: 20px !important;
    }

    .info-box {
        padding: 12px;
        margin-bottom: 10px;
    }

    .content-box {
        padding: 15px;
    }

    .btn {
        width: 100%;
        margin-bottom: 10px;
    }

    .d-flex.gap-2 {
        flex-direction: column-reverse;
    }

    .btn {
        margin-bottom: 0;
    }
}
</style>
@endsection

