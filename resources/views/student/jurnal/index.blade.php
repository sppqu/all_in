@extends('layouts.student')

@section('title', 'E-Jurnal Harian')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Header Section -->
    <div class="welcome-banner mb-4" style="background: linear-gradient(135deg, #6f42c1, #9c5de7); padding: 1.5rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(111, 66, 193, 0.2);">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h4 class="mb-1 fw-bold text-white">
                    <i class="fas fa-book me-2"></i>E-Jurnal Harian
                </h4>
                <p class="mb-0 text-white-50">Catat aktivitas harianmu dan pantau perkembangannya</p>
            </div>
            <div class="mt-2 mt-md-0">
                @if(!$todayJournal)
                <a href="{{ route('student.jurnal.create') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i>Isi Jurnal Hari Ini
                </a>
                @else
                <a href="{{ route('student.jurnal.show', $todayJournal->jurnal_id) }}" class="btn btn-light btn-sm">
                    <i class="fas fa-eye me-1"></i>Lihat Jurnal Hari Ini
                </a>
                @endif
            </div>
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
        <div class="col-6 col-md-4">
            <div class="stat-card" style="background: white; border-radius: 15px; padding: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem; transition: transform 0.3s ease;">
                <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; background: linear-gradient(135deg, #6f42c1, #9c5de7);">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info" style="flex: 1;">
                    <div class="stat-label" style="font-size: 0.75rem; color: #6c757d; margin-bottom: 0.25rem;">Jurnal Bulan Ini</div>
                    <div class="stat-value" style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">{{ $monthlyJournals }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card" style="background: white; border-radius: 15px; padding: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem; transition: transform 0.3s ease;">
                <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; background: linear-gradient(135deg, #f093fb, #f5576c);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info" style="flex: 1;">
                    <div class="stat-label" style="font-size: 0.75rem; color: #6c757d; margin-bottom: 0.25rem;">Menunggu Verifikasi</div>
                    <div class="stat-value" style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">{{ $pendingCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card" style="background: white; border-radius: 15px; padding: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem; transition: transform 0.3s ease;">
                <div class="stat-icon" style="width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; background: linear-gradient(135deg, #4facfe, #00f2fe);">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-info" style="flex: 1;">
                    <div class="stat-label" style="font-size: 0.75rem; color: #6c757d; margin-bottom: 0.25rem;">Total Jurnal</div>
                    <div class="stat-value" style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">{{ $journals->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold mb-1" style="color: #6f42c1;">
                        <i class="fas fa-bolt me-2"></i>Aksi Cepat
                    </h6>
                    <p class="text-muted small mb-0">Kelola jurnal harianmu dengan mudah</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('student.jurnal.create') }}" class="btn btn-sm btn-primary" style="border-radius: 10px; background: linear-gradient(135deg, #6f42c1, #9c5de7); border: none;">
                        <i class="fas fa-plus me-1"></i>Buat Jurnal Baru
                    </a>
                    <a href="{{ route('student.jurnal.rekap') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 10px; border-color: #6f42c1; color: #6f42c1;">
                        <i class="fas fa-chart-bar me-1"></i>Rekap Jurnal
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Journals -->
    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3" style="color: #6f42c1;">
                <i class="fas fa-history me-2"></i>Jurnal Terbaru
            </h6>
            
            @if($journals->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($journals as $journal)
                    <div class="list-group-item border-0 px-0 py-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: #e9ecef !important;">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px; background: linear-gradient(135deg, #6f42c1, #9c5de7);">
                                    <i class="fas fa-book text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($journal->tanggal)->format('d F Y') }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($journal->tanggal)->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div>
                                        @if($journal->status == 'submitted')
                                            <span class="badge bg-warning" style="padding: 6px 12px; border-radius: 8px;">
                                                <i class="fas fa-clock me-1"></i>Menunggu
                                            </span>
                                        @elseif($journal->status == 'verified')
                                            <span class="badge bg-success" style="padding: 6px 12px; border-radius: 8px;">
                                                <i class="fas fa-check-circle me-1"></i>Terverifikasi
                                            </span>
                                        @elseif($journal->status == 'revised')
                                            <span class="badge bg-danger" style="padding: 6px 12px; border-radius: 8px;">
                                                <i class="fas fa-times-circle me-1"></i>Perlu Revisi
                                            </span>
                                        @elseif($journal->status == 'draft')
                                            <span class="badge bg-secondary" style="padding: 6px 12px; border-radius: 8px;">
                                                <i class="fas fa-file me-1"></i>Draft
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($journal->catatan_umum)
                                <p class="text-muted small mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $journal->catatan_umum }}
                                </p>
                                @endif
                                
                                <div class="d-flex align-items-center gap-3">
                                    <small class="text-muted">
                                        <i class="fas fa-list-check me-1"></i>
                                        {{ $journal->entries->count() }} Kategori
                                    </small>
                                    @if($journal->foto)
                                    <small class="text-muted">
                                        <i class="fas fa-camera me-1"></i>Ada Foto
                                    </small>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0 ms-3">
                                <a href="{{ route('student.jurnal.show', $journal->jurnal_id) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   style="border-radius: 10px; border-color: #6f42c1; color: #6f42c1;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-book-open" style="font-size: 4rem; color: #e9ecef; margin-bottom: 1rem;"></i>
                    <h6 class="text-muted mb-2">Belum Ada Jurnal</h6>
                    <p class="text-muted small mb-3">Mulai catat aktivitas harianmu dengan membuat jurnal pertama</p>
                    <a href="{{ route('student.jurnal.create') }}" class="btn btn-primary" style="border-radius: 10px; background: linear-gradient(135deg, #6f42c1, #9c5de7); border: none;">
                        <i class="fas fa-plus me-1"></i>Buat Jurnal Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1) !important;
}

@media (max-width: 576px) {
    .stat-card {
        padding: 0.75rem !important;
    }
    
    .stat-icon {
        width: 40px !important;
        height: 40px !important;
        font-size: 1.2rem !important;
    }
    
    .stat-value {
        font-size: 1.2rem !important;
    }
}
</style>
@endsection

