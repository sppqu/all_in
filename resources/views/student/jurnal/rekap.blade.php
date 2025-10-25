@extends('layouts.student')

@section('title', 'Rekap Jurnal')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('student.jurnal.index') }}" class="btn btn-sm btn-outline-secondary me-3" style="border-radius: 10px;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-1" style="color: #6f42c1;">📊 Rekap Jurnal Bulanan</h5>
            <p class="text-muted small mb-0">Pilih bulan untuk melihat grafik</p>
        </div>
    </div>

    <!-- List of Months -->
    @forelse($months as $month)
    <a href="{{ route('student.jurnal.rekap.bulanan', [$month->month, $month->year]) }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-calendar-alt text-white" style="font-size: 1.3rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold">{{ \Carbon\Carbon::create($month->year, $month->month)->format('F Y') }}</h6>
                        <small class="text-muted">{{ $month->count }} jurnal</small>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </div>
            </div>
        </div>
    </a>
    @empty
    <div class="text-center py-5">
        <i class="fas fa-chart-line text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
        <p class="text-muted mt-3 mb-0">Belum ada data rekap.</p>
        <p class="text-muted small">Mulai isi jurnal untuk melihat rekapitulasi!</p>
        <a href="{{ route('student.jurnal.create') }}" class="btn btn-primary mt-2" style="border-radius: 10px;">
            <i class="fas fa-plus me-1"></i>Isi Jurnal
        </a>
    </div>
    @endforelse
</div>
@endsection

