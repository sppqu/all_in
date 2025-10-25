@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('jurnal.guru.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <h1 class="h3 mb-1 fw-bold" style="color: #6f42c1;">
                <i class="fas fa-book-open me-2"></i>Detail Jurnal Harian
            </h1>
            <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($jurnal->tanggal)->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>
        <div>
            @if($jurnal->status == 'verified')
                <span class="badge bg-success p-2 fs-6">
                    <i class="fas fa-check-circle me-1"></i>Terverifikasi
                </span>
            @elseif($jurnal->status == 'submitted')
                <span class="badge bg-warning p-2 fs-6">
                    <i class="fas fa-clock me-1"></i>Pending Verifikasi
                </span>
            @elseif($jurnal->status == 'revised')
                <span class="badge bg-danger p-2 fs-6">
                    <i class="fas fa-exclamation-circle me-1"></i>Perlu Revisi
                </span>
            @else
                <span class="badge bg-secondary p-2 fs-6">
                    <i class="fas fa-edit me-1"></i>Draft
                </span>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Student Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-user me-2"></i>Informasi Siswa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <span class="fw-bold fs-1">{{ substr($jurnal->siswa->student_full_name, 0, 1) }}</span>
                        </div>
                    </div>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-semibold">Nama:</td>
                            <td>{{ $jurnal->siswa->student_full_name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">NIS:</td>
                            <td>{{ $jurnal->siswa->student_nis }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Kelas:</td>
                            <td>
                                <span class="badge bg-info">{{ $jurnal->siswa->class->class_name ?? '-' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Tanggal:</td>
                            <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Waktu Pengisian:</td>
                            <td>{{ $jurnal->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>

                    @if($jurnal->catatan_umum)
                        <div class="mt-3">
                            <div class="alert alert-info mb-0">
                                <strong><i class="fas fa-sticky-note me-1"></i>Catatan Umum Siswa:</strong>
                                <p class="mb-0 mt-2">{{ $jurnal->catatan_umum }}</p>
                            </div>
                        </div>
                    @endif

                    @if($jurnal->foto)
                        <div class="mt-3">
                            <strong class="d-block mb-2"><i class="fas fa-image me-1"></i>Foto:</strong>
                            <img src="{{ asset('storage/' . $jurnal->foto) }}" alt="Foto Jurnal" class="img-fluid rounded">
                        </div>
                    @endif
                </div>
            </div>

            @if($jurnal->status == 'verified')
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-check-circle me-2"></i>Info Verifikasi
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="fw-semibold">Diverifikasi oleh:</td>
                                <td>{{ $jurnal->verifiedBy->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Waktu Verifikasi:</td>
                                <td>{{ $jurnal->verified_at ? \Carbon\Carbon::parse($jurnal->verified_at)->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Jurnal Entries -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-list-check me-2 text-success"></i>Kegiatan Harian 7KAIH
                    </h5>
                </div>
                <div class="card-body p-0">
                    @foreach($jurnal->entries as $entry)
                        <div class="border-bottom p-4">
                            <div class="d-flex align-items-start mb-3">
                                <div class="me-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px; background: {{ $entry->kategori->warna }}20;">
                                        <i class="{{ $entry->kategori->icon }} fs-4" style="color: {{ $entry->kategori->warna }};"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1">{{ $entry->kategori->nama_kategori }}</h6>
                                    <p class="text-muted small mb-2">{{ $entry->kategori->deskripsi }}</p>

                                    <!-- Display based on kategori kode -->
                                    @php
                                        $checklistData = $entry->checklist_data ? json_decode($entry->checklist_data, true) : null;
                                    @endphp

                                    @if($entry->kategori->kode == 'BANGUN' || $entry->kategori->kode == 'TIDUR')
                                        <!-- Jam & Keterangan -->
                                        <div class="mb-2">
                                            <strong><i class="fas fa-clock me-1"></i>Jam:</strong>
                                            <span class="badge bg-primary">{{ $entry->jam ?? '-' }}</span>
                                        </div>
                                        @if($entry->keterangan)
                                            <div>
                                                <strong><i class="fas fa-comment me-1"></i>Keterangan:</strong>
                                                <p class="mb-0 mt-1">{{ $entry->keterangan }}</p>
                                            </div>
                                        @endif

                                    @elseif($entry->kategori->kode == 'IBADAH')
                                        <!-- Sholat Checklist -->
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge {{ isset($checklistData['subuh']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['subuh']) ? 'fa-check' : 'fa-times' }} me-1"></i>Subuh
                                            </span>
                                            <span class="badge {{ isset($checklistData['dzuhur']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['dzuhur']) ? 'fa-check' : 'fa-times' }} me-1"></i>Dzuhur
                                            </span>
                                            <span class="badge {{ isset($checklistData['asar']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['asar']) ? 'fa-check' : 'fa-times' }} me-1"></i>Asar
                                            </span>
                                            <span class="badge {{ isset($checklistData['magrib']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['magrib']) ? 'fa-check' : 'fa-times' }} me-1"></i>Magrib
                                            </span>
                                            <span class="badge {{ isset($checklistData['isya']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['isya']) ? 'fa-check' : 'fa-times' }} me-1"></i>Isya
                                            </span>
                                        </div>

                                    @elseif($entry->kategori->kode == 'OLAHRAGA')
                                        <!-- Olahraga -->
                                        <div class="mb-2">
                                            <span class="badge {{ isset($checklistData['berolahraga']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['berolahraga']) ? 'fa-check' : 'fa-times' }} me-1"></i>
                                                {{ isset($checklistData['berolahraga']) ? 'Sudah Berolahraga' : 'Belum Berolahraga' }}
                                            </span>
                                        </div>
                                        @if($entry->jam)
                                            <div>
                                                <strong><i class="fas fa-clock me-1"></i>Jam:</strong>
                                                <span class="badge bg-primary">{{ $entry->jam }}</span>
                                            </div>
                                        @endif
                                        @if($entry->keterangan)
                                            <div class="mt-2">
                                                <strong><i class="fas fa-comment me-1"></i>Keterangan:</strong>
                                                <p class="mb-0 mt-1">{{ $entry->keterangan }}</p>
                                            </div>
                                        @endif

                                    @elseif($entry->kategori->kode == 'MAKAN')
                                        <!-- Makan -->
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="badge {{ isset($checklistData['pagi']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['pagi']) ? 'fa-check' : 'fa-times' }} me-1"></i>Pagi
                                            </span>
                                            <span class="badge {{ isset($checklistData['siang']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['siang']) ? 'fa-check' : 'fa-times' }} me-1"></i>Siang
                                            </span>
                                            <span class="badge {{ isset($checklistData['malam']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['malam']) ? 'fa-check' : 'fa-times' }} me-1"></i>Malam
                                            </span>
                                        </div>
                                        @if($entry->keterangan)
                                            <div>
                                                <strong><i class="fas fa-utensils me-1"></i>Menu:</strong>
                                                <p class="mb-0 mt-1">{{ $entry->keterangan }}</p>
                                            </div>
                                        @endif

                                    @elseif($entry->kategori->kode == 'MEMBACA')
                                        <!-- Membaca -->
                                        <div class="mb-2">
                                            <span class="badge {{ isset($checklistData['belajar']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['belajar']) ? 'fa-check' : 'fa-times' }} me-1"></i>
                                                {{ isset($checklistData['belajar']) ? 'Sudah Belajar' : 'Belum Belajar' }}
                                            </span>
                                        </div>
                                        @if($entry->keterangan)
                                            <div>
                                                <strong><i class="fas fa-book me-1"></i>Yang Dipelajari:</strong>
                                                <p class="mb-0 mt-1">{{ $entry->keterangan }}</p>
                                            </div>
                                        @endif

                                    @elseif($entry->kategori->kode == 'SOSIAL')
                                        <!-- Bermasyarakat -->
                                        @if($entry->keterangan)
                                            <div class="mb-2">
                                                <strong><i class="fas fa-comment me-1"></i>Kegiatan:</strong>
                                                <p class="mb-0 mt-1">{{ $entry->keterangan }}</p>
                                            </div>
                                        @endif
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge {{ isset($checklistData['keluarga']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['keluarga']) ? 'fa-check' : 'fa-times' }} me-1"></i>Keluarga
                                            </span>
                                            <span class="badge {{ isset($checklistData['teman']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['teman']) ? 'fa-check' : 'fa-times' }} me-1"></i>Teman
                                            </span>
                                            <span class="badge {{ isset($checklistData['tetangga']) ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ isset($checklistData['tetangga']) ? 'fa-check' : 'fa-times' }} me-1"></i>Tetangga
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Catatan Guru / Action -->
            @if($jurnal->catatan_guru)
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header {{ $jurnal->status == 'revised' ? 'bg-danger' : 'bg-success' }} text-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-comment-dots me-2"></i>Catatan Guru/Wali
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $jurnal->catatan_guru }}</p>
                    </div>
                </div>
            @endif

            @if(in_array($jurnal->status, ['submitted', 'revised']))
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-check-double me-2"></i>Verifikasi Jurnal
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('jurnal.guru.verify', $jurnal->jurnal_id) }}" onsubmit="return confirm('Apakah Anda yakin ingin memverifikasi jurnal ini?')">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Catatan (Opsional)</label>
                                        <textarea name="catatan_guru" class="form-control" rows="3" placeholder="Berikan catatan atau apresiasi untuk siswa..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check-circle me-1"></i>Verifikasi & Setujui
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('jurnal.guru.revision', $jurnal->jurnal_id) }}" onsubmit="return confirm('Apakah Anda yakin ingin meminta revisi?')">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Catatan Revisi <span class="text-danger">*</span></label>
                                        <textarea name="catatan_guru" class="form-control" rows="3" placeholder="Jelaskan apa yang perlu diperbaiki..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-exclamation-circle me-1"></i>Minta Revisi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .badge {
        font-weight: 600;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .gap-2 {
        gap: 0.5rem !important;
    }
</style>
@endsection

