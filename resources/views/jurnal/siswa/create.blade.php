@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="mb-1 fw-bold text-dark">
            <i class="fas fa-pen-fancy me-2 text-primary"></i>Isi Jurnal Harian
        </h2>
        <p class="text-muted mb-0">7 Kebiasaan Anak Indonesia Hebat - {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</p>
    </div>

    <form action="{{ route('jurnal.siswa.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Kategori Cards -->
                @foreach($kategori as $kat)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header" style="background: {{ $kat->warna }}20; border-left: 4px solid {{ $kat->warna }};">
                        <h5 class="mb-0" style="color: {{ $kat->warna }};">
                            <i class="{{ $kat->icon }} me-2"></i>{{ $kat->nama_kategori }}
                        </h5>
                        <small class="text-muted">{{ $kat->deskripsi }}</small>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Kegiatan -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi Kegiatan</label>
                                <textarea name="entries[{{ $kat->kategori_id }}][kegiatan]" 
                                          rows="3" 
                                          class="form-control @error('entries.'.$kat->kategori_id.'.kegiatan') is-invalid @enderror"
                                          placeholder="Ceritakan kegiatan {{ strtolower($kat->nama_kategori) }} yang kamu lakukan hari ini...">{{ old('entries.'.$kat->kategori_id.'.kegiatan') }}</textarea>
                                @error('entries.'.$kat->kategori_id.'.kegiatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nilai -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Penilaian Diri (1-10)</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="range" 
                                           name="entries[{{ $kat->kategori_id }}][nilai]" 
                                           class="form-range" 
                                           min="1" 
                                           max="10" 
                                           value="{{ old('entries.'.$kat->kategori_id.'.nilai', 5) }}"
                                           oninput="document.getElementById('nilai_{{ $kat->kategori_id }}').innerText = this.value">
                                    <span id="nilai_{{ $kat->kategori_id }}" 
                                          class="badge bg-primary fs-5" 
                                          style="min-width: 40px;">{{ old('entries.'.$kat->kategori_id.'.nilai', 5) }}</span>
                                </div>
                                <small class="text-muted">
                                    1 = Kurang, 5 = Cukup, 10 = Sangat Baik
                                </small>
                            </div>

                            <!-- Waktu -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Waktu Mulai</label>
                                <input type="time" 
                                       name="entries[{{ $kat->kategori_id }}][waktu_mulai]" 
                                       class="form-control"
                                       value="{{ old('entries.'.$kat->kategori_id.'.waktu_mulai') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Waktu Selesai</label>
                                <input type="time" 
                                       name="entries[{{ $kat->kategori_id }}][waktu_selesai]" 
                                       class="form-control"
                                       value="{{ old('entries.'.$kat->kategori_id.'.waktu_selesai') }}">
                            </div>

                            <!-- Keterangan Tambahan -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Keterangan Tambahan (Opsional)</label>
                                <textarea name="entries[{{ $kat->kategori_id }}][keterangan]" 
                                          rows="2" 
                                          class="form-control"
                                          placeholder="Catatan tambahan...">{{ old('entries.'.$kat->kategori_id.'.keterangan') }}</textarea>
                            </div>

                            <!-- Upload Foto -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Foto Dokumentasi (Opsional)</label>
                                <input type="file" 
                                       name="entries[{{ $kat->kategori_id }}][foto]" 
                                       class="form-control" 
                                       accept="image/*">
                                <small class="text-muted">Format: JPG, PNG. Max: 2MB</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Refleksi Umum -->
                <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb me-2 text-warning"></i>Refleksi Hari Ini
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan Umum</label>
                            <textarea name="catatan_umum" 
                                      rows="3" 
                                      class="form-control"
                                      placeholder="Apa yang spesial hari ini?">{{ old('catatan_umum') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Refleksi & Pembelajaran</label>
                            <textarea name="refleksi" 
                                      rows="3" 
                                      class="form-control"
                                      placeholder="Apa yang kamu pelajari hari ini? Apa yang bisa diperbaiki besok?">{{ old('refleksi') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Info -->
                <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body text-white">
                        <h5 class="text-white mb-3">
                            <i class="fas fa-info-circle me-2"></i>Panduan Pengisian
                        </h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle me-2"></i>
                                Isi minimal 3 kategori
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle me-2"></i>
                                Ceritakan dengan jujur
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle me-2"></i>
                                Nilai diri sendiri objektif
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle me-2"></i>
                                Foto sebagai bukti (opsional)
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Skala Nilai -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-star me-2"></i>Skala Penilaian</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2 d-flex align-items-center">
                            <span class="badge bg-success me-2" style="min-width: 30px;">9-10</span>
                            <span class="small">Sangat Baik</span>
                        </div>
                        <div class="mb-2 d-flex align-items-center">
                            <span class="badge bg-info me-2" style="min-width: 30px;">7-8</span>
                            <span class="small">Baik</span>
                        </div>
                        <div class="mb-2 d-flex align-items-center">
                            <span class="badge bg-warning me-2" style="min-width: 30px;">5-6</span>
                            <span class="small">Cukup</span>
                        </div>
                        <div class="mb-2 d-flex align-items-center">
                            <span class="badge bg-danger me-2" style="min-width: 30px;">1-4</span>
                            <span class="small">Perlu Perbaikan</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" name="status" value="submitted" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Jurnal
                            </button>
                            <button type="submit" name="status" value="draft" class="btn btn-secondary">
                                <i class="fas fa-save me-2"></i>Simpan sebagai Draft
                            </button>
                            <a href="{{ route('jurnal.siswa.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.form-range::-webkit-slider-thumb {
    background: #0d6efd;
}
.form-range::-moz-range-thumb {
    background: #0d6efd;
}
.card {
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}
</style>
@endsection

