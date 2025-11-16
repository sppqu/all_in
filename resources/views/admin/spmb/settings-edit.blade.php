@extends('layouts.adminty')

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
                        <i class="fas fa-edit me-2"></i>Edit Pengaturan SPMB
                    </h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Edit pengaturan SPMB</p>
                </div>
                <a href="{{ route('manage.spmb.settings') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="form-card">
                <div class="p-4">
                    <form method="POST" action="{{ route('manage.spmb.settings.update', $settings->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Tahun Pelajaran -->
                            <div class="col-md-6 mb-3">
                                <label for="tahun_pelajaran" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Tahun Pelajaran
                                </label>
                                <input type="text" 
                                       class="form-control @error('tahun_pelajaran') is-invalid @enderror" 
                                       id="tahun_pelajaran" 
                                       name="tahun_pelajaran" 
                                       value="{{ old('tahun_pelajaran', $settings->tahun_pelajaran) }}"
                                       placeholder="Contoh: 2024/2025">
                                @error('tahun_pelajaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status Pendaftaran -->
                            <div class="col-md-6 mb-3">
                                <label for="pendaftaran_dibuka" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>Status Pendaftaran
                                </label>
                                <select class="form-control select-primary @error('pendaftaran_dibuka') is-invalid @enderror" 
                                        id="pendaftaran_dibuka" 
                                        name="pendaftaran_dibuka">
                                    <option value="1" {{ old('pendaftaran_dibuka', $settings->pendaftaran_dibuka) ? 'selected' : '' }}>
                                        Dibuka
                                    </option>
                                    <option value="0" {{ !old('pendaftaran_dibuka', $settings->pendaftaran_dibuka) ? 'selected' : '' }}>
                                        Ditutup
                                    </option>
                                </select>
                                @error('pendaftaran_dibuka')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tanggal Buka -->
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_buka" class="form-label">
                                    <i class="fas fa-calendar-plus me-1"></i>Tanggal Buka
                                </label>
                                <input type="date" 
                                       class="form-control @error('tanggal_buka') is-invalid @enderror" 
                                       id="tanggal_buka" 
                                       name="tanggal_buka" 
                                       value="{{ old('tanggal_buka', $settings->tanggal_buka ? $settings->tanggal_buka->format('Y-m-d') : '') }}">
                                @error('tanggal_buka')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tanggal Tutup -->
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_tutup" class="form-label">
                                    <i class="fas fa-calendar-minus me-1"></i>Tanggal Tutup
                                </label>
                                <input type="date" 
                                       class="form-control @error('tanggal_tutup') is-invalid @enderror" 
                                       id="tanggal_tutup" 
                                       name="tanggal_tutup" 
                                       value="{{ old('tanggal_tutup', $settings->tanggal_tutup ? $settings->tanggal_tutup->format('Y-m-d') : '') }}">
                                @error('tanggal_tutup')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Biaya Pendaftaran -->
                            <div class="col-md-6 mb-3">
                                <label for="biaya_pendaftaran" class="form-label">
                                    <i class="fas fa-money-bill me-1"></i>Biaya Pendaftaran
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #01a9ac; color: #ffffff; border-color: #01a9ac; border-radius: 4px 0 0 4px; min-width: 80px; padding: 0.375rem 1.5rem; text-align: center; font-weight: 500;">Rp</span>
                                    <input type="number" 
                                           class="form-control @error('biaya_pendaftaran') is-invalid @enderror" 
                                           id="biaya_pendaftaran" 
                                           name="biaya_pendaftaran" 
                                           value="{{ old('biaya_pendaftaran', $settings->biaya_pendaftaran) }}"
                                           placeholder="Masukkan jumlah biaya pendaftaran"
                                           min="0" 
                                           step="1"
                                           style="border-color: #01a9ac; border-left: none; border-radius: 0 4px 4px 0;">
                                    @error('biaya_pendaftaran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Biaya SPMB -->
                            <div class="col-md-6 mb-3">
                                <label for="biaya_spmb" class="form-label">
                                    <i class="fas fa-money-bill-wave me-1"></i>Biaya SPMB
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #01a9ac; color: #ffffff; border-color: #01a9ac; border-radius: 4px 0 0 4px; min-width: 80px; padding: 0.375rem 1.5rem; text-align: center; font-weight: 500;">Rp</span>
                                    <input type="number" 
                                           class="form-control @error('biaya_spmb') is-invalid @enderror" 
                                           id="biaya_spmb" 
                                           name="biaya_spmb" 
                                           value="{{ old('biaya_spmb', $settings->biaya_spmb) }}"
                                           placeholder="Masukkan jumlah biaya SPMB"
                                           min="0" 
                                           step="1"
                                           style="border-color: #01a9ac; border-left: none; border-radius: 0 4px 4px 0;">
                                    @error('biaya_spmb')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-4">
                            <label for="deskripsi" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Deskripsi
                            </label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" 
                                      name="deskripsi" 
                                      rows="4" 
                                      placeholder="Deskripsi pengaturan SPMB...">{{ old('deskripsi', $settings->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('manage.spmb.settings') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="form-card mt-4">
                <div class="p-4">
                    <h6 class="mb-3">
                        <i class="fas fa-info-circle me-2"></i>Informasi Pengaturan
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Dibuat:</strong> {{ $settings->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Diupdate:</strong> {{ $settings->updated_at->format('d/m/Y H:i') }}
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
    // Auto format currency
    document.getElementById('biaya_pendaftaran').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });

    document.getElementById('biaya_spmb').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });

    // Date validation
    document.getElementById('tanggal_buka').addEventListener('change', function() {
        const tanggalBuka = new Date(this.value);
        const tanggalTutup = document.getElementById('tanggal_tutup');
        
        if (tanggalTutup.value) {
            const tanggalTutupDate = new Date(tanggalTutup.value);
            if (tanggalBuka > tanggalTutupDate) {
                alert('Tanggal buka tidak boleh lebih besar dari tanggal tutup!');
                this.value = '';
            }
        }
    });

    document.getElementById('tanggal_tutup').addEventListener('change', function() {
        const tanggalTutup = new Date(this.value);
        const tanggalBuka = document.getElementById('tanggal_buka');
        
        if (tanggalBuka.value) {
            const tanggalBukaDate = new Date(tanggalBuka.value);
            if (tanggalBukaDate > tanggalTutup) {
                alert('Tanggal tutup tidak boleh lebih kecil dari tanggal buka!');
                this.value = '';
            }
        }
    });
</script>
@endpush
