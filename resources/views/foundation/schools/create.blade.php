@extends('layouts.adminty')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0" style="font-size: 1.5rem;">Tambah Sekolah</h4>
        <a href="{{ route('manage.foundation.schools.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body">
            <form action="{{ route('manage.foundation.schools.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="foundation_id" value="{{ $foundation->id }}">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_sekolah" class="form-label" style="font-size: 0.9rem;">Nama Sekolah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_sekolah') is-invalid @enderror" 
                               id="nama_sekolah" name="nama_sekolah" value="{{ old('nama_sekolah') }}" required>
                        @error('nama_sekolah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="jenjang" class="form-label" style="font-size: 0.9rem;">Jenjang <span class="text-danger">*</span></label>
                        <select class="form-control select-primary @error('jenjang') is-invalid @enderror" id="jenjang" name="jenjang" required>
                            <option value="">Pilih Jenjang</option>
                            <option value="TK" {{ old('jenjang') == 'TK' ? 'selected' : '' }}>TK</option>
                            <option value="SD" {{ old('jenjang') == 'SD' ? 'selected' : '' }}>SD</option>
                            <option value="SMP" {{ old('jenjang') == 'SMP' ? 'selected' : '' }}>SMP</option>
                            <option value="SMA" {{ old('jenjang') == 'SMA' ? 'selected' : '' }}>SMA</option>
                            <option value="SMK" {{ old('jenjang') == 'SMK' ? 'selected' : '' }}>SMK</option>
                            <option value="MA" {{ old('jenjang') == 'MA' ? 'selected' : '' }}>MA</option>
                        </select>
                        @error('jenjang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kepala_sekolah" class="form-label" style="font-size: 0.9rem;">Kepala Sekolah</label>
                        <input type="text" class="form-control @error('kepala_sekolah') is-invalid @enderror" 
                               id="kepala_sekolah" name="kepala_sekolah" value="{{ old('kepala_sekolah') }}">
                        @error('kepala_sekolah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="npsn" class="form-label" style="font-size: 0.9rem;">NPSN</label>
                        <input type="text" class="form-control @error('npsn') is-invalid @enderror" 
                               id="npsn" name="npsn" value="{{ old('npsn') }}">
                        @error('npsn')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label" style="font-size: 0.9rem;">Alamat <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('alamat') is-invalid @enderror" 
                              id="alamat" name="alamat" rows="3" required>{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="alamat_baris_1" class="form-label" style="font-size: 0.9rem;">Alamat Baris 1</label>
                        <textarea class="form-control @error('alamat_baris_1') is-invalid @enderror" 
                                  id="alamat_baris_1" name="alamat_baris_1" rows="3">{{ old('alamat_baris_1') }}</textarea>
                        @error('alamat_baris_1')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="alamat_baris_2" class="form-label" style="font-size: 0.9rem;">Alamat Baris 2 (Kab./Kota)</label>
                        <input type="text" class="form-control @error('alamat_baris_2') is-invalid @enderror" 
                               id="alamat_baris_2" name="alamat_baris_2" value="{{ old('alamat_baris_2') }}">
                        @error('alamat_baris_2')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="no_telp" class="form-label" style="font-size: 0.9rem;">Telepon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('no_telp') is-invalid @enderror" 
                               id="no_telp" name="no_telp" value="{{ old('no_telp') }}" required>
                        @error('no_telp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label" style="font-size: 0.9rem;">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <input type="hidden" name="status" value="active">

                <div class="mb-3">
                    <label for="logo_sekolah" class="form-label" style="font-size: 0.9rem;">Logo Sekolah</label>
                    <input type="file" class="form-control @error('logo_sekolah') is-invalid @enderror" 
                           id="logo_sekolah" name="logo_sekolah" accept="image/jpeg,image/jpg,image/png">
                    <small class="form-text text-muted" style="font-size: 0.85rem;">Format: JPG, PNG (Max: 2MB)</small>
                    @error('logo_sekolah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('manage.foundation.schools.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

