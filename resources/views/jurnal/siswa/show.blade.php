@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark">
                <i class="fas fa-eye me-2 text-info"></i>Detail Jurnal Harian
            </h2>
            <p class="text-muted mb-0">{{ $jurnal->tanggal->format('d F Y, l') }}</p>
        </div>
        <div>
            <a href="{{ route('jurnal.siswa.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            @if($jurnal->status != 'verified')
            <a href="{{ route('jurnal.siswa.edit', $jurnal->jurnal_id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Info Jurnal -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Jurnal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Siswa</label>
                            <p class="mb-0 fw-bold">{{ $jurnal->siswa->student_full_name }}</p>
                            <small class="text-muted">{{ $jurnal->siswa->student_nis }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Kelas</label>
                            <p class="mb-0">
                                @if($jurnal->siswa->class)
                                    <span class="badge bg-secondary">{{ $jurnal->siswa->class->class_name }}</span>
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $jurnal->status_badge }} fs-6">
                                    {{ ucfirst($jurnal->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Total Nilai</label>
                            <p class="mb-0">
                                <strong class="fs-4">{{ $jurnal->total_nilai }}</strong>
                                <small class="text-muted">(Rata-rata: {{ number_format($jurnal->rata_rata_nilai, 1) }})</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kegiatan per Kategori -->
            @foreach($jurnal->entries as $entry)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header" style="background: {{ $entry->kategori->warna }}20; border-left: 4px solid {{ $entry->kategori->warna }};">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="color: {{ $entry->kategori->warna }};">
                            <i class="{{ $entry->kategori->icon }} me-2"></i>{{ $entry->kategori->nama_kategori }}
                        </h5>
                        <span class="badge bg-{{ $entry->nilai_badge }}">
                            {{ $entry->nilai }}/10 - {{ $entry->nilai_text }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-semibold small text-muted">Kegiatan:</label>
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $entry->kegiatan }}</p>
                    </div>

                    @if($entry->waktu_mulai || $entry->waktu_selesai)
                    <div class="mb-3">
                        <label class="fw-semibold small text-muted">Waktu:</label>
                        <p class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            @if($entry->waktu_mulai)
                                {{ date('H:i', strtotime($entry->waktu_mulai)) }}
                            @endif
                            @if($entry->waktu_mulai && $entry->waktu_selesai)
                                -
                            @endif
                            @if($entry->waktu_selesai)
                                {{ date('H:i', strtotime($entry->waktu_selesai)) }}
                            @endif
                        </p>
                    </div>
                    @endif

                    @if($entry->keterangan)
                    <div class="mb-3">
                        <label class="fw-semibold small text-muted">Keterangan:</label>
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $entry->keterangan }}</p>
                    </div>
                    @endif

                    @if($entry->foto)
                    <div class="mb-3">
                        <label class="fw-semibold small text-muted">Foto Dokumentasi:</label>
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $entry->foto) }}" 
                                 alt="Foto Kegiatan" 
                                 class="img-fluid rounded shadow-sm"
                                 style="max-width: 400px; cursor: pointer;"
                                 onclick="window.open(this.src, '_blank')">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            <!-- Refleksi -->
            @if($jurnal->catatan_umum || $jurnal->refleksi)
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Refleksi & Catatan
                    </h5>
                </div>
                <div class="card-body">
                    @if($jurnal->catatan_umum)
                    <div class="mb-3">
                        <label class="fw-semibold small text-muted">Catatan Umum:</label>
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $jurnal->catatan_umum }}</p>
                    </div>
                    @endif

                    @if($jurnal->refleksi)
                    <div>
                        <label class="fw-semibold small text-muted">Refleksi:</label>
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $jurnal->refleksi }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Catatan Guru -->
            @if($jurnal->catatan_guru)
            <div class="card border-0 shadow-sm mt-3" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body">
                    <h6 class="text-warning mb-2">
                        <i class="fas fa-user-tie me-2"></i>Catatan dari Guru
                    </h6>
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $jurnal->catatan_guru }}</p>
                    @if($jurnal->verified_by)
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-check-circle me-1"></i>
                        Diverifikasi oleh {{ $jurnal->verifiedBy->name ?? '-' }} 
                        pada {{ $jurnal->verified_at ? $jurnal->verified_at->format('d M Y H:i') : '-' }}
                    </small>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistics -->
            <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h5 class="text-white mb-3">
                        <i class="fas fa-chart-bar me-2"></i>Statistik Jurnal
                    </h5>
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="fs-3 fw-bold">{{ $jurnal->entries->count() }}</div>
                            <div class="small">Kegiatan</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="fs-3 fw-bold">{{ $jurnal->total_nilai }}</div>
                            <div class="small">Total Nilai</div>
                        </div>
                        <div class="col-12">
                            <div class="fs-3 fw-bold">{{ number_format($jurnal->rata_rata_nilai, 1) }}</div>
                            <div class="small">Rata-rata</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($jurnal->status != 'verified')
                        <a href="{{ route('jurnal.siswa.edit', $jurnal->jurnal_id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit Jurnal
                        </a>
                        @endif
                        
                        <a href="{{ route('jurnal.siswa.create', ['tanggal' => date('Y-m-d')]) }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Buat Jurnal Baru
                        </a>
                        
                        <a href="{{ route('jurnal.siswa.rekap-bulanan') }}" class="btn btn-info">
                            <i class="fas fa-chart-line me-2"></i>Lihat Rekap
                        </a>
                        
                        @if($jurnal->status == 'draft')
                        <form action="{{ route('jurnal.siswa.destroy', $jurnal->jurnal_id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Yakin ingin menghapus jurnal ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>Hapus Jurnal
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-3">
                            <i class="fas fa-plus-circle text-primary"></i>
                            <div>
                                <small class="text-muted">Dibuat</small>
                                <p class="mb-0 small">{{ $jurnal->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        @if($jurnal->updated_at != $jurnal->created_at)
                        <div class="timeline-item mb-3">
                            <i class="fas fa-edit text-warning"></i>
                            <div>
                                <small class="text-muted">Terakhir Diupdate</small>
                                <p class="mb-0 small">{{ $jurnal->updated_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        @if($jurnal->status == 'verified')
                        <div class="timeline-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <div>
                                <small class="text-muted">Diverifikasi</small>
                                <p class="mb-0 small">{{ $jurnal->verified_at ? $jurnal->verified_at->format('d M Y H:i') : '-' }}</p>
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
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}
.timeline-item {
    position: relative;
    display: flex;
    gap: 15px;
}
.timeline-item i {
    position: absolute;
    left: -23px;
    background: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}
</style>
@endsection

