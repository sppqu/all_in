@extends('layouts.student')

@section('title', 'Edit Jurnal')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('student.jurnal.show', $jurnal->jurnal_id) }}" class="btn btn-sm btn-outline-secondary me-3" style="border-radius: 10px;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-1" style="color: #6f42c1;">✏️ Edit Jurnal</h5>
            <p class="text-muted small mb-0">{{ $jurnal->tanggal->format('d F Y') }}</p>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('student.jurnal.update', $jurnal->jurnal_id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- 7 Kategori KAIH -->
        @foreach($categories as $category)
        @php
            $entry = $jurnal->entries->firstWhere('kategori_id', $category->kategori_id);
            $nilai = $entry ? $entry->nilai : 5;
            $catatan = $entry ? $entry->catatan : '';
        @endphp
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: {{ $category->warna }};">
                        <i class="{{ $category->icon }} text-white" style="font-size: 1.3rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">{{ $category->nama_kategori }}</h6>
                        <small class="text-muted">{{ $category->deskripsi }}</small>
                    </div>
                </div>

                <!-- Slider Nilai -->
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nilai (1-10)</label>
                    <div class="d-flex align-items-center">
                        <input type="range" 
                               class="form-range flex-grow-1 me-3 nilai-slider" 
                               name="kategori[{{ $category->kategori_id }}][nilai]" 
                               min="1" 
                               max="10" 
                               value="{{ $nilai }}" 
                               data-target="nilai-{{ $category->kategori_id }}"
                               required>
                        <div class="nilai-display fw-bold rounded px-3 py-2 text-white" 
                             id="nilai-{{ $category->kategori_id }}" 
                             style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-width: 50px; text-align: center; border-radius: 10px;">
                            {{ $nilai }}
                        </div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mt-1">
                        <span>😞 Kurang</span>
                        <span>😐 Cukup</span>
                        <span>😊 Baik</span>
                        <span>🤩 Sangat Baik</span>
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="form-label small fw-bold">Catatan (opsional)</label>
                    <textarea name="kategori[{{ $category->kategori_id }}][catatan]" 
                              class="form-control" 
                              rows="2" 
                              placeholder="Ceritakan kegiatan Anda hari ini..."
                              style="border-radius: 10px; font-size: 0.9rem;">{{ $catatan }}</textarea>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Catatan Umum -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
            <div class="card-body p-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-sticky-note me-2" style="color: #6f42c1;"></i>Catatan Umum (Opsional)
                </label>
                <textarea name="catatan_umum" 
                          class="form-control" 
                          rows="3" 
                          placeholder="Tulis catatan umum atau refleksi harian Anda..."
                          style="border-radius: 10px;">{{ $jurnal->catatan_umum }}</textarea>
            </div>
        </div>

        <!-- Upload Foto -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
            <div class="card-body p-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-camera me-2" style="color: #6f42c1;"></i>Foto Dokumentasi (Opsional)
                </label>
                @if($jurnal->foto)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $jurnal->foto) }}" alt="Foto Jurnal" class="img-fluid rounded" style="max-height: 150px; border-radius: 10px;">
                    <small class="text-muted d-block mt-1">Foto saat ini (upload foto baru untuk menggantinya)</small>
                </div>
                @endif
                <input type="file" 
                       name="foto" 
                       class="form-control" 
                       accept="image/*" 
                       id="fotoInput"
                       style="border-radius: 10px;">
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>Maksimal 2MB, format: JPG, PNG
                </small>
                <div id="preview" class="mt-3" style="display: none;">
                    <img id="previewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px; border-radius: 10px;">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg fw-bold" style="background: linear-gradient(135deg, #6f42c1, #9d5bd2); border: none; border-radius: 12px; box-shadow: 0 6px 20px rgba(111, 66, 193, 0.3);">
                <i class="fas fa-save me-2"></i>Update Jurnal
            </button>
            <a href="{{ route('student.jurnal.show', $jurnal->jurnal_id) }}" class="btn btn-outline-secondary btn-lg" style="border-radius: 12px;">
                <i class="fas fa-times me-2"></i>Batal
            </a>
        </div>
    </form>
</div>

<style>
    .form-range::-webkit-slider-thumb {
        background: linear-gradient(135deg, #6f42c1, #9d5bd2);
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(111, 66, 193, 0.4);
        width: 24px;
        height: 24px;
    }
    
    .form-range::-webkit-slider-track {
        background: linear-gradient(to right, #dc3545 0%, #ffc107 30%, #28a745 70%, #007bff 100%);
        height: 8px;
        border-radius: 10px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update nilai display
    const sliders = document.querySelectorAll('.nilai-slider');
    sliders.forEach(slider => {
        slider.addEventListener('input', function() {
            const targetId = this.getAttribute('data-target');
            const display = document.getElementById(targetId);
            const value = this.value;
            display.textContent = value;
            
            if (value <= 3) {
                display.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
            } else if (value <= 6) {
                display.style.background = 'linear-gradient(135deg, #ffc107, #e0a800)';
            } else if (value <= 8) {
                display.style.background = 'linear-gradient(135deg, #28a745, #218838)';
            } else {
                display.style.background = 'linear-gradient(135deg, #007bff, #0056b3)';
            }
        });
        
        // Trigger initial color
        slider.dispatchEvent(new Event('input'));
    });
    
    // Preview foto
    const fotoInput = document.getElementById('fotoInput');
    const preview = document.getElementById('preview');
    const previewImg = document.getElementById('previewImg');
    
    fotoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection

