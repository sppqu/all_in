@extends('layouts.student')

@section('title', 'E-Jurnal Harian 7KAIH')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1" style="color: #6f42c1;">📖 E-Jurnal Harian 7KAIH</h5>
            <p class="text-muted small mb-0">7 Kebiasaan Anak Indonesia Hebat</p>
        </div>
        @if(!$todayJournal)
        <a href="{{ route('student.jurnal.create') }}" class="btn btn-sm btn-primary" style="background: linear-gradient(135deg, #6f42c1, #9d5bd2); border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(111, 66, 193, 0.3);">
            <i class="fas fa-plus me-1"></i>Isi Jurnal Hari Ini
        </a>
        @else
        <span class="badge bg-success" style="padding: 10px 15px; border-radius: 10px;">
            <i class="fas fa-check-circle me-1"></i>Sudah Mengisi Hari Ini
        </span>
        @endif
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Jurnal Bulan Ini -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px;">
                <div class="card-body text-white text-center p-3">
                    <i class="fas fa-calendar-check mb-2" style="font-size: 2rem; opacity: 0.9;"></i>
                    <h2 class="fw-bold mb-0">{{ $monthlyJournals }}</h2>
                    <small style="opacity: 0.9;">Jurnal Bulan Ini</small>
                </div>
            </div>
        </div>

        <!-- Rata-rata Nilai -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 15px;">
                <div class="card-body text-white text-center p-3">
                    <i class="fas fa-star mb-2" style="font-size: 2rem; opacity: 0.9;"></i>
                    <h2 class="fw-bold mb-0">{{ number_format($monthlyAvg ?? 0, 1) }}</h2>
                    <small style="opacity: 0.9;">Rata-rata Nilai</small>
                </div>
            </div>
        </div>

        <!-- Menunggu Verifikasi -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 15px;">
                <div class="card-body text-white text-center p-3">
                    <i class="fas fa-clock mb-2" style="font-size: 2rem; opacity: 0.9;"></i>
                    <h2 class="fw-bold mb-0">{{ $pendingCount }}</h2>
                    <small style="opacity: 0.9;">Menunggu Verifikasi</small>
                </div>
            </div>
        </div>

        <!-- Rekap -->
        <div class="col-6 col-md-3">
            <a href="{{ route('student.jurnal.rekap') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 15px;">
                    <div class="card-body text-white text-center p-3">
                        <i class="fas fa-chart-line mb-2" style="font-size: 2rem; opacity: 0.9;"></i>
                        <h6 class="fw-bold mb-0">Lihat Rekap</h6>
                        <small style="opacity: 0.9;">& Grafik</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- 7 Kategori KAIH -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3" style="color: #6f42c1;">
                <i class="fas fa-lightbulb me-2"></i>7 Kebiasaan Anak Indonesia Hebat
            </h6>
            <div class="row g-2">
                <div class="col-6 col-md-3">
                    <div class="text-center p-2 rounded" style="background: #f8f9fa;">
                        <i class="fas fa-book-open mb-1" style="color: #6f42c1; font-size: 1.5rem;"></i>
                        <div class="small fw-bold">Belajar</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-2 rounded" style="background: #f8f9fa;">
                        <i class="fas fa-pray mb-1" style="color: #28a745; font-size: 1.5rem;"></i>
                        <div class="small fw-bold">Ibadah</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-2 rounded" style="background: #f8f9fa;">
                        <i class="fas fa-clock mb-1" style="color: #17a2b8; font-size: 1.5rem;"></i>
                        <div class="small fw-bold">Disiplin</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-2 rounded" style="background: #f8f9fa;">
                        <i class="fas fa-broom mb-1" style="color: #ffc107; font-size: 1.5rem;"></i>
                        <div class="small fw-bold">Kebersihan</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-2 rounded" style="background: #f8f9fa;">
                        <i class="fas fa-handshake mb-1" style="color: #dc3545; font-size: 1.5rem;"></i>
                        <div class="small fw-bold">Kejujuran</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-2 rounded" style="background: #f8f9fa;">
                        <i class="fas fa-users mb-1" style="color: #fd7e14; font-size: 1.5rem;"></i>
                        <div class="small fw-bold">Kerja Sama</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-2 rounded" style="background: #f8f9fa;">
                        <i class="fas fa-tasks mb-1" style="color: #6610f2; font-size: 1.5rem;"></i>
                        <div class="small fw-bold">Tanggung Jawab</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jurnal Terbaru -->
    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-header bg-white border-0 pt-3 pb-2" style="border-radius: 15px 15px 0 0;">
            <h6 class="fw-bold mb-0" style="color: #6f42c1;">
                <i class="fas fa-history me-2"></i>Jurnal Terbaru (7 Hari Terakhir)
            </h6>
        </div>
        <div class="card-body p-0">
            @forelse($journals as $journal)
            <div class="d-flex align-items-center p-3 border-bottom {{ !$loop->last ? '' : 'border-0' }}">
                <div class="flex-shrink-0 me-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-calendar-day text-white"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold">{{ $journal->tanggal->format('d M Y') }}</h6>
                            <small class="text-muted">
                                <i class="fas fa-star text-warning me-1"></i>
                                Rata-rata: {{ number_format($journal->entries->avg('nilai'), 1) }}/10
                            </small>
                        </div>
                        <div class="text-end">
                            @if($journal->status == 'submitted')
                                <span class="badge bg-warning">Menunggu</span>
                            @elseif($journal->status == 'verified')
                                <span class="badge bg-success">Terverifikasi</span>
                            @elseif($journal->status == 'revised')
                                <span class="badge bg-danger">Perlu Revisi</span>
                            @elseif($journal->status == 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                            <br>
                            <a href="{{ route('student.jurnal.show', $journal->jurnal_id) }}" class="btn btn-sm btn-outline-primary mt-1" style="border-radius: 8px;">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-book text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="text-muted mt-3 mb-0">Belum ada jurnal.</p>
                <p class="text-muted small">Mulai isi jurnal harian Anda hari ini!</p>
                <a href="{{ route('student.jurnal.create') }}" class="btn btn-primary btn-sm mt-2" style="border-radius: 10px;">
                    <i class="fas fa-plus me-1"></i>Isi Jurnal Pertama
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12) !important;
    }
    
    .badge {
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
    }
    
    .btn-sm {
        font-size: 0.75rem;
        padding: 4px 10px;
    }
</style>
@endsection

