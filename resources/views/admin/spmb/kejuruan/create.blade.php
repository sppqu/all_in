@extends('layouts.coreui')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .form-label {
        font-weight: 600;
        color: #495057;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                        <i class="fas fa-plus me-2"></i>Tambah Kejuruan SPMB
                    </h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Tambah kejuruan baru untuk SPMB</p>
                </div>
                <a href="{{ route('manage.spmb.kejuruan.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="form-card">
                <div class="p-4">
                    <form method="POST" action="{{ route('manage.spmb.kejuruan.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Nama Kejuruan -->
                            <div class="col-md-8 mb-3">
                                <label for="nama_kejuruan" class="form-label">
                                    <i class="fas fa-graduation-cap me-1"></i>Nama Kejuruan *
                                </label>
                                <input type="text" 
                                       class="form-control @error('nama_kejuruan') is-invalid @enderror" 
                                       id="nama_kejuruan" 
                                       name="nama_kejuruan" 
                                       value="{{ old('nama_kejuruan') }}"
                                       placeholder="Contoh: Teknik Informatika"
                                       required>
                                @error('nama_kejuruan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kode Kejuruan -->
                            <div class="col-md-4 mb-3">
                                <label for="kode_kejuruan" class="form-label">
                                    <i class="fas fa-code me-1"></i>Kode Kejuruan *
                                </label>
                                <input type="text" 
                                       class="form-control @error('kode_kejuruan') is-invalid @enderror" 
                                       id="kode_kejuruan" 
                                       name="kode_kejuruan" 
                                       value="{{ old('kode_kejuruan') }}"
                                       placeholder="Contoh: TI"
                                       maxlength="10"
                                       required>
                                @error('kode_kejuruan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Deskripsi
                            </label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" 
                                      name="deskripsi" 
                                      rows="3" 
                                      placeholder="Deskripsi kejuruan...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Kuota -->
                            <div class="col-md-6 mb-3">
                                <label for="kuota" class="form-label">
                                    <i class="fas fa-users me-1"></i>Kuota
                                </label>
                                <input type="number" 
                                       class="form-control @error('kuota') is-invalid @enderror" 
                                       id="kuota" 
                                       name="kuota" 
                                       value="{{ old('kuota') }}"
                                       min="0" 
                                       placeholder="Kosongkan untuk tidak terbatas">
                                <small class="form-text text-muted">Kosongkan untuk kuota tidak terbatas</small>
                                @error('kuota')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status Aktif -->
                            <div class="col-md-6 mb-3">
                                <label for="aktif" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>Status
                                </label>
                                <select class="form-select @error('aktif') is-invalid @enderror" 
                                        id="aktif" 
                                        name="aktif">
                                    <option value="1" {{ old('aktif', '1') == '1' ? 'selected' : '' }}>
                                        Aktif
                                    </option>
                                    <option value="0" {{ old('aktif') == '0' ? 'selected' : '' }}>
                                        Tidak Aktif
                                    </option>
                                </select>
                                @error('aktif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('manage.spmb.kejuruan.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan Kejuruan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="form-card mt-4">
                <div class="p-4">
                    <h6 class="mb-3">
                        <i class="fas fa-info-circle me-2"></i>Informasi Kejuruan
                    </h6>
                    <div class="row">
                        <div class="col-md-12">
                            <small class="text-muted">
                                <strong>Catatan:</strong> 
                                <ul class="mb-0">
                                    <li>Kode kejuruan harus unik dan tidak boleh sama dengan kejuruan lain</li>
                                    <li>Kuota kosong berarti tidak terbatas</li>
                                    <li>Kejuruan yang tidak aktif tidak akan muncul di form pendaftaran</li>
                                    <li>Kejuruan yang sudah memiliki pendaftar tidak dapat dihapus</li>
                                </ul>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto generate kode kejuruan from nama kejuruan
    document.getElementById('nama_kejuruan').addEventListener('input', function(e) {
        const nama = e.target.value;
        const kode = nama.split(' ')
            .map(word => word.charAt(0).toUpperCase())
            .join('')
            .substring(0, 10);
        
        if (!document.getElementById('kode_kejuruan').value) {
            document.getElementById('kode_kejuruan').value = kode;
        }
    });

    // Auto format kode kejuruan
    document.getElementById('kode_kejuruan').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });

    // Kuota validation
    document.getElementById('kuota').addEventListener('input', function(e) {
        if (e.target.value < 0) {
            e.target.value = 0;
        }
    });
</script>
@endpush
