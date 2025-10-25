@extends('layouts.student')

@section('title', 'Ajukan Bimbingan Konseling')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <h5 class="fw-bold mb-1" style="color: #3498db;">
            <i class="fas fa-hand-holding-heart me-2"></i>Ajukan Bimbingan Konseling
        </h5>
        <p class="text-muted small mb-0">Sampaikan permasalahan Anda kepada Guru BK</p>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info mb-4" style="border-radius: 12px; border-left: 4px solid #3498db;">
        <div class="d-flex align-items-start">
            <i class="fas fa-info-circle me-3" style="font-size: 1.5rem; margin-top: 3px;"></i>
            <div>
                <h6 class="fw-bold mb-1">Informasi Penting</h6>
                <ul class="mb-0 small">
                    <li>Semua informasi yang Anda berikan akan dijaga kerahasiaannya</li>
                    <li>Guru BK akan menghubungi Anda dalam waktu 1x24 jam</li>
                    <li>Silakan isi form dengan jujur dan lengkap</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="card shadow-sm" style="border-radius: 15px; border: none;">
        <div class="card-body p-4">
            <form action="{{ route('student.bk.store-bimbingan') }}" method="POST">
                @csrf
                
                <!-- Tanggal Pengajuan -->
                <div class="mb-4">
                    <label for="tanggal" class="form-label fw-semibold">
                        <i class="fas fa-calendar me-2" style="color: #3498db;"></i>Tanggal Pengajuan
                        <span class="text-danger">*</span>
                    </label>
                    <input type="date" 
                           class="form-control custom-input @error('tanggal') is-invalid @enderror" 
                           id="tanggal" 
                           name="tanggal" 
                           value="{{ old('tanggal', date('Y-m-d')) }}" 
                           required>
                    @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Jenis Bimbingan -->
                <div class="mb-4">
                    <label for="jenis_bimbingan" class="form-label fw-semibold">
                        <i class="fas fa-tag me-2" style="color: #3498db;"></i>Jenis Bimbingan
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select custom-input @error('jenis_bimbingan') is-invalid @enderror" 
                            id="jenis_bimbingan" 
                            name="jenis_bimbingan" 
                            required>
                        <option value="">-- Pilih Jenis Bimbingan --</option>
                        <option value="akademik" {{ old('jenis_bimbingan') == 'akademik' ? 'selected' : '' }}>Akademik (Belajar)</option>
                        <option value="pribadi" {{ old('jenis_bimbingan') == 'pribadi' ? 'selected' : '' }}>Pribadi</option>
                        <option value="sosial" {{ old('jenis_bimbingan') == 'sosial' ? 'selected' : '' }}>Sosial</option>
                        <option value="karir" {{ old('jenis_bimbingan') == 'karir' ? 'selected' : '' }}>Karir</option>
                    </select>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Akademik:</strong> Kesulitan belajar, nilai rendah<br>
                        <strong>Pribadi:</strong> Masalah emosional, kepercayaan diri<br>
                        <strong>Sosial:</strong> Hubungan dengan teman, keluarga<br>
                        <strong>Karir:</strong> Pemilihan jurusan, rencana masa depan
                    </small>
                    @error('jenis_bimbingan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Deskripsi Masalah -->
                <div class="mb-4">
                    <label for="deskripsi_masalah" class="form-label fw-semibold">
                        <i class="fas fa-file-alt me-2" style="color: #3498db;"></i>Deskripsi Masalah
                        <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control custom-input @error('deskripsi_masalah') is-invalid @enderror" 
                              id="deskripsi_masalah" 
                              name="deskripsi_masalah" 
                              rows="6" 
                              placeholder="Ceritakan permasalahan yang Anda hadapi dengan detail..."
                              required>{{ old('deskripsi_masalah') }}</textarea>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle me-1"></i>Jelaskan masalah yang Anda hadapi sejelas mungkin (minimal 50 karakter)
                    </small>
                    @error('deskripsi_masalah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Harapan -->
                <div class="mb-4">
                    <label for="harapan" class="form-label fw-semibold">
                        <i class="fas fa-lightbulb me-2" style="color: #3498db;"></i>Harapan dari Bimbingan
                        <span class="text-muted small">(Opsional)</span>
                    </label>
                    <textarea class="form-control custom-input @error('harapan') is-invalid @enderror" 
                              id="harapan" 
                              name="harapan" 
                              rows="4" 
                              placeholder="Apa yang Anda harapkan dari bimbingan ini?">{{ old('harapan') }}</textarea>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle me-1"></i>Ceritakan apa yang Anda harapkan dapat dicapai dari bimbingan ini
                    </small>
                    @error('harapan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Privacy Notice -->
                <div class="privacy-notice mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-shield-alt me-3" style="color: #27ae60; font-size: 1.5rem; margin-top: 3px;"></i>
                        <div>
                            <h6 class="fw-bold mb-1" style="color: #27ae60;">Privasi Terjaga</h6>
                            <p class="small mb-0 text-muted">
                                Semua informasi yang Anda sampaikan akan dijaga kerahasiaannya dan hanya akan diketahui oleh Guru BK.
                                Data Anda akan ditangani sesuai dengan kode etik konseling.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('student.bk.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">
                        <i class="fas fa-times me-1"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #3498db, #2980b9); border: none; border-radius: 10px; min-width: 150px;">
                        <i class="fas fa-paper-plane me-2"></i>Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Custom Input */
.custom-input {
    border-radius: 10px;
    border: 2px solid #e0e0e0;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.custom-input:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.15);
}

/* Privacy Notice */
.privacy-notice {
    background: linear-gradient(135deg, #d5f4e6, #d5f4e6);
    padding: 15px;
    border-radius: 10px;
    border-left: 4px solid #27ae60;
}

/* Form Label Icons */
.form-label i {
    width: 20px;
    text-align: center;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .card-body {
        padding: 20px !important;
    }

    .btn {
        width: 100%;
        margin-bottom: 10px;
    }

    .d-flex.gap-2 {
        flex-direction: column-reverse;
    }

    .btn {
        margin-bottom: 0;
    }
}
</style>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const deskripsi = document.getElementById('deskripsi_masalah');
    
    form.addEventListener('submit', function(e) {
        if (deskripsi.value.trim().length < 50) {
            e.preventDefault();
            alert('Deskripsi masalah minimal 50 karakter. Silakan jelaskan masalah Anda dengan lebih detail.');
            deskripsi.focus();
        }
    });

    // Character counter
    deskripsi.addEventListener('input', function() {
        const length = this.value.trim().length;
        const minLength = 50;
        if (length < minLength) {
            this.style.borderColor = '#e74c3c';
        } else {
            this.style.borderColor = '#27ae60';
        }
    });
});
</script>
@endsection

