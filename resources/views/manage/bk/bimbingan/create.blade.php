@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="mb-1 fw-bold text-dark">
            <i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Bimbingan Konseling
        </h2>
        <p class="text-muted mb-0">Tambahkan data bimbingan konseling siswa</p>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('manage.bk.bimbingan.store') }}" method="POST">
                @csrf
                
                <div class="row g-4">
                    <!-- Siswa -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold required">Pilih Siswa</label>
                        <select name="siswa_id" class="form-control @error('siswa_id') is-invalid @enderror" id="siswa_id" required>
                            <option value="">-- Pilih Siswa --</option>
                            @forelse($students as $student)
                                <option value="{{ $student->student_id }}" {{ old('siswa_id') == $student->student_id ? 'selected' : '' }}>
                                    {{ $student->student_nis }} - {{ $student->student_full_name }}
                                    @if($student->class)
                                        ({{ $student->class->class_name }})
                                    @endif
                                </option>
                            @empty
                                <option value="" disabled>Tidak ada data siswa</option>
                            @endforelse
                        </select>
                        @error('siswa_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Total {{ $students->count() }} siswa</small>
                    </div>

                    <!-- Tanggal Bimbingan -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold required">Tanggal Bimbingan</label>
                        <input type="date" name="tanggal_bimbingan" 
                               class="form-control @error('tanggal_bimbingan') is-invalid @enderror" 
                               value="{{ old('tanggal_bimbingan', date('Y-m-d')) }}" required>
                        @error('tanggal_bimbingan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Jenis Bimbingan -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold required">Jenis Bimbingan</label>
                        <select name="jenis_bimbingan" class="form-select @error('jenis_bimbingan') is-invalid @enderror" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="akademik" {{ old('jenis_bimbingan') == 'akademik' ? 'selected' : '' }}>Akademik</option>
                            <option value="pribadi" {{ old('jenis_bimbingan') == 'pribadi' ? 'selected' : '' }}>Pribadi</option>
                            <option value="sosial" {{ old('jenis_bimbingan') == 'sosial' ? 'selected' : '' }}>Sosial</option>
                            <option value="karir" {{ old('jenis_bimbingan') == 'karir' ? 'selected' : '' }}>Karir</option>
                        </select>
                        @error('jenis_bimbingan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kategori -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold required">Kategori</label>
                        <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="ringan" {{ old('kategori') == 'ringan' ? 'selected' : '' }}>Ringan</option>
                            <option value="sedang" {{ old('kategori') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="berat" {{ old('kategori') == 'berat' ? 'selected' : '' }}>Berat</option>
                        </select>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Sesi Ke -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold required">Sesi Ke</label>
                        <input type="number" name="sesi_ke" 
                               class="form-control @error('sesi_ke') is-invalid @enderror" 
                               value="{{ old('sesi_ke', 1) }}" min="1" required>
                        @error('sesi_ke')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold required">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="dijadwalkan" {{ old('status') == 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                            <option value="berlangsung" {{ old('status') == 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                            <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditunda" {{ old('status') == 'ditunda' ? 'selected' : '' }}>Ditunda</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Permasalahan -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold required">Permasalahan</label>
                        <textarea name="permasalahan" rows="4" 
                                  class="form-control @error('permasalahan') is-invalid @enderror" 
                                  placeholder="Deskripsikan permasalahan yang dihadapi siswa..." required>{{ old('permasalahan') }}</textarea>
                        @error('permasalahan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Analisis -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Analisis</label>
                        <textarea name="analisis" rows="3" 
                                  class="form-control @error('analisis') is-invalid @enderror" 
                                  placeholder="Analisis terhadap permasalahan (opsional)...">{{ old('analisis') }}</textarea>
                        @error('analisis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tindakan -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Tindakan/Solusi</label>
                        <textarea name="tindakan" rows="3" 
                                  class="form-control @error('tindakan') is-invalid @enderror" 
                                  placeholder="Tindakan atau solusi yang diberikan (opsional)...">{{ old('tindakan') }}</textarea>
                        @error('tindakan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Hasil -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Hasil</label>
                        <textarea name="hasil" rows="3" 
                                  class="form-control @error('hasil') is-invalid @enderror" 
                                  placeholder="Hasil dari bimbingan (opsional)...">{{ old('hasil') }}</textarea>
                        @error('hasil')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Catatan -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Catatan Tambahan</label>
                        <textarea name="catatan" rows="2" 
                                  class="form-control @error('catatan') is-invalid @enderror" 
                                  placeholder="Catatan tambahan (opsional)...">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('manage.bk.bimbingan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.required::after {
    content: " *";
    color: red;
}
</style>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function() {
    // Wait for page to load
    if (typeof jQuery === 'undefined') {
        console.error('jQuery not loaded!');
        return;
    }

    jQuery(document).ready(function($) {
        console.log('Initializing Select2 for siswa dropdown...');
        console.log('Total students:', {{ $students->count() }});
        
        // Initialize Select2 for siswa dropdown
        var $select = $('#siswa_id');
        
        if ($select.length) {
            $select.select2({
                placeholder: '-- Pilih Siswa --',
                allowClear: true,
                width: '100%'
            });
            console.log('Select2 initialized successfully');
        } else {
            console.error('Element #siswa_id not found!');
        }
    });
})();
</script>
@endpush

