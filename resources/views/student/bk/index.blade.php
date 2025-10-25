@extends('layouts.student')

@section('title', 'Bimbingan Konseling')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1" style="color: #e74c3c;">
                    <i class="fas fa-clipboard-list me-2"></i>Bimbingan Konseling
                </h5>
                <p class="text-muted small mb-0">Catatan Pelanggaran & Bimbingan Konseling</p>
            </div>
            <a href="{{ route('student.bk.create-bimbingan') }}" class="btn btn-sm btn-danger" style="border-radius: 10px;">
                <i class="fas fa-hand-holding-heart me-1"></i>Ajukan Bimbingan
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $totalPelanggaran }}</h3>
                    <p>Total Pelanggaran</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #d68910);">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $totalPoin }}</h3>
                    <p>Total Poin Pelanggaran</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                <div class="stat-icon">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $totalBimbingan }}</h3>
                    <p>Bimbingan Konseling</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pelanggaran by Kategori -->
    @if($pelanggaranByKategori->count() > 0)
    <div class="card shadow-sm mb-4" style="border-radius: 15px; border: none;">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3" style="color: #2c3e50;">
                <i class="fas fa-chart-pie me-2" style="color: #e74c3c;"></i>Pelanggaran Berdasarkan Kategori
            </h6>
            <div class="row g-3">
                @foreach($pelanggaranByKategori as $kategori => $data)
                <div class="col-md-4">
                    <div class="kategori-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge {{ $kategori == 'Ringan' ? 'bg-success' : ($kategori == 'Sedang' ? 'bg-warning' : 'bg-danger') }} mb-2">
                                    {{ $kategori }}
                                </span>
                                <h4 class="mb-0">{{ $data['count'] }} kali</h4>
                                <small class="text-muted">Total: {{ $data['poin'] }} poin</small>
                            </div>
                            <div class="kategori-icon {{ $kategori == 'Ringan' ? 'bg-success' : ($kategori == 'Sedang' ? 'bg-warning' : 'bg-danger') }}">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Tabs -->
    <ul class="nav nav-tabs nav-fill mb-4" id="bkTabs" role="tablist" style="border-bottom: 2px solid #e0e0e0;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pelanggaran-tab" data-bs-toggle="tab" data-bs-target="#pelanggaran" type="button" role="tab" style="font-weight: 600; color: #e74c3c;">
                <i class="fas fa-exclamation-triangle me-2"></i>Riwayat Pelanggaran
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="bimbingan-tab" data-bs-toggle="tab" data-bs-target="#bimbingan" type="button" role="tab" style="font-weight: 600; color: #3498db;">
                <i class="fas fa-hand-holding-heart me-2"></i>Riwayat Bimbingan
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="bkTabsContent">
        <!-- Pelanggaran Tab -->
        <div class="tab-pane fade show active" id="pelanggaran" role="tabpanel">
            @if($pelanggaran->count() > 0)
                @foreach($pelanggaran as $item)
                <div class="timeline-item mb-3">
                    <div class="timeline-marker {{ $item->kategori == 'Ringan' ? 'bg-success' : ($item->kategori == 'Sedang' ? 'bg-warning' : 'bg-danger') }}">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 fw-bold">{{ $item->nama_pelanggaran }}</h6>
                                <span class="badge {{ $item->kategori == 'Ringan' ? 'bg-success' : ($item->kategori == 'Sedang' ? 'bg-warning text-dark' : 'bg-danger') }} me-2">
                                    {{ $item->kategori }}
                                </span>
                                <span class="badge bg-secondary">{{ $item->poin }} Poin</span>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_pelanggaran)->format('d M Y') }}
                            </small>
                        </div>
                        @if($item->keterangan)
                        <p class="mb-2 text-muted small">{{ Str::limit($item->keterangan, 150) }}</p>
                        @endif
                        <a href="{{ route('student.bk.show-pelanggaran', $item->id) }}" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-eye me-1"></i>Lihat Detail
                        </a>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-check-circle mb-3" style="font-size: 4rem; color: #27ae60;"></i>
                        <h5>Tidak Ada Pelanggaran</h5>
                        <p class="text-muted">Pertahankan sikap dan perilaku yang baik! 👍</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Bimbingan Tab -->
        <div class="tab-pane fade" id="bimbingan" role="tabpanel">
            @if($bimbingan->count() > 0)
                @foreach($bimbingan as $item)
                <div class="bimbingan-card mb-3">
                    <div class="bimbingan-card-inner">
                        <div class="bimbingan-icon {{ $item->status == 'selesai' ? 'bg-success' : ($item->status == 'berlangsung' ? 'bg-info' : 'bg-warning') }}">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <div class="bimbingan-content">
                            <!-- Header: Jenis & Tanggal -->
                            <div class="bimbingan-header">
                                <div class="bimbingan-title-section">
                                    <h6 class="mb-2 fw-bold bimbingan-title">{{ ucfirst($item->jenis_bimbingan) }}</h6>
                                    <div class="bimbingan-badges">
                                        <span class="badge {{ $item->status == 'selesai' ? 'bg-success' : ($item->status == 'berlangsung' ? 'bg-info' : ($item->status == 'ditunda' ? 'bg-secondary' : 'bg-warning text-dark')) }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                        @if($item->sesi_ke > 1)
                                        <span class="badge bg-primary">Sesi ke-{{ $item->sesi_ke }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="bimbingan-date">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d M Y') }}
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Permasalahan -->
                            @if($item->permasalahan)
                            <div class="bimbingan-permasalahan">
                                <p class="mb-0 text-muted small permasalahan-text">{{ Str::limit($item->permasalahan, 150) }}</p>
                            </div>
                            @endif
                            
                            <!-- Guru BK -->
                            @if($item->nama_guru)
                            <div class="bimbingan-guru">
                                <i class="fas fa-user-tie me-1" style="color: #3498db;"></i>
                                <strong>Guru BK:</strong> <span class="guru-name">{{ $item->nama_guru }}</span>
                            </div>
                            @endif
                            
                            <!-- Hasil Bimbingan -->
                            @if($item->hasil)
                            <div class="alert alert-success alert-sm mt-2 mb-0 bimbingan-alert">
                                <strong><i class="fas fa-check-circle me-1"></i>Hasil Bimbingan:</strong><br>
                                <span class="hasil-text">{{ $item->hasil }}</span>
                            </div>
                            @endif
                            
                            <!-- Tindakan -->
                            @if($item->tindakan)
                            <div class="alert alert-warning alert-sm mt-2 mb-0 bimbingan-alert">
                                <strong><i class="fas fa-tasks me-1"></i>Tindakan:</strong><br>
                                <span class="tindakan-text">{{ $item->tindakan }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-hand-holding-heart mb-3" style="font-size: 4rem; color: #3498db;"></i>
                        <h5>Belum Ada Bimbingan</h5>
                        <p class="text-muted">Jika Anda memerlukan bimbingan, silakan ajukan permohonan.</p>
                        <a href="{{ route('student.bk.create-bimbingan') }}" class="btn btn-primary mt-3" style="border-radius: 10px;">
                            <i class="fas fa-hand-holding-heart me-2"></i>Ajukan Bimbingan
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Statistics Cards */
.stat-card {
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 15px;
    color: white;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

.stat-content p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

/* Kategori Card */
.kategori-card {
    background: white;
    padding: 15px;
    border-radius: 12px;
    border: 1px solid #e0e0e0;
    transition: all 0.3s ease;
}

.kategori-card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.kategori-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
    opacity: 0.2;
}

/* Timeline */
.timeline-item {
    display: flex;
    gap: 15px;
    position: relative;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 21px;
    top: 45px;
    width: 2px;
    height: calc(100% + 12px);
    background: linear-gradient(180deg, #e0e0e0 0%, transparent 100%);
}

.timeline-marker {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.timeline-content {
    flex-grow: 1;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid #e0e0e0;
}

/* Bimbingan Card */
.bimbingan-card {
    background: white;
    padding: 18px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid #e0e0e0;
    transition: all 0.3s ease;
    overflow: hidden;
    max-width: 100%;
    box-sizing: border-box;
}

.bimbingan-card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.bimbingan-card-inner {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    max-width: 100%;
    overflow: hidden;
}

.bimbingan-icon {
    width: 50px;
    height: 50px;
    min-width: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.bimbingan-content {
    flex: 1;
    min-width: 0; /* Important for text overflow */
    max-width: 100%;
    overflow: hidden;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.bimbingan-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.bimbingan-title-section {
    flex: 1;
    min-width: 0;
}

.bimbingan-title {
    font-size: 1rem;
    color: #2c3e50;
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    max-width: 100%;
    white-space: normal;
}

.bimbingan-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.bimbingan-badges .badge {
    font-size: 0.75rem;
    padding: 4px 10px;
    font-weight: 500;
}

.bimbingan-date {
    flex-shrink: 0;
}

.bimbingan-permasalahan {
    margin-bottom: 10px;
    max-width: 100%;
    overflow: hidden;
}

.permasalahan-text {
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-all; /* Force break long words */
    white-space: normal;
    max-width: 100%;
    display: block;
    overflow-wrap: anywhere; /* Modern browsers */
}

.bimbingan-guru {
    font-size: 0.875rem;
    margin-bottom: 8px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 100%;
    overflow: hidden;
}

.bimbingan-guru .guru-name {
    color: #2c3e50;
    word-break: break-all;
    overflow-wrap: anywhere;
    display: inline-block;
    max-width: 100%;
}

.bimbingan-alert {
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 100%;
    overflow: hidden;
}

.bimbingan-alert .hasil-text,
.bimbingan-alert .tindakan-text {
    display: block;
    margin-top: 5px;
    word-break: break-all;
    overflow-wrap: anywhere;
    white-space: normal;
    max-width: 100%;
}

.alert-sm {
    font-size: 0.85rem;
}

/* Empty State */
.empty-state {
    padding: 20px;
}

/* Tabs */
.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link.active {
    border-bottom-color: currentColor;
    background: none;
}

.nav-tabs .nav-link:hover {
    background: rgba(0, 0, 0, 0.02);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    .stat-card {
        padding: 15px;
        gap: 10px;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }

    .stat-content h3 {
        font-size: 1.5rem;
    }

    .timeline-item {
        gap: 10px;
    }

    .timeline-marker {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }

    .timeline-item:not(:last-child)::before {
        left: 17px;
    }

    .timeline-content {
        padding: 15px;
    }

    /* Bimbingan Card Mobile */
    .bimbingan-card {
        padding: 12px;
        max-width: 100%;
        overflow: hidden;
    }

    .bimbingan-card-inner {
        gap: 10px;
        max-width: 100%;
        overflow: hidden;
    }

    .bimbingan-icon {
        width: 40px;
        height: 40px;
        min-width: 40px;
        font-size: 1rem;
    }

    .bimbingan-content {
        max-width: calc(100% - 50px); /* Account for icon width + gap */
        overflow: hidden;
    }

    .bimbingan-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 10px;
        max-width: 100%;
        overflow: hidden;
    }

    .bimbingan-date {
        width: 100%;
        overflow: hidden;
    }

    .bimbingan-date small {
        display: inline-block;
        font-size: 0.75rem;
        word-break: break-word;
    }

    .bimbingan-title {
        font-size: 0.95rem;
        max-width: 100%;
        word-break: break-word;
    }

    .bimbingan-badges {
        max-width: 100%;
    }

    .bimbingan-badges .badge {
        font-size: 0.7rem;
        padding: 3px 8px;
    }

    .bimbingan-guru {
        font-size: 0.8rem;
        max-width: 100%;
        overflow: hidden;
    }

    .bimbingan-permasalahan {
        max-width: 100%;
        overflow: hidden;
    }

    .bimbingan-permasalahan p,
    .permasalahan-text {
        font-size: 0.8rem;
        word-break: break-all;
        overflow-wrap: anywhere;
        max-width: 100%;
    }

    .bimbingan-alert {
        padding: 8px !important;
        font-size: 0.8rem !important;
        max-width: 100%;
        overflow: hidden;
    }

    .page-header .btn {
        font-size: 0.85rem;
        padding: 8px 15px;
    }

    .page-header h5 {
        font-size: 1.1rem;
    }

    .kategori-card {
        padding: 12px;
    }
}

@media (max-width: 480px) {
    .stat-content h3 {
        font-size: 1.3rem;
    }

    .stat-content p {
        font-size: 0.8rem;
    }

    .bimbingan-card {
        padding: 10px;
        max-width: 100%;
        overflow: hidden;
    }

    .bimbingan-card-inner {
        gap: 8px;
        max-width: 100%;
    }

    .bimbingan-icon {
        width: 35px;
        height: 35px;
        min-width: 35px;
        font-size: 0.9rem;
    }

    .bimbingan-content {
        max-width: calc(100% - 43px); /* Account for icon width + gap */
        overflow: hidden;
    }

    .bimbingan-title {
        font-size: 0.9rem;
        margin-bottom: 8px !important;
        max-width: 100%;
        word-break: break-all;
    }

    .permasalahan-text {
        font-size: 0.75rem;
        word-break: break-all;
        overflow-wrap: anywhere;
    }

    .bimbingan-guru {
        font-size: 0.75rem;
    }

    .bimbingan-alert {
        font-size: 0.75rem !important;
        padding: 6px !important;
    }

    .page-header .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 10px;
    }

    .page-header .btn {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection

