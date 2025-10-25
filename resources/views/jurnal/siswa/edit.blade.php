@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="mb-1 fw-bold text-dark">
            <i class="fas fa-edit me-2 text-warning"></i>Edit Jurnal Harian
        </h2>
        <p class="text-muted mb-0">{{ $jurnal->tanggal->format('d F Y') }}</p>
    </div>

    <form action="{{ route('jurnal.siswa.update', $jurnal->jurnal_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="tanggal" value="{{ $jurnal->tanggal->format('Y-m-d') }}">

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Kategori Cards -->
                @foreach($kategori as $kat)
                @php
                    $entry = $jurnal->entries->where('kategori_id', $kat->kategori_id)->first();
                @endphp
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
                                          placeholder="Ceritakan kegiatan {{ strtolower($kat->nama_kategori) }} yang kamu lakukan hari ini...">{{ old('entries.'.$kat->kategori_id.'.kegiatan', $entry ? $entry->kegiatan : '') }}</textarea>
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
                                           value="{{ old('entries.'.$kat->kategori_id.'.nilai', $entry ? $entry->nilai : 5) }}"
                                           oninput="document.getElementById('nilai_{{ $kat->kategori_id }}').innerText = this.value">
                                    <span id="nilai_{{ $kat->kategori_id }}" 
                                          class="badge bg-primary fs-5" 
                                          style="min-width: 40px;">{{ old('entries.'.$kat->kategori_id.'.nilai', $entry ? $entry->nilai : 5) }}</span>
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
                                       value="{{ old('entries.'.$kat->kategori_id.'.waktu_mulai', $entry && $entry->waktu_mulai ? date('H:i', strtotime($entry->waktu_mulai)) : '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Waktu Selesai</label>
                                <input type="time" 
                                       name="entries[{{ $kat->kategori_id }}][waktu_selesai]" 
                                       class="form-control"
                                       value="{{ old('entries.'.$kat->kategori_id.'.waktu_selesai', $entry && $entry->waktu_selesai ? date('H:i', strtotime($entry->waktu_selesai)) : '') }}">
                            </div>

                            <!-- Keterangan Tambahan -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Keterangan Tambahan (Opsional)</label>
                                <textarea name="entries[{{ $kat->kategori_id }}][keterangan]" 
                                          rows="2" 
                                          class="form-control"
                                          placeholder="Catatan tambahan...">{{ old('entries.'.$kat->kategori_id.'.keterangan', $entry ? $entry->keterangan : '') }}</textarea>
                            </div>

                            <!-- Upload Foto -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Foto Dokumentasi (Opsional)</label>
                                @if($entry && $entry->foto)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $entry->foto) }}" 
                                         alt="Foto Lama" 
                                         class="img-thumbnail"
                                         style="max-width: 200px;">
                                    <p class="small text-muted mb-0">Foto saat ini (upload baru untuk mengganti)</p>
                                </div>
                                @endif
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
                                      placeholder="Apa yang spesial hari ini?">{{ old('catatan_umum', $jurnal->catatan_umum) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Refleksi & Pembelajaran</label>
                            <textarea name="refleksi" 
                                      rows="3" 
                                      class="form-control"
                                      placeholder="Apa yang kamu pelajari hari ini? Apa yang bisa diperbaiki besok?">{{ old('refleksi', $jurnal->refleksi) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Info -->
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Status Saat Ini:</strong> {{ ucfirst($jurnal->status) }}
                </div>

                <!-- Action Buttons -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" name="status" value="submitted" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Simpan & Kirim
                            </button>
                            <button type="submit" name="status" value="draft" class="btn btn-secondary">
                                <i class="fas fa-save me-2"></i>Simpan sebagai Draft
                            </button>
                            <a href="{{ route('jurnal.siswa.show', $jurnal->jurnal_id) }}" class="btn btn-outline-secondary">
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
</style>
@endsection

