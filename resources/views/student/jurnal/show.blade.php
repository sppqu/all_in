@extends('layouts.student')

@section('title', 'Detail Jurnal')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('student.jurnal.index') }}" class="btn btn-sm btn-outline-secondary me-3" style="border-radius: 10px;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-grow-1">
            <h5 class="fw-bold mb-1" style="color: #6f42c1;">📖 Detail Jurnal</h5>
            <p class="text-muted small mb-0">{{ $jurnal->tanggal->format('d F Y') }}</p>
        </div>
        @if(in_array($jurnal->status, ['submitted', 'revised']))
        <a href="{{ route('student.jurnal.edit', $jurnal->jurnal_id) }}" class="btn btn-sm btn-primary" style="border-radius: 10px;">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        @endif
    </div>

    <!-- Status -->
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted d-block mb-1">Status Jurnal</small>
                    @if($jurnal->status == 'submitted')
                        <span class="badge bg-warning" style="padding: 8px 16px; border-radius: 10px;">
                            <i class="fas fa-clock me-1"></i>Menunggu Verifikasi
                        </span>
                    @elseif($jurnal->status == 'verified')
                        <span class="badge bg-success" style="padding: 8px 16px; border-radius: 10px;">
                            <i class="fas fa-check-circle me-1"></i>Terverifikasi
                        </span>
                    @elseif($jurnal->status == 'revised')
                        <span class="badge bg-danger" style="padding: 8px 16px; border-radius: 10px;">
                            <i class="fas fa-times-circle me-1"></i>Perlu Revisi
                        </span>
                    @elseif($jurnal->status == 'draft')
                        <span class="badge bg-secondary" style="padding: 8px 16px; border-radius: 10px;">
                            <i class="fas fa-file me-1"></i>Draft
                        </span>
                    @endif
                </div>
                <div class="text-end">
                    <small class="text-muted d-block mb-1">Rata-rata Nilai</small>
                    <h3 class="mb-0 fw-bold" style="color: #6f42c1;">{{ number_format($jurnal->entries->avg('nilai'), 1) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Foto (jika ada) -->
    @if($jurnal->foto)
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-camera me-2" style="color: #6f42c1;"></i>Foto Dokumentasi
            </h6>
            <img src="{{ asset('storage/' . $jurnal->foto) }}" alt="Foto Jurnal" class="img-fluid rounded" style="border-radius: 10px; max-height: 300px; width: 100%; object-fit: cover;">
        </div>
    </div>
    @endif

    <!-- Catatan Umum (jika ada) -->
    @if($jurnal->catatan_umum)
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-2">
                <i class="fas fa-sticky-note me-2" style="color: #6f42c1;"></i>Catatan Umum
            </h6>
            <p class="mb-0 text-muted">{{ $jurnal->catatan_umum }}</p>
        </div>
    </div>
    @endif

    <!-- Nilai Per Kategori -->
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-list-check me-2" style="color: #6f42c1;"></i>Nilai Per Kategori
            </h6>
            @foreach($jurnal->entries as $entry)
            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div class="d-flex align-items-center mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 40px; height: 40px; background: {{ $entry->kategori->warna }};">
                        <i class="{{ $entry->kategori->icon }} text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold">{{ $entry->kategori->nama_kategori }}</h6>
                    </div>
                    <div class="nilai-badge fw-bold text-white px-3 py-2" 
                         style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px;">
                        {{ $entry->nilai }}/10
                    </div>
                </div>
                @if($entry->catatan)
                <div class="ps-5">
                    <small class="text-muted">{{ $entry->catatan }}</small>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Feedback Guru (jika ada) -->
    @if($jurnal->feedback_guru)
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px; background: linear-gradient(135deg, #667eea15, #764ba215);">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-2" style="color: #6f42c1;">
                <i class="fas fa-comment-dots me-2"></i>Feedback Guru
            </h6>
            <p class="mb-0">{{ $jurnal->feedback_guru }}</p>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    @if(in_array($jurnal->status, ['submitted']))
    <div class="d-grid gap-2 mb-4">
        <form action="{{ route('student.jurnal.delete', $jurnal->jurnal_id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jurnal ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-lg w-100" style="border-radius: 12px;">
                <i class="fas fa-trash me-2"></i>Hapus Jurnal
            </button>
        </form>
    </div>
    @endif
</div>

<style>
    .card {
        transition: all 0.3s ease;
    }
</style>
@endsection

